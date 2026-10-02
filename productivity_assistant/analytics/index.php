<?php

session_start();

if (!isset($_SESSION["user_id"])) {

    header("Location: ../auth/login.php");
    exit;

}

require_once "../config/trial_check.php";

require_once "../config/db.php";


// =========================
// CHECK LOGIN
// =========================


$user_id = $_SESSION["user_id"];

$user_name = $_SESSION["user_name"];


// =====================================================
// TOTAL TASKS
// =====================================================

$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM tasks
     WHERE user_id = ?"
);

$stmt->bind_param("i", $user_id);

$stmt->execute();

$result = $stmt->get_result();

$total_tasks = $result->fetch_assoc()["total"];

$stmt->close();


// =====================================================
// COMPLETED TASKS
// =====================================================

$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM tasks
     WHERE user_id = ?
     AND status = 'Completed'"
);

$stmt->bind_param("i", $user_id);

$stmt->execute();

$result = $stmt->get_result();

$completed_tasks =
    $result->fetch_assoc()["total"];

$stmt->close();


// =====================================================
// PENDING TASKS
// =====================================================

$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM tasks
     WHERE user_id = ?
     AND status = 'Pending'"
);

$stmt->bind_param("i", $user_id);

$stmt->execute();

$result = $stmt->get_result();

$pending_tasks =
    $result->fetch_assoc()["total"];

$stmt->close();


// =====================================================
// OVERDUE TASKS
// =====================================================

$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM tasks
     WHERE user_id = ?
     AND status = 'Pending'
     AND due_date IS NOT NULL
     AND due_date < CURDATE()"
);

$stmt->bind_param("i", $user_id);

$stmt->execute();

$result = $stmt->get_result();

$overdue_tasks =
    $result->fetch_assoc()["total"];

$stmt->close();


// =====================================================
// COMPLETION RATE
// =====================================================

if ($total_tasks > 0) {

    $completion_rate =
        round(
            ($completed_tasks / $total_tasks) * 100
        );

} else {

    $completion_rate = 0;

}


// =====================================================
// TOTAL NOTES
// =====================================================

$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM notes
     WHERE user_id = ?"
);

$stmt->bind_param("i", $user_id);

$stmt->execute();

$result = $stmt->get_result();

$total_notes =
    $result->fetch_assoc()["total"];

$stmt->close();


// =====================================================
// TOTAL REMINDERS
// =====================================================

$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM reminders
     WHERE user_id = ?"
);

$stmt->bind_param("i", $user_id);

$stmt->execute();

$result = $stmt->get_result();

$total_reminders =
    $result->fetch_assoc()["total"];

$stmt->close();


// =====================================================
// COMPLETED REMINDERS
// =====================================================

$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM reminders
     WHERE user_id = ?
     AND status = 'Completed'"
);

$stmt->bind_param("i", $user_id);

$stmt->execute();

$result = $stmt->get_result();

$completed_reminders =
    $result->fetch_assoc()["total"];

$stmt->close();


// =====================================================
// PENDING REMINDERS
// =====================================================

$stmt = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM reminders
     WHERE user_id = ?
     AND status = 'Pending'"
);

$stmt->bind_param("i", $user_id);

$stmt->execute();

$result = $stmt->get_result();

$pending_reminders =
    $result->fetch_assoc()["total"];

$stmt->close();


// =====================================================
// LAST 7 DAYS TASKS
// =====================================================

$weekly_labels = [];

$weekly_completed = [];

for ($i = 6; $i >= 0; $i--) {

    $date = date(
        "Y-m-d",
        strtotime("-$i days")
    );

    $weekly_labels[] =
        date("D", strtotime($date));


        $stmt = $conn->prepare(
    "SELECT COUNT(*) AS total
     FROM tasks
     WHERE user_id = ?
     AND status = 'Completed'
     AND DATE(created_at) = ?"
);

    $stmt->bind_param(
        "is",
        $user_id,
        $date
    );

    $stmt->execute();

    $result = $stmt->get_result();

    $count =
        $result->fetch_assoc()["total"];

    $weekly_completed[] =
        (int) $count;

    $stmt->close();

}


// =====================================================
// PRIORITY STATISTICS
// =====================================================

$high_priority = 0;

$medium_priority = 0;

$low_priority = 0;


$stmt = $conn->prepare(
    "SELECT priority, COUNT(*) AS total
     FROM tasks
     WHERE user_id = ?
     GROUP BY priority"
);

$stmt->bind_param(
    "i",
    $user_id
);

$stmt->execute();

$result = $stmt->get_result();


while ($row = $result->fetch_assoc()) {

    if ($row["priority"] === "High") {

        $high_priority =
            (int) $row["total"];

    }


    if ($row["priority"] === "Medium") {

        $medium_priority =
            (int) $row["total"];

    }


    if ($row["priority"] === "Low") {

        $low_priority =
            (int) $row["total"];

    }

}

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
        Analytics - AI Productivity Assistant
    </title>


    <!-- Bootstrap -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- Font Awesome -->

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
    >


    <style>

        body {

            background: #f5f7fb;

        }


        .stat-card {

            border: none;

            border-radius: 15px;

            transition: 0.2s;

        }


        .stat-card:hover {

            transform: translateY(-4px);

        }


        .stat-icon {

            width: 50px;

            height: 50px;

            border-radius: 12px;

            display: flex;

            align-items: center;

            justify-content: center;

            color: white;

            font-size: 20px;

        }


        .chart-card {

            border: none;

            border-radius: 15px;

        }


        .chart-box {

            position: relative;

            height: 320px;

        }


        @media (max-width: 768px) {

            .chart-box {

                height: 260px;

            }

        }

    </style>

</head>


<body>


<!-- =====================================================
     NAVBAR
===================================================== -->

<nav
    class="navbar navbar-dark bg-primary shadow-sm"
>

    <div class="container-fluid">


        <a
            href="../dashboard/index.php"
            class="navbar-brand fw-bold"
        >

            🤖 AI Productivity

        </a>


        <div
            class="d-flex align-items-center"
        >

            <span
                class="text-white me-3"
            >

                <i class="fa-solid fa-user"></i>

                <?php

                echo htmlspecialchars(
                    $user_name
                );

                ?>

            </span>


            <a
                href="../auth/logout.php"
                class="btn btn-light btn-sm"
            >

                <i
                    class="fa-solid fa-right-from-bracket"
                ></i>

                Logout

            </a>

        </div>

    </div>

</nav>



<!-- =====================================================
     MAIN
===================================================== -->

<div class="container-fluid">

    <div class="row">


        <!-- =================================================
             SIDEBAR
        ================================================= -->

        <div
            class="col-lg-2 bg-white shadow-sm min-vh-100 p-3"
        >

            <h6 class="text-muted mb-3">

                MENU

            </h6>


            <div
                class="list-group list-group-flush"
            >


                <a
                    href="../dashboard/index.php"
                    class="list-group-item list-group-item-action"
                >

                    <i
                        class="fa-solid fa-gauge me-2"
                    ></i>

                    Dashboard

                </a>


                <a
                    href="../tasks/index.php"
                    class="list-group-item list-group-item-action"
                >

                    <i
                        class="fa-solid fa-list-check me-2"
                    ></i>

                    Tasks

                </a>


                <a
                    href="../notes/index.php"
                    class="list-group-item list-group-item-action"
                >

                    <i
                        class="fa-solid fa-note-sticky me-2"
                    ></i>

                    Notes

                </a>


                <a
                    href="../reminders/index.php"
                    class="list-group-item list-group-item-action"
                >

                    <i
                        class="fa-solid fa-bell me-2"
                    ></i>

                    Reminders

                </a>


                <a
                    href="../calendar/index.php"
                    class="list-group-item list-group-item-action"
                >

                    <i
                        class="fa-solid fa-calendar me-2"
                    ></i>

                    Calendar

                </a>


                <a
                    href="../ai/index.php"
                    class="list-group-item list-group-item-action"
                >

                    <i
                        class="fa-solid fa-robot me-2"
                    ></i>

                    AI Assistant

                </a>


                <a
                    href="index.php"
                    class="list-group-item list-group-item-action active"
                >

                    <i
                        class="fa-solid fa-chart-line me-2"
                    ></i>

                    Analytics

                </a>


            </div>

        </div>



        <!-- =================================================
             ANALYTICS AREA
        ================================================= -->

        <div class="col-lg-10 p-4">


            <!-- PAGE TITLE -->

            <div class="mb-4">

                <h2 class="fw-bold">

                    <i
                        class="fa-solid fa-chart-line text-primary"
                    ></i>

                    Productivity Analytics

                </h2>


                <p class="text-muted">

                    Track your productivity and understand
                    your progress.

                </p>

            </div>



            <!-- =================================================
                 STAT CARDS
            ================================================= -->

            <div class="row g-4 mb-4">


                <!-- TOTAL TASKS -->

                <div class="col-md-6 col-xl-3">

                    <div
                        class="card stat-card shadow-sm"
                    >

                        <div class="card-body">


                            <div
                                class="d-flex justify-content-between"
                            >

                                <div>

                                    <p
                                        class="text-muted mb-1"
                                    >

                                        Total Tasks

                                    </p>


                                    <h2 class="fw-bold">

                                        <?php

                                        echo $total_tasks;

                                        ?>

                                    </h2>

                                </div>


                                <div
                                    class="stat-icon bg-primary"
                                >

                                    <i
                                        class="fa-solid fa-list-check"
                                    ></i>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>



                <!-- COMPLETED -->

                <div class="col-md-6 col-xl-3">

                    <div
                        class="card stat-card shadow-sm"
                    >

                        <div class="card-body">


                            <div
                                class="d-flex justify-content-between"
                            >

                                <div>

                                    <p
                                        class="text-muted mb-1"
                                    >

                                        Completed

                                    </p>


                                    <h2 class="fw-bold">

                                        <?php

                                        echo $completed_tasks;

                                        ?>

                                    </h2>

                                </div>


                                <div
                                    class="stat-icon bg-success"
                                >

                                    <i
                                        class="fa-solid fa-check"
                                    ></i>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>



                <!-- PENDING -->

                <div class="col-md-6 col-xl-3">

                    <div
                        class="card stat-card shadow-sm"
                    >

                        <div class="card-body">


                            <div
                                class="d-flex justify-content-between"
                            >

                                <div>

                                    <p
                                        class="text-muted mb-1"
                                    >

                                        Pending

                                    </p>


                                    <h2 class="fw-bold">

                                        <?php

                                        echo $pending_tasks;

                                        ?>

                                    </h2>

                                </div>


                                <div
                                    class="stat-icon bg-warning"
                                >

                                    <i
                                        class="fa-solid fa-clock"
                                    ></i>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>



                <!-- OVERDUE -->

                <div class="col-md-6 col-xl-3">

                    <div
                        class="card stat-card shadow-sm"
                    >

                        <div class="card-body">


                            <div
                                class="d-flex justify-content-between"
                            >

                                <div>

                                    <p
                                        class="text-muted mb-1"
                                    >

                                        Overdue

                                    </p>


                                    <h2 class="fw-bold">

                                        <?php

                                        echo $overdue_tasks;

                                        ?>

                                    </h2>

                                </div>


                                <div
                                    class="stat-icon bg-danger"
                                >

                                    <i
                                        class="fa-solid fa-triangle-exclamation"
                                    ></i>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>



            <!-- =================================================
                 SECOND STATS
            ================================================= -->

            <div class="row g-4 mb-4">


                <div class="col-md-4">

                    <div
                        class="card stat-card shadow-sm"
                    >

                        <div class="card-body">

                            <p
                                class="text-muted mb-1"
                            >

                                Notes

                            </p>


                            <h3 class="fw-bold">

                                <?php

                                echo $total_notes;

                                ?>

                            </h3>

                        </div>

                    </div>

                </div>


                <div class="col-md-4">

                    <div
                        class="card stat-card shadow-sm"
                    >

                        <div class="card-body">

                            <p
                                class="text-muted mb-1"
                            >

                                Total Reminders

                            </p>


                            <h3 class="fw-bold">

                                <?php

                                echo $total_reminders;

                                ?>

                            </h3>

                        </div>

                    </div>

                </div>


                <div class="col-md-4">

                    <div
                        class="card stat-card shadow-sm"
                    >

                        <div class="card-body">

                            <p
                                class="text-muted mb-1"
                            >

                                Completion Rate

                            </p>


                            <h3 class="fw-bold">

                                <?php

                                echo $completion_rate;

                                ?>%

                            </h3>


                            <div
                                class="progress mt-2"
                                style="height: 8px;"
                            >

                                <div
                                    class="progress-bar"
                                    style="width: <?php echo $completion_rate; ?>%;"
                                ></div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>



            <!-- =================================================
                 CHARTS
            ================================================= -->

            <div class="row g-4">


                <!-- WEEKLY PRODUCTIVITY -->

                <div class="col-lg-8">

                    <div
                        class="card chart-card shadow-sm"
                    >

                        <div class="card-body p-4">


                            <h4 class="fw-bold mb-3">

                                <i
                                    class="fa-solid fa-chart-column text-primary"
                                ></i>

                                Weekly Productivity

                            </h4>


                            <div class="chart-box">

                                <canvas
                                    id="weeklyChart"
                                ></canvas>

                            </div>

                        </div>

                    </div>

                </div>



                <!-- TASK STATUS -->

                <div class="col-lg-4">

                    <div
                        class="card chart-card shadow-sm"
                    >

                        <div class="card-body p-4">


                            <h4 class="fw-bold mb-3">

                                Task Status

                            </h4>


                            <div class="chart-box">

                                <canvas
                                    id="statusChart"
                                ></canvas>

                            </div>

                        </div>

                    </div>

                </div>



                <!-- PRIORITY -->

                <div class="col-lg-6">

                    <div
                        class="card chart-card shadow-sm"
                    >

                        <div class="card-body p-4">


                            <h4 class="fw-bold mb-3">

                                Task Priority

                            </h4>


                            <div class="chart-box">

                                <canvas
                                    id="priorityChart"
                                ></canvas>

                            </div>

                        </div>

                    </div>

                </div>



                <!-- REMINDERS -->

                <div class="col-lg-6">

                    <div
                        class="card chart-card shadow-sm"
                    >

                        <div class="card-body p-4">


                            <h4 class="fw-bold mb-3">

                                Reminder Status

                            </h4>


                            <div class="chart-box">

                                <canvas
                                    id="reminderChart"
                                ></canvas>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


        </div>

    </div>

</div>



<!-- =====================================================
     CHART.JS
===================================================== -->

<script
    src="https://cdn.jsdelivr.net/npm/chart.js"
></script>


<script>

    // ==========================================
    // WEEKLY PRODUCTIVITY
    // ==========================================

    const weeklyLabels = <?php

        echo json_encode(
            $weekly_labels
        );

    ?>;


    const weeklyData = <?php

        echo json_encode(
            $weekly_completed
        );

    ?>;


    new Chart(

        document.getElementById(
            "weeklyChart"
        ),

        {

            type: "bar",

            data: {

                labels: weeklyLabels,

                datasets: [

                    {

                        label:
                            "Completed Tasks",

                        data:
                            weeklyData,

                        borderWidth: 1

                    }

                ]

            },

            options: {

                responsive: true,

                maintainAspectRatio: false,

                scales: {

                    y: {

                        beginAtZero: true,

                        ticks: {

                            precision: 0

                        }

                    }

                }

            }

        }

    );



    // ==========================================
    // TASK STATUS
    // ==========================================

    new Chart(

        document.getElementById(
            "statusChart"
        ),

        {

            type: "doughnut",

            data: {

                labels: [

                    "Completed",

                    "Pending"

                ],

                datasets: [

                    {

                        data: [

                            <?php
                            echo $completed_tasks;
                            ?>,

                            <?php
                            echo $pending_tasks;
                            ?>

                        ],

                        borderWidth: 1

                    }

                ]

            },

            options: {

                responsive: true,

                maintainAspectRatio: false

            }

        }

    );



    // ==========================================
    // PRIORITY CHART
    // ==========================================

    new Chart(

        document.getElementById(
            "priorityChart"
        ),

        {

            type: "doughnut",

            data: {

                labels: [

                    "High",

                    "Medium",

                    "Low"

                ],

                datasets: [

                    {

                        data: [

                            <?php
                            echo $high_priority;
                            ?>,

                            <?php
                            echo $medium_priority;
                            ?>,

                            <?php
                            echo $low_priority;
                            ?>

                        ],

                        borderWidth: 1

                    }

                ]

            },

            options: {

                responsive: true,

                maintainAspectRatio: false

            }

        }

    );



    // ==========================================
    // REMINDER CHART
    // ==========================================

    new Chart(

        document.getElementById(
            "reminderChart"
        ),

        {

            type: "doughnut",

            data: {

                labels: [

                    "Completed",

                    "Pending"

                ],

                datasets: [

                    {

                        data: [

                            <?php
                            echo $completed_reminders;
                            ?>,

                            <?php
                            echo $pending_reminders;
                            ?>

                        ],

                        borderWidth: 1

                    }

                ]

            },

            options: {

                responsive: true,

                maintainAspectRatio: false

            }

        }

    );

</script>


<!-- Bootstrap JS -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
></script>


</body>

</html>