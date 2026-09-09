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

$success = "";
$error = "";


/* =====================================
   APPROVE / REJECT REVIEW
===================================== */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $action = trim($_POST["action"] ?? "");

    $review_id = filter_input(
        INPUT_POST,
        "review_id",
        FILTER_VALIDATE_INT
    );


    /* ---------------------------------
       VALIDATE REVIEW ID
    --------------------------------- */

    if (
        $review_id === false ||
        $review_id === null ||
        $review_id <= 0
    ) {

        $error = "Invalid review.";

    }

    /* ---------------------------------
       VALIDATE ACTION
    --------------------------------- */

    elseif (!in_array($action, ["approve", "reject", "delete"], true)) {

        $error = "Invalid action.";

    }

    elseif ($action === "delete") {

        /* ---------------------------------
           DELETE REVIEW
        --------------------------------- */

        $stmt = $conn->prepare(
            "DELETE FROM reviews
             WHERE review_id = ?"
        );

        if (!$stmt) {

            error_log(
                "Review delete prepare failed: " .
                $conn->error
            );

            $error =
                "Something went wrong. Please try again.";

        } else {

            $stmt->bind_param("i", $review_id);

            if ($stmt->execute()) {

                if ($stmt->affected_rows === 1) {

                    $success =
                        "Review deleted successfully.";

                } else {

                    $error =
                        "The review could not be deleted. It may have already been removed.";
                }

            } else {

                error_log(
                    "Review delete failed: " .
                    $stmt->error
                );

                $error =
                    "Something went wrong. Please try again.";
            }

            $stmt->close();
        }

    }

    else {

        $new_status =
            ($action === "approve")
            ? "Approved"
            : "Rejected";


        /* ---------------------------------
           UPDATE REVIEW
        --------------------------------- */

        $stmt = $conn->prepare(
            "UPDATE reviews
             SET status = ?
             WHERE review_id = ?
             AND status = 'Pending'"
        );


        if (!$stmt) {

            error_log(
                "Review update prepare failed: " .
                $conn->error
            );

            $error =
                "Something went wrong. Please try again.";

        } else {

            $stmt->bind_param(
                "si",
                $new_status,
                $review_id
            );


            if ($stmt->execute()) {

                if ($stmt->affected_rows === 1) {

                    if ($action === "approve") {

                        $success =
                            "Review approved successfully.";

                    } else {

                        $success =
                            "Review rejected successfully.";
                    }

                } else {

                    $error =
                        "The review could not be updated. It may have already been processed.";
                }

            } else {

                error_log(
                    "Review update failed: " .
                    $stmt->error
                );

                $error =
                    "Something went wrong. Please try again.";
            }


            $stmt->close();
        }
    }
}


/* =====================================
   GET REVIEWS
===================================== */

$result = $conn->query(
    "SELECT
        r.review_id,
        r.rating,
        r.review_text,
        r.status,
        r.created_at,
        u.name,
        u.email
     FROM reviews r
     INNER JOIN users u
        ON r.user_id = u.user_id
     ORDER BY r.created_at DESC"
);


if (!$result) {

    error_log(
        "Admin reviews query failed: " .
        $conn->error
    );

    $error =
        "Unable to load reviews.";
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Review Management | Maturan's Art Cafe</title>

    <link rel="stylesheet" href="Css/style.css">
    <link rel="stylesheet" href="Css/admin_reviews.css">
    <link rel="stylesheet" href="Css/admin.css">
    <link rel="stylesheet" href="Css/admin_dashboard.css">

</head>


<body>


    <div class="dash-layout">

        <?php $active_page = "reviews"; include "admin_sidebar.php"; ?>

        <main class="dash-main">

            <header class="dash-topbar">
                <div class="dash-admin-chip">
                    <?php
                        echo htmlspecialchars(
                            $_SESSION["user_name"]
                        );
                    ?>
                </div>
            </header>



<!-- =====================================
     MAIN CONTENT
===================================== -->

<main class="admin-reviews">


    <!-- PAGE HEADING -->

    <div class="admin-reviews-heading">

        <p>
            CUSTOMER FEEDBACK
        </p>


        <h2>
            REVIEW <span>MANAGEMENT</span>
        </h2>


        <p>
            Review customer submissions and approve
            or reject them before they appear publicly.
        </p>

    </div>



    <!-- =================================
         SUCCESS MESSAGE
    ================================== -->

    <?php if ($success !== ""): ?>

        <div class="admin-review-success">

            <?php
            echo htmlspecialchars($success);
            ?>

        </div>

    <?php endif; ?>



    <!-- =================================
         ERROR MESSAGE
    ================================== -->

    <?php if ($error !== ""): ?>

        <div class="admin-review-error">

            <?php
            echo htmlspecialchars($error);
            ?>

        </div>

    <?php endif; ?>



    <!-- =================================
         REVIEWS
    ================================== -->

    <?php if ($result && $result->num_rows > 0): ?>


        <div class="admin-reviews-list">


            <?php while ($row = $result->fetch_assoc()): ?>


                <!-- REVIEW CARD -->

                <div class="admin-review-card">


                    <!-- REVIEW HEADER -->

                    <div class="admin-review-header">


                        <div>

                            <h3>

                                <?php
                                echo htmlspecialchars(
                                    $row["name"]
                                );
                                ?>

                            </h3>


                            <p>

                                <?php
                                echo htmlspecialchars(
                                    $row["email"]
                                );
                                ?>

                            </p>

                        </div>



                        <!-- STATUS -->

                        <span
                            class="review-status <?php echo strtolower(
                                htmlspecialchars(
                                    $row["status"]
                                )
                            ); ?>"
                        >

                            <?php
                            echo htmlspecialchars(
                                $row["status"]
                            );
                            ?>

                        </span>

                    </div>



                    <!-- RATING -->

                    <div class="admin-review-rating">

                        <?php

                        $rating = (int) $row["rating"];

                        echo str_repeat(
                            "★",
                            $rating
                        );

                        echo str_repeat(
                            "☆",
                            5 - $rating
                        );

                        ?>

                    </div>



                    <!-- REVIEW TEXT -->

                    <p class="admin-review-text">

                        <?php

                        echo nl2br(
                            htmlspecialchars(
                                $row["review_text"]
                            )
                        );

                        ?>

                    </p>



                    <!-- DATE -->

                    <p class="admin-review-date">

                        Submitted:

                        <?php

                        echo htmlspecialchars(
                            $row["created_at"]
                        );

                        ?>

                    </p>



                    <!-- =================================
                         ACTION BUTTONS
                    ================================== -->

                    <?php if ($row["status"] === "Pending"): ?>


                        <div class="admin-review-actions">


                            <!-- APPROVE -->

                            <form
                                method="POST"
                                action="admin_reviews.php"
                            >

                                <input
                                    type="hidden"
                                    name="review_id"
                                    value="<?php
                                        echo (int) $row["review_id"];
                                    ?>"
                                >


                                <input
                                    type="hidden"
                                    name="action"
                                    value="approve"
                                >


                                <button
                                    type="submit"
                                    class="approve-review-btn"
                                >
                                    APPROVE
                                </button>

                            </form>



                            <!-- REJECT -->

                            <form
                                method="POST"
                                action="admin_reviews.php"
                            >

                                <input
                                    type="hidden"
                                    name="review_id"
                                    value="<?php
                                        echo (int) $row["review_id"];
                                    ?>"
                                >


                                <input
                                    type="hidden"
                                    name="action"
                                    value="reject"
                                >


                                <button
                                    type="submit"
                                    class="reject-review-btn"
                                >
                                    REJECT
                                </button>

                            </form>


                        </div>


                    <?php endif; ?>


                    <!-- DELETE (always available) -->

                    <div class="admin-review-actions">

                        <form
                            method="POST"
                            action="admin_reviews.php"
                            onsubmit="return confirm('Are you sure you want to permanently delete this review?');"
                        >

                            <input
                                type="hidden"
                                name="review_id"
                                value="<?php
                                    echo (int) $row["review_id"];
                                ?>"
                            >

                            <input
                                type="hidden"
                                name="action"
                                value="delete"
                            >

                            <button
                                type="submit"
                                class="reject-review-btn"
                            >
                                DELETE
                            </button>

                        </form>

                    </div>


                </div>


            <?php endwhile; ?>


        </div>


    <?php else: ?>


        <!-- =================================
             NO REVIEWS
        ================================== -->

        <div class="no-admin-reviews">

            <h3>
                NO REVIEWS YET
            </h3>


            <p>
                Customer reviews will appear here
                once they are submitted.
            </p>

        </div>


    <?php endif; ?>


</main>


        </main>

    </div>

</body>

</html>