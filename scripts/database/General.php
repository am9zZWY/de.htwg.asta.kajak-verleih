<?php

global $config;

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
        $connection = new mysqli($servername, $username, $password, $dbname);
        /* Check connection */
        if ($connection->connect_error) {
            error('connect_to_database', $ERROR_DATABASE_CONNECTION);
            return NULL;
        }
        // mysqli_report(MYSQLI_REPORT_OFF);
    } catch (Exception $exception) {
        error('connect_to_database', $exception);
        return NULL;
    }

    return $connection;
}

/**
 * Checks connection to database.
 *
 * @param mysqli|null $connection
 *
 * @return bool
 */
function check_connection(?mysqli $connection): bool
{
    global $ERROR_DATABASE_CONNECTION;
    if ($connection === NULL) {
        error('check_connection', $ERROR_DATABASE_CONNECTION);
        return FALSE;
    }
    return TRUE;
}

/**
 * Execute query and catch any errors that might occur.
 *
 * @param mysqli             $connection
 * @param string|mysqli_stmt $query
 * @param string             $fct_name
 * @param array|null         $params
 * @param bool               $check_connection
 *
 * @return false|mysqli_stmt
 */
function prep_exec_sql(mysqli $connection, $query, string $fct_name, array $params = NULL, bool $check_connection = TRUE)
{
    if ($check_connection && !check_connection($connection)) {
        return FALSE;
    }
    if (is_string($query)) {
        $sql_statement = $connection->prepare($query);

        if (!$sql_statement) {
            error($fct_name ?? 'execute_query', $connection->error);
            return FALSE;
        }

        if ($params !== NULL) {
            $types = '';
            $array = [];
            foreach ($params as $param) {
                $types .= is_int($param) ? 'i' : 's';
                $array[] = $param;
            }
            $sql_statement->bind_param($types, ...$array);
        }
    } else {
        /* query is already prepared and bound */
        $sql_statement = $query;
    }

    try {
        $result_execute = $sql_statement->execute();
    } catch (Exception $exception) {
        error($fct_name ?? 'execute_query', $exception);
        return FALSE;
    }

    return $result_execute ? $sql_statement : FALSE;
}

/**
 * USE WITH CAUTION!
 * USED BY ADMIN.
 *
 * Drops all tables.
 *
 * @param mysqli|null $connection
 * @param bool        $send_mail
 *
 * @return void
 */
function drop_all_tables(mysqli $connection, bool $send_mail = FALSE): void
{
    if (!check_connection($connection)) {
        return;
    }

    prep_exec_sql($connection, 'DROP TABLE IF EXISTS kajaks', 'drop_all_tables');
    prep_exec_sql($connection, 'DROP TABLE IF EXISTS reservations', 'drop_all_tables');
    prep_exec_sql($connection, 'DROP TABLE IF EXISTS kajak_reservation', 'drop_all_tables');
    prep_exec_sql($connection, 'DROP TABLE IF EXISTS blacklist', 'drop_all_tables');
    if ($send_mail) {
        send_mail('', 'Tabellen gelöscht', 'Tabellen gelöscht! Bitte überprüfen, ob diese Aktion gewollt war');
    }
}

/**
 * Calculate price.
 *
 * @param mysqli|null $connection
 * @param             $timeslots
 * @param             $amount_kajaks_per_kind
 *
 * @return int
 */
function calculate_price(mysqli $connection, $timeslots, $amount_kajaks_per_kind): int
{
    global $config;
    if (!check_connection($connection)) {
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

    $seats_per_kajak = $config->get_seats_per_kajak();
    $amount_seats = array_reduce($amount_kajaks_per_kind, static function ($carry, $kajak) use ($seats_per_kajak) {
        return $carry + ($seats_per_kajak[$kajak['kind']] * (int)$kajak['amount']);
    }, 0);

    /* max values when “all” is passed */
    $max_timeslots = count($config->get_timeslots());
    $kajaks = get_kajaks($connection, TRUE);
    $max_kajaks = count($kajaks);
    $max_seats = array_reduce($kajaks, static function ($carry, $kajak) use ($seats_per_kajak) {
        return $carry + $seats_per_kajak[$kajak['kind']];
    }, 0);

    /* prepare prices */
    $prices = $config->get_prices();
    $calculated_price = 0;
    foreach ($prices as $price) {
        $price_value = (int)$price['value'];

        $price_dependencies = $price['dependOn'];
        /* first check if requirements are met */
        $require_check = TRUE;
        foreach ($price_dependencies as $dependency) {
            $dep_name = $dependency['name'];
            $dep_amount = $dependency['amount'];

            if ($dep_amount === 'per') {
                continue;
            }

            $amount = 0;
            $max = 0;
            if ($dep_name === 'timeslot') {
                $amount = $amount_timeslots;
                $max = $max_timeslots;
            } elseif ($dep_name === 'seat') {
                $amount = $amount_seats;
                $max = $max_seats;
            } elseif ($dep_name === 'kajak') {
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
        foreach ($price_dependencies as $dependency) {
            $dep_name = $dependency['name'];
            $dep_amount = $dependency['amount'];

            if ($dep_amount !== 'per') {
                continue;
            }

            $amount = 0;
            if ($dep_name === 'timeslot') {
                $amount = $amount_timeslots;
            } elseif ($dep_name === 'seat') {
                $amount = $amount_seats;
            } elseif ($dep_name === 'kajak') {
                $amount = $amount_kajaks;
            }

            $calculated_price += $price_value * $amount;
        }
    }

    return $calculated_price;
}
