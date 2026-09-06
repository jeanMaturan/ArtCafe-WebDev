<?php

/* =====================================
   DATABASE CONFIGURATION
===================================== */

$host = "localhost";
$username = "root";
$password = "";
$database = "maturans_art_cafe";


/* =====================================
   DATABASE CONNECTION
===================================== */

$conn = new mysqli(
    $host,
    $username,
    $password,
    $database
);


/* =====================================
   CONNECTION ERROR
===================================== */

if ($conn->connect_errno) {

    error_log(
        "Database connection failed: " .
        $conn->connect_error
    );

    die("Unable to connect to the database. Please try again later.");
}


/* =====================================
   CHARACTER SET
===================================== */

if (!$conn->set_charset("utf8mb4")) {

    error_log(
        "Failed to set database character set: " .
        $conn->error
    );

    $conn->close();

    die("Unable to initialize the database. Please try again later.");
}

?>

