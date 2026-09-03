<?php

session_start();
require_once "db.php";

if (!isset($_SESSION["user_logged_in"]) || $_SESSION["user_logged_in"] !== true) {
    header("Location: login.php");
    exit();
}

$success = "";
$error = "";


// =====================================
// CHECK ARTIST CAPACITY
// =====================================

// Maximum number of approved artists
$max_artists = 5;

// Count approved artists for the active event
$approved_count = 0;

$capacity_query = $conn->query(
    "SELECT COUNT(*) AS approved_count
     FROM event_artists
     WHERE status = 'Approved'"
);

if ($capacity_query) {
    $capacity = $capacity_query->fetch_assoc();
    $approved_count = (int)$capacity["approved_count"];
}


// =====================================
// CHECK IF USER SUBMITTED THE FORM
// =====================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $artist_name = trim($_POST["artist_name"] ?? "");
    $bio = trim($_POST["bio"] ?? "");
    $contact = trim($_POST["contact"] ?? "");

    $user_id = $_SESSION["user_id"];


    // =====================================
    // CHECK IF EVENT IS ALREADY FULL
    // =====================================

    if ($approved_count >= $max_artists) {

        $error = "Artist registration is currently full. The maximum of 5 approved artists has been reached.";

    } elseif ($artist_name === "" || $contact === "") {

        $error = "Please fill in all required fields.";

    } else {


        // =====================================
        // CHECK IF USER IS ALREADY AN ARTIST
        // =====================================

        $check = $conn->prepare(
            "SELECT artist_id
             FROM artists
             WHERE user_id = ?"
        );

        $check->bind_param("i", $user_id);
        $check->execute();

        $result = $check->get_result();


        if ($result->num_rows > 0) {

            $error = "You are already registered as an artist.";

        } else {


            // =====================================
            // INSERT ARTIST
            // =====================================

            $stmt = $conn->prepare(
                "INSERT INTO artists
                (user_id, artist_name, bio, contact)
                VALUES (?, ?, ?, ?)"
            );

            $stmt->bind_param(
                "isss",
                $user_id,
                $artist_name,
                $bio,
                $contact
            );


            if ($stmt->execute()) {

                $artist_id = $stmt->insert_id;


                // =====================================
                // GET THE ACTIVE EVENT
                // =====================================

                $event_query = $conn->query(
                    "SELECT event_id
                     FROM events
                     WHERE status = 'Active'
                     ORDER BY event_id ASC
                     LIMIT 1"
                );


                if ($event_query && $event_query->num_rows === 1) {

                    $event = $event_query->fetch_assoc();
                    $event_id = $event["event_id"];


                    // =====================================
                    // CONNECT ARTIST TO EVENT
                    // =====================================

                    $event_stmt = $conn->prepare(
                        "INSERT INTO event_artists
                        (event_id, artist_id, status)
                        VALUES (?, ?, 'Pending')"
                    );

                    $event_stmt->bind_param(
                        "ii",
                        $event_id,
                        $artist_id
                    );


                    if ($event_stmt->execute()) {

                        $success =
                            "Artist registration submitted successfully! Your application is now pending approval.";

                    } else {

                        $error =
                            "Artist was registered, but the event application could not be submitted.";
                    }


                    $event_stmt->close();

                } else {

                    $error =
                        "No active event is currently available.";
                }


            } else {

                $error =
                    "Something went wrong. Please try again.";
            }


            $stmt->close();
        }


        $check->close();
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

    <link rel="stylesheet"
          href="Css/style.css">

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
                            ABOUT YOUR ART
                        </label>

                        <textarea
                            name="bio"
                            placeholder="Tell us about yourself and your artwork..."
                            rows="5"
                        ></textarea>

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