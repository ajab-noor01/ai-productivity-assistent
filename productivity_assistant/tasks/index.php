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

require_once "../config/trial_check.php";

require_once "../config/db.php";


// =========================
// CHECK LOGIN
// =========================


$user_id = $_SESSION["user_id"];

$message = "";
$message_type = "";


// =========================
// ADD TASK
// =========================

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["add_task"])) {

    $title = trim($_POST["title"]);
    $description = trim($_POST["description"]);
    $priority = $_POST["priority"];
    $due_date = !empty($_POST["due_date"])
        ? $_POST["due_date"]
        : null;


    if (empty($title)) {

        $message = "Task title is required.";
        $message_type = "danger";

    } else {

        $stmt = $conn->prepare(
            "INSERT INTO tasks
            (user_id, title, description, priority, due_date)
            VALUES (?, ?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "issss",
            $user_id,
            $title,
            $description,
            $priority,
            $due_date
        );


        if ($stmt->execute()) {

            $message = "Task added successfully.";
            $message_type = "success";

        } else {

            $message = "Failed to add task.";
            $message_type = "danger";
        }


        $stmt->close();
    }
}


// =========================
// COMPLETE TASK
// =========================

if (isset($_GET["complete"])) {

    $task_id = (int) $_GET["complete"];


    $stmt = $conn->prepare(
        "UPDATE tasks
         SET status = 'Completed'
         WHERE id = ? AND user_id = ?"
    );

    $stmt->bind_param(
        "ii",
        $task_id,
        $user_id
    );

    $stmt->execute();

    $stmt->close();


    header("Location: index.php");
    exit;
}


// =========================
// DELETE TASK
// =========================

if (isset($_GET["delete"])) {

    $task_id = (int) $_GET["delete"];


    $stmt = $conn->prepare(
        "DELETE FROM tasks
         WHERE id = ? AND user_id = ?"
    );

    $stmt->bind_param(
        "ii",
        $task_id,
        $user_id
    );

    $stmt->execute();

    $stmt->close();


    header("Location: index.php");
    exit;
}


// =========================
// GET USER TASKS
// =========================

$stmt = $conn->prepare(
    "SELECT *
     FROM tasks
     WHERE user_id = ?
     ORDER BY
     CASE
        WHEN status = 'Pending' THEN 1
        ELSE 2
     END,
     due_date ASC,
     id DESC"
);

$stmt->bind_param(
    "i",
    $user_id
);

$stmt->execute();

$tasks = $stmt->get_result();

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
        Tasks - TaskPulse AI
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


    <!-- Custom CSS -->

    <link
        rel="stylesheet"
        href="../assets/css/style.css"
    >

</head>


<body class="bg-light">


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
                echo htmlspecialchars($_SESSION["user_name"]);
                ?>

            </span>


            <a
                href="../auth/logout.php"
                class="btn btn-light btn-sm"
            >

                <i class="fa-solid fa-right-from-bracket"></i>

                Logout

            </a>

        </div>

    </div>

</nav>



<!-- =========================
     MAIN CONTAINER
========================= -->

<div class="container py-4">


    <!-- =========================
         PAGE HEADER
    ========================= -->

    <div
        class="d-flex justify-content-between align-items-center mb-4"
    >

        <div>

            <h2 class="fw-bold mb-1">

                <i class="fa-solid fa-list-check text-primary"></i>

                My Tasks

            </h2>


            <p class="text-muted mb-0">

                Create and manage your daily tasks.

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
         MESSAGE
    ========================= -->

    <?php if (!empty($message)): ?>

        <div
            class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show"
            role="alert"
        >

            <?php
            echo htmlspecialchars($message);
            ?>


            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>

        </div>

    <?php endif; ?>



    <div class="row g-4">


        <!-- =========================
             ADD TASK FORM
        ========================= -->

        <div class="col-lg-4">

            <div class="card border-0 shadow-sm">


                <div class="card-body p-4">


                    <h4 class="fw-bold mb-4">

                        <i class="fa-solid fa-plus text-primary"></i>

                        Add New Task

                    </h4>


                    <form method="POST">


                        <!-- TASK TITLE -->

                        <div class="mb-3">

                            <label
                                class="form-label fw-semibold"
                            >

                                Task Title

                            </label>


                            <input
                                type="text"
                                name="title"
                                class="form-control"
                                placeholder="Enter task title"
                                required
                            >

                        </div>



                        <!-- DESCRIPTION -->

                        <div class="mb-3">

                            <label
                                class="form-label fw-semibold"
                            >

                                Description

                            </label>


                            <textarea
                                name="description"
                                class="form-control"
                                rows="4"
                                placeholder="Task details..."
                            ></textarea>

                        </div>



                        <!-- PRIORITY -->

                        <div class="mb-3">

                            <label
                                class="form-label fw-semibold"
                            >

                                Priority

                            </label>


                            <select
                                name="priority"
                                class="form-select"
                            >

                                <option value="Low">

                                    🟢 Low

                                </option>


                                <option
                                    value="Medium"
                                    selected
                                >

                                    🟡 Medium

                                </option>


                                <option value="High">

                                    🔥 High

                                </option>

                            </select>

                        </div>



                        <!-- DUE DATE -->

                        <div class="mb-3">

                            <label
                                class="form-label fw-semibold"
                            >

                                Due Date

                            </label>


                            <input
                                type="date"
                                name="due_date"
                                class="form-control"
                            >

                        </div>



                        <!-- ADD BUTTON -->

                        <button
                            type="submit"
                            name="add_task"
                            class="btn btn-primary w-100"
                        >

                            <i class="fa-solid fa-plus"></i>

                            Add Task

                        </button>

                    </form>

                </div>

            </div>

        </div>



        <!-- =========================
             TASK LIST
        ========================= -->

        <div class="col-lg-8">

            <div class="card border-0 shadow-sm">


                <div class="card-body p-4">


                    <div
                        class="d-flex justify-content-between align-items-center mb-4"
                    >

                        <h4 class="fw-bold mb-0">

                            Your Tasks

                        </h4>


                        <span class="badge bg-primary">

                            <?php
                            echo $tasks->num_rows;
                            ?>

                            Tasks

                        </span>

                    </div>



                    <?php if ($tasks->num_rows > 0): ?>


                        <?php while ($task = $tasks->fetch_assoc()): ?>


                            <!-- =========================
                                 SINGLE TASK
                            ========================= -->

                            <div
                                class="border rounded-3 p-3 mb-3 bg-white"
                            >


                                <div
                                    class="d-flex justify-content-between align-items-start"
                                >


                                    <!-- TASK INFORMATION -->

                                    <div class="flex-grow-1">


                                        <!-- TITLE -->

                                        <h5
                                            class="fw-bold mb-2
                                            <?php

                                            if (
                                                $task["status"]
                                                ===
                                                "Completed"
                                            ) {

                                                echo "text-decoration-line-through text-muted";

                                            }

                                            ?>"
                                        >

                                            <?php

                                            echo htmlspecialchars(
                                                $task["title"]
                                            );

                                            ?>

                                        </h5>



                                        <!-- DESCRIPTION -->

                                        <?php if (
                                            !empty(
                                                $task["description"]
                                            )
                                        ): ?>

                                            <p
                                                class="text-muted mb-2"
                                            >

                                                <?php

                                                echo nl2br(
                                                    htmlspecialchars(
                                                        $task["description"]
                                                    )
                                                );

                                                ?>

                                            </p>

                                        <?php endif; ?>



                                        <!-- PRIORITY BADGE -->

                                        <?php

                                        if (
                                            $task["priority"]
                                            ===
                                            "High"
                                        ) {

                                            $priority_badge =
                                                "danger";

                                        } elseif (
                                            $task["priority"]
                                            ===
                                            "Medium"
                                        ) {

                                            $priority_badge =
                                                "warning";

                                        } else {

                                            $priority_badge =
                                                "success";
                                        }

                                        ?>


                                        <span
                                            class="badge bg-<?php echo $priority_badge; ?>"
                                        >

                                            <?php

                                            echo htmlspecialchars(
                                                $task["priority"]
                                            );

                                            ?>

                                        </span>



                                        <!-- STATUS BADGE -->

                                        <?php if (
                                            $task["status"]
                                            ===
                                            "Completed"
                                        ): ?>

                                            <span
                                                class="badge bg-success"
                                            >

                                                <i
                                                    class="fa-solid fa-check"
                                                ></i>

                                                Completed

                                            </span>

                                        <?php else: ?>

                                            <span
                                                class="badge bg-secondary"
                                            >

                                                Pending

                                            </span>

                                        <?php endif; ?>



                                        <!-- DUE DATE -->

                                        <?php if (
                                            !empty(
                                                $task["due_date"]
                                            )
                                        ): ?>

                                            <small
                                                class="text-muted ms-2"
                                            >

                                                <i
                                                    class="fa-regular fa-calendar"
                                                ></i>

                                                <?php

                                                echo htmlspecialchars(
                                                    $task["due_date"]
                                                );

                                                ?>

                                            </small>

                                        <?php endif; ?>


                                    </div>



                                    <!-- =========================
                                         ACTION BUTTONS
                                    ========================= -->

                                    <div
                                        class="ms-3 d-flex gap-1"
                                    >


                                        <!-- COMPLETE -->

                                        <?php if (
                                            $task["status"]
                                            ===
                                            "Pending"
                                        ): ?>

                                            <a
                                                href="?complete=<?php echo $task["id"]; ?>"
                                                class="btn btn-sm btn-success"
                                                title="Complete Task"
                                            >

                                                <i
                                                    class="fa-solid fa-check"
                                                ></i>

                                            </a>

                                        <?php endif; ?>



                                        <!-- EDIT -->

                                        <a
                                            href="edit.php?id=<?php echo $task["id"]; ?>"
                                            class="btn btn-sm btn-primary"
                                            title="Edit Task"
                                        >

                                            <i
                                                class="fa-solid fa-pen"
                                            ></i>

                                        </a>



                                        <!-- DELETE -->

                                        <a
                                            href="?delete=<?php echo $task["id"]; ?>"
                                            class="btn btn-sm btn-danger"
                                            title="Delete Task"
                                            onclick="return confirm('Are you sure you want to delete this task?');"
                                        >

                                            <i
                                                class="fa-solid fa-trash"
                                            ></i>

                                        </a>


                                    </div>

                                </div>

                            </div>


                        <?php endwhile; ?>


                    <?php else: ?>


                        <!-- =========================
                             NO TASKS
                        ========================= -->

                        <div
                            class="text-center py-5"
                        >

                            <i
                                class="fa-solid fa-list-check fa-3x text-muted mb-3"
                            ></i>


                            <h5 class="fw-bold">

                                No Tasks Yet

                            </h5>


                            <p class="text-muted">

                                Create your first task using
                                the form.

                            </p>

                        </div>


                    <?php endif; ?>


                </div>

            </div>

        </div>

    </div>

</div>



<!-- Bootstrap JS -->

<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"
></script>


</body>

</html>