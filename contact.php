<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Contact - Maturan's Art Cafe</title>

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


    <!-- CONTACT HERO -->
    <section class="contact-page-hero">

        <div class="contact-hero-content">

            <p class="contact-small">
                GET IN TOUCH
            </p>

            <h1>
                LET'S<br>
                <span>CONNECT.</span>
            </h1>

            <p>
                Whether you want to grab a coffee,
                join an event, or simply spend time
                creating, we'd love to hear from you.
            </p>

        </div>

        <div class="contact-hero-image">
            <img src="images/mainpic.jpg"
                 alt="Maturan's Art Cafe">
        </div>

    </section>


    <!-- CONTACT INFORMATION -->
    <section class="contact-info-section">

        <div class="contact-info-heading">

            <p class="section-label">
                FIND US
            </p>

            <h2>
                COME <span>VISIT.</span>
            </h2>

        </div>


        <div class="contact-info-container">

            <div class="contact-info-card">

                <div class="contact-icon">
                    📍
                </div>

                <h3>LOCATION</h3>

                <p>
                    Jawa, Valencia<br>
                    Negros Oriental
                </p>

            </div>


            <div class="contact-info-card">

                <div class="contact-icon">
                    ☎
                </div>

                <h3>PHONE</h3>

                <p>
                    0958 586 8934
                </p>

            </div>


            <div class="contact-info-card">

                <div class="contact-icon">
                    ✉
                </div>

                <h3>EMAIL</h3>

                <p>
                    maturansartcafe@gmail.com
                </p>

            </div>

        </div>

    </section>


    <!-- CONTACT + RESERVATION -->
    <section class="contact-form-section" id="reservation">

        <div class="contact-form-content">

            <p class="section-label">
                WE'D LOVE TO HEAR FROM YOU
            </p>

            <h2>
                SEND US A <span>MESSAGE.</span>
            </h2>

            <p>
                Have a question, suggestion, or want to
                know more about our events? Send us a message.
            </p>

        </div>


        <form class="contact-form" action="#" method="POST">

            <div class="form-row">

                <div class="form-group">
                    <label for="name">NAME</label>

                    <input
                        type="text"
                        id="name"
                        name="name"
                        placeholder="Your name"
                        required
                    >
                </div>


                <div class="form-group">
                    <label for="email">EMAIL</label>

                    <input
                        type="email"
                        id="email"
                        name="email"
                        placeholder="Your email"
                        required
                    >
                </div>

            </div>


            <div class="form-group">

                <label for="subject">
                    SUBJECT
                </label>

                <input
                    type="text"
                    id="subject"
                    name="subject"
                    placeholder="What is this about?"
                    required
                >

            </div>


            <div class="form-group">

                <label for="message">
                    MESSAGE
                </label>

                <textarea
                    id="message"
                    name="message"
                    rows="6"
                    placeholder="Write your message..."
                    required
                ></textarea>

            </div>


            <button type="submit" class="contact-submit">
                SEND MESSAGE
            </button>

        </form>

    </section>


    <!-- RESERVATION -->
    <section class="reservation-section">

        <div class="reservation-content">

            <p class="contact-small">
                PLAN YOUR VISIT
            </p>

            <h2>
                RESERVE A <span>TABLE.</span>
            </h2>

            <p>
                Planning to visit with friends or family?
                Reserve your table ahead of time and
                we'll have a cozy spot ready for you.
            </p>

        </div>


        <form class="reservation-form" action="#" method="POST">

            <div class="form-row">

                <div class="form-group">

                    <label for="reservation-name">
                        NAME
                    </label>

                    <input
                        type="text"
                        id="reservation-name"
                        name="reservation_name"
                        placeholder="Your name"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="reservation-phone">
                        PHONE
                    </label>

                    <input
                        type="tel"
                        id="reservation-phone"
                        name="reservation_phone"
                        placeholder="Your phone number"
                        required
                    >

                </div>

            </div>


            <div class="form-row">

                <div class="form-group">

                    <label for="date">
                        DATE
                    </label>

                    <input
                        type="date"
                        id="date"
                        name="date"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="time">
                        TIME
                    </label>

                    <input
                        type="time"
                        id="time"
                        name="time"
                        required
                    >

                </div>

            </div>


            <div class="form-group">

                <label for="guests">
                    NUMBER OF GUESTS
                </label>

                <select id="guests" name="guests" required>

                    <option value="">
                        Select number of guests
                    </option>

                    <option value="1">1 Guest</option>
                    <option value="2">2 Guests</option>
                    <option value="3">3 Guests</option>
                    <option value="4">4 Guests</option>
                    <option value="5">5 Guests</option>
                    <option value="6">6 Guests</option>
                    <option value="7">7 Guests</option>
                    <option value="8">8 Guests</option>
                    <option value="9">9 Guests</option>
                    <option value="10">10 Guests</option>

                </select>

            </div>


            <button type="submit" class="reservation-submit">
                RESERVE NOW
            </button>

        </form>

    </section>


    <!-- FOOTER -->
    <footer class="footer">

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


            <div class="footer-subscribe">

                <h3>STAY CONNECTED</h3>

                <p>
                    Subscribe to get updates on
                    new events and promos!
                </p>

                <form class="subscribe-form"
                      action="#"
                      method="POST">

                    <input
                        type="email"
                        name="email"
                        placeholder="Your email"
                        required
                    >

                    <button type="submit">
                        →
                    </button>

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