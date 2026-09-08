<?php

session_start();
require_once "db.php";


/* =====================================
   ADMIN LOGIN CHECK
===================================== */

if (
    !isset($_SESSION["admin_logged_in"]) ||
    $_SESSION["admin_logged_in"] !== true ||
    !isset($_SESSION["admin_id"]) ||
    !is_numeric($_SESSION["admin_id"]) ||
    !isset($_SESSION["admin_username"]) ||
    $_SESSION["admin_username"] === ""
) {
    header("Location: admin_login.php");
    exit();
}


/* =====================================
   STAT: TOTAL RESERVATIONS
===================================== */

$total_reservations = 0;

$res = $conn->query("SELECT COUNT(*) AS c FROM reservations");
if ($res) {
    $total_reservations = (int) $res->fetch_assoc()["c"];
}


/* =====================================
   STAT: UPCOMING RESERVATIONS
===================================== */

$upcoming_reservations = 0;

$res = $conn->query(
    "SELECT COUNT(*) AS c FROM reservations
     WHERE reservation_date >= CURDATE()"
);
if ($res) {
    $upcoming_reservations = (int) $res->fetch_assoc()["c"];
}


/* =====================================
   STAT: NEWSLETTER SUBSCRIBERS
===================================== */

$total_subscribers = 0;

$res = $conn->query("SELECT COUNT(*) AS c FROM newsletter_subscribers");
if ($res) {
    $total_subscribers = (int) $res->fetch_assoc()["c"];
}


/* =====================================
   STAT: UNREAD MESSAGES
===================================== */

$unread_messages = 0;

$res = $conn->query(
    "SELECT COUNT(*) AS c FROM contact_messages
     WHERE status = 'Unread'"
);
if ($res) {
    $unread_messages = (int) $res->fetch_assoc()["c"];
}


/* =====================================
   PENDING ACTIONS
===================================== */

$pending_artists = 0;
$res = $conn->query(
    "SELECT COUNT(*) AS c FROM event_artists WHERE status = 'Pending'"
);
if ($res) {
    $pending_artists = (int) $res->fetch_assoc()["c"];
}

$pending_artworks = 0;
$res = $conn->query(
    "SELECT COUNT(*) AS c FROM artworks WHERE status = 'Pending'"
);
if ($res) {
    $pending_artworks = (int) $res->fetch_assoc()["c"];
}

$pending_reviews = 0;
$res = $conn->query(
    "SELECT COUNT(*) AS c FROM reviews WHERE status = 'Pending'"
);
if ($res) {
    $pending_reviews = (int) $res->fetch_assoc()["c"];
}


/* =====================================
   RESERVATIONS PER MONTH (THIS YEAR)
===================================== */

$monthly_counts = array_fill(1, 12, 0);
$current_year = date("Y");

$res = $conn->prepare(
    "SELECT MONTH(reservation_date) AS m, COUNT(*) AS c
     FROM reservations
     WHERE YEAR(reservation_date) = ?
     GROUP BY MONTH(reservation_date)"
);
$res->bind_param("i", $current_year);
$res->execute();
$result = $res->get_result();

while ($row = $result->fetch_assoc()) {
    $monthly_counts[(int) $row["m"]] = (int) $row["c"];
}
$res->close();

$max_month_count = max(1, max($monthly_counts));


/* =====================================
   RECENT RESERVATIONS
===================================== */

$recent_reservations = [];

$res = $conn->query(
    "SELECT reservation_name, reservation_date, reservation_time, guests
     FROM reservations
     ORDER BY reservation_id DESC
     LIMIT 5"
);
if ($res) {
    while ($row = $res->fetch_assoc()) {
        $recent_reservations[] = $row;
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | Maturan's Art Cafe Admin</title>
    <link rel="stylesheet" href="Css/admin_dashboard.css">
</head>

<body>

    <div class="dash-layout">

        <!-- SIDEBAR -->
        <aside class="dash-sidebar">

            <div class="dash-brand">
                <img src="images/logo.png" alt="Maturan's Art Cafe">
                <span>Maturan's<br>Art Cafe</span>
            </div>

            <nav class="dash-nav">
                <a href="admin_dashboard.php" class="dash-nav-link active">Dashboard</a>
                <a href="admin.php" class="dash-nav-link">Artists</a>
                <a href="admin_artworks.php" class="dash-nav-link">Artworks</a>
                <a href="admin_reviews.php" class="dash-nav-link">Reviews</a>
                <a href="admin_messages.php" class="dash-nav-link">Messages</a>
                <a href="admin_subscribers.php" class="dash-nav-link">Subscribers</a>
            </nav>

            <div class="dash-sidebar-footer">
                <a href="index.php">View Site</a>
                <a href="admin_logout.php">Logout</a>
            </div>

        </aside>


        <!-- MAIN -->
        <main class="dash-main">

            <header class="dash-topbar">
                <h1>Admin Dashboard</h1>
                <div class="dash-admin-chip">
                    <?php
                        echo htmlspecialchars(
                            $_SESSION["admin_username"]
                        );
                    ?>
                </div>
            </header>


            <!-- STAT CARDS -->
            <div class="dash-stats">

                <div class="dash-stat-card">
                    <p class="dash-stat-label">Total Reservations</p>
                    <p class="dash-stat-value"><?php echo $total_reservations; ?></p>
                    <p class="dash-stat-sub"><?php echo $upcoming_reservations; ?> upcoming</p>
                </div>

                <div class="dash-stat-card">
                    <p class="dash-stat-label">Newsletter Subscribers</p>
                    <p class="dash-stat-value"><?php echo $total_subscribers; ?></p>
                    <p class="dash-stat-sub">total signed up</p>
                </div>

                <div class="dash-stat-card">
                    <p class="dash-stat-label">Unread Messages</p>
                    <p class="dash-stat-value"><?php echo $unread_messages; ?></p>
                    <p class="dash-stat-sub">need a reply</p>
                </div>

                <div class="dash-stat-card dash-stat-alert">
                    <p class="dash-stat-label">Pending Approvals</p>
                    <p class="dash-stat-value">
                        <?php echo $pending_artists + $pending_artworks + $pending_reviews; ?>
                    </p>
                    <p class="dash-stat-sub">across artists, artworks, reviews</p>
                </div>

            </div>


            <!-- CHART + RECENT -->
            <div class="dash-panels">

                <div class="dash-chart-card">

                    <h2>Reservations This Year — <?php echo $current_year; ?></h2>

                    <div class="dash-chart">
                        <?php
                        $month_labels = [
                            1 => "Jan", 2 => "Feb", 3 => "Mar", 4 => "Apr",
                            5 => "May", 6 => "Jun", 7 => "Jul", 8 => "Aug",
                            9 => "Sep", 10 => "Oct", 11 => "Nov", 12 => "Dec"
                        ];
                        for ($m = 1; $m <= 12; $m++):
                            $count = $monthly_counts[$m];
                            $height_pct = round(($count / $max_month_count) * 100);
                            if ($height_pct < 4 && $count > 0) {
                                $height_pct = 4;
                            }
                        ?>
                            <div class="dash-bar-col">
                                <div
                                    class="dash-bar<?php echo $count > 0 ? '' : ' dash-bar-empty'; ?>"
                                    style="height: <?php echo max($height_pct, 3); ?>%;"
                                    title="<?php echo $count; ?> reservation<?php echo $count === 1 ? '' : 's'; ?>"
                                ></div>
                                <span><?php echo $month_labels[$m]; ?></span>
                            </div>
                        <?php endfor; ?>
                    </div>

                </div>


                <div class="dash-recent-card">

                    <h2>Recent Reservations</h2>

                    <?php if (empty($recent_reservations)): ?>

                        <p class="dash-empty">No reservations yet.</p>

                    <?php else: ?>

                        <ul class="dash-recent-list">
                            <?php foreach ($recent_reservations as $r): ?>
                                <li>
                                    <span class="dash-recent-name">
                                        <?php echo htmlspecialchars($r["reservation_name"]); ?>
                                    </span>
                                    <span class="dash-recent-detail">
                                        <?php
                                            echo date("M j", strtotime($r["reservation_date"]));
                                            echo " · ";
                                            echo date("g:i A", strtotime($r["reservation_time"]));
                                            echo " · ";
                                            echo (int) $r["guests"];
                                            echo " guest" . ($r["guests"] == 1 ? "" : "s");
                                        ?>
                                    </span>
                                </li>
                            <?php endforeach; ?>
                        </ul>

                    <?php endif; ?>

                </div>

            </div>


            <!-- PENDING ACTIONS -->
            <div class="dash-pending-card">

                <h2>Pending Actions</h2>

                <div class="dash-pending-pills">

                    <a href="admin.php" class="dash-pill dash-pill-warn">
                        Artist Applications
                        <span><?php echo $pending_artists; ?></span>
                    </a>

                    <a href="admin_artworks.php" class="dash-pill dash-pill-warn">
                        Artwork Approvals
                        <span><?php echo $pending_artworks; ?></span>
                    </a>

                    <a href="admin_reviews.php" class="dash-pill dash-pill-warn">
                        Review Approvals
                        <span><?php echo $pending_reviews; ?></span>
                    </a>

                    <a href="admin_messages.php" class="dash-pill dash-pill-danger">
                        Unread Messages
                        <span><?php echo $unread_messages; ?></span>
                    </a>

                </div>

            </div>

        </main>

    </div>

</body>

</html>