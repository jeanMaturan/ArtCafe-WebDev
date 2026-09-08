<?php

session_start();
require_once "db.php";
require_once "image_helper.php";


/* =====================================
   GET ACTIVE EVENT
===================================== */

$active_event = null;

$result = $conn->query(
    "SELECT event_name, description, event_date, event_time, location, image
     FROM events
     WHERE status = 'Active'
     ORDER BY event_id ASC
     LIMIT 1"
);

if ($result && $result->num_rows === 1) {
    $active_event = $result->fetch_assoc();
}

$event_image_path = $active_event
    ? resolve_catalog_image($active_event["image"], "event_images/")
    : null;

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
    <?php include "header.php"; ?>


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

            <img src="images/line.png" alt="" class="events-line">

        </div>


        <?php if ($active_event === null): ?>

            <p class="events-empty-message">
                No upcoming events right now. Check back soon!
            </p>

        <?php else: ?>

            <!-- ACTIVE EVENT -->
            <div class="event-page-card">

                <div class="event-page-image">

                    <?php if ($event_image_path !== null): ?>
                        <img src="<?php echo htmlspecialchars($event_image_path); ?>"
                             alt="<?php echo htmlspecialchars($active_event["event_name"]); ?>">
                    <?php else: ?>
                        <img src="images/eventpic.png" alt="Maturan's Art Cafe Event">
                    <?php endif; ?>

                </div>

                <div class="event-page-info">

                    <p class="event-page-label">
                        UPCOMING EVENT
                    </p>

                    <h3>
                        <?php echo htmlspecialchars($active_event["event_name"]); ?>
                    </h3>

                    <?php if (!empty($active_event["description"])): ?>
                        <p class="event-page-description">
                            <?php echo nl2br(htmlspecialchars($active_event["description"])); ?>
                        </p>
                    <?php endif; ?>

                    <div class="event-details">

                        <?php if (!empty($active_event["event_date"])): ?>
                            <p>
                                📅 <?php echo htmlspecialchars(date("F j, Y", strtotime($active_event["event_date"]))); ?>
                            </p>
                        <?php endif; ?>

                        <?php if (!empty($active_event["event_time"])): ?>
                            <p>
                                🕔 <?php echo htmlspecialchars(date("g:i A", strtotime($active_event["event_time"]))); ?>
                            </p>
                        <?php endif; ?>

                        <?php if (!empty($active_event["location"])): ?>
                            <p>
                                📍 <?php echo htmlspecialchars($active_event["location"]); ?>
                            </p>
                        <?php endif; ?>

                    </div>

                    <div class="event-action-buttons">

                        <a href="artist_registration.php" class="reserve-btn artist-join-btn">
                            JOIN AS AN ARTIST
                        </a>

                        <a href="artist_gallery.php" class="reserve-btn artist-gallery-btn">
                             VIEW ARTISTS & ARTWORKS
                        </a>

                    </div>

                </div>

            </div>

        <?php endif; ?>

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
            <img src="images/coffee.png" alt="Coffee">
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

        <a href="index.php#contact" class="hero-btn">
            VISIT US
        </a>

    </section>


    <!-- FOOTER -->
    <?php include "footer.php"; ?>

</body>
</html>