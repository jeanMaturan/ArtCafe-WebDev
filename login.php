<?php

session_start();
require_once "db.php";


/* =====================================
   CHECK WHY USER IS LOGGING IN
===================================== */

$from = $_GET["from"] ?? "";

$from_contact = ($from === "contact");
$from_artist = ($from === "artist");
$from_review = ($from === "review");


/* =====================================
   IF ALREADY LOGGED IN
===================================== */

if (
    isset($_SESSION["user_logged_in"]) &&
    $_SESSION["user_logged_in"] === true
) {

    if ($from_contact) {

    header("Location: contact.php");

} elseif ($from_artist) {

    header("Location: artist_registration.php");

} elseif ($from_review) {

    header("Location: reviews.php");

} else {

    header("Location: reservation.php");
}
exit();
}


$error = "";


/* =====================================
   LOGIN
===================================== */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";


    /* =====================================
       VALIDATE INPUT
    ===================================== */

    if ($email === "" || $password === "") {

        $error = "Please enter your email and password.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    } else {


        /* =====================================
           FIND USER
        ===================================== */

        $stmt = $conn->prepare(
            "SELECT user_id, name, email, password, role
             FROM users
             WHERE email = ?
             LIMIT 1"
        );


        if (!$stmt) {

            $error = "Database error.";

        } else {

            $stmt->bind_param("s", $email);
            $stmt->execute();

            $result = $stmt->get_result();


            /* =====================================
               CHECK ACCOUNT
            ===================================== */

            if ($result->num_rows === 1) {

                $user = $result->fetch_assoc();


                /* =====================================
                   VERIFY PASSWORD
                ===================================== */

                if (
                    password_verify(
                        $password,
                        $user["password"]
                    )
                ) {


                    /* =====================================
                       PREVENT SESSION FIXATION
                    ===================================== */

                    session_regenerate_id(true);


                    /* =====================================
                       CREATE USER SESSION
                    ===================================== */

                    $_SESSION["user_logged_in"] = true;

                    $_SESSION["user_id"] =
                        (int)$user["user_id"];

                    $_SESSION["user_name"] =
                        $user["name"];

                    $_SESSION["user_email"] =
                        $user["email"];

                    $_SESSION["role"] =
                        $user["role"];


                    /* =====================================
                       REDIRECT
                    ===================================== */

                    if ($user["role"] === "admin") {

                        header("Location: admin_dashboard.php");
                        exit();

                    }

                    if ($from_contact) {

                        header("Location: contact.php");

                    } elseif ($from_artist) {

                        header("Location: artist_registration.php");

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

<?php include "header.php"; ?>


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
            : ($from_artist
                ? "JOIN."
                : ($from_review
                    ? "REVIEW."
                    : "RESERVE."
                )
            );
        ?>
    </span>
</h1>

<p class="login-description">
    <?php
    echo $from_contact
        ? "Log in to your account to send a message to Maturan's Art Cafe."
        : ($from_artist
            ? "Log in to your account to apply as an artist at Maturan's Art Cafe."
            : ($from_review
                ? "Log in to your account to share your experience at Maturan's Art Cafe."
                : "Log in to your account to reserve a table at Maturan's Art Cafe."
            )
        );
    ?>
</p>

        <?php if ($error !== ""): ?>

            <div class="login-error">
                <?php echo htmlspecialchars($error); ?>
            </div>

        <?php endif; ?>


        <form action="login.php<?php echo $from !== "" ? "?from=" . urlencode($from) : ""; ?>" method="POST">
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

<?php $show_newsletter = true; include "footer.php"; ?>

</body>
</html>