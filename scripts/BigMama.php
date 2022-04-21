<?php

class BigMama
{

    private string $xml;
    private mysqli $conn;

    /**
     * Constructor for Config class.
     */
    public function __construct()
    {
        /* read in from config.xml as string */
        $loaded_xml = file_get_contents('config.xml');
        if ($loaded_xml === false) {
            /* if it fails return */
            return;
        }

        /* save xml string to variable */
        $this->xml = $loaded_xml;

        $conn = $this->connect_to_database();
        if ($conn === null) {
            /* if it fails return */
            return;
        }

        $this->conn = $conn;
    }

    /**
     * Create connection to mysql database.
     * Returns connection object if successful.
     *
     * @return mysqli|null
     */
    private function connect_to_database(): mysqli|null
    {
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
                return null;
            }
        } catch (Exception) {
            return null;
        }

        return $conn;
    }

    /**
     * Get object with amount of kajaks
     * @return array
     */
    public function getAmountKajaks(): array
    {
        $kajaks = $this->getKajaks(true);
        $amount = array();
        foreach ($kajaks as $kajak) {
            $amount[$kajak->kind] = (int)$kajak->amount;
        }
        return $amount;
    }

    /**
     * Return kajaks as JSON object or as list.
     * @param bool $asList
     * @return array
     */
    public function getKajaks(bool $asList = false): array
    {
        /* create either array or object */
        $kajaks = $asList ? array() : new stdClass();

        $xml = $this->getSimpleXMLConfig();
        if ($xml === null) {
            return $kajaks;
        }

        /* prepare list of prices in which every price is an object with some props */
        foreach ($xml->kajaks->children() as $child) {
            $kajak = new stdClass();
            $name = (string)$child->attributes()->name;
            $kajak->name = $name;

            /* get all props and put them as attributes into object */
            $props = $child->xpath('.//prop');
            foreach ($props as $prop) {
                $kajak->{(string)$prop->attributes()->name} = (string)$prop;
            }
            if ($asList) {
                $kajaks [] = $kajak;
            } else {
                $kajaks->$name = $kajak;
            }
        }

        return $kajaks;
    }

    /**
     * Get xml string as SimpleXMLElement.
     * @return SimpleXMLElement|null
     */
    private function getSimpleXMLConfig(): ?SimpleXMLElement
    {
        try {
            return new SimpleXMLElement($this->xml);
        } catch (Exception) {
            return null;
        }
    }

    public function kajakToString($kajak): string
    {
        return "Name: " . $kajak->name . "<br>" .
            "Sitze: " . $kajak->seats . "<br>" .
            "Anzahl: " . $kajak->amount . "<br>" .
            "------------------------------" . "<br>";
    }

    public function pricesToString($prices): string
    {
        return "Timeslot: " . $prices->name . "<br>" .
            "Preis: " . $prices->price . "<br>" .
            "------------------------------" . "<br>";
    }

    public function timeslotToString($timeslots): string
    {
        return "Timeslot: " . $timeslots->name . "<br>" .
            "Start: " . $timeslots->start . "<br>" .
            "Ende: " . $timeslots->ende . "<br>" .
            "------------------------------" . "<br>";
    }

    /**
     * Get formatted timeslots.
     * @return array
     */
    public function getFormattedTimeslots(): array
    {
        return $this->formatTimeslot($this->getTimeslots());
    }

    /**
     * Formats multiple timeslots from e.g. [9:00, 13:00] to 9:00 - 13:00.
     * @param $timeslots
     * @return array
     */
    private function formatTimeslot($timeslots): array
    {
        return array_map(static function ($timeslot) {
            return date('H:i', strtotime($timeslot->start)) . " - " . date('H:i', strtotime($timeslot->end));
        }, $timeslots);
    }

    /**
     * Get all timeslots from config.
     * @param bool $asMatrix
     * @return array
     */
    #[Pure] public function getTimeslots(bool $asMatrix = false): array
    {
        $timeslots = array();
        $xml = $this->getSimpleXMLConfig();
        if ($xml === null) {
            return $timeslots;
        }

        foreach ($xml->timeslots->children() as $child) {
            $start = (string)$child->start;
            $end = (string)$child->ende;

            if ($asMatrix === true) {
                $timeslot = array();
                $timeslot[0] = $start;
                $timeslot[1] = $end;
            } else {
                $timeslot = new stdClass();
                $timeslot->start = $start;
                $timeslot->end = $end;
            }

            $timeslots[] = $timeslot;
        }
        return $timeslots;
    }

    /**
     * Returns the next max_days weekdays as a list of strings.
     * @return array<string>
     */
    public function getFormattedDays(): array
    {
        $config = $this->getDays();
        $min_days = $this->min_days;
        $max_days = $this->max_days;
        $weekdays = array("Sonntag", "Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag");

        /* create starting date */
        $date = date_create();
        if ($date === false) {
            return [''];
        }
        /* add min_days to starting date */
        date_add($date, new DateInterval("P${min_days}D"));

        /* create array with all weekdays */
        $days = array();
        for ($i = 0; $i < $max_days; $i++) {
            $weekday = (int)$date->format('w');
            /* ignore saturday and sunday */
            if ($weekday !== 0 && $weekday !== 6) {
                $days[$i] = array($weekdays[$weekday] . ' ' . $date->format('d.m.Y'), $date->format('Y-m-d'));
            }
            date_add($date, new DateInterval("P1D"));
        }
        return $days;
    }

    /**
     * Get config for days.
     * @return stdClass
     */
    #[Pure] public function getDays(): stdClass
    {
        $day_config = new stdClass();
        $xml = $this->getSimpleXMLConfig();
        if ($xml === null) {
            return $day_config;
        }

        $days = (array)$xml->days;
        $day_config->min_days = (int)$days["min"];
        $day_config->max_days = (int)$days["max"];
        return $day_config;
    }

    /**
     * Create the table for reservations.
     *
     * @return string|bool
     */
    function add_reservation_table(): string|bool
    {
        global $ERROR_DATABASE_CONNECTION, $ERROR_DATABASE_QUERY;
        $sql = $this->conn->prepare("
CREATE TABLE IF NOT EXISTS reservations
(
    reservation_id   INT             NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name             VARCHAR(30)     NOT NULL,
    email            VARCHAR(50)     NOT NULL,
    phone            VARCHAR(20)     NOT NULL,
    address          VARCHAR(200)    NOT NULL,
    date             DATE            NOT NULL,
    reservation_date DATE            NOT NULL,
    from_time        TIME            NOT NULL,
    to_time          TIME            NOT NULL,    
    price            NUMERIC         NOT NULL DEFAULT 0,
    archived         BOOLEAN         NOT NULL DEFAULT FALSE,
    cancelled        BOOLEAN         NOT NULL DEFAULT FALSE,
    CONSTRAINT NAME_CHECK CHECK (REGEXP_LIKE(name, '^[A-ZäÄöÖüÜßa-z]+ [A-ZäÄöÖüÜßa-z]+$'))
)");

        if ($sql === false) {
            return $ERROR_DATABASE_QUERY;
        }

        return $sql->execute();
    }

    /**
     * Create table for kajaks.
     *
     * @return string|bool
     */
    function add_kajak_table(): string|bool
    {
        global $ERROR_DATABASE_QUERY;
        $sql = $this->conn->prepare("
CREATE TABLE IF NOT EXISTS kajaks
(
    kajak_name             VARCHAR(30)     NOT NULL PRIMARY KEY,
    kind             VARCHAR(30)     NOT NULL,
    seats            INT             NOT NULL DEFAULT 0,
    available        BOOLEAN         NOT NULL DEFAULT TRUE,
    comment          VARCHAR(200)    NOT NULL DEFAULT ''
)");

        if ($sql === false) {
            return $ERROR_DATABASE_QUERY;
        }

        return $sql->execute();
    }

    /**
     * Create table for kajak reservations.
     *
     * @param mysqli|null $this- >conn
     * @return string|bool
     */
    function add_reservation_kajak_table(): string|bool
    {
        $sql = $this->conn->prepare("
CREATE TABLE IF NOT EXISTS kajak_reservation
(
    reservation_id   INT             NOT NULL,
    kajak_name       VARCHAR(30)     NOT NULL,
    PRIMARY KEY(reservation_id, kajak_name)
)");

        if ($sql === false) {
            return $ERROR_DATABASE_QUERY;
        }

        return $sql->execute();
    }

    /**
     * Add a kajak to the database.
     *
     * @param string $name
     * @param string $kind
     * @param int $amount_seats
     * @return string|bool
     */
    function add_kajak(string $name, string $kind, int $amount_seats): string|bool
    {
        /* get all kajaks and check if the kind is valid */
        $kajaks = $this->getKajaks(true);
        $kinds = array_unique(array_map(static function ($kajak) {
            return $kajak->kind;
        }, $kajaks));
        if (!in_array($kind, $kinds, true)) {
            return $ERROR_TYPE_NOT_IN_CONFIG;
        }

        /* add kajak to list of kajaks */
        try {
            $sql = $this->conn->prepare("
        INSERT INTO kajaks (kajak_name, kind, seats)
            VALUES (?, ?, ?);
        ");
            $sql->bind_param('sss', $name, $kind, $amount_seats);
            $result_execute = $sql->execute();
            return $result_execute !== false;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Sets kajak to unavailable.
     *
     * @param string $name
     * @return string|bool
     */
    function disable_kajak(string $name): string|bool
    {
        /* set available to 0 */
        $sql = $this->conn->prepare("UPDATE kajaks SET available = FALSE WHERE kajak_name = ?");
        $sql->bind_param('s', $name);
        return $sql->execute();
    }

    /**
     * Sets kajak to available.
     *
     * @param string $name
     * @return string|bool
     */
    function enable_kajak(string $name): string|bool
    {
        /* set available to 0 */
        $sql = $this->conn->prepare("UPDATE kajaks SET available = TRUE WHERE kajak_name = ?");
        $sql->bind_param('s', $name);
        return $sql->execute();
    }

    /**
     * Get all kajak names.
     * @return array|bool
     */
    function get_kajaks_kinds(): array|bool
    {
        try {
            $sql = $this->conn->prepare("SELECT DISTINCT(kind) FROM kajaks");
            $result_execute = $sql->execute();
            if ($result_execute === false) {
                return [];
            }
        } catch (Exception) {
            return [];
        }

        $result = $sql->get_result();
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    /**
     * Get all reservations from database.
     *
     * @return array<string>
     */
    function get_reservations(): array
    {
        try {
            $sql = $this->conn->prepare("SELECT * FROM reservations WHERE date >=current_Date() ORDER BY Date;");
            $result_execute = $sql->execute();
            if ($result_execute === false) {
                return [];
            }
        } catch (Exception) {
            return [];
        }

        $result = $sql->get_result();
        return mysqli_fetch_all($result, MYSQLI_ASSOC);
    }

    /**
     * USE WITH CAUTION!
     * USED BY ADMIN.
     *
     * Drops all tables.
     *
     * @param mysqli $this- >conn
     * @return void
     */
    function drop_all_tables(): void
    {
        $sql = $this->conn->prepare("DROP TABLE reservations");
        $sql->execute();
        $sql = $this->conn->prepare("DROP TABLE kajaks");
        $sql->execute();
        $sql = $this->conn->prepare("DROP TABLE kajak_reservation");
        $sql->execute();
    }

    /**
     * Returns the amount of kajaks of a kajak type.
     *
     * @param string $date
     * @param array<string> $timeslot
     * @param string $kajak_kind
     * @param int $requested_amount
     * @return bool|array
     */
    function get_available_kajaks(string $date, array $timeslot, string $kajak_kind, int $requested_amount): bool|array
    {
        /* if 0 kajaks where requested, they are available */
        if ($requested_amount === 0) {
            return true;
        }

        /* convert date to DateTime to be able to subtract one second */
        try {
            $timeslot[1] = new DateTime($timeslot[1]);
        } catch (Exception) {
            return false;
        }

        /* it is important to exclude the current time from the next timeslot */
        $timeslot[1]->modify("-1 second");
        $timeslot[1] = $timeslot[1]->format("H:i:s");

        $timeslots = array((string)$timeslot[0], $timeslot[1]);

        /* select all the kajak names of a type that are available in the requested timeslot */
        $sql = $this->conn->prepare("
        SELECT kajak_name, seats
FROM kajaks
WHERE kajak_name NOT IN (SELECT kajak_reservation.kajak_name
                         FROM kajak_reservation
                                  INNER JOIN reservations
                                             ON reservations.reservation_id = kajak_reservation.reservation_id
                         WHERE reservations.date = ?
                           AND (reservations.from_time BETWEEN ? AND ?
                             OR reservations.to_time BETWEEN ? AND ?))
  AND kajaks.kind = ? AND kajaks.available = 1");
        $sql->bind_param('ssssss', $date, $timeslots[0], $timeslots[1], $timeslots[0], $timeslots[1], $kajak_kind);

        $sql->execute();
        $result = $sql->get_result();

        /* fetch all names of available kajaks */
        $fetched_kajaks = mysqli_fetch_all($result, MYSQLI_ASSOC);

        /* if requested amount exceeds the available kajaks then return false */
        if (count($fetched_kajaks) < $requested_amount) {
            return false;
        }

        /* return so much names as the user requested */
        return array_slice($fetched_kajaks, 0, $requested_amount);
    }

    /**
     * Insert reservation into database.
     *
     * @param string $name
     * @param string $email
     * @param string $phone
     * @param string $date
     * @param array<string> $timeslot
     * @param array<string> $kajak_names
     * @param int $price
     * @return bool|string
     */
    function insert_reservation(string $name, string $email, string $phone, string $date, array $timeslot, array $kajak_names, int $price): bool|string
    {
        $reservation_date = date('Y-m-d');
        $address = '';

        try {
            $sql = $this->conn->prepare("
INSERT INTO reservations (name, email, phone, date, address, reservation_date, from_time, to_time, price)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?);
");
            $sql->bind_param('sssssssss', $name, $email, $phone, $date, $address, $reservation_date, $timeslot[0], $timeslot[1], $price);
            $result_execute = $sql->execute();
            if ($result_execute === false) {
                return false;
            }

            /* get the id of the reservation */
            $sql = $this->conn->prepare("SELECT LAST_INSERT_ID() as id");
            $sql->execute();
            $result = $sql->get_result();
            $reservation_id = $result->fetch_assoc()["id"];

            /* if reservation id is null it failed */
            if ($reservation_id === null) {
                return false;
            }

            /* assign each kajak the reservation id */
            foreach ($kajak_names as $kajak_name) {
                $sql = $this->conn->prepare("
INSERT INTO kajak_reservation (kajak_name, reservation_id)
    VALUES (?, ?);
");
                $sql->bind_param('ss', $kajak_name, $reservation_id);
                $result_execute = $sql->execute();
                if ($result_execute === false) {
                    return false;
                }
            }

            return $reservation_id;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Reservate a kajak.
     *
     * @param array $fields
     * @param bool $send_email
     * @return true | string
     */
    function reservate_kajak(array $fields, bool $send_email = false): bool|string
    {
        global $ERROR_RESERVATION, $ERROR_RESERVATION_KAJAK_NOT_AVAILABLE, $ERROR_RESERVATION_KAJAK_NOT_SELECTED, $ERROR_RESERVATION_TIMESLOT_NOT_SELECTED, $ERROR_SUCCESS_BUT_MAIL_NOT_SENT;

        $name = clean_string($fields["name"]);
        $surname = clean_string($fields["surname"]);
        $fullname = $name . ' ' . $surname;
        $email = clean_string($fields['email']);
        $phone = clean_string($fields['phone']);
        $date = clean_string($fields['date']);

        $timeslots = clean_array($fields['timeslots'] ?? []);
        $amount_timeslots = count($timeslots);

        /* check if timeslot is selected */
        if (empty($timeslots)) {
            return $ERROR_RESERVATION_TIMESLOT_NOT_SELECTED;
        }

        /* prepare timeslot */
        global $config_timeslots;
        $min_time_index = $timeslots[0];
        $max_time_index = end($timeslots);
        $min_time = $config_timeslots[$min_time_index][0];
        $max_time = $config_timeslots[$max_time_index][1];
        $timeslots = array($min_time, $max_time);

        /* get all kajak kinds */
        $kajak_kinds = get_kajaks_kinds($this->conn);

        /* check if more than 0 kajaks where selected */
        $amount_kajaks = array_map(static function ($kajak_kind) {
            $kajak_kind = $kajak_kind["kind"];
            if (!isset($_POST[$kajak_kind])) {
                return 0;
            }
            return (int)clean_string($_POST[$kajak_kind]);
        }, $kajak_kinds);
        $sum_kajaks = array_sum($amount_kajaks);

        /* throw error if no kajak was selected */
        if ($sum_kajaks === 0) {
            return $ERROR_RESERVATION_KAJAK_NOT_SELECTED;
        }

        /* check for each kind, if it is available */
        $reserved_kajaks = array();
        foreach ($kajak_kinds as $kajak_kind) {
            $kajak_kind = $kajak_kind["kind"];
            $requested_amount = !isset($_POST[$kajak_kind]) ? 0 : (int)clean_string($_POST[$kajak_kind]);
            /* skip 0 requested kajaks */
            if ($requested_amount === 0) {
                break;
            }
            $available_kajaks = get_available_kajaks($this->conn, $date, $timeslots, $kajak_kind, $requested_amount);
            if ($available_kajaks === false) {
                return $ERROR_RESERVATION_KAJAK_NOT_AVAILABLE;
            }

            /* skip 0 requested kajaks */
            if ($available_kajaks === true) {
                break;
            }

            /* this will be a matrix which is then flattened */
            $reserved_kajaks[] = $available_kajaks;
        }

        /* flatten array */
        $reserved_kajaks = array_merge(...$reserved_kajaks);

        /* calculate price */
        $price = $this->calculatePrice($amount_timeslots, $sum_kajaks);

        /* insert reservation into database and get reservation_id back */
        $kajak_names = array_map(static function ($available_kajak) {
            return $available_kajak["kajak_name"];
        }, $reserved_kajaks);
        $reservation_id = insert_reservation($this->conn, $fullname, $email, $phone, $date, $timeslots, $kajak_names, $price);
        if ($reservation_id === false) {
            return $ERROR_RESERVATION;
        }

        /* send email */
        if ($send_email) {
            $send_mail_status = send_reservation_email($reservation_id, $name, $email, $reserved_kajaks, $timeslots, $date, $price);
            if ($send_mail_status === false) {
                return $ERROR_SUCCESS_BUT_MAIL_NOT_SENT;
            }
        }
        return true;
    }

    /**
     * Calculate price depending on timeslots and amount of kajaks.
     * @param int $amount_timeslots
     * @param int $amount_kajaks
     * @return int
     */
    public function calculatePrice(int $amount_timeslots, int $amount_kajaks): int
    {
        global $config_timeslots;

        if ($amount_timeslots === 0 || $amount_kajaks === 0) {
            return 0;
        }

        /* prepare prices */
        $prices = $this->getPrices(true);

        /* calculate all static prices e.g. prices that don't depend on a variable */
        $static_prices = array_sum(
            array_map(static function ($price) {
                return (int)$price->value;
            },
                array_filter($prices, static function ($price) {
                    return !property_exists($price, 'dependOn');
                })));

        /* calculate all prices that are per kajak */
        $perKajak_prices = array_sum(
            array_map(static function ($price) {
                return (int)$price->value;
            },
                array_filter($prices, static function ($price) {
                    return property_exists($price, 'dependOn') && $price->dependOn === 'kajak';
                })));

        /* calculate price */
        foreach ($prices as $price) {
            if (property_exists($price, 'amountTimeslots') &&
                ((int)$price->amountTimeslots === $amount_timeslots ||
                    ($price->amountTimeslots === 'all' && $amount_timeslots === count($config_timeslots)))) {
                return $amount_kajaks * ((int)$price->value + $perKajak_prices) + $static_prices;
            }
        }

        return 0;
    }

    /**
     * Return prices as JSON object like
     * ```json
     * "single": {
     *   "name": "single",
     *   "price": "5"
     * }, ...
     * ```
     * or as list like
     * ```json
     * [{
     *   "name": "single",
     *   "price": "5"
     * }, ...]
     * ```
     *
     * @param bool $asList
     * @return stdClass|array
     */
    public function getPrices(bool $asList = false): stdClass|array
    {
        /* create either array or object */
        $prices = $asList ? array() : new stdClass();

        $xml = $this->getSimpleXMLConfig();
        if ($xml === null) {
            return $prices;
        }

        /* prepare list of prices in which every price is an object with some props */
        foreach ($xml->prices->children() as $child) {
            $price = new stdClass();
            $name = (string)$child->attributes()->name;
            $price->name = $name;

            /* get all props and put them as attributes into object */
            $props = $child->xpath('.//prop');
            foreach ($props as $prop) {
                $price->{(string)$prop->attributes()->name} = (string)$prop;
            }

            /* decide between array or object */
            if ($asList) {
                $prices[] = $price;
            } else {
                $prices->$name = $price;
            }
        }
        return $prices;
    }

    /**
     * Archive reservations by id.
     * USED BY ADMIN.
     *
     * @param array<string> $ids
     * @return void
     */
    function archive_reservation(array $ids): void
    {
        global $ERROR_DATABASE_CONNECTION;

        if ($this->conn === null) {
            echo $ERROR_DATABASE_CONNECTION;
            return;
        }

        $sql = "UPDATE reservations SET archived = TRUE WHERE reservations.reservation_id IN (" . implode(',', $ids) . ")";
        $this->conn->query($sql);
    }

    /**
     * Cancel reservation by id.
     *
     * @param array<string> $fields
     * @param bool $send_email
     * @return string
     */
    function cancel_reservation(array $fields, bool $send_email = false): string
    {
        global $ERROR_CANCELLATION, $ERROR_CANCELLATION_NOT_FOUND, $INFO_CANCELLATION_CANCELED, $ERROR_MAIL_NOT_SENT;

        /* prepare values */
        $reservation_id = clean_string($fields['id']);
        $email = clean_string($fields['email']);

        /* check if reservation exists and is valid */
        $sql = $this->conn->prepare("SELECT COUNT(*) as amount FROM reservations WHERE reservation_id = ? AND email = ? AND cancelled = 0 AND archived = 0");
        $sql->bind_param('ss', $reservation_id, $email);
        $sql->execute();
        $result = $sql->get_result();
        $amount = $result->fetch_assoc()["amount"];

        /* if reservation does not exist it might be already cancelled */
        if ($amount === null || (int)$amount === 0) {
            return $ERROR_CANCELLATION_NOT_FOUND;
        }

        /* cancel reservation */
        $sql = $this->conn->prepare("UPDATE reservations SET cancelled = TRUE WHERE reservation_id = ?");
        $sql->bind_param('s', $reservation_id);
        if ($sql->execute()) {
            if ($send_email) {
                $send_mail_status = send_cancellation_email($reservation_id, $email);
                if ($send_mail_status === false) {
                    return $ERROR_MAIL_NOT_SENT;
                }
            }
            return $INFO_CANCELLATION_CANCELED;
        }

        return $ERROR_CANCELLATION;
    }
}