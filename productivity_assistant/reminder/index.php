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
// ADD REMINDER
// =========================

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["add_reminder"])
) {

    $title = trim($_POST["title"]);
    $description = trim($_POST["description"]);
    $reminder_date = $_POST["reminder_date"];
    $reminder_time = $_POST["reminder_time"];


    if (
        empty($title)
        ||
        empty($reminder_date)
        ||
        empty($reminder_time)
    ) {

        $message = "Title, date and time are required.";
        $message_type = "danger";

    } else {

        $stmt = $conn->prepare(
            "INSERT INTO reminders
            (user_id, title, description, reminder_date, reminder_time)
            VALUES (?, ?, ?, ?, ?)"
        );


        $stmt->bind_param(
            "issss",
            $user_id,
            $title,
            $description,
            $reminder_date,
            $reminder_time
        );


        if ($stmt->execute()) {

            $message = "Reminder created successfully.";
            $message_type = "success";

        } else {

            $message = "Failed to create reminder.";
            $message_type = "danger";
        }


        $stmt->close();
    }
}


// =========================
// COMPLETE REMINDER
// =========================

if (isset($_GET["complete"])) {

    $reminder_id = (int) $_GET["complete"];


    $stmt = $conn->prepare(
        "UPDATE reminders
         SET status = 'Completed'
         WHERE id = ? AND user_id = ?"
    );


    $stmt->bind_param(
        "ii",
        $reminder_id,
        $user_id
    );


    $stmt->execute();

    $stmt->close();


    header("Location: index.php");
    exit;
}


// =========================
// DELETE REMINDER
// =========================

if (isset($_GET["delete"])) {

    $reminder_id = (int) $_GET["delete"];


    $stmt = $conn->prepare(
        "DELETE FROM reminders
         WHERE id = ? AND user_id = ?"
    );


    $stmt->bind_param(
        "ii",
        $reminder_id,
        $user_id
    );


    $stmt->execute();

    $stmt->close();


    header("Location: index.php");
    exit;
}


// =========================
// GET REMINDERS
// =========================

$stmt = $conn->prepare(
    "SELECT *
     FROM reminders
     WHERE user_id = ?
     ORDER BY
     CASE
        WHEN status = 'Pending' THEN 1
        ELSE 2
     END,
     reminder_date ASC,
     reminder_time ASC,
     id DESC"
);


$stmt->bind_param(
    "i",
    $user_id
);


$stmt->execute();

$reminders = $stmt->get_result();

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
        Reminders - TaskPulse AI
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

            🤖 TaskPulse AI

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


    <!-- HEADER -->

    <div
        class="d-flex justify-content-between align-items-center mb-4"
    >

        <div>

            <h2 class="fw-bold mb-1">

                <i
                    class="fa-solid fa-bell text-primary"
                ></i>

                My Reminders

            </h2>


            <p class="text-muted mb-0">

                Never forget your important tasks.

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



    <!-- MESSAGE -->

    <?php if (!empty($message)): ?>

        <div
            class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show"
        >

            <?php

            echo htmlspecialchars(
                $message
            );

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
             ADD REMINDER
        ========================= -->

        <div class="col-lg-4">

            <div class="card border-0 shadow-sm">

                <div class="card-body p-4">


                    <h4 class="fw-bold mb-4">

                        <i
                            class="fa-solid fa-plus text-primary"
                        ></i>

                        Add Reminder

                    </h4>


                    <form method="POST">


                        <!-- TITLE -->

                        <div class="mb-3">

                            <label
                                class="form-label fw-semibold"
                            >

                                Reminder Title

                            </label>


                            <input
                                type="text"
                                name="title"
                                class="form-control"
                                placeholder="e.g. Project Review"
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
                                placeholder="Reminder details..."
                            ></textarea>

                        </div>



                        <!-- DATE -->

                        <div class="mb-3">

                            <label
                                class="form-label fw-semibold"
                            >

                                Reminder Date

                            </label>


                            <input
                                type="date"
                                name="reminder_date"
                                class="form-control"
                                required
                            >

                        </div>



                        <!-- TIME -->

                        <div class="mb-3">

                            <label
                                class="form-label fw-semibold"
                            >

                                Reminder Time

                            </label>


                            <input
                                type="time"
                                name="reminder_time"
                                class="form-control"
                                required
                            >

                        </div>



                        <!-- BUTTON -->

                        <button
                            type="submit"
                            name="add_reminder"
                            class="btn btn-primary w-100"
                        >

                            <i class="fa-solid fa-bell"></i>

                            Create Reminder

                        </button>

                    </form>

                </div>

            </div>

        </div>



        <!-- =========================
             REMINDER LIST
        ========================= -->

        <div class="col-lg-8">

            <div class="card border-0 shadow-sm">

                <div class="card-body p-4">


                    <div
                        class="d-flex justify-content-between align-items-center mb-4"
                    >

                        <h4 class="fw-bold mb-0">

                            Your Reminders

                        </h4>


                        <span class="badge bg-primary">

                            <?php

                            echo $reminders->num_rows;

                            ?>

                            Reminders

                        </span>

                    </div>



                    <?php if ($reminders->num_rows > 0): ?>


                        <?php while (
                            $reminder =
                            $reminders->fetch_assoc()
                        ): ?>


                            <div
                                class="border rounded-3 p-3 mb-3 bg-white"
                            >


                                <div
                                    class="d-flex justify-content-between align-items-start"
                                >


                                    <!-- INFORMATION -->

                                    <div class="flex-grow-1">


                                        <!-- TITLE -->

                                        <h5
                                            class="fw-bold mb-2
                                            <?php

                                            if (
                                                $reminder["status"]
                                                ===
                                                "Completed"
                                            ) {

                                                echo "text-decoration-line-through text-muted";

                                            }

                                            ?>"
                                        >

                                            <?php

                                            echo htmlspecialchars(
                                                $reminder["title"]
                                            );

                                            ?>

                                        </h5>



                                        <!-- DESCRIPTION -->

                                        <?php if (
                                            !empty(
                                                $reminder["description"]
                                            )
                                        ): ?>

                                            <p
                                                class="text-muted mb-2"
                                            >

                                                <?php

                                                echo nl2br(
                                                    htmlspecialchars(
                                                        $reminder["description"]
                                                    )
                                                );

                                                ?>

                                            </p>

                                        <?php endif; ?>



                                        <!-- DATE -->

                                        <span
                                            class="badge bg-info text-dark"
                                        >

                                            <i
                                                class="fa-regular fa-calendar"
                                            ></i>

                                            <?php

                                            echo htmlspecialchars(
                                                $reminder["reminder_date"]
                                            );

                                            ?>

                                        </span>



                                        <!-- TIME -->

                                        <span
                                            class="badge bg-warning text-dark"
                                        >

                                            <i
                                                class="fa-regular fa-clock"
                                            ></i>

                                            <?php

                                            echo date(
                                                "h:i A",
                                                strtotime(
                                                    $reminder["reminder_time"]
                                                )
                                            );

                                            ?>

                                        </span>



                                        <!-- STATUS -->

                                        <?php if (
                                            $reminder["status"]
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


                                    </div>



                                    <!-- ACTION BUTTONS -->

                                    <div
                                        class="ms-3 d-flex gap-1"
                                    >


                                        <!-- COMPLETE -->

                                        <?php if (
                                            $reminder["status"]
                                            ===
                                            "Pending"
                                        ): ?>

                                            <a
                                                href="?complete=<?php echo $reminder["id"]; ?>"
                                                class="btn btn-sm btn-success"
                                                title="Complete"
                                            >

                                                <i
                                                    class="fa-solid fa-check"
                                                ></i>

                                            </a>

                                        <?php endif; ?>



                                        <!-- EDIT -->

                                        <a
                                            href="edit.php?id=<?php echo $reminder["id"]; ?>"
                                            class="btn btn-sm btn-primary"
                                            title="Edit"
                                        >

                                            <i
                                                class="fa-solid fa-pen"
                                            ></i>

                                        </a>



                                        <!-- DELETE -->

                                        <a
                                            href="?delete=<?php echo $reminder["id"]; ?>"
                                            class="btn btn-sm btn-danger"
                                            title="Delete"
                                            onclick="return confirm('Are you sure you want to delete this reminder?');"
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


                        <div
                            class="text-center py-5"
                        >

                            <i
                                class="fa-solid fa-bell-slash fa-3x text-muted mb-3"
                            ></i>


                            <h5 class="fw-bold">

                                No Reminders Yet

                            </h5>


                            <p class="text-muted">

                                Create your first reminder.

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