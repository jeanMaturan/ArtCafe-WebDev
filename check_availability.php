<?php

session_start();
require_once "db.php";
require_once "reservation_config.php";

header("Content-Type: application/json");


/* =========================
   MUST BE LOGGED IN
========================= */

if (
    !isset($_SESSION["user_logged_in"]) ||
    $_SESSION["user_logged_in"] !== true ||
    !isset($_SESSION["user_id"]) ||
    !is_numeric($_SESSION["user_id"])
) {
    http_response_code(401);
    echo json_encode(["error" => "Not logged in."]);
    exit();
}


/* =========================
   READ + VALIDATE INPUT
========================= */

$date = trim($_GET["date"] ?? "");
$time = trim($_GET["time"] ?? "");

if ($date === "" || $time === "") {
    http_response_code(400);
    echo json_encode(["error" => "Missing date or time."]);
    exit();
}

$valid_date =
    DateTime::createFromFormat("Y-m-d", $date) &&
    DateTime::createFromFormat("Y-m-d", $date)->format("Y-m-d") === $date;

if (!$valid_date) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid date."]);
    exit();
}

$valid_time =
    DateTime::createFromFormat("H:i", $time) ||
    DateTime::createFromFormat("H:i:s", $time);

if (!$valid_time) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid time."]);
    exit();
}


/* =========================
   READ + VALIDATE GUESTS (OPTIONAL)
========================= */

$guests_param = $_GET["guests"] ?? null;
$guests = null;

if ($guests_param !== null && $guests_param !== "") {

    $guests = filter_var($guests_param, FILTER_VALIDATE_INT);

    if ($guests === false || $guests < 1) {
        http_response_code(400);
        echo json_encode(["error" => "Invalid guest count."]);
        exit();
    }
}


/* =========================
   CLEAR OUT NO-SHOWS FIRST
   (so freed tables count as available)
========================= */

expire_stale_reservations($conn);


/* =========================
   COMPUTE TABLES LEFT
========================= */

$tables_used = count_tables_used_in_slot($conn, $date, $time);
$tables_left = max(0, MAX_TABLES - $tables_used);

$response = [
    "max_tables" => MAX_TABLES,
    "guests_per_table" => GUESTS_PER_TABLE,
    "tables_used" => $tables_used,
    "tables_left" => $tables_left,
];

if ($guests !== null) {

    $tables_needed = tables_needed_for_guests($guests);

    $response["tables_needed"] = $tables_needed;
    $response["fits"] = $tables_left >= $tables_needed;

} else {

    $response["fits"] = $tables_left > 0;
}

$response["full"] = !$response["fits"];

echo json_encode($response);