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
    $course_name = trim($_POST["course_name"]);
    $description = trim($_POST["description"]);
    $teacher_name = trim($_POST["teacher_name"]);

    if (empty($course_name) || empty($description) || empty($teacher_name)) {
        $error = "All fields are required.";
    } else {
        if (isset($_POST["course_id"]) && $_POST["course_id"] !== "") {
            $course_id = (int) $_POST["course_id"];

            $sql = "UPDATE courses
                    SET course_name = ?, description = ?, teacher_name = ?
                    WHERE id = ?";

            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sssi", $course_name, $description, $teacher_name, $course_id);

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                header("Location: courses.php?updated=1");
                exit;
            } else {
                $error = "Course update failed.";
            }

            mysqli_stmt_close($stmt);
        } else {
            $sql = "INSERT INTO courses
                    (course_name, description, teacher_name)
                    VALUES (?, ?, ?)";

            $stmt = mysqli_prepare($conn, $sql);
            mysqli_stmt_bind_param($stmt, "sss", $course_name, $description, $teacher_name);

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                header("Location: courses.php?added=1");
                exit;
            } else {
                $error = "Course could not be added.";
            }

            mysqli_stmt_close($stmt);
        }
    }
}

if (isset($_GET["delete"])) {
    $course_id = (int) $_GET["delete"];

    $sql = "DELETE FROM courses WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $course_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header("Location: courses.php?deleted=1");
    exit;
}

$edit_course = null;

if (isset($_GET["edit"])) {
    $course_id = (int) $_GET["edit"];

    $sql = "SELECT id, course_name, description, teacher_name
            FROM courses
            WHERE id = ?";

    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $course_id);
    mysqli_stmt_execute($stmt);

    $result_edit = mysqli_stmt_get_result($stmt);
    $edit_course = mysqli_fetch_assoc($result_edit);

    mysqli_stmt_close($stmt);
}

if (isset($_GET["added"])) {
    $success = "Course added successfully.";
}

if (isset($_GET["updated"])) {
    $success = "Course updated successfully.";
}

if (isset($_GET["deleted"])) {
    $success = "Course deleted successfully.";
}

$sql = "SELECT id, course_name, description, teacher_name, created_at
        FROM courses
        ORDER BY created_at DESC";

$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Courses</title>
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

        .btn-success {
            background-color: var(--green) !important;
            border-color: var(--green) !important;
            border-radius: 8px !important;
            font-weight: 600;
        }

        .btn-success:hover {
            background-color: var(--green-dark) !important;
            border-color: var(--green-dark) !important;
        }

        .btn-secondary {
            border-radius: 8px !important;
        }

        .btn-danger {
            border-radius: 8px !important;
            font-weight: 600;
        }

        .btn-sm {
            border-radius: 6px !important;
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

        .table-hover tbody tr:hover {
            background-color: rgba(255, 239, 179, 0.25);
        }

        .alert-success {
            border-radius: 10px;
            border-left: 4px solid var(--green);
        }

        .alert-danger {
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

    <div class="container-fluid">
        <div class="row">

            <div class="col-md-3 col-lg-2 bg-dark text-white min-vh-100 p-3">
                <h4 class="text-center mb-4">Admin Panel</h4>

                <div class="nav flex-column">
                    <a href="dashboard.php" class="nav-link text-white mb-2">Dashboard</a>
                    <a href="students.php" class="nav-link text-white mb-2">Manage Students</a>
                    <a href="courses.php" class="nav-link text-white mb-2">Manage Courses</a>
                    <a href="assignments.php" class="nav-link text-white mb-2">Manage Assignments</a>
                    <a href="results.php" class="nav-link text-white mb-2">Upload Results</a>
                    <a href="notice.php" class="nav-link text-white mb-2">Post Notice</a>
                    <a href="logout.php" class="nav-link text-danger">Logout</a>
                </div>
            </div>

            <div class="col-md-9 col-lg-10 p-4">
                <h2 class="mb-4">Manage Courses</h2>

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
                        <?php if ($edit_course): ?>
                            <h4 class="mb-3">Edit Course</h4>
                        <?php else: ?>
                            <h4 class="mb-3">Add New Course</h4>
                        <?php endif; ?>

                        <form method="POST">
                            <?php if ($edit_course): ?>
                                <input type="hidden" name="course_id" value="<?php echo $edit_course["id"]; ?>">
                            <?php endif; ?>

                            <div class="mb-3">
                                <label class="form-label">Course Name</label>
                                <input
                                    type="text"
                                    name="course_name"
                                    class="form-control"
                                    value="<?php echo $edit_course ? htmlspecialchars($edit_course["course_name"]) : ""; ?>"
                                    required
                                >
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="4" required><?php echo $edit_course ? htmlspecialchars($edit_course["description"]) : ""; ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Teacher Name</label>
                                <input
                                    type="text"
                                    name="teacher_name"
                                    class="form-control"
                                    value="<?php echo $edit_course ? htmlspecialchars($edit_course["teacher_name"]) : ""; ?>"
                                    required
                                >
                            </div>

                            <?php if ($edit_course): ?>
                                <button type="submit" class="btn btn-primary">Update Course</button>
                                <a href="courses.php" class="btn btn-secondary">Cancel</a>
                            <?php else: ?>
                                <button type="submit" class="btn btn-success">Add Course</button>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <h4 class="mb-3">All Courses</h4>

                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Course Name</th>
                                        <th>Description</th>
                                        <th>Teacher Name</th>
                                        <th>Created Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (mysqli_num_rows($result) > 0): ?>
                                        <?php while ($course = mysqli_fetch_assoc($result)): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($course["course_name"]); ?></td>
                                                <td><?php echo htmlspecialchars($course["description"]); ?></td>
                                                <td><?php echo htmlspecialchars($course["teacher_name"]); ?></td>
                                                <td><?php echo htmlspecialchars($course["created_at"]); ?></td>
                                                <td>
                                                    <a href="courses.php?edit=<?php echo $course["id"]; ?>" class="btn btn-sm btn-primary">Edit</a>
                                                    <a
                                                        href="courses.php?delete=<?php echo $course["id"]; ?>"
                                                        class="btn btn-sm btn-danger"
                                                        onclick="return confirm('Are you sure you want to delete this course?');"
                                                    >
                                                        Delete
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" class="text-center">No courses found.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

</body>
</html>