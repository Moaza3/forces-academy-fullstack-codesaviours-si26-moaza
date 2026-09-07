<?php
session_start();

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit;
}

require_once "config/db.php";

$student_id = (int) $_SESSION['student_id'];

$sql = "SELECT
            results.subject,
            results.marks,
            results.total_marks,
            results.grade,
            results.exam_type,
            courses.course_name
        FROM results
        LEFT JOIN courses
        ON results.course_id = courses.id
        WHERE results.student_id = $student_id
        ORDER BY results.id DESC";

$results = mysqli_query($conn, $sql);

if (!$results) {
    die("Database Error: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Results</title>
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
                <a href="assignment.php" class="nav-link text-white">Assignment</a>
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

    <div class="p-4" style="margin-left: 250px;">
        <h2 class="mb-4">My Results</h2>

        <?php if (mysqli_num_rows($results) > 0): ?>

            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Subject</th>
                            <th>Marks</th>
                            <th>Total</th>
                            <th>Grade</th>
                            <th>Exam Type</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($results)): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['subject']); ?></td>
                                <td><?php echo htmlspecialchars($row['marks']); ?></td>
                                <td><?php echo htmlspecialchars($row['total_marks']); ?></td>
                                <td>
                                    <span class="badge bg-success">
                                        <?php echo htmlspecialchars($row['grade']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($row['exam_type']); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>

        <?php else: ?>

            <div class="alert alert-info">
                <h5>No Results Available</h5>
                <p class="mb-0">Your results have not been added yet.</p>
            </div>

        <?php endif; ?>

    </div>

</body>
</html>