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


/* =====================================
   SEND ADMIN REPLY / UPDATE STATUS
===================================== */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

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

        $_SESSION["admin_message_error"] = "Invalid message.";

        header("Location: admin_messages.php");
        exit();
    }


    if (!in_array($action, ["reply", "read", "replied"], true)) {

        $_SESSION["admin_message_error"] = "Invalid action.";

        header("Location: admin_messages.php");
        exit();
    }


    /* =====================================
       SEND REPLY
    ===================================== */

    if ($action === "reply") {

        $admin_reply = trim(
            $_POST["admin_reply"] ?? ""
        );


        if ($admin_reply === "") {

            $_SESSION["admin_message_error"] =
                "Please enter a reply.";

        } elseif (strlen($admin_reply) < 2) {

            $_SESSION["admin_message_error"] =
                "Reply must be at least 2 characters.";

        } elseif (strlen($admin_reply) > 5000) {

            $_SESSION["admin_message_error"] =
                "Reply must not exceed 5000 characters.";

        } else {

            /*
             * Only allow replying to an existing message.
             */

            $stmt = $conn->prepare(
                "UPDATE contact_messages
                 SET
                    admin_reply = ?,
                    status = 'Replied',
                    replied_at = NOW()
                 WHERE message_id = ?"
            );

            if (!$stmt) {

                $_SESSION["admin_message_error"] =
                    "Something went wrong. Please try again.";

            } else {

                $stmt->bind_param(
                    "si",
                    $admin_reply,
                    $message_id
                );

                if ($stmt->execute()) {

                    if ($stmt->affected_rows >= 1) {

                        $_SESSION["admin_message_success"] =
                            "Reply sent successfully.";

                    } else {

                        $_SESSION["admin_message_error"] =
                            "The message could not be updated.";
                    }

                } else {

                    $_SESSION["admin_message_error"] =
                        "Something went wrong. Please try again.";
                }

                $stmt->close();
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

        if (!$stmt) {

            $_SESSION["admin_message_error"] =
                "Something went wrong. Please try again.";

        } else {

            $stmt->bind_param(
                "i",
                $message_id
            );

            if ($stmt->execute()) {

                if ($stmt->affected_rows === 1) {

                    $_SESSION["admin_message_success"] =
                        "Message marked as read.";

                } else {

                    $_SESSION["admin_message_error"] =
                        "The message could not be updated.";
                }

            } else {

                $_SESSION["admin_message_error"] =
                    "Something went wrong. Please try again.";
            }

            $stmt->close();
        }
    }


    /* =====================================
       MARK AS REPLIED
    ===================================== */

    elseif ($action === "replied") {

        $stmt = $conn->prepare(
            "UPDATE contact_messages
             SET
                status = 'Replied',
                replied_at = COALESCE(replied_at, NOW())
             WHERE message_id = ?
               AND status <> 'Replied'"
        );

        if (!$stmt) {

            $_SESSION["admin_message_error"] =
                "Something went wrong. Please try again.";

        } else {

            $stmt->bind_param(
                "i",
                $message_id
            );

            if ($stmt->execute()) {

                if ($stmt->affected_rows === 1) {

                    $_SESSION["admin_message_success"] =
                        "Message marked as replied.";

                } else {

                    $_SESSION["admin_message_error"] =
                        "The message could not be updated.";
                }

            } else {

                $_SESSION["admin_message_error"] =
                    "Something went wrong. Please try again.";
            }

            $stmt->close();
        }
    }


    /* =====================================
       REFRESH PAGE
    ===================================== */

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
   GET CUSTOMER MESSAGES
===================================== */

$result = $conn->query(
    "SELECT
        message_id,
        name,
        email,
        subject,
        message,
        status,
        admin_reply,
        created_at,
        replied_at
     FROM contact_messages
     ORDER BY created_at DESC"
);

if (!$result) {

    $result = false;

    if ($error === "") {
        $error = "Unable to load customer messages.";
    }
}

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

    <link
        rel="stylesheet"
        href="Css/style.css"
    >

    <link
        rel="stylesheet"
        href="Css/admin.css"
    >

    <link
        rel="stylesheet"
        href="Css/admin_messages.css"
    >

    <link
        rel="stylesheet"
        href="Css/admin_dashboard.css"
    >

</head>


<body class="admin-dashboard-page">


    <div class="dash-layout">

    <?php
    $admin_active = "messages";
    include "admin_sidebar.php";
    ?>

    <div class="dash-main">

    <!-- =====================================
         CUSTOMER MESSAGES
    ====================================== -->

    <main class="messages-page">


        <div class="messages-header">

            <h1>
                CUSTOMER
                <span>MESSAGES.</span>
            </h1>

            <p>
                Messages submitted through the Contact page.
            </p>

        </div>



        <?php if (
            $result &&
            $result->num_rows > 0
        ): ?>


            <?php while (
                $row = $result->fetch_assoc()
            ): ?>


                <div
                    class="message-card
                    <?php
                    echo $row["status"] === "Unread"
                        ? "unread"
                        : "";
                    ?>"
                >


                    <!-- MESSAGE HEADER -->

                    <div class="message-top">

                        <div class="message-subject">

                            <?php
                            echo htmlspecialchars(
                                $row["subject"]
                            );
                            ?>

                        </div>


                        <div class="message-status">

                            <?php
                            echo htmlspecialchars(
                                $row["status"]
                            );
                            ?>

                        </div>

                    </div>



                    <!-- CUSTOMER INFORMATION -->

                    <div class="message-info">


                        <div>

                            <strong>
                                From:
                            </strong>

                            <?php
                            echo htmlspecialchars(
                                $row["name"]
                            );
                            ?>

                        </div>


                        <div>

                            <strong>
                                Email:
                            </strong>

                            <?php
                            echo htmlspecialchars(
                                $row["email"]
                            );
                            ?>

                        </div>


                        <div>

                            <strong>
                                Date:
                            </strong>

                            <?php
                            echo htmlspecialchars(
                                $row["created_at"]
                            );
                            ?>

                        </div>


                    </div>



                    <!-- CUSTOMER MESSAGE -->

                    <div class="message-body">

                        <?php
                        echo nl2br(
                            htmlspecialchars(
                                $row["message"]
                            )
                        );
                        ?>

                    </div>



                    <!-- =================================
                         ADMIN REPLY
                    ================================== -->

                    <?php if (
                        !empty($row["admin_reply"])
                    ): ?>

                        <div class="admin-reply-display">

                            <strong>
                                ADMIN REPLY
                            </strong>

                            <p>

                                <?php
                                echo nl2br(
                                    htmlspecialchars(
                                        $row["admin_reply"]
                                    )
                                );
                                ?>

                            </p>

                        </div>

                    <?php endif; ?>



                    <!-- =================================
                         REPLY FORM
                    ================================== -->

                    <?php if (
                        empty($row["admin_reply"])
                    ): ?>

                        <form
                            method="POST"
                            class="reply-form"
                        >

                            <input
                                type="hidden"
                                name="message_id"
                                value="<?php
                                echo $row["message_id"];
                                ?>"
                            >


                            <input
                                type="hidden"
                                name="action"
                                value="reply"
                            >


                            <textarea
                                name="admin_reply"
                                class="admin-reply-input"
                                placeholder="Write your reply to this customer..."
                                required
                            ></textarea>


                            <button
                                type="submit"
                                class="message-button"
                            >
                                SEND REPLY
                            </button>

                        </form>

                    <?php endif; ?>



                    <!-- =================================
                         MESSAGE ACTIONS
                    ================================== -->

                    <div class="message-actions">


                        <?php if (
                            $row["status"] === "Unread"
                        ): ?>

                            <form method="POST">

                                <input
                                    type="hidden"
                                    name="message_id"
                                    value="<?php
                                    echo $row["message_id"];
                                    ?>"
                                >

                                <input
                                    type="hidden"
                                    name="action"
                                    value="read"
                                >

                                <button
                                    type="submit"
                                    class="message-button secondary"
                                >
                                    MARK AS READ
                                </button>

                            </form>

                        <?php endif; ?>



                        <?php if (
                            $row["status"] !== "Replied"
                        ): ?>

                            <form method="POST">

                                <input
                                    type="hidden"
                                    name="message_id"
                                    value="<?php
                                    echo $row["message_id"];
                                    ?>"
                                >

                                <input
                                    type="hidden"
                                    name="action"
                                    value="replied"
                                >

                                <button
                                    type="submit"
                                    class="message-button secondary"
                                >
                                    MARK AS REPLIED
                                </button>

                            </form>

                        <?php endif; ?>


                    </div>


                </div>


            <?php endwhile; ?>


        <?php else: ?>


            <div class="no-messages">

                No customer messages yet.

            </div>


        <?php endif; ?>


    </main>

    </div>

    </div>


    <script src="JS/script.js"></script>

</body>

</html>