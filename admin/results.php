<?php
session_start();
require_once "../config/db.php";

if (!isset($_SESSION["admin_id"]) || $_SESSION["admin_role"] !== "admin") {
    header("Location: login.php");
    exit;
}

$error = "";
$success = "";

// Handle form submission
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

// Fetch students for dropdown
$students_query = "SELECT id, full_name FROM students ORDER BY full_name ASC";
$students_result = mysqli_query($conn, $students_query);

// Fetch courses for dropdown
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

        body {
            background-color: #f4f6f5 !important;
            font-family: 'Segoe UI', 'Poppins', sans-serif;
            margin: 0;
            display: flex;
        }

        /* Sidebar Styling */
        .sidebar {
            width: 260px;
            background: var(--green);
            color: #ffffff;
            min-height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            display: flex;
            flex-direction: column;
            box-shadow: 4px 0 10px rgba(0, 0, 0, 0.05);
            z-index: 100;
        }

        .sidebar .sidebar-brand {
            padding: 25px 20px;
            font-size: 20px;
            font-weight: 700;
            color: var(--butter);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .sidebar-nav {
            list-style: none;
            padding: 20px 0;
            margin: 0;
            flex-grow: 1;
        }

        .sidebar-nav li a {
            display: block;
            padding: 12px 25px;
            color: #ffffff;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.2s ease;
        }

        .sidebar-nav li a:hover,
        .sidebar-nav li a.active {
            background-color: rgba(255, 239, 179, 0.15);
            color: var(--butter);
            border-left: 4px solid var(--butter);
        }

        .sidebar-nav li a.text-danger:hover {
            background-color: rgba(255, 107, 107, 0.15);
            color: #ff8787 !important;
            border-left-color: #ff8787;
        }

        /* Main Content Area */
        .main-content {
            margin-left: 260px;
            flex-grow: 1;
            padding: 35px;
            width: calc(100% - 260px);
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
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
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
            padding: 10px 20px;
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
            letter-speech: 0.4px;
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

        /* Mobile & Tablet Responsive Styles */
        @media (max-width: 991px) {
            body {
                flex-direction: column;
            }
            .sidebar {
                width: 100%;
                min-height: auto;
                position: relative;
                display: none;
            }
            .sidebar.show {
                display: flex;
            }
            .main-content {
                margin-left: 0;
                width: 100%;
                padding: 20px;
            }
        }
    </style>
</head>
<body>

    <!-- Sidebar Menu -->
    <div class="sidebar" id="sidebarMenu">
        <div class="sidebar-brand">Admin Panel</div>
        <ul class="sidebar-nav">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="students.php">Manage Students</a></li>
            <li><a href="courses.php">Manage Courses</a></li>
            <li><a href="assignments.php">Manage Assignments</a></li>
            <li><a href="results.php" class="active">Upload Results</a></li>
            <li><a href="timetable.php">Timetable</a></li>
            <li><a href="notices.php">Post Notice</a></li>
            <li><a href="fees.php">Manage Fees</a></li>
            <li><a href="logout.php" class="text-danger">Logout</a></li>
        </ul>
    </div>

    <!-- Main Content Area -->
    <div class="main-content">

        <!-- Mobile Hamburger Toggle Button -->
        <div class="d-lg-none mb-3">
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
                <h4 class="mb-3">Add Result</h4>
                <form method="POST">
                    <div class="mb-3">
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

                    <div class="mb-3">
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

                    <div class="mb-3">
                        <label class="form-label">Subject</label>
                        <input type="text" name="subject" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Marks</label>
                        <input type="number" name="marks" class="form-control" min="0" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Total Marks</label>
                        <input type="number" name="total_marks" class="form-control" min="1" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Grade</label>
                        <input type="text" name="grade" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Exam Type</label>
                        <input type="text" name="exam_type" class="form-control" placeholder="e.g. Midterm, Final" required>
                    </div>

                    <button type="submit" class="btn btn-primary">Upload Result</button>
                </form>
            </div>
        </div>

        <!-- Recently Uploaded Results Table -->
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h4 class="mb-3">Recently Uploaded Results</h4>
                <?php if (mysqli_num_rows($results) > 0): ?>
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
                                        <td><?php echo htmlspecialchars($row["full_name"]); ?></td>
                                        <td><?php echo htmlspecialchars($row["course_name"]); ?></td>
                                        <td><?php echo htmlspecialchars($row["subject"]); ?></td>
                                        <td><?php echo htmlspecialchars($row["marks"]); ?></td>
                                        <td><?php echo htmlspecialchars($row["total_marks"]); ?></td>
                                        <td><?php echo htmlspecialchars($row["grade"]); ?></td>
                                        <td><?php echo htmlspecialchars($row["exam_type"]); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="alert alert-info mb-0">
                        No results uploaded yet.
                    </div>
                <?php endif; ?>
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