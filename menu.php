<?php

session_start();
require_once "db.php";
require_once "image_helper.php";


/* =====================================
   GET COFFEE PRODUCTS
===================================== */

$coffee_products = [];

$result = $conn->query(
    "SELECT name, description, price, image
     FROM products
     WHERE category = 'Coffee' AND status = 'Active'
     ORDER BY created_at ASC"
);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $coffee_products[] = $row;
    }
}


/* =====================================
   GET PASTRY PRODUCTS
===================================== */

$pastry_products = [];

$result = $conn->query(
    "SELECT name, description, price, image
     FROM products
     WHERE category = 'Pastries' AND status = 'Active'
     ORDER BY created_at ASC"
);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $pastry_products[] = $row;
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Menu - Maturan's Art Cafe</title>

    <link rel="stylesheet" href="Css/style.css">
    <link rel="stylesheet" href="Css/menu.css">
</head>

<body>

    <!HEADER>
    <?php include "header.php"; ?>


    <!-- MENU HERO -->
    <section class="menu-page-hero">

        <div class="menu-hero-content">

            <p class="menu-small">OUR MENU</p>

            <h1>
                SIP.<br>
                <span>CREATE.</span><br>
                RELAX.
            </h1>

            <p class="menu-description">
                Enjoy carefully crafted drinks and delicious treats
                made for every creative moment.
            </p>

        </div>

        <div class="menu-hero-image">
            <img src="images/mainpic.jpg" alt="Maturan's Art Cafe">
        </div>

    </section>


    <!-- COFFEE MENU -->
    <section class="menu-section">

        <div class="menu-section-header">
            <h2>
                COFFEE
                <img src="images/orangeheart.png" alt="" class="heart-icon">
            </h2>

            <img src="images/line.png" alt="" class="menu-line">
        </div>


        <div class="menu-products">

            <?php if (count($coffee_products) === 0): ?>

                <p class="menu-empty-message">No coffee items available right now. Check back soon!</p>

            <?php else: ?>

                <?php foreach ($coffee_products as $product):
                    $image_path = resolve_catalog_image($product["image"], "product_images/");
                ?>

                    <div class="menu-product-card">

                        <div class="menu-product-image">

                            <?php if ($image_path !== null): ?>
                                <img src="<?php echo htmlspecialchars($image_path); ?>"
                                     alt="<?php echo htmlspecialchars($product["name"]); ?>">
                            <?php else: ?>
                                <div class="menu-no-image">NO IMAGE</div>
                            <?php endif; ?>

                        </div>

                        <div class="menu-product-info">
                            <h3><?php echo htmlspecialchars($product["name"]); ?></h3>

                            <?php if (!empty($product["description"])): ?>
                                <p><?php echo htmlspecialchars($product["description"]); ?></p>
                            <?php endif; ?>

                            <strong>₱<?php echo number_format((float) $product["price"], 0); ?></strong>
                        </div>

                    </div>

                <?php endforeach; ?>

            <?php endif; ?>

        </div>

    </section>


    <!-- PASTRIES -->
    <section class="menu-section menu-pastries">

        <div class="menu-section-header">
            <h2>
                PASTRIES
                <img src="images/orangeheart.png" alt="" class="heart-icon">
            </h2>

            <img src="images/line.png" alt="" class="menu-line">
        </div>


        <div class="menu-products">

            <?php if (count($pastry_products) === 0): ?>

                <p class="menu-empty-message">No pastries available right now. Check back soon!</p>

            <?php else: ?>

                <?php foreach ($pastry_products as $product):
                    $image_path = resolve_catalog_image($product["image"], "product_images/");
                ?>

                    <div class="menu-product-card">

                        <div class="menu-product-image">

                            <?php if ($image_path !== null): ?>
                                <img src="<?php echo htmlspecialchars($image_path); ?>"
                                     alt="<?php echo htmlspecialchars($product["name"]); ?>">
                            <?php else: ?>
                                <div class="menu-no-image">NO IMAGE</div>
                            <?php endif; ?>

                        </div>

                        <div class="menu-product-info">
                            <h3><?php echo htmlspecialchars($product["name"]); ?></h3>

                            <?php if (!empty($product["description"])): ?>
                                <p><?php echo htmlspecialchars($product["description"]); ?></p>
                            <?php endif; ?>

                            <strong>₱<?php echo number_format((float) $product["price"], 0); ?></strong>
                        </div>

                    </div>

                <?php endforeach; ?>

            <?php endif; ?>


            <!-- EXTRA MENU CARD -->
            <div class="menu-text-card">

                <p class="menu-card-label">SOMETHING SWEET</p>

                <h3>
                    MADE WITH
                    <span>LOVE</span>
                </h3>

                <p>
                    Pair your favorite coffee with
                    one of our delicious pastries.
                </p>

                <div class="menu-heart">♥</div>

            </div>


            <!-- CAFE CARD -->
            <div class="menu-text-card dark-menu-card">

                <p class="menu-card-label">TAKE A BREAK</p>

                <h3>
                    SIP.<br>
                    CREATE.<br>
                    <span>RELAX.</span>
                </h3>

                <p>
                    Good coffee, good food,
                    and a space made for creativity.
                </p>

            </div>

        </div>

    </section>


    <!-- BOTTOM CTA -->
    <section class="menu-cta">

        <h2>
            FIND YOUR
            <span>FAVORITE.</span>
        </h2>

        <p>
            Come visit Maturan's Art Cafe and enjoy
            something delicious while you create.
        </p>

        <a href="contact.php" class="hero-btn">
            VISIT US
        </a>

    </section>


    <!-- FOOTER -->
    <?php include "footer.php"; ?>

</body>
</html>