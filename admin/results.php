<?php
session_start();
require_once "../config/db.php";

if (!isset($_SESSION["admin_id"]) || $_SESSION["admin_role"] !== "admin") {
    header("Location: login.php");
    exit;
}

$error = "";
$success = "";

// Form sticky variables
$student_id_val = "";
$course_id_val = "";
$subject_val = "";
$marks_val = "";
$total_marks_val = "";
$grade_val = "";
$exam_type_val = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $student_id = (int) ($_POST["student_id"] ?? 0);
    $course_id = (int) ($_POST["course_id"] ?? 0);
    $subject = trim($_POST["subject"] ?? "");
    $marks = isset($_POST["marks"]) ? (int) $_POST["marks"] : -1;
    $total_marks = (int) ($_POST["total_marks"] ?? 0);
    $grade = trim($_POST["grade"] ?? "");
    $exam_type = trim($_POST["exam_type"] ?? "");

    // Preserve posted values for form retention on error
    $student_id_val = $student_id;
    $course_id_val = $course_id;
    $subject_val = $subject;
    $marks_val = $marks >= 0 ? $marks : "";
    $total_marks_val = $total_marks > 0 ? $total_marks : "";
    $grade_val = $grade;
    $exam_type_val = $exam_type;

    if (
        $student_id <= 0 ||
        $course_id <= 0 ||
        empty($subject) ||
        $marks < 0 ||
        $total_marks <= 0 ||
        empty($grade) ||
        empty($exam_type)
    ) {
        $error = "All fields are required and valid numbers must be provided.";
    } elseif ($marks > $total_marks) {
        $error = "Obtained marks cannot be greater than total marks.";
    } else {
        // Check if the student is actually enrolled in this course
        $check_sql = "SELECT * FROM student_courses WHERE student_id = ? AND course_id = ?";
        $check_stmt = mysqli_prepare($conn, $check_sql);
        mysqli_stmt_bind_param($check_stmt, "ii", $student_id, $course_id);
        mysqli_stmt_execute($check_stmt);
        $check_result = mysqli_stmt_get_result($check_stmt);

        if (mysqli_num_rows($check_result) === 0) {
            $error = "Selected student is not enrolled in this course.";
            mysqli_stmt_close($check_stmt);
        } else {
            mysqli_stmt_close($check_stmt);

            $sql = "INSERT INTO results
                    (student_id, course_id, subject, marks, total_marks, grade, exam_type)
                    VALUES (?, ?, ?, ?, ?, ?, ?)";

            $stmt = mysqli_prepare($conn, $sql);

            if ($stmt) {
                mysqli_stmt_bind_param(
                    $stmt,
                    "iisiiss",
                    $student_id,
                    $course_id,
                    $subject,
                    $marks,
                    $total_marks,
                    $grade,
                    $exam_type
                );

                if (mysqli_stmt_execute($stmt)) {
                    mysqli_stmt_close($stmt);
                    header("Location: results.php?success=1");
                    exit;
                } else {
                    $error = "Result could not be uploaded. Please try again.";
                    mysqli_stmt_close($stmt);
                }
            } else {
                $error = "Database error. Please try again.";
            }
        }
    }
}

if (isset($_GET["success"])) {
    $success = "Result uploaded successfully.";
}

// Fetch dropdown options
$students_query = "SELECT id, full_name FROM students ORDER BY full_name ASC";
$students_result = mysqli_query($conn, $students_query);

$courses_query = "SELECT id, course_name FROM courses ORDER BY course_name ASC";
$courses_result = mysqli_query($conn, $courses_query);

// Fetch recent results
$results_query = "SELECT
                    results.id,
                    students.full_name,
                    courses.course_name,
                    results.subject,
                    results.marks,
                    results.total_marks,
                    results.grade,
                    results.exam_type
                  FROM results
                  LEFT JOIN students
                    ON results.student_id = students.id
                  LEFT JOIN courses
                    ON results.course_id = courses.id
                  ORDER BY results.id DESC
                  LIMIT 10";

$results = mysqli_query($conn, $results_query);

if (!$results) {
    die("Database Error: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Results</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
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
            border: none !important;
            border-radius: 14px !important;
            overflow: hidden;
        }

        .card-body h4 {
            color: var(--green);
            font-weight: 700;
        }

        .form-label {
            color: var(--green);
            font-weight: 600;
        }

        .form-control,
        .form-select {
            border-radius: 8px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--green);
            box-shadow: 0 0 0 0.2rem rgba(1, 62, 55, 0.15);
        }

        .btn-primary {
            background-color: var(--green) !important;
            border-color: var(--green) !important;
            border-radius: 8px !important;
            font-weight: 600;
        }

        .btn-primary:hover {
            background-color: var(--green-dark) !important;
            border-color: var(--green-dark) !important;
        }

        .table-dark {
            --bs-table-bg: var(--green);
            --bs-table-color: #fff;
        }

        .table thead th {
            font-weight: 600;
            letter-spacing: 0.4px;
            border: none;
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(255, 239, 179, 0.15);
        }

        .alert-info {
            background-color: var(--butter);
            border: none;
            border-radius: 10px;
            color: var(--text-dark);
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

        <div class="col-md-3 col-lg-2 bg-dark text-white min-vh-100 p-3">
            <h4 class="text-center mb-4">Admin Panel</h4>

            <div class="nav flex-column">
                <a href="dashboard.php" class="nav-link text-white mb-2">Dashboard</a>
                <a href="students.php" class="nav-link text-white mb-2">Manage Students</a>
                <a href="courses.php" class="nav-link text-white mb-2">Manage Courses</a>
                <a href="assignments.php" class="nav-link text-white mb-2">Manage Assignments</a>
                <a href="results.php" class="nav-link text-white mb-2 fw-bold active">Upload Results</a>
                <a href="notices.php" class="nav-link text-white mb-2">Post Notice</a>
                <a href="logout.php" class="nav-link text-danger">Logout</a>
            </div>
        </div>

        <div class="col-md-9 col-lg-10 p-4">

            <h2 class="mb-4">Upload Results</h2>

            <?php if ($error !== ""): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if ($success !== ""): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($success); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <h4 class="mb-3">Add Result</h4>

                    <form method="POST" action="">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="student_id">Student</label>
                                <select id="student_id" name="student_id" class="form-select" required>
                                    <option value="">Select Student</option>
                                    <?php if ($students_result): ?>
                                        <?php while ($student = mysqli_fetch_assoc($students_result)): ?>
                                            <option value="<?php echo $student["id"]; ?>" <?php echo ($student_id_val == $student["id"]) ? "selected" : ""; ?>>
                                                <?php echo htmlspecialchars($student["full_name"]); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="course_id">Course</label>
                                <select id="course_id" name="course_id" class="form-select" required>
                                    <option value="">Select Course</option>
                                    <?php if ($courses_result): ?>
                                        <?php while ($course = mysqli_fetch_assoc($courses_result)): ?>
                                            <option value="<?php echo $course["id"]; ?>" <?php echo ($course_id_val == $course["id"]) ? "selected" : ""; ?>>
                                                <?php echo htmlspecialchars($course["course_name"]); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="subject">Subject</label>
                                <input type="text" id="subject" name="subject" class="form-control" value="<?php echo htmlspecialchars($subject_val); ?>" required>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label class="form-label" for="marks">Obtained Marks</label>
                                <input type="number" id="marks" name="marks" class="form-control" min="0" value="<?php echo htmlspecialchars($marks_val); ?>" required>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label class="form-label" for="total_marks">Total Marks</label>
                                <input type="number" id="total_marks" name="total_marks" class="form-control" min="1" value="<?php echo htmlspecialchars($total_marks_val); ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="grade">Grade</label>
                                <input type="text" id="grade" name="grade" class="form-control" placeholder="e.g. A+, B, C" value="<?php echo htmlspecialchars($grade_val); ?>" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label" for="exam_type">Exam Type</label>
                                <input type="text" id="exam_type" name="exam_type" class="form-control" placeholder="e.g. Midterm, Final" value="<?php echo htmlspecialchars($exam_type_val); ?>" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">Upload Result</button>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <h4 class="mb-3">Recently Uploaded Results</h4>

                    <?php if ($results && mysqli_num_rows($results) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-striped align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Student</th>
                                        <th>Course</th>
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
                                            <td><?php echo htmlspecialchars($row["full_name"] ?? "N/A"); ?></td>
                                            <td><?php echo htmlspecialchars($row["course_name"] ?? "N/A"); ?></td>
                                            <td><?php echo htmlspecialchars($row["subject"]); ?></td>
                                            <td><?php echo htmlspecialchars($row["marks"]); ?></td>
                                            <td><?php echo htmlspecialchars($row["total_marks"]); ?></td>
                                            <td><span class="badge bg-secondary"><?php echo htmlspecialchars($row["grade"]); ?></span></td>
                                            <td><?php echo htmlspecialchars($row["exam_type"]); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mb-0">No results uploaded yet.</div>
                    <?php endif; ?>

                </div>
            </div>

        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>