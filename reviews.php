<?php

session_start();
require_once "db.php";


/* =====================================
   REVIEW SUBMISSION
===================================== */

$success = "";
$error = "";


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    /* ---------------------------------
       LOGIN CHECK
    --------------------------------- */

    if (
        !isset($_SESSION["user_logged_in"]) ||
        $_SESSION["user_logged_in"] !== true ||
        !isset($_SESSION["user_id"]) ||
        !is_numeric($_SESSION["user_id"])
    ) {

        $error = "Please log in before submitting a review.";

    } else {

        $user_id = (int) $_SESSION["user_id"];

        $rating = filter_input(
            INPUT_POST,
            "rating",
            FILTER_VALIDATE_INT
        );

        $review_text = trim(
            $_POST["review_text"] ?? ""
        );


        /* ---------------------------------
           VALIDATE RATING
        --------------------------------- */

        if (
            $rating === false ||
            $rating === null ||
            $rating < 1 ||
            $rating > 5
        ) {

            $error = "Please select a rating from 1 to 5 stars.";

        }

        /* ---------------------------------
           VALIDATE REVIEW
        --------------------------------- */

        elseif ($review_text === "") {

            $error = "Please enter your review.";

        }

        elseif (strlen($review_text) < 5) {

            $error = "Your review must be at least 5 characters.";

        }

        elseif (strlen($review_text) > 2000) {

            $error = "Your review must not exceed 2000 characters.";

        }

        else {

            /* ---------------------------------
               INSERT REVIEW
            --------------------------------- */

            $stmt = $conn->prepare(
                "INSERT INTO reviews
                (user_id, rating, review_text, status)
                VALUES (?, ?, ?, 'Pending')"
            );


            if (!$stmt) {

                error_log(
                    "Review prepare failed: " .
                    $conn->error
                );

                $error =
                    "Something went wrong. Please try again.";

            } else {

                $stmt->bind_param(
                    "iis",
                    $user_id,
                    $rating,
                    $review_text
                );


                if ($stmt->execute()) {

                    $success =
                        "Thank you! Your review has been submitted and is waiting for approval.";

                } else {

                    error_log(
                        "Review insert failed: " .
                        $stmt->error
                    );

                    $error =
                        "Something went wrong. Please try again.";
                }


                $stmt->close();
            }
        }
    }
}


/* =====================================
   GET APPROVED REVIEWS
===================================== */

$approved_reviews = [];

$stmt = $conn->prepare(
    "SELECT
        r.rating,
        r.review_text,
        r.created_at,
        u.name,
        u.profile_picture
     FROM reviews r
     INNER JOIN users u
        ON r.user_id = u.user_id
     WHERE r.status = 'Approved'
     ORDER BY r.created_at DESC"
);


if ($stmt) {

    if ($stmt->execute()) {

        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {

            $approved_reviews[] = $row;
        }
    }

    $stmt->close();
}

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

    <!-- SUBMIT REVIEW -->

<section class="submit-review-section">

    <div class="submit-review-container">

        <p class="all-reviews-label">
            SHARE YOUR EXPERIENCE
        </p>

        <h2>
            LEAVE A <span>REVIEW.</span>
        </h2>

        <p>
            We would love to hear about your experience
            at Maturan's Art Cafe.
        </p>


        <?php if ($success !== ""): ?>

            <div class="review-success">
                <?php echo htmlspecialchars($success); ?>
            </div>

        <?php endif; ?>


        <?php if ($error !== ""): ?>

            <div class="review-error">
                <?php echo htmlspecialchars($error); ?>
            </div>

        <?php endif; ?>


        <?php if (
            isset($_SESSION["user_logged_in"]) &&
            $_SESSION["user_logged_in"] === true
        ): ?>

            <form method="POST" action="reviews.php">

                <div class="review-rating">

                    <label for="rating">
                        YOUR RATING
                    </label>

                    <select
                        name="rating"
                        id="rating"
                        required
                    >

                        <option value="">
                            Select a rating
                        </option>

                        <option value="5">
                            ★★★★★ - Excellent
                        </option>

                        <option value="4">
                            ★★★★ - Very Good
                        </option>

                        <option value="3">
                            ★★★ - Good
                        </option>

                        <option value="2">
                            ★★ - Fair
                        </option>

                        <option value="1">
                            ★ - Poor
                        </option>

                    </select>

                </div>


                <div class="review-message">

                    <label for="review_text">
                        YOUR REVIEW
                    </label>

                    <textarea
                        name="review_text"
                        id="review_text"
                        rows="6"
                        maxlength="2000"
                        placeholder="Tell us about your experience..."
                        required
                    ></textarea>

                </div>


                <button
                    type="submit"
                    class="submit-review-btn"
                >
                    SUBMIT REVIEW
                </button>

            </form>


        <?php else: ?>

            <div class="review-login-message">

                <p>
                    Please log in to share your experience.
                </p>

                <a href="login.php?from=review">
                    LOG IN TO WRITE A REVIEW
                </a>

            </div>

        <?php endif; ?>

    </div>

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

            <!-- APPROVED USER REVIEWS -->

<?php foreach ($approved_reviews as $review): ?>

    <div class="all-review-card">

        <div class="all-review-top">

            <img
                src="images/quote.png"
                alt=""
                class="quote-icon"
            >

            <div class="stars">
                <?php
                echo str_repeat(
                    "★",
                    (int) $review["rating"]
                );
                ?>
            </div>

        </div>


        <p class="all-review-text">
            <?php
            echo htmlspecialchars(
                $review["review_text"]
            );
            ?>
        </p>


        <div class="all-customer">

            <div class="all-customer-image user-review-avatar">

    <?php if (!empty($review["profile_picture"])): ?>

        <img
            src="<?php echo htmlspecialchars($review["profile_picture"]); ?>"
            alt="Profile Picture"
        >

    <?php else: ?>

        <?php
        echo strtoupper(
            htmlspecialchars(
                substr($review["name"], 0, 1)
            )
        );
        ?>

    <?php endif; ?>

</div>

            <div>

                <p class="all-customer-name">
                    <?php
                    echo htmlspecialchars(
                        $review["name"]
                    );
                    ?>
                </p>

                <p class="all-customer-location">
                    Customer
                </p>

            </div>

        </div>

    </div>

<?php endforeach; ?>

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