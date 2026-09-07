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
        WHERE results.student_id = ?
        ORDER BY results.id DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);
$results = mysqli_stmt_get_result($stmt);

if (!$results) {
    die("Database Error: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Results | Student Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
        <h2 class="mb-4">My Results</h2>

        <?php if (mysqli_num_rows($results) > 0): ?>

            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th class="ps-3">Subject</th>
                                    <th>Course</th>
                                    <th>Marks Obtained</th>
                                    <th>Total Marks</th>
                                    <th>Grade</th>
                                    <th>Exam Type</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($results)): ?>
                                    <?php
                                    $grade = strtoupper(trim($row['grade']));
                                    $badge_class = 'bg-secondary';
                                    
                                    if (in_array($grade, ['A+', 'A', 'A-'])) {
                                        $badge_class = 'bg-success';
                                    } elseif (in_array($grade, ['B+', 'B', 'B-'])) {
                                        $badge_class = 'bg-primary';
                                    } elseif (in_array($grade, ['C+', 'C', 'C-'])) {
                                        $badge_class = 'bg-info text-dark';
                                    } elseif (in_array($grade, ['D+', 'D'])) {
                                        $badge_class = 'bg-warning text-dark';
                                    } elseif ($grade === 'F') {
                                        $badge_class = 'bg-danger';
                                    }
                                    ?>
                                    <tr>
                                        <td class="ps-3 fw-semibold"><?php echo htmlspecialchars($row['subject']); ?></td>
                                        <td><?php echo htmlspecialchars($row['course_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($row['marks']); ?></td>
                                        <td><?php echo htmlspecialchars($row['total_marks']); ?></td>
                                        <td>
                                            <span class="badge <?php echo $badge_class; ?>">
                                                <?php echo htmlspecialchars($row['grade']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['exam_type']); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        <?php else: ?>

            <div class="alert alert-info border-0 shadow-sm">
                <h5 class="alert-heading">No Results Available</h5>
                <p class="mb-0">Your results have not been added yet.</p>
            </div>

        <?php endif; ?>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"></script>
</body>
</html>