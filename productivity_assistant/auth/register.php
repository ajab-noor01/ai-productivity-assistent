<?php

require_once "../config/db.php";

$message = "";
$message_type = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name = trim($_POST["name"] ?? "");
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";
    $confirm_password = $_POST["confirm_password"] ?? "";


    // =====================================================
    // VALIDATION
    // =====================================================

    if (
        empty($name) ||
        empty($email) ||
        empty($password) ||
        empty($confirm_password)
    ) {

        $message = "Please fill all fields.";
        $message_type = "danger";

    }

    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $message = "Please enter a valid email address.";
        $message_type = "danger";

    }

    elseif (strlen($password) < 6) {

        $message = "Password must be at least 6 characters.";
        $message_type = "danger";

    }

    elseif ($password !== $confirm_password) {

        $message = "Passwords do not match.";
        $message_type = "danger";

    }

    else {


        // =================================================
        // CHECK EXISTING EMAIL
        // =================================================

        $check = $conn->prepare(
            "SELECT id FROM users WHERE email = ?"
        );

        $check->bind_param(
            "s",
            $email
        );

        $check->execute();

        $result = $check->get_result();


        if ($result->num_rows > 0) {

            $message =
                "This email is already registered.";

            $message_type = "warning";

        }

        else {


            // =============================================
            // PASSWORD HASH
            // =============================================

            $hashed_password =
                password_hash(
                    $password,
                    PASSWORD_DEFAULT
                );


            // =============================================
            // 15-DAY FREE TRIAL
            // =============================================

            $trial_start =
                date("Y-m-d H:i:s");

            $trial_end =
                date(
                    "Y-m-d H:i:s",
                    strtotime("+15 days")
                );

            $subscription_status =
                "trial";

            $plan =
                "free_trial";


            // =============================================
            // CREATE USER
            // =============================================

            $stmt = $conn->prepare(

                "INSERT INTO users
                (
                    name,
                    email,
                    password,
                    trial_start,
                    trial_end,
                    subscription_status,
                    plan
                )
                VALUES (?, ?, ?, ?, ?, ?, ?)"

            );


            $stmt->bind_param(

                "sssssss",

                $name,
                $email,
                $hashed_password,
                $trial_start,
                $trial_end,
                $subscription_status,
                $plan

            );


            // =============================================
            // SUCCESS
            // =============================================

           
if ($stmt->execute()) {

    /*
     * Registration successful.
     * Send the user to the login page.
     */

    header("Location: login.php?registered=1");
    exit;

}



            else {

                $message =
                    "Something went wrong. "
                    . "Please try again.";

                $message_type =
                    "danger";

            }


            $stmt->close();

        }


        $check->close();

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
        Register - AI Productivity Assistant
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


        .register-wrapper {

            min-height: 100vh;

            display: flex;

            align-items: center;

            justify-content: center;

            padding: 30px 15px;

        }


        .register-card {

            width: 100%;

            max-width: 480px;

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


        .register-title {

            font-weight: 700;

        }


        .register-subtitle {

            color: #6c757d;

            font-size: 14px;

        }


        .trial-box {

            background: #eef5ff;

            border: 1px solid #cfe2ff;

            border-radius: 12px;

            padding: 14px;

            color: #084298;

        }


        .trial-box strong {

            display: block;

            margin-bottom: 3px;

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


        .btn-register {

            padding: 12px;

            border-radius: 10px;

            font-weight: 600;

        }


        .login-link {

            color: #0d6efd;

            text-decoration: none;

            font-weight: 600;

        }


        .login-link:hover {

            text-decoration: underline;

        }

    </style>

</head>


<body>


<div class="register-wrapper">


    <div class="register-card">


        <div class="card-body p-4 p-md-5">


            <!-- BRAND -->

            <div class="text-center mb-4">

                <div class="brand-icon">

                    <i class="fa-solid fa-robot"></i>

                </div>


                <h2 class="register-title mb-2">

                    Create Your Account

                </h2>


                <p class="register-subtitle mb-0">

                    Start managing your productivity smarter.

                </p>

            </div>


            <!-- TRIAL -->

            <div class="trial-box mb-4">

                <strong>

                    <i class="fa-solid fa-gift me-1"></i>

                    15-Day Free Trial

                </strong>

                <small>

                    Get full access for 15 days.
                    No payment is required to start.

                </small>

            </div>


            <!-- MESSAGE -->

            <?php if (!empty($message)): ?>

                <div
                    class="alert alert-<?=
                        htmlspecialchars($message_type)
                    ?>"
                >

                    <?= htmlspecialchars($message) ?>

                </div>

            <?php endif; ?>


            <!-- FORM -->

            <form
                method="POST"
                novalidate
            >


                <!-- NAME -->

                <div class="mb-3">

                    <label
                        class="form-label fw-semibold"
                    >

                        Full Name

                    </label>


                    <input
                        type="text"
                        name="name"
                        class="form-control"
                        placeholder="Enter your full name"
                        value="<?=
                            htmlspecialchars(
                                $_POST["name"] ?? ""
                            )
                        ?>"
                        required
                    >

                </div>


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

                <div class="mb-3">

                    <label
                        class="form-label fw-semibold"
                    >

                        Password

                    </label>


                    <input
                        type="password"
                        name="password"
                        class="form-control"
                        placeholder="At least 6 characters"
                        required
                    >

                </div>


                <!-- CONFIRM PASSWORD -->

                <div class="mb-4">

                    <label
                        class="form-label fw-semibold"
                    >

                        Confirm Password

                    </label>


                    <input
                        type="password"
                        name="confirm_password"
                        class="form-control"
                        placeholder="Confirm your password"
                        required
                    >

                </div>


                <!-- SUBMIT -->

                <button
                    type="submit"
                    class="btn btn-primary
                           btn-register
                           w-100"
                >

                    <i
                        class="fa-solid
                               fa-user-plus
                               me-2"
                    ></i>

                    Create Account

                </button>


            </form>


            <!-- LOGIN -->

            <div
                class="text-center
                       mt-4"
            >

                <span class="text-muted">

                    Already have an account?

                </span>


                <a
                    href="login.php"
                    class="login-link"
                >

                    Login

                </a>

            </div>


        </div>

    </div>

</div>


</body>

</html>
