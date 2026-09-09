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

$admin_id = (int) $_SESSION["user_id"];


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

    $event_id = (int) $event["event_id"];
    $event_name = $event["event_name"];
}


/* =====================================
   APPROVE / REJECT ARTIST
===================================== */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $action = trim($_POST["action"] ?? "");
    $event_artist_id = filter_input(
        INPUT_POST,
        "event_artist_id",
        FILTER_VALIDATE_INT
    );


    /* =====================================
       VALIDATE POST DATA
    ===================================== */

    if (
        !in_array(
            $action,
            ["approve", "reject"],
            true
        )
    ) {

        $message = "Invalid action.";
        $message_type = "error";

    } elseif (
        $event_artist_id === false ||
        $event_artist_id === null ||
        $event_artist_id <= 0
    ) {

        $message = "Invalid artist application.";
        $message_type = "error";

    } elseif ($event_id === null) {

        $message = "No active event is currently available.";
        $message_type = "error";

    } else {


        /* =====================================
           APPROVE
        ===================================== */

        if ($action === "approve") {


            /* =====================================
               COUNT APPROVED ARTISTS
            ===================================== */

            $count_stmt = $conn->prepare(
                "SELECT COUNT(*) AS approved_count
                 FROM event_artists
                 WHERE event_id = ?
                 AND status = 'Approved'"
            );


            if (!$count_stmt) {

                $message =
                    "Something went wrong. Please try again.";

                $message_type = "error";

            } else {

                $count_stmt->bind_param(
                    "i",
                    $event_id
                );

                $count_stmt->execute();

                $count_result =
                    $count_stmt->get_result();

                $count_data =
                    $count_result->fetch_assoc();

                $approved_count =
                    (int) $count_data["approved_count"];

                $count_stmt->close();


                /* =====================================
                   CHECK EVENT CAPACITY
                ===================================== */

                if ($approved_count >= $max_artists) {

                    $message =
                        "The event already has 5 approved artists. You cannot approve another artist.";

                    $message_type = "error";

                } else {


                    /* =====================================
                       APPROVE ONLY PENDING APPLICATION
                       FROM ACTIVE EVENT
                    ===================================== */

                    $approve_stmt = $conn->prepare(
                        "UPDATE event_artists
                         SET status = 'Approved'
                         WHERE event_artist_id = ?
                         AND event_id = ?
                         AND status = 'Pending'"
                    );


                    if (!$approve_stmt) {

                        $message =
                            "Something went wrong. Please try again.";

                        $message_type = "error";

                    } else {

                        $approve_stmt->bind_param(
                            "ii",
                            $event_artist_id,
                            $event_id
                        );


                        if ($approve_stmt->execute()) {

                            if (
                                $approve_stmt->affected_rows > 0
                            ) {

                                $message =
                                    "Artist approved successfully.";

                                $message_type = "success";

                            } else {

                                $message =
                                    "The artist could not be approved. The application may no longer be pending.";

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
            }
        }


        /* =====================================
           REJECT
        ===================================== */

        elseif ($action === "reject") {


            /* =====================================
               REJECT PENDING OR APPROVED ARTIST
               ONLY FROM ACTIVE EVENT
            ===================================== */

            $reject_stmt = $conn->prepare(
                "UPDATE event_artists
                 SET status = 'Rejected'
                 WHERE event_artist_id = ?
                 AND event_id = ?
                 AND status IN ('Pending', 'Approved')"
            );


            if (!$reject_stmt) {

                $message =
                    "Something went wrong. Please try again.";

                $message_type = "error";

            } else {

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
                            "The artist could not be rejected. The application may already be rejected.";

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


    if ($approved_stmt) {

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
            (int) $approved_data["approved_count"];

        $approved_stmt->close();
    }
}


$remaining_slots =
    max(0, $max_artists - $approved_count);


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


    if ($artist_stmt) {

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
    <link rel="stylesheet" href="Css/admin_dashboard.css">

</head>


<body class="admin-dashboard-page">


    <div class="dash-layout">

        <?php $active_page = "artists"; include "admin_sidebar.php"; ?>

        <main class="dash-main">

            <header class="dash-topbar">
                <div class="dash-admin-chip">
                    <?php
                        echo htmlspecialchars(
                            $_SESSION["user_name"]
                        );
                    ?>
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


        </main>

    </div>

</body>

</html>