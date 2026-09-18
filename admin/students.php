<?php
session_start();
require_once "../config/db.php";

if (!isset($_SESSION["admin_id"]) || $_SESSION["admin_role"] !== "admin") {
    header("Location: login.php");
    exit;
}

if (isset($_GET["delete"])) {
    $student_id = (int) $_GET["delete"];

    $delete_sql = "DELETE FROM students WHERE id = ?";
    $delete_stmt = mysqli_prepare($conn, $delete_sql);

    if ($delete_stmt) {
        mysqli_stmt_bind_param($delete_stmt, "i", $student_id);
        mysqli_stmt_execute($delete_stmt);
        mysqli_stmt_close($delete_stmt);
    }

    header("Location: students.php");
    exit;
}

$search = "";

if (isset($_GET["search"])) {
    $search = trim($_GET["search"]);
}

if ($search !== "") {
    $sql = "SELECT id, full_name, email, roll_number, class, created_at
            FROM students
            WHERE full_name LIKE ? OR email LIKE ? OR roll_number LIKE ?
            ORDER BY created_at DESC";

    $stmt = mysqli_prepare($conn, $sql);
    $search_value = "%" . $search . "%";

    mysqli_stmt_bind_param(
        $stmt,
        "sss",
        $search_value,
        $search_value,
        $search_value
    );

    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
} else {
    $sql = "SELECT id, full_name, email, roll_number, class, created_at
            FROM students
            ORDER BY created_at DESC";

    $result = mysqli_query($conn, $sql);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Students</title>
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

        /* Top Navbar Styling */
        .navbar-custom {
            background: linear-gradient(135deg, var(--green) 0%, var(--green-dark) 100%);
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .navbar-custom .navbar-brand {
            color: var(--butter) !important;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        .navbar-custom .nav-link {
            color: #ffffff !important;
            font-weight: 500;
            border-radius: 6px;
            padding: 8px 12px !important;
            transition: all 0.2s ease;
        }

        .navbar-custom .nav-link:hover,
        .navbar-custom .nav-link.active {
            background-color: rgba(255, 239, 179, 0.15);
            color: var(--butter) !important;
        }

        .navbar-custom .nav-link.text-danger:hover {
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
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
            overflow: hidden;
        }

        .form-control {
            border-radius: 8px;
        }

        .form-control:focus {
            border-color: var(--green);
            box-shadow: 0 0 0 0.2rem rgba(1, 62, 55, 0.15);
        }

        .btn-primary {
            background-color: var(--green) !important;
            border-color: var(--green) !important;
            border-radius: 0 8px 8px 0 !important;
            font-weight: 600;
        }

        .btn-primary:hover {
            background-color: var(--green-dark) !important;
            border-color: var(--green-dark) !important;
        }

        .btn-secondary {
            border-radius: 0 !important;
        }

        .btn-info {
            background-color: var(--butter) !important;
            border-color: var(--butter) !important;
            color: var(--text-dark) !important;
            border-radius: 6px !important;
            font-weight: 600;
        }

        .btn-info:hover {
            background-color: #ffe58f !important;
            border-color: #ffe58f !important;
        }

        .btn-danger {
            border-radius: 6px !important;
            font-weight: 600;
        }

        .btn-sm {
            border-radius: 6px !important;
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
    </style>
</head>
<body class="bg-light">

    <!-- Top Menu Bar / Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom sticky-top px-3 mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">Admin Panel</a>
            <button class="navbar-toggler bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
                <ul class="navbar-nav align-items-lg-center gap-1">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="students.php">Students</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="courses.php">Courses</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="assignments.php">Assignments</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="results.php">Results</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="notices.php">Notices</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="fees.php">Fees</a>
                    </li>
                    <li class="nav-item ms-lg-2">
                        <a class="nav-link text-danger" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Container -->
    <div class="container mb-5">
        <h2 class="mb-4">Manage Students</h2>

        <form method="GET" action="students.php" class="mb-4">
            <div class="input-group">
                <input
                    type="text"
                    name="search"
                    class="form-control"
                    placeholder="Search by name, email or roll number"
                    value="<?php echo htmlspecialchars($search); ?>"
                >
                <button
                    type="submit"
                    class="btn btn-primary"
                >
                    Search
                </button>
                <?php if ($search !== ""): ?>
                    <a
                        href="students.php"
                        class="btn btn-secondary"
                    >
                        Clear
                    </a>
                <?php endif; ?>
            </div>
        </form>

        <div class="card shadow-sm">
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Roll Number</th>
                                <th>Class</th>
                                <th>Registered Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while ($student = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td>
                                        <?php echo htmlspecialchars($student["full_name"]); ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($student["email"]); ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($student["roll_number"]); ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($student["class"]); ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($student["created_at"]); ?>
                                    </td>
                                    <td>
                                        <a
                                            href="student-details.php?id=<?php echo $student["id"]; ?>"
                                            class="btn btn-sm btn-info text-white"
                                        >
                                            View
                                        </a>
                                        <a
                                            href="students.php?delete=<?php echo $student["id"]; ?>"
                                            class="btn btn-sm btn-danger"
                                            onclick="return confirm('Are you sure you want to delete this student?');"
                                        >
                                            Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td
                                    colspan="6"
                                    class="text-center py-3"
                                >
                                    No students found.
                                </td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>