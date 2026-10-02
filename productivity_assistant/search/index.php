<?php

session_start();

if (!isset($_SESSION["user_id"])) {

    header("Location: ../auth/login.php");
    exit;

}

require_once "../config/trial_check.php";

require_once "../config/db.php";


// =====================================================
// CHECK LOGIN
// =====================================================



$user_id = $_SESSION["user_id"];

$user_name = $_SESSION["user_name"] ?? "User";


// =====================================================
// SEARCH VARIABLE
// =====================================================

$search = "";

$tasks = [];

$notes = [];

$reminders = [];


if (isset($_GET["q"])) {

    $search = trim($_GET["q"]);

}


// =====================================================
// PERFORM SEARCH
// =====================================================

if ($search !== "") {


    $search_value = "%" . $search . "%";


    // =================================================
    // TASK SEARCH
    // =================================================

    $stmt = $conn->prepare(

        "SELECT
            id,
            title,
            priority,
            status,
            due_date
         FROM tasks
         WHERE user_id = ?
         AND (
            title LIKE ?
            OR priority LIKE ?
            OR status LIKE ?
         )
         ORDER BY id DESC"

    );


    $stmt->bind_param(

        "isss",

        $user_id,
        $search_value,
        $search_value,
        $search_value

    );


    $stmt->execute();


    $result = $stmt->get_result();


    while ($row = $result->fetch_assoc()) {

        $tasks[] = $row;

    }


    $stmt->close();



    // =================================================
    // NOTES SEARCH
    // =================================================

    $stmt = $conn->prepare(

        "SELECT
            id,
            title,
            content,
            category,
            created_at
         FROM notes
         WHERE user_id = ?
         AND (
            title LIKE ?
            OR content LIKE ?
            OR category LIKE ?
         )
         ORDER BY id DESC"

    );


    $stmt->bind_param(

        "isss",

        $user_id,
        $search_value,
        $search_value,
        $search_value

    );


    $stmt->execute();


    $result = $stmt->get_result();


    while ($row = $result->fetch_assoc()) {

        $notes[] = $row;

    }


    $stmt->close();


// =================================================
// REMINDER SEARCH
// =================================================

$stmt = $conn->prepare(

    "SELECT
        id,
        title,
        description,
        reminder_date,
        reminder_time,
        status,
        created_at
     FROM reminders
     WHERE user_id = ?
     AND (
        title LIKE ?
        OR description LIKE ?
        OR status LIKE ?
     )
     ORDER BY id DESC"

);

$stmt->bind_param(

    "isss",

    $user_id,
    $search_value,
    $search_value,
    $search_value

);

$stmt->execute();

$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {

    $reminders[] = $row;

}

$stmt->close();


}


$total_results =
    count($tasks)
    +
    count($notes)
    +
    count($reminders);

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
        Search -TaskPulse AI
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

            min-height: 100vh;

        }


        /* =========================
           NAVBAR
        ========================= */

        .navbar-brand {

            font-size: 20px;

        }


        /* =========================
           SEARCH WRAPPER
        ========================= */

        .search-wrapper {

            max-width: 850px;

            margin: 70px auto 0;

        }


        /* =========================
           SEARCH CARD
        ========================= */

        .search-card {

            border: none;

            border-radius: 18px;

        }


        .search-input {

            height: 58px;

            border-radius: 12px 0 0 12px;

            font-size: 16px;

        }


        .search-button {

            border-radius: 0 12px 12px 0;

            padding-left: 28px;

            padding-right: 28px;

        }


        /* =========================
           RESULT CARD
        ========================= */

        .result-card {

            border: none;

            border-radius: 14px;

            transition: 0.2s;

        }


        .result-card:hover {

            transform: translateY(-3px);

        }


        .result-icon {

            width: 45px;

            height: 45px;

            border-radius: 12px;

            display: flex;

            align-items: center;

            justify-content: center;

            color: white;

            flex-shrink: 0;

        }


        /* =========================
           SEARCH TITLE
        ========================= */

        .search-title {

            font-weight: 700;

            font-size: 32px;

        }


        .search-subtitle {

            color: #6c757d;

        }


        /* =========================
           EMPTY STATE
        ========================= */

        .empty-icon {

            font-size: 55px;

            color: #adb5bd;

        }


        /* =========================
           MOBILE
        ========================= */

        @media (max-width: 576px) {

            .search-wrapper {

                margin-top: 40px;

                padding-left: 15px;

                padding-right: 15px;

            }


            .search-title {

                font-size: 26px;

            }


            .search-input {

                height: 52px;

            }

        }

    </style>

</head>


<body>


<!-- =====================================================
     NAVBAR
===================================================== -->

<nav class="navbar navbar-dark bg-primary shadow-sm">

    <div class="container">


        <!-- BRAND -->

        <a
            href="../dashboard/index.php"
            class="navbar-brand fw-bold"
        >

            🤖 TaskPulse AI

        </a>


        <!-- RIGHT SIDE -->

        <div class="d-flex align-items-center">


            <!-- USER -->

            <span
                class="text-white me-3 d-none d-md-inline"
            >

                <i class="fa-solid fa-user me-1"></i>

                <?php

                echo htmlspecialchars($user_name);

                ?>

            </span>


            <!-- DASHBOARD -->

            <a
                href="../dashboard/index.php"
                class="btn btn-light btn-sm"
            >

                <i
                    class="fa-solid fa-gauge me-1"
                ></i>

                Dashboard

            </a>

        </div>

    </div>

</nav>



<!-- =====================================================
     MAIN SEARCH AREA
===================================================== -->

<div class="container">

    <div class="search-wrapper">


        <!-- =================================================
             TITLE
        ================================================= -->

        <div class="text-center mb-4">

            <h1 class="search-title">

                <i
                    class="fa-solid fa-magnifying-glass text-primary"
                ></i>

                Search

            </h1>


            <p class="search-subtitle">

                Search your tasks, notes and reminders

            </p>

        </div>



        <!-- =================================================
             SEARCH BOX
        ================================================= -->

        <div
            class="card search-card shadow-sm mb-4"
        >

            <div class="card-body p-4">


                <form
                    method="GET"
                    action=""
                >

                    <div class="input-group">

                        <input
                            type="text"
                            name="q"
                            class="form-control search-input"
                            placeholder="Search here..."
                            value="<?php echo htmlspecialchars($search); ?>"
                            autofocus
                        >


                        <button
                            type="submit"
                            class="btn btn-primary search-button"
                        >

                            <i
                                class="fa-solid fa-magnifying-glass me-2"
                            ></i>

                            Search

                        </button>

                    </div>

                </form>


                <div class="text-muted small mt-3">

                    <i
                        class="fa-solid fa-lightbulb me-1"
                    ></i>

                    Try searching words like
                    <strong>project</strong>,
                    <strong>PHP</strong>,
                    <strong>meeting</strong>
                    or
                    <strong>High</strong>.

                </div>

            </div>

        </div>



        <?php if ($search !== ""): ?>


            <!-- =================================================
                 RESULT SUMMARY
            ================================================= -->

            <div
                class="d-flex justify-content-between align-items-center mb-3"
            >

                <h5 class="fw-bold mb-0">

                    Search Results

                </h5>


                <span class="badge bg-primary">

                    <?php

                    echo $total_results;

                    ?>

                    result(s)

                </span>

            </div>



            <!-- =================================================
                 TASK RESULTS
            ================================================= -->

            <?php if (count($tasks) > 0): ?>


                <h6 class="fw-bold text-primary mt-4 mb-3">

                    <i
                        class="fa-solid fa-list-check me-2"
                    ></i>

                    Tasks

                </h6>


                <div class="row g-3">

                    <?php foreach ($tasks as $task): ?>


                        <div class="col-md-6">

                            <div
                                class="card result-card shadow-sm h-100"
                            >

                                <div class="card-body">


                                    <div class="d-flex">


                                        <div
                                            class="result-icon bg-primary me-3"
                                        >

                                            <i
                                                class="fa-solid fa-list-check"
                                            ></i>

                                        </div>


                                        <div>

                                            <h6 class="fw-bold">

                                                <?php

                                                echo htmlspecialchars(
                                                    $task["title"]
                                                );

                                                ?>

                                            </h6>


                                            <div class="mt-2">


                                                <span
                                                    class="badge bg-secondary"
                                                >

                                                    <?php

                                                    echo htmlspecialchars(
                                                        $task["priority"]
                                                    );

                                                    ?>

                                                </span>


                                                <span
                                                    class="badge bg-info"
                                                >

                                                    <?php

                                                    echo htmlspecialchars(
                                                        $task["status"]
                                                    );

                                                    ?>

                                                </span>

                                            </div>


                                            <?php if (!empty($task["due_date"])): ?>

                                                <small
                                                    class="text-muted d-block mt-2"
                                                >

                                                    <i
                                                        class="fa-solid fa-calendar me-1"
                                                    ></i>

                                                    Due:

                                                    <?php

                                                    echo htmlspecialchars(
                                                        $task["due_date"]
                                                    );

                                                    ?>

                                                </small>

                                            <?php endif; ?>


                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>


                    <?php endforeach; ?>

                </div>


            <?php endif; ?>



            <!-- =================================================
                 NOTES
            ================================================= -->

            <?php if (count($notes) > 0): ?>


                <h6 class="fw-bold text-success mt-5 mb-3">

                    <i
                        class="fa-solid fa-note-sticky me-2"
                    ></i>

                    Notes

                </h6>


                <div class="row g-3">


                    <?php foreach ($notes as $note): ?>


                        <div class="col-md-6">

                            <div
                                class="card result-card shadow-sm h-100"
                            >

                                <div class="card-body">


                                    <div class="d-flex">


                                        <div
                                            class="result-icon bg-success me-3"
                                        >

                                            <i
                                                class="fa-solid fa-note-sticky"
                                            ></i>

                                        </div>


                                        <div>

                                            <h6 class="fw-bold">

                                                <?php

                                                echo htmlspecialchars(
                                                    $note["title"]
                                                );

                                                ?>

                                            </h6>


                                            <p
                                                class="text-muted small mb-2"
                                            >

                                                <?php

                                                $content =
                                                    $note["content"];

                                                echo htmlspecialchars(
                                                    strlen($content) > 150
                                                        ? substr($content, 0, 150) . "..."
                                                        : $content
                                                );

                                                ?>

                                            </p>


                                            <?php if (!empty($note["category"])): ?>

                                                <span
                                                    class="badge bg-success"
                                                >

                                                    <?php

                                                    echo htmlspecialchars(
                                                        $note["category"]
                                                    );

                                                    ?>

                                                </span>

                                            <?php endif; ?>


                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>


                    <?php endforeach; ?>


                </div>


            <?php endif; ?>



            <!-- =================================================
                 REMINDERS
            ================================================= -->

            <?php if (count($reminders) > 0): ?>


                <h6 class="fw-bold text-warning mt-5 mb-3">

                    <i
                        class="fa-solid fa-bell me-2"
                    ></i>

                    Reminders

                </h6>


                <div class="row g-3">


                    <?php foreach ($reminders as $reminder): ?>


                        <div class="col-md-6">

                            <div
                                class="card result-card shadow-sm h-100"
                            >

                                <div class="card-body">


                                    <div class="d-flex">


                                        <div
                                            class="result-icon bg-warning me-3"
                                        >

                                            <i
                                                class="fa-solid fa-bell"
                                            ></i>

                                        </div>


                                        <div>

                                            <h6 class="fw-bold">

                                                Reminder

                                            </h6>


                                            <p
                                                class="text-muted small mb-2"
                                            >

                                           <p class="text-muted small mb-2">

                                             Reminder

                                            </p>     

                                            </p>


                                            <span
                                                class="badge bg-secondary"
                                            >

                                                <?php

                                                echo htmlspecialchars(
                                                    $reminder["status"]
                                                );

                                                ?>

                                            </span>


                                            <small
                                                class="text-muted d-block mt-2"
                                            >

                                                <i
                                                    class="fa-solid fa-clock me-1"
                                                ></i>

                                                <?php

                                                echo htmlspecialchars(
                                                    $reminder["reminder_time"]
                                                );

                                                ?>

                                            </small>


                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>


                    <?php endforeach; ?>


                </div>


            <?php endif; ?>



            <!-- =================================================
                 NO RESULTS
            ================================================= -->

            <?php if ($total_results == 0): ?>


                <div
                    class="card border-0 shadow-sm"
                >

                    <div class="card-body text-center py-5">


                        <i
                            class="fa-solid fa-magnifying-glass empty-icon mb-3"
                        ></i>


                        <h5 class="fw-bold">

                            No results found

                        </h5>


                        <p class="text-muted mb-0">

                            Try another keyword.

                        </p>


                    </div>

                </div>


            <?php endif; ?>


        <?php else: ?>


            <!-- =================================================
                 INITIAL EMPTY STATE
            ================================================= -->

            <div
                class="card border-0 shadow-sm"
            >

                <div class="card-body text-center py-5">


                    <i
                        class="fa-solid fa-folder-open empty-icon mb-3"
                    ></i>


                    <h5 class="fw-bold">

                        Search your productivity data

                    </h5>


                    <p class="text-muted mb-0">

                        Find your tasks, notes and reminders
                        quickly from one place.

                    </p>


                </div>

            </div>


        <?php endif; ?>


    </div>

</div>


<!-- Bootstrap JS -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
></script>


</body>

</html>