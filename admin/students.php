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
            <li><a href="students.php" class="active">Manage Students</a></li>
            <li><a href="courses.php">Manage Courses</a></li>
            <li><a href="assignments.php">Manage Assignments</a></li>
            <li><a href="results.php">Upload Results</a></li>
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
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover align-middle">
                        <thead class="table-dark">
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
                                    class="text-center py-4"
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