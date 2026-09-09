<header class="header">

    <div class="logo">
        <a href="index.php">
            <img src="images/logo.png" alt="Maturan's Art Cafe">
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
            <a href="profile.php">PROFILE</a>
        <?php endif; ?>
    </nav>

    <?php if (
        isset($_SESSION["user_logged_in"]) &&
        $_SESSION["user_logged_in"] === true
    ): ?>

        <?php if (basename($_SERVER["PHP_SELF"]) === "profile.php"): ?>

            <a href="logout.php" class="logout-btn">
                LOGOUT
            </a>

        <?php else: ?>

            <a href="reservation.php" class="reserve-btn">
                RESERVE A TABLE
            </a>

        <?php endif; ?>

    <?php else: ?>

        <a href="login.php" class="reserve-btn">
            RESERVE A TABLE
        </a>

    <?php endif; ?>

</header>