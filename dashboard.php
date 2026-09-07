<?php
require_once 'config/db.php';
session_start();

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit;
}

$student_id = $_SESSION['student_id'];
$student_name = $_SESSION['student_name'];

$sql = "SELECT COUNT(*) AS total_courses FROM courses";
$result_courses = mysqli_query($conn, $sql);

if (!$result_courses) {
    die("Database Error (courses): " . mysqli_error($conn));
}

$row_courses = mysqli_fetch_assoc($result_courses);
$total_courses = $row_courses['total_courses'];

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
</head>
<body>

    <div class="bg-dark text-white p-3 vh-100" style="width: 250px; position: fixed; left: 0; top: 0;">
        <h4 class="text-center mb-4">Student Portal</h4>
        <ul class="nav flex-column">
            <li class="nav-item mb-2">
                <a href="dashboard.php" class="nav-link text-white">Dashboard</a>
            </li>
            <li class="nav-item mb-2">
                <a href="courses.php" class="nav-link text-white">My Courses</a>
            </li>
            <li class="nav-item mb-2">
                <a href="timetable.php" class="nav-link text-white">Timetable</a>
            </li>
            <li class="nav-item mb-2">
                <a href="assignment.php" class="nav-link text-white">Assignments</a>
            </li>
            <li class="nav-item mb-2">
                <a href="results.php" class="nav-link text-white">My Results</a>
            </li>
            <li class="nav-item mb-2">
                <a href="notices.php" class="nav-link text-white">Notices</a>
            </li>
            <li class="nav-item mt-3">
                <a href="logout.php" class="nav-link text-danger">Logout</a>
            </li>
        </ul>
    </div>

    <div class="p-4" style="margin-left: 250px; width: calc(100% - 250px);">
        <h2>Welcome, <?php echo htmlspecialchars($student_name); ?>!</h2>
        <p class="text-muted">Welcome to your student dashboard.</p>
        <hr>

        <div class="row mt-4">
            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5>Total Courses</h5>
                        <h2><?php echo $total_courses; ?></h2>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5>Pending Assignments</h5>
                        <h2>0</h2>
                        <p class="text-muted">Coming soon</p>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5>Latest Notice</h5>
                        <?php if ($latest_notice) { ?>
                            <h6><?php echo htmlspecialchars($latest_notice['title']); ?></h6>
                            <p><?php echo htmlspecialchars($latest_notice['content']); ?></p>
                            <small class="text-muted">
                                Posted on:
                                <?php echo date('d M Y', strtotime($latest_notice['created_at'])); ?>
                            </small>
                        <?php } else { ?>
                            <p>No notices yet.</p>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-5">
            <h4>Recent Notices</h4>
            <?php if (mysqli_num_rows($result_recent) > 0) { ?>
                <?php while ($notice = mysqli_fetch_assoc($result_recent)) { ?>
                    <div class="card mb-3 shadow-sm">
                        <div class="card-body">
                            <h5><?php echo htmlspecialchars($notice['title']); ?></h5>
                            <p><?php echo htmlspecialchars($notice['content']); ?></p>
                            <small class="text-muted">
                                Posted on:
                                <?php echo date('d M Y', strtotime($notice['created_at'])); ?>
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
            <a href="assignment.php" class="btn btn-success">Assignments</a>
        </div>
    </div>

</body>
</html>