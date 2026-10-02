<?php

session_start();

require_once "../config/db.php";


// =====================================================
// LOGIN CHECK
// =====================================================

if (!isset($_SESSION["user_id"])) {

    header("Location: ../auth/login.php");
    exit;

}


$user_id = (int) $_SESSION["user_id"];

$user_name =
    $_SESSION["user_name"] ?? "User";


// =====================================================
// GET USER SUBSCRIPTION
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

$stmt->bind_param(
    "i",
    $user_id
);

$stmt->execute();

$result =
    $stmt->get_result();


if ($result->num_rows !== 1) {

    $stmt->close();

    session_destroy();

    header("Location: ../auth/login.php");
    exit;

}


$user =
    $result->fetch_assoc();

$stmt->close();


// =====================================================
// SUBSCRIPTION INFORMATION
// =====================================================

$trial_start =
    $user["trial_start"];

$trial_end =
    $user["trial_end"];

$subscription_status =
    $user["subscription_status"];

$plan =
    $user["plan"];


// =====================================================
// CURRENT TIME
// =====================================================

$current_time =
    new DateTime();


// =====================================================
// TRIAL STATUS + REMAINING DAYS
// =====================================================

$remaining_days = 0;

$trial_active = false;

$trial_expired = false;


if (!empty($trial_end)) {

    $end_time =
        new DateTime($trial_end);


    // -------------------------------------------------
    // TRIAL ACTIVE
    // -------------------------------------------------

    if (
        $subscription_status === "trial"
        &&
        $end_time > $current_time
    ) {

        $trial_active = true;


        // Calculate exact remaining seconds

        $remaining_seconds =
            $end_time->getTimestamp()
            -
            $current_time->getTimestamp();


        // Round UP so active trial never shows 0
        $remaining_days =
            (int) ceil(
                $remaining_seconds / 86400
            );


        if ($remaining_days < 1) {

            $remaining_days = 1;

        }

    }


    // -------------------------------------------------
    // TRIAL EXPIRED
    // -------------------------------------------------

    elseif (
        $subscription_status === "trial"
        &&
        $end_time <= $current_time
    ) {

        $trial_expired = true;

    }


    // -------------------------------------------------
    // ALREADY EXPIRED
    // -------------------------------------------------

    elseif (
        $subscription_status === "expired"
    ) {

        $trial_expired = true;

    }

}


// =====================================================
// PAID SUBSCRIPTION
// =====================================================

$is_paid =
    (
        $subscription_status === "active"
        ||
        $subscription_status === "paid"
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
        Subscription -TaskPulse AI
    </title>


    <!-- Bootstrap -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- Font Awesome -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >


    <style>

        body {

            background: #f5f7fb;

            color: #212529;

        }


        .subscription-wrapper {

            min-height: 100vh;

            display: flex;

            align-items: center;

            justify-content: center;

            padding: 30px 15px;

        }


        .subscription-card {

            width: 100%;

            max-width: 650px;

            background: #ffffff;

            border: 1px solid #e9ecef;

            border-radius: 20px;

            box-shadow:
                0 12px 40px
                rgba(0, 0, 0, 0.07);

            overflow: hidden;

        }


        .subscription-header {

            background:
                linear-gradient(
                    135deg,
                    #0d6efd,
                    #0a58ca
                );

            color: #ffffff;

            padding: 35px 30px;

            text-align: center;

        }


        .subscription-icon {

            width: 65px;

            height: 65px;

            display: inline-flex;

            align-items: center;

            justify-content: center;

            border-radius: 18px;

            background:
                rgba(255, 255, 255, 0.15);

            font-size: 27px;

            margin-bottom: 15px;

        }


        .subscription-header h2 {

            font-weight: 700;

            margin-bottom: 8px;

        }


        .subscription-header p {

            margin: 0;

            opacity: 0.9;

        }


        .subscription-body {

            padding: 30px;

        }


        .status-card {

            background: #f8f9fa;

            border: 1px solid #e9ecef;

            border-radius: 15px;

            padding: 20px;

            margin-bottom: 20px;

        }


        .status-label {

            color: #6c757d;

            font-size: 13px;

            margin-bottom: 5px;

        }


        .status-value {

            font-weight: 700;

            font-size: 17px;

        }


        .trial-count {

            font-size: 42px;

            font-weight: 800;

            color: #0d6efd;

            line-height: 1;

        }


        .trial-text {

            color: #6c757d;

            margin-top: 8px;

        }


        .info-row {

            display: flex;

            justify-content: space-between;

            gap: 20px;

            padding: 13px 0;

            border-bottom: 1px solid #e9ecef;

        }


        .info-row:last-child {

            border-bottom: none;

        }


        .info-label {

            color: #6c757d;

        }


        .info-value {

            font-weight: 600;

            text-align: right;

        }


        .active-badge {

            background: #d1e7dd;

            color: #0f5132;

            padding: 6px 12px;

            border-radius: 20px;

            font-size: 13px;

            font-weight: 600;

        }


        .expired-badge {

            background: #f8d7da;

            color: #842029;

            padding: 6px 12px;

            border-radius: 20px;

            font-size: 13px;

            font-weight: 600;

        }


        .upgrade-box {

            background: #eef5ff;

            border: 1px solid #cfe2ff;

            border-radius: 15px;

            padding: 20px;

            margin-top: 20px;

        }


        .upgrade-box h5 {

            font-weight: 700;

        }


        .upgrade-box p {

            color: #6c757d;

            margin-bottom: 15px;

        }


        .btn-dashboard {

            border-radius: 10px;

            padding: 11px 18px;

            font-weight: 600;

        }


        @media (max-width: 576px) {

            .subscription-header {

                padding: 30px 20px;

            }


            .subscription-body {

                padding: 20px;

            }


            .info-row {

                flex-direction: column;

                gap: 4px;

            }


            .info-value {

                text-align: left;

            }

        }

    </style>

</head>


<body>


<div class="subscription-wrapper">


    <div class="subscription-card">


        <!-- =================================================
             HEADER
        ================================================== -->

        <div class="subscription-header">


            <div class="subscription-icon">

                <i class="fa-solid fa-gift"></i>

            </div>


            <h2>

                Your Free Trial

            </h2>


            <p>

                Welcome, <?= htmlspecialchars($user_name) ?>

            </p>


        </div>


        <!-- =================================================
             BODY
        ================================================== -->

        <div class="subscription-body">


            <?php if ($trial_active): ?>


                <!-- =========================================
                     ACTIVE TRIAL
                ========================================== -->

                <div class="status-card text-center">


                    <div class="trial-count">

                        <?= $remaining_days ?>

                    </div>


                    <div class="trial-text">

                        Days Remaining

                    </div>


                </div>


                <div class="status-card">


                    <div class="info-row">

                        <span class="info-label">

                            Current Plan

                        </span>


                        <span class="info-value">

                            15-Day Free Trial

                        </span>

                    </div>


                    <div class="info-row">

                        <span class="info-label">

                            Status

                        </span>


                        <span class="info-value">

                            <span class="active-badge">

                                Active

                            </span>

                        </span>

                    </div>


                    <div class="info-row">

                        <span class="info-label">

                            Trial Started

                        </span>


                        <span class="info-value">

                            <?= htmlspecialchars(
                                date(
                                    "M d, Y",
                                    strtotime($trial_start)
                                )
                            ) ?>

                        </span>

                    </div>


                    <div class="info-row">

                        <span class="info-label">

                            Trial Ends

                        </span>


                        <span class="info-value">

                            <?= htmlspecialchars(
                                date(
                                    "M d, Y",
                                    strtotime($trial_end)
                                )
                            ) ?>

                        </span>

                    </div>


                </div>


                <div class="upgrade-box">


                    <h5>

                        <i
                            class="fa-solid
                                   fa-circle-info
                                   me-1"
                        ></i>

                        Enjoy your free trial

                    </h5>


                    <p>

                        You currently have access to
                        the productivity assistant during
                        your free trial period.

                    </p>


                    <a
                        href="../dashboard/index.php"
                        class="btn btn-primary
                               btn-dashboard"
                    >

                        <i
                            class="fa-solid
                                   fa-arrow-left
                                   me-2"
                        ></i>

                        Back to Dashboard

                    </a>


                </div>


            <?php else: ?>


                <!-- =========================================
                     EXPIRED
                ========================================== -->

                <div class="status-card text-center">


                    <div class="mb-3">

                        <i
                            class="fa-solid
                                   fa-clock
                                   text-danger"
                            style="font-size: 42px;"
                        ></i>

                    </div>


                    <h4 class="fw-bold">

                        Your Free Trial Has Ended

                    </h4>


                    <p class="text-muted mb-0">

                        Your 15-day free trial has expired.
                        Please choose a subscription plan
                        to continue using the service.

                    </p>


                </div>


                <div class="status-card">


                    <div class="info-row">

                        <span class="info-label">

                            Plan

                        </span>


                        <span class="info-value">

                            15-Day Free Trial

                        </span>

                    </div>


                    <div class="info-row">

                        <span class="info-label">

                            Status

                        </span>


                        <span class="info-value">

                            <span class="expired-badge">

                                Expired

                            </span>

                        </span>

                    </div>


                    <?php if (!empty($trial_end)): ?>

                        <div class="info-row">

                            <span class="info-label">

                                Trial Ended

                            </span>


                            <span class="info-value">

                                <?= htmlspecialchars(
                                    date(
                                        "M d, Y",
                                        strtotime($trial_end)
                                    )
                                ) ?>

                            </span>

                        </div>

                    <?php endif; ?>


                </div>


            <!-- =========================================
     SUBSCRIPTION PLANS
========================================== -->

<div class="mt-4">

    <h4 class="fw-bold text-center mb-3">
        Choose Your Plan
    </h4>

    <p class="text-muted text-center mb-4">
        Upgrade when your free trial ends.
    </p>


    <div class="row g-3">


        <!-- MONTHLY PLAN -->

        <div class="col-md-6">

            <div class="status-card h-100">

                <div class="text-center">

                    <div class="mb-2">

                        <i
                            class="fa-solid fa-calendar-days
                                   text-primary"
                            style="font-size: 32px;"
                        ></i>

                    </div>

                    <h5 class="fw-bold">
                        Monthly Plan
                    </h5>

                    <h3 class="fw-bold">
                        $5
                        <small
                            class="text-muted fs-6"
                        >
                            / month
                        </small>
                    </h3>

                    <p class="text-muted small">
                        Full access for 30 days.
                    </p>

                    <button
                        type="button"
                        class="btn btn-primary w-100"
                        disabled
                    >
                        Coming Soon
                    </button>

                </div>

            </div>

        </div>


        <!-- YEARLY PLAN -->

        <div class="col-md-6">

            <div class="status-card h-100">

                <div class="text-center">

                    <div class="mb-2">

                        <i
                            class="fa-solid fa-crown
                                   text-warning"
                            style="font-size: 32px;"
                        ></i>

                    </div>

                    <h5 class="fw-bold">
                        Yearly Plan
                    </h5>

                    <h3 class="fw-bold">
                        $50
                        <small
                            class="text-muted fs-6"
                        >
                            / year
                        </small>
                    </h3>

                    <p class="text-muted small">
                        Full access for 365 days.
                    </p>

                    <button
                        type="button"
                        class="btn btn-warning w-100"
                        disabled
                    >
                        Coming Soon
                    </button>

                </div>

            </div>

        </div>


    </div>


    <!-- BACK TO DASHBOARD -->

    <div class="text-center mt-4">

        <a
            href="../dashboard/index.php"
            class="btn btn-outline-dark btn-dashboard"
        >

            <i
                class="fa-solid
                       fa-arrow-left
                       me-2"
            ></i>

            Back to Dashboard

        </a>

    </div>

</div>


            <?php endif; ?>


        </div>

    </div>

</div>


</body>

</html>