<?php
require_once 'config/db.php';
session_start();

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit;
}

$student_id = (int)$_SESSION['student_id'];
$student_name = $_SESSION['student_name'] ?? 'Student';

$sql = "SELECT COUNT(*) AS total_courses FROM courses";
$result_courses = mysqli_query($conn, $sql);

if (!$result_courses) {
    die("Database Error (courses): " . mysqli_error($conn));
}

$row_courses = mysqli_fetch_assoc($result_courses);
$total_courses = $row_courses['total_courses'];

$sql_pending = "SELECT COUNT(*) AS total_pending 
                FROM assignment 
                WHERE id NOT IN (
                    SELECT assignment_id 
                    FROM submissions 
                    WHERE student_id = $student_id
                )";
$result_pending = mysqli_query($conn, $sql_pending);
$pending_assignments = 0;

if ($result_pending) {
    $row_pending = mysqli_fetch_assoc($result_pending);
    $pending_assignments = $row_pending['total_pending'];
}

$sql = "SELECT title, content, created_at FROM notices ORDER BY created_at DESC LIMIT 1";
$result_latest = mysqli_query($conn, $sql);

if (!$result_latest) {
    die("Database Error (latest notice): " . mysqli_error($conn));
}

$latest_notice = mysqli_fetch_assoc($result_latest);

$sql = "SELECT title, content, created_at FROM notices ORDER BY created_at DESC LIMIT 3";
$result_recent = mysqli_query($conn, $sql);

if (!$result_recent) {
    die("Database Error (recent notices): " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
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
        }

        .bg-dark .nav-link.text-danger:hover {
            background-color: rgba(255, 107, 107, 0.15);
            color: #ff8787 !important;
        }

        /* Main heading */
        h2 {
            color: var(--green);
            font-weight: 700;
        }

        hr {
            border-top: 2px solid var(--butter);
            opacity: 1;
        }

        h4 {
            color: var(--green);
            font-weight: 700;
        }

        /* Cards */
        .card {
            border-radius: 14px !important;
            transition: transform 0.25s ease, box-shadow 0.25s ease;
            border-top: 4px solid var(--green) !important;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(1, 62, 55, 0.15) !important;
        }

        .card-body h5 {
            color: var(--green);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.95rem;
            letter-spacing: 0.5px;
        }

        .card-body h2 {
            font-weight: 800;
            font-size: 2.2rem;
        }

        .text-warning {
            color: #d9a441 !important;
        }

        /* Buttons - keep same classes, only recolor */
        .btn-primary {
            background-color: var(--green) !important;
            border-color: var(--green) !important;
        }
        .btn-primary:hover {
            background-color: var(--green-dark) !important;
            border-color: var(--green-dark) !important;
        }

        .btn-info {
            background-color: var(--butter) !important;
            border-color: var(--butter) !important;
            color: var(--text-dark) !important;
        }
        .btn-info:hover {
            background-color: #ffe58f !important;
            border-color: #ffe58f !important;
            color: var(--text-dark) !important;
        }

        .btn-success {
            background-color: var(--green) !important;
            border-color: var(--green) !important;
        }
        .btn-success:hover {
            background-color: var(--green-dark) !important;
            border-color: var(--green-dark) !important;
        }

        .btn-secondary {
            background-color: #6c757d !important;
            border-color: #6c757d !important;
        }

        .btn {
            border-radius: 8px !important;
            font-weight: 500;
        }

        .alert-info {
            background-color: var(--butter);
            border-color: var(--butter);
            color: var(--text-dark);
            border-radius: 10px;
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

    <div class="bg-dark text-white p-3 vh-100" style="width: 250px; position: fixed; left: 0; top: 0;">
        <h4 class="text-center mb-4">Student Portal</h4>
        <ul class="nav flex-column">
            <li class="nav-item mb-2">
                <a href="dashboard.php" class="nav-link text-white fw-normal">Dashboard</a>
            </li>
            <li class="nav-item mb-2">
                <a href="profile.php" class="nav-link text-white fw-normal">My Profile</a>
            </li>
            <li class="nav-item mb-2">
                <a href="courses.php" class="nav-link text-white fw-normal">My Courses</a>
            </li>
            <li class="nav-item mb-2">
                <a href="timetable.php" class="nav-link text-white fw-normal">Timetable</a>
            </li>
            <li class="nav-item mb-2">
                <a href="assignment.php" class="nav-link text-white fw-normal">Assignments</a>
            </li>
            <li class="nav-item mb-2">
                <a href="results.php" class="nav-link text-white fw-normal">My Results</a>
            </li>
            <li class="nav-item mb-2">
                <a href="notices.php" class="nav-link text-white fw-normal">Notices</a>
            </li>
            <li class="nav-item mt-3">
                <a href="logout.php" class="nav-link text-danger fw-normal">Logout</a>
            </li>
        </ul>
    </div>

    <div class="p-4" style="margin-left: 250px; width: calc(100% - 250px);">
        <h2>Welcome, <?php echo htmlspecialchars($student_name); ?>!</h2>
        <p class="text-muted">Welcome to your student dashboard.</p>
        <hr>

        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h5>Total Courses</h5>
                        <h2><?php echo $total_courses; ?></h2>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h5>Pending Assignments</h5>
                        <h2 class="text-warning"><?php echo $pending_assignments; ?></h2>
                        <p class="text-muted mb-0">Assignments remaining to submit</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h5>Latest Notice</h5>
                        <?php if ($latest_notice) { ?>
                            <h6><?php echo htmlspecialchars($latest_notice['title']); ?></h6>
                            <p class="mb-1"><?php echo htmlspecialchars($latest_notice['content']); ?></p>
                            <small class="text-muted">
                                Posted on: <?php echo date('d M Y', strtotime($latest_notice['created_at'])); ?>
                            </small>
                        <?php } else { ?>
                            <p class="mb-0">No notices yet.</p>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-5">
            <h4>Recent Notices</h4>
            <?php if (mysqli_num_rows($result_recent) > 0) { ?>
                <?php while ($notice = mysqli_fetch_assoc($result_recent)) { ?>
                    <div class="card mb-3 shadow-sm border-0">
                        <div class="card-body">
                            <h5><?php echo htmlspecialchars($notice['title']); ?></h5>
                            <p class="mb-1"><?php echo htmlspecialchars($notice['content']); ?></p>
                            <small class="text-muted">
                                Posted on: <?php echo date('d M Y', strtotime($notice['created_at'])); ?>
                            </small>
                        </div>
                    </div>
                <?php } ?>
            <?php } else { ?>
                <div class="alert alert-info">No recent notices available.</div>
            <?php } ?>
        </div>

        <div class="mt-4">
            <h4>Quick Links</h4>
            <a href="courses.php" class="btn btn-primary me-2">My Courses</a>
            <a href="timetable.php" class="btn btn-info text-white me-2">Timetable</a>
            <a href="assignment.php" class="btn btn-success me-2">Assignments</a>
            <a href="profile.php" class="btn btn-secondary">My Profile</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>