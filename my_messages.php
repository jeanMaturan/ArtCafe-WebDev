<?php

session_start();
require_once "db.php";

/* USER MUST BE LOGGED IN */
if (
    !isset($_SESSION["user_logged_in"]) ||
    $_SESSION["user_logged_in"] !== true ||
    !isset($_SESSION["user_id"]) ||
    !is_numeric($_SESSION["user_id"])
) {
    header("Location: login.php");
    exit();
}

/* CONVERT SESSION USER ID TO INTEGER */
$user_id = (int) $_SESSION["user_id"];

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

if (!$stmt) {
    die("Something went wrong. Please try again.");
}

$stmt->bind_param("i", $user_id);

if (!$stmt->execute()) {
    $stmt->close();
    die("Something went wrong. Please try again.");
}

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
    <?php include "header.php"; ?>


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


    <?php include "footer.php"; ?>

</body>
</html>

<?php
$stmt->close();
?>