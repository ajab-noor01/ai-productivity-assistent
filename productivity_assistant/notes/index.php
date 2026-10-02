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
require_once "../config/db.php";

// =====================================================
// USER ID
// =====================================================

$user_id = (int) $_SESSION["user_id"];

$message = "";
$message_type = "";

// =====================================================
// ADD NOTE
// =====================================================

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
    && isset($_POST["add_note"])
) {

    $title = trim($_POST["title"] ?? "");
    $content = trim($_POST["content"] ?? "");
    $category = trim($_POST["category"] ?? "");

    if (empty($title) || empty($content)) {

        $message = "Note title and content are required.";
        $message_type = "danger";

    } else {

        if (empty($category)) {
            $category = "General";
        }

        $stmt = $conn->prepare(
            "INSERT INTO notes
            (user_id, title, content, category)
            VALUES (?, ?, ?, ?)"
        );

        $stmt->bind_param(
            "isss",
            $user_id,
            $title,
            $content,
            $category
        );

        if ($stmt->execute()) {

            $message = "Note created successfully.";
            $message_type = "success";

        } else {

            $message = "Failed to create note.";
            $message_type = "danger";
        }

        $stmt->close();
    }
}

// =====================================================
// DELETE NOTE
// =====================================================

if (isset($_GET["delete"])) {

    $note_id = (int) $_GET["delete"];

    $stmt = $conn->prepare(
        "DELETE FROM notes
         WHERE id = ? AND user_id = ?"
    );

    $stmt->bind_param(
        "ii",
        $note_id,
        $user_id
    );

    $stmt->execute();

    $stmt->close();

    header("Location: index.php");
    exit;
}

// =====================================================
// SEARCH NOTES
// =====================================================

$search = "";

if (isset($_GET["search"])) {

    $search = trim($_GET["search"]);
}

if (!empty($search)) {

    $search_value = "%" . $search . "%";

    $stmt = $conn->prepare(
        "SELECT *
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

} else {

    $stmt = $conn->prepare(
        "SELECT *
         FROM notes
         WHERE user_id = ?
         ORDER BY id DESC"
    );

    $stmt->bind_param(
        "i",
        $user_id
    );
}

$stmt->execute();

$notes = $stmt->get_result();

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
        Notes - TaskPulse AI
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

<!-- =====================================================
     NAVBAR
===================================================== -->

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
                    $_SESSION["user_name"] ?? "User"
                );

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


<!-- =====================================================
     MAIN CONTAINER
===================================================== -->

<div class="container py-4">

    <!-- HEADER -->

    <div
        class="d-flex justify-content-between align-items-center mb-4"
    >

        <div>

            <h2 class="fw-bold mb-1">

                <i
                    class="fa-solid fa-note-sticky text-primary"
                ></i>

                My Notes

            </h2>

            <p class="text-muted mb-0">

                Create and organize your important notes.

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
            class="alert alert-<?php echo htmlspecialchars($message_type); ?> alert-dismissible fade show"
        >

            <?php echo htmlspecialchars($message); ?>

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert"
            ></button>

        </div>

    <?php endif; ?>


    <div class="row g-4">

        <!-- =================================================
             ADD NOTE
        ================================================== -->

        <div class="col-lg-4">

            <div class="card border-0 shadow-sm">

                <div class="card-body p-4">

                    <h4 class="fw-bold mb-4">

                        <i
                            class="fa-solid fa-plus text-primary"
                        ></i>

                        Create Note

                    </h4>

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
                                placeholder="Enter note title"
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

                                <option value="General">
                                    General
                                </option>

                                <option value="Study">
                                    Study
                                </option>

                                <option value="Work">
                                    Work
                                </option>

                                <option value="Project">
                                    Project
                                </option>

                                <option value="Meeting">
                                    Meeting
                                </option>

                                <option value="Ideas">
                                    Ideas
                                </option>

                            </select>

                        </div>


                        <!-- CONTENT -->

                        <div class="mb-3">

                            <label
                                class="form-label fw-semibold"
                            >
                                Note Content
                            </label>

                            <textarea
                                name="content"
                                class="form-control"
                                rows="7"
                                placeholder="Write your note..."
                                required
                            ></textarea>

                        </div>


                        <!-- BUTTON -->

                        <button
                            type="submit"
                            name="add_note"
                            class="btn btn-primary w-100"
                        >

                            <i class="fa-solid fa-plus"></i>

                            Create Note

                        </button>

                    </form>

                </div>

            </div>

        </div>


        <!-- =================================================
             NOTES AREA
        ================================================== -->

        <div class="col-lg-8">

            <!-- SEARCH -->

            <div class="card border-0 shadow-sm mb-4">

                <div class="card-body">

                    <form
                        method="GET"
                        class="d-flex gap-2"
                    >

                        <input
                            type="text"
                            name="search"
                            class="form-control"
                            placeholder="Search notes..."
                            value="<?php echo htmlspecialchars($search); ?>"
                        >

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >

                            <i class="fa-solid fa-search"></i>

                            Search

                        </button>

                        <?php if (!empty($search)): ?>

                            <a
                                href="index.php"
                                class="btn btn-outline-secondary"
                            >
                                Clear
                            </a>

                        <?php endif; ?>

                    </form>

                </div>

            </div>


            <!-- NOTES LIST -->

            <div class="card border-0 shadow-sm">

                <div class="card-body p-4">

                    <div
                        class="d-flex justify-content-between align-items-center mb-4"
                    >

                        <h4 class="fw-bold mb-0">
                            Your Notes
                        </h4>

                        <span class="badge bg-primary">

                            <?php echo $notes->num_rows; ?>

                            Notes

                        </span>

                    </div>


                    <?php if ($notes->num_rows > 0): ?>

                        <div class="row g-3">

                            <?php while (
                                $note = $notes->fetch_assoc()
                            ): ?>

                                <div class="col-md-6">

                                    <div
                                        class="card h-100 border shadow-sm"
                                    >

                                        <div class="card-body">

                                            <!-- TITLE -->

                                            <h5 class="fw-bold">

                                                <?php

                                                echo htmlspecialchars(
                                                    $note["title"]
                                                );

                                                ?>

                                            </h5>


                                            <!-- CATEGORY -->

                                            <span
                                                class="badge bg-info text-dark mb-2"
                                            >

                                                <?php

                                                echo htmlspecialchars(
                                                    $note["category"] ?? "General"
                                                );

                                                ?>

                                            </span>


                                            <!-- CONTENT -->

                                            <p
                                                class="text-muted"
                                                style="white-space: pre-line;"
                                            >

                                                <?php

                                                echo htmlspecialchars(
                                                    $note["content"]
                                                );

                                                ?>

                                            </p>

                                        </div>


                                        <!-- FOOTER -->

                                        <div
                                            class="card-footer bg-white border-0"
                                        >

                                            <small
                                                class="text-muted"
                                            >

                                                <i
                                                    class="fa-regular fa-clock"
                                                ></i>

                                                <?php

                                                echo htmlspecialchars(
                                                    $note["created_at"]
                                                );

                                                ?>

                                            </small>


                                            <div
                                                class="float-end"
                                            >

                                                <!-- EDIT -->

                                                <a
                                                    href="edit.php?id=<?php echo $note["id"]; ?>"
                                                    class="btn btn-sm btn-primary"
                                                    title="Edit Note"
                                                >

                                                    <i
                                                        class="fa-solid fa-pen"
                                                    ></i>

                                                </a>


                                                <!-- DELETE -->

                                                <a
                                                    href="?delete=<?php echo $note["id"]; ?>"
                                                    class="btn btn-sm btn-danger"
                                                    title="Delete Note"
                                                    onclick="return confirm('Are you sure you want to delete this note?');"
                                                >

                                                    <i
                                                        class="fa-solid fa-trash"
                                                    ></i>

                                                </a>

                                            </div>

                                        </div>

                                    </div>

                                </div>

                            <?php endwhile; ?>

                        </div>

                    <?php else: ?>

                        <div
                            class="text-center py-5"
                        >

                            <i
                                class="fa-solid fa-note-sticky fa-3x text-muted mb-3"
                            ></i>

                            <h5 class="fw-bold">
                                No Notes Found
                            </h5>

                            <p class="text-muted">
                                Create your first note using
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