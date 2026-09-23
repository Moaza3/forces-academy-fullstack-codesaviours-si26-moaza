<?php
session_start();
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit;
}
require_once "config/db.php";
$student_id = $_SESSION['student_id'];
$query = "SELECT courses.* FROM courses 
          JOIN student_courses ON courses.id = student_courses.course_id 
          WHERE student_courses.student_id = ? 
          ORDER BY courses.created_at DESC";

$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --butter: #FFEFB3;
            --green: #013E37;
            --green-dark: #012a25;
            --text-dark: #1a1a1a;
        }

        body {
            background-color: #f4f6f5;
            font-family: 'Segoe UI', 'Poppins', sans-serif;
        }

        .navbar-custom {
            background: linear-gradient(135deg, var(--green) 0%, var(--green-dark) 100%);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .navbar-custom .navbar-brand {
            color: var(--butter) !important;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .navbar-custom .nav-link {
            color: #ffffff !important;
            font-weight: 500;
            border-radius: 6px;
            padding: 8px 12px !important;
            transition: all 0.2s ease;
        }

        .navbar-custom .nav-link:hover,
        .navbar-custom .nav-link.active {
            background-color: rgba(255, 239, 179, 0.15);
            color: var(--butter) !important;
        }

        .navbar-custom .nav-link.text-danger:hover {
            background-color: rgba(255, 107, 107, 0.15);
            color: #ff8787 !important;
        }

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

        .card {
            border-radius: 14px !important;
            border: none !important;
            border-top: 4px solid var(--green) !important;
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }

        .card:hover {
            transform: translateY(-6px);
            box-shadow: 0 10px 22px rgba(1, 62, 55, 0.15) !important;
        }

        .card-title {
            color: var(--green);
            font-weight: 700;
        }

        .card-text {
            color: #555;
        }

        .card-body strong {
            color: var(--green-dark);
        }

        .alert-info {
            background-color: var(--butter);
            border: none;
            border-radius: 12px;
            color: var(--text-dark);
        }
    </style>
</head>
<body>

    <nav class="navbar navbar-expand-lg navbar-custom sticky-top px-3 mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">Student Portal</a>
            <button class="navbar-toggler bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav align-items-lg-center gap-1">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="courses.php">My Courses</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="assignment.php">Assignments</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="result.php">My Results</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="notices.php">Notices</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="fees.php">Manage Fees</a>
                    </li>
                    <li class="nav-item ms-lg-2">
                        <a class="nav-link text-danger" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mb-5">
        <h2 class="mb-4">My Courses</h2>

        <div class="row">
            <?php if ($result && mysqli_num_rows($result) > 0): ?>
                <?php while ($course = mysqli_fetch_assoc($result)): ?>
                    <div class="col-md-4 mb-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <?php echo htmlspecialchars($course['course_name'] ?? ''); ?>
                                </h5>

                                <p class="card-text">
                                    <?php echo htmlspecialchars($course['description'] ?? ''); ?>
                                </p>

                                <p class="mb-0">
                                    <strong>Teacher:</strong>
                                    <?php echo htmlspecialchars($course['teacher_name'] ?? 'N/A'); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info">
                        <h5 class="fw-bold">No Courses Available</h5>
                        <p class="mb-0">You are not enrolled in any courses at the moment. Please check again later.</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>