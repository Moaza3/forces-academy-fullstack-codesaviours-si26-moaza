<?php

session_start();

require_once "../config/db.php";

if (!isset($_SESSION["admin_id"]) || $_SESSION["admin_role"] !== "admin") {
    header("Location: login.php");
    exit;
}

$error = "";
$success = "";

$students_query = "SELECT id, full_name FROM students ORDER BY full_name ASC";
$students_result = mysqli_query($conn, $students_query);

$courses_query = "SELECT id, course_name FROM courses ORDER BY course_name ASC";
$courses_result = mysqli_query($conn, $courses_query);

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $student_id = (int) $_POST["student_id"];
    $course_id = (int) $_POST["course_id"];
    $subject = trim($_POST["subject"]);
    $marks = (int) $_POST["marks"];
    $total_marks = (int) $_POST["total_marks"];
    $grade = trim($_POST["grade"]);
    $exam_type = trim($_POST["exam_type"]);

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

        $sql = "INSERT INTO results
                (student_id, course_id, subject, marks, total_marks, grade, exam_type)
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($conn, $sql);

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
    }
}

if (isset($_GET["success"])) {
    $success = "Result uploaded successfully.";
}

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

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Upload Results</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>

<body class="bg-light">

<div class="container-fluid">

    <div class="row">

        <div class="col-md-3 col-lg-2 bg-dark text-white min-vh-100 p-3">

            <h4 class="text-center mb-4">
                Admin Panel
            </h4>

            <div class="nav flex-column">

                <a
                    href="dashboard.php"
                    class="nav-link text-white mb-2"
                >
                    Dashboard
                </a>

                <a
                    href="students.php"
                    class="nav-link text-white mb-2"
                >
                    Manage Students
                </a>

                <a
                    href="courses.php"
                    class="nav-link text-white mb-2"
                >
                    Manage Courses
                </a>

                <a
                    href="assignments.php"
                    class="nav-link text-white mb-2"
                >
                    Manage Assignments
                </a>

                <a
                    href="results.php"
                    class="nav-link text-white mb-2"
                >
                    Upload Results
                </a>

                <a
                    href="notices.php"
                    class="nav-link text-white mb-2"
                >
                    Post Notice
                </a>

                <a
                    href="logout.php"
                    class="nav-link text-danger"
                >
                    Logout
                </a>

            </div>

        </div>

        <div class="col-md-9 col-lg-10 p-4">

            <h2 class="mb-4">
                Upload Results
            </h2>

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

            <div class="card shadow-sm mb-4">

                <div class="card-body">

                    <h4 class="mb-3">
                        Add Result
                    </h4>

                    <form method="POST">

                        <div class="mb-3">

                            <label class="form-label">
                                Student
                            </label>

                            <select
                                name="student_id"
                                class="form-select"
                                required
                            >

                                <option value="">
                                    Select Student
                                </option>

                                <?php while ($student = mysqli_fetch_assoc($students_result)): ?>

                                    <option
                                        value="<?php echo $student["id"]; ?>"
                                    >
                                        <?php
                                        echo htmlspecialchars(
                                            $student["full_name"]
                                        );
                                        ?>
                                    </option>

                                <?php endwhile; ?>

                            </select>

                        </div>

                        <div class="mb-3">

                            <label class="form-label">
                                Course
                            </label>

                            <select
                                name="course_id"
                                class="form-select"
                                required
                            >

                                <option value="">
                                    Select Course
                                </option>

                                <?php while ($course = mysqli_fetch_assoc($courses_result)): ?>

                                    <option
                                        value="<?php echo $course["id"]; ?>"
                                    >
                                        <?php
                                        echo htmlspecialchars(
                                            $course["course_name"]
                                        );
                                        ?>
                                    </option>

                                <?php endwhile; ?>

                            </select>

                        </div>

                        <div class="mb-3">

                            <label class="form-label">
                                Subject
                            </label>

                            <input
                                type="text"
                                name="subject"
                                class="form-control"
                                required
                            >

                        </div>

                        <div class="mb-3">

                            <label class="form-label">
                                Marks
                            </label>

                            <input
                                type="number"
                                name="marks"
                                class="form-control"
                                min="0"
                                required
                            >

                        </div>

                        <div class="mb-3">

                            <label class="form-label">
                                Total Marks
                            </label>

                            <input
                                type="number"
                                name="total_marks"
                                class="form-control"
                                min="1"
                                required
                            >

                        </div>

                        <div class="mb-3">

                            <label class="form-label">
                                Grade
                            </label>

                            <input
                                type="text"
                                name="grade"
                                class="form-control"
                                required
                            >

                        </div>

                        <div class="mb-3">

                            <label class="form-label">
                                Exam Type
                            </label>

                            <input
                                type="text"
                                name="exam_type"
                                class="form-control"
                                placeholder="e.g. Midterm, Final"
                                required
                            >

                        </div>

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            Upload Result
                        </button>

                    </form>

                </div>

            </div>

            <div class="card shadow-sm">

                <div class="card-body">

                    <h4 class="mb-3">
                        Recently Uploaded Results
                    </h4>

                    <?php if (mysqli_num_rows($results) > 0): ?>

                        <div class="table-responsive">

                            <table class="table table-bordered table-striped">

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

                                            <td>
                                                <?php
                                                echo htmlspecialchars(
                                                    $row["full_name"]
                                                );
                                                ?>
                                            </td>

                                            <td>
                                                <?php
                                                echo htmlspecialchars(
                                                    $row["course_name"]
                                                );
                                                ?>
                                            </td>

                                            <td>
                                                <?php
                                                echo htmlspecialchars(
                                                    $row["subject"]
                                                );
                                                ?>
                                            </td>

                                            <td>
                                                <?php
                                                echo htmlspecialchars(
                                                    $row["marks"]
                                                );
                                                ?>
                                            </td>

                                            <td>
                                                <?php
                                                echo htmlspecialchars(
                                                    $row["total_marks"]
                                                );
                                                ?>
                                            </td>

                                            <td>
                                                <?php
                                                echo htmlspecialchars(
                                                    $row["grade"]
                                                );
                                                ?>
                                            </td>

                                            <td>
                                                <?php
                                                echo htmlspecialchars(
                                                    $row["exam_type"]
                                                );
                                                ?>
                                            </td>

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

    </div>

</div>

</body>

</html>