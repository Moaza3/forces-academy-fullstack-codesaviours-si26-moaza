<?php
session_start();
require_once "../config/db.php";

if (!isset($_SESSION["admin_id"]) || $_SESSION["admin_role"] !== "admin") {
    header("Location: login.php");
    exit;
}

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $student_id  = (int) $_POST["student_id"];
    $course_id   = (int) $_POST["course_id"];
    $subject     = trim($_POST["subject"]);
    $marks       = (int) $_POST["marks"];
    $total_marks = (int) $_POST["total_marks"];
    $grade       = trim($_POST["grade"]);
    $exam_type   = trim($_POST["exam_type"]);

    if (
        $student_id <= 0 ||
        $course_id <= 0 ||
        empty($subject) ||
        $total_marks <= 0 ||
        empty($grade) ||
        empty($exam_type)
    ) {
        $error = "All fields are required.";
    } else {
        $sql = "INSERT INTO results (student_id, course_id, subject, marks, total_marks, grade, exam_type) VALUES (?, ?, ?, ?, ?, ?, ?)";
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
                $error = "Result could not be uploaded.";
                mysqli_stmt_close($stmt);
            }
        } else {
            $error = "Database query preparation failed.";
        }
    }
}

if (isset($_GET["success"])) {
    $success = "Result uploaded successfully.";
}

$students_query = "SELECT id, full_name FROM students ORDER BY full_name ASC";
$students_result = mysqli_query($conn, $students_query);

$courses_query = "SELECT id, course_name FROM courses ORDER BY course_name ASC";
$courses_result = mysqli_query($conn, $courses_query);

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
                  LEFT JOIN students ON results.student_id = students.id
                  LEFT JOIN courses ON results.course_id = courses.id
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
            color: #ffffff !important;
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
            border-radius: 16px !important;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
            overflow: hidden;
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
            padding: 10px 20px;
        }

        .btn-primary:hover {
            background-color: var(--green-dark) !important;
            border-color: var(--green-dark) !important;
        }

        .table thead {
            background-color: var(--green);
            color: white;
        }

        .table thead th {
            font-weight: 600;
            letter-spacing: 0.4px;
            border: none;
            padding: 12px;
        }

        .table tbody td {
            padding: 12px;
            vertical-align: middle;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(255, 239, 179, 0.25);
        }

        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--green);
            border-radius: 4px;
        }

        @media (max-width: 768px) {
            #sidebarMenu {
                display: none;
                position: absolute;
                z-index: 1000;
                width: 100%;
                left: 0;
            }
            #sidebarMenu.show {
                display: block !important;
            }
        }
    </style>
</head>
<body class="bg-light">

<div class="container-fluid">
    <div class="row">

        <div class="col-md-3 col-lg-2 bg-dark text-white min-vh-100 p-3" id="sidebarMenu">
            <h4 class="text-center mb-4">Admin Panel</h4>

            <div class="nav flex-column">
                <a href="dashboard.php" class="nav-link mb-2">Dashboard</a>
                <a href="students.php" class="nav-link mb-2">Manage Students</a>
                <a href="courses.php" class="nav-link mb-2">Manage Courses</a>
                <a href="assignments.php" class="nav-link mb-2">Manage Assignments</a>
                <a href="results.php" class="nav-link mb-2 active" style="background-color: rgba(255, 239, 179, 0.15); color: var(--butter) !important;">Upload Results</a>
                <a href="notices.php" class="nav-link mb-2">Post Notice</a>
                <a href="fees.php" class="nav-link mb-2">Manage Fees</a>
                <a href="logout.php" class="nav-link text-danger">Logout</a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 col-lg-10 p-4">

            <!-- Mobile Hamburger Toggle Button -->
            <div class="d-md-none mb-3">
                <button class="btn text-white px-3 py-2 rounded-3 shadow-sm" id="mobileMenuBtn" style="background-color: var(--green);">
                    ☰ Menu
                </button>
            </div>

            <h2 class="mb-4">Upload Results</h2>

            <?php if ($error !== ""): ?>
                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success !== ""): ?>
                <div class="alert alert-success">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <!-- Add Result Form -->
            <div class="card shadow-sm mb-4">
                <div class="card-body p-4">
                    <h5 class="mb-4 text-secondary" style="font-size: 1.25rem; font-weight: 600;">Add Result</h5>
                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Student</label>
                                <select name="student_id" class="form-select" required>
                                    <option value="">Select Student</option>
                                    <?php while ($student = mysqli_fetch_assoc($students_result)): ?>
                                        <option value="<?php echo $student["id"]; ?>">
                                            <?php echo htmlspecialchars($student["full_name"]); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Course</label>
                                <select name="course_id" class="form-select" required>
                                    <option value="">Select Course</option>
                                    <?php while ($course = mysqli_fetch_assoc($courses_result)): ?>
                                        <option value="<?php echo $course["id"]; ?>">
                                            <?php echo htmlspecialchars($course["course_name"]); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Subject</label>
                                <input type="text" name="subject" class="form-control" placeholder="Enter subject name" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label">Exam Type</label>
                                <input type="text" name="exam_type" class="form-control" placeholder="e.g. Midterm, Final" required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Marks Obtained</label>
                                <input type="number" name="marks" class="form-control" min="0" placeholder="e.g. 85" required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Total Marks</label>
                                <input type="number" name="total_marks" class="form-control" min="1" placeholder="e.g. 100" required>
                            </div>

                            <div class="col-md-4 mb-3">
                                <label class="form-label">Grade</label>
                                <input type="text" name="grade" class="form-control" placeholder="e.g. A+" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary mt-2">Upload Result</button>
                    </form>
                </div>
            </div>

            <!-- Recently Uploaded Results Table -->
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h5 class="mb-4 text-secondary" style="font-size: 1.25rem; font-weight: 600;">Recently Uploaded Results</h5>
                    <?php if (mysqli_num_rows($results) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead>
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
                                            <td><?php echo htmlspecialchars($row["full_name"]); ?></td>
                                            <td><?php echo htmlspecialchars($row["course_name"]); ?></td>
                                            <td><?php echo htmlspecialchars($row["subject"]); ?></td>
                                            <td><?php echo htmlspecialchars($row["marks"]); ?></td>
                                            <td><?php echo htmlspecialchars($row["total_marks"]); ?></td>
                                            <td><span class="badge bg-success"><?php echo htmlspecialchars($row["grade"]); ?></span></td>
                                            <td><?php echo htmlspecialchars($row["exam_type"]); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mb-0 text-center py-3">
                            No results uploaded yet.
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>

    </div>
</div>

<script>
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const sidebarMenu = document.getElementById('sidebarMenu');

    if (mobileMenuBtn && sidebarMenu) {
        mobileMenuBtn.addEventListener('click', function() {
            sidebarMenu.classList.toggle('show');
        });
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>