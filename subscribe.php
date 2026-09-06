<?php

require_once "db.php";

header("Content-Type: application/json");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    echo json_encode([
        "status" => "error",
        "message" => "Invalid request."
    ]);

    exit();
}


$email = trim($_POST["email"] ?? "");


/* CHECK EMAIL */

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
     WHERE email = ?"
);

$check->bind_param("s", $email);
$check->execute();
$check->store_result();


if ($check->num_rows > 0) {

    $check->close();
    $conn->close();

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

$stmt->bind_param("s", $email);


if ($stmt->execute()) {

    echo json_encode([
        "status" => "success",
        "message" => "Successfully subscribed!"
    ]);

} else {

    echo json_encode([
        "status" => "error",
        "message" => "Something went wrong. Please try again."
    ]);

}


$stmt->close();
$conn->close();

?>