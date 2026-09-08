<?php

session_start();
require_once "db.php";


/* USER MUST BE LOGGED IN */
if (
    !isset($_SESSION["user_logged_in"]) ||
    $_SESSION["user_logged_in"] !== true
) {
    header("Location: login.php");
    exit();
}


$success = "";
$error = "";


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $user_id = $_SESSION["user_id"];

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $subject = trim($_POST["subject"] ?? "");
    $message = trim($_POST["message"] ?? "");

    if (
        $name === "" ||
        $email === "" ||
        $subject === "" ||
        $message === ""
    ) {

        $error = "Please complete all fields.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    } else {

        $stmt = $conn->prepare(
            "INSERT INTO contact_messages
            (
                user_id,
                name,
                email,
                subject,
                message
            )
            VALUES (?, ?, ?, ?, ?)"
        );

        if (!$stmt) {

            $error = "Database error: " . $conn->error;

        } else {

            $stmt->bind_param(
                "issss",
                $user_id,
                $name,
                $email,
                $subject,
                $message
            );

            if ($stmt->execute()) {

                $success = "Your message has been sent successfully!";

            } else {

                $error = "Something went wrong. Please try again.";
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Contact - Maturan's Art Cafe</title>

    <link rel="stylesheet" href="Css/style.css">
    <link rel="stylesheet" href="Css/contact.css">

</head>

<body>


    <!HEADER>

    <header class="header">

        <div class="logo">

            <img
                src="images/logo.png"
                alt="Maturan's Art Cafe"
            >

        </div>


        <nav class="navbar">
            <a href="index.php">HOME</a>
            <a href="about.php">ABOUT</a>
            <a href="menu.php">MENU</a>
            <a href="events.php">EVENTS</a>
            <a href="contact.php" class="active" style="color: #ed542c !important;">CONTACT</a>

            <?php if (isset($_SESSION["user_id"])): ?>
             <a href="my_messages.php">MY MESSAGES</a>
            <?php endif; ?>
        </nav>


        <a
            href="login.php"
            class="reserve-btn"
        >
            RESERVE A TABLE
        </a>

    </header>



    <!HERO>

    <section class="contact-page-hero">

        <div class="contact-hero-content">

            <p class="contact-small">
                GET IN TOUCH
            </p>


            <h1>
                LET'S<br>
                <span>CONNECT.</span>
            </h1>


            <p>
                Whether you want to grab a coffee,
                join an event, or simply spend time
                creating, we'd love to hear from you.
            </p>

        </div>


        <div class="contact-hero-image">

            <img
                src="images/mainpic.jpg"
                alt="Maturan's Art Cafe"
            >

        </div>

    </section>



    <!-- =========================
         CONTACT INFORMATION
    ========================== -->

    <section class="contact-info-section">

        <div class="contact-info-heading">

            <p class="section-label">
                FIND US
            </p>


            <h2>
                COME <span>VISIT.</span>
            </h2>

        </div>


        <div class="contact-info-container">


            <!-- LOCATION -->

            <div class="contact-info-card">

                <div class="contact-icon">
                    📍
                </div>


                <h3>
                    LOCATION
                </h3>


                <p>
                    Jawa, Valencia<br>
                    Negros Oriental
                </p>

            </div>



            <!-- PHONE -->

            <div class="contact-info-card">

                <div class="contact-icon">
                    ☎
                </div>


                <h3>
                    PHONE
                </h3>


                <p>
                    0958 586 8934
                </p>

            </div>



            <!-- EMAIL -->

            <div class="contact-info-card">

                <div class="contact-icon">
                    ✉
                </div>


                <h3>
                    EMAIL
                </h3>


                <p>
                    maturansartcafe@gmail.com
                </p>

            </div>

        </div>

    </section>



    <!-- =========================
         CONTACT FORM
    ========================== -->

    <section class="contact-form-section">

        <div class="contact-form-content">

            <p class="section-label">
                WE'D LOVE TO HEAR FROM YOU
            </p>


            <h2>
                SEND US A <span>MESSAGE.</span>
            </h2>


            <p>
                Have a question, suggestion, or want to
                know more about our events? Send us a message.
            </p>

        </div>



        <form
            class="contact-form"
            action="contact.php"
            method="POST"
        >

            <?php if ($success !== ""): ?>

                 <div class="contact-success">
                      <?php echo htmlspecialchars($success); ?>
                 </div>

            <?php endif; ?>


            <?php if ($error !== ""): ?>

                <div class="contact-error">
                  <?php echo htmlspecialchars($error); ?>
                </div>

            <?php endif; ?>



            <!-- NAME + EMAIL -->

            <div class="form-row">


                <div class="form-group">

                    <label for="name">
                        NAME
                    </label>


                    <input
                        type="text"
                        id="name"
                        name="name"
                        placeholder="Your name"
                        required
                    >

                </div>



                <div class="form-group">

                    <label for="email">
                        EMAIL
                    </label>


                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="Your email"
                        required
                    >

                </div>

            </div>



            <!-- SUBJECT -->

            <div class="form-group">

                <label for="subject">
                    SUBJECT
                </label>


                <input
                    type="text"
                    id="subject"
                    name="subject"
                    placeholder="What is this about?"
                    required
                >

            </div>



            <!-- MESSAGE -->

            <div class="form-group">

                <label for="message">
                    MESSAGE
                </label>


                <textarea
                    id="message"
                    name="message"
                    rows="6"
                    placeholder="Write your message..."
                    required
                ></textarea>

            </div>



            <!-- SEND BUTTON -->

            <button
                type="submit"
                class="contact-submit"
            >
                SEND MESSAGE
            </button>

        </form>

    </section>



    <!-- =========================
         FOOTER
    ========================== -->

    <footer class="footer">

        <div class="footer-content">


            <!-- FOOTER BRAND -->

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


                <a href="index.php">Home</a>
                <a href="about.php">About</a>
                <a href="menu.php">Menu</a>
                <a href="events.php">Events</a>
                <a href="contact.php">Contact</a>

            </div>

            <!-- FOOTER CONTACT -->

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