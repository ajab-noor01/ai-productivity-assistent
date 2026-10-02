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


$user_id = $_SESSION["user_id"];

if (!isset($_GET["id"])) {
    header("Location: index.php");
    exit;
}

$task_id = (int) $_GET["id"];

$message = "";
$message_type = "";


/* =========================
   GET TASK
========================= */

$stmt = $conn->prepare(
    "SELECT *
     FROM tasks
     WHERE id = ? AND user_id = ?"
);

$stmt->bind_param("ii", $task_id, $user_id);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows !== 1) {
    header("Location: index.php");
    exit;
}

$task = $result->fetch_assoc();

$stmt->close();


/* =========================
   UPDATE TASK
========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST["title"]);
    $description = trim($_POST["description"]);
    $priority = $_POST["priority"];
    $status = $_POST["status"];
    $due_date = !empty($_POST["due_date"])
        ? $_POST["due_date"]
        : null;


    if (empty($title)) {

        $message = "Task title is required.";
        $message_type = "danger";

    } else {

        $stmt = $conn->prepare(
            "UPDATE tasks
             SET title = ?,
                 description = ?,
                 priority = ?,
                 status = ?,
                 due_date = ?
             WHERE id = ? AND user_id = ?"
        );

        $stmt->bind_param(
            "sssssii",
            $title,
            $description,
            $priority,
            $status,
            $due_date,
            $task_id,
            $user_id
        );


        if ($stmt->execute()) {

            header("Location: index.php");
            exit;

        } else {

            $message = "Failed to update task.";
            $message_type = "danger";
        }

        $stmt->close();
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

    <title>Edit Task -TaskPulse AI</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
    >

</head>


<body class="bg-light">


<nav class="navbar navbar-dark bg-primary shadow-sm">

    <div class="container">

        <a
            href="index.php"
            class="navbar-brand fw-bold"
        >
            🤖 TaskPulse AI
        </a>

    </div>

</nav>


<div class="container py-5">

    <div class="row justify-content-center">

        <div class="col-lg-7">

            <div class="card border-0 shadow-sm">

                <div class="card-body p-4">

                    <div class="d-flex justify-content-between align-items-center mb-4">

                        <h3 class="fw-bold mb-0">

                            <i class="fa-solid fa-pen-to-square text-primary"></i>

                            Edit Task

                        </h3>

                        <a
                            href="index.php"
                            class="btn btn-outline-secondary"
                        >
                            Back
                        </a>

                    </div>


                    <?php if (!empty($message)): ?>

                        <div class="alert alert-<?php echo $message_type; ?>">

                            <?php echo htmlspecialchars($message); ?>

                        </div>

                    <?php endif; ?>


                    <form method="POST">


                        <!-- TITLE -->

                        <div class="mb-3">

                            <label class="form-label fw-semibold">
                                Task Title
                            </label>

                            <input
                                type="text"
                                name="title"
                                class="form-control"
                                value="<?php echo htmlspecialchars($task["title"]); ?>"
                                required
                            >

                        </div>


                        <!-- DESCRIPTION -->

                        <div class="mb-3">

                            <label class="form-label fw-semibold">
                                Description
                            </label>

                            <textarea
                                name="description"
                                class="form-control"
                                rows="5"
                            ><?php echo htmlspecialchars($task["description"]); ?></textarea>

                        </div>


                        <div class="row">


                            <!-- PRIORITY -->

                            <div class="col-md-6 mb-3">

                                <label class="form-label fw-semibold">
                                    Priority
                                </label>

                                <select
                                    name="priority"
                                    class="form-select"
                                >

                                    <option
                                        value="Low"
                                        <?php echo $task["priority"] === "Low" ? "selected" : ""; ?>
                                    >
                                        🟢 Low
                                    </option>

                                    <option
                                        value="Medium"
                                        <?php echo $task["priority"] === "Medium" ? "selected" : ""; ?>
                                    >
                                        🟡 Medium
                                    </option>

                                    <option
                                        value="High"
                                        <?php echo $task["priority"] === "High" ? "selected" : ""; ?>
                                    >
                                        🔥 High
                                    </option>

                                </select>

                            </div>


                            <!-- STATUS -->

                            <div class="col-md-6 mb-3">

                                <label class="form-label fw-semibold">
                                    Status
                                </label>

                                <select
                                    name="status"
                                    class="form-select"
                                >

                                    <option
                                        value="Pending"
                                        <?php echo $task["status"] === "Pending" ? "selected" : ""; ?>
                                    >
                                        Pending
                                    </option>

                                    <option
                                        value="Completed"
                                        <?php echo $task["status"] === "Completed" ? "selected" : ""; ?>
                                    >
                                        Completed
                                    </option>

                                </select>

                            </div>

                        </div>


                        <!-- DUE DATE -->

                        <div class="mb-4">

                            <label class="form-label fw-semibold">
                                Due Date
                            </label>

                            <input
                                type="date"
                                name="due_date"
                                class="form-control"
                                value="<?php echo htmlspecialchars($task["due_date"] ?? ""); ?>"
                            >

                        </div>


                        <button
                            type="submit"
                            class="btn btn-primary w-100"
                        >

                            <i class="fa-solid fa-save"></i>

                            Update Task

                        </button>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

</body>

</html>