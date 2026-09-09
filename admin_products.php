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

$success = "";
$error = "";

if (isset($_SESSION["flash_success"])) {
    $success = $_SESSION["flash_success"];
    unset($_SESSION["flash_success"]);
}
if (isset($_SESSION["flash_error"])) {
    $error = $_SESSION["flash_error"];
    unset($_SESSION["flash_error"]);
}


/* =====================================
   DELETE PRODUCT
===================================== */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $product_id = filter_input(
        INPUT_POST,
        "product_id",
        FILTER_VALIDATE_INT
    );

    $action = trim($_POST["action"] ?? "");

    if ($product_id === false || $product_id === null || $product_id <= 0) {

        $error = "Invalid product.";

    } elseif ($action === "delete") {

        /* Look up the image first so we can remove the file too */

        $img_stmt = $conn->prepare(
            "SELECT image FROM products WHERE product_id = ?"
        );
        $img_stmt->bind_param("i", $product_id);
        $img_stmt->execute();
        $img_result = $img_stmt->get_result();
        $product_row = $img_result->fetch_assoc();
        $img_stmt->close();

        $stmt = $conn->prepare(
            "DELETE FROM products WHERE product_id = ?"
        );
        $stmt->bind_param("i", $product_id);

        if ($stmt->execute()) {

            if (
                $product_row &&
                !empty($product_row["image"]) &&
                file_exists("product_images/" . $product_row["image"])
            ) {
                unlink("product_images/" . $product_row["image"]);
            }

            $error = "";
            $success = "Product deleted successfully.";

        } else {

            $error = "Something went wrong. Please try again.";
        }

        $stmt->close();

    } elseif ($action === "toggle_status") {

        $stmt = $conn->prepare(
            "UPDATE products
             SET status = IF(status = 'Active', 'Inactive', 'Active')
             WHERE product_id = ?"
        );
        $stmt->bind_param("i", $product_id);

        if ($stmt->execute()) {
            $success = "Product status updated.";
        } else {
            $error = "Something went wrong. Please try again.";
        }

        $stmt->close();
    }

    $_SESSION["flash_success"] = $success;
    $_SESSION["flash_error"] = $error;

    header("Location: admin_products.php" . (isset($_GET["category"]) ? "?category=" . urlencode($_GET["category"]) : ""));
    exit();
}


/* =====================================
   ACTIVE CATEGORY TAB
===================================== */

$valid_categories = ["Coffee", "Pastries", "Merch"];
$active_category = $_GET["category"] ?? "Coffee";

if (!in_array($active_category, $valid_categories, true)) {
    $active_category = "Coffee";
}


/* =====================================
   GET PRODUCTS FOR ACTIVE CATEGORY
===================================== */

$products = [];

$stmt = $conn->prepare(
    "SELECT product_id, name, description, price, image, status, created_at
     FROM products
     WHERE category = ?
     ORDER BY created_at DESC"
);
$stmt->bind_param("s", $active_category);
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $products[] = $row;
}
$stmt->close();


/* =====================================
   CATEGORY COUNTS (for tab badges)
===================================== */

$category_counts = ["Coffee" => 0, "Pastries" => 0, "Merch" => 0];

$count_result = $conn->query(
    "SELECT category, COUNT(*) AS c FROM products GROUP BY category"
);

if ($count_result) {
    while ($row = $count_result->fetch_assoc()) {
        $category_counts[$row["category"]] = (int) $row["c"];
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Product Management | Maturan's Art Cafe</title>

    <link rel="stylesheet" href="Css/style.css">
    <link rel="stylesheet" href="Css/admin.css">
    <link rel="stylesheet" href="Css/admin_dashboard.css">
    <link rel="stylesheet" href="Css/admin_artworks.css">
    <link rel="stylesheet" href="Css/admin_products.css">

</head>

<body class="admin-dashboard-page">

<div class="dash-layout">

    <?php
    $active_page = "products";
    include "admin_sidebar.php";
    ?>

    <div class="dash-main">

        <main class="admin-content">

            <div class="admin-page-title product-page-title">

                <div>
                    <h2>PRODUCT MANAGEMENT</h2>
                    <p>Add and manage coffee, pastries, and merch available at the cafe.</p>
                </div>

                <a href="admin_add_product.php?category=<?php echo urlencode($active_category); ?>" class="add-artwork-button product-add-button">
                    + ADD PRODUCT
                </a>

            </div>


            <?php if ($success !== ""): ?>
                <div class="admin-message success"><?php echo htmlspecialchars($success); ?></div>
            <?php endif; ?>

            <?php if ($error !== ""): ?>
                <div class="admin-message error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>


            <!-- CATEGORY TABS -->

            <div class="product-tabs">

                <?php foreach ($valid_categories as $cat): ?>

                    <a
                        href="admin_products.php?category=<?php echo urlencode($cat); ?>"
                        class="product-tab<?php echo $active_category === $cat ? " active" : ""; ?>"
                    >
                        <?php echo strtoupper($cat); ?>
                        <span><?php echo $category_counts[$cat]; ?></span>
                    </a>

                <?php endforeach; ?>

            </div>


            <section class="clean-admin-section artwork-section">

                <?php if (count($products) === 0): ?>

                    <div class="clean-empty">
                        <p>No <?php echo strtolower($active_category); ?> items yet. Click "Add Product" to create one.</p>
                    </div>

                <?php else: ?>

                    <div class="clean-artwork-grid">

                        <?php foreach ($products as $product): ?>

                            <div class="clean-artwork-card">

                                <?php if (!empty($product["image"]) && file_exists("product_images/" . $product["image"])): ?>

                                    <img
                                        src="product_images/<?php echo htmlspecialchars($product["image"]); ?>"
                                        alt="<?php echo htmlspecialchars($product["name"]); ?>"
                                        class="clean-artwork-image"
                                    >

                                <?php else: ?>

                                    <div class="clean-no-image">NO IMAGE</div>

                                <?php endif; ?>


                                <div class="clean-artwork-info">

                                    <h4><?php echo htmlspecialchars($product["name"]); ?></h4>

                                    <?php if (!empty($product["description"])): ?>
                                        <p class="artwork-description"><?php echo nl2br(htmlspecialchars($product["description"])); ?></p>
                                    <?php endif; ?>

                                    <div class="clean-artwork-bottom">

                                        <strong>₱<?php echo number_format((float) $product["price"], 2); ?></strong>

                                        <span class="artwork-status <?php echo $product["status"] === "Active" ? "approved-status" : "rejected-status"; ?>">
                                            <?php echo strtoupper($product["status"]); ?>
                                        </span>

                                    </div>

                                    <div class="artwork-admin-actions">

                                        <a
                                            href="admin_edit_product.php?id=<?php echo (int) $product["product_id"]; ?>"
                                            class="artwork-approve-button product-edit-link"
                                        >
                                            EDIT
                                        </a>

                                        <form method="POST" onsubmit="return confirm('Toggle this product\'s status?');">
                                            <input type="hidden" name="product_id" value="<?php echo (int) $product["product_id"]; ?>">
                                            <input type="hidden" name="action" value="toggle_status">
                                            <button type="submit" class="product-toggle-button">
                                                <?php echo $product["status"] === "Active" ? "DEACTIVATE" : "ACTIVATE"; ?>
                                            </button>
                                        </form>

                                        <form method="POST" onsubmit="return confirm('Delete this product? This cannot be undone.');">
                                            <input type="hidden" name="product_id" value="<?php echo (int) $product["product_id"]; ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <button type="submit" class="artwork-reject-button">DELETE</button>
                                        </form>

                                    </div>

                                </div>

                            </div>

                        <?php endforeach; ?>

                    </div>

                <?php endif; ?>

            </section>

        </main>

    </div>

</div>

</body>

</html>