<?php


class ReturnValue
{
    public $status;
    public $statusMessage;

    public function __construct($status, $statusMessage)
    {
        $this->status = $status;
        $this->statusMessage = $statusMessage;
    }

    public static function error($statusMessage): ReturnValue
    {
        return new ReturnValue(FALSE, $statusMessage);
    }

    public static function success($statusMessage): ReturnValue
    {
        return new ReturnValue(TRUE, $statusMessage);
    }
}

/**
 * Create the table for reservations.
 *
 * @param mysqli|null $conn
 */
function add_reservation_table(?mysqli $conn): void
{
    global $ERROR_TABLE_CREATION, $ERROR_DATABASE_CONNECTION, $ERROR_DATABASE_QUERY;

    if ($conn === NULL) {
        error('add_reservation_table', $ERROR_DATABASE_CONNECTION);
        return;
    }

    $sql = $conn->prepare("
CREATE TABLE IF NOT EXISTS reservations
(
    reservation_id   VARCHAR(60)     NOT NULL PRIMARY KEY,
    name             VARCHAR(40)     NOT NULL,
    email            VARCHAR(50)     NOT NULL,
    phone            VARCHAR(20)     NOT NULL,
    address          VARCHAR(200)    NOT NULL,
    date             DATE            NOT NULL,
    reservation_date DATE            NOT NULL,
    from_time        TIME            NOT NULL,
    to_time          TIME            NOT NULL,    
    price            NUMERIC         NOT NULL DEFAULT 0,
    cancelled        BOOLEAN         NOT NULL DEFAULT FALSE,
    CONSTRAINT NAME_CHECK CHECK (REGEXP_LIKE(name, '^[A-ZäÄöÖüÜßa-z]+ [A-ZäÄöÖüÜßa-z]+$')),
    CONSTRAINT EMAIL_CHECK CHECK (REGEXP_LIKE(email, '^[A-Za-z0-9\.-]+@(htwg-konstanz.de|uni-konstanz.de)$'))
)");

    if ($sql === FALSE) {
        error('add_reservation_table', $ERROR_DATABASE_QUERY);
        return;
    }

    if ($sql->execute()) {
        return;
    }
    error('add_reservation_table', $ERROR_TABLE_CREATION);
}


/**
 * Get all reservations from database.
 *
 * @param mysqli|null $conn
 * @return array<string>
 */
function get_reservations(?mysqli $conn): array
{
    global $ERROR_DATABASE_CONNECTION, $ERROR_DATABASE_QUERY;
    if ($conn === NULL) {
        error('get_reservations', $ERROR_DATABASE_CONNECTION);
        return [];
    }

    try {
        $sql = $conn->prepare('SELECT * FROM reservations WHERE date >=CURRENT_DATE() ORDER BY Date;');
        if ($sql === FALSE) {
            error('get_reservations', $ERROR_DATABASE_QUERY);
            return [];
        }
        $result_execute = $sql->execute();
        if ($result_execute === FALSE) {
            return [];
        }
    } catch (Exception $e) {
        error('get_reservations', $e);
        return [];
    }

    $result = $sql->get_result();
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}


/**
 * Returns the amount of kajaks of ONE TYPE.
 *
 * @param mysqli|null $conn
 * @param string $date
 * @param array<string> $timeslots
 * @param string $kajak_kind
 * @return array
 */
function get_available_kajaks(?mysqli $conn, string $date, array $timeslots, string $kajak_kind): array
{
    global $ERROR_DATABASE_CONNECTION;

    if ($conn === NULL) {
        error('get_available_kajaks', $ERROR_DATABASE_CONNECTION);
        return [];
    }

    if (count($timeslots) === 0) {
        return [];
    }

    /* convert date to DateTime to be able to subtract one second */
    try {
        $timeslots[1] = new DateTime(end($timeslots));
    } catch (Exception $e) {
        error('get_available_kajaks', $e);
        return [];
    }

    /* it is important to exclude the current time from the next timeslot */
    $timeslots[1]->modify('-1 second');
    $timeslots[1] = $timeslots[1]->format('H:i:s');
    $timeslots = array((string)$timeslots[0], $timeslots[1]);

    /* select all the kajak names of a type that are available in the requested timeslot */
    $sql = $conn->prepare("
        SELECT kajak_name, seats
FROM kajaks
WHERE kajak_name NOT IN (SELECT kajak_reservation.kajak_name
                         FROM kajak_reservation
                                  INNER JOIN reservations
                                             ON reservations.reservation_id = kajak_reservation.reservation_id
                         WHERE reservations.date = ?
                           AND reservations.cancelled = '0'
                           AND (reservations.from_time BETWEEN ? AND ?
                             OR reservations.to_time BETWEEN ? AND ?))
  AND kajaks.kind = ? AND kajaks.available = 1");
    $sql->bind_param('ssssss', $date, $timeslots[0], $timeslots[1], $timeslots[0], $timeslots[1], $kajak_kind);

    $sql->execute();
    $result = $sql->get_result();

    if ($result === FALSE) {
        error('get_available_kajaks', $sql->error);
        return [];
    }

    /* fetch all names of available kajaks */
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

/**
 * Returns the amount of kajaks of ALL TYPES.
 *
 * @param mysqli|null $conn
 * @param string $date
 * @param array $timeslots
 * @param array $kajak_kinds
 * @param array $requested_amounts
 * @return array
 */
function get_all_available_kajaks(?mysqli $conn, string $date, array $timeslots, array $kajak_kinds, array $requested_amounts): array
{
    global $ERROR_DATABASE_CONNECTION;

    if ($conn === NULL) {
        error('get_all_available_kajaks', $ERROR_DATABASE_CONNECTION);
        return [];
    }

    $reserved_kajaks = [];
    foreach ($kajak_kinds as $kajak_kind) {
        $requested_amount = !isset($requested_amounts[$kajak_kind]) ? 0 : (int)clean_string($requested_amounts[$kajak_kind]);
        /* skip 0 requested kajaks */
        if ($requested_amount === 0) {
            continue;
        }

        $available_kajaks = get_available_kajaks($conn, $date, $timeslots, $kajak_kind);
        if (count($available_kajaks) === 0 || count($available_kajaks) < $requested_amount) {
            /* early skip because it does not make any sense to continue, if a specific kind is not available */
            return [];
        }

        $reserved_kajaks[] = array_slice($available_kajaks, 0, $requested_amount);
    }
    /* return flattened array */
    return array_merge(...$reserved_kajaks);
}

/**
 * Insert reservation into database.
 *
 * @param mysqli|null $conn
 * @param string $name
 * @param string $email
 * @param string $phone
 * @param string $address
 * @param string $date
 * @param array<string> $timeslot
 * @param array<string> $kajak_names
 * @param int $price
 * @return string the reservation id or empty string if something fails.
 */
function insert_reservation(?mysqli $conn, string $name, string $email, string $phone, string $address, string $date, array $timeslot, array $kajak_names, int $price): string
{
    global $ERROR_DATABASE_CONNECTION;

    if ($conn === NULL) {
        error('insert_reservation', $ERROR_DATABASE_CONNECTION);
        return '';
    }

    $reservation_date = date('Y-m-d');
    $reservation_id = uniqid('', TRUE);

    try {
        $sql = $conn->prepare('
INSERT INTO reservations (reservation_id, name, email, phone, date, address, reservation_date, from_time, to_time, price)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ? ,?);
');
        $sql->bind_param('ssssssssss', $reservation_id, $name, $email, $phone, $date, $address, $reservation_date, $timeslot[0], $timeslot[1], $price);
        $result_execute = $sql->execute();
        if ($result_execute === FALSE) {
            return '';
        }

        /* assign each kajak the reservation id */
        foreach ($kajak_names as $kajak_name) {
            $sql = $conn->prepare('
INSERT INTO kajak_reservation (kajak_name, reservation_id)
    VALUES (?, ?);
');
            $sql->bind_param('ss', $kajak_name, $reservation_id);
            $result_execute = $sql->execute();
            if ($result_execute === FALSE) {
                return '';
            }
        }

        return $reservation_id;
    } catch (Exception $e) {
        error('insert_reservation', $e);
        return '';
    }
}


/**
 * Reservate a kajak.
 *
 * @param mysqli|null $conn
 * @param array $fields
 * @param bool $send_email
 * @return ReturnValue
 */
function user_reservate_kajak(?mysqli $conn, array $fields, bool $send_email = FALSE): ReturnValue
{
    global $ERROR_DATABASE_CONNECTION;
    if ($conn === NULL) {
        error('reservate_kajak', $ERROR_DATABASE_CONNECTION);
        return ReturnValue::error($ERROR_DATABASE_CONNECTION);
    }

    global $INFO_RESERVATION_SUCCESS, $ERROR_CHECK_FORM, $ERROR_TIMESLOT_GAP, $ERROR_RESERVATION_KAJAK_NOT_AVAILABLE, $ERROR_RESERVATION_KAJAK_NOT_SELECTED, $ERROR_RESERVATION_TIMESLOT_NOT_SELECTED, $ERROR_SUCCESS_BUT_MAIL_NOT_SENT;

    $name = clean_string($fields['name']);
    $full_name = $name . ' ' . clean_string($fields['surname']);
    $email = mb_strtolower(clean_string($fields['email'] ?? ''));
    /* check if email in blacklist */
    global $ERROR_USER_BLOCKED;
    if (in_array($email, get_blacklist_emails($conn), TRUE)) {
        return ReturnValue::error($ERROR_USER_BLOCKED);
    }

    $phone = clean_string($fields['phone'] ?? '');
    $address = clean_string($fields['street'] . ' ' . $fields['plz'] . ', ' . $fields['city'] . ', ' . $fields['country']);
    $date = clean_string($fields['date']);

    /****** prepare timeslots ******/
    $raw_timeslots = clean_array($fields['timeslots'] ?? []);

    $amount_timeslots = count($raw_timeslots);
    /* check if timeslot is selected */
    if ($amount_timeslots === 0) {
        return ReturnValue::error($ERROR_RESERVATION_TIMESLOT_NOT_SELECTED);
    }

    /* check if there is a gap in the timeslots, e.g. if at least 3 timeslots where selected */
    if ($amount_timeslots > 1) {
        for ($index = 0, $indexMax = count($raw_timeslots); $index < $indexMax; $index++) {
            if ($index + 1 !== $indexMax && (int)$raw_timeslots[$index] + 1 !== (int)$raw_timeslots[$index + 1]) {
                return ReturnValue::error($ERROR_TIMESLOT_GAP);
            }
        }
    }

    global $config;
    $config_timeslots = $config->get_timeslots();
    $min_time_index = $raw_timeslots[0];
    $max_time_index = end($raw_timeslots);
    $min_time = $config_timeslots[$min_time_index][0];
    $max_time = $config_timeslots[$max_time_index][1];
    $timeslots = [$min_time, $max_time];

    /* get all kajak kinds */
    $kajak_kinds = get_kajak_kinds($conn);

    /* check if more than 0 kajaks where selected */
    $amount_kajaks = array_reduce($kajak_kinds, static function ($carry, $kajak_kind) {
        $amount = (int)clean_string($_POST[$kajak_kind] ?? '0');
        $carry[] = array(
            'kind' => $kajak_kind,
            'amount' => $amount
        );
        return $carry;
    }, []);

    /* sum up kajaks to check if any kajak was selected */
    if (array_reduce($amount_kajaks, static function ($carry, $kajak) {
            return $carry + $kajak['amount'];
        }, 0) === 0) {
        /* throw error if no kajak was selected */
        return ReturnValue::error($ERROR_RESERVATION_KAJAK_NOT_SELECTED);
    }

    /* get names of available kajaks */
    $reserved_kajaks = get_all_available_kajaks($conn, $date, $timeslots, $kajak_kinds, $_POST);
    if (count($reserved_kajaks) === 0) {
        return ReturnValue::error($ERROR_RESERVATION_KAJAK_NOT_AVAILABLE);
    }

    /* get all kajak names */
    $kajak_names = array_map(static function ($available_kajak) {
        return $available_kajak['kajak_name'];
    }, $reserved_kajaks);

    /* calculate price */
    $price = calculate_price($conn, array_map(static function () {
        return TRUE;
    }, $raw_timeslots), $amount_kajaks);

    /****** insert reservation ******/
    $reservation_id = insert_reservation($conn, $full_name, $email, $phone, $address, $date, $timeslots, $kajak_names, $price);
    if ($reservation_id === '') {
        return ReturnValue::error($ERROR_CHECK_FORM);
    }

    /* send email */
    if ($send_email) {
        $send_mail_status = send_reservation_email($reservation_id, $name, $email, $reserved_kajaks, $timeslots, $date, $price);
        if ($send_mail_status === FALSE) {
            return ReturnValue::error($ERROR_SUCCESS_BUT_MAIL_NOT_SENT);
        }
    }
    return ReturnValue::success($INFO_RESERVATION_SUCCESS);
}


/**
 * Recover cancelled reservations by id.
 * USED BY ADMIN.
 *
 * @param mysqli|null $conn
 * @param array<string> $ids
 * @return void
 */
function admin_recover_reservations(?mysqli $conn, array $ids): void
{
    global $ERROR_DATABASE_CONNECTION;

    if ($conn === NULL) {
        error('recover_reservations', $ERROR_DATABASE_CONNECTION);
        return;
    }

    /* concat all strings in array to one string */
    $ids_as_string = implode(',', $ids);
    $sql = $conn->prepare('UPDATE reservations SET cancelled = FALSE WHERE FIND_IN_SET(reservation_id, ?)');
    $sql->bind_param('s', $ids_as_string);
    $sql->execute();
}

/**
 * Cancel reservations by id.
 * USED BY ADMIN.
 *
 * @param mysqli|null $conn
 * @param array<string> $ids
 * @return void
 */
function admin_cancel_reservations(?mysqli $conn, array $ids): void
{
    global $ERROR_DATABASE_CONNECTION;

    if ($conn === NULL) {
        error('cancel_reservations', $ERROR_DATABASE_CONNECTION);
        return;
    }

    /* concat all strings in array to one string */
    $ids_as_string = implode(',', $ids);
    $sql = $conn->prepare('UPDATE reservations SET cancelled = TRUE WHERE FIND_IN_SET(reservation_id, ?)');
    $sql->bind_param('s', $ids_as_string);
    $sql->execute();
}

/**
 * Cancel reservation by id.
 *
 * @param mysqli|null $conn
 * @param array<string> $fields
 * @param bool $send_email
 * @return string
 */
function user_cancel_reservation(?mysqli $conn, array $fields, bool $send_email = FALSE): string
{
    global $ERROR_DATABASE_CONNECTION, $ERROR_DATABASE_QUERY;

    if ($conn === NULL) {
        error('cancel_reservation', $ERROR_DATABASE_CONNECTION);
        return $ERROR_DATABASE_CONNECTION;
    }

    global $ERROR_CANCELLATION, $ERROR_CANCELLATION_NOT_FOUND, $INFO_CANCELLATION_CANCELED, $ERROR_MAIL_NOT_SENT;

    /* prepare values */
    $reservation_id = clean_string($fields['id']);
    $email = clean_string($fields['email']);

    /* check if reservation exists and is valid */
    $sql = $conn->prepare('SELECT COUNT(*) AS amount FROM reservations WHERE reservation_id = ? AND email = ? AND cancelled = 0');
    if ($sql === FALSE) {
        error('cancel_reservation', $ERROR_DATABASE_QUERY);
        return $ERROR_CANCELLATION;
    }
    $sql->bind_param('ss', $reservation_id, $email);
    $sql->execute();
    $result = $sql->get_result();
    $amount = $result->fetch_assoc()['amount'];

    /* if reservation does not exist it might be already cancelled */
    if ($amount === NULL || (int)$amount === 0) {
        return $ERROR_CANCELLATION_NOT_FOUND;
    }

    /* cancel reservation */
    $sql = $conn->prepare('UPDATE reservations SET cancelled = TRUE WHERE reservation_id = ?');
    $sql->bind_param('s', $reservation_id);
    if ($sql->execute()) {
        if ($send_email) {
            $send_mail_status = send_cancellation_email($reservation_id, $email);
            if ($send_mail_status === FALSE) {
                error('cancel_reservation', $ERROR_MAIL_NOT_SENT);
                return $ERROR_MAIL_NOT_SENT;
            }
        }
        return $INFO_CANCELLATION_CANCELED;
    }

    error('cancel_reservation', $ERROR_CANCELLATION);
    return $ERROR_CANCELLATION;
}

/**********************************************************************************************************************************************************************************/
/***********************************************************************KAJAK-RESERVATION TABLE************************************************************************************/
/**********************************************************************************************************************************************************************************/


/**
 * Create table for kajak reservations.
 *
 * @param mysqli|null $conn
 * @return void
 */
function add_reservation_kajak_table(?mysqli $conn): void
{
    global $ERROR_TABLE_CREATION, $ERROR_DATABASE_CONNECTION, $ERROR_DATABASE_QUERY;

    if ($conn === NULL) {
        error('add_reservation_kajak_table', $ERROR_DATABASE_CONNECTION);
        return;
    }

    $sql = $conn->prepare('
CREATE TABLE IF NOT EXISTS kajak_reservation
(
    reservation_id   VARCHAR(60)     NOT NULL,
    kajak_name       VARCHAR(30)     NOT NULL,
    PRIMARY KEY(reservation_id, kajak_name)
)');

    if ($sql === FALSE) {
        error('add_reservation_kajak_table', $ERROR_DATABASE_QUERY);
        return;
    }

    if ($sql->execute()) {
        return;
    }
    error('add_reservation_kajak_table', $ERROR_TABLE_CREATION);
}


/**
 * Get a dictionary of kajaks which are mapped to their reservation id.
 *
 * @param mysqli|null $conn
 * @return array
 */
function get_reserved_kajaks_by_id(?mysqli $conn): array
{
    global $ERROR_DATABASE_CONNECTION, $ERROR_DATABASE_QUERY;

    if ($conn === NULL) {
        error('get_reserved_kajaks_by_id', $ERROR_DATABASE_CONNECTION);
        return [];
    }

    try {
        $sql = $conn->prepare('SELECT * FROM kajak_reservation');
        if ($sql === FALSE) {
            error('get_reserved_kajaks_by_id', $ERROR_DATABASE_QUERY);
            return [];
        }
        $result_execute = $sql->execute();
        if ($result_execute === FALSE) {
            return [];
        }
    } catch (Exception $e) {
        error('get_reserved_kajaks_by_id', $e);
        return [];
    }

    $result = $sql->get_result();
    if ($result === FALSE) {
        return [];
    }
    $kajak_reservation_list = mysqli_fetch_all($result, MYSQLI_ASSOC);

    $kajaks_by_reservation_id = [];
    foreach ($kajak_reservation_list as $kajak_reservation) {
        $kajak_name = $kajak_reservation['kajak_name'];
        $reservation_id = $kajak_reservation['reservation_id'];
        if (!array_key_exists($reservation_id, $kajaks_by_reservation_id)) {
            $kajaks_by_reservation_id[$reservation_id] = [];
        }
        $kajaks_by_reservation_id[$reservation_id][] = $kajak_name;
    }
    return $kajaks_by_reservation_id;
}
