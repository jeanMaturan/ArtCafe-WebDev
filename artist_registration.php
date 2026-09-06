<?php

session_start();
require_once "db.php";

/* =====================================
   USER MUST BE LOGGED IN
===================================== */

if (
    !isset($_SESSION["user_logged_in"]) ||
    $_SESSION["user_logged_in"] !== true ||
    !isset($_SESSION["user_id"]) ||
    !is_numeric($_SESSION["user_id"])
) {
    header("Location: login.php?from=artist");
    exit();
}

$user_id = (int) $_SESSION["user_id"];

$success = "";
$error = "";


/* =====================================
   CHECK IF USER IS ALREADY AN APPROVED ARTIST
===================================== */

$artist_check = $conn->prepare(
    "SELECT a.artist_id
     FROM artists a
     INNER JOIN event_artists ea
        ON a.artist_id = ea.artist_id
     WHERE a.user_id = ?
       AND ea.status = 'Approved'
     LIMIT 1"
);

if (!$artist_check) {
    die("Something went wrong. Please try again.");
}

$artist_check->bind_param("i", $user_id);
$artist_check->execute();

$artist_result = $artist_check->get_result();

if ($artist_result->num_rows === 1) {

    $artist_check->close();

    header("Location: artist_artworks.php");
    exit();

}

$artist_check->close();


/* =====================================
   ARTIST CAPACITY
===================================== */

$max_artists = 5;
$approved_count = 0;

$capacity_query = $conn->query(
    "SELECT COUNT(*) AS approved_count
     FROM event_artists
     WHERE status = 'Approved'"
);

if ($capacity_query) {

    $capacity = $capacity_query->fetch_assoc();

    $approved_count = (int) $capacity["approved_count"];
}


/* =====================================
   FORM SUBMISSION
===================================== */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $artist_name = trim($_POST["artist_name"] ?? "");
    $bio = trim($_POST["bio"] ?? "");
    $contact = trim($_POST["contact"] ?? "");


    /* =====================================
       CHECK ARTIST CAPACITY AGAIN
       IMPORTANT: DO NOT TRUST OLD COUNT
    ===================================== */

    $capacity_check = $conn->query(
        "SELECT COUNT(*) AS approved_count
         FROM event_artists
         WHERE status = 'Approved'"
    );

    if ($capacity_check) {

        $capacity_data = $capacity_check->fetch_assoc();

        $approved_count = (int) $capacity_data["approved_count"];

    }


    if ($approved_count >= $max_artists) {

        $error =
            "Artist registration is currently full. The maximum of 5 approved artists has been reached.";

    }


    /* =====================================
       REQUIRED FIELDS
    ===================================== */

    elseif ($artist_name === "" || $contact === "") {

        $error = "Please fill in all required fields.";

    }


    /* =====================================
       VALIDATE ARTIST NAME
    ===================================== */

    elseif (strlen($artist_name) < 2) {

        $error = "Artist name must be at least 2 characters.";

    }

    elseif (strlen($artist_name) > 100) {

        $error = "Artist name must not exceed 100 characters.";

    }

    elseif (!preg_match('/[A-Za-z]/', $artist_name)) {

        $error = "Artist name must contain at least one letter.";

    }


    /* =====================================
       VALIDATE CONTACT NUMBER
       PHILIPPINE FORMAT
    ===================================== */

    elseif (!preg_match('/^(09\d{9}|\+639\d{9})$/', $contact)) {

        $error =
            "Please enter a valid Philippine contact number (09XXXXXXXXX or +639XXXXXXXXX).";

    }


    /* =====================================
       VALIDATE BIO
    ===================================== */

    elseif (strlen($bio) > 2000) {

        $error = "Artist description must not exceed 2000 characters.";

    }


    else {

        /* =====================================
           CHECK IF USER ALREADY HAS AN ARTIST RECORD
        ===================================== */

        $check = $conn->prepare(
            "SELECT artist_id
             FROM artists
             WHERE user_id = ?
             LIMIT 1"
        );

        if (!$check) {

            $error = "Something went wrong. Please try again.";

        } else {

            $check->bind_param("i", $user_id);
            $check->execute();

            $result = $check->get_result();


            if ($result->num_rows > 0) {

                $error = "You are already registered as an artist.";

            }

            else {

                /* =====================================
                   GET ACTIVE EVENT
                ===================================== */

                $event_stmt = $conn->prepare(
                    "SELECT event_id
                     FROM events
                     WHERE status = 'Active'
                     ORDER BY event_id ASC
                     LIMIT 1"
                );

                if (!$event_stmt) {

                    $error = "Something went wrong. Please try again.";

                } else {

                    $event_stmt->execute();

                    $event_result = $event_stmt->get_result();


                    if ($event_result->num_rows !== 1) {

                        $error = "No active event is currently available.";

                    }

                    else {

                        $event = $event_result->fetch_assoc();
                        $event_id = (int) $event["event_id"];


                        /* =====================================
                           INSERT ARTIST
                        ===================================== */

                        $stmt = $conn->prepare(
                            "INSERT INTO artists
                            (user_id, artist_name, bio, contact)
                            VALUES (?, ?, ?, ?)"
                        );

                        if (!$stmt) {

                            $error =
                                "Something went wrong. Please try again.";

                        } else {

                            $stmt->bind_param(
                                "isss",
                                $user_id,
                                $artist_name,
                                $bio,
                                $contact
                            );


                            if ($stmt->execute()) {

                                $artist_id = $stmt->insert_id;


                                /* =====================================
                                   CONNECT ARTIST TO EVENT
                                ===================================== */

                                $event_artist_stmt = $conn->prepare(
                                    "INSERT INTO event_artists
                                    (event_id, artist_id, status)
                                    VALUES (?, ?, 'Pending')"
                                );

                                if (!$event_artist_stmt) {

                                    $error =
                                        "Artist was registered, but the event application could not be submitted.";

                                } else {

                                    $event_artist_stmt->bind_param(
                                        "ii",
                                        $event_id,
                                        $artist_id
                                    );


                                    if ($event_artist_stmt->execute()) {

                                        $success =
                                            "Artist registration submitted successfully! Your application is now pending approval.";

                                    } else {

                                        $error =
                                            "Artist was registered, but the event application could not be submitted.";

                                    }

                                    $event_artist_stmt->close();
                                }

                            } else {

                                $error =
                                    "Something went wrong. Please try again.";
                            }

                            $stmt->close();
                        }
                    }

                    $event_stmt->close();
                }
            }

            $check->close();
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Join as an Artist | Maturan's Art Cafe</title>

    <link rel="stylesheet" href="Css/style.css">
    <link rel="stylesheet" href="Css/artist_registration.css">

</head>


<body>


    <!-- HEADER -->

    <header class="header">

        <div class="logo">

            <img
                src="images/logo.png"
                alt="Maturan's Art Cafe Logo"
            >

        </div>

        <button class="menu-toggle" id="menuToggle">
            ☰
        </button>

        <nav class="navbar">

            <a href="index.php">HOME</a>

            <a href="about.php">ABOUT</a>

            <a href="menu.php">MENU</a>

            <a href="events.php">EVENTS</a>

            <a href="contact.php">CONTACT</a>

        </nav>


        <a
            href="login.php"
            class="reserve-btn"
        >
            RESERVE A TABLE
        </a>

    </header>



    <!-- ARTIST REGISTRATION -->

    <section class="artist-registration-page">

        <div class="artist-registration-box">


            <h1>JOIN AS AN ARTIST</h1>


            <p class="artist-description">

                Become part of our Paint & Sip Night
                and share your creativity with other
                art lovers at Maturan's Art Cafe.

            </p>


            <!-- =====================================
                 ARTIST CAPACITY
            ====================================== -->

            <div class="artist-capacity">

                <strong>
                    Artist Slots:
                </strong>

                <?php echo $approved_count; ?>
                / <?php echo $max_artists; ?>

            </div>


            <?php if ($approved_count >= $max_artists): ?>

                <div class="error-message">

                    Artist registration is currently full.
                    The maximum of 5 approved artists
                    has been reached.

                </div>

            <?php endif; ?>


            <?php if ($success !== ""): ?>

                <div class="success-message">

                    <?php
                    echo htmlspecialchars($success);
                    ?>

                </div>

            <?php endif; ?>


            <?php if ($error !== "" && $approved_count < $max_artists): ?>

                <div class="error-message">

                    <?php
                    echo htmlspecialchars($error);
                    ?>

                </div>

            <?php endif; ?>


            <?php if ($approved_count < $max_artists): ?>


                <!-- =====================================
                     REGISTRATION FORM
                ====================================== -->

                <form method="POST">


                    <div class="login-group">

                        <label class="login-label">
                            ARTIST NAME
                        </label>

                        <input
                            type="text"
                            name="artist_name"
                            placeholder="Enter your artist name"
                            required
                        >

                    </div>



                    <div class="login-group">

                        <label class="login-label">
                            EMAIL
                        </label>

                        <input
                            type="email"
                            value="<?php
                                echo htmlspecialchars(
                                    $_SESSION["user_email"]
                                );
                            ?>"
                            disabled
                        >

                    </div>



                    <div class="login-group">

                        <label class="login-label">
                            CONTACT NUMBER
                        </label>

                        <input
                            type="text"
                            name="contact"
                            placeholder="Enter your contact number"
                            required
                        >

                    </div>



                    <div class="login-group">

                        <label class="login-label">
                            ABOUT YOU AS AN ARTIST
                        </label>

                        <textarea
                            name="bio"
                            placeholder="Tell us about yourself..."
                            rows="5"
                        ></textarea>

                    </div>

                    <div class="login-group">

</div>



                    <button
                        type="submit"
                        class="login-button"
                    >
                        SUBMIT ARTIST APPLICATION
                    </button>


                </form>


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