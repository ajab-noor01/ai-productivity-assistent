<?php

session_start();

require_once "../config/db.php";

$message = "";
$message_type = "";


// =====================================================
// REGISTRATION SUCCESS MESSAGE
// =====================================================

if (
    isset($_GET["registered"]) &&
    $_GET["registered"] === "1"
) {

    $message =
        "Account created successfully! "
        . "Your 15-day free trial has started. "
        . "Please login to continue.";

    $message_type = "success";
}


// =====================================================
// LOGIN
// =====================================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email =
        trim($_POST["email"] ?? "");

    $password =
        $_POST["password"] ?? "";


    // =================================================
    // VALIDATION
    // =================================================

    if (
        empty($email) ||
        empty($password)
    ) {

        $message =
            "Please enter your email and password.";

        $message_type =
            "danger";

    }

    else {


        // =============================================
        // GET USER
        // =============================================

        $stmt = $conn->prepare(

            "SELECT
                id,
                name,
                email,
                password,
                trial_start,
                trial_end,
                subscription_status,
                plan
             FROM users
             WHERE email = ?"

        );


        $stmt->bind_param(
            "s",
            $email
        );


        $stmt->execute();


        $result =
            $stmt->get_result();


        // =============================================
        // USER FOUND
        // =============================================

        if ($result->num_rows === 1) {

            $user =
                $result->fetch_assoc();


            // =========================================
            // PASSWORD CHECK
            // =========================================

            if (
                password_verify(
                    $password,
                    $user["password"]
                )
            ) {


                // =====================================
                // CHECK TRIAL / SUBSCRIPTION
                // =====================================

                $current_time =
                    time();

                $trial_end =
                    strtotime(
                        $user["trial_end"]
                    );


                // =====================================
                // ACTIVE PAID ACCOUNT
                // =====================================

                if (
                    $user["subscription_status"]
                    === "active"
                ) {

                    // Create session

                    $_SESSION["user_id"] =
                        $user["id"];

                    $_SESSION["user_name"] =
                        $user["name"];

                    $_SESSION["user_email"] =
                        $user["email"];

                    $_SESSION["subscription_status"] =
                        $user["subscription_status"];

                    $_SESSION["plan"] =
                        $user["plan"];


                    // Dashboard

                    header(
                        "Location: ../dashboard/index.php"
                    );

                    exit;

                }


                // =====================================
                // ACTIVE FREE TRIAL
                // =====================================

                if (
                    $user["subscription_status"]
                    === "trial"
                    &&
                    $trial_end > $current_time
                ) {


                    // Create session

                    $_SESSION["user_id"] =
                        $user["id"];

                    $_SESSION["user_name"] =
                        $user["name"];

                    $_SESSION["user_email"] =
                        $user["email"];

                    $_SESSION["subscription_status"] =
                        "trial";

                    $_SESSION["plan"] =
                        "free_trial";


                    // Dashboard

                    header(
                        "Location: ../dashboard/index.php"
                    );

                    exit;

                }


                // =====================================
                // TRIAL EXPIRED
                // =====================================

                if (
                    $user["subscription_status"]
                    === "trial"
                    &&
                    $trial_end <= $current_time
                ) {


                    // Update database

                    $update =
                        $conn->prepare(

                            "UPDATE users
                             SET subscription_status = 'expired'
                             WHERE id = ?"

                        );


                    $update->bind_param(
                        "i",
                        $user["id"]
                    );


                    $update->execute();

                    $update->close();


                    // Store minimum session data

                    $_SESSION["user_id"] =
                        $user["id"];

                    $_SESSION["user_name"] =
                        $user["name"];

                    $_SESSION["user_email"] =
                        $user["email"];


                    // Send to subscription page

                    header(
                        "Location: ../dashboard/index.php"
                    );

                    exit;

                }


                // =====================================
                // OTHER / EXPIRED ACCOUNT
                // =====================================

                if (
                    $user["subscription_status"]
                    === "expired"
                ) {

                    $_SESSION["user_id"] =
                        $user["id"];

                    $_SESSION["user_name"] =
                        $user["name"];

                    $_SESSION["user_email"] =
                        $user["email"];


                    header(
                        "Location: ../subscription/index.php"
                    );

                    exit;

                }


                // =====================================
                // UNKNOWN STATUS
                // =====================================

                $message =
                    "Your account status could not be verified. "
                    . "Please contact support.";

                $message_type =
                    "danger";

            }

            else {

                $message =
                    "Incorrect email or password.";

                $message_type =
                    "danger";

            }

        }

        else {

            $message =
                "Incorrect email or password.";

            $message_type =
                "danger";

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
        Login - AI Productivity Assistant
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


    <style>

        body {

            background: #f5f7fb;

        }


        .login-wrapper {

            min-height: 100vh;

            display: flex;

            align-items: center;

            justify-content: center;

            padding: 30px 15px;

        }


        .login-card {

            width: 100%;

            max-width: 460px;

            background: #ffffff;

            border: 1px solid #e9ecef;

            border-radius: 18px;

            box-shadow:
                0 10px 35px
                rgba(0, 0, 0, 0.07);

        }


        .brand-icon {

            width: 52px;

            height: 52px;

            display: inline-flex;

            align-items: center;

            justify-content: center;

            border-radius: 14px;

            background: #0d6efd;

            color: #ffffff;

            font-size: 22px;

            margin-bottom: 15px;

        }


        .login-title {

            font-weight: 700;

        }


        .login-subtitle {

            color: #6c757d;

            font-size: 14px;

        }


        .trial-info {

            background: #eef5ff;

            border: 1px solid #cfe2ff;

            border-radius: 12px;

            padding: 13px;

            color: #084298;

        }


        .form-control {

            padding: 11px 13px;

            border-radius: 10px;

        }


        .form-control:focus {

            border-color: #86b7fe;

            box-shadow:
                0 0 0
                0.2rem
                rgba(13, 110, 253, 0.15);

        }


        .login-btn {

            padding: 12px;

            border-radius: 10px;

            font-weight: 600;

        }


        .register-link {

            color: #0d6efd;

            text-decoration: none;

            font-weight: 600;

        }


        .register-link:hover {

            text-decoration: underline;

        }

    </style>

</head>


<body>


<div class="login-wrapper">


    <div class="login-card">


        <div class="card-body p-4 p-md-5">


            <!-- BRAND -->

            <div class="text-center mb-4">

                <div class="brand-icon">

                    <i class="fa-solid fa-robot"></i>

                </div>


                <h2 class="login-title mb-2">

                    Welcome Back

                </h2>


                <p class="login-subtitle mb-0">

                    Sign in to continue to your
                    productivity workspace.

                </p>

            </div>


            <!-- MESSAGE -->

            <?php if (!empty($message)): ?>

                <div
                    class="alert alert-<?=
                        htmlspecialchars(
                            $message_type
                        )
                    ?>"
                >

                    <?= htmlspecialchars($message) ?>

                </div>

            <?php endif; ?>


            <!-- TRIAL INFO -->

            <div class="trial-info mb-4">

                <i
                    class="fa-solid
                           fa-gift
                           me-1"
                ></i>

                <strong>
                    15-Day Free Trial
                </strong>

                <div class="small mt-1">

                    New users can start with
                    a 15-day free trial.

                </div>

            </div>


            <!-- LOGIN FORM -->

            <form method="POST">


                <!-- EMAIL -->

                <div class="mb-3">

                    <label
                        class="form-label fw-semibold"
                    >

                        Email Address

                    </label>


                    <input
                        type="email"
                        name="email"
                        class="form-control"
                        placeholder="Enter your email"
                        value="<?=
                            htmlspecialchars(
                                $_POST["email"] ?? ""
                            )
                        ?>"
                        required
                    >

                </div>


                <!-- PASSWORD -->

                <div class="mb-4">

                    <label
                        class="form-label fw-semibold"
                    >

                        Password

                    </label>


                    <input
                        type="password"
                        name="password"
                        class="form-control"
                        placeholder="Enter your password"
                        required
                    >

                </div>


                <!-- LOGIN BUTTON -->

                <button
                    type="submit"
                    class="btn btn-primary
                           login-btn
                           w-100"
                >

                    <i
                        class="fa-solid
                               fa-right-to-bracket
                               me-2"
                    ></i>

                    Login

                </button>


            </form>


            <!-- REGISTER -->

            <div
                class="text-center
                       mt-4"
            >

                <span class="text-muted">

                    Don't have an account?

                </span>


                <a
                    href="register.php"
                    class="register-link"
                >

                    Create Account

                </a>

            </div>


        </div>

    </div>

</div>


</body>

</html>
