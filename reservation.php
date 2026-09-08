<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once "db.php";

/* =========================
   CHECK IF USER IS LOGGED IN
========================= */

if (
    !isset($_SESSION["user_logged_in"]) ||
    $_SESSION["user_logged_in"] !== true
) {
    header("Location: login.php");
    exit();
}


$message = "";
$error = "";


/* =========================
   HANDLE RESERVATION
========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $user_id = $_SESSION["user_id"];

    $name = trim($_POST["reservation_name"]);
    $phone = trim($_POST["reservation_phone"]);
    $date = $_POST["date"];
    $time = $_POST["time"];
    $guests = (int) $_POST["guests"];


    /* CHECK REQUIRED FIELDS */

    if (
        empty($name) ||
        empty($phone) ||
        empty($date) ||
        empty($time) ||
        $guests < 1
    ) {

        $error = "Please complete all required fields.";

    } else {


        /* INSERT RESERVATION */

        $stmt = $conn->prepare(
            "INSERT INTO reservations
            (
                user_id,
                reservation_name,
                reservation_phone,
                reservation_date,
                reservation_time,
                guests
            )
            VALUES (?, ?, ?, ?, ?, ?)"
        );


        if (!$stmt) {

            $error = "Database error: " . $conn->error;

        } else {

            $stmt->bind_param(
                "issssi",
                $user_id,
                $name,
                $phone,
                $date,
                $time,
                $guests
            );


            if ($stmt->execute()) {

                $message = "Your table has been reserved successfully!";

            } else {

                $error = "Unable to save your reservation: " . $stmt->error;

            }


            $stmt->close();

        }

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
        Reserve a Table - Maturan's Art Cafe
    </title>

    <link
        rel="stylesheet"
        href="Css/style.css"
    >

</head>


<body>


<!-- =========================
     HEADER
========================= -->

<header class="header">

    <div class="logo">

        <img
            src="images/logo.png"
            alt="Maturan's Art Cafe"
        >

    </div>


    <nav class="navbar">

        <a href="index.php">
            HOME
        </a>

        <a href="about.php">
            ABOUT
        </a>

        <a href="menu.php">
            MENU
        </a>

        <a href="events.php">
            EVENTS
        </a>

        <a href="contact.php">
            CONTACT
        </a>

    </nav>


    <a
        href="reservation.php"
        class="reserve-btn"
    >
        RESERVE A TABLE
    </a>

</header>



<!-- =========================
     RESERVATION SECTION
========================= -->

<section class="reservation-section reservation-page">


    <div class="reservation-content">

        <p class="contact-small">
            PLAN YOUR VISIT
        </p>


        <h2>
            RESERVE A <span>TABLE.</span>
        </h2>


        <p>
            Planning to visit with friends or family?
            Reserve your table ahead of time and
            we'll have a cozy spot ready for you.
        </p>


        <p class="logged-user">

            Logged in as:

            <strong>
                <?= htmlspecialchars($_SESSION["user_email"]) ?>
            </strong>

        </p>

    </div>



    <!-- =========================
         SUCCESS MESSAGE
    ========================== -->

    <?php if (!empty($message)): ?>

        <p class="login-success">

            <?= htmlspecialchars($message) ?>

        </p>

    <?php endif; ?>



    <!-- =========================
         ERROR MESSAGE
    ========================== -->

    <?php if (!empty($error)): ?>

        <p class="login-error">

            <?= htmlspecialchars($error) ?>

        </p>

    <?php endif; ?>



    <!-- =========================
         RESERVATION FORM
    ========================== -->

    <form
        class="reservation-form"
        method="POST"
    >


        <!-- NAME + PHONE -->

        <div class="form-row">


            <div class="form-group">

                <label for="reservation-name">
                    NAME
                </label>


                <input
                    type="text"
                    id="reservation-name"
                    name="reservation_name"
                    placeholder="Your name"
                    value="<?= htmlspecialchars($_SESSION["user_name"]) ?>"
                    required
                >

            </div>



            <div class="form-group">

                <label for="reservation-phone">
                    PHONE
                </label>


                <input
                    type="tel"
                    id="reservation-phone"
                    name="reservation_phone"
                    placeholder="Your phone number"
                    required
                >

            </div>

        </div>



        <!-- DATE + TIME -->

        <div class="form-row">


            <div class="form-group">

                <label for="date">
                    DATE
                </label>


                <input
                    type="date"
                    id="date"
                    name="date"
                    required
                >

            </div>



            <div class="form-group">

                <label for="time">
                    TIME
                </label>


                <input
                    type="time"
                    id="time"
                    name="time"
                    required
                >

            </div>

        </div>



        <!-- NUMBER OF GUESTS -->

        <div class="form-group">

            <label for="guests">
                NUMBER OF GUESTS
            </label>


            <select
                id="guests"
                name="guests"
                required
            >

                <option value="">
                    Select number of guests
                </option>

                <option value="1">
                    1 Guest
                </option>

                <option value="2">
                    2 Guests
                </option>

                <option value="3">
                    3 Guests
                </option>

                <option value="4">
                    4 Guests
                </option>

                <option value="5">
                    5 Guests
                </option>

                <option value="6">
                    6 Guests
                </option>

                <option value="7">
                    7 Guests
                </option>

                <option value="8">
                    8 Guests
                </option>

                <option value="9">
                    9 Guests
                </option>

                <option value="10">
                    10 Guests
                </option>

            </select>

        </div>



        <!-- SUBMIT -->

        <button
            type="submit"
            class="reservation-submit"
        >
            RESERVE NOW
        </button>

    </form>

</section>



<!-- =========================
     FOOTER
========================= -->

<footer class="footer">

    <div class="footer-content">


        <!-- BRAND -->

        <div class="footer-brand">

            <img
                src="images/logo.png"
                alt="Maturan's Art Cafe"
                class="footer-logo"
            >


            <p class="footer-tagline">
                Sip. Create. Relax.
            </p>


            <p class="footer-description">
                A cozy art cafe inspiring creativity,
                connection, and community.
            </p>


            <div class="social-icons">

                <a href="#">
                    ●
                </a>

                <a href="#">
                    ◎
                </a>

                <a href="#">
                    ✉
                </a>

            </div>

        </div>



        <!-- QUICK LINKS -->

        <div class="footer-links">

            <h3>
                QUICK LINKS
            </h3>


            <a href="index.php">
                Home
            </a>


            <a href="about.php">
                About
            </a>


            <a href="menu.php">
                Menu
            </a>


            <a href="events.php">
                Events
            </a>


            <a href="contact.php">
                Contact
            </a>

        </div>



        <!-- CONTACT -->

        <div class="footer-contact">

            <h3>
                CONTACTS
            </h3>


            <p>
                ◈ &nbsp; Jawa, Valencia Negros Oriental
            </p>


            <p>
                ☎ &nbsp; 0958 586 8934
            </p>


            <p>
                ✉ &nbsp; maturansartcafe@gmail.com
            </p>

        </div>



        <!-- SUBSCRIBE -->

        <div class="footer-subscribe">

            <h3>
                STAY CONNECTED
            </h3>


            <p>
                Subscribe to get updates on
                new events and promos!
            </p>


            <form
                class="subscribe-form"
                action="#"
                method="POST"
            >

                <input
                    type="email"
                    name="email"
                    placeholder="Your email"
                    required
                >


                <button type="submit">
                    →
                </button>

            </form>


            <img
                src="images/whiteheart.png"
                alt=""
                class="heart-small"
            >

        </div>

    </div>

</footer>



<script src="JS/script.js"></script>

</body>

</html>