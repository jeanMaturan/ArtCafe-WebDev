<?php
session_start();

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = $_POST["email"];
    $password = $_POST["password"];

    if ($email == "user@gmail.com" && $password == "123456") {

        $_SESSION["user_logged_in"] = true;
        $_SESSION["user_email"] = $email;

        header("Location: reserve.php");
        exit();

    } else {

        $error = "Invalid email or password.";

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

</head>

<body>

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

    </header>


    <section class="login-page">

        <div class="login-box">

            <p class="login-label">
                WELCOME BACK
            </p>

            <h1>
                LOG <span>IN.</span>
            </h1>

            <p class="login-description">
                Please log in before making a table reservation.
            </p>


            <?php if ($error != ""): ?>

                <p class="login-error">
                    <?php echo $error; ?>
                </p>

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
                        placeholder="Enter your email"
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
                        placeholder="Enter your password"
                        required
                    >

                </div>


                <button type="submit" class="login-button">
                    LOG IN
                </button>

            </form>


            <p class="login-note">
                Don't have an account?
                <a href="register.php">Create one</a>
            </p>

        </div>

    </section>

</body>
</html>