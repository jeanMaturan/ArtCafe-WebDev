<?php
session_start();
require_once "db.php";

/* USER MUST BE LOGGED IN */
if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION["user_id"];

/* GET ONLY THIS USER'S MESSAGES */
$stmt = $conn->prepare(
    "SELECT
        message_id,
        subject,
        message,
        status,
        admin_reply,
        created_at,
        replied_at
     FROM contact_messages
     WHERE user_id = ?
     ORDER BY created_at DESC"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Messages - Maturan's Art Cafe</title>

    <link rel="stylesheet" href="Css/style.css">
    <link rel="stylesheet" href="Css/my_messages.css">

</head>

<body>

    <!-- HEADER -->
    <header class="header">

        <div class="logo">
            <img src="images/logo.png" alt="Maturan's Art Cafe Logo">
        </div>

        <nav class="navbar">
            <a href="index.php">HOME</a>
            <a href="about.php">ABOUT</a>
            <a href="menu.php">MENU</a>
            <a href="events.php">EVENTS</a>
            <a href="contact.php">CONTACT</a>
            <a href="my_messages.php" class="active" style="color: #ed542c !important;">MY MESSAGES</a>
        </nav>

        <a href="reservation.php" class="reserve-btn">
            RESERVE A TABLE
        </a>

    </header>


    <!-- MY MESSAGES -->
    <main class="my-messages-page">

        <div class="my-messages-header">
            <h1>MY <span>MESSAGES.</span></h1>
            <p>
                View your messages and replies from Maturan's Art Cafe.
            </p>
        </div>


        <?php if ($result && $result->num_rows > 0): ?>

            <?php while ($row = $result->fetch_assoc()): ?>

                <div class="my-message-card">

                    <div class="my-message-top">

                        <div class="my-message-subject">
                            <?php echo htmlspecialchars($row["subject"]); ?>
                        </div>

                        <div class="my-message-status">
                            <?php echo htmlspecialchars($row["status"]); ?>
                        </div>

                    </div>


                    <div class="my-message-date">
                        Sent on:
                        <?php echo htmlspecialchars($row["created_at"]); ?>
                    </div>


                    <div class="my-message-content">
                        <?php
                        echo nl2br(
                            htmlspecialchars($row["message"])
                        );
                        ?>
                    </div>


                    <?php if (!empty($row["admin_reply"])): ?>

                        <div class="admin-reply-box">

                            <strong>REPLY FROM MATURAN'S ART CAFE</strong>

                            <p>
                                <?php
                                echo nl2br(
                                    htmlspecialchars($row["admin_reply"])
                                );
                                ?>
                            </p>

                        </div>

                    <?php endif; ?>

                </div>

            <?php endwhile; ?>

        <?php else: ?>

            <div class="no-messages">
                You don't have any messages yet.
            </div>

        <?php endif; ?>

    </main>


    <!-- FOOTER -->
    <footer class="footer">
        <div class="footer-content">

            <div class="footer-logo">
                <img src="images/logo.png" alt="Maturan's Art Cafe Logo">
            </div>

            <div class="footer-links">
                <a href="index.php">HOME</a>
                <a href="about.php">ABOUT</a>
                <a href="menu.php">MENU</a>
                <a href="events.php">EVENTS</a>
                <a href="contact.php">CONTACT</a>
            </div>

        </div>
    </footer>

</body>
</html>

<?php
$stmt->close();
?>