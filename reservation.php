<?php

session_start();
require_once "db.php";
require_once "reservation_config.php";


/* =========================
   CHECK IF USER IS LOGGED IN
========================= */

if (
    !isset($_SESSION["user_logged_in"]) ||
    $_SESSION["user_logged_in"] !== true ||
    !isset($_SESSION["user_id"]) ||
    !is_numeric($_SESSION["user_id"])
) {
    header("Location: login.php");
    exit();
}


/* Make sure the session ID is an integer */
$user_id = (int) $_SESSION["user_id"];


$message = "";
$error = "";


/* =========================
   HANDLE RESERVATION
========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // The reservation name is always the logged-in user's account name.
    // It's locked in the UI, but we also ignore whatever is posted here
    // so it can't be tampered with client-side.
    $name = trim($_SESSION["user_name"] ?? "");
    $phone = trim($_POST["reservation_phone"] ?? "");
    $date = trim($_POST["date"] ?? "");
    $time = trim($_POST["time"] ?? "");
    $guests = filter_input(
        INPUT_POST,
        "guests",
        FILTER_VALIDATE_INT
    );
    $payment_type = trim($_POST["payment_type"] ?? "");
    $payment_reference = trim($_POST["payment_reference"] ?? "");


    /* =========================
       CHECK REQUIRED FIELDS
    ========================= */

    if (
        $name === "" ||
        $phone === "" ||
        $date === "" ||
        $time === "" ||
        $guests === false ||
        $guests === null ||
        $payment_type === "" ||
        $payment_reference === ""
    ) {

        $error = "Please complete all required fields.";

    }


    /* =========================
       VALIDATE NAME
    ========================= */

    elseif (!preg_match("/[A-Za-z]/", $name)) {

        $error = "Please enter a valid name.";

    }


    /* =========================
       VALIDATE PHILIPPINE PHONE
    ========================= */

    elseif (!preg_match("/^(09\d{9}|\+639\d{9})$/", $phone)) {

        $error = "Please enter a valid Philippine mobile number.";

    }


    /* =========================
       VALIDATE NUMBER OF GUESTS
    ========================= */

    elseif ($guests < 1 || $guests > 10) {

        $error = "Number of guests must be between 1 and 10.";

    }


    /* =========================
       VALIDATE PAYMENT TYPE
    ========================= */

    elseif (!in_array($payment_type, VALID_PAYMENT_TYPES, true)) {

        $error = "Please choose a valid payment option.";

    }


    /* =========================
       VALIDATE PAYMENT REFERENCE NUMBER
    ========================= */

    elseif (
        !preg_match("/^[A-Za-z0-9\-\_ ]{4,50}$/", $payment_reference)
    ) {

        $error = "Please enter a valid payment reference number (4-50 characters).";

    }


    /* =========================
       VALIDATE DATE
    ========================= */

    elseif (
        !DateTime::createFromFormat("Y-m-d", $date) ||
        DateTime::createFromFormat("Y-m-d", $date)->format("Y-m-d") !== $date
    ) {

        $error = "Please enter a valid reservation date.";

    }


    /* =========================
       PREVENT PAST DATES
    ========================= */

    elseif ($date < date("Y-m-d")) {

        $error = "You cannot reserve a table for a past date.";

    }


    /* =========================
       VALIDATE TIME
    ========================= */

    elseif (
        !DateTime::createFromFormat("H:i", $time) &&
        !DateTime::createFromFormat("H:i:s", $time)
    ) {

        $error = "Please enter a valid reservation time.";

    }


    /* =========================
       CHECK TABLE AVAILABILITY
    ========================= */

    elseif (
        (count_tables_used_in_slot($conn, $date, $time) + tables_needed_for_guests($guests))
        > MAX_TABLES
    ) {

        $error = "Sorry, that time slot doesn't have enough tables for your party size. Please choose another date, time, or a smaller group.";

    }


    /* =========================
       INSERT RESERVATION
    ========================= */

    else {

        /* Re-check capacity inside a transaction right before
           inserting, so two people submitting at the same
           instant can't both slip past the check above. */

        $conn->begin_transaction();

        $tables_needed = tables_needed_for_guests($guests);

        $tables_used = count_tables_used_in_slot($conn, $date, $time);

        if (($tables_used + $tables_needed) > MAX_TABLES) {

            $conn->rollback();

            $error = "Sorry, that time slot just filled up for a party your size. Please choose another date, time, or a smaller group.";

        } else {

            /* Amount due is computed server-side from the guest
               count and payment type -- never trust a client-posted
               amount. */
            $payment_amount = calculate_payment_amount($guests, $payment_type);

            $stmt = $conn->prepare(
                "INSERT INTO reservations
                (
                    user_id,
                    reservation_name,
                    reservation_phone,
                    reservation_date,
                    reservation_time,
                    guests,
                    payment_type,
                    payment_amount,
                    payment_reference
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );


            if (!$stmt) {

                $conn->rollback();

                $error = "Unable to process your reservation.";

            } else {

                $stmt->bind_param(
                    "issssisds",
                    $user_id,
                    $name,
                    $phone,
                    $date,
                    $time,
                    $guests,
                    $payment_type,
                    $payment_amount,
                    $payment_reference
                );


                if ($stmt->execute()) {

                    $conn->commit();

                    $message =
                        "Your table has been reserved successfully! " .
                        ($payment_type === "Downpayment" ? "Downpayment" : "Full payment") .
                        " of ₱" . number_format($payment_amount, 2) .
                        " (Ref# " . $payment_reference . ") received.";

                } else {

                    $conn->rollback();

                    $error =
                        "Unable to save your reservation. Please try again.";
                }


                $stmt->close();
            }
        }
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
        Reserve a Table - Maturan's Art Cafe
    </title>

    <link rel="stylesheet" href="Css/style.css">
    <link rel="stylesheet" href="Css/reservation.css">
</head>


<body>


<!-- =========================
     HEADER
========================= -->

<?php include "header.php"; ?>



<!-- =========================
     RESERVATION SECTION
========================= -->

<section class="reservation-section reservation-page">


    <div class="reservation-content">

        <p class="logged-user">
            Logged in as:
            <strong>
                <?= htmlspecialchars($_SESSION["user_email"]) ?>
            </strong>
        </p>

        <p class="contact-small">
            PLAN YOUR VISIT
        </p>


        <h2>
            RESERVE A <span>TABLE.</span>
        </h2>


        <p>
            Planning to visit with friends or family?
            Reserve your table ahead of time and
            we'll have a cozy spot ready for you.
        </p>

    </div>



    <!-- =========================
         SUCCESS MESSAGE
    ========================== -->

    <?php if (!empty($message)): ?>

        <p class="login-success">

            <?= htmlspecialchars($message) ?>

        </p>

    <?php endif; ?>



    <!-- =========================
         ERROR MESSAGE
    ========================== -->

    <?php if (!empty($error)): ?>

        <p class="login-error">

            <?= htmlspecialchars($error) ?>

        </p>

    <?php endif; ?>



    <!-- =========================
         RESERVATION FORM
    ========================== -->

    <form
    class="reservation-form"
    method="POST"
    >

    <!-- NAME + PHONE -->

    <div class="form-row">

        <div class="form-group">

            <label for="reservation-name">
                NAME
            </label>

            <p id="reservation-name" class="reservation-name-display">
                <?= htmlspecialchars($_SESSION["user_name"]) ?>
            </p>

            <input
                type="hidden"
                name="reservation_name"
                value="<?= htmlspecialchars($_SESSION["user_name"]) ?>"
            >
        </div>

        
            <div class="form-group">

                <label for="reservation-phone">
                    PHONE
                </label>


                <input
                    type="tel"
                    id="reservation-phone"
                    name="reservation_phone"
                    placeholder="Your phone number"
                    required
                >

            </div>

        </div>



        <!-- DATE + TIME -->

        <div class="form-row">


            <div class="form-group">

                <label for="date">
                    DATE
                </label>


                <input
                    type="date"
                    id="date"
                    name="date"
                    required
                >

            </div>



            <div class="form-group">

                <label for="time">
                    TIME
                </label>


                <input
                    type="time"
                    id="time"
                    name="time"
                    required
                >

                <p id="tables-left-msg" class="tables-left-msg"></p>

            </div>

        </div>



        <!-- NUMBER OF GUESTS -->

        <div class="form-group">

            <label for="guests">
                NUMBER OF GUESTS
            </label>

            <p class="guests-hint">
                Each table seats 4 — larger groups may be split across
                a couple of tables so everyone has a seat.
            </p>


            <select
                id="guests"
                name="guests"
                required
            >

                <option value="">
                    Select number of guests
                </option>

                <option value="1">
                    1 Guest
                </option>

                <option value="2">
                    2 Guests
                </option>

                <option value="3">
                    3 Guests
                </option>

                <option value="4">
                    4 Guests
                </option>

                <option value="5">
                    5 Guests
                </option>

                <option value="6">
                    6 Guests
                </option>

                <option value="7">
                    7 Guests
                </option>

                <option value="8">
                    8 Guests
                </option>

                <option value="9">
                    9 Guests
                </option>

                <option value="10">
                    10 Guests
                </option>

            </select>

        </div>



        <!-- PAYMENT -->

        <div class="form-row">

            <div class="form-group">

                <label for="payment_type">
                    PAYMENT
                </label>

                <select
                    id="payment_type"
                    name="payment_type"
                    required
                >

                    <option value="">
                        Select payment option
                    </option>

                    <option value="Downpayment">
                        Downpayment (<?= DOWNPAYMENT_PERCENT ?>%)
                    </option>

                    <option value="Full">
                        Full Payment
                    </option>

                </select>

                <p id="payment-amount-msg" class="form-hint">
                    Select number of guests and a payment option to see the amount due.
                </p>

            </div>

            <div class="form-group">

                <label for="payment_reference">
                    PAYMENT REFERENCE NUMBER
                </label>

                <input
                    type="text"
                    id="payment_reference"
                    name="payment_reference"
                    placeholder="GCash / bank transfer reference no."
                    maxlength="50"
                    required
                >

                <p class="form-hint">
                    Please pay via GCash/bank transfer first, then enter the
                    reference number shown on your receipt.
                </p>

            </div>

        </div>



        <!-- SUBMIT -->

        <button
            type="submit"
            id="reservation-submit-btn"
            class="reservation-submit"
        >
            RESERVE NOW
        </button>

    </form>

</section>



<!-- =========================
     FOOTER
========================= -->

<?php include "footer.php"; ?>


<!-- =========================
     LIVE "TABLES LEFT" CHECK
========================= -->

<script>
(function () {

    const FEE_PER_GUEST = <?= (int) RESERVATION_FEE_PER_GUEST ?>;
    const DOWNPAYMENT_PERCENT = <?= (int) DOWNPAYMENT_PERCENT ?>;

    const dateInput = document.getElementById("date");
    const timeInput = document.getElementById("time");
    const guestsInput = document.getElementById("guests");
    const msgEl = document.getElementById("tables-left-msg");
    const submitBtn = document.getElementById("reservation-submit-btn");

    const paymentTypeInput = document.getElementById("payment_type");
    const paymentAmountMsgEl = document.getElementById("payment-amount-msg");

    function formatPeso(amount) {
        return "₱" + amount.toLocaleString("en-PH", {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function updatePaymentAmount() {

        const guests = parseInt(guestsInput.value, 10);
        const paymentType = paymentTypeInput.value;

        if (!guests || !paymentType) {
            paymentAmountMsgEl.textContent =
                "Select number of guests and a payment option to see the amount due.";
            return;
        }

        const total = guests * FEE_PER_GUEST;
        const amount = paymentType === "Downpayment"
            ? total * (DOWNPAYMENT_PERCENT / 100)
            : total;

        paymentAmountMsgEl.textContent =
            "Amount due (" + paymentType + "): " + formatPeso(amount);
    }

    guestsInput.addEventListener("change", updatePaymentAmount);
    paymentTypeInput.addEventListener("change", updatePaymentAmount);

    async function checkAvailability() {

        const date = dateInput.value;
        const time = timeInput.value;
        const guests = guestsInput.value;

        if (!date || !time) {
            msgEl.textContent = "";
            submitBtn.disabled = false;
            return;
        }

        msgEl.textContent = "Checking availability...";
        msgEl.classList.remove("tables-left-warning");

        try {

            let url =
                "check_availability.php?date=" +
                encodeURIComponent(date) +
                "&time=" +
                encodeURIComponent(time);

            if (guests) {
                url += "&guests=" + encodeURIComponent(guests);
            }

            const response = await fetch(url);
            const data = await response.json();

            if (!response.ok || data.error) {
                msgEl.textContent = "";
                submitBtn.disabled = false;
                return;
            }

            if (!data.fits) {

                if (guests && data.tables_needed > 1) {

                    msgEl.textContent =
                        "Your group needs " + data.tables_needed +
                        " tables, but only " + data.tables_left +
                        " of " + data.max_tables + " are left for this time.";

                } else {

                    msgEl.textContent =
                        "This time slot is fully booked. Please choose another date or time.";
                }

                msgEl.classList.add("tables-left-warning");
                submitBtn.disabled = true;

            } else {

                let text =
                    data.tables_left + " of " + data.max_tables + " tables left for this time.";

                if (guests && data.tables_needed > 1) {
                    text += " Your group will need " + data.tables_needed + " tables.";
                }

                msgEl.textContent = text;
                submitBtn.disabled = false;
            }

        } catch (err) {

            msgEl.textContent = "";
            submitBtn.disabled = false;
        }
    }

    dateInput.addEventListener("change", checkAvailability);
    timeInput.addEventListener("change", checkAvailability);
    guestsInput.addEventListener("change", checkAvailability);

})();
</script>

</body>

</html>