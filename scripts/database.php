<?php
$servername = "mysql-test-service:3306";
$username = "user";
$password = "password";
$dbname = "db";

function connect_to_database()
{
    global $servername, $username, $password, $dbname;

    // Create connection
    $conn = new mysqli($servername, $username, $password, $dbname);
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    return $conn;
}

function prepare_reservation_table($conn)
{
    $sql = "CREATE TABLE IF NOT EXISTS reservations (
        id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(30) NOT NULL,
        email VARCHAR(50) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        data DATE NOT NULL
    )";
    $conn->query($sql);
}

function check_if_reservation_available($conn, $date, $time): bool
{
    $sql = "SELECT * FROM reservations WHERE date = '$date' AND time = '$time'";
    $result = $conn->query($sql);

    return $result->num_rows <= 0;
}

function insert_reservation($conn, $date, $time, $name, $email, $phone)
{
    $sql = "INSERT INTO reservations (date, time, name, email, phone) VALUES ('$date', '$time', '$name', '$email', '$phone')";
    $conn->query($sql);
}