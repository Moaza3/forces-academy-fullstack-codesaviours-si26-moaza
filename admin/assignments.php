<?php
session_start();
include("../config/db.php");

if (!isset($_SESSION["admin_id"]) || $_SESSION["admin_role"] !== "admin") {
    header("Location: login.php");
    exit;
}

if (isset($_POST['add_assignment'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $course_id = (int) $_POST['course_id'];
    $due_date = $_POST['due_date'];

    $sql = "INSERT INTO assignment (title, description, course_id, due_date)
            VALUES ('$title', '$description', $course_id, '$due_date')";

    mysqli_query($conn, $sql);

    header("Location: assignments.php");
    exit();
}

if (isset($_POST['update_assignment'])) {
    $id = (int) $_POST['assignment_id'];
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $course_id = (int) $_POST['course_id'];
    $due_date = $_POST['due_date'];

    $sql = "UPDATE assignment SET
            title='$title',
            description='$description',
            course_id=$course_id,
            due_date='$due_date'
            WHERE id=$id";

    mysqli_query($conn, $sql);

    header("Location: assignments.php");
    exit();
}

if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];

    mysqli_query($conn, "DELETE FROM assignment WHERE id = $id");

    header("Location: assignments.php");
    exit();
}

$edit_data = null;
if (isset($_GET['edit'])) {
    $edit_id = (int) $_GET['edit'];
    $edit_query = mysqli_query($conn, "SELECT * FROM assignment WHERE id = $edit_id");
    $edit_data = mysqli_fetch_assoc($edit_query);
}

$courses = mysqli_query($conn, "SELECT id, course_name FROM courses ORDER BY course_name ASC");

$assignments = mysqli_query($conn, "
    SELECT assignment.*, courses.course_name
    FROM assignment
    LEFT JOIN courses ON assignment.course_id = courses.id
    ORDER BY assignment.id DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Assignments</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --butter: #FFEFB3;
            --green: #013E37;
            --green-dark: #012a25;
        }

        body {
            background-color: #f4f6f5;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
        }

        /* Custom Theme Overrides for Bootstrap Elements */
        .bg-custom-dark {
            background: linear-gradient(180deg, var(--green) 0%, var(--green-dark) 100%) !important;
        }

        .text-butter {
            color: var(--butter) !important;
        }

        .border-butter {
            border-color: var(--butter) !important;
        }

        .sidebar-brand {
            font-weight: 800 !important;
            letter-spacing: 0.5px;
        }

        .nav-link-custom {
            color: #ffffff;
            border-radius: 0.5rem;
            padding: 0.6rem 1rem;
            transition: all 0.2s ease-in-out;
            font-weight: 400;
        }

        .nav-link-custom:hover {
            background-color: rgba(255, 239, 179, 0.15);
            color: var(--butter);
            padding-left: 1.25rem;
        }

        .nav-link-custom.active {
            background-color: rgba(255, 239, 179, 0.2);
            color: var(--butter) !important;
            font-weight: 600;
        }

        .btn-custom-green {
            background-color: var(--green);
            color: #ffffff;
            font-weight: 600;
            border: none;
        }

        .btn-custom-green:hover {
            background-color: var(--green-dark);
            color: #ffffff;
        }

        .custom-heading {
            color: var(--green);
            position: relative;
            padding-bottom: 0.5rem;
        }

        .custom-heading::after {
            content: "";
            position: absolute;
            left: 0;
            bottom: 0;
            width: 60px;
            height: 4px;
            background-color: var(--butter);
            border-radius: 2px;
        }

        .table-custom-header {
            background-color: var(--green) !important;
            color: #ffffff !important;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--green);
            box-shadow: 0 0 0 0.25rem rgba(1, 62, 55, 0.15);
        }
    </style>
</head>
<body>

    <!-- Mobile Top Navigation Bar -->
    <nav class="navbar navbar-dark bg-custom-dark d-md-none p-3 shadow-sm">
        <div class="container-fluid">
            <span class="navbar-brand sidebar-brand text-butter fs-4 m-0">Admin Panel</span>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">

            <!-- Sidebar Navigation -->
            <div class="col-md-3 col-lg-2 bg-custom-dark text-white min-vh-md-100 p-3 collapse d-md-block" id="sidebarMenu">
                <h4 class="text-center sidebar-brand text-butter fs-4 pb-3 mb-4 border-bottom border-secondary border-opacity-25 d-none d-md-block">Admin Panel</h4>

                <div class="nav flex-column gap-1">
                    <a href="dashboard.php" class="nav-link nav-link-custom"><i class="bi bi-speedometer2 me-2"></i>Dashboard</a>
                    <a href="students.php" class="nav-link nav-link-custom"><i class="bi bi-people me-2"></i>Manage Students</a>
                    <a href="courses.php" class="nav-link nav-link-custom"><i class="bi bi-book me-2"></i>Manage Courses</a>
                    <a href="assignments.php" class="nav-link nav-link-custom active"><i class="bi bi-journal-text me-2"></i>Manage Assignments</a>
                    <a href="fees.php" class="nav-link nav-link-custom"><i class="bi bi-cash-stack me-2"></i>Manage Fees</a>
                    <a href="results.php" class="nav-link nav-link-custom"><i class="bi bi-award me-2"></i>Upload Results</a>
                    <a href="notices.php" class="nav-link nav-link-custom"><i class="bi bi-megaphone me-2"></i>Post Notice</a>
                    <a href="logout.php" class="nav-link nav-link-custom text-danger mt-3"><i class="bi bi-box-arrow-right me-2"></i>Logout</a>
                </div>
            </div>

            <!-- Main Content Area -->
            <div class="col-md-9 col-lg-10 p-3 p-md-4">
                <h2 class="custom-heading fw-bold mb-4">Manage Assignments</h2>

                <!-- Form Section -->
                <div class="card border-0 shadow-sm rounded-3 mb-5 border-top border-4 border-success">
                    <div class="card-body p-4">
                        <h4 class="card-title text-success fw-semibold mb-3 fs-5">
                            <?php echo $edit_data ? 'Edit Assignment' : 'Add New Assignment'; ?>
                        </h4>

                        <form method="POST">
                            <?php if ($edit_data) { ?>
                                <input type="hidden" name="assignment_id" value="<?php echo $edit_data['id']; ?>">
                            <?php } ?>

                            <div class="mb-3">
                                <label class="form-label fw-semibold text-success">Title</label>
                                <input type="text" class="form-control" name="title" value="<?php echo $edit_data ? htmlspecialchars($edit_data['title']) : ''; ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold text-success">Description</label>
                                <textarea class="form-control" name="description" rows="4" required><?php echo $edit_data ? htmlspecialchars($edit_data['description']) : ''; ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold text-success">Select Course</label>
                                <select class="form-select" name="course_id" required>
                                    <option value="">-- Select Course --</option>
                                    <?php
                                    mysqli_data_seek($courses, 0);
                                    while ($course = mysqli_fetch_assoc($courses)) {
                                        $selected = ($edit_data && $edit_data['course_id'] == $course['id']) ? 'selected' : '';
                                    ?>
                                        <option value="<?php echo $course['id']; ?>" <?php echo $selected; ?>>
                                            <?php echo htmlspecialchars($course['course_name']); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-semibold text-success">Due Date</label>
                                <input type="date" class="form-control" name="due_date" value="<?php echo $edit_data ? $edit_data['due_date'] : ''; ?>" required>
                            </div>

                            <div class="d-flex gap-2">
                                <?php if ($edit_data) { ?>
                                    <button type="submit" name="update_assignment" class="btn btn-custom-green px-4 py-2">Update Assignment</button>
                                    <a href="assignments.php" class="btn btn-secondary px-4 py-2">Cancel</a>
                                <?php } else { ?>
                                    <button type="submit" name="add_assignment" class="btn btn-custom-green px-4 py-2">Add Assignment</button>
                                <?php } ?>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Table Section -->
                <h4 class="text-success fw-semibold mb-3 fs-5">All Assignments</h4>

                <div class="card border-0 shadow-sm rounded-3 overflow-hidden border-top border-4 border-warning">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="table-custom-header py-3 px-3">ID</th>
                                    <th class="table-custom-header py-3 px-3">Title</th>
                                    <th class="table-custom-header py-3 px-3">Description</th>
                                    <th class="table-custom-header py-3 px-3">Course</th>
                                    <th class="table-custom-header py-3 px-3">Due Date</th>
                                    <th class="table-custom-header py-3 px-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($assignment = mysqli_fetch_assoc($assignments)) { ?>
                                    <tr>
                                        <td class="px-3"><?php echo $assignment['id']; ?></td>
                                        <td class="px-3 fw-medium"><?php echo htmlspecialchars($assignment['title']); ?></td>
                                        <td class="px-3 text-muted"><?php echo htmlspecialchars($assignment['description']); ?></td>
                                        <td class="px-3"><?php echo htmlspecialchars($assignment['course_name']); ?></td>
                                        <td class="px-3 text-nowrap"><?php echo htmlspecialchars($assignment['due_date']); ?></td>
                                        <td class="px-3 text-nowrap">
                                            <a class="btn btn-sm btn-outline-success me-1" href="assignments.php?edit=<?php echo $assignment['id']; ?>">
                                                <i class="bi bi-pencil-square"></i> Edit
                                            </a>
                                            <a class="btn btn-sm btn-outline-danger" href="assignments.php?delete=<?php echo $assignment['id']; ?>" onclick="return confirm('Are you sure you want to delete this assignment?');">
                                                <i class="bi bi-trash"></i> Delete
                                            </a>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>