<?php

session_start();
require_once "db.php";


/* =====================================
   ADMIN LOGIN CHECK
===================================== */

if (
    !isset($_SESSION["user_logged_in"]) ||
    $_SESSION["user_logged_in"] !== true ||
    !isset($_SESSION["user_id"]) ||
    !is_numeric($_SESSION["user_id"]) ||
    !isset($_SESSION["role"]) ||
    $_SESSION["role"] !== "admin"
) {
    header("Location: login.php");
    exit();
}

$admin_id = (int) $_SESSION["user_id"];


/* =====================================
   HANDLE POST ACTIONS
===================================== */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    /* A background "mark as read" call fired when the admin
       opens a conversation, messenger-style. It should not
       redirect or leave a banner for the next real page view. */

    $is_ajax = (
        isset($_SERVER["HTTP_X_REQUESTED_WITH"]) &&
        strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) === "xmlhttprequest"
    );

    $action = trim($_POST["action"] ?? "");

    $message_id = filter_input(
        INPUT_POST,
        "message_id",
        FILTER_VALIDATE_INT
    );


    /* =====================================
       VALIDATE INPUT
    ===================================== */

    if (
        $message_id === false ||
        $message_id === null ||
        $message_id <= 0
    ) {

        if ($is_ajax) {
            http_response_code(400);
            exit();
        }

        $_SESSION["admin_message_error"] = "Invalid message.";

        header("Location: admin_messages.php");
        exit();
    }


    if (!in_array($action, ["reply", "read"], true)) {

        if ($is_ajax) {
            http_response_code(400);
            exit();
        }

        $_SESSION["admin_message_error"] = "Invalid action.";

        header("Location: admin_messages.php");
        exit();
    }


    /* =====================================
       SEND REPLY (ADDS TO THE THREAD --
       DOES NOT OVERWRITE ANYTHING, SO THE
       ADMIN CAN REPLY AS MANY TIMES AS
       THE CONVERSATION NEEDS)
    ===================================== */

    if ($action === "reply") {

        $reply_text = trim($_POST["admin_reply"] ?? "");


        if ($reply_text === "") {

            $_SESSION["admin_message_error"] =
                "Please enter a reply.";

        } elseif (strlen($reply_text) < 2) {

            $_SESSION["admin_message_error"] =
                "Reply must be at least 2 characters.";

        } elseif (strlen($reply_text) > 5000) {

            $_SESSION["admin_message_error"] =
                "Reply must not exceed 5000 characters.";

        } else {

            /* Confirm the thread exists first */

            $check_stmt = $conn->prepare(
                "SELECT message_id FROM contact_messages WHERE message_id = ?"
            );
            $check_stmt->bind_param("i", $message_id);
            $check_stmt->execute();
            $exists = $check_stmt->get_result()->num_rows > 0;
            $check_stmt->close();

            if (!$exists) {

                $_SESSION["admin_message_error"] =
                    "That conversation no longer exists.";

            } else {

                $conn->begin_transaction();

                $insert_stmt = $conn->prepare(
                    "INSERT INTO message_replies
                        (message_id, sender_type, reply_text)
                     VALUES (?, 'admin', ?)"
                );

                $insert_stmt->bind_param("is", $message_id, $reply_text);
                $insert_ok = $insert_stmt->execute();
                $insert_stmt->close();

                if ($insert_ok) {

                    $update_stmt = $conn->prepare(
                        "UPDATE contact_messages
                         SET status = 'Replied',
                             replied_at = NOW()
                         WHERE message_id = ?"
                    );

                    $update_stmt->bind_param("i", $message_id);
                    $update_stmt->execute();
                    $update_stmt->close();

                    $conn->commit();

                    $_SESSION["admin_message_success"] =
                        "Reply sent successfully.";

                } else {

                    $conn->rollback();

                    $_SESSION["admin_message_error"] =
                        "Something went wrong. Please try again.";
                }
            }
        }
    }


    /* =====================================
       MARK AS READ
    ===================================== */

    elseif ($action === "read") {

        $stmt = $conn->prepare(
            "UPDATE contact_messages
             SET status = 'Read'
             WHERE message_id = ?
               AND status = 'Unread'"
        );

        $read_ok = false;
        $rows_changed = 0;

        if ($stmt) {

            $stmt->bind_param("i", $message_id);

            if ($stmt->execute()) {
                $read_ok = true;
                $rows_changed = $stmt->affected_rows;
            }

            $stmt->close();
        }

        /* Opening a conversation fires this in the background --
           just acknowledge it and stop, no redirect or banner. */

        if ($is_ajax) {

            http_response_code($read_ok ? 200 : 500);
            exit();
        }

        if ($read_ok && $rows_changed === 1) {

            $_SESSION["admin_message_success"] =
                "Message marked as read.";

        } elseif ($read_ok) {

            $_SESSION["admin_message_error"] =
                "The message could not be updated.";

        } else {

            $_SESSION["admin_message_error"] =
                "Something went wrong. Please try again.";
        }
    }


    header("Location: admin_messages.php");
    exit();
}


/* =====================================
   DISPLAY ONE-TIME MESSAGES
===================================== */

$success = $_SESSION["admin_message_success"] ?? "";
$error = $_SESSION["admin_message_error"] ?? "";

unset($_SESSION["admin_message_success"]);
unset($_SESSION["admin_message_error"]);


/* =====================================
   GET CONVERSATION THREADS
   (ordered by most recent activity, so
   threads with a new customer reply
   bubble back to the top)
===================================== */

$threads = [];

$result = $conn->query(
    "SELECT
        cm.message_id,
        cm.name,
        cm.email,
        cm.subject,
        cm.message,
        cm.status,
        cm.created_at,
        COALESCE(MAX(mr.created_at), cm.created_at) AS last_activity
     FROM contact_messages cm
     LEFT JOIN message_replies mr ON mr.message_id = cm.message_id
     GROUP BY cm.message_id
     ORDER BY last_activity DESC"
);

if ($result) {

    while ($row = $result->fetch_assoc()) {
        $threads[] = $row;
    }

} elseif ($error === "") {

    $error = "Unable to load customer messages.";
}


/* =====================================
   GET ALL REPLIES FOR EACH THREAD
===================================== */

$replies_by_message = [];

if (!empty($threads)) {

    $reply_result = $conn->query(
        "SELECT message_id, sender_type, reply_text, created_at
         FROM message_replies
         ORDER BY created_at ASC"
    );

    if ($reply_result) {

        while ($row = $reply_result->fetch_assoc()) {
            $replies_by_message[$row["message_id"]][] = $row;
        }
    }
}


/* =====================================
   BUILD INBOX PREVIEWS
   (last message in each thread, plus a
   running count of unread threads)
===================================== */

$unread_count = 0;

foreach ($threads as &$t) {

    $mid = (int) $t["message_id"];

    if ($t["status"] === "Unread") {
        $unread_count++;
    }

    if (!empty($replies_by_message[$mid])) {

        $last_reply = end($replies_by_message[$mid]);

        $t["preview_is_admin"] = $last_reply["sender_type"] === "admin";
        $t["preview_text"] = $last_reply["reply_text"];

    } else {

        $t["preview_is_admin"] = false;
        $t["preview_text"] = $t["message"];
    }
}

unset($t);

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
        Messages - Maturan's Art Cafe
    </title>

    <link rel="stylesheet" href="Css/style.css">
    <link rel="stylesheet" href="Css/admin.css">
    <link rel="stylesheet" href="Css/admin_messages.css">
    <link rel="stylesheet" href="Css/admin_dashboard.css">

</head>


<body class="admin-dashboard-page">


    <div class="dash-layout">

        <?php $active_page = "messages"; include "admin_sidebar.php"; ?>

        <main class="dash-main">

            <header class="dash-topbar">
                <div class="dash-admin-chip">
                    <?php echo htmlspecialchars($_SESSION["user_name"]); ?>
                </div>
            </header>


            <main class="messages-page">


                <div class="messages-header">
                    <h1>CUSTOMER <span>MESSAGES.</span></h1>
                    <p>Messages submitted through the Contact page.</p>

                    <span
                        class="msgr-unread-badge"
                        data-count="<?php echo $unread_count; ?>"
                        style="<?php echo $unread_count > 0 ? '' : 'display:none;'; ?>"
                    ><?php echo $unread_count; ?> unread</span>
                </div>


                <?php if ($success !== ""): ?>
                    <div class="admin-message success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <?php if ($error !== ""): ?>
                    <div class="admin-message error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>


                <?php if (!empty($threads)): ?>

                    <div class="msgr-app" data-mode="admin">

                        <!-- =================================
                             CONVERSATION LIST
                        ================================== -->

                        <div class="msgr-list">

                            <?php foreach ($threads as $thread): ?>

                                <?php $mid = (int) $thread["message_id"]; ?>

                                <div
                                    class="msgr-list-item<?php echo $thread["status"] === "Unread" ? " unread" : ""; ?>"
                                    data-message-id="<?php echo $mid; ?>"
                                >

                                    <span class="msgr-unread-dot"></span>

                                    <div class="msgr-item-body">

                                        <div class="msgr-item-top">
                                            <span class="msgr-item-subject"><?php echo htmlspecialchars($thread["subject"]); ?></span>
                                            <span class="msgr-item-time"><?php echo date("M j", strtotime($thread["last_activity"])); ?></span>
                                        </div>

                                        <div class="msgr-item-from">
                                            <?php echo htmlspecialchars($thread["name"]); ?>
                                        </div>

                                        <div class="msgr-item-preview">
                                            <?php echo $thread["preview_is_admin"] ? "You: " : ""; ?><?php echo htmlspecialchars(mb_strimwidth($thread["preview_text"], 0, 70, "…")); ?>
                                        </div>

                                    </div>

                                </div>

                            <?php endforeach; ?>

                        </div>


                        <!-- =================================
                             CONVERSATION VIEW
                        ================================== -->

                        <div class="msgr-view">

                            <div class="msgr-placeholder active">
                                <p>Select a conversation to view messages</p>
                            </div>

                            <?php foreach ($threads as $thread): ?>

                                <?php $mid = (int) $thread["message_id"]; ?>

                                <div class="msgr-panel" data-message-id="<?php echo $mid; ?>">

                                    <div class="msgr-panel-header">

                                        <button type="button" class="msgr-back-btn">‹</button>

                                        <div class="msgr-panel-heading">
                                            <div class="msgr-panel-subject"><?php echo htmlspecialchars($thread["subject"]); ?></div>
                                            <div class="msgr-panel-meta">
                                                <?php echo htmlspecialchars($thread["name"]); ?> &middot;
                                                <?php echo htmlspecialchars($thread["email"]); ?>
                                            </div>
                                        </div>

                                        <div class="message-status"><?php echo htmlspecialchars($thread["status"]); ?></div>

                                    </div>


                                    <!-- CONVERSATION THREAD -->

                                    <div class="conversation-thread">

                                        <!-- ORIGINAL CUSTOMER MESSAGE -->
                                        <div class="thread-bubble thread-customer">
                                            <div class="thread-bubble-meta">
                                                <?php echo htmlspecialchars($thread["name"]); ?>
                                                &middot;
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

                                                <div class="thread-bubble <?php echo $is_admin ? 'thread-admin' : 'thread-customer'; ?>">
                                                    <div class="thread-bubble-meta">
                                                        <?php echo $is_admin ? "Maturan's Art Cafe (You)" : htmlspecialchars($thread["name"]); ?>
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


                                    <!-- ALWAYS-AVAILABLE REPLY FORM -->

                                    <form method="POST" class="reply-form">

                                        <input type="hidden" name="message_id" value="<?php echo $mid; ?>">
                                        <input type="hidden" name="action" value="reply">

                                        <textarea
                                            name="admin_reply"
                                            class="admin-reply-input"
                                            placeholder="Write a reply..."
                                            required
                                        ></textarea>

                                        <button type="submit" class="message-button">
                                            SEND REPLY
                                        </button>

                                    </form>

                                </div>

                            <?php endforeach; ?>

                        </div>

                    </div>

                <?php else: ?>

                    <div class="no-messages">
                        No customer messages yet.
                    </div>

                <?php endif; ?>


            </main>

        </main>

    </div>

    <script src="JS/script.js"></script>

</body>

</html>