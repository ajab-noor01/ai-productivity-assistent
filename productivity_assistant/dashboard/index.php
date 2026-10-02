<?php

session_start();

// =====================================================
// LOGIN CHECK
// =====================================================

if (!isset($_SESSION["user_id"])) {

    header("Location: ../auth/login.php");
    exit;

}

// =====================================================
// TRIAL / SUBSCRIPTION ACCESS CHECK
// =====================================================

require_once('../config/trial_check.php');

// =====================================================
// DATABASE
// =====================================================

require_once('../config/db.php');




// =====================================================
// CHECK LOGIN
// =====================================================

if (!isset($_SESSION["user_id"])) {

    header("Location: ../auth/login.php");
    exit;

}


$user_id = (int) $_SESSION["user_id"];

$user_name =
    $_SESSION["user_name"] ?? "User";

    // =====================================================
// FREE TRIAL
// =====================================================

$stmt = $conn->prepare(
    "SELECT trial_end, subscription_status, plan
     FROM users
     WHERE id = ?"
);

$stmt->bind_param("i", $user_id);
$stmt->execute();

$result = $stmt->get_result();
$subscription = $result->fetch_assoc();

$stmt->close();

$trial_end = $subscription["trial_end"];
$subscription_status = $subscription["subscription_status"];
$plan = $subscription["plan"];

$trial_remaining_days = 0;
$trial_active = false;

if (
    $subscription_status === "trial"
    && !empty($trial_end)
) {

    $now = new DateTime();
    $end = new DateTime($trial_end);

    if ($end > $now) {

        $seconds =
            $end->getTimestamp()
            - $now->getTimestamp();

        $trial_remaining_days =
            (int) ceil($seconds / 86400);

        if ($trial_remaining_days < 1) {
            $trial_remaining_days = 1;
        }

        $trial_active = true;
    }
}

// =====================================================
// TASK STATISTICS
// =====================================================

// Total Tasks

$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM tasks
     WHERE user_id = ?"
);

$stmt->bind_param(
    "i",
    $user_id
);

$stmt->execute();

$result =
    $stmt->get_result();

$total_tasks =
    $result->fetch_assoc()["total"];

$stmt->close();


// Completed Tasks

$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM tasks
     WHERE user_id = ?
     AND status = 'Completed'"
);

$stmt->bind_param(
    "i",
    $user_id
);

$stmt->execute();

$result =
    $stmt->get_result();

$completed_tasks =
    $result->fetch_assoc()["total"];

$stmt->close();


// Pending Tasks

$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM tasks
     WHERE user_id = ?
     AND status = 'Pending'"
);

$stmt->bind_param(
    "i",
    $user_id
);

$stmt->execute();

$result =
    $stmt->get_result();

$pending_tasks =
    $result->fetch_assoc()["total"];

$stmt->close();


// =====================================================
// NOTES
// =====================================================

$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM notes
     WHERE user_id = ?"
);

$stmt->bind_param(
    "i",
    $user_id
);

$stmt->execute();

$result =
    $stmt->get_result();

$total_notes =
    $result->fetch_assoc()["total"];

$stmt->close();


// =====================================================
// REMINDERS
// =====================================================

// Pending Reminders

$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM reminders
     WHERE user_id = ?
     AND status = 'Pending'"
);

$stmt->bind_param(
    "i",
    $user_id
);

$stmt->execute();

$result =
    $stmt->get_result();

$pending_reminders =
    $result->fetch_assoc()["total"];

$stmt->close();


// Completed Reminders

$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM reminders
     WHERE user_id = ?
     AND status = 'Completed'"
);

$stmt->bind_param(
    "i",
    $user_id
);

$stmt->execute();

$result =
    $stmt->get_result();

$completed_reminders =
    $result->fetch_assoc()["total"];

$stmt->close();


// =====================================================
// COMPLETION RATE
// =====================================================

$completion_rate =
    ($total_tasks > 0)
        ? round(
            ($completed_tasks / $total_tasks) * 100
        )
        : 0;


// =====================================================
// UPCOMING TASKS
// =====================================================

$stmt = $conn->prepare(
    "SELECT
        id,
        title,
        priority,
        due_date,
        status
     FROM tasks
     WHERE user_id = ?
     AND status = 'Pending'
     AND due_date IS NOT NULL
     AND due_date >= CURDATE()
     ORDER BY due_date ASC
     LIMIT 5"
);

$stmt->bind_param(
    "i",
    $user_id
);

$stmt->execute();

$upcoming_tasks =
    $stmt->get_result();

$stmt->close();


// =====================================================
// UPCOMING REMINDERS
// =====================================================

$stmt = $conn->prepare(
    "SELECT
        id,
        description,
        reminder_date,
        reminder_time,
        status
     FROM reminders
     WHERE user_id = ?
     AND status = 'Pending'
     AND (
        reminder_date > CURDATE()
        OR (
            reminder_date = CURDATE()
            AND reminder_time >= CURTIME()
        )
     )
     ORDER BY
        reminder_date ASC,
        reminder_time ASC
     LIMIT 5"
);

$stmt->bind_param(
    "i",
    $user_id
);

$stmt->execute();

$upcoming_reminders =
    $stmt->get_result();

$stmt->close();

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
        Dashboard |TaskPulse AI
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


    <!-- Existing CSS -->

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >


<style>

/* =================================================
   GLOBAL
================================================= */

body {

    background: #f5f7fb;

    color: #212529;

}


/* =================================================
   NAVBAR
================================================= */

.main-navbar {

    min-height: 68px;

    background: #0d6efd !important;

}


.brand-icon {

    width: 38px;

    height: 38px;

    display: inline-flex;

    align-items: center;

    justify-content: center;

    border-radius: 10px;

    background: rgba(
        255,
        255,
        255,
        0.16
    );

    margin-right: 8px;

}


/* =================================================
   SIDEBAR
================================================= */

.sidebar {

    min-height: calc(100vh - 68px);

    background: #ffffff;

    border-right: 1px solid #e5e7eb;

}


.sidebar-title {

    font-size: 11px;

    font-weight: 700;

    letter-spacing: 1px;

    color: #9ca3af;

}


.sidebar-link {

    border: none !important;

    border-radius: 10px !important;

    margin-bottom: 5px;

    padding: 11px 13px;

    color: #495057;

    transition: all 0.2s ease;

}


.sidebar-link:hover {

    background: #eaf2ff;

    color: #0d6efd;

    transform: translateX(2px);

}


.sidebar-link.active {

    background: #0d6efd;

    color: #ffffff;

    box-shadow:
        0 4px 10px
        rgba(13, 110, 253, 0.20);

}


/* =================================================
   MAIN CONTENT
================================================= */

.dashboard-main {

    padding: 30px;

}


/* =================================================
   PAGE HEADER
================================================= */

.page-header {

    background: #ffffff;

    border: 1px solid #e5e7eb;

    border-radius: 16px;

    padding: 24px;

    box-shadow:
        0 5px 20px
        rgba(13, 110, 253, 0.04);

}


.page-title {

    font-size: 28px;

    font-weight: 700;

    margin-bottom: 5px;

    color: #1f2937;

}


.page-subtitle {

    color: #6b7280;

    margin-bottom: 0;

}


/* =================================================
   STAT CARDS
================================================= */

.stat-card {

    background: #ffffff;

    border: 1px solid #e5e7eb;

    border-radius: 16px;

    box-shadow:
        0 5px 20px
        rgba(13, 110, 253, 0.04);

    transition: all 0.2s ease;

}


.stat-card:hover {

    transform: translateY(-4px);

    box-shadow:
        0 10px 28px
        rgba(13, 110, 253, 0.10);

}


.stat-icon {

    width: 48px;

    height: 48px;

    border-radius: 12px;

    display: flex;

    align-items: center;

    justify-content: center;

    color: #ffffff;

    font-size: 19px;

}


.stat-label {

    color: #6b7280;

    font-size: 14px;

    margin-bottom: 5px;

}


.stat-number {

    font-size: 27px;

    font-weight: 700;

    margin: 0;

    color: #1f2937;

}


/* =================================================
   CONTENT CARDS
================================================= */

.content-card {

    background: #ffffff;

    border: 1px solid #e5e7eb;

    border-radius: 16px;

    box-shadow:
        0 5px 20px
        rgba(13, 110, 253, 0.04);

}


.section-title {

    font-size: 18px;

    font-weight: 700;

    color: #1f2937;

}


/* =================================================
   LIST ITEMS
================================================= */

.task-item {

    background: #f8faff;

    border-left: 4px solid #0d6efd;

    border-radius: 10px;

    padding: 13px;

    margin-bottom: 10px;

    transition: 0.2s ease;

}


.task-item:hover {

    background: #eef5ff;

}


.reminder-item {

    background: #fffaf0;

    border-left: 4px solid #ffc107;

    border-radius: 10px;

    padding: 13px;

    margin-bottom: 10px;

    transition: 0.2s ease;

}


.reminder-item:hover {

    background: #fff7df;

}


/* =================================================
   QUICK ACTIONS
================================================= */

.quick-action {

    border-radius: 12px;

    padding: 15px;

    font-weight: 600;

    transition: all 0.2s ease;

}


.quick-action:hover {

    transform: translateY(-2px);

    box-shadow:
        0 6px 15px
        rgba(13, 110, 253, 0.15);

}


/* =================================================
   AI CARD
================================================= */

.ai-card {

    background:
        linear-gradient(
            135deg,
            #0d6efd,
            #0b5ed7
        );

    color: #ffffff;

    border-radius: 16px;

    overflow: hidden;

    box-shadow:
        0 8px 25px
        rgba(13, 110, 253, 0.20);

}


.ai-card p {

    color: #e8f1ff;

}


.ai-icon {

    width: 55px;

    height: 55px;

    border-radius: 14px;

    background: rgba(
        255,
        255,
        255,
        0.16
    );

    display: flex;

    align-items: center;

    justify-content: center;

    font-size: 24px;

}


/* =================================================
   PROGRESS
================================================= */

.progress {

    height: 9px;

    border-radius: 20px;

    background: #e9f1ff;

}


.progress-bar {

    background: #0d6efd !important;

    border-radius: 20px;

}


/* =================================================
   BUTTONS
================================================= */

.btn-primary {

    background: #0d6efd;

    border-color: #0d6efd;

}


.btn-primary:hover {

    background: #0b5ed7;

    border-color: #0b5ed7;

}


.btn-dark {

    background: #0d6efd;

    border-color: #0d6efd;

}


.btn-dark:hover {

    background: #0b5ed7;

    border-color: #0b5ed7;

}


/* =================================================
   MOBILE
================================================= */

@media (max-width: 991px) {

    .sidebar {

        min-height: auto;

        border-right: none;

        border-bottom:
            1px solid #e5e7eb;

    }


    .dashboard-main {

        padding: 20px;

    }

}


@media (max-width: 576px) {

    .page-title {

        font-size: 22px;

    }


    .dashboard-main {

        padding: 15px;

    }

}

</style>

</head>


<body>


<!-- =====================================================
     NAVBAR
===================================================== -->

<nav
    class="navbar navbar-dark bg-dark shadow-sm main-navbar"
>

    <div class="container-fluid px-3 px-lg-4">


        <!-- BRAND -->

        <a
            href="index.php"
            class="navbar-brand fw-bold d-flex align-items-center"
        >

            <span class="brand-icon">

                <i class="fa-solid fa-brain"></i>

            </span>

           TaskPulse AI

        </a>


        <!-- RIGHT SIDE -->

        <div
            class="d-flex align-items-center gap-2"
        >

            <a
                href="../search/index.php"
                class="btn btn-outline-light btn-sm"
            >

                <i
                    class="fa-solid
                           fa-magnifying-glass
                           me-1"
                ></i>

                <span class="d-none d-md-inline">

                    Search

                </span>

            </a>


            <span
                class="text-white d-none d-md-inline"
            >

                <i
                    class="fa-solid fa-user me-1"
                ></i>

                <?= htmlspecialchars($user_name) ?>

            </span>


            <a
                href="../auth/logout.php"
                class="btn btn-light btn-sm"
            >

                <i
                    class="fa-solid
                           fa-right-from-bracket
                           me-1"
                ></i>

                Logout

            </a>

        </div>

    </div>

</nav>


<!-- =====================================================
     LAYOUT
===================================================== -->

<div class="container-fluid">

    <div class="row">


        <!-- =================================================
             SIDEBAR
        ================================================= -->

        <aside
            class="col-lg-2 sidebar p-3"
        >

            <div class="sidebar-title mb-3">

                WORKSPACE

            </div>


            <div
                class="list-group list-group-flush"
            >


                <!-- Dashboard -->

                <a
                    href="index.php"
                    class="list-group-item
                           sidebar-link
                           active"
                >

                    <i
                        class="fa-solid
                               fa-gauge
                               fa-fw
                               me-2"
                    ></i>

                    Dashboard

                </a>


                <!-- Tasks -->

                <a
                    href="../tasks/index.php"
                    class="list-group-item
                           sidebar-link"
                >

                    <i
                        class="fa-solid
                               fa-list-check
                               fa-fw
                               me-2"
                    ></i>

                    Tasks

                </a>


                <!-- Notes -->

                <a
                    href="../notes/index.php"
                    class="list-group-item
                           sidebar-link"
                >

                    <i
                        class="fa-solid
                               fa-note-sticky
                               fa-fw
                               me-2"
                    ></i>

                    Notes

                </a>


                <!-- Reminders -->

                <a
                    href="../reminder/index.php"
                    class="list-group-item
                           sidebar-link"
                >

                    <i
                        class="fa-solid
                               fa-bell
                               fa-fw
                               me-2"
                    ></i>

                    Reminders

                </a>


                <!-- Calendar -->

                <a
                    href="../calendar/index.php"
                    class="list-group-item
                           sidebar-link"
                >

                    <i
                        class="fa-solid
                               fa-calendar
                               fa-fw
                               me-2"
                    ></i>

                    Calendar

                </a>


                <!-- AI -->

                <a
                    href="../ai/index.php"
                    class="list-group-item
                           sidebar-link"
                >

                    <i
                        class="fa-solid
                               fa-robot
                               fa-fw
                               me-2"
                    ></i>

                   TaskPulse AI

                </a>


                <!-- Analytics -->

                <a
                    href="../analytics/index.php"
                    class="list-group-item
                           sidebar-link"
                >

                    <i
                        class="fa-solid
                               fa-chart-line
                               fa-fw
                               me-2"
                    ></i>

                    Analytics

                </a>

            </div>


        </aside>


        <!-- =================================================
             MAIN
        ================================================= -->

        <main
            class="col-lg-10 dashboard-main"
        >

<?php if ($subscription_status === "active"): ?>

    <div class="alert alert-success d-flex align-items-center justify-content-between">

        <div>

            <strong>
                Active Subscription
            </strong>

            <div class="small">
                Your account has full access to all features.
            </div>

        </div>

        <span class="badge bg-success">
            Active
        </span>

    </div>


<?php elseif ($trial_active): ?>

    <div class="alert alert-primary d-flex align-items-center justify-content-between">

        <div>

            <strong>
                Free Trial
            </strong>

            <div class="small">
                <?= $trial_remaining_days ?> days remaining
            </div>

        </div>

        <a
            href="../subscription/index.php"
            class="btn btn-primary btn-sm"
        >
            View Plans
        </a>

    </div>


<?php else: ?>

    <div class="alert alert-warning d-flex align-items-center justify-content-between">

        <div>

            <strong>
                Free Trial Expired
            </strong>

            <div class="small">
                Your 15-day free trial has ended.
            </div>

        </div>

        <a
            href="../subscription/index.php"
            class="btn btn-warning btn-sm"
        >
            Subscribe Now
        </a>

    </div>

<?php endif; ?>
            <!-- =================================================
                 PROFESSIONAL HEADER
            ================================================= -->

            <div
                class="page-header mb-4"
            >

                <div
                    class="d-flex
                           flex-column
                           flex-md-row
                           justify-content-between
                           align-items-md-center
                           gap-3"
                >

                    <div>

                        <div
                            class="text-uppercase
                                   small
                                   fw-semibold
                                   text-muted
                                   mb-1"
                        >

                            Productivity Overview

                        </div>


                        <h1
                            class="page-title"
                        >

                            Your Workspace

                        </h1>


                        <p
                            class="page-subtitle"
                        >

                            Manage your tasks,
                            notes, reminders and
                            daily productivity
                            from one place.

                        </p>

                    </div>


                    <a
                        href="../ai/index.php"
                        class="btn btn-dark px-4 py-2"
                    >

                        <i
                            class="fa-solid
                                   fa-robot
                                   me-2"
                        ></i>

                        Open TaskPulse AI

                    </a>

                </div>

            </div>


            <!-- =================================================
                 STATISTICS
            ================================================= -->

            <div
                class="row g-4 mb-4"
            >


                <!-- Total Tasks -->

                <div
                    class="col-md-6 col-xl-3"
                >

                    <div
                        class="stat-card h-100 p-4"
                    >

                        <div
                            class="d-flex
                                   justify-content-between
                                   align-items-start"
                        >

                            <div>

                                <div
                                    class="stat-label"
                                >

                                    Total Tasks

                                </div>


                                <h2
                                    class="stat-number"
                                >

                                    <?= $total_tasks ?>

                                </h2>

                            </div>


                            <div
                                class="stat-icon bg-primary"
                            >

                                <i
                                    class="fa-solid
                                           fa-list-check"
                                ></i>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- Completed -->

                <div
                    class="col-md-6 col-xl-3"
                >

                    <div
                        class="stat-card h-100 p-4"
                    >

                        <div
                            class="d-flex
                                   justify-content-between
                                   align-items-start"
                        >

                            <div>

                                <div
                                    class="stat-label"
                                >

                                    Completed

                                </div>


                                <h2
                                    class="stat-number"
                                >

                                    <?= $completed_tasks ?>

                                </h2>

                            </div>


                            <div
                                class="stat-icon bg-success"
                            >

                                <i
                                    class="fa-solid
                                           fa-check"
                                ></i>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- Pending -->

                <div
                    class="col-md-6 col-xl-3"
                >

                    <div
                        class="stat-card h-100 p-4"
                    >

                        <div
                            class="d-flex
                                   justify-content-between
                                   align-items-start"
                        >

                            <div>

                                <div
                                    class="stat-label"
                                >

                                    Pending

                                </div>


                                <h2
                                    class="stat-number"
                                >

                                    <?= $pending_tasks ?>

                                </h2>

                            </div>


                            <div
                                class="stat-icon bg-warning"
                            >

                                <i
                                    class="fa-solid
                                           fa-clock"
                                ></i>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- Notes -->

                <div
                    class="col-md-6 col-xl-3"
                >

                    <div
                        class="stat-card h-100 p-4"
                    >

                        <div
                            class="d-flex
                                   justify-content-between
                                   align-items-start"
                        >

                            <div>

                                <div
                                    class="stat-label"
                                >

                                    Notes

                                </div>


                                <h2
                                    class="stat-number"
                                >

                                    <?= $total_notes ?>

                                </h2>

                            </div>


                            <div
                                class="stat-icon bg-info"
                            >

                                <i
                                    class="fa-solid
                                           fa-note-sticky"
                                ></i>

                            </div>

                        </div>

                    </div>

                </div>


            </div>


            <!-- =================================================
                 PROGRESS + REMINDERS
            ================================================= -->

            <div
                class="row g-4 mb-4"
            >


                <!-- Completion -->

                <div
                    class="col-lg-8"
                >

                    <div
                        class="content-card p-4 h-100"
                    >

                        <div
                            class="d-flex
                                   justify-content-between
                                   align-items-center
                                   mb-3"
                        >

                            <div>

                                <div
                                    class="text-muted
                                           small
                                           mb-1"
                                >

                                    Performance

                                </div>


                                <h3
                                    class="section-title mb-0"
                                >

                                    Task Completion Rate

                                </h3>

                            </div>


                            <div
                                class="fw-bold
                                       fs-4
                                       text-primary"
                            >

                                <?= $completion_rate ?>%

                            </div>

                        </div>


                        <div
                            class="progress"
                        >

                           <div
    class="progress-bar bg-primary"
    role="progressbar"
    style="width: <?= $completion_rate ?>%;"
    aria-valuenow="<?= $completion_rate ?>"
    aria-valuemin="0"
    aria-valuemax="100"
></div>

                        </div>


                        <div
                            class="d-flex
                                   justify-content-between
                                   mt-3
                                   small
                                   text-muted"
                        >

                            <span>

                                <?= $completed_tasks ?>
                                completed

                            </span>


                            <span>

                                <?= $pending_tasks ?>
                                pending

                            </span>

                        </div>

                    </div>

                </div>


                <!-- Reminders -->

                <div
                    class="col-lg-4"
                >

                    <div
                        class="content-card p-4 h-100"
                    >

                        <div
                            class="d-flex
                                   justify-content-between
                                   align-items-center"
                        >

                            <div>

                                <div
                                    class="text-muted
                                           small"
                                >

                                    Reminders

                                </div>


                                <h3
                                    class="fw-bold mb-0"
                                >

                                    <?= $pending_reminders ?>

                                </h3>

                                <small
                                    class="text-muted"
                                >

                                    Pending reminders

                                </small>

                            </div>


                            <div
                                class="stat-icon bg-warning"
                            >

                                <i
                                    class="fa-solid
                                           fa-bell"
                                ></i>

                            </div>

                        </div>


                        <a
                            href="../reminders/index.php"
                            class="btn
                                   btn-outline-dark
                                   btn-sm
                                   mt-3"
                        >

                            Manage Reminders

                        </a>

                    </div>

                </div>

            </div>


            <!-- =================================================
                 QUICK ACTIONS
            ================================================= -->

            <div
                class="content-card p-4 mb-4"
            >

                <div class="mb-3">

                    <div
                        class="text-muted
                               small"
                    >

                        Shortcuts

                    </div>


                    <h3
                        class="section-title mb-0"
                    >

                        Quick Actions

                    </h3>

                </div>


                <div
                    class="row g-3"
                >


                    <div
                        class="col-md-4"
                    >

                        <a
                            href="../tasks/index.php"
                            class="btn
                                   btn-primary
                                   quick-action
                                   w-100"
                        >

                            <i
                                class="fa-solid
                                       fa-plus
                                       me-2"
                            ></i>

                            Create Task

                        </a>

                    </div>


                    <div
                        class="col-md-4"
                    >

                        <a
                            href="../notes/index.php"
                            class="btn
                                   btn-success
                                   quick-action
                                   w-100"
                        >

                            <i
                                class="fa-solid
                                       fa-note-sticky
                                       me-2"
                            ></i>

                            Create Note

                        </a>

                    </div>


                    <div
                        class="col-md-4"
                    >

                        <a
                            href="../reminders/index.php"
                            class="btn
                                   btn-warning
                                   quick-action
                                   w-100"
                        >

                            <i
                                class="fa-solid
                                       fa-bell
                                       me-2"
                            ></i>

                            Add Reminder

                        </a>

                    </div>


                </div>

            </div>


            <!-- =================================================
                 UPCOMING TASKS + REMINDERS
            ================================================= -->

            <div
                class="row g-4 mb-4"
            >


                <!-- Upcoming Tasks -->

                <div
                    class="col-lg-6"
                >

                    <div
                        class="content-card p-4 h-100"
                    >

                        <div
                            class="d-flex
                                   justify-content-between
                                   align-items-center
                                   mb-3"
                        >

                            <h3
                                class="section-title mb-0"
                            >

                                <i
                                    class="fa-solid
                                           fa-list-check
                                           text-primary
                                           me-2"
                                ></i>

                                Upcoming Tasks

                            </h3>


                            <a
                                href="../tasks/index.php"
                                class="small
                                       text-decoration-none"
                            >

                                View All

                            </a>

                        </div>


                        <?php if (
                            $upcoming_tasks->num_rows > 0
                        ): ?>


                            <?php while (
                                $task =
                                $upcoming_tasks->fetch_assoc()
                            ): ?>


                                <div
                                    class="task-item"
                                >

                                    <div
                                        class="d-flex
                                               justify-content-between
                                               gap-2"
                                    >

                                        <strong>

                                            <?= htmlspecialchars(
                                                $task["title"]
                                            ) ?>

                                        </strong>


                                        <?php

                                        if (
                                            $task["priority"]
                                            ===
                                            "High"
                                        ) {

                                            $badge =
                                                "danger";

                                        } elseif (
                                            $task["priority"]
                                            ===
                                            "Medium"
                                        ) {

                                            $badge =
                                                "warning";

                                        } else {

                                            $badge =
                                                "success";

                                        }

                                        ?>


                                        <span
                                            class="badge
                                                   bg-<?= $badge ?>"
                                        >

                                            <?= htmlspecialchars(
                                                $task["priority"]
                                            ) ?>

                                        </span>

                                    </div>


                                    <small
                                        class="text-muted"
                                    >

                                        <i
                                            class="fa-regular
                                                   fa-calendar
                                                   me-1"
                                        ></i>

                                        <?= date(
                                            "M d, Y",
                                            strtotime(
                                                $task["due_date"]
                                            )
                                        ) ?>

                                    </small>

                                </div>


                            <?php endwhile; ?>


                        <?php else: ?>


                            <div
                                class="text-center
                                       text-muted
                                       py-5"
                            >

                                <i
                                    class="fa-solid
                                           fa-circle-check
                                           fa-2x
                                           mb-2"
                                ></i>


                                <p
                                    class="mb-0"
                                >

                                    No upcoming tasks.

                                </p>

                            </div>


                        <?php endif; ?>


                    </div>

                </div>


                <!-- Upcoming Reminders -->

                <div
                    class="col-lg-6"
                >

                    <div
                        class="content-card p-4 h-100"
                    >

                        <div
                            class="d-flex
                                   justify-content-between
                                   align-items-center
                                   mb-3"
                        >

                            <h3
                                class="section-title mb-0"
                            >

                                <i
                                    class="fa-solid
                                           fa-bell
                                           text-warning
                                           me-2"
                                ></i>

                                Upcoming Reminders

                            </h3>


                            <a
                                href="../reminders/index.php"
                                class="small
                                       text-decoration-none"
                            >

                                View All

                            </a>

                        </div>


                        <?php if (
                            $upcoming_reminders->num_rows > 0
                        ): ?>


                            <?php while (
                                $reminder =
                                $upcoming_reminders->fetch_assoc()
                            ): ?>


                                <div
                                    class="reminder-item"
                                >

                                    <strong>

                                        <?= htmlspecialchars(
                                            $reminder["description"]
                                        ) ?>

                                    </strong>


                                    <div
                                        class="small
                                               text-muted
                                               mt-1"
                                    >

                                        <i
                                            class="fa-regular
                                                   fa-calendar
                                                   me-1"
                                        ></i>

                                        <?= date(
                                            "M d, Y",
                                            strtotime(
                                                $reminder[
                                                    "reminder_date"
                                                ]
                                            )
                                        ) ?>


                                        <span
                                            class="mx-1"
                                        >
                                            ·
                                        </span>


                                        <i
                                            class="fa-regular
                                                   fa-clock
                                                   me-1"
                                        ></i>

                                        <?= date(
                                            "h:i A",
                                            strtotime(
                                                $reminder[
                                                    "reminder_time"
                                                ]
                                            )
                                        ) ?>

                                    </div>

                                </div>


                            <?php endwhile; ?>


                        <?php else: ?>


                            <div
                                class="text-center
                                       text-muted
                                       py-5"
                            >

                                <i
                                    class="fa-solid
                                           fa-bell-slash
                                           fa-2x
                                           mb-2"
                                ></i>


                                <p
                                    class="mb-0"
                                >

                                    No upcoming reminders.

                                </p>

                            </div>


                        <?php endif; ?>


                    </div>

                </div>


            </div>


            <!-- =================================================
                 AI ASSISTANT
            ================================================= -->

            <div
                class="ai-card p-4 p-lg-5"
            >

                <div
                    class="row align-items-center g-4"
                >

                    <div
                        class="col-lg-8"
                    >

                        <div
                            class="d-flex
                                   align-items-start
                                   gap-3"
                        >

                            <div
                                class="ai-icon
                                       flex-shrink-0"
                            >

                                <i
                                    class="fa-solid
                                           fa-robot"
                                ></i>

                            </div>


                            <div>

                                <div
                                    class="small
                                           text-uppercase
                                           fw-semibold
                                           mb-1"
                                >

                                    Intelligent Assistant

                                </div>


                                <h3
                                    class="fw-bold
                                           mb-2"
                                >

                                    Work smarter with AI

                                </h3>


                                <p
                                    class="mb-0"
                                >

                                    Ask questions about your
                                    tasks, notes and reminders,
                                    and get useful productivity
                                    assistance in seconds.

                                </p>

                            </div>

                        </div>

                    </div>


                    <div
                        class="col-lg-4 text-lg-end"
                    >

                        <a
                            href="../ai/index.php"
                            class="btn btn-light
                                   btn-lg
                                   px-4"
                        >

                            <i
                                class="fa-solid
                                       fa-comments
                                       me-2"
                            ></i>

                            Open TaskPulse AI

                        </a>

                    </div>

                </div>

            </div>


        </main>


    </div>

</div>


<!-- Bootstrap JS -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
></script>


</body>

</html>
