<?php

session_start();
require_once "db.php";
require_once "reservation_config.php";


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


/* Free up any tables from no-shows before showing the list */
expire_stale_reservations($conn);


/* =====================================
   HANDLE DELETE / CANCEL RESERVATION
===================================== */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $action = trim($_POST["action"] ?? "");

    $reservation_id = filter_input(
        INPUT_POST,
        "reservation_id",
        FILTER_VALIDATE_INT
    );

    if (
        $action === "delete" &&
        $reservation_id !== false &&
        $reservation_id !== null &&
        $reservation_id > 0
    ) {

        $stmt = $conn->prepare(
            "DELETE FROM reservations
             WHERE reservation_id = ?"
        );

        if ($stmt) {

            $stmt->bind_param("i", $reservation_id);

            if ($stmt->execute() && $stmt->affected_rows > 0) {

                $_SESSION["admin_reservation_success"] =
                    "Reservation deleted successfully.";

            } else {

                $_SESSION["admin_reservation_error"] =
                    "The reservation could not be deleted.";
            }

            $stmt->close();

        } else {

            $_SESSION["admin_reservation_error"] =
                "Something went wrong. Please try again.";
        }

    } elseif (
        $action === "seated" &&
        $reservation_id !== false &&
        $reservation_id !== null &&
        $reservation_id > 0
    ) {

        $stmt = $conn->prepare(
            "UPDATE reservations
             SET status = 'Seated'
             WHERE reservation_id = ?
               AND status = 'Pending'"
        );

        if ($stmt) {

            $stmt->bind_param("i", $reservation_id);

            if ($stmt->execute() && $stmt->affected_rows > 0) {

                $_SESSION["admin_reservation_success"] =
                    "Reservation marked as seated.";

            } else {

                $_SESSION["admin_reservation_error"] =
                    "The reservation could not be updated.";
            }

            $stmt->close();

        } else {

            $_SESSION["admin_reservation_error"] =
                "Something went wrong. Please try again.";
        }

    } elseif (
        $action === "cancel" &&
        $reservation_id !== false &&
        $reservation_id !== null &&
        $reservation_id > 0
    ) {

        $stmt = $conn->prepare(
            "UPDATE reservations
             SET status = 'Cancelled'
             WHERE reservation_id = ?
               AND status IN ('Pending', 'Seated')"
        );

        if ($stmt) {

            $stmt->bind_param("i", $reservation_id);

            if ($stmt->execute() && $stmt->affected_rows > 0) {

                $_SESSION["admin_reservation_success"] =
                    "Reservation cancelled. The table is now available.";

            } else {

                $_SESSION["admin_reservation_error"] =
                    "The reservation could not be cancelled.";
            }

            $stmt->close();

        } else {

            $_SESSION["admin_reservation_error"] =
                "Something went wrong. Please try again.";
        }

    } else {

        $_SESSION["admin_reservation_error"] = "Invalid request.";
    }

    header("Location: admin_reservations.php");
    exit();
}


/* =====================================
   DISPLAY ONE-TIME MESSAGES
===================================== */

$success = $_SESSION["admin_reservation_success"] ?? "";
$error = $_SESSION["admin_reservation_error"] ?? "";

unset($_SESSION["admin_reservation_success"]);
unset($_SESSION["admin_reservation_error"]);


/* =====================================
   FILTER: UPCOMING / PAST / ALL
===================================== */

$filter = $_GET["filter"] ?? "upcoming";

if (!in_array($filter, ["upcoming", "past", "all"], true)) {
    $filter = "upcoming";
}

$where_clause = "";

if ($filter === "upcoming") {
    $where_clause = "WHERE r.reservation_date >= CURDATE()";
} elseif ($filter === "past") {
    $where_clause = "WHERE r.reservation_date < CURDATE()";
}


/* =====================================
   GET RESERVATIONS
===================================== */

$reservations = [];

$sql = "SELECT
            r.reservation_id,
            r.reservation_name,
            r.reservation_phone,
            r.reservation_date,
            r.reservation_time,
            r.guests,
            r.status,
            u.email AS user_email
        FROM reservations r
        LEFT JOIN users u ON u.user_id = r.user_id
        $where_clause
        ORDER BY r.reservation_date ASC, r.reservation_time ASC";

$result = $conn->query($sql);

if ($result) {

    while ($row = $result->fetch_assoc()) {
        $reservations[] = $row;
    }

} elseif ($error === "") {

    $error = "Unable to load reservations.";
}


/* =====================================
   DETECT OVERBOOKED SLOTS (CONFLICTS)

   Grouped across ALL reservations (not just the
   currently filtered view), so a conflict on a
   past date still shows up if you switch filters.
===================================== */

$slot_counts = [];      // "date|slot_start" => count of reservations
$reservation_slot = []; // reservation_id => "date|slot_start"

$slot_result = $conn->query(
    "SELECT reservation_id, reservation_date, reservation_time
     FROM reservations
     WHERE status IN ('Pending', 'Seated')"
);

if ($slot_result) {

    while ($row = $slot_result->fetch_assoc()) {

        $slot_start = get_slot_start($row["reservation_time"]);
        $key = $row["reservation_date"] . "|" . $slot_start;

        $slot_counts[$key] = ($slot_counts[$key] ?? 0) + 1;
        $reservation_slot[$row["reservation_id"]] = $key;
    }
}

$conflict_slots = array_filter(
    $slot_counts,
    fn($count) => $count > MAX_TABLES
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Reservations - Maturan's Art Cafe</title>

    <link rel="stylesheet" href="Css/style.css">
    <link rel="stylesheet" href="Css/admin.css">
    <link rel="stylesheet" href="Css/admin_dashboard.css">
    <link rel="stylesheet" href="Css/admin_reservations.css">

</head>


<body class="admin-dashboard-page">

    <div class="dash-layout">

        <?php $active_page = "reservations"; include "admin_sidebar.php"; ?>

        <main class="dash-main">

            <header class="dash-topbar">
                <div class="dash-admin-chip">
                    <?php echo htmlspecialchars($_SESSION["user_name"]); ?>
                </div>
            </header>


            <div class="admin-content">

                <div class="admin-page-title">
                    <h2>RESERVATIONS</h2>
                    <p>View and manage all table reservations.</p>
                    <p class="capacity-note">
                        Table capacity: <strong><?php echo MAX_TABLES; ?> tables</strong>
                        per <?php echo SLOT_MINUTES; ?>-minute slot.
                    </p>
                </div>


                <?php if (!empty($conflict_slots)): ?>

                    <div class="conflict-summary">

                        <h3>⚠ Overbooked Slots</h3>

                        <p>
                            These time slots have more reservations than
                            available tables. Review and cancel/delete
                            reservations below to resolve them.
                        </p>

                        <ul>

                            <?php foreach ($conflict_slots as $key => $count): ?>

                                <?php
                                    [$slot_date, $slot_time] = explode("|", $key);
                                ?>

                                <li>
                                    <?php echo date("F j, Y", strtotime($slot_date)); ?>
                                    at
                                    <?php echo date("g:i A", strtotime($slot_time)); ?>
                                    —
                                    <strong><?php echo $count; ?></strong>
                                    reservations
                                    (<?php echo $count - MAX_TABLES; ?> over the
                                    <?php echo MAX_TABLES; ?>-table limit)
                                </li>

                            <?php endforeach; ?>

                        </ul>

                    </div>

                <?php endif; ?>


                <?php if ($success !== ""): ?>
                    <div class="admin-message success">
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <?php if ($error !== ""): ?>
                    <div class="admin-message error">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>


                <!-- FILTER TABS -->
                <div class="reservation-filters">

                    <a
                        href="admin_reservations.php?filter=upcoming"
                        class="reservation-filter-link<?php echo $filter === 'upcoming' ? ' active' : ''; ?>"
                    >
                        Upcoming
                    </a>

                    <a
                        href="admin_reservations.php?filter=past"
                        class="reservation-filter-link<?php echo $filter === 'past' ? ' active' : ''; ?>"
                    >
                        Past
                    </a>

                    <a
                        href="admin_reservations.php?filter=all"
                        class="reservation-filter-link<?php echo $filter === 'all' ? ' active' : ''; ?>"
                    >
                        All
                    </a>

                </div>


                <?php if (count($reservations) === 0): ?>

                    <div class="admin-empty">
                        <h3>No reservations found.</h3>
                        <p>Reservations made through the site will appear here.</p>
                    </div>

                <?php else: ?>

                    <div class="admin-artist-list">

                        <?php foreach ($reservations as $r): ?>

                            <?php
                                $slot_key = $reservation_slot[$r["reservation_id"]] ?? null;
                                $is_conflict = $slot_key !== null && isset($conflict_slots[$slot_key]);
                            ?>

                            <div class="admin-artist-card<?php echo $is_conflict ? ' conflict-card' : ''; ?>">

                                <div class="admin-artist-info">

                                    <div class="admin-artist-top">
                                        <h3>
                                            <?php echo htmlspecialchars($r["reservation_name"]); ?>
                                        </h3>

                                        <span class="artist-status guest-badge">
                                            <?php
                                                echo (int) $r["guests"];
                                                echo " guest" . ($r["guests"] == 1 ? "" : "s");
                                            ?>
                                        </span>

                                        <span class="artist-status status-<?php echo strtolower(str_replace('-', '', $r["status"])); ?>">
                                            <?php echo htmlspecialchars($r["status"]); ?>
                                        </span>

                                        <?php if ($is_conflict): ?>
                                            <span class="artist-status conflict-badge">
                                                CONFLICT
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <div class="artist-detail">
                                        <strong>Date:</strong>
                                        <?php
                                            echo date("F j, Y", strtotime($r["reservation_date"]));
                                        ?>
                                    </div>

                                    <div class="artist-detail">
                                        <strong>Time:</strong>
                                        <?php
                                            echo date("g:i A", strtotime($r["reservation_time"]));
                                        ?>
                                    </div>

                                    <div class="artist-detail">
                                        <strong>Phone:</strong>
                                        <?php echo htmlspecialchars($r["reservation_phone"]); ?>
                                    </div>

                                    <?php if (!empty($r["user_email"])): ?>
                                        <div class="artist-detail">
                                            <strong>Email:</strong>
                                            <?php echo htmlspecialchars($r["user_email"]); ?>
                                        </div>
                                    <?php endif; ?>

                                </div>


                                <div class="admin-artist-actions">

                                    <?php if ($r["status"] === "Pending"): ?>

                                        <form method="POST">
                                            <input type="hidden" name="reservation_id" value="<?php echo (int) $r['reservation_id']; ?>">
                                            <input type="hidden" name="action" value="seated">
                                            <button type="submit" class="approve-button">
                                                MARK AS SEATED
                                            </button>
                                        </form>

                                        <form method="POST">
                                            <input type="hidden" name="reservation_id" value="<?php echo (int) $r['reservation_id']; ?>">
                                            <input type="hidden" name="action" value="cancel">
                                            <button type="submit" class="reject-button">
                                                CANCEL
                                            </button>
                                        </form>

                                    <?php endif; ?>

                                    <form
                                        method="POST"
                                        onsubmit="return confirm('Permanently delete this reservation?');"
                                    >
                                        <input
                                            type="hidden"
                                            name="reservation_id"
                                            value="<?php echo (int) $r['reservation_id']; ?>"
                                        >

                                        <input type="hidden" name="action" value="delete">

                                        <button type="submit" class="reject-button">
                                            DELETE
                                        </button>
                                    </form>

                                </div>

                            </div>

                        <?php endforeach; ?>

                    </div>

                <?php endif; ?>

            </div>

        </main>

    </div>

    <script src="JS/script.js"></script>

</body>

</html>