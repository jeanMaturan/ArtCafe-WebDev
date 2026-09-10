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

$user_id = (int) $_SESSION["user_id"];

$success = "";
$error = "";


/* =====================================
   HANDLE USER REPLY (CONTINUES THE
   THREAD -- THIS IS WHAT LETS THE
   CONVERSATION GO BACK AND FORTH)
===================================== */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $message_id = filter_input(
        INPUT_POST,
        "message_id",
        FILTER_VALIDATE_INT
    );

    $reply_text = trim($_POST["reply_text"] ?? "");


    if (
        $message_id === false ||
        $message_id === null ||
        $message_id <= 0
    ) {

        $error = "Invalid conversation.";

    } elseif ($reply_text === "") {

        $error = "Please enter a message.";

    } elseif (strlen($reply_text) < 2) {

        $error = "Message must be at least 2 characters.";

    } elseif (strlen($reply_text) > 5000) {

        $error = "Message must not exceed 5000 characters.";

    } else {

        /* Confirm this thread actually belongs to this user */

        $check_stmt = $conn->prepare(
            "SELECT message_id FROM contact_messages
             WHERE message_id = ? AND user_id = ?"
        );
        $check_stmt->bind_param("ii", $message_id, $user_id);
        $check_stmt->execute();
        $owns_thread = $check_stmt->get_result()->num_rows > 0;
        $check_stmt->close();

        if (!$owns_thread) {

            $error = "That conversation could not be found.";

        } else {

            $conn->begin_transaction();

            $insert_stmt = $conn->prepare(
                "INSERT INTO message_replies
                    (message_id, sender_type, reply_text)
                 VALUES (?, 'user', ?)"
            );

            $insert_stmt->bind_param("is", $message_id, $reply_text);
            $insert_ok = $insert_stmt->execute();
            $insert_stmt->close();

            if ($insert_ok) {

                /* A new customer reply always needs fresh
                   admin attention, so reopen it as Unread. */

                $update_stmt = $conn->prepare(
                    "UPDATE contact_messages
                     SET status = 'Unread'
                     WHERE message_id = ?"
                );

                $update_stmt->bind_param("i", $message_id);
                $update_stmt->execute();
                $update_stmt->close();

                $conn->commit();

                $success = "Your message has been sent.";

            } else {

                $conn->rollback();

                $error = "Something went wrong. Please try again.";
            }
        }
    }
}


/* =====================================
   GET THIS USER'S CONVERSATION THREADS
   (most recently active first)
===================================== */

$threads = [];

$stmt = $conn->prepare(
    "SELECT
        cm.message_id,
        cm.subject,
        cm.message,
        cm.status,
        cm.created_at,
        COALESCE(MAX(mr.created_at), cm.created_at) AS last_activity
     FROM contact_messages cm
     LEFT JOIN message_replies mr ON mr.message_id = cm.message_id
     WHERE cm.user_id = ?
     GROUP BY cm.message_id
     ORDER BY last_activity DESC"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $threads[] = $row;
    }
}

$stmt->close();


/* =====================================
   GET ALL REPLIES FOR EACH THREAD
===================================== */

$replies_by_message = [];

if (!empty($threads)) {

    $ids = array_column($threads, "message_id");
    $placeholders = implode(",", array_fill(0, count($ids), "?"));
    $types = str_repeat("i", count($ids));

    $reply_stmt = $conn->prepare(
        "SELECT message_id, sender_type, reply_text, created_at
         FROM message_replies
         WHERE message_id IN ($placeholders)
         ORDER BY created_at ASC"
    );

    $reply_stmt->bind_param($types, ...$ids);
    $reply_stmt->execute();
    $reply_result = $reply_stmt->get_result();

    while ($row = $reply_result->fetch_assoc()) {
        $replies_by_message[$row["message_id"]][] = $row;
    }

    $reply_stmt->close();
}

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
                View your conversation with Maturan's Art Cafe and reply anytime.
            </p>
        </div>


        <?php if ($success !== ""): ?>
            <div class="my-message-alert success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <?php if ($error !== ""): ?>
            <div class="my-message-alert error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>


        <?php if (!empty($threads)): ?>

            <?php foreach ($threads as $thread): ?>

                <?php $mid = (int) $thread["message_id"]; ?>

                <div class="my-message-card">

                    <div class="my-message-top">
                        <div class="my-message-subject">
                            <?php echo htmlspecialchars($thread["subject"]); ?>
                        </div>

                        <div class="my-message-status">
                            <?php echo htmlspecialchars($thread["status"]); ?>
                        </div>
                    </div>


                    <div class="my-message-date">
                        Started on:
                        <?php echo htmlspecialchars($thread["created_at"]); ?>
                    </div>


                    <!-- =================================
                         CONVERSATION THREAD
                    ================================== -->

                    <div class="conversation-thread">

                        <!-- ORIGINAL MESSAGE -->
                        <div class="thread-bubble thread-mine">
                            <div class="thread-bubble-meta">
                                You &middot;
                                <?php echo date("M j, Y g:i A", strtotime($thread["created_at"])); ?>
                            </div>
                            <div class="thread-bubble-text">
                                <?php echo nl2br(htmlspecialchars($thread["message"])); ?>
                            </div>
                        </div>

                        <!-- ALL FOLLOW-UP REPLIES, IN ORDER -->
                        <?php if (!empty($replies_by_message[$mid])): ?>

                            <?php foreach ($replies_by_message[$mid] as $reply): ?>

                                <?php $is_admin = $reply["sender_type"] === "admin"; ?>

                                <div class="thread-bubble <?php echo $is_admin ? 'thread-cafe' : 'thread-mine'; ?>">
                                    <div class="thread-bubble-meta">
                                        <?php echo $is_admin ? "Maturan's Art Cafe" : "You"; ?>
                                        &middot;
                                        <?php echo date("M j, Y g:i A", strtotime($reply["created_at"])); ?>
                                    </div>
                                    <div class="thread-bubble-text">
                                        <?php echo nl2br(htmlspecialchars($reply["reply_text"])); ?>
                                    </div>
                                </div>

                            <?php endforeach; ?>

                        <?php endif; ?>

                    </div>


                    <!-- =================================
                         REPLY FORM
                    ================================== -->

                    <form method="POST" class="my-reply-form">

                        <input type="hidden" name="message_id" value="<?php echo $mid; ?>">

                        <textarea
                            name="reply_text"
                            class="my-reply-input"
                            placeholder="Write a reply..."
                            required
                        ></textarea>

                        <button type="submit" class="my-reply-button">
                            SEND
                        </button>

                    </form>

                </div>

            <?php endforeach; ?>

        <?php else: ?>

            <div class="no-messages">
                You don't have any messages yet.
            </div>

        <?php endif; ?>

    </main>


    <?php include "footer.php"; ?>

</body>
</html>