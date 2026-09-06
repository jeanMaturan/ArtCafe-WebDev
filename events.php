<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Events - Maturan's Art Cafe</title>

    <link rel="stylesheet" href="Css/style.css">
    <link rel="stylesheet" href="Css/events.css">
</head>

<body>

    <!-- HEADER -->
    <header class="header">

        <div class="logo">
            <img src="images/logo.png" alt="Maturan's Art Cafe">
        </div>

        <nav class="navbar">
            <a href="index.php">HOME</a>
            <a href="about.php">ABOUT</a>
            <a href="menu.php">MENU</a>
            <a href="events.php" class="active" style="color: #ed542c !important;">EVENTS</a>
            <a href="contact.php">CONTACT</a>

            <?php if (isset($_SESSION["user_id"])): ?>
             <a href="my_messages.php">MY MESSAGES</a>
            <?php endif; ?>
        </nav>

        <a href="login.php" class="reserve-btn">
            RESERVE A TABLE
        </a>

    </header>


    <!-- EVENTS HERO -->
    <section class="events-page-hero">

        <div class="events-hero-content">

            <p class="events-small">WHAT'S HAPPENING</p>

            <h1>
                CREATE.<br>
                <span>CONNECT.</span><br>
                ENJOY.
            </h1>

            <p class="events-description">
                Join us for creative events, relaxing evenings,
                and memorable moments with fellow artists
                and coffee lovers.
            </p>

        </div>

        <div class="events-hero-image">
            <img src="images/eventpic.png" alt="Maturan's Art Cafe Event">
        </div>

    </section>


    <!-- UPCOMING EVENTS -->
    <section class="events-section">

        <div class="events-heading">

            <p class="section-label">
                MARK YOUR CALENDAR
            </p>

            <h2>
                UPCOMING <span>EVENTS</span>
            </h2>

            <img src="images/line.png"
                 alt=""
                 class="events-line">

        </div>


        <!-- EVENT 1 -->
        <div class="event-page-card">

            <div class="event-page-image">
                <img src="images/eventpic.png"
                     alt="Paint and Sip Night">
            </div>

            <div class="event-page-info">

                <p class="event-page-label">
                    UPCOMING EVENT
                </p>

                <h3>
                    Paint & Sip<br>
                    <span>Night</span>
                </h3>

                <p class="event-page-description">
                    Paint, sip, unwind, and let your creativity
                    flow. Bring your friends and enjoy a relaxing
                    night of art, coffee, and good company.
                </p>

                <div class="event-details">

                    <p>
                        📅 December 4, 2027
                    </p>

                    <p>
                        🕔 5:00 PM
                    </p>

                    <p>
                        📍 Maturan's Art Cafe
                    </p>
                    <a href="artist_registration.php" class="reserve-btn">
                        JOIN AS AN ARTIST
                    </a>

                </div>

            </div>

        </div>

    </section>


    <!-- SPECIAL OFFER -->
    <section class="events-special">

        <div class="special-page-content">

            <p class="events-small">
                SPECIAL OFFER
            </p>

            <h2>
                20% <span>OFF</span>
            </h2>

            <h3>
                ON ALL DRINKS<br>
                & PASTRIES
            </h3>

            <p>
                Enjoy your favorite drinks and treats
                while spending time creating.
            </p>

            <div class="special-page-badge">
                FOR A LIMITED TIME ONLY!
            </div>

        </div>

        <div class="special-page-image">
            <img src="images/coffee.png"
                 alt="Coffee">
        </div>

    </section>


    <!-- EVENT CTA -->
    <section class="events-cta">

        <h2>
            COME CREATE WITH <span>US.</span>
        </h2>

        <p>
            Follow our events and join us for creative
            experiences, great coffee, and good company.
        </p>

        <a href="index.php#contact"
           class="hero-btn">
            VISIT US
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
                <a href="index.php#contact">Contact</a>

            </div>


            <div class="footer-contact">

                <h3>CONTACTS</h3>

                <p>◈ &nbsp; Jawa, Valencia Negros Oriental</p>
                <p>☎ &nbsp; 0958 586 8934</p>
                <p>✉ &nbsp; maturansartcafe@gmail.com</p>

            </div>


            <div class="footer-subscribe">

                <h3>STAY CONNECTED</h3>

                <p>
                    Subscribe to get updates on
                    new events and promos!
                </p>

                <form class="subscribe-form"
                      action="check_email.php"
                      method="POST">

                    <input
                        type="email"
                        name="email"
                        placeholder="Your email"
                        required
                    >

                    <button type="submit">→</button>

                </form>

                <img src="images/whiteheart.png"
                     alt=""
                     class="heart-small">

            </div>

        </div>

    </footer>


    <script src="JS/script.js"></script>

</body>
</html>