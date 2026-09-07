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
    <header class="header">

        <div class="logo">
            <a href="index.php">
                <img src="images/logo.png" alt="Maturan's Art Cafe Logo">
            </a>
        </div>

        <button class="menu-toggle" id="menuToggle">
            ☰
        </button>

        <nav class="navbar">
            <a href="index.php">HOME</a>
            <a href="about.php">ABOUT</a>
            <a href="menu.php">MENU</a>
            <a href="events.php">EVENTS</a>
            <a href="contact.php">CONTACT</a>

            <?php if (isset($_SESSION["user_id"])): ?>
                <a href="my_messages.php">MY MESSAGES</a>
            <?php endif; ?>
        </nav>

        <?php if (isset($_SESSION["user_logged_in"]) && $_SESSION["user_logged_in"] === true): ?>

            <a href="reservation.php" class="reserve-btn">
                RESERVE A TABLE
            </a>

        <?php else: ?>

            <a href="login.php" class="reserve-btn">
                RESERVE A TABLE
            </a>

        <?php endif; ?>

    </header>


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


            <!-- SHIRT -->
            <div class="merch-card">

                <div class="merch-image">

                    <img
                        src="images/shirt.png"
                        alt="Maturan's Art Cafe Shirt"
                    >

                </div>

                <div class="merch-info">

                    <h2>CAFE SHIRT</h2>

                    <p>
                        Wear the Maturan's Art Cafe spirit
                        wherever you go.
                    </p>

                    <span class="merch-price">
                        ₱550
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
    <footer id="contact" class="footer">

        <div class="footer-content">

            <div class="footer-brand">

                <img src="images/logo.png"
                     alt="Maturan's Art Cafe"
                     class="footer-logo">

                <p class="footer-tagline">
                    Sip. Create. Relax.
                </p>

                <p class="footer-description">
                    A cozy art cafe inspiring creativity,
                    connection, and community.
                </p>

                <div class="social-icons">
                    <a href="#" aria-label="Facebook"><img src="images/fb.png" alt="Facebook"></a>
                    <a href="#" aria-label="Instagram"><img src="images/insta.png" alt="Instagram"></a>
                    <a href="#" aria-label="Email"><img src="images/email.png" alt="Email"></a>
                </div>

            </div>


            <div class="footer-links">

                <h3>QUICK LINKS</h3>

                <a href="index.php">Home</a>
                <a href="about.php">About</a>
                <a href="menu.php">Menu</a>
                <a href="events.php">Events</a>
                <a href="contact.php">Contact</a>

            </div>


            <div class="footer-contact">

                <h3>CONTACTS</h3>

                <p><img src="images/map.png" alt="" class="footer-contact-icon"> Jawa, Valencia Negros Oriental</p>
                <p><img src="images/tele.png" alt="" class="footer-contact-icon"> 0958 586 8934</p>
                <p><img src="images/email.png" alt="" class="footer-contact-icon"> maturansartcafe@gmail.com</p>

            </div>

        </div>

    </footer>

    <script src="JS/script.js"></script>

</body>

</html>