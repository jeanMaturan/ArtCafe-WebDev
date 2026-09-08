<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Menu - Maturan's Art Cafe</title>

    <link rel="stylesheet" href="Css/style.css">
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
            <a href="events.php">EVENTS</a>
            <a href="contact.php">CONTACT</a>
        </nav>

        <a href="login.php" class="reserve-btn">
            RESERVE A TABLE
        </a>

    </header>


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


    <!-- COFFEE -->
    <section class="menu-section">

        <div class="menu-section-header">
            <h2>COFFEE <span>♡</span></h2>

            <img src="images/line.png" alt="" class="menu-line">
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

        </div>

    </section>


    <!-- PASTRIES -->
    <section class="menu-section menu-pastries">

        <div class="menu-section-header">
            <h2>PASTRIES <span>♡</span></h2>

            <img src="images/line.png" alt="" class="menu-line">
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
                <a href="index.php#events">Events</a>
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