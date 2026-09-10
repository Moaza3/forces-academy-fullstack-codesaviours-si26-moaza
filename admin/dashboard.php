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