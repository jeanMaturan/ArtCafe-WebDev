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

/* Close out seated reservations whose date has fully passed */
complete_past_reservations($conn);


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
        $action === "complete" &&
        $reservation_id !== false &&
        $reservation_id !== null &&
        $reservation_id > 0
    ) {

        $stmt = $conn->prepare(
            "UPDATE reservations
             SET status = 'Completed'
             WHERE reservation_id = ?
               AND status = 'Seated'"
        );

        if ($stmt) {

            $stmt->bind_param("i", $reservation_id);

            if ($stmt->execute() && $stmt->affected_rows > 0) {

                $_SESSION["admin_reservation_success"] =
                    "Reservation marked as completed.";

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

$slot_counts = [];      // "date|slot_start" => tables used (accounts for party size)
$reservation_slot = []; // reservation_id => "date|slot_start"

$slot_result = $conn->query(
    "SELECT reservation_id, reservation_date, reservation_time, guests
     FROM reservations
     WHERE status IN ('Pending', 'Seated')"
);

if ($slot_result) {

    while ($row = $slot_result->fetch_assoc()) {

        $slot_start = get_slot_start($row["reservation_time"]);
        $key = $row["reservation_date"] . "|" . $slot_start;

        $tables_for_this_reservation = tables_needed_for_guests((int) $row["guests"]);

        $slot_counts[$key] = ($slot_counts[$key] ?? 0) + $tables_for_this_reservation;
        $reservation_slot[$row["reservation_id"]] = $key;
    }
}

$conflict_slots = array_filter(
    $slot_counts,
    fn($count) => $count > MAX_TABLES
);


/* =====================================
   AVAILABILITY SUMMARY (UPCOMING SLOTS)

   Shows, for every slot from today onward that
   has at least one active reservation, how many
   tables are booked and how many are still left.
===================================== */

$today = date("Y-m-d");

$availability_slots = [];

foreach ($slot_counts as $key => $count) {

    [$slot_date, $slot_time] = explode("|", $key);

    if ($slot_date < $today) {
        continue;
    }

    $availability_slots[] = [
        "date" => $slot_date,
        "time" => $slot_time,
        "booked" => $count,
        "left" => max(0, MAX_TABLES - $count),
    ];
}

usort(
    $availability_slots,
    fn($a, $b) =>
        [$a["date"], $a["time"]] <=> [$b["date"], $b["time"]]
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
                        per <?php echo SLOT_MINUTES; ?>-minute slot
                        (<?php echo GUESTS_PER_TABLE; ?> guests per table —
                        larger parties use more than one table).
                    </p>
                </div>


                <!-- =====================================
                     AVAILABILITY BY TIME SLOT
                ===================================== -->

                <div class="availability-summary">

                    <h3>Availability by Time Slot</h3>

                    <?php if (empty($availability_slots)): ?>

                        <p class="availability-empty">
                            No upcoming reservations yet —
                            all <?php echo MAX_TABLES; ?> tables are open
                            for every slot.
                        </p>

                    <?php else: ?>

                        <div class="availability-table">

                            <div class="availability-row availability-head">
                                <span>Date</span>
                                <span>Time</span>
                                <span>Booked</span>
                                <span>Tables Left</span>
                            </div>

                            <?php foreach ($availability_slots as $slot): ?>

                                <div class="availability-row<?php echo $slot['left'] === 0 ? ' availability-full' : ''; ?>">

                                    <span>
                                        <?php echo date("F j, Y", strtotime($slot["date"])); ?>
                                    </span>

                                    <span>
                                        <?php echo date("g:i A", strtotime($slot["time"])); ?>
                                    </span>

                                    <span>
                                        <?php echo $slot["booked"]; ?> / <?php echo MAX_TABLES; ?>
                                    </span>

                                    <span class="availability-left-value">
                                        <?php if ($slot["left"] === 0): ?>
                                            FULL
                                        <?php else: ?>
                                            <?php echo $slot["left"]; ?> left
                                        <?php endif; ?>
                                    </span>

                                </div>

                            <?php endforeach; ?>

                        </div>

                    <?php endif; ?>

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

                                    <?php if ($r["status"] === "Seated"): ?>

                                        <form method="POST">
                                            <input type="hidden" name="reservation_id" value="<?php echo (int) $r['reservation_id']; ?>">
                                            <input type="hidden" name="action" value="complete">
                                            <button type="submit" class="approve-button">
                                                MARK AS COMPLETED
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