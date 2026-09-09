<?php

session_start();
require_once "db.php";


/* =====================================
   LOGIN CHECK
===================================== */

if (
    !isset($_SESSION["user_logged_in"]) ||
    $_SESSION["user_logged_in"] !== true ||
    !isset($_SESSION["user_id"]) ||
    !is_numeric($_SESSION["user_id"])
) {
    header("Location: login.php");
    exit();
}


$user_id = (int) $_SESSION["user_id"];

$success = "";
$error = "";


/* =====================================
   GET CURRENT PROFILE
===================================== */

$stmt = $conn->prepare(
    "SELECT name, email, profile_picture
     FROM users
     WHERE user_id = ?
     LIMIT 1"
);

if (!$stmt) {
    die("Something went wrong. Please try again.");
}

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

$stmt->close();


if (!$user) {
    die("User account not found.");
}


/* =====================================
   UPLOAD PROFILE PICTURE
===================================== */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    if (
        !isset($_FILES["profile_picture"]) ||
        $_FILES["profile_picture"]["error"] !== UPLOAD_ERR_OK
    ) {
        $error = "Please select a profile picture.";

    } else {

        $file = $_FILES["profile_picture"];

        /* MAXIMUM 5 MB */

        if ($file["size"] > 5 * 1024 * 1024) {

            $error = "Profile picture must not exceed 5 MB.";

        } else {

            /* CHECK REAL IMAGE TYPE */

            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime = finfo_file($finfo, $file["tmp_name"]);
            finfo_close($finfo);


            $allowed_types = [
                "image/jpeg" => "jpg",
                "image/png"  => "png",
                "image/webp" => "webp"
            ];


            if (!isset($allowed_types[$mime])) {

                $error = "Only JPG, PNG, and WEBP images are allowed.";

            } else {

                $extension = $allowed_types[$mime];

                /* CREATE PROFILE IMAGE FOLDER */

                $upload_dir = "profile_images/";

                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }


                /* RANDOM FILE NAME */

                try {

                    $random_name =
                        bin2hex(random_bytes(16));

                } catch (Exception $e) {

                    $error =
                        "Something went wrong. Please try again.";
                }


                if ($error === "") {

                    $filename =
                        "profile_" .
                        $user_id .
                        "_" .
                        $random_name .
                        "." .
                        $extension;

                    $destination =
                        $upload_dir . $filename;


                    /* MOVE IMAGE */

                    if (
                        move_uploaded_file(
                            $file["tmp_name"],
                            $destination
                        )
                    ) {

                        /* SAVE DATABASE PATH */

                        $stmt = $conn->prepare(
                            "UPDATE users
                             SET profile_picture = ?
                             WHERE user_id = ?"
                        );


                        if (!$stmt) {

                            unlink($destination);

                            $error =
                                "Something went wrong. Please try again.";

                        } else {

                            $stmt->bind_param(
                                "si",
                                $destination,
                                $user_id
                            );


                            if ($stmt->execute()) {

                                /* DELETE OLD IMAGE */

                                if (
                                    !empty($user["profile_picture"]) &&
                                    file_exists($user["profile_picture"])
                                ) {
                                    unlink(
                                        $user["profile_picture"]
                                    );
                                }


                                $user["profile_picture"] =
                                    $destination;

                                $success =
                                    "Profile picture updated successfully.";

                            } else {

                                unlink($destination);

                                $error =
                                    "Something went wrong. Please try again.";
                            }


                            $stmt->close();
                        }

                    } else {

                        $error =
                            "Unable to upload the profile picture.";
                    }
                }
            }
        }
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

<title>My Profile - Maturan's Art Cafe</title>

<link rel="stylesheet" href="Css/style.css">
<link rel="stylesheet" href="Css/profile.css">

</head>

<body>

    <!-- HEADER -->
    <?php include "header.php"; ?>

<section class="profile-page">


<div class="profile-box">

    <?php if (!empty($user["profile_picture"])): ?>

        <img
            src="<?php echo htmlspecialchars($user["profile_picture"]); ?>"
            alt="Profile Picture"
            class="profile-picture"
        >

    <?php else: ?>

        <div class="profile-letter">
            <?php
            echo strtoupper(
                substr($user["name"], 0, 1)
            );
            ?>
        </div>

    <?php endif; ?>


    <h1>
        <?php echo htmlspecialchars($user["name"]); ?>
    </h1>

    <p>
        <?php echo htmlspecialchars($user["email"]); ?>
    </p>


    <?php if ($success !== ""): ?>

        <div class="profile-success">
            <?php echo htmlspecialchars($success); ?>
        </div>

    <?php endif; ?>


    <?php if ($error !== ""): ?>

        <div class="profile-error">
            <?php echo htmlspecialchars($error); ?>
        </div>

    <?php endif; ?>


    <form
        class="profile-upload"
        action="profile.php"
        method="POST"
        enctype="multipart/form-data"
    >

        <label for="profile_picture">
            <strong>PROFILE PICTURE</strong>
        </label>

        <br><br>

        <input
            type="file"
            id="profile_picture"
            name="profile_picture"
            accept=".jpg,.jpeg,.png,.webp"
            required
        >

        <br>

        <button type="submit">
            UPLOAD PROFILE PICTURE
        </button>

    </form>


    <a href="logout.php" class="logout-btn">
        LOGOUT
    </a>

</div>

</section>

    <script src="JS/script.js"></script>

</body>

</html>