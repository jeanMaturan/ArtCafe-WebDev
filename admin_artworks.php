<?php

session_start();
require_once "db.php";

/* =====================================
   ADMIN LOGIN CHECK
===================================== */

if (
    !isset($_SESSION["admin_logged_in"]) ||
    $_SESSION["admin_logged_in"] !== true
) {
    header("Location: admin_login.php");
    exit();
}


/* =====================================
   GET APPROVED ARTISTS
===================================== */

$artists = [];

$stmt = $conn->prepare(
    "SELECT
        a.artist_id,
        a.artist_name
     FROM artists a
     INNER JOIN event_artists ea
        ON a.artist_id = ea.artist_id
     WHERE ea.status = 'Approved'
     ORDER BY a.artist_name ASC"
);

$stmt->execute();

$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $artists[] = $row;
}

$stmt->close();


/* =====================================
   GET EXISTING ARTWORKS
===================================== */

$artworks = [];

$stmt = $conn->prepare(
    "SELECT
        aw.artwork_id,
        aw.title,
        aw.description,
        aw.price,
        aw.image,
        aw.status,
        a.artist_name
     FROM artworks aw
     INNER JOIN artists a
        ON aw.artist_id = a.artist_id
     INNER JOIN event_artists ea
        ON a.artist_id = ea.artist_id
     WHERE ea.status = 'Approved'
     ORDER BY aw.created_at DESC"
);

$stmt->execute();

$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $artworks[] = $row;
}

$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Artwork Management | Maturan's Art Cafe
    </title>

    <link
        rel="stylesheet"
        href="Css/style.css"
    >
    <link
        rel="stylesheet"
        href="Css/admin_artworks.css"
    >

</head>


<body class="admin-dashboard-page">


    <!-- =====================================
         HEADER
    ====================================== -->

    <header class="admin-header">

        <div class="admin-header-left">

            <img
                src="images/logo.png"
                alt="Maturan's Art Cafe Logo"
                class="admin-header-logo"
            >

            <div>

                <h1>ADMIN PANEL</h1>

                <p>Maturan's Art Cafe</p>

            </div>

        </div>


        <div class="admin-header-right">

            <a
                href="admin.php"
                class="admin-nav-button"
            >
                ARTIST APPLICATIONS
            </a>

            <a
                href="admin_logout.php"
                class="admin-logout-button"
            >
                LOGOUT
            </a>

        </div>

    </header>



    <!-- =====================================
         MAIN CONTENT
    ====================================== -->

    <main class="admin-content">


        <div class="admin-page-title">

            <h2>ARTWORK MANAGEMENT</h2>

            <p>
                Manage artworks for approved artists.
            </p>

        </div>



        <!-- =====================================
             APPROVED ARTISTS
        ====================================== -->

        <section class="clean-admin-section">

            <div class="clean-section-heading">

                <h3>APPROVED ARTISTS</h3>

                <span>
                    <?php echo count($artists); ?> artists
                </span>

            </div>


            <?php if (count($artists) === 0): ?>

                <div class="clean-empty">

                    <p>
                        No approved artists yet.
                    </p>

                </div>

            <?php else: ?>


                <div class="approved-artist-list">

                    <?php foreach ($artists as $artist): ?>

                        <div class="approved-artist-row">

                            <div class="approved-artist-name">

                                <span class="artist-circle">
                                    <?php
                                    echo strtoupper(
                                        substr(
                                            $artist["artist_name"],
                                            0,
                                            1
                                        )
                                    );
                                    ?>
                                </span>

                                <strong>
                                    <?php
                                    echo htmlspecialchars(
                                        $artist["artist_name"]
                                    );
                                    ?>
                                </strong>

                            </div>


                            <a
                                href="admin_add_artwork.php?artist_id=<?php
                                echo $artist["artist_id"];
                                ?>"
                                class="add-artwork-button"
                            >
                                + ADD ARTWORK
                            </a>

                        </div>

                    <?php endforeach; ?>

                </div>


            <?php endif; ?>

        </section>



        <!-- =====================================
             ARTWORKS
        ====================================== -->

        <section class="clean-admin-section artwork-section">

            <div class="clean-section-heading">

                <h3>ARTWORKS</h3>

                <span>
                    <?php echo count($artworks); ?> artworks
                </span>

            </div>


            <?php if (count($artworks) === 0): ?>

                <div class="clean-empty">

                    <p>
                        No artworks have been added yet.
                    </p>

                </div>

            <?php else: ?>


                <div class="clean-artwork-grid">


                    <?php foreach ($artworks as $artwork): ?>

                        <div class="clean-artwork-card">


                            <?php if (
                                !empty($artwork["image"]) &&
                                file_exists(
                                    "artwork_images/" .
                                    $artwork["image"]
                                )
                            ): ?>

                                <img
                                    src="artwork_images/<?php
                                    echo htmlspecialchars(
                                        $artwork["image"]
                                    );
                                    ?>"
                                    alt="<?php
                                    echo htmlspecialchars(
                                        $artwork["title"]
                                    );
                                    ?>"
                                    class="clean-artwork-image"
                                >

                            <?php else: ?>

                                <div class="clean-no-image">
                                    NO IMAGE
                                </div>

                            <?php endif; ?>


                            <div class="clean-artwork-info">

                                <span class="clean-artist-name">

                                    <?php
                                    echo htmlspecialchars(
                                        $artwork["artist_name"]
                                    );
                                    ?>

                                </span>


                                <h4>

                                    <?php
                                    echo htmlspecialchars(
                                        $artwork["title"]
                                    );
                                    ?>

                                </h4>


                                <div class="clean-artwork-bottom">

                                    <strong>
                                        ₱<?php
                                        echo number_format(
                                            $artwork["price"],
                                            2
                                        );
                                        ?>
                                    </strong>

                                    <span class="artwork-status">
                                        <?php
                                        echo htmlspecialchars(
                                            $artwork["status"]
                                        );
                                        ?>
                                    </span>

                                </div>

                            </div>


                        </div>

                    <?php endforeach; ?>


                </div>


            <?php endif; ?>

        </section>


    </main>


</body>

</html>