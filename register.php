<?php

require_once "db.php";

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";


    // =====================================
    // CHECK EMPTY FIELDS
    // =====================================

    if (
        $name === "" ||
        $email === "" ||
        $password === "" ||
        $confirm_password === ""
    ) {

        $error = "Please fill in all fields.";

    }


    // =====================================
    // VALIDATE FULL NAME
    // =====================================

    elseif (!preg_match("/[A-Za-z]/", $name)) {

        $error = "Full name must contain letters.";

    }

    elseif (preg_match("/^[0-9]+$/", $name)) {

        $error = "Full name cannot contain numbers only.";

    }

    elseif (strlen($name) < 2) {

        $error = "Please enter a valid full name.";

    }


    // =====================================
    // VALIDATE EMAIL
    // =====================================

    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $error = "Please enter a valid email address.";

    }

    elseif (!preg_match("/[A-Za-z]/", explode("@", $email)[0])) {

        $error = "Email address cannot use numbers only before @.";

    }


    // =====================================
    // VALIDATE PASSWORD LENGTH
    // =====================================

    elseif (strlen($password) < 8) {

        $error = "Password must be at least 8 characters.";

    }


    // =====================================
    // PASSWORD MUST CONTAIN UPPERCASE
    // =====================================

    elseif (!preg_match("/[A-Z]/", $password)) {

        $error = "Password must contain at least one uppercase letter.";

    }


    // =====================================
    // PASSWORD MUST CONTAIN LOWERCASE
    // =====================================

    elseif (!preg_match("/[a-z]/", $password)) {

        $error = "Password must contain at least one lowercase letter.";

    }


    // =====================================
    // PASSWORD MUST CONTAIN NUMBER
    // =====================================

    elseif (!preg_match("/[0-9]/", $password)) {

        $error = "Password must contain at least one number.";

    }


    // =====================================
    // PASSWORD MUST CONTAIN SPECIAL CHARACTER
    // =====================================

    elseif (!preg_match("/[^A-Za-z0-9]/", $password)) {

        $error = "Password must contain at least one special character.";

    }


    // =====================================
    // CONFIRM PASSWORD
    // =====================================

    elseif ($password !== $confirm_password) {

        $error = "Passwords do not match.";

    }


    // =====================================
    // CHECK DUPLICATE EMAIL
    // =====================================

    else {

        $check = $conn->prepare(
            "SELECT user_id
             FROM users
             WHERE email = ?
             LIMIT 1"
        );

        if (!$check) {

            $error = "Database error.";

        } else {

            $check->bind_param("s", $email);
            $check->execute();

            $result = $check->get_result();

            if ($result->num_rows > 0) {

                $error = "An account with that email already exists.";

            } else {

                // =====================================
                // HASH PASSWORD
                // =====================================

                $hashed_password = password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );


                // =====================================
                // CREATE ACCOUNT
                // =====================================

                $stmt = $conn->prepare(
                    "INSERT INTO users
                    (name, email, password)
                    VALUES (?, ?, ?)"
                );

                if (!$stmt) {

                    $error = "Database error.";

                } else {

                    $stmt->bind_param(
                        "sss",
                        $name,
                        $email,
                        $hashed_password
                    );

                    if ($stmt->execute()) {

                        $success =
                            "Account created successfully! You can now log in.";

                    } else {

                        $error =
                            "Something went wrong. Please try again.";
                    }

                    $stmt->close();
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Create Account - Maturan's Art Cafe</title>

    <link rel="stylesheet" href="Css/style.css">
    <link rel="stylesheet" href="Css/login.css">

</head>

<body>

<header class="header">

    <div class="logo">
        <img src="images/logo.png" alt="Maturan's Art Cafe">
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

    <a href="login.php" class="reserve-btn">
        RESERVE A TABLE
    </a>

</header>


<section class="login-page">

    <div class="login-box">

        <h1>CREATE ACCOUNT</h1>

        <p class="login-description">
            Create an account to reserve a table at Maturan's Art Cafe.
        </p>


        <?php if (!empty($error)): ?>

            <p class="login-error">
                <?= htmlspecialchars($error) ?>
            </p>

        <?php endif; ?>


        <?php if (!empty($success)): ?>

            <p class="login-success">
                <?= htmlspecialchars($success) ?>
            </p>

        <?php endif; ?>


        <form method="POST">

            <div class="login-group">

                <label class="login-label">
                    Full Name
                </label>

                <input
                    type="text"
                    name="name"
                    required
                >

            </div>


            <div class="login-group">

                <label class="login-label">
                    Email
                </label>

                <input
                    type="email"
                    name="email"
                    required
                >

            </div>


            <div class="login-group">

                <label class="login-label">
                    Password
                </label>

                <input
                    type="password"
                    name="password"
                    required
                >

            </div>


            <div class="login-group">

                <label class="login-label">
                    Confirm Password
                </label>

                <input
                    type="password"
                    name="confirm_password"
                    required
                >

            </div>


            <button
                type="submit"
                class="login-button"
            >
                CREATE ACCOUNT
            </button>

        </form>


        <p class="login-note">
            Already have an account?
            <a href="login.php">Log in here</a>
        </p>

    </div>

</section>


    <script src="JS/script.js"></script>

</body>
</html>