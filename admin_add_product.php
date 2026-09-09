<?php

session_start();
require_once "db.php";


/* =====================================
   ADMIN LOGIN CHECK
===================================== */

if (
    !isset($_SESSION["user_logged_in"]) ||
    $_SESSION["user_logged_in"] !== true ||
    !isset($_SESSION["user_id"]) ||
    !is_numeric($_SESSION["user_id"]) ||
    !isset($_SESSION["role"]) ||
    $_SESSION["role"] !== "admin"
) {
    header("Location: login.php");
    exit();
}


$valid_categories = ["Coffee", "Pastries", "Merch"];

$category = $_GET["category"] ?? "Coffee";
if (!in_array($category, $valid_categories, true)) {
    $category = "Coffee";
}

$name = "";
$description = "";
$price = "";
$error = "";
$success = "";


/* =====================================
   ADD PRODUCT
===================================== */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $category = trim($_POST["category"] ?? "");
    $name = trim($_POST["name"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $price = trim($_POST["price"] ?? "");

    $image_name = "";


    /* =================================
       VALIDATE TEXT FIELDS
    ================================= */

    if (!in_array($category, $valid_categories, true)) {

        $error = "Please choose a valid category.";

    } elseif ($name === "") {

        $error = "Please enter a product name.";

    } elseif (strlen($name) > 100) {

        $error = "Product name must not exceed 100 characters.";

    } elseif ($price === "" || !is_numeric($price)) {

        $error = "Please enter a valid price.";

    } elseif ((float) $price < 0) {

        $error = "Price cannot be negative.";
    }


    /* =================================
       HANDLE IMAGE (OPTIONAL)
    ================================= */

    if (
        $error === "" &&
        isset($_FILES["product_image"]) &&
        $_FILES["product_image"]["error"] !== UPLOAD_ERR_NO_FILE
    ) {

        $file = $_FILES["product_image"];

        if ($file["error"] !== UPLOAD_ERR_OK) {

            $error = "There was a problem uploading the image.";

        } else {

            $allowed_types = ["image/jpeg", "image/png", "image/webp"];
            $file_type = mime_content_type($file["tmp_name"]);

            if (!in_array($file_type, $allowed_types)) {

                $error = "Only JPG, PNG, and WEBP images are allowed.";

            } else {

                $extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
                $image_name = uniqid("product_", true) . "." . $extension;

                $upload_directory = "product_images/";

                if (!is_dir($upload_directory)) {
                    mkdir($upload_directory, 0777, true);
                }

                $upload_path = $upload_directory . $image_name;

                if (!move_uploaded_file($file["tmp_name"], $upload_path)) {
                    $error = "Failed to upload the product image.";
                }
            }
        }
    }


    /* =================================
       INSERT PRODUCT
    ================================= */

    if ($error === "") {

        $stmt = $conn->prepare(
            "INSERT INTO products
            (category, name, description, price, image, status)
            VALUES (?, ?, ?, ?, ?, 'Active')"
        );

        $price_value = (float) $price;

        $stmt->bind_param(
            "sssds",
            $category,
            $name,
            $description,
            $price_value,
            $image_name
        );

        if ($stmt->execute()) {

            $success = "Product added successfully.";
            $name = "";
            $description = "";
            $price = "";

        } else {

            if ($image_name !== "" && file_exists("product_images/" . $image_name)) {
                unlink("product_images/" . $image_name);
            }

            $error = "Failed to save product: " . $stmt->error;
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

    <title>Add Product | Maturan's Art Cafe</title>

    <link rel="stylesheet" href="Css/style.css">
    <link rel="stylesheet" href="Css/admin.css">
    <link rel="stylesheet" href="Css/admin_dashboard.css">
    <link rel="stylesheet" href="Css/admin_login.css">
    <link rel="stylesheet" href="Css/admin_add_artwork.css">

</head>

<body class="admin-dashboard-page">

<div class="dash-layout">

    <?php
    $admin_active = "products";
    include "admin_sidebar.php";
    ?>

    <div class="dash-main">

        <main class="admin-content">

            <div class="admin-page-title">
                <h2>ADD PRODUCT</h2>
                <p>Add a new coffee, pastry, or merch item to the catalog.</p>
            </div>

            <?php if ($error !== ""): ?>
                <div class="admin-message error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success !== ""): ?>
                <div class="admin-message success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <div class="admin-form-card">

                <form method="POST" enctype="multipart/form-data">

                    <div class="admin-form-group">
                        <label for="category">CATEGORY</label>
                        <select id="category" name="category" required>
                            <?php foreach ($valid_categories as $cat): ?>
                                <option value="<?php echo $cat; ?>" <?php echo $category === $cat ? "selected" : ""; ?>>
                                    <?php echo strtoupper($cat); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="admin-form-group">
                        <label for="name">PRODUCT NAME</label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            value="<?php echo htmlspecialchars($name); ?>"
                            placeholder="e.g. Caramel Macchiato"
                            required
                        >
                    </div>

                    <div class="admin-form-group">
                        <label for="description">DESCRIPTION</label>
                        <textarea
                            id="description"
                            name="description"
                            rows="4"
                            placeholder="Short description shown on the menu"
                        ><?php echo htmlspecialchars($description); ?></textarea>
                    </div>

                    <div class="admin-form-group">
                        <label for="price">PRICE (₱)</label>
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

                    <div class="admin-form-group">
                        <label for="product_image">PRODUCT IMAGE</label>
                        <input
                            type="file"
                            id="product_image"
                            name="product_image"
                            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                        >
                        <small class="admin-file-help">JPG, PNG, or WEBP. Optional.</small>
                    </div>

                    <div class="admin-form-actions">
                        <a href="admin_products.php?category=<?php echo urlencode($category); ?>" class="admin-cancel-button">CANCEL</a>
                        <button type="submit" class="admin-login-button">ADD PRODUCT</button>
                    </div>

                </form>

            </div>

        </main>

    </div>

</div>

</body>

</html>