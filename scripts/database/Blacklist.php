<?php

/**
 * Create the table for the blacklist.
 *
 * @param mysqli|null $conn
 *
 * @return void
 */
function add_blacklist_table(?mysqli $conn): void
{
    global $ERROR_TABLE_CREATION, $ERROR_DATABASE_CONNECTION, $ERROR_DATABASE_QUERY;

    if (!check_connection($conn)) {
        error('add_blacklist_table', $ERROR_DATABASE_CONNECTION);
        return;
    }

    $sql = $conn->prepare('
CREATE TABLE IF NOT EXISTS blacklist
(
    
    name             VARCHAR(40)     NOT NULL,
    email            VARCHAR(50)     NOT NULL,
    comment          VARCHAR(255)    NOT NULL,
    PRIMARY KEY(name, email)
)');

    if ($sql === FALSE) {
        error('add_blacklist_table', $ERROR_DATABASE_QUERY);
        return;
    }

    if ($sql->execute()) {
        return;
    }
    error('add_blacklist_table', $ERROR_TABLE_CREATION);
}


/**
 * Get blacklist from database.
 *
 * @param mysqli|null $conn
 *
 * @return array
 */
function get_blacklist(?mysqli $conn): array
{
    global $ERROR_DATABASE_CONNECTION, $ERROR_DATABASE_QUERY;

    if (!check_connection($conn)) {
        return [];
    }

    try {
        $sql = $conn->prepare('SELECT * FROM blacklist');
        if ($sql === FALSE) {
            error('get_blacklist', $ERROR_DATABASE_QUERY);
            return [];
        }
        $result_execute = $sql->execute();
        if ($result_execute === FALSE) {
            return [];
        }
    } catch (Exception $exception) {
        error('get_blacklist', $exception);
        return [];
    }

    $result = $sql->get_result();
    return mysqli_fetch_all($result, MYSQLI_ASSOC);
}

/**
 * Get a list of all blocked emails.
 *
 * @param mysqli|null $conn
 *
 * @return array
 */
function get_blacklist_emails(?mysqli $conn): array
{
    global $ERROR_DATABASE_CONNECTION;

    if (!check_connection($conn)) {
        return [];
    }

    $blacklist = get_blacklist($conn);
    return array_map(static function ($entry) {
        return $entry['email'];
    }, $blacklist);
}

/**
 * Remove a bad person who really nice though e.g. Marcel Geiss.
 *
 * @param mysqli|null $conn
 * @param string      $name
 * @param string      $email
 *
 * @return void
 */
function remove_bad_person(?mysqli $conn, string $name, string $email): void
{
    global $ERROR_DATABASE_CONNECTION, $ERROR_DATABASE_QUERY;

    if (!check_connection($conn)) {
        error('remove_bad_person', $ERROR_DATABASE_CONNECTION);
        return;
    }

    $sql = $conn->prepare('DELETE FROM blacklist WHERE name = ? AND email = ?');
    if ($sql === FALSE) {
        error('remove_bad_person', $ERROR_DATABASE_QUERY);
        return;
    }
    $sql->bind_param('ss', $name, $email);
    if ($sql->execute()) {
        return;
    }
    error('remove_bad_person', $ERROR_DATABASE_QUERY);
}

/**
 * Add a bad bad person e.g. Matthias Asche.
 *
 * @param mysqli|null $conn
 * @param string      $name
 * @param string      $email
 * @param string      $comment
 *
 * @return void
 */
function add_bad_person(?mysqli $conn, string $name, string $email, string $comment): void
{
    global $ERROR_DATABASE_CONNECTION, $ERROR_DATABASE_QUERY;

    if (!check_connection($conn)) {
        error('add_bad_person', $ERROR_DATABASE_CONNECTION);
        return;
    }

    try {
        $sql = $conn->prepare('INSERT INTO blacklist (name, email, comment) VALUES (?, ?, ?)');
        if ($sql === FALSE) {
            error('add_bad_person', $ERROR_DATABASE_QUERY);
        }
        $sql->bind_param('sss', $name, $email, $comment);
        if ($sql->execute()) {
            return;
        }
    } catch (Exception $e) {
        error('add_bad_person', $e);
    }
    error('add_bad_person', $ERROR_DATABASE_QUERY);
}

/**
 * Update kajak in the database.
 *
 * @param mysqli|null $conn
 * @param string      $old_name
 * @param string      $name
 * @param string      $old_email
 * @param string      $email
 * @param string      $comment
 *
 * @return void
 */
function update_bad_person(?mysqli $conn, string $name, string $email, string $comment, string $old_name, string $old_email): void
{
    global $ERROR_DATABASE_CONNECTION, $ERROR_DATABASE_QUERY, $ERROR_EXECUTION;

    if (!check_connection($conn)) {
        error('update_bad_person', $ERROR_DATABASE_CONNECTION);
        return;
    }

    /* add kajak to list of kajaks */
    try {
        $sql = $conn->prepare('
        UPDATE blacklist
        SET name = ?, email = ?, comment = ?
        WHERE name = ? AND email = ?
        ');

        if ($sql === FALSE) {
            error('update_bad_person', $ERROR_DATABASE_QUERY);
            return;
        }

        $sql->bind_param('sssss', $name, $email, $comment, $old_name, $old_email);
        if ($sql->execute()) {
            return;
        }
        error('update_bad_person', $ERROR_EXECUTION);
    } catch (Exception $e) {
        error('update_bad_person', $e);
        return;
    }
}
