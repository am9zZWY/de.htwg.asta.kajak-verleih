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

function check_if_available($conn, $date, $time)
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