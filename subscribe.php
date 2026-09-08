<?php

require_once "db.php";

header("Content-Type: application/json; charset=UTF-8");


/* ONLY ALLOW POST REQUESTS */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    http_response_code(405);

    echo json_encode([
        "status" => "error",
        "message" => "Invalid request."
    ]);

    exit();
}


/* GET AND CLEAN EMAIL */

$email = trim($_POST["email"] ?? "");


/* VALIDATE EMAIL */

if ($email === "") {

    echo json_encode([
        "status" => "error",
        "message" => "Please enter your email address."
    ]);

    exit();
}


if (strlen($email) > 150) {

    echo json_encode([
        "status" => "error",
        "message" => "Email address is too long."
    ]);

    exit();
}


if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    echo json_encode([
        "status" => "error",
        "message" => "Please enter a valid email address."
    ]);

    exit();
}


/* CHECK IF EMAIL ALREADY EXISTS */

$check = $conn->prepare(
    "SELECT subscriber_id
     FROM newsletter_subscribers
     WHERE email = ?
     LIMIT 1"
);


if (!$check) {

    http_response_code(500);

    echo json_encode([
        "status" => "error",
        "message" => "Something went wrong. Please try again."
    ]);

    exit();
}


$check->bind_param("s", $email);


if (!$check->execute()) {

    $check->close();

    http_response_code(500);

    echo json_encode([
        "status" => "error",
        "message" => "Something went wrong. Please try again."
    ]);

    exit();
}


$check->store_result();


if ($check->num_rows > 0) {

    $check->close();

    echo json_encode([
        "status" => "exists",
        "message" => "This email is already subscribed."
    ]);

    exit();
}


$check->close();


/* INSERT EMAIL */

$stmt = $conn->prepare(
    "INSERT INTO newsletter_subscribers (email)
     VALUES (?)"
);


if (!$stmt) {

    http_response_code(500);

    echo json_encode([
        "status" => "error",
        "message" => "Something went wrong. Please try again."
    ]);

    exit();
}


$stmt->bind_param("s", $email);


if ($stmt->execute()) {

    echo json_encode([
        "status" => "success",
        "message" => "Successfully subscribed!"
    ]);

} else {

    http_response_code(500);

    echo json_encode([
        "status" => "error",
        "message" => "Something went wrong. Please try again."
    ]);
}


$stmt->close();
$conn->close();

?>