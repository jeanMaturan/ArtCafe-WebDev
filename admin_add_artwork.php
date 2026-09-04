<?php

session_start();
require_once "db.php";


/* =====================================
   ADMIN LOGIN CHECK
===================================== */

if (
    !isset($_SESSION["admin_logged_in"]) ||
    $_SESSION["admin_logged_in"] !== true
) {
    header("Location: admin_login.php");
    exit();
}


/* =====================================
   GET ARTIST ID
===================================== */

$artist_id = (int)($_GET["artist_id"] ?? 0);

$error = "";
$success = "";


/* =====================================
   CHECK ARTIST
===================================== */

$artist = null;

$stmt = $conn->prepare(
    "SELECT
        a.artist_id,
        a.artist_name

     FROM artists a

     INNER JOIN event_artists ea
        ON a.artist_id = ea.artist_id

     WHERE a.artist_id = ?
     AND ea.status = 'Approved'

     LIMIT 1"
);

$stmt->bind_param("i", $artist_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows === 1) {

    $artist = $result->fetch_assoc();

} else {

    $error = "Artist not found or the artist is not approved.";
}

$stmt->close();


/* =====================================
   ADD ARTWORK
===================================== */

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    $artist !== null
) {

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

    } elseif ((float)$price < 0) {

        $error = "Price cannot be negative.";

    }


    /* =================================
       HANDLE IMAGE
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

            $file_type = mime_content_type(
                $file["tmp_name"]
            );


            if (!in_array($file_type, $allowed_types)) {

                $error =
                    "Only JPG, PNG, and WEBP images are allowed.";

            } else {

                $extension = strtolower(
                    pathinfo(
                        $file["name"],
                        PATHINFO_EXTENSION
                    )
                );


                $image_name =
                    uniqid("artwork_", true) .
                    "." .
                    $extension;


                $upload_directory =
                    "artwork_images/";


                if (
                    !is_dir($upload_directory)
                ) {

                    mkdir(
                        $upload_directory,
                        0777,
                        true
                    );
                }


                $upload_path =
                    $upload_directory .
                    $image_name;


                if (
                    !move_uploaded_file(
                        $file["tmp_name"],
                        $upload_path
                    )
                ) {

                    $error =
                        "Failed to upload the artwork image.";
                }
            }
        }

    } elseif (
        $error === "" &&
        !isset($_FILES["artwork_image"])
    ) {

        $error =
            "Please select an artwork image.";
    }


    /* =================================
       INSERT ARTWORK
    ================================= */

    if ($error === "") {

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
            VALUES (?, ?, ?, ?, ?, 'Available')"
        );


        $price_value = (float)$price;


        $stmt->bind_param(
            "issds",
            $artist_id,
            $title,
            $description,
            $price_value,
            $image_name
        );


        if ($stmt->execute()) {

            $success =
                "Artwork added successfully.";

            /* Clear form */
            $title = "";
            $description = "";
            $price = "";

        } else {

            /* Delete uploaded image if database insert fails */

            if (
                $image_name !== "" &&
                file_exists(
                    "artwork_images/" .
                    $image_name
                )
            ) {

                unlink(
                    "artwork_images/" .
                    $image_name
                );
            }


            $error =
                "Failed to save artwork: " .
                $stmt->error;
        }


        $stmt->close();
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
        Add Artwork | Maturan's Art Cafe
    </title>

    <link rel="stylesheet"href="Css/style.css">
    <link rel="stylesheet" href="Css/admin_add_artwork.css">

</head>


<body class="admin-dashboard-page">


    <!-- =====================================
         ADMIN HEADER
    ====================================== -->

    <header class="admin-header">

        <div class="admin-header-left">

            <img
                src="images/logo.png"
                alt="Maturan's Art Cafe Logo"
                class="admin-header-logo"
            >

            <div>

                <h1>
                    ADMIN PANEL
                </h1>

                <p>
                    Maturan's Art Cafe
                </p>

            </div>

        </div>


        <div class="admin-header-right">

            <span>
                Welcome,
                <?php
                echo htmlspecialchars(
                    $_SESSION["admin_username"]
                );
                ?>
            </span>

            <a
                href="admin_artworks.php"
                class="admin-logout-button"
            >
                ARTWORKS
            </a>

            <a
                href="admin_logout.php"
                class="admin-logout-button"
            >
                LOGOUT
            </a>

        </div>

    </header>



    <!-- =====================================
         CONTENT
    ====================================== -->

    <main class="admin-content">


        <div class="admin-page-title">

            <h2>
                ADD ARTWORK
            </h2>

            <?php if ($artist !== null): ?>

                <p>
                    Adding artwork for
                    <strong>
                        <?php
                        echo htmlspecialchars(
                            $artist["artist_name"]
                        );
                        ?>
                    </strong>
                </p>

            <?php endif; ?>

        </div>



        <!-- =====================================
             ERROR
        ====================================== -->

        <?php if ($error !== ""): ?>

            <div class="admin-message error">

                <?php
                echo htmlspecialchars($error);
                ?>

            </div>

        <?php endif; ?>



        <!-- =====================================
             SUCCESS
        ====================================== -->

        <?php if ($success !== ""): ?>

            <div class="admin-message success">

                <?php
                echo htmlspecialchars($success);
                ?>

            </div>

        <?php endif; ?>



        <?php if ($artist !== null): ?>


            <!-- =====================================
                 ARTWORK FORM
            ====================================== -->

            <div class="admin-form-card">

                <form
                    method="POST"
                    enctype="multipart/form-data"
                >


                    <div class="admin-form-group">

                        <label for="title">
                            ARTWORK TITLE
                        </label>

                        <input
                            type="text"
                            id="title"
                            name="title"
                            value="<?php
                            echo htmlspecialchars(
                                $title ?? ""
                            );
                            ?>"
                            placeholder="Enter artwork title"
                            required
                        >

                    </div>



                    <div class="admin-form-group">

                        <label for="description">
                            DESCRIPTION
                        </label>

                        <textarea
                            id="description"
                            name="description"
                            rows="5"
                            placeholder="Enter a short description of the artwork"
                        ><?php
                        echo htmlspecialchars(
                            $description ?? ""
                        );
                        ?></textarea>

                    </div>



                    <div class="admin-form-group">

                        <label for="price">
                            PRICE
                        </label>

                        <input
                            type="number"
                            id="price"
                            name="price"
                            value="<?php
                            echo htmlspecialchars(
                                $price ?? ""
                            );
                            ?>"
                            placeholder="0.00"
                            min="0"
                            step="0.01"
                            required
                        >

                    </div>



                    <div class="admin-form-group">

                        <label for="artwork_image">
                            ARTWORK IMAGE
                        </label>

                        <input
                            type="file"
                            id="artwork_image"
                            name="artwork_image"
                            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                            required
                        >

                        <small class="admin-file-help">
                            JPG, PNG, or WEBP only.
                        </small>

                    </div>



                    <div class="admin-form-actions">

                        <a
                            href="admin_artworks.php"
                            class="admin-cancel-button"
                        >
                            CANCEL
                        </a>

                        <button
                            type="submit"
                            class="admin-login-button"
                        >
                            ADD ARTWORK
                        </button>

                    </div>


                </form>

            </div>


        <?php endif; ?>


    </main>


</body>

</html>