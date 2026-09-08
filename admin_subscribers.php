<?php

session_start();
require_once "db.php";


/* =====================================
   ADMIN LOGIN CHECK
===================================== */

if (
    !isset($_SESSION["admin_logged_in"]) ||
    $_SESSION["admin_logged_in"] !== true ||
    !isset($_SESSION["admin_id"]) ||
    !is_numeric($_SESSION["admin_id"]) ||
    !isset($_SESSION["admin_username"]) ||
    $_SESSION["admin_username"] === ""
) {
    header("Location: admin_login.php");
    exit();
}

$admin_id = (int) $_SESSION["admin_id"];

$success = "";
$error = "";


/* =====================================
   DELETE SELECTED SUBSCRIBERS
===================================== */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $subscriber_ids = $_POST["subscriber_ids"] ?? [];

    /* ================================
       VALIDATE SUBSCRIBER IDS
    ================================= */

    if (!is_array($subscriber_ids)) {

        $error = "Invalid subscriber selection.";

    } else {

        $clean_ids = [];

        foreach ($subscriber_ids as $id) {

            $validated_id = filter_var(
                $id,
                FILTER_VALIDATE_INT
            );

            if (
                $validated_id !== false &&
                $validated_id > 0
            ) {
                $clean_ids[] = $validated_id;
            }
        }

        /* Remove duplicate IDs */

        $clean_ids = array_values(
            array_unique($clean_ids)
        );


        if (empty($clean_ids)) {

            $error = "Please select at least one subscriber.";

        } else {

            /* ================================
               PREPARED DELETE
            ================================= */

            $placeholders = implode(
                ",",
                array_fill(
                    0,
                    count($clean_ids),
                    "?"
                )
            );

            $types = str_repeat(
                "i",
                count($clean_ids)
            );


            $stmt = $conn->prepare(
                "DELETE FROM newsletter_subscribers
                 WHERE subscriber_id IN ($placeholders)"
            );


            if (!$stmt) {

                $error =
                    "Something went wrong. Please try again.";

            } else {

                $stmt->bind_param(
                    $types,
                    ...$clean_ids
                );


                if ($stmt->execute()) {

                    if ($stmt->affected_rows > 0) {

                        $success =
                            $stmt->affected_rows .
                            " subscriber(s) deleted successfully.";

                    } else {

                        $error =
                            "No matching subscribers were found.";
                    }

                } else {

                    $error =
                        "Something went wrong. Please try again.";
                }


                $stmt->close();
            }
        }
    }
}


/* =====================================
   GET ALL SUBSCRIBERS
===================================== */

$result = $conn->query(
    "SELECT
        subscriber_id,
        email,
        subscribed_at
     FROM newsletter_subscribers
     ORDER BY subscribed_at DESC"
);


if (!$result) {

    $error = "Unable to load subscribers.";
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Subscribers | Maturan's Art Cafe</title>

    <link rel="stylesheet" href="Css/style.css">
    <link rel="stylesheet" href="Css/admin.css">
    <link rel="stylesheet" href="Css/admin_subscribers.css">
</head>

<body>

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

        <a href="admin.php" class="admin-nav-button">
            ARTISTS
        </a>

        <a href="admin_artworks.php" class="admin-nav-button">
            ARTWORKS
        </a>

        <a href="admin_reviews.php" class="admin-nav-button">REVIEWS</a>

        <a href="admin_messages.php" class="admin-nav-button">
            MESSAGES
        </a>

        <a href="admin_subscribers.php" class="admin-nav-button">
            SUBSCRIBERS
        </a>

        <span>
            Welcome,
            <?php echo htmlspecialchars($_SESSION["admin_username"]); ?>
        </span>

        <a href="admin_logout.php" class="admin-logout-button">
            LOGOUT
        </a>

    </div>

</header>


<main class="admin-page">

    <div class="admin-title">

        <h2>
            NEWSLETTER <span>SUBSCRIBERS</span>
        </h2>

        <p>
            Emails subscribed to updates, events, and promos.
        </p>

    </div>

    <div class="admin-card">

    <form
        method="POST"
        onsubmit="return confirm('Are you sure you want to delete the selected subscribers?');"
    >
       
        <table class="admin-table">

            <thead>
                <tr>
                    <th>#</th>
                    <th>EMAIL</th>
                    <th>SUBSCRIBED AT</th>
                     <th>
        <button
            type="submit"
            class="delete-selected-btn"
        >
            DELETE SELECTED
        </button>
        </th>
                </tr>
            </thead>

            <tbody>

                <?php while ($row = $result->fetch_assoc()): ?>

                    <tr>

                        <td>
                            <?php echo $row["subscriber_id"]; ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($row["email"]); ?>
                        </td>

                        <td>
                            <?php echo $row["subscribed_at"]; ?>
                        </td>

                        <td>
                            <input
                                type="checkbox"
                                name="subscriber_ids[]"
                                value="<?php echo $row["subscriber_id"]; ?>"
                                class="subscriber-checkbox"
                            >
                        </td>

                    </tr>

                <?php endwhile; ?>

            </tbody>

        </table>

    </form>

</div>
    

</main>
<script>

const selectAll = document.getElementById("selectAll");

const subscriberCheckboxes =
    document.querySelectorAll(".subscriber-checkbox");


selectAll.addEventListener("change", function () {

    subscriberCheckboxes.forEach(function (checkbox) {

        checkbox.checked = selectAll.checked;

    });

});

</script>
</body>
</html>