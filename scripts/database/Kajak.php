<?php

/**
 * Create table for kajaks.
 *
 * @param mysqli|null $connection
 *
 * @return void
 */
function add_kajak_table(mysqli $connection): void
{
    $query = "
CREATE TABLE IF NOT EXISTS kajaks
(
    kajak_name       VARCHAR(30)     NOT NULL PRIMARY KEY,
    kind             VARCHAR(30)     NOT NULL,
    seats            INT             NOT NULL DEFAULT 0,
    available        BOOLEAN         NOT NULL DEFAULT TRUE,
    comment          VARCHAR(200)    NOT NULL DEFAULT '',
    UNIQUE INDEX index_available_kajak_name (available, kajak_name),
    UNIQUE INDEX index_kajak_name_seats (seats, kajak_name)
)";
    prep_exec_sql($connection, $query, 'add_kajak_table');
}


/**
 * Add a kajak to the database.
 *
 * @param mysqli|null $connection
 * @param string      $name
 * @param string      $kind
 * @param int         $seats
 *
 * @return void
 */
function add_kajak(mysqli $connection, string $name, string $kind, int $seats): void
{
    global $ERROR_TYPE_NOT_IN_CONFIG, $ERROR_TOO_MANY_SEATS, $config;

    if (!check_connection($connection)) {
        return;
    }

    /* get all kajaks and check if the kind is valid */
    $kinds = $config->get_kajak_kinds();
    if (!in_array($kind, $kinds, TRUE)) {
        error('add_kajak', $ERROR_TYPE_NOT_IN_CONFIG);
        return;
    }

    /* get seats per kajak and check if number does not exceed config. It can be less if one seat is e.g. damaged */
    $seats_per_kajak = $config->get_seats_per_kajak();
    if ($seats_per_kajak[$kind] < $seats) {
        error('add_kajak', $ERROR_TOO_MANY_SEATS);
        return;
    }
    prep_exec_sql($connection, 'INSERT INTO kajaks (kajak_name, kind, seats) VALUES (?, ?, ?)', 'add_kajak', [$name, $kind, $seats], FALSE);
}


/**
 * Update kajak in the database.
 *
 * @param mysqli|null $connection
 * @param string      $old_name
 * @param string      $name
 * @param string      $kind
 * @param int         $seats
 * @param int         $available
 * @param string      $comment
 *
 * @return void
 */
function update_kajak(mysqli $connection, string $old_name, string $name, string $kind, int $seats, int $available, string $comment): void
{
    global $ERROR_TYPE_NOT_IN_CONFIG, $ERROR_TOO_MANY_SEATS, $config;

    if (!check_connection($connection)) {
        return;
    }

    /* get all kajaks and check if the kind is valid */
    $kinds = $config->get_kajak_kinds();
    if (!in_array($kind, $kinds, TRUE)) {
        error('update_kajak', $ERROR_TYPE_NOT_IN_CONFIG);
        return;
    }

    /* get seats per kajak and check if number does not exceed config. It can be less if one seat is e.g. damaged */
    $seats_per_kajak = $config->get_seats_per_kajak();
    if ($seats_per_kajak[$kind] < $seats) {
        error('update_kajak', $ERROR_TOO_MANY_SEATS);
        return;
    }

    $query = '
        UPDATE kajaks
        SET kajak_name = ?, kind = ?, seats = ?, available = ?, comment = ?
        WHERE kajak_name = ?;
        ';
    prep_exec_sql($connection, $query, 'update_kajak', [$name, $kind, $seats, $available, $comment, $old_name], FALSE);
}

/**
 * Remove kajak from database by name.
 *
 * @param mysqli|null $connection
 * @param string      $kajak_name
 *
 * @return void
 */
function remove_kajak(mysqli $connection, string $kajak_name): void
{
    prep_exec_sql($connection, 'DELETE FROM kajaks WHERE kajak_name = ?', 'add_kajak', [$kajak_name]);
}

/**
 * Get all kajak.
 *
 * @param mysqli $connection
 * @param bool   $exclude_not_available
 *
 * @return array
 */
function get_kajaks(mysqli $connection, bool $exclude_not_available = FALSE): array
{
    if (!check_connection($connection)) {
        return [];
    }

    if ($exclude_not_available) {
        $sql_statement = 'SELECT * FROM kajaks WHERE available = 1 ORDER BY seats, kajak_name';
    } else {
        $sql_statement = 'SELECT * FROM kajaks ORDER BY seats, kajak_name';
    }
    $sql_ret = prep_exec_sql($connection, $sql_statement, 'get_kajaks');
    return $sql_ret === FALSE ? [] : mysqli_fetch_all($sql_ret->get_result(), MYSQLI_ASSOC);
}

/**
 * Get kajaks sorted by kinds.
 *
 * @return void
 */
function get_kajak_with_real_amount(mysqli $connection): array
{
    if (!check_connection($connection)) {
        return [];
    }

    global $config;
    $kajaks = $config->get_kajaks();
    $kajak_amounts = get_kajak_amounts($connection);
    $kajaks_with_amount = [];

    foreach ($kajaks as $kajak) {
        /* if kajak is not available, set amount to 0 */
        $kajak['amount'] = ($kajak_amounts[$kajak['kind']] ?? [])['amount'] ?? 0;
        $kajaks_with_amount[] = $kajak;
    }

    return $kajaks_with_amount;
}

/**
 * Get all kajak amounts by kind.
 *
 * @param mysqli|null $connection
 *
 * @return array
 */
function get_kajak_amounts(mysqli $connection): array
{
    if (!check_connection($connection)) {
        return [];
    }

    return array_reduce(get_kajaks($connection, TRUE), static function ($carry, $kajak) {
        $kajak_kind = $kajak['kind'];
        if (isset($carry[$kajak_kind])) {
            $carry[$kajak_kind]['amount']++;
        } else {
            $carry[$kajak_kind]['amount'] = 1;
        }
        return $carry;
    }, []);
}

/**
 * Get all kajak kinds.
 *
 * @param mysqli|null $connection
 *
 * @return array
 */
function get_kajak_kinds(mysqli $connection): array
{
    if (!check_connection($connection)) {
        return [];
    }

    return array_values(array_unique(array_map(static function ($kajak) {
        return $kajak['kind'];
    }, get_kajaks($connection))));
}