<?php

session_start();
require_once "db.php";


/* =====================================
   ADMIN LOGIN CHECK
===================================== */

if (
    !isset($_SESSION["admin_logged_in"]) ||
    $_SESSION["admin_logged_in"] !== true
) {
    header("Location: admin_login.php");
    exit();
}

$success = "";
$error = "";

if (isset($_SESSION["flash_success"])) {
    $success = $_SESSION["flash_success"];
    unset($_SESSION["flash_success"]);
}
if (isset($_SESSION["flash_error"])) {
    $error = $_SESSION["flash_error"];
    unset($_SESSION["flash_error"]);
}


/* =====================================
   HANDLE ACTIONS
===================================== */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $event_id = filter_input(INPUT_POST, "event_id", FILTER_VALIDATE_INT);
    $action = trim($_POST["action"] ?? "");

    if ($event_id === false || $event_id === null || $event_id <= 0) {

        $error = "Invalid event.";

    } elseif ($action === "delete") {

        $img_stmt = $conn->prepare("SELECT image FROM events WHERE event_id = ?");
        $img_stmt->bind_param("i", $event_id);
        $img_stmt->execute();
        $event_row = $img_stmt->get_result()->fetch_assoc();
        $img_stmt->close();

        $stmt = $conn->prepare("DELETE FROM events WHERE event_id = ?");
        $stmt->bind_param("i", $event_id);

        if ($stmt->execute()) {

            if (!empty($event_row["image"]) && file_exists("event_images/" . $event_row["image"])) {
                unlink("event_images/" . $event_row["image"]);
            }

            $success = "Event deleted successfully.";

        } else {

            $error = "Something went wrong. Please try again.";
        }

        $stmt->close();

    } elseif ($action === "set_active") {

        /* Only one event can be Active at a time — the rest of the
           site (artist registration, admin dashboard) assumes this. */

        $conn->query("UPDATE events SET status = 'Inactive'");

        $stmt = $conn->prepare("UPDATE events SET status = 'Active' WHERE event_id = ?");
        $stmt->bind_param("i", $event_id);

        if ($stmt->execute()) {
            $success = "Event marked as active.";
        } else {
            $error = "Something went wrong. Please try again.";
        }

        $stmt->close();

    } elseif ($action === "set_inactive") {

        $stmt = $conn->prepare("UPDATE events SET status = 'Inactive' WHERE event_id = ?");
        $stmt->bind_param("i", $event_id);

        if ($stmt->execute()) {
            $success = "Event marked as inactive.";
        } else {
            $error = "Something went wrong. Please try again.";
        }

        $stmt->close();
    }

    $_SESSION["flash_success"] = $success;
    $_SESSION["flash_error"] = $error;

    header("Location: admin_events.php");
    exit();
}


/* =====================================
   GET ALL EVENTS
===================================== */

$events = [];

$result = $conn->query(
    "SELECT event_id, event_name, description, event_date, event_time, location, image, status
     FROM events
     ORDER BY event_date IS NULL, event_date ASC, event_id DESC"
);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $events[] = $row;
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Event Management | Maturan's Art Cafe</title>

    <link rel="stylesheet" href="Css/style.css">
    <link rel="stylesheet" href="Css/admin.css">
    <link rel="stylesheet" href="Css/admin_dashboard.css">
    <link rel="stylesheet" href="Css/admin_subscribers.css">
    <link rel="stylesheet" href="Css/admin_events.css">

</head>

<body class="admin-dashboard-page">

<div class="dash-layout">

    <?php
    $admin_active = "events";
    include "admin_sidebar.php";
    ?>

    <div class="dash-main">

        <main class="admin-content">

            <div class="admin-page-title product-page-title">

                <div>
                    <h2>EVENT MANAGEMENT</h2>
                    <p>Create and manage cafe events. Only one event can be Active at a time.</p>
                </div>

                <a href="admin_add_event.php" class="add-artwork-button product-add-button">
                    + ADD EVENT
                </a>

            </div>

            <?php if ($success !== ""): ?>
                <div class="admin-message success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <?php if ($error !== ""): ?>
                <div class="admin-message error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="admin-card">

                <?php if (count($events) === 0): ?>

                    <div class="clean-empty">
                        <p>No events yet. Click "Add Event" to create one.</p>
                    </div>

                <?php else: ?>

                    <table class="admin-table">

                        <thead>
                            <tr>
                                <th>EVENT</th>
                                <th>DATE</th>
                                <th>TIME</th>
                                <th>LOCATION</th>
                                <th>STATUS</th>
                                <th>ACTIONS</th>
                            </tr>
                        </thead>

                        <tbody>

                            <?php foreach ($events as $event): ?>

                                <tr>

                                    <td><?php echo htmlspecialchars($event["event_name"]); ?></td>

                                    <td>
                                        <?php
                                        echo !empty($event["event_date"])
                                            ? htmlspecialchars(date("M j, Y", strtotime($event["event_date"])))
                                            : "—";
                                        ?>
                                    </td>

                                    <td>
                                        <?php
                                        echo !empty($event["event_time"])
                                            ? htmlspecialchars(date("g:i A", strtotime($event["event_time"])))
                                            : "—";
                                        ?>
                                    </td>

                                    <td><?php echo htmlspecialchars($event["location"] ?? "—"); ?></td>

                                    <td>
                                        <span class="artwork-status <?php echo $event["status"] === "Active" ? "approved-status" : "rejected-status"; ?>">
                                            <?php echo strtoupper($event["status"]); ?>
                                        </span>
                                    </td>

                                    <td class="event-actions-cell">

                                        <a href="admin_edit_event.php?id=<?php echo (int) $event["event_id"]; ?>" class="event-action-link">EDIT</a>

                                        <?php if ($event["status"] !== "Active"): ?>

                                            <form method="POST" onsubmit="return confirm('Set this as the active event? Any other active event will become inactive.');">
                                                <input type="hidden" name="event_id" value="<?php echo (int) $event["event_id"]; ?>">
                                                <input type="hidden" name="action" value="set_active">
                                                <button type="submit" class="event-action-link">SET ACTIVE</button>
                                            </form>

                                        <?php else: ?>

                                            <form method="POST" onsubmit="return confirm('Mark this event inactive?');">
                                                <input type="hidden" name="event_id" value="<?php echo (int) $event["event_id"]; ?>">
                                                <input type="hidden" name="action" value="set_inactive">
                                                <button type="submit" class="event-action-link">DEACTIVATE</button>
                                            </form>

                                        <?php endif; ?>

                                        <form method="POST" onsubmit="return confirm('Delete this event? This cannot be undone.');">
                                            <input type="hidden" name="event_id" value="<?php echo (int) $event["event_id"]; ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <button type="submit" class="event-action-link delete">DELETE</button>
                                        </form>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                <?php endif; ?>

            </div>

        </main>

    </div>

</div>

</body>

</html>