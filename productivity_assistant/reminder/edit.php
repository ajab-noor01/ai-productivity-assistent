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


$reminder_id = (int) $_GET["id"];


// =========================
// GET REMINDER
// =========================

$stmt = $conn->prepare(
    "SELECT *
     FROM reminders
     WHERE id = ? AND user_id = ?"
);


$stmt->bind_param(
    "ii",
    $reminder_id,
    $user_id
);


$stmt->execute();

$result = $stmt->get_result();


if ($result->num_rows !== 1) {

    header("Location: index.php");
    exit;
}


$reminder = $result->fetch_assoc();

$stmt->close();


$message = "";
$message_type = "";


// =========================
// UPDATE REMINDER
// =========================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST["title"]);
    $description = trim($_POST["description"]);
    $reminder_date = $_POST["reminder_date"];
    $reminder_time = $_POST["reminder_time"];
    $status = $_POST["status"];


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
            "UPDATE reminders
             SET title = ?,
                 description = ?,
                 reminder_date = ?,
                 reminder_time = ?,
                 status = ?
             WHERE id = ? AND user_id = ?"
        );


        $stmt->bind_param(
            "sssssii",
            $title,
            $description,
            $reminder_date,
            $reminder_time,
            $status,
            $reminder_id,
            $user_id
        );


        if ($stmt->execute()) {

            header("Location: index.php");
            exit;

        } else {

            $message = "Failed to update reminder.";
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

    <title>
        Edit Reminder -TaskPulse AI
    </title>


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


                    <div
                        class="d-flex justify-content-between align-items-center mb-4"
                    >

                        <h3 class="fw-bold mb-0">

                            <i
                                class="fa-solid fa-pen-to-square text-primary"
                            ></i>

                            Edit Reminder

                        </h3>


                        <a
                            href="index.php"
                            class="btn btn-outline-secondary"
                        >

                            Back

                        </a>

                    </div>



                    <?php if (!empty($message)): ?>

                        <div
                            class="alert alert-<?php echo $message_type; ?>"
                        >

                            <?php

                            echo htmlspecialchars(
                                $message
                            );

                            ?>

                        </div>

                    <?php endif; ?>



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
                                value="<?php echo htmlspecialchars($reminder["title"]); ?>"
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
                                rows="5"
                            ><?php

                            echo htmlspecialchars(
                                $reminder["description"]
                            );

                            ?></textarea>

                        </div>



                        <div class="row">


                            <!-- DATE -->

                            <div class="col-md-6 mb-3">

                                <label
                                    class="form-label fw-semibold"
                                >

                                    Reminder Date

                                </label>


                                <input
                                    type="date"
                                    name="reminder_date"
                                    class="form-control"
                                    value="<?php echo htmlspecialchars($reminder["reminder_date"]); ?>"
                                    required
                                >

                            </div>



                            <!-- TIME -->

                            <div class="col-md-6 mb-3">

                                <label
                                    class="form-label fw-semibold"
                                >

                                    Reminder Time

                                </label>


                                <input
                                    type="time"
                                    name="reminder_time"
                                    class="form-control"
                                    value="<?php echo htmlspecialchars($reminder["reminder_time"]); ?>"
                                    required
                                >

                            </div>

                        </div>



                        <!-- STATUS -->

                        <div class="mb-4">

                            <label
                                class="form-label fw-semibold"
                            >

                                Status

                            </label>


                            <select
                                name="status"
                                class="form-select"
                            >

                                <option
                                    value="Pending"
                                    <?php

                                    echo $reminder["status"]
                                        ===
                                        "Pending"
                                        ? "selected"
                                        : "";

                                    ?>
                                >

                                    Pending

                                </option>


                                <option
                                    value="Completed"
                                    <?php

                                    echo $reminder["status"]
                                        ===
                                        "Completed"
                                        ? "selected"
                                        : "";

                                    ?>
                                >

                                    Completed

                                </option>

                            </select>

                        </div>



                        <!-- BUTTON -->

                        <button
                            type="submit"
                            class="btn btn-primary w-100"
                        >

                            <i
                                class="fa-solid fa-save"
                            ></i>

                            Update Reminder

                        </button>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

</body>

</html>