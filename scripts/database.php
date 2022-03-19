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
        date DATE NOT NULL,
        from TIME NOT NULL,
        to TIME NOT NULL,
        single_kajak NUMERIC NOT NULL,
        double_kajak NUMERIC NOT NULL,
        CONSTRAINT NAME_CHECK   CHECK(REGEXP_LIKE (name, '^[A-Za-z ]+'))
    )";
    $conn->query($sql);
}

function drop_table($conn)
{
    $sql = "DROP TABLE reservations";
    $conn->query($sql);
}

function check_if_reservation_available($conn, $date, $time): bool
{
    $sql = "SELECT * FROM reservations WHERE date = '$date' AND time = '$time'";
    $result = $conn->query($sql);

    return $result->num_rows <= 0;
}

function insert_reservation($conn, $name, $email, $phone, $date, $timeslots, $kajaks)
{
    $sql = "INSERT INTO reservations (name, email, phone, date, from, to, single_kajak, double_kajak)
    VALUES ('$name', '$email', '$phone', '$date', '$timeslots[0]', '$timeslots[1]', '$kajaks[0]', '$kajaks[1]')";

    if ($conn->query($sql) === TRUE) {
        echo "New reservation created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
