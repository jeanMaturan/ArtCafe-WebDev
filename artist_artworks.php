<?php

session_start();
require_once "db.php";

if (!isset($_SESSION["user_logged_in"]) || $_SESSION["user_logged_in"] !== true) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

$error = "";
$success = "";

// Get the artist record belonging to the logged-in user
$artist_stmt = $conn->prepare(
    "SELECT artist_id, artist_name
     FROM artists
     WHERE user_id = ?"
);

$artist_stmt->bind_param("i", $user_id);
$artist_stmt->execute();

$artist_result = $artist_stmt->get_result();

if ($artist_result->num_rows !== 1) {
    die("You must register as an artist before adding artwork.");
}

$artist = $artist_result->fetch_assoc();

$artist_id = $artist["artist_id"];
$artist_name = $artist["artist_name"];

$artist_stmt->close();


// Submit artwork
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $price = trim($_POST["price"] ?? "");

    if ($title === "" || $price === "") {

        $error = "Please enter the artwork title and price.";

    } elseif (!is_numeric($price) || $price < 0) {

        $error = "Please enter a valid price.";

    } else {

        $stmt = $conn->prepare(
            "INSERT INTO artworks
            (artist_id, title, description, price)
            VALUES (?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "issd",
            $artist_id,
            $title,
            $description,
            $price
        );

        if ($stmt->execute()) {
            $success = "Artwork submitted successfully!";
        } else {
            $error = "Something went wrong. Please try again.";
        }

        $stmt->close();
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Artworks | Maturan's Art Cafe</title>

    <link rel="stylesheet" href="Css/style.css">

</head>

<body>

    <!-- HEADER -->

    <header class="header">

        <div class="logo">
            <img src="images/logo.png" alt="Maturan's Art Cafe Logo">
        </div>

        <nav class="navbar">
            <a href="index.php">HOME</a>
            <a href="about.php">ABOUT</a>
            <a href="menu.php">MENU</a>
            <a href="events.php">EVENTS</a>
            <a href="contact.php">CONTACT</a>
        </nav>

        <a href="logout.php" class="reserve-btn">
            LOGOUT
        </a>

    </header>


    <!-- ARTWORK FORM -->

    <section class="artist-registration-page">

        <div class="artist-registration-box">

            <h1>ADD YOUR ARTWORK</h1>

            <p class="artist-description">
                Welcome, <?php echo htmlspecialchars($artist_name); ?>!
                Add an artwork that you would like to display and sell
                during our event.
            </p>


            <?php if ($success !== ""): ?>

                <div class="success-message">
                    <?php echo htmlspecialchars($success); ?>
                </div>

            <?php endif; ?>


            <?php if ($error !== ""): ?>

                <div class="error-message">
                    <?php echo htmlspecialchars($error); ?>
                </div>

            <?php endif; ?>


            <form method="POST">

                <div class="login-group">

                    <label class="login-label">
                        ARTWORK TITLE
                    </label>

                    <input
                        type="text"
                        name="title"
                        placeholder="Enter artwork title"
                        required
                    >

                </div>


                <div class="login-group">

                    <label class="login-label">
                        DESCRIPTION
                    </label>

                    <textarea
                        name="description"
                        placeholder="Tell people about your artwork..."
                        rows="5"
                    ></textarea>

                </div>


                <div class="login-group">

                    <label class="login-label">
                        PRICE
                    </label>

                    <input
                        type="number"
                        name="price"
                        placeholder="Enter selling price"
                        min="0"
                        step="0.01"
                        required
                    >

                </div>


                <button type="submit" class="login-button">
                    SUBMIT ARTWORK
                </button>

            </form>

        </div>

    </section>


    <!-- FOOTER -->

    <footer class="footer">

        <p>
            © 2027 Maturan's Art Cafe. All Rights Reserved.
        </p>

    </footer>

</body>

</html>