<?php

session_start();
require_once "db.php";
require_once "image_helper.php";


/* =====================================
   GET MERCH PRODUCTS
===================================== */

$merch_products = [];

$result = $conn->query(
    "SELECT name, description, price, image
     FROM products
     WHERE category = 'Merch' AND status = 'Active'
     ORDER BY created_at ASC"
);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $merch_products[] = $row;
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Merch | Maturan's Art Cafe</title>

    <link rel="stylesheet" href="Css/style.css">
    <link rel="stylesheet" href="Css/merch.css">
</head>

<body>

    <!-- HEADER -->
    <?php include "header.php"; ?>


    <!-- MERCH HERO -->
    <section class="merch-hero">

        <p class="merch-label">
            TAKE A PIECE OF THE CAFE HOME
        </p>

        <h1>
            OUR <span>MERCH.</span>
        </h1>

        <p class="merch-intro">
            Bring a little piece of Maturan's Art Cafe with you.
            Explore our collection of cafe-inspired merchandise.
        </p>

    </section>


    <!-- MERCH COLLECTION -->
    <section class="merch-section">

        <?php if (count($merch_products) === 0): ?>

            <p class="merch-empty-message">No merch items available right now. Check back soon!</p>

        <?php else: ?>

            <div class="merch-grid">

                <?php foreach ($merch_products as $product):
                    $image_path = resolve_catalog_image($product["image"], "product_images/");
                ?>

                    <div class="merch-card">

                        <div class="merch-image">

                            <?php if ($image_path !== null): ?>
                                <img src="<?php echo htmlspecialchars($image_path); ?>"
                                     alt="<?php echo htmlspecialchars($product["name"]); ?>">
                            <?php else: ?>
                                <div class="merch-no-image">NO IMAGE</div>
                            <?php endif; ?>

                        </div>

                        <div class="merch-info">
                            <h2><?php echo htmlspecialchars(strtoupper($product["name"])); ?></h2>

                            <?php if (!empty($product["description"])): ?>
                                <p><?php echo htmlspecialchars($product["description"]); ?></p>
                            <?php endif; ?>

                            <span class="merch-price">
                                ₱<?php echo number_format((float) $product["price"], 0); ?>
                            </span>
                        </div>

                    </div>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </section>


    <!-- BACK TO HOME -->
    <section class="merch-back">

        <a href="index.php">
            ← BACK TO HOME
        </a>

    </section>


    <!-- FOOTER -->
    <?php include "footer.php"; ?>

</body>

</html>