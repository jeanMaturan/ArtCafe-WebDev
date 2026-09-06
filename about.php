<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>About Us | Maturan's Art Cafe</title>

    <link rel="stylesheet" href="Css/style.css">
    <link rel="stylesheet" href="Css/about.css">
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
            <a href="about.php" class="active" style="color: #ed542c !important;">ABOUT</a>
            <a href="menu.php">MENU</a>
            <a href="events.php">EVENTS</a>
            <a href="contact.php">CONTACT</a>

            <?php if (isset($_SESSION["user_id"])): ?>
             <a href="my_messages.php">MY MESSAGES</a>
            <?php endif; ?>
        </nav>

        <a href="login.php" class="reserve-btn">RESERVE A TABLE</a>

    </header>


    <section class="about-page-hero">

        <div class="about-page-content">

            <p class="about-page-small">WELCOME TO</p>

            <h1>
                Maturan's<br>
                <span>Art Cafe</span>
            </h1>

            <p class="about-page-description">
                A cozy space where coffee, creativity, and community
                come together.
            </p>

        </div>

        <div class="about-page-image">
            <img src="images/place.png" alt="Maturan's Art Cafe">
        </div>

    </section>

    <section class="our-story">

        <div class="story-image">
            <img src="images/eventpic.png" alt="Art and coffee at Maturan's Art Cafe">
        </div>

        <div class="story-content">

            <p class="section-label">OUR STORY</p>

            <h2>
                More Than Just<br>
                <span>A Cup of Coffee</span>
            </h2>

            <p>
                Maturan's Art Cafe was created as a welcoming place where
                people can enjoy good coffee while expressing their
                creativity.
            </p>

            <p>
                We believe that a cafe can be more than a place to grab
                a drink. It can be a place to slow down, create something
                meaningful, meet new people, and simply enjoy the moment.
            </p>

        </div>

    </section>


    <section class="cafe-concept">

        <div class="concept-content">

            <p class="section-label">OUR CONCEPT</p>

            <h2>
                Sip. Create.<br>
                <span>Relax.</span>
            </h2>

            <p>
                Our cafe combines the warmth of a coffee shop with the
                freedom of an art space. From freshly prepared drinks
                to creative activities, every part of Maturan's Art Cafe
                is designed to inspire.
            </p>

        </div>

        <div class="concept-cards">

            <div class="concept-card">
                <h3>SIP</h3>
                <p>
                    Enjoy carefully prepared coffee and refreshing drinks
                    in a cozy atmosphere.
                </p>
            </div>

            <div class="concept-card">
                <h3>CREATE</h3>
                <p>
                    Explore your creativity through art, activities,
                    and creative experiences.
                </p>
            </div>

            <div class="concept-card">
                <h3>RELAX</h3>
                <p>
                    Take a break from your busy day and enjoy a comfortable
                    space with friends.
                </p>
            </div>

        </div>

    </section>


    <!-- OUR VALUES -->
    <section class="our-values">

        <div class="values-heading">

            <p class="section-label">WHAT WE VALUE</p>

            <h2>
                A Place Made<br>
                <span>For Everyone</span>
            </h2>

        </div>

        <div class="values-container">

            <div class="value-card">
                <h3>CREATIVITY</h3>
                <p>
                    We encourage everyone to express themselves and
                    discover their creative side.
                </p>
            </div>

            <div class="value-card">
                <h3>COMMUNITY</h3>
                <p>
                    We want our cafe to be a place where people connect,
                    share ideas, and make memories.
                </p>
            </div>

            <div class="value-card">
                <h3>QUALITY</h3>
                <p>
                    We strive to provide quality drinks, food, service,
                    and experiences for every guest.
                </p>
            </div>

        </div>

    </section>


    <!-- CALL TO ACTION -->
    <section class="about-cta">

        <h2>
            Come In.<br>
            <span>Stay Awhile.</span>
        </h2>

        <p>
            Whether you're here for coffee, art, or good company,
            there's always a place for you at Maturan's Art Cafe.
        </p>

        <a href="contact.php" class="hero-btn">VISIT US</a>

    </section>


    <!-- FOOTER -->
    <footer class="footer">

        <div class="footer-content">

            <div class="footer-brand">

                <img
                    src="images/logo.png"
                    alt="Maturan's Art Cafe"
                    class="footer-logo"
                >

                <p class="footer-tagline">
                    SIP. CREATE. RELAX.
                </p>

                <p class="footer-description">
                    A creative space for coffee lovers,
                    artists, and everyone in between.
                </p>

                <div class="social-icons">
                    <a href="#">F</a>
                    <a href="#">I</a>
                    <a href="#">T</a>
                </div>

            </div>


            <div class="footer-links">

                <h3>QUICK LINKS</h3>

                <a href="index.php">Home</a>
                <a href="about.php">About Us</a>
                <a href="menu.php">Menu</a>
                <a href="events.php">Events</a>
                <a href="contact.php">Contact</a>

            </div>


            <div class="footer-contact">

                <h3>CONTACT US</h3>

                <p>Dumaguete City</p>
                <p>Philippines</p>
                <p>0912 345 6789</p>
                <p>hello@maturansartcafe.com</p>

            </div>

        </div>

    </footer>

</body>

</html>