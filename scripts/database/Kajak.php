<?php
/** @noinspection ForgottenDebugOutputInspection */


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