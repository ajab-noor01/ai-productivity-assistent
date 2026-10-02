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


// =========================
// SELECT MONTH
// =========================

$month = isset($_GET["month"])
    ? (int) $_GET["month"]
    : (int) date("m");

$year = isset($_GET["year"])
    ? (int) $_GET["year"]
    : (int) date("Y");


// =========================
// VALIDATE MONTH
// =========================

if ($month < 1) {

    $month = 12;
    $year--;

}

if ($month > 12) {

    $month = 1;
    $year++;

}


// =========================
// MONTH INFORMATION
// =========================

$first_day = mktime(
    0,
    0,
    0,
    $month,
    1,
    $year
);

$days_in_month = date(
    "t",
    $first_day
);

$start_day = date(
    "w",
    $first_day
);

$month_name = date(
    "F",
    $first_day
);


// =========================
// PREVIOUS / NEXT MONTH
// =========================

$previous_month = $month - 1;
$previous_year = $year;

if ($previous_month < 1) {

    $previous_month = 12;
    $previous_year--;

}


$next_month = $month + 1;
$next_year = $year;

if ($next_month > 12) {

    $next_month = 1;
    $next_year++;

}


// =========================
// GET TASKS
// =========================

$tasks = [];

$stmt = $conn->prepare(
    "SELECT id, title, due_date, status, priority
     FROM tasks
     WHERE user_id = ?
     AND due_date IS NOT NULL
     AND MONTH(due_date) = ?
     AND YEAR(due_date) = ?"
);

$stmt->bind_param(
    "iii",
    $user_id,
    $month,
    $year
);

$stmt->execute();

$result = $stmt->get_result();


while ($row = $result->fetch_assoc()) {

    $tasks[$row["due_date"]][] = $row;

}

$stmt->close();


// =========================
// GET REMINDERS
// =========================

$reminders = [];

$stmt = $conn->prepare(
    "SELECT id, title, reminder_date, reminder_time, status
     FROM reminders
     WHERE user_id = ?
     AND MONTH(reminder_date) = ?
     AND YEAR(reminder_date) = ?"
);

$stmt->bind_param(
    "iii",
    $user_id,
    $month,
    $year
);

$stmt->execute();

$result = $stmt->get_result();


while ($row = $result->fetch_assoc()) {

    $reminders[$row["reminder_date"]][] = $row;

}

$stmt->close();


// =========================
// TODAY
// =========================

$today = date("Y-m-d");

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
        Calendar - AI Productivity Assistant
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


        .calendar-card {
            border: none;
            border-radius: 15px;
            overflow: hidden;
        }


        .calendar-header {
            background: #0d6efd;
            color: white;
            padding: 20px;
        }


        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
        }


        .day-name {

            background: #f1f3f5;
            padding: 12px;
            text-align: center;
            font-weight: 600;

        }


        .calendar-day {

            min-height: 125px;
            border: 1px solid #eee;
            padding: 8px;
            background: white;

        }


        .calendar-day:hover {

            background: #f8f9fa;

        }


        .empty-day {

            background: #f8f9fa;

        }


        .today {

            background: #e7f1ff;
            border: 2px solid #0d6efd;

        }


        .day-number {

            font-weight: bold;
            margin-bottom: 8px;

        }


        .event-item {

            display: block;
            font-size: 12px;
            padding: 4px 6px;
            margin-bottom: 4px;
            border-radius: 5px;
            text-decoration: none;
            overflow: hidden;
            white-space: nowrap;
            text-overflow: ellipsis;

        }


        .task-event {

            background: #dbeafe;
            color: #0d47a1;

        }


        .reminder-event {

            background: #fff3cd;
            color: #664d03;

        }


        @media (max-width: 768px) {

            .calendar-day {

                min-height: 90px;
                padding: 5px;

            }


            .event-item {

                font-size: 10px;

            }


            .day-name {

                font-size: 12px;
                padding: 8px;

            }

        }

    </style>

</head>


<body>


<!-- =========================
     NAVBAR
========================= -->

<nav class="navbar navbar-dark bg-primary shadow-sm">

    <div class="container-fluid">


        <a
            href="../dashboard/index.php"
            class="navbar-brand fw-bold"
        >

            🤖 AI Productivity

        </a>


        <div class="d-flex align-items-center">


            <span class="text-white me-3">

                <i class="fa-solid fa-user"></i>

                <?php

                echo htmlspecialchars(
                    $_SESSION["user_name"]
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



<!-- =========================
     MAIN
========================= -->

<div class="container py-4">


    <!-- PAGE HEADER -->

    <div
        class="d-flex justify-content-between align-items-center mb-4"
    >

        <div>

            <h2 class="fw-bold mb-1">

                <i
                    class="fa-solid fa-calendar-days text-primary"
                ></i>

                Calendar

            </h2>


            <p class="text-muted mb-0">

                View your tasks and reminders by date.

            </p>

        </div>


        <a
            href="../dashboard/index.php"
            class="btn btn-outline-primary"
        >

            <i class="fa-solid fa-arrow-left"></i>

            Dashboard

        </a>

    </div>



    <!-- =========================
         CALENDAR CARD
    ========================= -->

    <div class="card shadow-sm calendar-card">


        <!-- CALENDAR HEADER -->

        <div class="calendar-header">


            <div
                class="d-flex justify-content-between align-items-center"
            >


                <!-- PREVIOUS -->

                <a
                    href="?month=<?php echo $previous_month; ?>&year=<?php echo $previous_year; ?>"
                    class="btn btn-light"
                >

                    <i
                        class="fa-solid fa-chevron-left"
                    ></i>

                </a>



                <!-- MONTH -->

                <h3 class="mb-0 fw-bold">

                    <?php

                    echo $month_name . " " . $year;

                    ?>

                </h3>



                <!-- NEXT -->

                <a
                    href="?month=<?php echo $next_month; ?>&year=<?php echo $next_year; ?>"
                    class="btn btn-light"
                >

                    <i
                        class="fa-solid fa-chevron-right"
                    ></i>

                </a>

            </div>

        </div>



        <!-- =========================
             DAY NAMES
        ========================= -->

        <div class="calendar-grid">


            <div class="day-name">Sun</div>

            <div class="day-name">Mon</div>

            <div class="day-name">Tue</div>

            <div class="day-name">Wed</div>

            <div class="day-name">Thu</div>

            <div class="day-name">Fri</div>

            <div class="day-name">Sat</div>


        </div>



        <!-- =========================
             CALENDAR DAYS
        ========================= -->

        <div class="calendar-grid">


            <?php

            // EMPTY DAYS BEFORE MONTH

            for (
                $i = 0;
                $i < $start_day;
                $i++
            ):

            ?>

                <div
                    class="calendar-day empty-day"
                ></div>


            <?php endfor; ?>



            <?php

            // MONTH DAYS

            for (
                $day = 1;
                $day <= $days_in_month;
                $day++
            ):


                $date = sprintf(
                    "%04d-%02d-%02d",
                    $year,
                    $month,
                    $day
                );


                $is_today =
                    ($date === $today)
                    ? "today"
                    : "";

            ?>


                <div
                    class="calendar-day <?php echo $is_today; ?>"
                >


                    <!-- DAY NUMBER -->

                    <div class="day-number">

                        <?php echo $day; ?>


                        <?php if ($date === $today): ?>

                            <span
                                class="badge bg-primary"
                                style="font-size: 9px;"
                            >

                                Today

                            </span>

                        <?php endif; ?>

                    </div>



                    <!-- =========================
                         TASKS
                    ========================= -->

                    <?php if (
                        isset($tasks[$date])
                    ): ?>


                        <?php foreach (
                            $tasks[$date]
                            as $task
                        ): ?>


                            <a
                                href="../tasks/index.php"
                                class="event-item task-event"
                                title="<?php echo htmlspecialchars($task["title"]); ?>"
                            >

                                <i
                                    class="fa-solid fa-list-check"
                                ></i>

                                <?php

                                echo htmlspecialchars(
                                    $task["title"]
                                );

                                ?>

                            </a>


                        <?php endforeach; ?>


                    <?php endif; ?>



                    <!-- =========================
                         REMINDERS
                    ========================= -->

                    <?php if (
                        isset($reminders[$date])
                    ): ?>


                        <?php foreach (
                            $reminders[$date]
                            as $reminder
                        ): ?>


                            <a
                                href="../reminders/index.php"
                                class="event-item reminder-event"
                                title="<?php echo htmlspecialchars($reminder["title"]); ?>"
                            >

                                <i
                                    class="fa-solid fa-bell"
                                ></i>

                                <?php

                                echo htmlspecialchars(
                                    $reminder["title"]
                                );

                                ?>


                                <small>

                                    <?php

                                    echo date(
                                        "h:i A",
                                        strtotime(
                                            $reminder["reminder_time"]
                                        )
                                    );

                                    ?>

                                </small>

                            </a>


                        <?php endforeach; ?>


                    <?php endif; ?>


                </div>


            <?php endfor; ?>


        </div>

    </div>



    <!-- =========================
         LEGEND
    ========================= -->

    <div class="mt-3">


        <span class="badge task-event me-2 p-2">

            <i class="fa-solid fa-list-check"></i>

            Tasks

        </span>


        <span class="badge reminder-event p-2">

            <i class="fa-solid fa-bell"></i>

            Reminders

        </span>


    </div>

</div>



<!-- Bootstrap JS -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
></script>


</body>

</html>