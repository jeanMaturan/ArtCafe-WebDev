<?php

session_start();
require_once "db.php";


/* =====================================
   USER MUST BE LOGGED IN
===================================== */

if (
    !isset($_SESSION["user_logged_in"]) ||
    $_SESSION["user_logged_in"] !== true ||
    !isset($_SESSION["user_id"]) ||
    !is_numeric($_SESSION["user_id"])
) {
    header("Location: login.php?from=artist");
    exit();
}

$user_id = (int) $_SESSION["user_id"];

$error = "";
$success = "";


/* =====================================
   MAKE SURE THIS USER IS AN APPROVED ARTIST
   AND GET THEIR artist_id
===================================== */

$artist = null;

$stmt = $conn->prepare(
    "SELECT a.artist_id, a.artist_name
     FROM artists a
     INNER JOIN event_artists ea
        ON a.artist_id = ea.artist_id
     WHERE a.user_id = ?
       AND ea.status = 'Approved'
     LIMIT 1"
);

if (!$stmt) {
    die("Something went wrong. Please try again.");
}

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 1) {

    $artist = $result->fetch_assoc();

} else {

    /* Not an approved artist — send them back */
    $stmt->close();
    header("Location: artist_registration.php");
    exit();
}

$stmt->close();

$artist_id = (int) $artist["artist_id"];


/* =====================================
   HANDLE ARTWORK SUBMISSION
===================================== */

$title = "";
$description = "";
$price = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST["title"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $price = trim($_POST["price"] ?? "");

    $image_name = "";


    /* =================================
       VALIDATE TEXT FIELDS
    ================================= */

    if ($title === "") {

        $error = "Please enter an artwork title.";

    } elseif ($price === "" || !is_numeric($price)) {

        $error = "Please enter a valid price.";

    } elseif ((float) $price < 0) {

        $error = "Price cannot be negative.";

    }


    /* =================================
       HANDLE IMAGE UPLOAD
    ================================= */

    if ($error === "" && isset($_FILES["artwork_image"])) {

        $file = $_FILES["artwork_image"];

        if ($file["error"] !== UPLOAD_ERR_OK) {

            $error = "Please select an artwork image.";

        } else {

            $allowed_types = [
                "image/jpeg",
                "image/png",
                "image/webp"
            ];

            $file_type = mime_content_type($file["tmp_name"]);

            if (!in_array($file_type, $allowed_types)) {

                $error = "Only JPG, PNG, and WEBP images are allowed.";

            } else {

                $extension = strtolower(
                    pathinfo($file["name"], PATHINFO_EXTENSION)
                );

                $image_name = uniqid("artwork_", true) . "." . $extension;

                $upload_directory = "artwork_images/";

                if (!is_dir($upload_directory)) {
                    mkdir($upload_directory, 0777, true);
                }

                $upload_path = $upload_directory . $image_name;

                if (!move_uploaded_file($file["tmp_name"], $upload_path)) {

                    $error = "Failed to upload the artwork image.";
                }
            }
        }

    } elseif ($error === "" && !isset($_FILES["artwork_image"])) {

        $error = "Please select an artwork image.";
    }


    /* =================================
       INSERT ARTWORK
    ================================= */

    if ($error === "") {

        $stmt = $conn->prepare(
            "INSERT INTO artworks
            (artist_id, title, description, price, image, status)
            VALUES (?, ?, ?, ?, ?, 'Available')"
        );

        $price_value = (float) $price;

        $stmt->bind_param(
            "issds",
            $artist_id,
            $title,
            $description,
            $price_value,
            $image_name
        );

        if ($stmt->execute()) {

            $success = "Artwork added successfully!";

            /* Clear form */
            $title = "";
            $description = "";
            $price = "";

        } else {

            /* Delete uploaded image if the database insert fails */
            if (
                $image_name !== "" &&
                file_exists("artwork_images/" . $image_name)
            ) {
                unlink("artwork_images/" . $image_name);
            }

            $error = "Failed to save artwork: " . $stmt->error;
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
    <link rel="stylesheet" href="Css/artist_artworks.css">

</head>

<body>

    <!-- HEADER -->
    <?php include "header.php"; ?>


    <!-- ARTIST ARTWORKS -->
    <section class="artist-artworks-page">

        <div class="artist-artworks-header">

            <p class="section-label">WELCOME, ARTIST</p>

            <h1>
                <?php echo htmlspecialchars($artist["artist_name"]); ?>'s
                <span>Artworks</span>
            </h1>

            <p>
                Add your artwork below so guests can discover
                your work in our gallery.
            </p>

        </div>


        <div class="artist-artwork-form-card">

            <?php if ($success !== ""): ?>

                <div class="artist-success">
                    <?php echo htmlspecialchars($success); ?>
                </div>

            <?php endif; ?>


            <?php if ($error !== ""): ?>

                <div class="artist-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>

            <?php endif; ?>


            <form method="POST" enctype="multipart/form-data">

                <div class="form-group">
                    <label for="title">ARTWORK TITLE</label>
                    <input
                        type="text"
                        id="title"
                        name="title"
                        value="<?php echo htmlspecialchars($title); ?>"
                        placeholder="Enter artwork title"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="description">DESCRIPTION</label>
                    <textarea
                        id="description"
                        name="description"
                        rows="5"
                        placeholder="Enter a short description of the artwork"
                    ><?php echo htmlspecialchars($description); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="price">PRICE</label>
                    <input
                        type="number"
                        id="price"
                        name="price"
                        value="<?php echo htmlspecialchars($price); ?>"
                        placeholder="0.00"
                        min="0"
                        step="0.01"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="artwork_image">ARTWORK IMAGE</label>
                    <input
                        type="file"
                        id="artwork_image"
                        name="artwork_image"
                        accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                        required
                    >
                    <small>JPG, PNG, or WEBP only.</small>
                </div>

                <button type="submit" class="artist-submit-button">
                    ADD ARTWORK
                </button>

            </form>

        </div>

    </section>


    <!-- FOOTER -->
    <?php include "footer.php"; ?>

</body>

</html>