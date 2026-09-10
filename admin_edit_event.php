<?php

session_start();
require_once "db.php";


/* =====================================
   ADMIN LOGIN CHECK
===================================== */

if (
    !isset($_SESSION["user_logged_in"]) ||
    $_SESSION["user_logged_in"] !== true ||
    !isset($_SESSION["user_id"]) ||
    !is_numeric($_SESSION["user_id"]) ||
    !isset($_SESSION["role"]) ||
    $_SESSION["role"] !== "admin"
) {
    header("Location: login.php");
    exit();
}

$event_id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if ($event_id === false || $event_id === null || $event_id <= 0) {
    header("Location: admin_events.php");
    exit();
}

$error = "";
$success = "";


/* =====================================
   LOAD EXISTING EVENT
===================================== */

$stmt = $conn->prepare(
    "SELECT event_id, event_name, description, event_date, event_time, location, image, status
     FROM events WHERE event_id = ?"
);
$stmt->bind_param("i", $event_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    header("Location: admin_events.php");
    exit();
}

$event = $result->fetch_assoc();
$stmt->close();

$event_name = $event["event_name"];
$description = $event["description"];
$event_date = $event["event_date"];
$event_time = $event["event_time"];
$location = $event["location"];
$current_image = $event["image"] ?? "";
$make_active = $event["status"] === "Active";


/* =====================================
   UPDATE EVENT
===================================== */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $event_name = trim($_POST["event_name"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $event_date = trim($_POST["event_date"] ?? "");
    $event_time = trim($_POST["event_time"] ?? "");
    $location = trim($_POST["location"] ?? "");
    $make_active = isset($_POST["make_active"]);

    $image_name = $current_image;


    /* =================================
       VALIDATE
    ================================= */

    if ($event_name === "") {

        $error = "Please enter an event name.";

    } elseif (strlen($event_name) > 150) {

        $error = "Event name must not exceed 150 characters.";

    } elseif ($event_date === "" || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $event_date)) {

        $error = "Please enter a valid event date.";

    } elseif ($event_time === "" || !preg_match('/^\d{2}:\d{2}$/', $event_time)) {

        $error = "Please enter a valid event time.";
    }


    /* =================================
       HANDLE NEW IMAGE (OPTIONAL REPLACE)
    ================================= */

    if (
        $error === "" &&
        isset($_FILES["event_image"]) &&
        $_FILES["event_image"]["error"] !== UPLOAD_ERR_NO_FILE
    ) {

        $file = $_FILES["event_image"];

        if ($file["error"] !== UPLOAD_ERR_OK) {

            $error = "There was a problem uploading the image.";

        } else {

            $allowed_types = ["image/jpeg", "image/png", "image/webp"];
            $file_type = mime_content_type($file["tmp_name"]);

            if (!in_array($file_type, $allowed_types)) {

                $error = "Only JPG, PNG, and WEBP images are allowed.";

            } else {

                $extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
                $new_image_name = uniqid("event_", true) . "." . $extension;

                $upload_directory = "event_images/";

                if (!is_dir($upload_directory)) {
                    mkdir($upload_directory, 0777, true);
                }

                if (move_uploaded_file($file["tmp_name"], $upload_directory . $new_image_name)) {

                    /* Delete the old image now that the new one is saved */

                    if (!empty($current_image) && file_exists($upload_directory . $current_image)) {
                        unlink($upload_directory . $current_image);
                    }

                    $image_name = $new_image_name;

                } else {

                    $error = "Failed to upload the event image.";
                }
            }
        }
    }


    /* =================================
       SAVE CHANGES
    ================================= */

    if ($error === "") {

        $status = $make_active ? "Active" : "Inactive";
        $location_value = $location !== "" ? $location : null;

        if ($make_active) {
            $conn->query("UPDATE events SET status = 'Inactive' WHERE event_id != $event_id");
        }

        $stmt = $conn->prepare(
            "UPDATE events
             SET event_name = ?, description = ?, event_date = ?, event_time = ?,
                 location = ?, image = ?, status = ?
             WHERE event_id = ?"
        );

        $stmt->bind_param(
            "sssssssi",
            $event_name,
            $description,
            $event_date,
            $event_time,
            $location_value,
            $image_name,
            $status,
            $event_id
        );

        if ($stmt->execute()) {

            $success = "Event updated successfully.";
            $current_image = $image_name;

        } else {

            $error = "Failed to update event: " . $stmt->error;
        }

        $stmt->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Event | Maturan's Art Cafe</title>

    <link rel="stylesheet" href="Css/style.css">
    <link rel="stylesheet" href="Css/admin.css">
    <link rel="stylesheet" href="Css/admin_dashboard.css">
    <link rel="stylesheet" href="Css/admin_login.css">
    <link rel="stylesheet" href="Css/admin_add_artwork.css">

</head>

<body class="admin-dashboard-page">

<div class="dash-layout">

    <?php
    $admin_active = "events";
    include "admin_sidebar.php";
    ?>

    <div class="dash-main">

        <main class="admin-content">

            <div class="admin-page-title">
                <h2>EDIT EVENT</h2>
                <p>Update the details for this event.</p>
            </div>

            <?php if ($error !== ""): ?>
                <div class="admin-message error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success !== ""): ?>
                <div class="admin-message success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <div class="admin-form-card">

                <?php if (!empty($current_image) && file_exists("event_images/" . $current_image)): ?>

                    <img
                        src="event_images/<?php echo htmlspecialchars($current_image); ?>"
                        alt="<?php echo htmlspecialchars($event_name); ?>"
                        class="product-current-image"
                    >

                <?php endif; ?>

                <form method="POST" enctype="multipart/form-data">

                    <div class="admin-form-group">
                        <label for="event_name">EVENT NAME</label>
                        <input
                            type="text"
                            id="event_name"
                            name="event_name"
                            value="<?php echo htmlspecialchars($event_name); ?>"
                            required
                        >
                    </div>

                    <div class="admin-form-group">
                        <label for="description">DESCRIPTION</label>
                        <textarea
                            id="description"
                            name="description"
                            rows="4"
                        ><?php echo htmlspecialchars($description ?? ""); ?></textarea>
                    </div>

                    <div class="admin-form-group">
                        <label for="event_date">DATE</label>
                        <input type="date" id="event_date" name="event_date" value="<?php echo htmlspecialchars($event_date); ?>" required>
                    </div>

                    <div class="admin-form-group">
                        <label for="event_time">TIME</label>
                        <input type="time" id="event_time" name="event_time" value="<?php echo htmlspecialchars($event_time); ?>" required>
                    </div>

                    <div class="admin-form-group">
                        <label for="location">LOCATION</label>
                        <input
                            type="text"
                            id="location"
                            name="location"
                            value="<?php echo htmlspecialchars($location ?? ""); ?>"
                        >
                    </div>

                    <div class="admin-form-group">
                        <label for="event_image">REPLACE IMAGE</label>
                        <input
                            type="file"
                            id="event_image"
                            name="event_image"
                            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                        >
                        <small class="admin-file-help">Leave empty to keep the current image.</small>
                    </div>

                    <div class="admin-form-group admin-checkbox-group">
                        <label>
                            <input type="checkbox" name="make_active" <?php echo $make_active ? "checked" : ""; ?>>
                            Make this the active event
                        </label>
                        <small class="admin-file-help">Only one event can be active at a time. This is the event artists apply to join.</small>
                    </div>

                    <div class="admin-form-actions">
                        <a href="admin_events.php" class="admin-cancel-button">CANCEL</a>
                        <button type="submit" class="admin-login-button">SAVE CHANGES</button>
                    </div>

                </form>

            </div>

        </main>

    </div>

</div>

</body>

</html>