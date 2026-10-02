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


$note_id = (int) $_GET["id"];


// =========================
// GET NOTE
// =========================

$stmt = $conn->prepare(
    "SELECT *
     FROM notes
     WHERE id = ? AND user_id = ?"
);

$stmt->bind_param(
    "ii",
    $note_id,
    $user_id
);

$stmt->execute();

$result = $stmt->get_result();


if ($result->num_rows !== 1) {

    header("Location: index.php");
    exit;
}


$note = $result->fetch_assoc();

$stmt->close();


$message = "";
$message_type = "";


// =========================
// UPDATE NOTE
// =========================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST["title"]);
    $content = trim($_POST["content"]);
    $category = trim($_POST["category"]);


    if (
        empty($title)
        ||
        empty($content)
    ) {

        $message = "Title and content are required.";
        $message_type = "danger";

    } else {

        if (empty($category)) {
            $category = "General";
        }


        $stmt = $conn->prepare(
            "UPDATE notes
             SET title = ?,
                 content = ?,
                 category = ?
             WHERE id = ? AND user_id = ?"
        );


        $stmt->bind_param(
            "sssii",
            $title,
            $content,
            $category,
            $note_id,
            $user_id
        );


        if ($stmt->execute()) {

            header("Location: index.php");
            exit;

        } else {

            $message = "Failed to update note.";
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
        Edit Note -TaskPulse AI
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

                            Edit Note

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

                                Note Title

                            </label>


                            <input
                                type="text"
                                name="title"
                                class="form-control"
                                value="<?php echo htmlspecialchars($note["title"]); ?>"
                                required
                            >

                        </div>



                        <!-- CATEGORY -->

                        <div class="mb-3">

                            <label
                                class="form-label fw-semibold"
                            >

                                Category

                            </label>


                            <select
                                name="category"
                                class="form-select"
                            >

                                <option
                                    value="General"
                                    <?php

                                    echo $note["category"]
                                        ===
                                        "General"
                                        ? "selected"
                                        : "";

                                    ?>
                                >

                                    General

                                </option>


                                <option
                                    value="Study"
                                    <?php

                                    echo $note["category"]
                                        ===
                                        "Study"
                                        ? "selected"
                                        : "";

                                    ?>
                                >

                                    Study

                                </option>


                                <option
                                    value="Work"
                                    <?php

                                    echo $note["category"]
                                        ===
                                        "Work"
                                        ? "selected"
                                        : "";

                                    ?>
                                >

                                    Work

                                </option>


                                <option
                                    value="Project"
                                    <?php

                                    echo $note["category"]
                                        ===
                                        "Project"
                                        ? "selected"
                                        : "";

                                    ?>
                                >

                                    Project

                                </option>


                                <option
                                    value="Meeting"
                                    <?php

                                    echo $note["category"]
                                        ===
                                        "Meeting"
                                        ? "selected"
                                        : "";

                                    ?>
                                >

                                    Meeting

                                </option>


                                <option
                                    value="Ideas"
                                    <?php

                                    echo $note["category"]
                                        ===
                                        "Ideas"
                                        ? "selected"
                                        : "";

                                    ?>
                                >

                                    Ideas

                                </option>

                            </select>

                        </div>



                        <!-- CONTENT -->

                        <div class="mb-4">

                            <label
                                class="form-label fw-semibold"
                            >

                                Note Content

                            </label>


                            <textarea
                                name="content"
                                class="form-control"
                                rows="10"
                                required
                            ><?php

                            echo htmlspecialchars(
                                $note["content"]
                            );

                            ?></textarea>

                        </div>



                        <!-- UPDATE -->

                        <button
                            type="submit"
                            class="btn btn-primary w-100"
                        >

                            <i
                                class="fa-solid fa-save"
                            ></i>

                            Update Note

                        </button>

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

</body>

</html>