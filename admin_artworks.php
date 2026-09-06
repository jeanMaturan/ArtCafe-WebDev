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


$success = "";
$error = "";


/* =====================================
   APPROVE / REJECT ARTWORK
===================================== */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $artwork_id = (int)($_POST["artwork_id"] ?? 0);
    $action = $_POST["action"] ?? "";


    if ($artwork_id <= 0) {

        $error = "Invalid artwork.";

    } elseif ($action === "approve") {

        $stmt = $conn->prepare(
            "UPDATE artworks
             SET status = 'Available'
             WHERE artwork_id = ?"
        );

        $stmt->bind_param(
            "i",
            $artwork_id
        );

        if ($stmt->execute()) {

            $success = "Artwork approved successfully.";

        } else {

            $error = "Failed to approve artwork.";

        }

        $stmt->close();


    } elseif ($action === "reject") {

        $stmt = $conn->prepare(
            "UPDATE artworks
             SET status = 'Rejected'
             WHERE artwork_id = ?"
        );

        $stmt->bind_param(
            "i",
            $artwork_id
        );

        if ($stmt->execute()) {

            $success = "Artwork rejected.";

        } else {

            $error = "Failed to reject artwork.";

        }

        $stmt->close();

    }
}


/* =====================================
   GET PENDING ARTWORKS
===================================== */

$pending_artworks = [];

$stmt = $conn->prepare(
    "SELECT
        aw.artwork_id,
        aw.title,
        aw.description,
        aw.price,
        aw.image,
        aw.status,
        aw.created_at,
        a.artist_name
     FROM artworks aw
     INNER JOIN artists a
        ON aw.artist_id = a.artist_id
     INNER JOIN event_artists ea
        ON a.artist_id = ea.artist_id
     WHERE aw.status = 'Pending'
       AND ea.status = 'Approved'
     ORDER BY aw.created_at DESC"
);

$stmt->execute();

$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {

    $pending_artworks[] = $row;

}

$stmt->close();


/* =====================================
   GET APPROVED ARTWORKS
===================================== */

$approved_artworks = [];

$stmt = $conn->prepare(
    "SELECT
        aw.artwork_id,
        aw.title,
        aw.description,
        aw.price,
        aw.image,
        aw.status,
        aw.created_at,
        a.artist_name
     FROM artworks aw
     INNER JOIN artists a
        ON aw.artist_id = a.artist_id
     INNER JOIN event_artists ea
        ON a.artist_id = ea.artist_id
     WHERE aw.status = 'Available'
       AND ea.status = 'Approved'
     ORDER BY aw.created_at DESC"
);

$stmt->execute();

$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {

    $approved_artworks[] = $row;

}

$stmt->close();


/* =====================================
   GET REJECTED ARTWORKS
===================================== */

$rejected_artworks = [];

$stmt = $conn->prepare(
    "SELECT
        aw.artwork_id,
        aw.title,
        aw.description,
        aw.price,
        aw.image,
        aw.status,
        aw.created_at,
        a.artist_name
     FROM artworks aw
     INNER JOIN artists a
        ON aw.artist_id = a.artist_id
     INNER JOIN event_artists ea
        ON a.artist_id = ea.artist_id
     WHERE aw.status = 'Rejected'
       AND ea.status = 'Approved'
     ORDER BY aw.created_at DESC"
);

$stmt->execute();

$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {

    $rejected_artworks[] = $row;

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

    <link rel="stylesheet" href="Css/style.css">
    <link rel="stylesheet" href="Css/admin.css">
    <link rel="stylesheet" href="Css/admin_artworks.css">

</head>


<body class="admin-dashboard-page">


    <!-- =====================================
         ADMIN HEADER
    ====================================== -->

    <header class="admin-header">

        <div class="admin-header-left">

            <img
                src="images/logo.png"
                alt="Maturan's Art Cafe Logo"
                class="admin-header-logo"
            >

            <div>

                <h1>
                    ADMIN PANEL
                </h1>

                <p>
                    Maturan's Art Cafe
                </p>

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
                href="admin_artworks.php"
                class="admin-nav-button"
            >
                ARTWORKS
            </a>

            <span>
                Welcome,
                <?php
                echo htmlspecialchars(
                    $_SESSION["admin_username"]
                );
                ?>
            </span>

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

            <h2>
                ARTWORK MANAGEMENT
            </h2>

            <p>
                Review and manage artwork submissions
                from approved artists.
            </p>

        </div>



        <!-- =====================================
             SUCCESS / ERROR MESSAGE
        ====================================== -->

        <?php if ($success !== ""): ?>

            <div class="admin-message success">

                <?php
                echo htmlspecialchars($success);
                ?>

            </div>

        <?php endif; ?>


        <?php if ($error !== ""): ?>

            <div class="admin-message error">

                <?php
                echo htmlspecialchars($error);
                ?>

            </div>

        <?php endif; ?>



        <!-- =====================================
             PENDING ARTWORKS
        ====================================== -->

        <section class="clean-admin-section">


            <div class="clean-section-heading">

                <h3>
                    PENDING ARTWORKS
                </h3>

                <span>
                    <?php
                    echo count($pending_artworks);
                    ?>
                    pending
                </span>

            </div>


            <?php if (count($pending_artworks) === 0): ?>

                <div class="clean-empty">

                    <p>
                        No artwork submissions are waiting
                        for approval.
                    </p>

                </div>

            <?php else: ?>


                <div class="clean-artwork-grid">


                    <?php foreach ($pending_artworks as $artwork): ?>

                        <div class="clean-artwork-card pending-artwork-card">


                            <!-- ARTWORK IMAGE -->

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



                            <!-- ARTWORK INFORMATION -->

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


                                <?php if (
                                    !empty(
                                        $artwork["description"]
                                    )
                                ): ?>

                                    <p class="artwork-description">

                                        <?php
                                        echo nl2br(
                                            htmlspecialchars(
                                                $artwork["description"]
                                            )
                                        );
                                        ?>

                                    </p>

                                <?php endif; ?>



                                <div class="clean-artwork-bottom">

                                    <strong>
                                        ₱<?php
                                        echo number_format(
                                            (float)$artwork["price"],
                                            2
                                        );
                                        ?>
                                    </strong>

                                    <span class="artwork-status pending-status">
                                        PENDING
                                    </span>

                                </div>



                                <!-- ACTION BUTTONS -->

                                <div class="artwork-admin-actions">


                                    <!-- APPROVE -->

                                    <form
                                        method="POST"
                                        onsubmit="return confirm('Approve this artwork?');"
                                    >

                                        <input
                                            type="hidden"
                                            name="artwork_id"
                                            value="<?php
                                            echo $artwork["artwork_id"];
                                            ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="action"
                                            value="approve"
                                        >

                                        <button
                                            type="submit"
                                            class="artwork-approve-button"
                                        >
                                            APPROVE
                                        </button>

                                    </form>



                                    <!-- REJECT -->

                                    <form
                                        method="POST"
                                        onsubmit="return confirm('Reject this artwork?');"
                                    >

                                        <input
                                            type="hidden"
                                            name="artwork_id"
                                            value="<?php
                                            echo $artwork["artwork_id"];
                                            ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="action"
                                            value="reject"
                                        >

                                        <button
                                            type="submit"
                                            class="artwork-reject-button"
                                        >
                                            REJECT
                                        </button>

                                    </form>


                                </div>


                            </div>


                        </div>

                    <?php endforeach; ?>


                </div>


            <?php endif; ?>


        </section>



        <!-- =====================================
             APPROVED ARTWORKS
        ====================================== -->

        <section class="clean-admin-section artwork-section">


            <div class="clean-section-heading">

                <h3>
                    APPROVED ARTWORKS
                </h3>

                <span>
                    <?php
                    echo count($approved_artworks);
                    ?>
                    artworks
                </span>

            </div>


            <?php if (count($approved_artworks) === 0): ?>

                <div class="clean-empty">

                    <p>
                        No approved artworks yet.
                    </p>

                </div>

            <?php else: ?>


                <div class="clean-artwork-grid">


                    <?php foreach ($approved_artworks as $artwork): ?>

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


                                <?php if (
                                    !empty(
                                        $artwork["description"]
                                    )
                                ): ?>

                                    <p class="artwork-description">

                                        <?php
                                        echo nl2br(
                                            htmlspecialchars(
                                                $artwork["description"]
                                            )
                                        );
                                        ?>

                                    </p>

                                <?php endif; ?>


                                <div class="clean-artwork-bottom">

                                    <strong>
                                        ₱<?php
                                        echo number_format(
                                            (float)$artwork["price"],
                                            2
                                        );
                                        ?>
                                    </strong>

                                    <span class="artwork-status approved-status">
                                        APPROVED
                                    </span>

                                </div>


                            </div>


                        </div>

                    <?php endforeach; ?>


                </div>


            <?php endif; ?>


        </section>



        <!-- =====================================
             REJECTED ARTWORKS
        ====================================== -->

        <section class="clean-admin-section artwork-section">


            <div class="clean-section-heading">

                <h3>
                    REJECTED ARTWORKS
                </h3>

                <span>
                    <?php
                    echo count($rejected_artworks);
                    ?>
                    rejected
                </span>

            </div>


            <?php if (count($rejected_artworks) === 0): ?>

                <div class="clean-empty">

                    <p>
                        No rejected artworks.
                    </p>

                </div>

            <?php else: ?>


                <div class="clean-artwork-grid">


                    <?php foreach ($rejected_artworks as $artwork): ?>

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


                                <?php if (
                                    !empty(
                                        $artwork["description"]
                                    )
                                ): ?>

                                    <p class="artwork-description">

                                        <?php
                                        echo nl2br(
                                            htmlspecialchars(
                                                $artwork["description"]
                                            )
                                        );
                                        ?>

                                    </p>

                                <?php endif; ?>


                                <div class="clean-artwork-bottom">

                                    <strong>
                                        ₱<?php
                                        echo number_format(
                                            (float)$artwork["price"],
                                            2
                                        );
                                        ?>
                                    </strong>

                                    <span class="artwork-status rejected-status">
                                        REJECTED
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