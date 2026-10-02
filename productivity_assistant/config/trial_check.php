<?php

// =====================================================
// TRIAL / SUBSCRIPTION ACCESS CHECK
// =====================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


// =====================================================
// CHECK LOGIN
// =====================================================

if (!isset($_SESSION["user_id"])) {

    header("Location: ../auth/login.php");
    exit;

}


$user_id = (int) $_SESSION["user_id"];


// =====================================================
// DATABASE
// =====================================================

require_once __DIR__ . "/db.php";


// =====================================================
// GET SUBSCRIPTION DATA
// =====================================================

$stmt = $conn->prepare(
    "SELECT
        trial_start,
        trial_end,
        subscription_status,
        plan
     FROM users
     WHERE id = ?"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();

$user = $result->fetch_assoc();

$stmt->close();


// =====================================================
// USER NOT FOUND
// =====================================================

if (!$user) {

    session_unset();
    session_destroy();

    header("Location: ../auth/login.php");
    exit;

}


// =====================================================
// SUBSCRIPTION INFORMATION
// =====================================================

$trial_start = $user["trial_start"];
$trial_end = $user["trial_end"];

$subscription_status =
    $user["subscription_status"];

$plan = $user["plan"];


// =====================================================
// CHECK PAID SUBSCRIPTION
// =====================================================

if ($subscription_status === "active") {

    return;

}


// =====================================================
// CHECK FREE TRIAL
// =====================================================

$trial_active = false;
$remaining_days = 0;

if (!empty($trial_end)) {

    $current_time = new DateTime();

    $end_time = new DateTime($trial_end);


    if ($end_time > $current_time) {

        $trial_active = true;

        $remaining_days =
            (int) $current_time
                ->diff($end_time)
                ->days;

        // Show minimum 1 day
        if ($remaining_days < 1) {

            $remaining_days = 1;

        }

    }

}


// =====================================================
// TRIAL EXPIRED
// =====================================================

if (!$trial_active) {

    // Update status
    $update = $conn->prepare(
        "UPDATE users
         SET subscription_status = 'expired'
         WHERE id = ?"
    );

    $update->bind_param(
        "i",
        $user_id
    );

    $update->execute();

    $update->close();


    header(
        "Location: ../subscription/index.php?expired=1"
    );

    exit;

}