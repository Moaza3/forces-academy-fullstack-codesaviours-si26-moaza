<?php
session_start();

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit;
}

require_once "config/db.php";

$student_id = $_SESSION['student_id'];

$sql = "SELECT
            assignment.id,
            assignment.title,
            assignment.description,
            assignment.due_date,
            courses.course_name
        FROM assignment
        LEFT JOIN courses
        ON assignment.course_id = courses.id
        ORDER BY assignment.due_date ASC";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Database Error: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Assignments</title>
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
                <a href="timetable.php" class="nav-link text-white">📅 Timetable</a>
            </li>
            <li class="nav-item mb-2">
                <a href="assignment.php" class="nav-link text-white">Assignment</a>
            </li>
            <li class="nav-item mb-2">
                <a href="result.php" class="nav-link text-white">My Results</a>
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
        <h2 class="mb-4">Assignments</h2>

        <div class="row">
            <?php if (mysqli_num_rows($result) > 0): ?>

                <?php while ($assignment = mysqli_fetch_assoc($result)): ?>

                    <?php
                    $assignment_id = (int) $assignment['id'];

                    $check_sql = "SELECT id
                                  FROM submissions
                                  WHERE assignment_id = $assignment_id
                                  AND student_id = $student_id
                                  LIMIT 1";

                    $check_result = mysqli_query($conn, $check_sql);

                    if (!$check_result) {
                        die("Submission Check Error: " . mysqli_error($conn));
                    }

                    $already_submitted = mysqli_num_rows($check_result) > 0;
                    ?>

                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card shadow-sm h-100">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <?php echo htmlspecialchars($assignment['title']); ?>
                                </h5>

                                <p class="mb-2">
                                    <strong>Course:</strong>
                                    <?php echo htmlspecialchars($assignment['course_name'] ?? 'N/A'); ?>
                                </p>

                                <p class="mb-2">
                                    <strong>Due Date:</strong>
                                    <?php echo date("d M Y", strtotime($assignment['due_date'])); ?>
                                </p>

                                <p class="card-text">
                                    <?php echo htmlspecialchars($assignment['description']); ?>
                                </p>

                                <?php if ($already_submitted): ?>
                                    <span class="badge bg-success">Submitted</span>
                                <?php else: ?>
                                    <a href="submit_assignment.php?id=<?php echo $assignment_id; ?>" class="btn btn-primary">
                                        Submit Assignment
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                <?php endwhile; ?>

            <?php else: ?>

                <div class="col-12">
                    <div class="alert alert-info">
                        <h5>No Assignment Available</h5>
                        <p class="mb-0">There are no assignments available at the moment.</p>
                    </div>
                </div>

            <?php endif; ?>
        </div>
    </div>

</body>
</html>