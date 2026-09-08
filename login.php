<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once "db.php";


/* CHECK WHY USER IS LOGGING IN */

$from_contact =
    isset($_GET["from"]) &&
    $_GET["from"] === "contact";

$from_artist =
    isset($_GET["from"]) &&
    $_GET["from"] === "artist";


/* IF ALREADY LOGGED IN */

if (
    isset($_SESSION["user_logged_in"]) &&
    $_SESSION["user_logged_in"] === true
) {

    if ($from_contact) {
    header("Location: contact.php");
} elseif ($from_artist) {
    header("Location: artist_registration.php");
} else {
    header("Location: reservation.php");
}

    exit();
}


$error = "";


/* LOGIN */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($email === "" || $password === "") {

        $error = "Please enter your email and password.";

    } else {

        $stmt = $conn->prepare(
            "SELECT user_id, name, email, password
             FROM users
             WHERE email = ?"
        );

        if (!$stmt) {

            $error = "Database error: " . $conn->error;

        } else {

            $stmt->bind_param("s", $email);
            $stmt->execute();

            $result = $stmt->get_result();

            if ($result->num_rows === 1) {

                $user = $result->fetch_assoc();

                if (
                    password_verify(
                        $password,
                        $user["password"]
                    )
                ) {

                    $_SESSION["user_logged_in"] = true;
                    $_SESSION["user_id"] = $user["user_id"];
                    $_SESSION["user_name"] = $user["name"];
                    $_SESSION["user_email"] = $user["email"];


                    /* REDIRECT BASED ON WHERE THEY CAME FROM */

                    if ($from_contact) {

                        header("Location: contact.php");

                    } else {

                        header("Location: reservation.php");
                    }

                    exit();

                } else {

                    $error = "Invalid email or password.";
                }

            } else {

                $error = "Invalid email or password.";
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

    <title>Login - Maturan's Art Cafe</title>

    <link rel="stylesheet" href="Css/style.css">
    <link rel="stylesheet" href="Css/login.css">

</head>

<body>

<!-- HEADER -->

<header class="header">

    <div class="logo">
        <img src="images/logo.png" alt="Maturan's Art Cafe">
    </div>

    <nav class="navbar">

        <a href="index.php">HOME</a>

        <a href="about.php">ABOUT</a>

        <a href="menu.php">MENU</a>

        <a href="events.php">EVENTS</a>

        <a href="contact.php">CONTACT</a>

    </nav>

    <?php if (isset($_SESSION["user_logged_in"]) && $_SESSION["user_logged_in"] === true): ?>

         <a href="reservation.php" class="reserve-btn">
                RESERVE A TABLE
         </a>

    <?php else: ?>

         <a href="login.php" class="reserve-btn">
             RESERVE A TABLE
         </a>

    <?php endif; ?>

</header>


<!-- LOGIN -->

<section class="login-page">

    <div class="login-box">

        <p class="login-label">
    <?php
    echo $from_contact
        ? "GET IN TOUCH"
        : ($from_artist ? "JOIN OUR ARTISTS" : "WELCOME BACK");
    ?>
</p>

<h1>
    LOGIN TO<br>
    <span>
        <?php
        echo $from_contact
            ? "MESSAGE."
            : ($from_artist ? "JOIN." : "RESERVE.");
        ?>
    </span>
</h1>

<p class="login-description">
    <?php
    echo $from_contact
        ? "Log in to your account to send a message to Maturan's Art Cafe."
        : ($from_artist
            ? "Log in to your account to apply as an artist at Maturan's Art Cafe."
            : "Log in to your account to reserve a table at Maturan's Art Cafe.");
    ?>
</p>

        <?php if ($error !== ""): ?>

            <div class="login-error">
                <?php echo htmlspecialchars($error); ?>
            </div>

        <?php endif; ?>


        <form action="login.php" method="POST">

            <div class="login-group">

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


            <div class="login-group">

                <label for="password">
                    PASSWORD
                </label>

                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Your password"
                    required
                >

            </div>


            <button
                type="submit"
                class="login-button"
            >
                LOGIN
            </button>

        </form>


        <p class="login-note">

            Don't have an account?

            <a href="register.php">
                CREATE AN ACCOUNT
            </a>

        </p>

    </div>

</section>


<!-- FOOTER -->

<footer class="footer">

    <div class="footer-content">

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

        </div>


        <div class="footer-links">

            <h3>QUICK LINKS</h3>

            <a href="index.php">Home</a>
            <a href="about.php">About</a>
            <a href="menu.php">Menu</a>
            <a href="events.php">Events</a>
            <a href="contact.php">Contact</a>

        </div>


        <div class="footer-contact">

            <h3>CONTACTS</h3>

            <p>◈ &nbsp; Jawa, Valencia Negros Oriental</p>

            <p>☎ &nbsp; 0958 586 8934</p>

            <p>✉ &nbsp; maturansartcafe@gmail.com</p>

        </div>


        <div class="footer-subscribe">

            <h3>STAY CONNECTED</h3>

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

        </div>

    </div>

</footer>


<script src="JS/script.js"></script>

</body>
</html>