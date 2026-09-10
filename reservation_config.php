<?php
if (!defined("MAX_TABLES")) {
    define("MAX_TABLES", 10);
}

if (!defined("SLOT_MINUTES")) {
    define("SLOT_MINUTES", 30);
}


if (!defined("NO_SHOW_GRACE_MINUTES")) {
    define("NO_SHOW_GRACE_MINUTES", 60);
}


if (!defined("GUESTS_PER_TABLE")) {
    define("GUESTS_PER_TABLE", 4);
}


/* =========================
   RESERVATION PAYMENT
========================= */

/* Reservation fee charged per guest, in PHP. */
if (!defined("RESERVATION_FEE_PER_GUEST")) {
    define("RESERVATION_FEE_PER_GUEST", 150);
}

/* What percentage of the total fee counts as a downpayment. */
if (!defined("DOWNPAYMENT_PERCENT")) {
    define("DOWNPAYMENT_PERCENT", 50);
}

/* The only payment types a reservation may be saved with. */
if (!defined("VALID_PAYMENT_TYPES")) {
    define("VALID_PAYMENT_TYPES", ["Downpayment", "Full"]);
}


/**
 * How many tables a party of this size needs.
 * A table seats GUESTS_PER_TABLE guests, so parties
 * bigger than that take up more than one table.
 *
 * Example (GUESTS_PER_TABLE = 4): 5 guests -> 2 tables.
 */
function tables_needed_for_guests(int $guests): int
{
    if ($guests < 1) {
        return 0;
    }

    return (int) ceil($guests / GUESTS_PER_TABLE);
}


/**
 * The total reservation fee for a party of this size,
 * before any downpayment discount is applied.
 */
function total_reservation_fee(int $guests): float
{
    if ($guests < 1) {
        return 0;
    }

    return $guests * RESERVATION_FEE_PER_GUEST;
}


/**
 * The amount actually due for a given payment type.
 * "Downpayment" is DOWNPAYMENT_PERCENT of the total fee;
 * "Full" is the whole fee. Computed server-side so the
 * amount can't be tampered with client-side.
 */
function calculate_payment_amount(int $guests, string $payment_type): float
{
    $total = total_reservation_fee($guests);

    if ($payment_type === "Downpayment") {
        return round($total * (DOWNPAYMENT_PERCENT / 100), 2);
    }

    return round($total, 2);
}

function expire_stale_reservations(mysqli $conn): void
{
    $conn->query(
        "UPDATE reservations
         SET status = 'No-Show'
         WHERE status = 'Pending'
           AND TIMESTAMP(reservation_date, reservation_time)
               < (NOW() - INTERVAL " . NO_SHOW_GRACE_MINUTES . " MINUTE)"
    );
}


/**
 * A table that was checked in ("Seated") but whose reservation
 * date has fully passed is done -- there's no realistic scenario
 * where the guest is still sitting there. Close it out as
 * "Completed" so it stops showing as an active/occupied table
 * once it lands in the Past filter.
 */
function complete_past_reservations(mysqli $conn): void
{
    $conn->query(
        "UPDATE reservations
         SET status = 'Completed'
         WHERE status = 'Seated'
           AND reservation_date < CURDATE()"
    );
}


/**
 * Round a time string ("H:i" or "H:i:s") down to the
 * start of the slot it belongs to.
 *
 * Example (SLOT_MINUTES = 30): "17:12" -> "17:00:00"
 */
function get_slot_start(string $time): ?string
{
    $dt = DateTime::createFromFormat("H:i:s", $time);

    if (!$dt) {
        $dt = DateTime::createFromFormat("H:i", $time);
    }

    if (!$dt) {
        return null;
    }

    $minutes = (int) $dt->format("i");
    $rounded = floor($minutes / SLOT_MINUTES) * SLOT_MINUTES;

    $dt->setTime((int) $dt->format("H"), (int) $rounded, 0);

    return $dt->format("H:i:s");
}


/**
 * Get the end of a slot, given its start.
 */
function get_slot_end(string $slot_start): string
{
    $dt = DateTime::createFromFormat("H:i:s", $slot_start);
    $dt->modify("+" . SLOT_MINUTES . " minutes");

    return $dt->format("H:i:s");
}


/**
 * Count how many reservations already exist in the same
 * slot (same date, same rounded time block).
 *
 * Pass $exclude_id when checking during an edit, so a
 * reservation doesn't count against itself.
 */
function count_reservations_in_slot(
    mysqli $conn,
    string $date,
    string $time,
    ?int $exclude_id = null
): int {

    $slot_start = get_slot_start($time);

    if ($slot_start === null) {
        return 0;
    }

    $slot_end = get_slot_end($slot_start);

    if ($exclude_id !== null) {

        $stmt = $conn->prepare(
            "SELECT COUNT(*) AS c
             FROM reservations
             WHERE reservation_date = ?
               AND reservation_time >= ?
               AND reservation_time < ?
               AND reservation_id != ?
               AND status IN ('Pending', 'Seated')"
        );

        $stmt->bind_param(
            "sssi",
            $date,
            $slot_start,
            $slot_end,
            $exclude_id
        );

    } else {

        $stmt = $conn->prepare(
            "SELECT COUNT(*) AS c
             FROM reservations
             WHERE reservation_date = ?
               AND reservation_time >= ?
               AND reservation_time < ?
               AND status IN ('Pending', 'Seated')"
        );

        $stmt->bind_param(
            "sss",
            $date,
            $slot_start,
            $slot_end
        );
    }

    $stmt->execute();

    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $stmt->close();

    return (int) $row["c"];
}


/**
 * Sum how many TABLES are actually in use for a slot,
 * accounting for parties that need more than one table
 * (any reservation over GUESTS_PER_TABLE guests).
 *
 * This is what should be compared against MAX_TABLES —
 * count_reservations_in_slot() only counts reservations,
 * not the tables they occupy.
 *
 * Pass $exclude_id when checking during an edit, so a
 * reservation doesn't count against itself.
 */
function count_tables_used_in_slot(
    mysqli $conn,
    string $date,
    string $time,
    ?int $exclude_id = null
): int {

    $slot_start = get_slot_start($time);

    if ($slot_start === null) {
        return 0;
    }

    $slot_end = get_slot_end($slot_start);

    $guests_per_table = (int) GUESTS_PER_TABLE;

    if ($exclude_id !== null) {

        $stmt = $conn->prepare(
            "SELECT COALESCE(SUM(CEIL(guests / $guests_per_table)), 0) AS tables_used
             FROM reservations
             WHERE reservation_date = ?
               AND reservation_time >= ?
               AND reservation_time < ?
               AND reservation_id != ?
               AND status IN ('Pending', 'Seated')
             FOR UPDATE"
        );

        $stmt->bind_param(
            "sssi",
            $date,
            $slot_start,
            $slot_end,
            $exclude_id
        );

    } else {

        $stmt = $conn->prepare(
            "SELECT COALESCE(SUM(CEIL(guests / $guests_per_table)), 0) AS tables_used
             FROM reservations
             WHERE reservation_date = ?
               AND reservation_time >= ?
               AND reservation_time < ?
               AND status IN ('Pending', 'Seated')
             FOR UPDATE"
        );

        $stmt->bind_param(
            "sss",
            $date,
            $slot_start,
            $slot_end
        );
    }

    $stmt->execute();

    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    $stmt->close();

    return (int) $row["tables_used"];
}

?>