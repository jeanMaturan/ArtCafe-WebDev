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


/* =====================================
   SETTINGS
===================================== */

$max_artists = 5;

$message = "";
$message_type = "";


/* =====================================
   GET ACTIVE EVENT
===================================== */

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


/* =====================================
   APPROVE / REJECT ARTIST
===================================== */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $action = $_POST["action"] ?? "";
    $event_artist_id = (int)($_POST["event_artist_id"] ?? 0);


    /* ================================
       APPROVE
    ================================= */

    if ($action === "approve") {

        /* Count approved artists
           for the active event */

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

        $approved_count =
            (int)$count_data["approved_count"];

        $count_stmt->close();


        /* Check if already full */

        if ($approved_count >= $max_artists) {

            $message =
                "The event already has 5 approved artists. You cannot approve another artist.";

            $message_type = "error";

        } else {

            /* Approve artist */

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

                    $message =
                        "Artist approved successfully.";

                    $message_type = "success";

                } else {

                    $message =
                        "The artist could not be approved.";

                    $message_type = "error";
                }

            } else {

                $message =
                    "Something went wrong while approving the artist.";

                $message_type = "error";
            }

            $approve_stmt->close();
        }
    }


    /* ================================
       REJECT
    ================================= */

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

                $message =
                    "Artist application rejected.";

                $message_type = "success";

            } else {

                $message =
                    "The artist could not be rejected.";

                $message_type = "error";
            }

        } else {

            $message =
                "Something went wrong while rejecting the artist.";

            $message_type = "error";
        }

        $reject_stmt->close();
    }
}


/* =====================================
   COUNT APPROVED ARTISTS
===================================== */

$approved_count = 0;

if ($event_id !== null) {

    $approved_stmt = $conn->prepare(
        "SELECT COUNT(*) AS approved_count
         FROM event_artists
         WHERE event_id = ?
         AND status = 'Approved'"
    );

    $approved_stmt->bind_param(
        "i",
        $event_id
    );

    $approved_stmt->execute();

    $approved_result =
        $approved_stmt->get_result();

    $approved_data =
        $approved_result->fetch_assoc();

    $approved_count =
        (int)$approved_data["approved_count"];

    $approved_stmt->close();
}

$remaining_slots =
    $max_artists - $approved_count;


/* =====================================
   GET ARTIST APPLICATIONS
===================================== */

$artists = [];

if ($event_id !== null) {

    $artist_stmt = $conn->prepare(
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

    $artist_stmt->bind_param(
        "i",
        $event_id
    );

    $artist_stmt->execute();

    $artist_result =
        $artist_stmt->get_result();

    while ($row = $artist_result->fetch_assoc()) {

        $artists[] = $row;
    }

    $artist_stmt->close();
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

    <title>
        Admin Dashboard | Maturan's Art Cafe
    </title>

    <link rel="stylesheet" href="Css/style.css">
    <link rel="stylesheet" href="Css/admin.css">

</head>


<body class="admin-dashboard-page">


    <!-- =====================================
         ADMIN HEADER
    ====================================== -->

    <header class="admin-header">

    <div class="admin-header-left">

        <img
            src="images/logo.png"
            alt="Maturan's Art Cafe Logo"
            class="admin-header-logo"
        >

        <div>

            <h1>
                ADMIN PANEL
            </h1>

            <p>
                Maturan's Art Cafe
            </p>

        </div>

    </div>


    <div class="admin-header-right">

        <a
            href="admin.php"
            class="admin-nav-button"
        >
            ARTISTS
        </a>

        <a
            href="admin_artworks.php"
            class="admin-nav-button"
        >
            ARTWORKS
        </a>

        <a
            href="admin_messages.php"
            class="admin-nav-button"
        >
            MESSAGES
        </a>

        <span>
            Welcome, <?php
            echo htmlspecialchars(
                $_SESSION["admin_username"]
            );
            ?>
        </span>

        <a
            href="admin_logout.php"
            class="admin-logout-button"
        >
            LOGOUT
        </a>

    </div>

</header>



    <!-- =====================================
         ADMIN CONTENT
    ====================================== -->

    <main class="admin-content">


        <div class="admin-page-title">

            <h2>
                ARTIST APPLICATIONS
            </h2>

            <p>
                Manage artists who want to participate
                in the upcoming event.
            </p>

        </div>



        <!-- =====================================
             EVENT INFORMATION
        ====================================== -->

        <?php if ($event_id !== null): ?>

            <div class="admin-event-card">

                <div>

                    <span class="admin-small-label"> ACTIVE EVENT </span>

                    <h3>
                        <?php
                        echo htmlspecialchars(
                            $event_name
                        );
                        ?>
                    </h3>

                </div>

                <div class="artist-slot-info">

                    <div>

                        <strong>
                            <?php echo $approved_count; ?>
                            / <?php echo $max_artists; ?>
                        </strong>

                        <span> Approved Artists </span>

                    </div>

                    <div>

                        <strong>
                            <?php echo $remaining_slots; ?>
                        </strong>

                        <span>
                            Slots Remaining
                        </span>

                    </div>

                </div>

            </div>


        <?php else: ?>

            <div class="admin-message error">

                No active event is currently available.

            </div>

        <?php endif; ?>



        <!-- =====================================
             MESSAGE
        ====================================== -->

        <?php if ($message !== ""): ?>

            <div
                class="admin-message <?php echo $message_type; ?>"
            >

                <?php
                echo htmlspecialchars($message);
                ?>

            </div>

        <?php endif; ?>



        <!-- =====================================
             ARTIST LIST
        ====================================== -->

        <div class="admin-section-title">

            <h3>
                REGISTERED ARTISTS
            </h3>

        </div>


        <?php if (count($artists) === 0): ?>

            <div class="admin-empty">

                <h3>
                    No artist applications yet.
                </h3>

                <p>
                    Artists who register for the event
                    will appear here.
                </p>

            </div>

        <?php else: ?>


            <div class="admin-artist-list">


                <?php foreach ($artists as $artist): ?>


                    <div class="admin-artist-card">


                        <!-- ARTIST INFORMATION -->

                        <div class="admin-artist-info">

                            <div class="admin-artist-top">

                                <h3>
                                    <?php
                                    echo htmlspecialchars(
                                        $artist["artist_name"]
                                    );
                                    ?>
                                </h3>


                                <span
                                    class="artist-status <?php
                                    echo strtolower(
                                        $artist["status"]
                                    );
                                    ?>"
                                >
                                    <?php
                                    echo htmlspecialchars(
                                        $artist["status"]
                                    );
                                    ?>
                                </span>

                            </div>


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


                            <?php if (
                                !empty($artist["bio"])
                            ): ?>

                                <div class="artist-bio">

                                    <strong>
                                        About the Artist:
                                    </strong>

                                    <p>
                                        <?php
                                        echo nl2br(
                                            htmlspecialchars(
                                                $artist["bio"]
                                            )
                                        );
                                        ?>
                                    </p>

                                </div>

                            <?php endif; ?>


                        </div>



                        <!-- ACTIONS -->

                        <div class="admin-artist-actions">


                            <?php if (
                                $artist["status"] === "Pending"
                            ): ?>


                                <?php if (
                                    $approved_count < $max_artists
                                ): ?>

                                    <form method="POST">

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
                                            class="approve-button"
                                        >
                                            APPROVE
                                        </button>

                                    </form>

                                <?php else: ?>

                                    <span class="full-label">
                                        EVENT FULL
                                    </span>

                                <?php endif; ?>


                                <form method="POST">

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
                                        class="reject-button"
                                    >
                                        REJECT
                                    </button>

                                </form>


                            <?php elseif (
                                $artist["status"] === "Approved"
                            ): ?>


                                <span class="approved-label">
                                    ✓ APPROVED
                                </span>


                                <form method="POST">

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
                                        class="reject-button"
                                    >
                                        REJECT
                                    </button>

                                </form>


                            <?php else: ?>


                                <span class="rejected-label">
                                    REJECTED
                                </span>


                            <?php endif; ?>


                        </div>


                    </div>


                <?php endforeach; ?>


            </div>


        <?php endif; ?>


    </main>


</body>

</html>