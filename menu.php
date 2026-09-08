<?php
session_start();
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


    <!COFFEE MENU>
    <section class="menu-section">

        <div class="menu-section-header">
    <h2>
        COFFEE
        <img src="images/orangeheart.png" alt="" class="heart-icon">
    </h2>

    <img
        src="images/line.png"
        alt=""
        class="menu-line"
    >

</div>


        <div class="menu-products">

            <!-- PRODUCT 1 -->
            <div class="menu-product-card">

                <div class="menu-product-image">
                    <img src="images/caramelmacchiato.png"
                         alt="Caramel Macchiato">
                </div>

                <div class="menu-product-info">
                    <h3>Caramel Macchiato</h3>
                    <p>
                        Rich espresso with creamy milk
                        and sweet caramel.
                    </p>
                    <strong>₱140</strong>
                </div>

            </div>


            <!-- PRODUCT 2 -->
            <div class="menu-product-card">

                <div class="menu-product-image">
                    <img src="images/icedspanishlatte.png"
                         alt="Iced Spanish Latte">
                </div>

                <div class="menu-product-info">
                    <h3>Iced Spanish Latte</h3>
                    <p>
                        Smooth espresso blended with
                        creamy sweet milk over ice.
                    </p>
                    <strong>₱150</strong>
                </div>

            </div>


            <!-- PRODUCT 3 -->
            <div class="menu-product-card">

                <div class="menu-product-image">
                    <img src="images/chocolatelatte.png"
                         alt="Chocolate Latte">
                </div>

                <div class="menu-product-info">
                    <h3>Chocolate Latte</h3>
                    <p>
                        Smooth coffee combined with
                        rich and creamy chocolate.
                    </p>
                    <strong>₱115</strong>
                </div>

            </div>


            <!-- PRODUCT 5 -->
            <div class="menu-product-card">

                <div class="menu-product-image">
                    <img src="images/mochadream.jpg"
                         alt="Mocha Dream">
                </div>

                <div class="menu-product-info">
                    <h3>Mocha Dream</h3>
                    <p>
                        Rich espresso blended with
                        chocolate and creamy milk.
                    </p>
                    <strong>₱150</strong>
                </div>

            </div>


            <!-- PRODUCT 6 -->
            <div class="menu-product-card">

                <div class="menu-product-image">
                    <img src="images/icedmatchalatte.jpg"
                         alt="Iced Matcha Latte">
                </div>

                <div class="menu-product-info">
                    <h3>Iced Matcha Latte</h3>
                    <p>
                        Earthy matcha combined with
                        creamy milk for a refreshing drink.
                    </p>
                    <strong>₱160</strong>
                </div>

            </div>


            <!-- PRODUCT 7 -->
            <div class="menu-product-card">

                <div class="menu-product-image">
                    <img src="images/strawberryCreamfrappe.jpg"
                         alt="Strawberry Cream Frappe">
                </div>

                <div class="menu-product-info">
                    <h3>Strawberry Cream Frappe</h3>
                    <p>
                        Sweet strawberry blended with ice
                        and creamy milk, topped with whipped cream.
                    </p>
                    <strong>₱165</strong>
                </div>

            </div>


            <!-- PRODUCT 8 -->
            <div class="menu-product-card">

                <div class="menu-product-image">
                    <img src="images/cappucino.jpg"
                         alt="Cappuccino">
                </div>

                <div class="menu-product-info">
                    <h3>Cappuccino</h3>
                    <p>
                        Rich espresso topped with steamed
                        milk and a thick layer of creamy foam.
                    </p>
                    <strong>₱140</strong>
                </div>

            </div>


            <!-- PRODUCT 9 -->
            <div class="menu-product-card">

                <div class="menu-product-image">
                    <img src="images/coffee.png"
                         alt="Americano">
                </div>

                <div class="menu-product-info">
                    <h3>Americano</h3>
                    <p>
                        Smooth and bold espresso balanced
                        with hot water for a clean coffee flavor.
                    </p>
                    <strong>₱120</strong>
                </div>

            </div>


            <!-- PRODUCT 10 -->
            <div class="menu-product-card">

                <div class="menu-product-image">
                    <img src="images/ubelatte.jpg"
                         alt="Ube Latte">
                </div>

                <div class="menu-product-info">
                    <h3>Ube Latte</h3>
                    <p>
                        Creamy espresso blended with sweet
                        ube for a smooth Filipino-inspired latte.
                    </p>
                    <strong>₱155</strong>
                </div>

            </div>

        </div>

    </section>


    <!-- PASTRIES -->
    <section class="menu-section menu-pastries">

        <div class="menu-section-header">
    <h2>
        PASTRIES
        <img src="images/orangeheart.png" alt="" class="heart-icon">
    </h2>

    <img
        src="images/line.png"
        alt=""
        class="menu-line"
    >

</div>


        <div class="menu-products">

            <!-- PRODUCT 4 -->
            <div class="menu-product-card">

                <div class="menu-product-image">
                    <img src="images/bluberry.jpeg"
                         alt="Blueberry Cheesecake">
                </div>

                <div class="menu-product-info">
                    <h3>Blueberry Cheesecake</h3>
                    <p>
                        Creamy cheesecake topped with
                        sweet blueberry goodness.
                    </p>
                    <strong>₱120</strong>
                </div>

            </div>


            <!-- PRODUCT 11 -->
            <div class="menu-product-card">

                <div class="menu-product-image">
                    <img src="images/butterCroissant.jpg"
                         alt="Butter Croissant">
                </div>

                <div class="menu-product-info">
                    <h3>Butter Croissant</h3>
                    <p>
                        Flaky, golden pastry with a
                        rich buttery flavor.
                    </p>
                    <strong>₱95</strong>
                </div>

            </div>


            <!-- PRODUCT 12 -->
            <div class="menu-product-card">

                <div class="menu-product-image">
                    <img src="images/chocCroissant.jpg"
                         alt="Chocolate Croissant">
                </div>

                <div class="menu-product-info">
                    <h3>Chocolate Croissant</h3>
                    <p>
                        Buttery, flaky pastry filled
                        with smooth chocolate.
                    </p>
                    <strong>₱110</strong>
                </div>

            </div>


            <!-- PRODUCT 13 -->
            <div class="menu-product-card">

                <div class="menu-product-image">
                    <img src="images/cinnamonroll.jpg"
                         alt="Cinnamon Roll">
                </div>

                <div class="menu-product-info">
                    <h3>Cinnamon Roll</h3>
                    <p>
                        Soft, sweet pastry filled with
                        cinnamon and topped with creamy glaze.
                    </p>
                    <strong>₱105</strong>
                </div>

            </div>


            <!-- PRODUCT 14 -->
            <div class="menu-product-card">

                <div class="menu-product-image">
                    <img src="images/bananacake.jpg"
                         alt="Banana Cake">
                </div>

                <div class="menu-product-info">
                    <h3>Banana Cake</h3>
                    <p>
                        Soft, moist banana loaf with
                        a comforting homemade taste.
                    </p>
                    <strong>₱95</strong>
                </div>

            </div>


            <!-- PRODUCT 15 -->
            <div class="menu-product-card">

                <div class="menu-product-image">
                    <img src="images/cheeseDanish.jpg"
                         alt="Cheese Danish">
                </div>

                <div class="menu-product-info">
                    <h3>Cheese Danish</h3>
                    <p>
                        Flaky Danish pastry filled with
                        sweet and creamy cheese.
                    </p>
                    <strong>₱110</strong>
                </div>

            </div>


            <!-- PRODUCT 16 -->
            <div class="menu-product-card">

                <div class="menu-product-image">
                    <img src="images/chocCookies.jpg"
                         alt="Chocolate Chip Cookie">
                </div>

                <div class="menu-product-info">
                    <h3>Chocolate Chip Cookie</h3>
                    <p>
                        Soft-baked cookie loaded with
                        rich chocolate chips.
                    </p>
                    <strong>₱75</strong>
                </div>

            </div>


            <!-- PRODUCT 17 -->
            <div class="menu-product-card">

                <div class="menu-product-image">
                    <img src="images/coconutMuffs.jpg"
                         alt="Coconut Muffin">
                </div>

                <div class="menu-product-info">
                    <h3>Coconut Muffin</h3>
                    <p>
                        Soft and moist muffin with a lightly
                        sweet coconut flavor and toasted coconut.
                    </p>
                    <strong>₱100</strong>
                </div>

            </div>


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