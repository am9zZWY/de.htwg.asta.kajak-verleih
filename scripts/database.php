<?php
$servername = "mysql-service:3306";
$username = "root";
$password = "";
$dbname = "php-service";

function connect_to_database()
{
    global $servername, $username, $password, $dbname;

    // Create connection
    $conn = new mysqli($servername, "user", "password", "database");
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    return $conn;
}