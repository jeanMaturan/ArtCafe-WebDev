<?php

session_start();
require_once "db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($username === "" || $password === "") {

        $error = "Please enter your username and password.";

    } else {

        $stmt = $conn->prepare(
            "SELECT admin_id, username, password
             FROM admins
             WHERE username = ?"
        );

        if (!$stmt) {

            $error = "Database error: " . $conn->error;

        } else {

            $stmt->bind_param("s", $username);
            $stmt->execute();

            $result = $stmt->get_result();

            if ($result->num_rows === 1) {

                $admin = $result->fetch_assoc();

                if (password_verify($password, $admin["password"])) {

                    $_SESSION["admin_logged_in"] = true;
                    $_SESSION["admin_id"] = $admin["admin_id"];
                    $_SESSION["admin_username"] = $admin["username"];

                    header("Location: admin.php");
                    exit();

                } else {

                    $error = "Invalid username or password.";

                }

            } else {

                $error = "Invalid username or password.";

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

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Admin Login | Maturan's Art Cafe</title>

    <link rel="stylesheet" href="Css/style.css">
    <link rel="stylesheet" href="Css/admin_login.css">

</head>


<body class="admin-page">


    <!-- ADMIN LOGIN -->

    <section class="admin-login-page">

        <div class="admin-login-box">

            <img
                src="images/logo.png"
                alt="Maturan's Art Cafe Logo"
                class="admin-logo"
            >

            <h1>ADMIN LOGIN</h1>

            <p class="admin-login-subtitle">
                Maturan's Art Cafe
            </p>


            <?php if ($error !== ""): ?>

                <div class="admin-error">

                    <?php
                    echo htmlspecialchars($error);
                    ?>

                </div>

            <?php endif; ?>


            <form method="POST">


                <div class="admin-form-group">

                    <label for="username">
                        USERNAME
                    </label>

                    <input
                        type="text"
                        id="username"
                        name="username"
                        placeholder="Enter admin username"
                        required
                    >

                </div>


                <div class="admin-form-group">

                    <label for="password">
                        PASSWORD
                    </label>

                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter admin password"
                        required
                    >

                </div>


                <button
                    type="submit"
                    class="admin-login-button"
                >
                    LOGIN
                </button>


            </form>


            <a
                href="index.php"
                class="admin-back-link"
            >
                ← Back to Website
            </a>

        </div>

    </section>


</body>

</html>