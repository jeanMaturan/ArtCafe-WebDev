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

$valid_categories = ["Coffee", "Pastries", "Merch"];

$product_id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if ($product_id === false || $product_id === null || $product_id <= 0) {
    header("Location: admin_products.php");
    exit();
}

$error = "";
$success = "";


/* =====================================
   LOAD EXISTING PRODUCT
===================================== */

$stmt = $conn->prepare(
    "SELECT product_id, category, name, description, price, image, status
     FROM products WHERE product_id = ?"
);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    header("Location: admin_products.php");
    exit();
}

$product = $result->fetch_assoc();
$stmt->close();

$category = $product["category"];
$name = $product["name"];
$description = $product["description"];
$price = $product["price"];
$current_image = $product["image"];


/* =====================================
   UPDATE PRODUCT
===================================== */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $category = trim($_POST["category"] ?? "");
    $name = trim($_POST["name"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $price = trim($_POST["price"] ?? "");

    $image_name = $current_image;


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
       HANDLE NEW IMAGE (OPTIONAL REPLACE)
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
                $new_image_name = uniqid("product_", true) . "." . $extension;

                $upload_directory = "product_images/";

                if (!is_dir($upload_directory)) {
                    mkdir($upload_directory, 0777, true);
                }

                if (move_uploaded_file($file["tmp_name"], $upload_directory . $new_image_name)) {

                    /* Delete the old image now that the new one is saved */

                    if ($current_image !== "" && file_exists($upload_directory . $current_image)) {
                        unlink($upload_directory . $current_image);
                    }

                    $image_name = $new_image_name;

                } else {

                    $error = "Failed to upload the product image.";
                }
            }
        }
    }


    /* =================================
       SAVE CHANGES
    ================================= */

    if ($error === "") {

        $stmt = $conn->prepare(
            "UPDATE products
             SET category = ?, name = ?, description = ?, price = ?, image = ?
             WHERE product_id = ?"
        );

        $price_value = (float) $price;

        $stmt->bind_param(
            "sssdsi",
            $category,
            $name,
            $description,
            $price_value,
            $image_name,
            $product_id
        );

        if ($stmt->execute()) {

            $success = "Product updated successfully.";
            $current_image = $image_name;

        } else {

            $error = "Failed to update product: " . $stmt->error;
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

    <title>Edit Product | Maturan's Art Cafe</title>

    <link rel="stylesheet" href="Css/style.css">
    <link rel="stylesheet" href="Css/admin.css">
    <link rel="stylesheet" href="Css/admin_dashboard.css">
    <link rel="stylesheet" href="Css/admin_login.css">
    <link rel="stylesheet" href="Css/admin_add_artwork.css">
    <link rel="stylesheet" href="Css/admin_products.css">

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
                <h2>EDIT PRODUCT</h2>
                <p>Update the details for this item.</p>
            </div>

            <?php if ($error !== ""): ?>
                <div class="admin-message error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <?php if ($success !== ""): ?>
                <div class="admin-message success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <div class="admin-form-card">

                <?php if (!empty($current_image) && file_exists("product_images/" . $current_image)): ?>

                    <img
                        src="product_images/<?php echo htmlspecialchars($current_image); ?>"
                        alt="<?php echo htmlspecialchars($name); ?>"
                        class="product-current-image"
                    >

                <?php endif; ?>

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
                            required
                        >
                    </div>

                    <div class="admin-form-group">
                        <label for="description">DESCRIPTION</label>
                        <textarea
                            id="description"
                            name="description"
                            rows="4"
                        ><?php echo htmlspecialchars($description ?? ""); ?></textarea>
                    </div>

                    <div class="admin-form-group">
                        <label for="price">PRICE (₱)</label>
                        <input
                            type="number"
                            id="price"
                            name="price"
                            value="<?php echo htmlspecialchars($price); ?>"
                            min="0"
                            step="0.01"
                            required
                        >
                    </div>

                    <div class="admin-form-group">
                        <label for="product_image">REPLACE IMAGE</label>
                        <input
                            type="file"
                            id="product_image"
                            name="product_image"
                            accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                        >
                        <small class="admin-file-help">Leave empty to keep the current image.</small>
                    </div>

                    <div class="admin-form-actions">
                        <a href="admin_products.php?category=<?php echo urlencode($category); ?>" class="admin-cancel-button">CANCEL</a>
                        <button type="submit" class="admin-login-button">SAVE CHANGES</button>
                    </div>

                </form>

            </div>

        </main>

    </div>

</div>

</body>

</html>