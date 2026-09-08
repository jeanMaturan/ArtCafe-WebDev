<?php

session_start();
require_once "db.php";


/* =====================================
   GET APPROVED ARTISTS AND ARTWORKS
===================================== */

$sql = "
    SELECT
        a.artist_id,
        a.artist_name,
        a.bio,
        aw.artwork_id,
        aw.title,
        aw.description,
        aw.price,
        aw.image
    FROM artists a

    INNER JOIN artworks aw
        ON a.artist_id = aw.artist_id

    WHERE EXISTS (
        SELECT 1
        FROM event_artists ea
        WHERE ea.artist_id = a.artist_id
        AND ea.status = 'Approved'
    )

    AND aw.status = 'Available'

    ORDER BY
        a.artist_name ASC,
        aw.created_at DESC
";


$result = $conn->query($sql);


/* =====================================
   DATABASE ERROR HANDLING
===================================== */

if (!$result) {

    error_log(
        "Artist gallery query failed: " .
        $conn->error
    );

    $result = false;
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Artists & Artworks - Maturan's Art Cafe</title>

    <link rel="stylesheet" href="Css/style.css">
    <link rel="stylesheet" href="Css/artist_gallery.css">

</head>

<body>


<!-- HEADER -->

<?php include "header.php"; ?>



<!-- PAGE HERO -->

<section class="artist-gallery-hero">

    <div class="artist-gallery-hero-content">

        <p class="section-label">
            MEET THE ARTISTS
        </p>

        <h1>
            ARTISTS &<br>
            <span>ARTWORKS.</span>
        </h1>

        <p>
            Discover the talented artists joining us at
            Maturan's Art Cafe and explore their creative works.
        </p>

    </div>

</section>



<!-- ARTISTS -->

<section class="artist-gallery-section">

    <div class="artist-gallery-heading">

        <p class="section-label">
            CREATIVE MINDS
        </p>

        <h2>
            OUR <span>ARTISTS</span>
        </h2>

        <img
            src="images/line.png"
            alt=""
            class="artist-gallery-line"
        >

    </div>


    <?php if ($result && $result->num_rows > 0): ?>


        <?php

        $current_artist = null;

        while ($row = $result->fetch_assoc()):

            if ($current_artist !== $row["artist_id"]):

                if ($current_artist !== null):
                    ?>
                    
                    </div>
                    </div>

                    <?php
                endif;

                $current_artist = $row["artist_id"];
                ?>

                <div class="artist-gallery-artist">

                    <div class="artist-gallery-artist-info">

                        <h3>
                            <?php
                            echo htmlspecialchars($row["artist_name"]);
                            ?>
                        </h3>

                        <?php if (!empty($row["bio"])): ?>

                            <p>
                                <?php
                                echo nl2br(
                                    htmlspecialchars($row["bio"])
                                );
                                ?>
                            </p>

                        <?php endif; ?>

                    </div>


                    <div class="artist-artworks-grid">

            <?php endif; ?>


                        <!-- ARTWORK -->

                        <div class="public-artwork-card">

                            <div class="public-artwork-image">

                                <?php if (!empty($row["image"])): ?>

                                    <?php

                                    $image_path = $row["image"];

                                    if (
                                        strpos(
                                            $image_path,
                                            "artwork_images/"
                                        ) !== 0
                                    ) {
                                        $image_path =
                                            "artwork_images/" .
                                            $image_path;
                                    }

                                    ?>

                                    <img
                                        src="<?php echo htmlspecialchars($image_path); ?>"
                                        alt="<?php echo htmlspecialchars($row["title"]); ?>"
                                    >

                                <?php else: ?>

                                    <div class="no-artwork-image">
                                        NO IMAGE
                                    </div>

                                <?php endif; ?>

                            </div>


                            <div class="public-artwork-info">

                                <h4>
                                    <?php
                                    echo htmlspecialchars(
                                        $row["title"]
                                    );
                                    ?>
                                </h4>


                                <?php if (!empty($row["description"])): ?>

                                    <p>
                                        <?php
                                        echo nl2br(
                                            htmlspecialchars(
                                                $row["description"]
                                            )
                                        );
                                        ?>
                                    </p>

                                <?php endif; ?>


                                <div class="public-artwork-price">

                                    ₱<?php
                                    echo number_format(
                                        $row["price"],
                                        2
                                    );
                                    ?>

                                </div>

                            </div>

                        </div>


        <?php endwhile; ?>


                    </div>
                </div>


    <?php else: ?>

        <div class="no-artists-message">

            <h3>
                NO ARTISTS YET
            </h3>

            <p>
                Approved artists and their artworks
                will appear here.
            </p>

        </div>

    <?php endif; ?>


</section>



<!-- BACK TO EVENTS -->

<section class="artist-gallery-back">

    <a href="events.php" class="hero-btn">
        ← BACK TO EVENTS
    </a>

</section>



<!-- FOOTER -->

<?php include "footer.php"; ?>

</body>

</html>