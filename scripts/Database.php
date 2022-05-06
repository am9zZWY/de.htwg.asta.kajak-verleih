<?php
/** @noinspection ForgottenDebugOutputInspection */

global $config;

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
 * Create connection to mysql database.
 * Returns connection object if successful.
 *
 * @return mysqli|null
 */
function connect_to_database(): ?mysqli
{
    global $ERROR_DATABASE_CONNECTION;

    /* credentials to connect to database */
    $servername = get_env('MYSQL_SERVER');
    $username = get_env('MYSQL_USERNAME');
    $password = get_env('MYSQL_PASSWORD');
    $dbname = get_env('MYSQL_DATABASE');

    /* Create connection */
    try {
        $conn = new mysqli($servername, $username, $password, $dbname);
        /* Check connection */
        if ($conn->connect_error) {
            error_log($ERROR_DATABASE_CONNECTION);
            return null;
        }
    } catch (Exception $e) {
        error_log($ERROR_DATABASE_CONNECTION);
        return null;
    }

    return $conn;
}


function add_blacklist_table(?mysqli $conn): void
{
    global $ERROR_TABLE_CREATION, $ERROR_DATABASE_CONNECTION, $ERROR_DATABASE_QUERY;

    if ($conn === null) {
        error_log($ERROR_DATABASE_CONNECTION);
        return;
    }

    $sql = $conn->prepare("
CREATE TABLE IF NOT EXISTS blacklist
(
    
    name             VARCHAR(40)     NOT NULL,
    email            VARCHAR(50)     NOT NULL,
    comment          VARCHAR(255)    NOT NULL,
    PRIMARY KEY(name, email)
)");

    if ($sql === false) {
        error_log($ERROR_DATABASE_QUERY);
        return;
    }

    if ($sql->execute()) {
        return;
    }
    error_log($ERROR_TABLE_CREATION);
}

/**
 * Create the table for reservations.
 *
 * @param mysqli|null $conn
 */
function add_reservation_table(?mysqli $conn): void
{
    global $ERROR_TABLE_CREATION, $ERROR_DATABASE_CONNECTION, $ERROR_DATABASE_QUERY;

    if ($conn === null) {
        error_log($ERROR_DATABASE_CONNECTION);
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
    CONSTRAINT EMAIL_CHECK CHECK (REGEXP_LIKE(email, '^[A-Za-z0-9\._%+-]+@(htwg-konstanz.de|uni-konstanz.de)$'))
)");

    if ($sql === FALSE) {
        error_log($ERROR_DATABASE_QUERY);
        return;
    }

    if ($sql->execute()) {
        return;
    }
    error_log($ERROR_TABLE_CREATION);
}

/**
 * Create table for kajaks.
 *
 * @param mysqli|null $conn
 * @return void
 */
function add_kajak_table(?mysqli $conn): void
{
    global $ERROR_TABLE_CREATION, $ERROR_DATABASE_CONNECTION, $ERROR_DATABASE_QUERY;

    if ($conn === null) {
        error_log($ERROR_DATABASE_CONNECTION);
        return;
    }

    $sql = $conn->prepare("
CREATE TABLE IF NOT EXISTS kajaks
(
    kajak_name       VARCHAR(30)     NOT NULL PRIMARY KEY,
    kind             VARCHAR(30)     NOT NULL,
    seats            INT             NOT NULL DEFAULT 0,
    available        BOOLEAN         NOT NULL DEFAULT TRUE,
    comment          VARCHAR(200)    NOT NULL DEFAULT ''
)");

    if ($sql === FALSE) {
        error_log($ERROR_DATABASE_QUERY);
        return;
    }

    if ($sql->execute()) {
        return;
    }
    error_log($ERROR_TABLE_CREATION);
}

/**
 * Create table for kajak reservations.
 *
 * @param mysqli|null $conn
 * @return void
 */
function add_reservation_kajak_table(?mysqli $conn): void
{
    global $ERROR_TABLE_CREATION, $ERROR_DATABASE_CONNECTION, $ERROR_DATABASE_QUERY;

    if ($conn === null) {
        error_log($ERROR_DATABASE_CONNECTION);
        return;
    }

    $sql = $conn->prepare("
CREATE TABLE IF NOT EXISTS kajak_reservation
(
    reservation_id   VARCHAR(60)     NOT NULL,
    kajak_name       VARCHAR(30)     NOT NULL,
    PRIMARY KEY(reservation_id, kajak_name)
)");

    if ($sql === FALSE) {
        error_log($ERROR_DATABASE_QUERY);
        return;
    }

    if ($sql->execute()) {
        return;
    }
    error_log($ERROR_TABLE_CREATION);
}

/**
 * Add a kajak to the database.
 *
 * @param mysqli|null $conn
 * @param string $name
 * @param string $kind
 * @param int $seats
 * @return void
 */
function add_kajak(?mysqli $conn, string $name, string $kind, int $seats): void
{
    global $ERROR_DATABASE_CONNECTION, $ERROR_DATABASE_QUERY, $ERROR_TYPE_NOT_IN_CONFIG, $ERROR_TOO_MANY_SEATS, $ERROR_EXECUTION, $config;

    if ($conn === null) {
        error_log($ERROR_DATABASE_CONNECTION);
        return;
    }

    /* get all kajaks and check if the kind is valid */
    $kinds = $config->getKajakKinds();
    if (!in_array($kind, $kinds, TRUE)) {
        error_log($ERROR_TYPE_NOT_IN_CONFIG);
        return;
    }

    /* get seats per kajak and check if number does not exceed config. It can be less if one seat is e.g. damaged */
    $seats_per_kajak = $config->getSeatsPerKajak();
    if ($seats_per_kajak[$kind] < $seats) {
        error_log($ERROR_TOO_MANY_SEATS);
        return;
    }

    /* add kajak to list of kajaks */
    try {
        $sql = $conn->prepare("
        INSERT INTO kajaks (kajak_name, kind, seats)
            VALUES (?, ?, ?);
        ");

        if ($sql === FALSE) {
            error_log($ERROR_DATABASE_QUERY);
            return;
        }

        $sql->bind_param('sss', $name, $kind, $seats);
        if ($sql->execute()) {
            return;
        }
        error_log($ERROR_EXECUTION);
    } catch (Exception $e) {
        error_log($e);
        return;
    }
}

/**
 * Update kajak in the database.
 *
 * @param mysqli|null $conn
 * @param string $old_name
 * @param string $name
 * @param string $kind
 * @param int $seats
 * @param int $available
 * @param string $comment
 * @return void
 */
function update_kajak(?mysqli $conn, string $old_name, string $name, string $kind, int $seats, int $available, string $comment): void
{
    global $ERROR_DATABASE_CONNECTION, $ERROR_DATABASE_QUERY, $ERROR_TYPE_NOT_IN_CONFIG, $ERROR_TOO_MANY_SEATS, $ERROR_EXECUTION, $config;

    if ($conn === null) {
        error_log($ERROR_DATABASE_CONNECTION);
        return;
    }

    /* get all kajaks and check if the kind is valid */
    $kinds = $config->getKajakKinds();
    if (!in_array($kind, $kinds, TRUE)) {
        error_log($ERROR_TYPE_NOT_IN_CONFIG);
        return;
    }

    /* get seats per kajak and check if number does not exceed config. It can be less if one seat is e.g. damaged */
    $seats_per_kajak = $config->getSeatsPerKajak();
    if ($seats_per_kajak[$kind] < $seats) {
        error_log($ERROR_TOO_MANY_SEATS);
        return;
    }

    /* add kajak to list of kajaks */
    try {
        $sql = $conn->prepare("
        UPDATE kajaks
        SET kajak_name = ?, kind = ?, seats = ?, available = ?, comment = ?
        WHERE kajak_name = ?;
        ");

        if ($sql === FALSE) {
            error_log($ERROR_DATABASE_QUERY);
            return;
        }

        $sql->bind_param('ssssss', $name, $kind, $seats, $available, $comment, $old_name);
        if ($sql->execute()) {
            return;
        }
        error_log($ERROR_EXECUTION);
    } catch (Exception $e) {
        error_log($e);
        return;
    }
}

/**
 * Remove kajak from database by name.
 *
 * @param mysqli|null $conn
 * @param string $kajak_name
 * @return void
 */
function remove_kajak(?mysqli $conn, string $kajak_name): void
{
    global $ERROR_DATABASE_CONNECTION, $ERROR_EXECUTION;

    if ($conn === null) {
        error_log($ERROR_DATABASE_CONNECTION);
        return;
    }

    try {
        $sql = $conn->prepare("DELETE FROM kajaks WHERE kajak_name = ?");
        $sql->bind_param('s', $kajak_name);
        if ($sql->execute()) {
            return;
        }
        error_log($ERROR_EXECUTION);
    } catch (Exception $e) {
        error_log($e);
        return;
    }
}

/**
 * Get all kajak.
 *
 * @param mysqli|null $conn
 * @param bool $exclude_not_available
 * @return array
 */
function get_kajaks(?mysqli $conn, bool $exclude_not_available = FALSE): array
{
    global $ERROR_DATABASE_CONNECTION;
    if ($conn === null) {
        error_log($ERROR_DATABASE_CONNECTION);
        return [];
    }

    try {
        if ($exclude_not_available) {
            $sql = $conn->prepare("SELECT * FROM kajaks WHERE available = 1 ORDER BY seats, kajak_name");
        } else {
            $sql = $conn->prepare("SELECT * FROM kajaks ORDER BY seats, kajak_name");
        }
        $result_execute = $sql->execute();
        if ($result_execute === FALSE) {
            return [];
        }
    } catch (Exception $e) {
        error_log($e);
        return [];
    }

    $result = $sql->get_result();
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

/**
 * Get kajaks sorted by kinds.
 *
 * @return void
 */
function get_kajak_with_real_amount(?mysqli $conn): array
{
    global $ERROR_DATABASE_CONNECTION;
    if ($conn === null) {
        error_log($ERROR_DATABASE_CONNECTION);
        return [];
    }

    global $config;
    $kajaks = $config->getKajaks();
    $kajak_amounts = get_kajak_amounts($conn);

    foreach ($kajaks as $kajak) {
        $kajak->amount = $kajak_amounts[$kajak->kind]['amount'];
    }

    return $kajaks;
}

/**
 * Get all kajak amounts by kind.
 *
 * @param mysqli|null $conn
 * @return array
 */
function get_kajak_amounts(?mysqli $conn): array
{
    global $ERROR_DATABASE_CONNECTION;
    if ($conn === null) {
        error_log($ERROR_DATABASE_CONNECTION);
        return [];
    }

    return array_reduce(get_kajaks($conn, TRUE), static function ($carry, $kajak) {
        $kajak_kind = $kajak['kind'];
        if (isset($carry[$kajak_kind])) {
            $carry[$kajak_kind]['amount']++;
        } else {
            $carry[$kajak_kind]['amount'] = 1;
        }
        return $carry;
    }, array());
}

/**
 * Get all kajak kinds.
 *
 * @param mysqli|null $conn
 * @return array
 */
function get_kajak_kinds(?mysqli $conn): array
{
    global $ERROR_DATABASE_CONNECTION;
    if ($conn === null) {
        error_log($ERROR_DATABASE_CONNECTION);
        return [];
    }

    return array_values(array_unique(array_map(static function ($kajak) {
        return $kajak['kind'];
    }, get_kajaks($conn))));
}

/**
 * Get all reservations from database.
 *
 * @param mysqli|null $conn
 * @return array<string>
 */
function get_reservations(?mysqli $conn): array
{
    global $ERROR_DATABASE_CONNECTION;
    if ($conn === null) {
        error_log($ERROR_DATABASE_CONNECTION);
        return [];
    }

    try {
        $sql = $conn->prepare("SELECT * FROM reservations WHERE reservation_date >=CURRENT_DATE() ORDER BY Date;");
        $result_execute = $sql->execute();
        if ($result_execute === FALSE) {
            return [];
        }
    } catch (Exception $e) {
        error_log($e);
        return [];
    }

    $result = $sql->get_result();
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

/**
 * Get a dictionary of kajaks which are mapped to their reservation id.
 *
 * @param mysqli|null $conn
 * @return array
 */
function get_reserved_kajaks_by_id(?mysqli $conn): array
{
    global $ERROR_DATABASE_CONNECTION;
    if ($conn === null) {
        error_log($ERROR_DATABASE_CONNECTION);
        return [];
    }

    try {
        $sql = $conn->prepare("SELECT * FROM kajak_reservation");
        $result_execute = $sql->execute();
        if ($result_execute === FALSE) {
            return [];
        }
    } catch (Exception $e) {
        error_log($e);
        return [];
    }

    $result = $sql->get_result();
    if ($result === FALSE) {
        return [];
    }
    $kajak_reservation_list = mysqli_fetch_all($result, MYSQLI_ASSOC);

    $kajaks_by_reservation_id = array();
    foreach ($kajak_reservation_list as $item) {
        $kajak_name = $item["kajak_name"];
        $reservation_id = $item["reservation_id"];
        if (!array_key_exists($reservation_id, $kajaks_by_reservation_id)) {
            $kajaks_by_reservation_id[$reservation_id] = array();
        }
        $kajaks_by_reservation_id[$reservation_id][] = $kajak_name;
    }
    return $kajaks_by_reservation_id;
}

/**
 * USE WITH CAUTION!
 * USED BY ADMIN.
 *
 * Drops all tables.
 *
 * @param mysqli|null $conn
 * @return void
 */
function drop_all_tables(?mysqli $conn): void
{
    global $ERROR_DATABASE_CONNECTION;
    if ($conn === null) {
        error_log($ERROR_DATABASE_CONNECTION);
        return;
    }

    $sql = $conn->prepare("DROP TABLE reservations");
    $sql->execute();
    $sql = $conn->prepare("DROP TABLE kajak_reservation");
    $sql->execute();
    send_mail('', 'Tabellen gelöscht', 'Tabellen gelöscht! Bitte überprüfen, ob diese Aktion gewollt war');
}

/**
 * Calculate price.
 *
 * @param mysqli|null $conn
 * @param $timeslots
 * @param $amount_kajaks_per_kind
 * @return int
 */
function calculatePrice(?mysqli $conn, $timeslots, $amount_kajaks_per_kind): int
{
    global $config, $ERROR_DATABASE_CONNECTION;
    if ($conn === null) {
        error_log($ERROR_DATABASE_CONNECTION);
        return 0;
    }

    /* get all kajaks */
    $amount_kajaks = array_reduce($amount_kajaks_per_kind, static function ($carry, $kajak) {
        return $carry + $kajak['amount'];
    }, 0);

    /* get amount timeslots */
    $amount_timeslots = count(array_filter($timeslots, static function ($timeslot) {
        return $timeslot;
    }));

    $seats_per_kajak = $config->getSeatsPerKajak();
    $amount_seats = array_reduce($amount_kajaks_per_kind, static function ($carry, $kajak) use ($seats_per_kajak) {
        return $carry + ($seats_per_kajak[$kajak['kind']] * (int)$kajak['amount']);
    }, 0);

    /* max values when "all" is passed */
    $max_timeslots = count($config->getTimeslots());
    $kajaks = get_kajaks($conn, TRUE);
    $max_kajaks = count($kajaks);
    $max_seats = array_reduce($kajaks, static function ($carry, $kajak) use ($seats_per_kajak) {
        return $carry + $seats_per_kajak[$kajak['kind']];
    }, 0);

    /* prepare prices */
    $prices = $config->getPrices();
    $calculated_price = 0;
    foreach ($prices as $price) {
        $price_value = (int)$price['value'];

        $price_dependencies = $price['dependOn'];
        /* first check if requirements are met */
        $require_check = true;
        foreach ($price_dependencies as $dep) {
            $dep_name = $dep['name'];
            $dep_amount = $dep['amount'];

            if ($dep_amount === 'per') {
                continue;
            }

            $amount = 0;
            $max = 0;
            if ($dep_name === 'timeslot') {
                $amount = $amount_timeslots;
                $max = $max_timeslots;
            } else if ($dep_name === 'seat') {
                $amount = $amount_seats;
                $max = $max_seats;
            } else if ($dep_name === 'kajak') {
                $amount = $amount_kajaks;
                $max = $max_kajaks;
            }

            /* check if either the dep_amount as a number is the same as amount or if amount is max */
            $require_check = $require_check && (((string)(int)$dep_amount === $dep_amount && (int)$dep_amount === $amount)
                    || ($dep_amount === 'all' && $amount === $max));
        }

        /* if requirements are specified in dependsOn are not met, continue with next price */
        if (!$require_check) {
            continue;
        }

        /* calculate price */
        foreach ($price_dependencies as $dep) {
            $dep_name = $dep['name'];
            $dep_amount = $dep['amount'];

            if ($dep_amount !== 'per') {
                continue;
            }

            $amount = 0;
            if ($dep_name === 'timeslot') {
                $amount = $amount_timeslots;
            } else if ($dep_name === 'seat') {
                $amount = $amount_seats;
            } else if ($dep_name === 'kajak') {
                $amount = $amount_kajaks;
            }

            $calculated_price += $price_value * $amount;
        }
    }

    return $calculated_price;
}

/**
 * Returns the amount of kajaks of a kajak type.
 *
 * @param mysqli|null $conn
 * @param string $date
 * @param array<string> $timeslots
 * @param string $kajak_kind
 * @param int $requested_amount
 * @return array
 */
function get_available_kajaks(?mysqli $conn, string $date, array $timeslots, string $kajak_kind, int $requested_amount): array
{
    global $ERROR_DATABASE_CONNECTION;

    if ($conn === null) {
        error_log($ERROR_DATABASE_CONNECTION);
        return [];
    }

    if (count($timeslots) === 0) {
        return [];
    }

    /* convert date to DateTime to be able to subtract one second */
    try {
        $timeslots[1] = new DateTime(end($timeslots));
    } catch (Exception $e) {
        error_log($e);
        return [];
    }

    /* it is important to exclude the current time from the next timeslot */
    $timeslots[1]->modify("-1 second");
    $timeslots[1] = $timeslots[1]->format("H:i:s");

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
        return [];
    }

    /* fetch all names of available kajaks */
    $fetched_kajaks = mysqli_fetch_all($result, MYSQLI_ASSOC);

    /* if requested amount exceeds the available kajaks then return false */
    if (count($fetched_kajaks) < $requested_amount) {
        return [];
    }

    /* return so much names as the user requested */
    return array_slice($fetched_kajaks, 0, $requested_amount);
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

    if ($conn === null) {
        error_log($ERROR_DATABASE_CONNECTION);
        return '';
    }

    $reservation_date = date('Y-m-d');
    $reservation_id = uniqid('', TRUE);

    try {
        $sql = $conn->prepare("
INSERT INTO reservations (reservation_id, name, email, phone, date, address, reservation_date, from_time, to_time, price)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ? ,?);
");
        $sql->bind_param('ssssssssss', $reservation_id, $name, $email, $phone, $date, $address, $reservation_date, $timeslot[0], $timeslot[1], $price);
        $result_execute = $sql->execute();
        if ($result_execute === FALSE) {
            return '';
        }

        /* assign each kajak the reservation id */
        foreach ($kajak_names as $kajak_name) {
            $sql = $conn->prepare("
INSERT INTO kajak_reservation (kajak_name, reservation_id)
    VALUES (?, ?);
");
            $sql->bind_param('ss', $kajak_name, $reservation_id);
            $result_execute = $sql->execute();
            if ($result_execute === FALSE) {
                return '';
            }
        }

        return $reservation_id;
    } catch (Exception $e) {
        error_log($e);
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
function reservate_kajak(?mysqli $conn, array $fields, bool $send_email = FALSE): ReturnValue
{
    global $ERROR_DATABASE_CONNECTION;
    if ($conn === null) {
        error_log($ERROR_DATABASE_CONNECTION);
        return ReturnValue::error($ERROR_DATABASE_CONNECTION);
    }

    global $INFO_RESERVATION_SUCCESS, $ERROR_CHECK_FORM, $ERROR_TIMESLOT_GAP, $ERROR_RESERVATION_KAJAK_NOT_AVAILABLE, $ERROR_RESERVATION_KAJAK_NOT_SELECTED, $ERROR_RESERVATION_TIMESLOT_NOT_SELECTED, $ERROR_SUCCESS_BUT_MAIL_NOT_SENT;

    $name = clean_string($fields["name"]);
    $full_name = $name . ' ' . clean_string($fields["surname"]);
    $email = mb_strtolower(clean_string($fields['email']));
    $phone = clean_string($fields['phone']);
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
    $config_timeslots = $config->getTimeslots();
    $min_time_index = $raw_timeslots[0];
    $max_time_index = end($raw_timeslots);
    $min_time = $config_timeslots[$min_time_index][0];
    $max_time = $config_timeslots[$max_time_index][1];
    $timeslots = array($min_time, $max_time);

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
    $sum_kajaks = array_reduce($amount_kajaks, static function ($carry, $kajak) {
        return $carry + $kajak['amount'];
    }, 0);

    /* throw error if no kajak was selected */
    if ($sum_kajaks === 0) {
        return ReturnValue::error($ERROR_RESERVATION_KAJAK_NOT_SELECTED);
    }

    /* check for each kind, if it is available */
    $reserved_kajaks = array();
    foreach ($kajak_kinds as $kajak_kind) {
        $requested_amount = !isset($_POST[$kajak_kind]) ? 0 : (int)clean_string($_POST[$kajak_kind]);
        /* skip 0 requested kajaks */
        if ($requested_amount === 0) {
            break;
        }
        $available_kajaks = get_available_kajaks($conn, $date, $timeslots, $kajak_kind, $requested_amount);
        if (count($available_kajaks) === 0) {
            return ReturnValue::error($ERROR_RESERVATION_KAJAK_NOT_AVAILABLE);
        }

        /* this will be a matrix which is then flattened */
        $reserved_kajaks[] = $available_kajaks;
    }

    /* flatten array */
    $reserved_kajaks = array_merge(...$reserved_kajaks);

    /* insert reservation into database and get reservation_id back */
    $kajak_names = array_map(static function ($available_kajak) {
        return $available_kajak["kajak_name"];
    }, $reserved_kajaks);

    /* calculate price */
    global $config;
    $price = $config->calculatePrice(array_map(static function () {
        return true;
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
function recover_reservations(?mysqli $conn, array $ids): void
{
    global $ERROR_DATABASE_CONNECTION;

    if ($conn === null) {
        error_log($ERROR_DATABASE_CONNECTION);
        return;
    }

    /* concat all strings in array to one string */
    $ids_as_string = implode(',', $ids);
    $sql = $conn->prepare("UPDATE reservations SET cancelled = FALSE WHERE FIND_IN_SET(reservation_id, ?)");
    $sql->bind_param("s", $ids_as_string);
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
function cancel_reservations(?mysqli $conn, array $ids): void
{
    global $ERROR_DATABASE_CONNECTION;

    if ($conn === null) {
        error_log($ERROR_DATABASE_CONNECTION);
        return;
    }

    /* concat all strings in array to one string */
    $ids_as_string = implode(',', $ids);
    $sql = $conn->prepare("UPDATE reservations SET cancelled = TRUE WHERE FIND_IN_SET(reservation_id, ?)");
    $sql->bind_param("s", $ids_as_string);
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
function cancel_reservation(?mysqli $conn, array $fields, bool $send_email = FALSE): string
{
    global $ERROR_DATABASE_CONNECTION;

    if ($conn === null) {
        error_log($ERROR_DATABASE_CONNECTION);
        return $ERROR_DATABASE_CONNECTION;
    }

    global $ERROR_CANCELLATION, $ERROR_CANCELLATION_NOT_FOUND, $INFO_CANCELLATION_CANCELED, $ERROR_MAIL_NOT_SENT;

    /* prepare values */
    $reservation_id = clean_string($fields['id']);
    $email = clean_string($fields['email']);

    /* check if reservation exists and is valid */
    $sql = $conn->prepare("SELECT COUNT(*) AS amount FROM reservations WHERE reservation_id = ? AND email = ? AND cancelled = 0");
    $sql->bind_param('ss', $reservation_id, $email);
    $sql->execute();
    $result = $sql->get_result();
    $amount = $result->fetch_assoc()["amount"];

    /* if reservation does not exist it might be already cancelled */
    if ($amount === null || (int)$amount === 0) {
        return $ERROR_CANCELLATION_NOT_FOUND;
    }

    /* cancel reservation */
    $sql = $conn->prepare("UPDATE reservations SET cancelled = TRUE WHERE reservation_id = ?");
    $sql->bind_param('s', $reservation_id);
    if ($sql->execute()) {
        if ($send_email) {
            $send_mail_status = send_cancellation_email($reservation_id, $email);
            if ($send_mail_status === FALSE) {
                error_log($ERROR_MAIL_NOT_SENT);
                return $ERROR_MAIL_NOT_SENT;
            }
        }
        return $INFO_CANCELLATION_CANCELED;
    }

    error_log($ERROR_CANCELLATION);
    return $ERROR_CANCELLATION;
}

/**
 * Get blacklist from database.
 *
 * @param mysqli|null $conn
 * @return array
 */
function get_blacklist(?mysqli $conn): array
{
    global $ERROR_DATABASE_CONNECTION;

    if ($conn === null) {
        error_log($ERROR_DATABASE_CONNECTION);
        return [];
    }

    try {
        $sql = $conn->prepare("SELECT * FROM blacklist");
        $result_execute = $sql->execute();
        if ($result_execute === false) {
            return [];
        }
    } catch (Exception $e) {
        error_log($e);
        return [];
    }

    $result = $sql->get_result();
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

/**
 * Remove a bad person who really nice though e.g. Marcel Geiss.
 *
 * @param mysqli|null $conn
 * @param string $name
 * @param string $email
 * @return void
 */
function remove_bad_person(?mysqli $conn, string $name, string $email): void
{
    global $ERROR_DATABASE_CONNECTION, $ERROR_DATABASE_QUERY;

    if ($conn === null) {
        error_log($ERROR_DATABASE_CONNECTION);
        return;
    }

    $sql = $conn->prepare("DELETE FROM blacklist WHERE name = ? AND email = ?");
    $sql->bind_param('ss', $name, $email);
    if ($sql->execute()) {
        return;
    }
    error_log($ERROR_DATABASE_QUERY);
}

/**
 * Add a bad bad person e.g. Matthias Asche.
 * @param mysqli|null $conn
 * @param string $name
 * @param string $email
 * @param string $comment
 * @return void
 */
function add_bad_person(?mysqli $conn, string $name, string $email, string $comment): void
{
    global $ERROR_DATABASE_CONNECTION, $ERROR_DATABASE_QUERY;

    if ($conn === null) {
        error_log($ERROR_DATABASE_CONNECTION);
        return;
    }

    try {
        $sql = $conn->prepare("INSERT INTO blacklist (name, email, comment) VALUES (?, ?, ?)");
        $sql->bind_param('sss', $name, $email, $comment);
        if ($sql->execute()) {
            return;
        }
    } catch (Exception $e) {
        error_log($e);
    }
    error_log($ERROR_DATABASE_QUERY);
}

/**
 * Update kajak in the database.
 *
 * @param mysqli|null $conn
 * @param string $old_name
 * @param string $name
 * @param string $old_email
 * @param string $email
 * @param string $comment
 * @return void
 */
function update_bad_person(?mysqli $conn, string $name, string $email, string $comment, string $old_name, string $old_email): void
{
    global $ERROR_DATABASE_CONNECTION, $ERROR_DATABASE_QUERY, $ERROR_EXECUTION;

    if ($conn === null) {
        error_log($ERROR_DATABASE_CONNECTION);
        return;
    }

    /* add kajak to list of kajaks */
    try {
        $sql = $conn->prepare("
        UPDATE blacklist
        SET name = ?, email = ?, comment = ?
        WHERE name = ? AND email = ?
        ");

        if ($sql === FALSE) {
            error_log($ERROR_DATABASE_QUERY);
            return;
        }

        $sql->bind_param('sssss', $name, $email, $comment, $old_name, $old_email);
        if ($sql->execute()) {
            return;
        }
        error_log($ERROR_EXECUTION);
    } catch (Exception $e) {
        error_log($e);
        return;
    }
}
