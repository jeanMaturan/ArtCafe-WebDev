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
   SEND ADMIN REPLY
===================================== */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $action = $_POST["action"] ?? "";
    $message_id = (int)($_POST["message_id"] ?? 0);


    /* ================================
       SEND REPLY
    ================================= */

    if ($action === "reply") {

        $admin_reply = trim(
            $_POST["admin_reply"] ?? ""
        );


        if (
            $message_id > 0 &&
            $admin_reply !== ""
        ) {

            $stmt = $conn->prepare(
                "UPDATE contact_messages
                 SET
                    admin_reply = ?,
                    status = 'Replied',
                    replied_at = NOW()
                 WHERE message_id = ?"
            );


            $stmt->bind_param(
                "si",
                $admin_reply,
                $message_id
            );


            $stmt->execute();

            $stmt->close();
        }
    }


    /* ================================
       MARK AS READ
    ================================= */

    elseif ($action === "read") {

        $stmt = $conn->prepare(
            "UPDATE contact_messages
             SET status = 'Read'
             WHERE message_id = ?"
        );


        $stmt->bind_param(
            "i",
            $message_id
        );


        $stmt->execute();

        $stmt->close();
    }


    /* ================================
       MARK AS REPLIED
    ================================= */

    elseif ($action === "replied") {

        $stmt = $conn->prepare(
            "UPDATE contact_messages
             SET status = 'Replied'
             WHERE message_id = ?"
        );


        $stmt->bind_param(
            "i",
            $message_id
        );


        $stmt->execute();

        $stmt->close();
    }


    /* ================================
       REFRESH PAGE
    ================================= */

    header("Location: admin_messages.php");
    exit();
}


/* =====================================
   GET MESSAGES
===================================== */

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
                ARTISTS
            </a>


            <a
                href="admin_artworks.php"
                class="admin-nav-button"
            >
                ARTWORKS
            </a>


            <a
                href="admin_messages.php"
                class="admin-nav-button"
            >
                MESSAGES
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


    <script src="JS/script.js"></script>

</body>

</html>