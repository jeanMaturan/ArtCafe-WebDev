<?php

session_start();
require_once "db.php";

/* ADMIN MUST BE LOGGED IN */
if (
    !isset($_SESSION["admin_logged_in"]) ||
    $_SESSION["admin_logged_in"] !== true
) {
    header("Location: admin_login.php");
    exit();
}

/* ================================
   DELETE SELECTED SUBSCRIBERS
================================ */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $subscriber_ids = $_POST["subscriber_ids"] ?? [];

    if (!empty($subscriber_ids)) {

        $subscriber_ids = array_map("intval", $subscriber_ids);

        $subscriber_ids = array_filter(
            $subscriber_ids,
            function ($id) {
                return $id > 0;
            }
        );

        if (!empty($subscriber_ids)) {

            $placeholders = implode(
                ",",
                array_fill(0, count($subscriber_ids), "?")
            );

            $types = str_repeat("i", count($subscriber_ids));

            $stmt = $conn->prepare(
                "DELETE FROM newsletter_subscribers
                 WHERE subscriber_id IN ($placeholders)"
            );

            $stmt->bind_param(
                $types,
                ...$subscriber_ids
            );

            $stmt->execute();
            $stmt->close();
        }


        /* CHECK IF TABLE IS EMPTY */

        $check = $conn->query(
            "SELECT COUNT(*) AS total
             FROM newsletter_subscribers"
        );

        $row = $check->fetch_assoc();

        if ((int)$row["total"] === 0) {

            $conn->query(
                "TRUNCATE TABLE newsletter_subscribers"
            );
        }
    }
}


/* ================================
   GET ALL SUBSCRIBERS
================================ */

$result = $conn->query(
    "SELECT subscriber_id, email, subscribed_at
     FROM newsletter_subscribers
     ORDER BY subscribed_at DESC"
);

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