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

$user_id = $_SESSION["user_id"];

$error = "";
$success = "";


/* CHECK IF USER IS AN APPROVED ARTIST */
$stmt = $conn->prepare(
    "SELECT
        a.artist_id,
        a.artist_name,
        a.bio,
        a.contact
     FROM artists a
     INNER JOIN event_artists ea
        ON a.artist_id = ea.artist_id
     WHERE a.user_id = ?
       AND ea.status = 'Approved'
     LIMIT 1"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    $stmt->close();

    echo "<script>
        alert('Your artist application has not been approved yet.');
        window.location.href = 'events.php';
    </script>";
    exit();
}

$artist = $result->fetch_assoc();
$artist_id = $artist["artist_id"];

$stmt->close();


/* HANDLE ARTWORK SUBMISSION */
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $price = trim($_POST["price"] ?? "");

    /* VALIDATE TEXT FIELDS */
    if ($title === "" || $description === "" || $price === "") {

        $error = "Please complete all artwork fields.";

    } elseif (!is_numeric($price) || $price < 0) {

        $error = "Please enter a valid artwork price.";

    } elseif (!isset($_FILES["artwork_image"]) ||
              $_FILES["artwork_image"]["error"] !== UPLOAD_ERR_OK) {

        $error = "Please select an artwork image.";

    } else {

        $file = $_FILES["artwork_image"];

        $file_name = $file["name"];
        $file_tmp = $file["tmp_name"];
        $file_size = $file["size"];

        /* GET FILE EXTENSION */
        $extension = strtolower(
            pathinfo($file_name, PATHINFO_EXTENSION)
        );

        /* ALLOWED IMAGE TYPES */
        $allowed_extensions = [
            "jpg",
            "jpeg",
            "png",
            "webp"
        ];

        if (!in_array($extension, $allowed_extensions)) {

            $error = "Only JPG, JPEG, PNG, and WEBP images are allowed.";

        } elseif ($file_size > 5 * 1024 * 1024) {

            $error = "Artwork image must be 5MB or smaller.";

        } else {

            /* CREATE UNIQUE FILE NAME */
            $new_file_name =
                "artwork_" .
                $artist_id .
                "_" .
                time() .
                "_" .
                uniqid() .
                "." .
                $extension;

            $upload_folder = "artwork_images/";

            /* CREATE FOLDER IF IT DOES NOT EXIST */
            if (!is_dir($upload_folder)) {
                mkdir($upload_folder, 0777, true);
            }

            $upload_path = $upload_folder . $new_file_name;

            /* MOVE IMAGE */
            if (move_uploaded_file($file_tmp, $upload_path)) {

                /* INSERT ARTWORK AS PENDING */
                $stmt = $conn->prepare(
                    "INSERT INTO artworks
                    (
                        artist_id,
                        title,
                        description,
                        price,
                        image,
                        status
                    )
                    VALUES (?, ?, ?, ?, ?, 'Pending')"
                );

                if (!$stmt) {

                    $error = "Database error: " . $conn->error;

                    /* DELETE UPLOADED FILE IF DATABASE INSERT FAILS */
                    if (file_exists($upload_path)) {
                        unlink($upload_path);
                    }

                } else {

                    $price = (float)$price;

                    $stmt->bind_param(
                        "issds",
                        $artist_id,
                        $title,
                        $description,
                        $price,
                        $new_file_name
                    );

                    if ($stmt->execute()) {

                        $success =
                            "Your artwork has been submitted successfully! " .
                            "It is now waiting for admin approval.";

                    } else {

                        $error =
                            "Something went wrong while saving your artwork.";

                        if (file_exists($upload_path)) {
                            unlink($upload_path);
                        }
                    }

                    $stmt->close();
                }

            } else {

                $error =
                    "The artwork image could not be uploaded. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Artworks - Maturan's Art Cafe</title>

    <link rel="stylesheet" href="Css/style.css">
    <link rel="stylesheet" href="Css/artist_artworks.css">

</head>

<body>


<!-- HEADER -->

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

        <a href="contact.php">CONTACT</a>

        <a href="my_messages.php">MY MESSAGES</a>

    </nav>


    <a href="logout.php" class="reserve-btn">
        LOGOUT
    </a>

</header>


<!-- ARTIST ARTWORK PAGE -->

<section class="artist-artworks-page">

    <div class="artist-artworks-header">

        <p class="section-label">
            APPROVED ARTIST
        </p>

        <h1>
            MY <span>ARTWORKS.</span>
        </h1>

        <p>
            Welcome, <?= htmlspecialchars($artist["artist_name"]) ?>.
            Submit your artwork below for review by Maturan's Art Cafe.
        </p>

    </div>


    <!-- FORM CARD -->

    <div class="artist-artwork-form-card">

        <?php if ($success !== ""): ?>

            <div class="artist-success">
                <?= htmlspecialchars($success) ?>
            </div>

        <?php endif; ?>


        <?php if ($error !== ""): ?>

            <div class="artist-error">
                <?= htmlspecialchars($error) ?>
            </div>

        <?php endif; ?>


        <form
            action="artist_artworks.php"
            method="POST"
            enctype="multipart/form-data"
        >

            <div class="form-group">

                <label for="title">
                    ARTWORK NAME
                </label>

                <input
                    type="text"
                    id="title"
                    name="title"
                    placeholder="Enter your artwork name"
                    required
                >

            </div>


            <div class="form-group">

                <label for="description">
                    DESCRIPTION
                </label>

                <textarea
                    id="description"
                    name="description"
                    rows="6"
                    placeholder="Tell us about your artwork..."
                    required
                ></textarea>

            </div>


            <div class="form-group">

                <label for="price">
                    PRICE
                </label>

                <input
                    type="number"
                    id="price"
                    name="price"
                    min="0"
                    step="0.01"
                    placeholder="Enter artwork price"
                    required
                >

            </div>


            <div class="form-group">

                <label for="artwork_image">
                    ARTWORK IMAGE
                </label>

                <input
                    type="file"
                    id="artwork_image"
                    name="artwork_image"
                    accept=".jpg,.jpeg,.png,.webp"
                    required
                >

                <small>
                    Accepted formats: JPG, JPEG, PNG, WEBP.
                    Maximum size: 5MB.
                </small>

            </div>


            <button
                type="submit"
                class="artist-submit-button"
            >
                SUBMIT ARTWORK
            </button>

        </form>

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

    </div>

</footer>


<script src="JS/script.js"></script>

</body>
</html>