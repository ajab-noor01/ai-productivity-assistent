<?php

require_once "config/db.php";
require_once "config/trial_check.php";

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>TaskPulse AI</title>

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
            color: #212529;
        }

        .navbar {
            min-height: 70px;
        }

        .brand-icon {
            width: 40px;
            height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            background: #0d6efd;
            color: #ffffff;
            border-radius: 10px;
            margin-right: 8px;
        }

        .hero {
            padding: 90px 20px 70px;
        }

        .hero-badge {
            display: inline-block;
            background: #e7f1ff;
            color: #0d6efd;
            padding: 8px 15px;
            border-radius: 50px;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .hero-title {
            font-size: 52px;
            font-weight: 800;
            line-height: 1.15;
        }

        .hero-title span {
            color: #0d6efd;
        }

        .hero-text {
            max-width: 650px;
            margin: 20px auto;
            color: #6c757d;
            font-size: 18px;
            line-height: 1.7;
        }

        .hero-buttons {
            margin-top: 30px;
        }

        .hero-buttons .btn {
            padding: 12px 24px;
            border-radius: 10px;
            font-weight: 600;
        }

        .feature-section {
            padding: 30px 20px 80px;
        }

        .feature-card {
            background: #ffffff;
            border: 1px solid #e9ecef;
            border-radius: 16px;
            padding: 28px;
            height: 100%;
            transition: 0.2s ease;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.08);
        }

        .feature-icon {
            width: 52px;
            height: 52px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 13px;
            background: #e7f1ff;
            color: #0d6efd;
            font-size: 21px;
            margin-bottom: 18px;
        }

        .feature-card h5 {
            font-weight: 700;
        }

        .feature-card p {
            color: #6c757d;
            margin-bottom: 0;
            line-height: 1.6;
        }

        .cta-section {
            padding: 30px 20px 80px;
        }

        .cta-card {
            background: linear-gradient(
                135deg,
                #0d6efd,
                #084298
            );
            color: #ffffff;
            border-radius: 20px;
            padding: 50px 30px;
        }

        .cta-card p {
            color: #dbe9ff;
        }

        footer {
            background: #212529;
            color: #adb5bd;
            padding: 25px 20px;
        }

        @media (max-width: 768px) {

            .hero {
                padding-top: 60px;
            }

            .hero-title {
                font-size: 38px;
            }

            .hero-text {
                font-size: 16px;
            }

        }

    </style>

</head>


<body>


<!-- =====================================================
     NAVBAR
===================================================== -->

<nav class="navbar navbar-expand-lg bg-white border-bottom">

    <div class="container">

        <a
            href="index.php"
            class="navbar-brand fw-bold d-flex align-items-center"
        >

            <span class="brand-icon">

                <i class="fa-solid fa-robot"></i>

            </span>

            TaskPulse AI

        </a>


        <div class="ms-auto">

            <a
                href="auth/login.php"
                class="btn btn-outline-primary me-2"
            >

                Login

            </a>


            <a
                href="auth/register.php"
                class="btn btn-primary"
            >

                Get Started

            </a>

        </div>

    </div>

</nav>


<!-- =====================================================
     HERO
===================================================== -->

<section class="hero text-center">

    <div class="container">

        <div class="hero-badge">

            <i class="fa-solid fa-sparkles me-1"></i>

            Smart Productivity Management

        </div>


        <h1 class="hero-title">

            Organize Your Work.
            <br>

            <span>Work Smarter.</span>

        </h1>


        <p class="hero-text">

            Manage your tasks, notes and reminders
            from one professional productivity platform
            powered by an intelligent AI assistant.

        </p>


        <div class="hero-buttons">

            <a
                href="auth/register.php"
                class="btn btn-primary me-2"
            >

                <i class="fa-solid fa-rocket me-1"></i>

                Get Started

            </a>


            <a
                href="auth/login.php"
                class="btn btn-outline-dark"
            >

                <i class="fa-solid fa-right-to-bracket me-1"></i>

                Login

            </a>

        </div>

    </div>

</section>


<!-- =====================================================
     FEATURES
===================================================== -->

<section class="feature-section">

    <div class="container">

        <div class="text-center mb-5">

            <h2 class="fw-bold">

                Everything You Need

            </h2>

            <p class="text-muted">

                Simple tools designed to keep your work organized.

            </p>

        </div>


        <div class="row g-4">


            <!-- TASKS -->

            <div class="col-md-6 col-lg-3">

                <div class="feature-card">

                    <div class="feature-icon">

                        <i class="fa-solid fa-list-check"></i>

                    </div>

                    <h5>

                        Tasks

                    </h5>

                    <p>

                        Create, organize and track
                        your daily tasks with priorities
                        and due dates.

                    </p>

                </div>

            </div>


            <!-- NOTES -->

            <div class="col-md-6 col-lg-3">

                <div class="feature-card">

                    <div class="feature-icon">

                        <i class="fa-solid fa-note-sticky"></i>

                    </div>

                    <h5>

                        Notes

                    </h5>

                    <p>

                        Keep important ideas,
                        project information and
                        notes organized in one place.

                    </p>

                </div>

            </div>


            <!-- REMINDERS -->

            <div class="col-md-6 col-lg-3">

                <div class="feature-card">

                    <div class="feature-icon">

                        <i class="fa-solid fa-bell"></i>

                    </div>

                    <h5>

                        Reminders

                    </h5>

                    <p>

                        Never forget important
                        activities with organized
                        date and time reminders.

                    </p>

                </div>

            </div>


            <!-- AI -->

            <div class="col-md-6 col-lg-3">

                <div class="feature-card">

                    <div class="feature-icon">

                        <i class="fa-solid fa-robot"></i>

                    </div>

                    <h5>

                        AI Assistant

                    </h5>

                    <p>

                        Ask questions naturally
                        and get intelligent insights
                        about your productivity.

                    </p>

                </div>

            </div>


        </div>

    </div>

</section>


<!-- =====================================================
     CTA
===================================================== -->

<section class="cta-section">

    <div class="container">

        <div class="cta-card text-center">

            <h2 class="fw-bold mb-3">

                Ready to Work Smarter?

            </h2>


            <p class="mb-4">

                Start organizing your tasks,
                notes and reminders today.

            </p>


            <a
                href="auth/register.php"
                class="btn btn-light btn-lg px-4"
            >

                Create Your Account

            </a>

        </div>

    </div>

</section>


<!-- =====================================================
     FOOTER
===================================================== -->

<footer>

    <div class="container text-center">

        <div class="mb-2">

            <i class="fa-solid fa-robot me-1"></i>

           TaskPulse AI

        </div>

        <small>

            Smart productivity management for modern work.

        </small>

    </div>

</footer>


</body>

</html>
