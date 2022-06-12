<?php

/**
 * Create the table for the blacklist.
 *
 * @param mysqli|null $connection
 *
 * @return void
 */
function add_blacklist_table(mysqli $connection): void
{
    /* create table and index to speed up searching */
    $query = '
CREATE TABLE IF NOT EXISTS blacklist
(
    
    name             VARCHAR(40)     NOT NULL,
    email            VARCHAR(50)     NOT NULL,
    comment          VARCHAR(255)    NOT NULL,
    PRIMARY KEY(name, email),
    UNIQUE INDEX index_blacklist_name_email (name, email)
)';
    prep_exec_sql($connection, $query, 'add_blacklist_table');
}


/**
 * Get blacklist from database.
 *
 * @param mysqli|null $connection
 *
 * @return array
 */
function get_blacklist(mysqli $connection): array
{
    $sql_ret = prep_exec_sql($connection, 'SELECT * FROM blacklist', 'get_blacklist');
    return !$sql_ret ? [] : mysqli_fetch_all($sql_ret->get_result(), MYSQLI_ASSOC);
}

/**
 * Get a list of all blocked emails.
 *
 * @param mysqli|null $connection
 *
 * @return array
 */
function get_blacklist_emails(mysqli $connection): array
{
    return array_map(static function ($entry) {
        return $entry['email'];
    }, get_blacklist($connection));
}

/**
 * Remove a bad person who really nice though e.g. Marcel Geiss.
 *
 * @param mysqli|null $connection
 * @param string      $name
 * @param string      $email
 *
 * @return void
 */
function remove_bad_person(mysqli $connection, string $name, string $email): void
{
    prep_exec_sql($connection, 'DELETE FROM blacklist WHERE name = ? AND email = ?', 'remove_bad_person', [$name, $email]);
}

/**
 * Add a bad, bad person e.g. Matthias Asche.
 *
 * @param mysqli|null $connection
 * @param string      $name
 * @param string      $email
 * @param string      $comment
 *
 * @return void
 */
function add_bad_person(mysqli $connection, string $name, string $email, string $comment): void
{
    prep_exec_sql($connection, 'INSERT INTO blacklist (name, email, comment) VALUES (?, ?, ?)', 'add_bad_person', [$name, $email, $comment]);
}

/**
 * Update kajak in the database.
 *
 * @param mysqli|null $connection
 * @param string      $old_name
 * @param string      $name
 * @param string      $old_email
 * @param string      $email
 * @param string      $comment
 *
 * @return void
 */
function update_bad_person(mysqli $connection, string $name, string $email, string $comment, string $old_name, string $old_email): void
{
    $query = '
        UPDATE blacklist
        SET name = ?, email = ?, comment = ?
        WHERE name = ? AND email = ?
        ';
    prep_exec_sql($connection, $query, 'update_bad_person', [$name, $email, $comment, $old_name, $old_email]);
}
