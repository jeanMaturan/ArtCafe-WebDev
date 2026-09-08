<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Reviews | Maturan's Art Cafe</title>

    <link rel="stylesheet" href="Css/style.css">
    <link rel="stylesheet" href="Css/reviews.css">
</head>

<body>

    <!-- HEADER -->
    <header class="header">

        <div class="logo">
            <a href="index.php">
                <img src="images/logo.png" alt="Maturan's Art Cafe Logo">
            </a>
        </div>

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

        <?php if (
            isset($_SESSION["user_logged_in"]) &&
            $_SESSION["user_logged_in"] === true
        ): ?>

            <a href="reservation.php" class="reserve-btn">
                RESERVE A TABLE
            </a>

        <?php else: ?>

            <a href="login.php" class="reserve-btn">
                RESERVE A TABLE
            </a>

        <?php endif; ?>

    </header>


    <!-- REVIEWS HEADER -->
    <section class="all-reviews-header">

        <p class="all-reviews-label">
            HEAR FROM OUR GUESTS
        </p>

        <h1>
            WHAT OUR <span>GUESTS SAY.</span>
        </h1>

        <p>
            See what our guests have to say about their
            experience at Maturan's Art Cafe.
        </p>

    </section>


    <!-- ALL REVIEWS -->
    <section class="all-reviews-section">

        <div class="all-reviews-grid">


            <!-- REVIEW 1 -->
            <div class="all-review-card">

                <div class="all-review-top">
                    <img src="images/quote.png" alt="" class="quote-icon">

                    <div class="stars">
                        ★★★★★
                    </div>
                </div>

                <p class="all-review-text">
                    I love painting here! The staff are
                    friendly and the food is delicious!
                </p>

                <div class="all-customer">

                    <img
                        src="images/derpie.png"
                        alt="Dirpie"
                        class="all-customer-image"
                    >

                    <div>
                        <p class="all-customer-name">Dirpie</p>
                        <p class="all-customer-location">Valencia</p>
                    </div>

                </div>

            </div>


            <!-- REVIEW 2 -->
            <div class="all-review-card">

                <div class="all-review-top">
                    <img src="images/quote.png" alt="" class="quote-icon">

                    <div class="stars">
                        ★★★★★
                    </div>
                </div>

                <p class="all-review-text">
                    It felt like home. I highly
                    recommend!
                </p>

                <div class="all-customer">

                    <img
                        src="images/beam.png"
                        alt="Beam"
                        class="all-customer-image"
                    >

                    <div>
                        <p class="all-customer-name">Beam</p>
                        <p class="all-customer-location">Dauin</p>
                    </div>

                </div>

            </div>


            <!-- REVIEW 3 -->
            <div class="all-review-card">

                <div class="all-review-top">
                    <img src="images/quote.png" alt="" class="quote-icon">

                    <div class="stars">
                        ★★★★★
                    </div>
                </div>

                <p class="all-review-text">
                    Good coffee + cozy space?
                    Amazing.
                </p>

                <div class="all-customer">

                    <img
                        src="images/gordon.png"
                        alt="Gordon"
                        class="all-customer-image"
                    >

                    <div>
                        <p class="all-customer-name">Gordon</p>
                        <p class="all-customer-location">Tanjay</p>
                    </div>

                </div>

            </div>


            <!-- REVIEW 4 -->
            <div class="all-review-card">

                <div class="all-review-top">
                    <img src="images/quote.png" alt="" class="quote-icon">

                    <div class="stars">
                        ★★★★★
                    </div>
                </div>

                <p class="all-review-text">
                    The atmosphere is so cozy and the coffee
                    tastes amazing!
                </p>

                <div class="all-customer">

                    <img
                        src="images/beam.png"
                        alt="Mia"
                        class="all-customer-image"
                    >

                    <div>
                        <p class="all-customer-name">Mia</p>
                        <p class="all-customer-location">Dumaguete</p>
                    </div>

                </div>

            </div>


            <!-- REVIEW 5 -->
            <div class="all-review-card">

                <div class="all-review-top">
                    <img src="images/quote.png" alt="" class="quote-icon">

                    <div class="stars">
                        ★★★★★
                    </div>
                </div>

                <p class="all-review-text">
                    A beautiful place to relax, create,
                    and enjoy good food.
                </p>

                <div class="all-customer">

                    <img
                        src="images/gordon.png"
                        alt="Kyle"
                        class="all-customer-image"
                    >

                    <div>
                        <p class="all-customer-name">Kyle</p>
                        <p class="all-customer-location">Bacong</p>
                    </div>

                </div>

            </div>


            <!-- REVIEW 6 -->
            <div class="all-review-card">

                <div class="all-review-top">
                    <img src="images/quote.png" alt="" class="quote-icon">

                    <div class="stars">
                        ★★★★★
                    </div>
                </div>

                <p class="all-review-text">
                    Friendly staff, great coffee, and
                    such a creative environment.
                </p>

                <div class="all-customer">

                    <img
                        src="images/derpie.png"
                        alt="Anna"
                        class="all-customer-image"
                    >

                    <div>
                        <p class="all-customer-name">Anna</p>
                        <p class="all-customer-location">Valencia</p>
                    </div>

                </div>

            </div>


            <!-- REVIEW 7 -->
            <div class="all-review-card">

                <div class="all-review-top">
                    <img src="images/quote.png" alt="" class="quote-icon">

                    <div class="stars">
                        ★★★★★
                    </div>
                </div>

                <p class="all-review-text">
                    One of my favorite places to spend
                    a quiet afternoon.
                </p>

                <div class="all-customer">

                    <img
                        src="images/beam.png"
                        alt="Lia"
                        class="all-customer-image"
                    >

                    <div>
                        <p class="all-customer-name">Lia</p>
                        <p class="all-customer-location">Dauin</p>
                    </div>

                </div>

            </div>


            <!-- REVIEW 8 -->
            <div class="all-review-card">

                <div class="all-review-top">
                    <img src="images/quote.png" alt="" class="quote-icon">

                    <div class="stars">
                        ★★★★★
                    </div>
                </div>

                <p class="all-review-text">
                    The perfect combination of art,
                    coffee, and good vibes.
                </p>

                <div class="all-customer">

                    <img
                        src="images/gordon.png"
                        alt="Mark"
                        class="all-customer-image"
                    >

                    <div>
                        <p class="all-customer-name">Mark</p>
                        <p class="all-customer-location">Tanjay</p>
                    </div>

                </div>

            </div>


            <!-- REVIEW 9 -->
            <div class="all-review-card">

                <div class="all-review-top">
                    <img src="images/quote.png" alt="" class="quote-icon">

                    <div class="stars">
                        ★★★★★
                    </div>
                </div>

                <p class="all-review-text">
                    Amazing experience every time.
                    I'll definitely come back!
                </p>

                <div class="all-customer">

                    <img
                        src="images/derpie.png"
                        alt="John"
                        class="all-customer-image"
                    >

                    <div>
                        <p class="all-customer-name">John</p>
                        <p class="all-customer-location">Dumaguete</p>
                    </div>

                </div>

            </div>

        <!-- REVIEW 10 -->
            <div class="all-review-card">

                <div class="all-review-top">
                    <img src="images/quote.png" alt="" class="quote-icon">

                    <div class="stars">
                        ★★★★
                    </div>
                </div>

                <p class="all-review-text">
                    I love the cozy atmosphere and the art on display. The coffee is great too!
                </p>

                <div class="all-customer">

                    <img
                        src="images/derpie.png"
                        alt="Klara"
                        class="all-customer-image"
                    >

                    <div>
                        <p class="all-customer-name">Klara</p>
                        <p class="all-customer-location">Bais</p>
                    </div>

                </div>

            </div>

            <!-- REVIEW 9 -->
            <div class="all-review-card">

                <div class="all-review-top">
                    <img src="images/quote.png" alt="" class="quote-icon">

                    <div class="stars">
                        ★★★★★
                    </div>
                </div>

                <p class="all-review-text">
                    It's so very near to my place, and I love the ambiance. The staff are always welcoming and the coffee is top-notch!
                </p>

                <div class="all-customer">

                    <img
                        src="images/derpie.png"
                        alt="Shimael"
                        class="all-customer-image"
                    >

                    <div>
                        <p class="all-customer-name">Shimael</p>
                        <p class="all-customer-location">Jawa</p>
                    </div>

                </div>

            </div>

            <!-- REVIEW 9 -->
            <div class="all-review-card">

                <div class="all-review-top">
                    <img src="images/quote.png" alt="" class="quote-icon">

                    <div class="stars">
                        ★★★★★
                    </div>
                </div>

                <p class="all-review-text">
                    Perfect place to unwind and enjoy a cup of coffee while appreciating art.
                </p>

                <div class="all-customer">

                    <img
                        src="images/derpie.png"
                        alt="Khell"
                        class="all-customer-image"
                    >

                    <div>
                        <p class="all-customer-name">Khell</p>
                        <p class="all-customer-location">Liptong</p>
                    </div>

                </div>

            </div>

        </div>

    </section>


    <!-- BACK TO HOME -->
    <section class="reviews-back">

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
                    <a href="#">●</a>
                    <a href="#">◎</a>
                    <a href="#">✉</a>
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

                <p>◈ &nbsp; Jawa, Valencia Negros Oriental</p>
                <p>☎ &nbsp; 0958 586 8934</p>
                <p>✉ &nbsp; maturansartcafe@gmail.com</p>

            </div>

        </div>

    </footer>
    
</body>

</html>