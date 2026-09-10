<?php
session_start();
require_once "../config/db.php";

// Check admin session
if (!isset($_SESSION["admin_id"]) || $_SESSION["admin_role"] !== "admin") {
    header("Location: login.php");
    exit;
}

// Count total students
$student_query = "SELECT COUNT(*) AS total_students FROM students";
$student_result = mysqli_query($conn, $student_query);
$student_data = mysqli_fetch_assoc($student_result);
$total_students = $student_data["total_students"];

// Count total courses
$course_query = "SELECT COUNT(*) AS total_courses FROM courses";
$course_result = mysqli_query($conn, $course_query);
$course_data = mysqli_fetch_assoc($course_result);
$total_courses = $course_data["total_courses"];

// Count total assignments
$assignment_query = "SELECT COUNT(*) AS total_assignments FROM assignment";
$assignment_result = mysqli_query($conn, $assignment_query);
$assignment_data = mysqli_fetch_assoc($assignment_result);
$total_assignments = $assignment_data["total_assignments"];

// Count total notices
$notice_query = "SELECT COUNT(*) AS total_notices FROM notices";
$notice_result = mysqli_query($conn, $notice_query);
$notice_data = mysqli_fetch_assoc($notice_result);
$total_notices = $notice_data["total_notices"];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        /* ============================================
           Theme: Butter & Green
           Butter: #FFEFB3   |   Green: #013E37
           Only visual styling — no structure changed.
        ============================================ */

        :root {
            --butter: #FFEFB3;
            --green: #013E37;
            --green-dark: #012a25;
            --text-dark: #1a1a1a;
        }

        body.bg-light {
            background-color: #f4f6f5 !important;
            font-family: 'Segoe UI', 'Poppins', sans-serif;
        }

        /* Sidebar */
        .bg-dark {
            background: linear-gradient(180deg, var(--green) 0%, var(--green-dark) 100%) !important;
        }

        .bg-dark h4 {
            color: var(--butter) !important;
            font-weight: 700;
            letter-spacing: 0.5px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255, 239, 179, 0.2);
        }

        .bg-dark .nav-link {
            border-radius: 8px;
            padding: 10px 14px;
            transition: all 0.25s ease;
            font-weight: 500;
        }

        .bg-dark .nav-link:hover {
            background-color: rgba(255, 239, 179, 0.15);
            color: var(--butter) !important;
            padding-left: 20px;
        }

        .bg-dark .nav-link.text-danger {
            color: #ff6b6b !important;
            margin-top: 10px;
        }

        .bg-dark .nav-link.text-danger:hover {
            background-color: rgba(255, 107, 107, 0.15);
            color: #ff8787 !important;
        }

        /* Headings */
        h2.mb-4 {
            color: var(--green);
            font-weight: 700;
            position: relative;
            padding-bottom: 10px;
        }

        h2.mb-4::after {
            content: "";
            position: absolute;
            left: 0;
            bottom: 0;
            width: 60px;
            height: 4px;
            background-color: var(--butter);
            border-radius: 2px;
        }

        .container-fluid p.mb-4 {
            color: #555;
            font-size: 1.05rem;
        }

        /* Cards */
        .card {
            border: none !important;
            border-radius: 16px !important;
            overflow: hidden;
            transition: transform 0.25s ease, box-shadow 0.25s ease;
            border-top: 4px solid var(--green) !important;
        }

        .card:hover {
            transform: translateY(-6px);
            box-shadow: 0 10px 25px rgba(1, 62, 55, 0.15) !important;
        }

        .card-body {
            padding: 28px 20px;
        }

        .card-title {
            color: var(--green);
            font-weight: 600;
            font-size: 1.05rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .card-body h2 {
            color: var(--text-dark);
            font-weight: 800;
            font-size: 2.4rem;
        }

        .row.g-4 > div:nth-child(1) .card { border-top-color: var(--green); }
        .row.g-4 > div:nth-child(2) .card { border-top-color: #FFD75E; }
        .row.g-4 > div:nth-child(3) .card { border-top-color: var(--green); }
        .row.g-4 > div:nth-child(4) .card { border-top-color: #FFD75E; }

        .row.g-4 > div:nth-child(2) .card-body h2,
        .row.g-4 > div:nth-child(4) .card-body h2 {
            color: var(--green);
        }

        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-thumb {
            background: var(--green);
            border-radius: 4px;
        }
    </style>
</head>
<body class="bg-light">

    <div class="container-fluid">
        <div class="row">

            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 bg-dark text-white min-vh-100 p-3">
                <h4 class="text-center mb-4">Admin Panel</h4>

                <div class="nav flex-column">
                    <a href="students.php" class="nav-link text-white mb-2">Manage Students</a>
                    <a href="courses.php" class="nav-link text-white mb-2">Manage Courses</a>
                    <a href="assignments.php" class="nav-link text-white mb-2">Manage Assignments</a>
                    <a href="results.php" class="nav-link text-white mb-2">Upload Results</a>
                    <a href="notices.php" class="nav-link text-white mb-2">Post Notice</a>
                    <a href="logout.php" class="nav-link text-danger">Logout</a>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 p-4">
                <h2 class="mb-4">Admin Dashboard</h2>

                <p class="mb-4">
                    Welcome, <?php echo htmlspecialchars($_SESSION["admin_username"]); ?>!
                </p>

                <!-- Statistics Cards -->
                <div class="row g-4">

                    <!-- Students -->
                    <div class="col-md-6 col-xl-3">
                        <div class="card shadow-sm">
                            <div class="card-body text-center">
                                <h5 class="card-title">Total Students</h5>
                                <h2 class="mt-3"><?php echo $total_students; ?></h2>
                            </div>
                        </div>
                    </div>

                    <!-- Courses -->
                    <div class="col-md-6 col-xl-3">
                        <div class="card shadow-sm">
                            <div class="card-body text-center">
                                <h5 class="card-title">Total Courses</h5>
                                <h2 class="mt-3"><?php echo $total_courses; ?></h2>
                            </div>
                        </div>
                    </div>

                    <!-- Assignments -->
                    <div class="col-md-6 col-xl-3">
                        <div class="card shadow-sm">
                            <div class="card-body text-center">
                                <h5 class="card-title">Total Assignments</h5>
                                <h2 class="mt-3"><?php echo $total_assignments; ?></h2>
                            </div>
                        </div>
                    </div>

                    <!-- Notices -->
                    <div class="col-md-6 col-xl-3">
                        <div class="card shadow-sm">
                            <div class="card-body text-center">
                                <h5 class="card-title">Total Notices</h5>
                                <h2 class="mt-3"><?php echo $total_notices; ?></h2>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

</body>
</html>