<?php
session_start();
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

        <div class="merch-grid">

            <!-- TOTE BAG -->
            <div class="merch-card">

                <div class="merch-image">
                    <img
                        src="images/tote.png"
                        alt="Maturan's Art Cafe Tote Bag"
                    >
                </div>

                <div class="merch-info">
                    <h2>TOTE BAG</h2>

                    <p>
                        Carry your everyday essentials with
                        our Maturan's Art Cafe tote bag.
                    </p>

                    <span class="merch-price">
                        ₱350
                    </span>
                </div>

            </div>


            <!-- COFFEE CUP -->
            <div class="merch-card">

                <div class="merch-image">

                    <img
                        src="images/coffeecup.png"
                        alt="Maturan's Art Cafe Coffee Cup"
                    >

                </div>

                <div class="merch-info">

                    <h2>COFFEE CUP</h2>

                    <p>
                        Enjoy your favorite coffee in our
                        signature Maturan's Art Cafe cup.
                    </p>

                    <span class="merch-price">
                        ₱280
                    </span>

                </div>

            </div>


            <!-- APRON -->
            <div class="merch-card">

                <div class="merch-image">

                    <img
                        src="images/apron.png"
                        alt="Maturan's Art Cafe Apron"
                    >

                </div>

                <div class="merch-info">

                    <h2>CAFE APRON</h2>

                    <p>
                        A simple and stylish apron inspired by
                        the creative atmosphere of our cafe.
                    </p>

                    <span class="merch-price">
                        ₱450
                    </span>

                </div>

            </div>


            <!-- STICKERS -->
            <div class="merch-card">

                <div class="merch-image">

                    <img
                        src="images/stickers.jpg"
                        alt="Maturan's Art Cafe Stickers"
                    >

                </div>

                <div class="merch-info">

                    <h2>CAFE STICKERS</h2>

                    <p>
                        Adorn your belongings with our exclusive
                        Maturan's Art Cafe stickers.
                    </p>

                    <span class="merch-price">
                        ₱100
                    </span>

                </div>

            </div>

        </div>

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