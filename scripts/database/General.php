<?php
/** @noinspection ForgottenDebugOutputInspection */

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
