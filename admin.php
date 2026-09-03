<?php

session_start();
require_once "db.php";


// =====================================
// CHECK ADMIN LOGIN
// =====================================

if (
    !isset($_SESSION["admin_logged_in"]) ||
    $_SESSION["admin_logged_in"] !== true
) {
    header("Location: admin_login.php");
    exit();
}


$success = "";
$error = "";


// =====================================
// MAXIMUM APPROVED ARTISTS
// =====================================

$max_artists = 5;


// =====================================
// GET ACTIVE EVENT
// =====================================

$event_id = null;
$event_name = "";

$event_query = $conn->query(
    "SELECT event_id, event_name
     FROM events
     WHERE status = 'Active'
     ORDER BY event_id ASC
     LIMIT 1"
);

if ($event_query && $event_query->num_rows === 1) {

    $event = $event_query->fetch_assoc();

    $event_id = $event["event_id"];
    $event_name = $event["event_name"];
}


// =====================================
// APPROVE / REJECT ARTIST
// =====================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $event_artist_id = (int)($_POST["event_artist_id"] ?? 0);
    $action = $_POST["action"] ?? "";


    if ($event_artist_id <= 0) {

        $error = "Invalid artist application.";

    } elseif ($event_id === null) {

        $error = "No active event is currently available.";

    } else {


        // =====================================
        // APPROVE ARTIST
        // =====================================

        if ($action === "approve") {


            // Count currently approved artists
            $count_stmt = $conn->prepare(
                "SELECT COUNT(*) AS approved_count
                 FROM event_artists
                 WHERE event_id = ?
                 AND status = 'Approved'"
            );

            $count_stmt->bind_param(
                "i",
                $event_id
            );

            $count_stmt->execute();

            $count_result = $count_stmt->get_result();

            $count_data = $count_result->fetch_assoc();

            $approved_count = (int)$count_data["approved_count"];

            $count_stmt->close();


            // Check if already at maximum
            if ($approved_count >= $max_artists) {

                $error =
                    "Cannot approve this artist. The maximum of 5 approved artists has already been reached.";

            } else {


                // Make sure application belongs to active event
                $approve_stmt = $conn->prepare(
                    "UPDATE event_artists
                     SET status = 'Approved'
                     WHERE event_artist_id = ?
                     AND event_id = ?
                     AND status = 'Pending'"
                );

                $approve_stmt->bind_param(
                    "ii",
                    $event_artist_id,
                    $event_id
                );


                if ($approve_stmt->execute()) {

                    if ($approve_stmt->affected_rows > 0) {

                        $success =
                            "Artist application approved successfully.";

                    } else {

                        $error =
                            "This artist application could not be approved. It may already have been processed.";
                    }

                } else {

                    $error =
                        "Something went wrong while approving the artist.";
                }


                $approve_stmt->close();
            }
        }


        // =====================================
        // REJECT ARTIST
        // =====================================

        elseif ($action === "reject") {

            $reject_stmt = $conn->prepare(
                "UPDATE event_artists
                 SET status = 'Rejected'
                 WHERE event_artist_id = ?
                 AND event_id = ?
                 AND status = 'Pending'"
            );

            $reject_stmt->bind_param(
                "ii",
                $event_artist_id,
                $event_id
            );


            if ($reject_stmt->execute()) {

                if ($reject_stmt->affected_rows > 0) {

                    $success =
                        "Artist application rejected.";

                } else {

                    $error =
                        "This artist application could not be rejected. It may already have been processed.";
                }

            } else {

                $error =
                    "Something went wrong while rejecting the artist.";
            }


            $reject_stmt->close();
        }
    }
}


// =====================================
// COUNT APPROVED ARTISTS
// =====================================

$approved_count = 0;

if ($event_id !== null) {

    $approved_query = $conn->prepare(
        "SELECT COUNT(*) AS approved_count
         FROM event_artists
         WHERE event_id = ?
         AND status = 'Approved'"
    );

    $approved_query->bind_param(
        "i",
        $event_id
    );

    $approved_query->execute();

    $approved_result = $approved_query->get_result();

    $approved_data = $approved_result->fetch_assoc();

    $approved_count = (int)$approved_data["approved_count"];

    $approved_query->close();
}


$remaining_slots = $max_artists - $approved_count;


// =====================================
// GET ARTIST APPLICATIONS
// =====================================

$artists = [];

if ($event_id !== null) {

    $artist_query = $conn->prepare(
        "SELECT
            ea.event_artist_id,
            ea.status,
            ea.joined_at,

            a.artist_id,
            a.artist_name,
            a.bio,
            a.contact,

            u.email

         FROM event_artists ea

         INNER JOIN artists a
            ON ea.artist_id = a.artist_id

         INNER JOIN users u
            ON a.user_id = u.user_id

         WHERE ea.event_id = ?

         ORDER BY
            CASE
                WHEN ea.status = 'Pending' THEN 1
                WHEN ea.status = 'Approved' THEN 2
                WHEN ea.status = 'Rejected' THEN 3
            END,
            ea.joined_at ASC"
    );

    $artist_query->bind_param(
        "i",
        $event_id
    );

    $artist_query->execute();

    $artist_result = $artist_query->get_result();

    while ($row = $artist_result->fetch_assoc()) {

        $artists[] = $row;
    }

    $artist_query->close();
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Admin | Maturan's Art Cafe</title>

    <link
        rel="stylesheet"
        href="Css/style.css"
    >

    <style>

        /* =========================
           ADMIN PAGE
           ========================= */

        .admin-page {
            min-height: calc(100vh - 90px);

            background: #fff9df;

            padding: 50px 45px;
        }


        .admin-container {
            max-width: 1100px;

            margin: 0 auto;
        }


        .admin-header {
            display: flex;

            justify-content: space-between;

            align-items: center;

            margin-bottom: 30px;
        }


        .admin-header h1 {
            color: #4a2819;

            font-size: 32px;

            letter-spacing: 1px;
        }


        .admin-logout {
            background: #ed542c;

            color: white;

            text-decoration: none;

            padding: 11px 18px;

            border-radius: 5px;

            font-size: 11px;

            font-weight: bold;

            letter-spacing: 1px;
        }


        .admin-event {
            color: #4a2819;

            font-size: 20px;

            margin-bottom: 25px;
        }


        /* =========================
           CAPACITY BOX
           ========================= */

        .capacity-box {
            background: white;

            border-radius: 15px;

            padding: 25px;

            margin-bottom: 30px;

            box-shadow: 5px 6px 0 rgba(0, 0, 0, 0.08);

            display: flex;

            gap: 50px;

            flex-wrap: wrap;
        }


        .capacity-item {
            display: flex;

            flex-direction: column;

            gap: 5px;
        }


        .capacity-label {
            color: #998c85;

            font-size: 11px;

            font-weight: bold;

            letter-spacing: 1px;
        }


        .capacity-number {
            color: #4a2819;

            font-size: 25px;

            font-weight: bold;
        }


        /* =========================
           MESSAGES
           ========================= */

        .admin-success {
            background: #e5f5e5;

            color: #286628;

            padding: 14px 18px;

            border-radius: 7px;

            margin-bottom: 20px;

            font-size: 13px;
        }


        .admin-error {
            background: #ffe8e3;

            color: #a52e1c;

            padding: 14px 18px;

            border-radius: 7px;

            margin-bottom: 20px;

            font-size: 13px;
        }


        /* =========================
           APPLICATIONS
           ========================= */

        .applications-title {
            color: #4a2819;

            font-size: 24px;

            margin-bottom: 20px;
        }


        .artist-application {
            background: white;

            border-radius: 15px;

            padding: 25px;

            margin-bottom: 20px;

            box-shadow: 5px 6px 0 rgba(0, 0, 0, 0.08);
        }


        .artist-top {
            display: flex;

            justify-content: space-between;

            align-items: flex-start;

            gap: 20px;

            margin-bottom: 15px;
        }


        .artist-name {
            color: #4a2819;

            font-size: 20px;

            font-weight: bold;
        }


        /* =========================
           STATUS
           ========================= */

        .artist-status {
            display: inline-block;

            padding: 6px 12px;

            border-radius: 20px;

            font-size: 10px;

            font-weight: bold;

            letter-spacing: 1px;
        }


        .status-pending {
            background: #fff0d8;

            color: #9a6100;
        }


        .status-approved {
            background: #e2f4e2;

            color: #287128;
        }


        .status-rejected {
            background: #ffe4df;

            color: #a83221;
        }


        /* =========================
           ARTIST DETAILS
           ========================= */

        .artist-details {
            display: grid;

            grid-template-columns: repeat(2, 1fr);

            gap: 12px;

            margin-bottom: 15px;
        }


        .artist-detail {
            font-size: 13px;

            color: #5e514b;
        }


        .artist-detail strong {
            color: #3d2116;
        }


        .artist-bio {
            background: #fff9df;

            padding: 15px;

            border-radius: 8px;

            margin-bottom: 20px;

            font-size: 13px;

            line-height: 1.6;

            color: #5e514b;
        }


        /* =========================
           BUTTONS
           ========================= */

        .artist-actions {
            display: flex;

            gap: 10px;
        }


        .approve-btn,
        .reject-btn {
            border: none;

            padding: 10px 18px;

            border-radius: 5px;

            color: white;

            font-size: 11px;

            font-weight: bold;

            letter-spacing: 1px;

            cursor: pointer;
        }


        .approve-btn {
            background: #4b8f4b;
        }


        .approve-btn:hover {
            background: #397439;
        }


        .reject-btn {
            background: #d94b35;
        }


        .reject-btn:hover {
            background: #b83b28;
        }


        /* =========================
           NO APPLICATIONS
           ========================= */

        .no-applications {
            background: white;

            padding: 35px;

            text-align: center;

            border-radius: 15px;

            color: #998c85;

            font-size: 14px;
        }


        /* =========================
           MOBILE
           ========================= */

        @media (max-width: 650px) {

            .admin-page {
                padding: 35px 20px;
            }


            .admin-header {
                align-items: flex-start;

                gap: 15px;

                flex-direction: column;
            }


            .admin-header h1 {
                font-size: 27px;
            }


            .capacity-box {
                gap: 25px;
            }


            .artist-top {
                flex-direction: column;
            }


            .artist-details {
                grid-template-columns: 1fr;
            }


            .artist-actions {
                flex-direction: column;
            }


            .approve-btn,
            .reject-btn {
                width: 100%;
            }

        }

    </style>

</head>


<body>


    <!-- =====================================
         HEADER
    ====================================== -->

    <header class="header">

        <div class="logo">

            <img
                src="images/logo.png"
                alt="Maturan's Art Cafe Logo"
            >

        </div>


        <nav class="navbar">

            <a href="index.php">HOME</a>

            <a href="about.php">ABOUT</a>

            <a href="menu.php">MENU</a>

            <a href="events.php">EVENTS</a>

            <a href="contact.php">CONTACT</a>

        </nav>


        <a
            href="admin_logout.php"
            class="reserve-btn"
        >
            LOGOUT
        </a>

    </header>



    <!-- =====================================
         ADMIN PAGE
    ====================================== -->

    <section class="admin-page">

        <div class="admin-container">


            <!-- HEADER -->

            <div class="admin-header">

                <h1>
                    ADMIN PANEL
                </h1>

            </div>


            <?php if ($event_id !== null): ?>

                <div class="admin-event">

                    <?php
                    echo htmlspecialchars($event_name);
                    ?>

                </div>

            <?php else: ?>

                <div class="admin-error">

                    No active event is currently available.

                </div>

            <?php endif; ?>


            <!-- =====================================
                 MESSAGES
            ====================================== -->

            <?php if ($success !== ""): ?>

                <div class="admin-success">

                    <?php
                    echo htmlspecialchars($success);
                    ?>

                </div>

            <?php endif; ?>


            <?php if ($error !== ""): ?>

                <div class="admin-error">

                    <?php
                    echo htmlspecialchars($error);
                    ?>

                </div>

            <?php endif; ?>



            <!-- =====================================
                 CAPACITY
            ====================================== -->

            <div class="capacity-box">


                <div class="capacity-item">

                    <span class="capacity-label">
                        APPROVED ARTISTS
                    </span>

                    <span class="capacity-number">

                        <?php echo $approved_count; ?>
                        / <?php echo $max_artists; ?>

                    </span>

                </div>


                <div class="capacity-item">

                    <span class="capacity-label">
                        AVAILABLE SLOTS
                    </span>

                    <span class="capacity-number">

                        <?php echo max(0, $remaining_slots); ?>

                    </span>

                </div>


            </div>



            <!-- =====================================
                 APPLICATIONS
            ====================================== -->

            <h2 class="applications-title">

                ARTIST APPLICATIONS

            </h2>


            <?php if (count($artists) > 0): ?>


                <?php foreach ($artists as $artist): ?>


                    <div class="artist-application">


                        <!-- ARTIST NAME + STATUS -->

                        <div class="artist-top">


                            <div class="artist-name">

                                <?php
                                echo htmlspecialchars(
                                    $artist["artist_name"]
                                );
                                ?>

                            </div>


                            <?php

                            $status_class =
                                "status-pending";

                            if (
                                $artist["status"]
                                === "Approved"
                            ) {

                                $status_class =
                                    "status-approved";

                            } elseif (
                                $artist["status"]
                                === "Rejected"
                            ) {

                                $status_class =
                                    "status-rejected";
                            }

                            ?>

                            <span
                                class="artist-status <?php echo $status_class; ?>"
                            >

                                <?php
                                echo htmlspecialchars(
                                    strtoupper(
                                        $artist["status"]
                                    )
                                );
                                ?>

                            </span>


                        </div>



                        <!-- ARTIST DETAILS -->

                        <div class="artist-details">


                            <div class="artist-detail">

                                <strong>
                                    Email:
                                </strong>

                                <?php
                                echo htmlspecialchars(
                                    $artist["email"]
                                );
                                ?>

                            </div>


                            <div class="artist-detail">

                                <strong>
                                    Contact:
                                </strong>

                                <?php
                                echo htmlspecialchars(
                                    $artist["contact"]
                                );
                                ?>

                            </div>


                        </div>



                        <!-- BIO -->

                        <?php if (
                            !empty($artist["bio"])
                        ): ?>

                            <div class="artist-bio">

                                <strong>
                                    About Their Art:
                                </strong>

                                <br>

                                <?php
                                echo nl2br(
                                    htmlspecialchars(
                                        $artist["bio"]
                                    )
                                );
                                ?>

                            </div>

                        <?php endif; ?>



                        <!-- =====================================
                             ACTION BUTTONS
                        ====================================== -->

                        <?php if (
                            $artist["status"]
                            === "Pending"
                        ): ?>

                            <div class="artist-actions">


                                <!-- APPROVE -->

                                <form
                                    method="POST"
                                >

                                    <input
                                        type="hidden"
                                        name="event_artist_id"
                                        value="<?php
                                            echo $artist[
                                                "event_artist_id"
                                            ];
                                        ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="action"
                                        value="approve"
                                    >

                                    <button
                                        type="submit"
                                        class="approve-btn"
                                    >
                                        APPROVE
                                    </button>

                                </form>



                                <!-- REJECT -->

                                <form
                                    method="POST"
                                >

                                    <input
                                        type="hidden"
                                        name="event_artist_id"
                                        value="<?php
                                            echo $artist[
                                                "event_artist_id"
                                            ];
                                        ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="action"
                                        value="reject"
                                    >

                                    <button
                                        type="submit"
                                        class="reject-btn"
                                    >
                                        REJECT
                                    </button>

                                </form>


                            </div>


                        <?php elseif (
                            $artist["status"]
                            === "Approved"
                        ): ?>

                            <!-- APPROVED ARTIST -->

                            <div class="artist-actions">

                                <form
                                    method="POST"
                                >

                                    <input
                                        type="hidden"
                                        name="event_artist_id"
                                        value="<?php
                                            echo $artist[
                                                "event_artist_id"
                                            ];
                                        ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="action"
                                        value="reject"
                                    >

                                    <button
                                        type="submit"
                                        class="reject-btn"
                                    >
                                        REJECT
                                    </button>

                                </form>

                            </div>


                        <?php endif; ?>


                    </div>


                <?php endforeach; ?>


            <?php else: ?>


                <div class="no-applications">

                    No artist applications yet.

                </div>


            <?php endif; ?>


        </div>

    </section>



    <!-- FOOTER -->

    <footer class="footer">

        <p>

            © 2027 Maturan's Art Cafe.
            All Rights Reserved.

        </p>

    </footer>


</body>

</html>