<?php

session_start();

/* Prevent users from accessing the reservation
   page without logging in */

if (!isset($_SESSION["user_logged_in"]) || $_SESSION["user_logged_in"] !== true) {

    header("Location: login.php");
    exit();

}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Reserve a Table - Maturan's Art Cafe</title>

    <link rel="stylesheet" href="Css/style.css">

</head>

<body>

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

    </header>


    <section class="reservation-page">

        <div class="reservation-page-content">

            <p class="contact-small">
                PLAN YOUR VISIT
            </p>

            <h1>
                RESERVE A<br>
                <span>TABLE.</span>
            </h1>

            <p>
                Welcome back! Choose your preferred date,
                time, and number of guests below.
            </p>

        </div>


        <form class="reservation-page-form"
              action="#"
              method="POST">

            <h2>
                TABLE <span>RESERVATION</span>
            </h2>

            <p class="logged-user">
                Logged in as:
                <strong>
                    <?php echo htmlspecialchars($_SESSION["user_email"]); ?>
                </strong>
            </p>


            <div class="form-row">

                <div class="form-group">

                    <label for="reservation_date">
                        DATE
                    </label>

                    <input
                        type="date"
                        id="reservation_date"
                        name="reservation_date"
                        required
                    >

                </div>


                <div class="form-group">

                    <label for="reservation_time">
                        TIME
                    </label>

                    <input
                        type="time"
                        id="reservation_time"
                        name="reservation_time"
                        required
                    >

                </div>

            </div>


            <div class="form-group">

                <label for="guests">
                    NUMBER OF GUESTS
                </label>

                <select
                    id="guests"
                    name="guests"
                    required
                >

                    <option value="">
                        Select guests
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


            <div class="form-group">

                <label for="special_request">
                    SPECIAL REQUEST
                </label>

                <textarea
                    id="special_request"
                    name="special_request"
                    rows="5"
                    placeholder="Any special requests?"
                ></textarea>

            </div>


            <button
                type="submit"
                class="reservation-page-button"
            >
                RESERVE NOW
            </button>

        </form>

    </section>

</body>
</html>