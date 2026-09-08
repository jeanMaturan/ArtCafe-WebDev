<?php

session_start();

require_once "db.php";

if (!isset($_SESSION["admin_logged_in"]) || $_SESSION["admin_logged_in"] !== true) {
    header("Location: admin_login.php");
    exit();
}


/* MARK MESSAGE AS READ */

if (isset($_GET["read"])) {

    $message_id = (int) $_GET["read"];

    $stmt = $conn->prepare(
        "UPDATE contact_messages
         SET status = 'Read'
         WHERE message_id = ?
         AND status = 'Unread'"
    );

    $stmt->bind_param("i", $message_id);
    $stmt->execute();
    $stmt->close();

    header("Location: admin_messages.php");
    exit();
}


/* MARK MESSAGE AS REPLIED */

if (isset($_GET["replied"])) {

    $message_id = (int) $_GET["replied"];

    $stmt = $conn->prepare(
        "UPDATE contact_messages
         SET status = 'Replied'
         WHERE message_id = ?"
    );

    $stmt->bind_param("i", $message_id);
    $stmt->execute();
    $stmt->close();

    header("Location: admin_messages.php");
    exit();
}


/* GET MESSAGES */

$result = $conn->query(
    "SELECT *
     FROM contact_messages
     ORDER BY created_at DESC"
);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Messages - Maturan's Art Cafe</title>

    <link rel="stylesheet" href="Css/style.css">
    <link rel="stylesheet" href="Css/admin.css">
    <link rel="stylesheet" href="Css/admin_messages.css">


</head>

<body>

    <header class="header">

        <div class="logo">

            <img
                src="images/logo.png"
                alt="Maturan's Art Cafe"
            >

        </div>

        <nav class="navbar">

            <a href="admin.php">ARTISTS</a>

            <a href="admin_artworks.php">ARTWORKS</a>

            <a href="admin_messages.php">MESSAGES</a>

        </nav>

        <a
            href="admin_logout.php"
            class="reserve-btn"
        >
            LOGOUT
        </a>

    </header>


    <section class="messages-page">

        <div class="messages-header">

            <h1>
                CUSTOMER <span>MESSAGES.</span>
            </h1>

            <p>
                Messages submitted through the Contact page.
            </p>

        </div>


        <?php if ($result && $result->num_rows > 0): ?>

            <?php while ($row = $result->fetch_assoc()): ?>

                <div
                    class="message-card <?php echo $row["status"] === "Unread" ? "unread" : ""; ?>"
                >

                    <div class="message-top">

                        <div class="message-subject">

                            <?php
                            echo htmlspecialchars($row["subject"]);
                            ?>

                        </div>

                        <div class="message-status">

                            <?php
                            echo htmlspecialchars($row["status"]);
                            ?>

                        </div>

                    </div>


                    <div class="message-info">

                        <div>
                            <strong>From:</strong>
                            <?php
                            echo htmlspecialchars($row["name"]);
                            ?>
                        </div>

                        <div>
                            <strong>Email:</strong>
                            <?php
                            echo htmlspecialchars($row["email"]);
                            ?>
                        </div>

                        <div>
                            <strong>Date:</strong>
                            <?php
                            echo htmlspecialchars($row["created_at"]);
                            ?>
                        </div>

                    </div>


                    <div class="message-body">

                        <?php
                        echo htmlspecialchars($row["message"]);
                        ?>

                    </div>


                    <div class="message-actions">

                        <?php if ($row["status"] === "Unread"): ?>

                            <a
                                href="admin_messages.php?read=<?php echo $row["message_id"]; ?>"
                                class="message-button"
                            >
                                MARK AS READ
                            </a>

                        <?php endif; ?>


                        <?php if ($row["status"] !== "Replied"): ?>

                            <a
                                href="admin_messages.php?replied=<?php echo $row["message_id"]; ?>"
                                class="message-button secondary"
                            >
                                MARK AS REPLIED
                            </a>

                        <?php endif; ?>

                    </div>

                </div>

            <?php endwhile; ?>

        <?php else: ?>

            <div class="no-messages">

                No customer messages yet.

            </div>

        <?php endif; ?>

    </section>


    <script src="JS/script.js"></script>

</body>

</html>