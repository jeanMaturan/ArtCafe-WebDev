<footer id="contact" class="footer">

        <div class="footer-content">

            <!-- FOOTER BRAND -->
            <div class="footer-brand">

                <img
                    src="images/logo.png"
                    alt="Maturan's Art Cafe"
                    class="footer-logo"
                >

                <p class="footer-tagline">
                    Sip. Create. Relax.
                </p>

                <p class="footer-description">
                    A cozy art cafe inspiring creativity,
                    connection, and community.
                </p>

                <div class="social-icons">

                    <a href="#" aria-label="Facebook">
                        <img src="images/fb.png" alt="Facebook">
                    </a>

                    <a href="#" aria-label="Instagram">
                        <img src="images/insta.png" alt="Instagram">
                    </a>

                    <a href="#" aria-label="Email">
                        <img src="images/email.png" alt="Email">
                    </a>

                </div>

            </div>


            <!-- QUICK LINKS -->
            <div class="footer-links">

                <h3>QUICK LINKS</h3>

                <a href="index.php">Home</a>
                <a href="about.php">About</a>
                <a href="menu.php">Menu</a>
                <a href="events.php">Events</a>
                <a href="contact.php">Contact</a>

            </div>


            <!-- FOOTER CONTACT -->
            <div class="footer-contact">

                <h3>CONTACTS</h3>

                <p>
                    <img src="images/map.png" alt="" class="footer-contact-icon"> Jawa, Valencia Negros Oriental
                </p>

                <p>
                    <img src="images/tele.png" alt="" class="footer-contact-icon"> 0958 586 8934
                </p>

                <p>
                    <img src="images/email.png" alt="" class="footer-contact-icon"> maturansartcafe@gmail.com
                </p>

            </div>


            <?php if (!empty($show_newsletter)): ?>
            <!-- STAY CONNECTED -->
            <div class="footer-subscribe">

                <h3>STAY CONNECTED</h3>

                <p>
                    Subscribe to get updates on
                    new events and promos!
                </p>

                <form class="subscribe-form" id="subscribeForm">

                    <input
                        type="email"
                        name="email"
                        id="subscribeEmail"
                        placeholder="Your email"
                        required
                    >

                    <button type="submit">→</button>

                </form>

                <p
                    class="subscribe-message"
                    id="subscribeMessage"
                    style="display: none;"
                ></p>

                <img
                    src="images/whiteheart.png"
                    alt=""
                    class="heart-small">

            </div>
            <?php endif; ?>

        </div>

    </footer>

    <script src="JS/script.js"></script>