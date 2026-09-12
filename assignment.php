<?php
session_start();

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit;
}

require_once "config/db.php";

$student_id = (int)$_SESSION['student_id'];

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
    <title>Assignments | Student Portal</title>
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
        }

        .bg-dark .nav-link:hover {
            background-color: rgba(255, 239, 179, 0.15);
            color: var(--butter) !important;
            padding-left: 20px;
        }

        .bg-dark .nav-link.active {
            background-color: var(--butter) !important;
            color: var(--green-dark) !important;
            font-weight: 400 !important;
        }

        .bg-dark .nav-link.active:hover {
            background-color: var(--butter) !important;
            color: var(--green-dark) !important;
        }

        .bg-dark .nav-link:focus,
        .bg-dark .nav-link:active {
            color: #fff !important;
        }

        .bg-dark .nav-link.active:focus,
        .bg-dark .nav-link.active:active {
            color: var(--green-dark) !important;
        }

        .bg-dark .nav-link.text-danger {
            color: #ff6b6b !important;
        }

        .bg-dark .nav-link.text-danger:hover {
            background-color: rgba(255, 107, 107, 0.15);
            color: #ff8787 !important;
        }

        h2 {
            color: var(--green);
            font-weight: 700;
            position: relative;
            padding-bottom: 10px;
        }

        h2::after {
            content: "";
            position: absolute;
            left: 0;
            bottom: 0;
            width: 60px;
            height: 4px;
            background-color: var(--butter);
            border-radius: 2px;
        }

        h4 {
            color: var(--green);
            font-weight: 700;
        }

        hr {
            border-top: 2px solid var(--butter);
            opacity: 1;
        }

        .card {
            border-radius: 14px !important;
            overflow: hidden;
        }

        .form-label {
            color: var(--green);
            font-weight: 600;
        }

        .form-control {
            border-radius: 8px;
        }

        .form-control:focus {
            border-color: var(--green);
            box-shadow: 0 0 0 0.2rem rgba(1, 62, 55, 0.15);
        }

        .btn {
            border-radius: 8px !important;
            font-weight: 600;
        }

        .btn-primary {
            background-color: var(--green) !important;
            border-color: var(--green) !important;
        }

        .btn-primary:hover {
            background-color: var(--green-dark) !important;
            border-color: var(--green-dark) !important;
        }

        .btn-success {
            background-color: var(--green) !important;
            border-color: var(--green) !important;
        }

        .btn-success:hover {
            background-color: var(--green-dark) !important;
            border-color: var(--green-dark) !important;
        }

        .btn-warning {
            background-color: var(--butter) !important;
            border-color: var(--butter) !important;
            color: var(--text-dark) !important;
        }

        .btn-warning:hover {
            background-color: #ffe58f !important;
            border-color: #ffe58f !important;
        }

        .btn-info {
            background-color: var(--butter) !important;
            border-color: var(--butter) !important;
            color: var(--text-dark) !important;
        }

        .btn-info:hover {
            background-color: #ffe58f !important;
            border-color: #ffe58f !important;
            color: var(--text-dark) !important;
        }

        .btn-outline-secondary {
            border-radius: 8px !important;
        }

        .alert-success {
            border-radius: 10px;
            border-left: 4px solid var(--green);
        }

        .alert-danger {
            border-radius: 10px;
        }

        .alert-info {
            background-color: var(--butter);
            border: none;
            border-radius: 12px;
            color: var(--text-dark);
        }

        .alert-info .alert-heading {
            color: var(--green-dark);
            font-weight: 700;
        }

        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--green);
            border-radius: 4px;
        }

        .card {
            border-top: 4px solid var(--green) !important;
            transition: transform 0.25s ease, box-shadow 0.25s ease;
        }

        .card:hover {
            transform: translateY(-6px);
            box-shadow: 0 10px 22px rgba(1, 62, 55, 0.15) !important;
        }

        .card-title {
            color: var(--green);
            font-weight: 700;
        }

        .card-body strong {
            color: var(--green-dark);
        }

        .card-text {
            color: #555;
        }

        .badge.bg-success {
            background-color: var(--green) !important;
            font-size: 0.8rem;
            padding: 7px 12px;
            border-radius: 6px;
        }

        .mobile-toggle-btn {
            display: none;
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 1050;
            background: var(--green);
            color: #fff;
            border: none;
            border-radius: 8px;
            width: 44px;
            height: 44px;
            font-size: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.25);
            cursor: pointer;
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1030;
        }

        .sidebar-overlay.show {
            display: block;
        }

        @media (max-width: 991px) {
            .mobile-toggle-btn {
                display: block;
            }

            #appSidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
                z-index: 1040;
            }

            #appSidebar.sidebar-open {
                transform: translateX(0);
            }

            #mainContent {
                margin-left: 0 !important;
                width: 100% !important;
                padding-top: 75px !important;
            }
        }
    </style>

</head>
<body class="bg-light">

    <button class="mobile-toggle-btn" onclick="document.getElementById('appSidebar').classList.toggle('sidebar-open'); document.getElementById('sidebarOverlay').classList.toggle('show');">&#9776;</button>
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="document.getElementById('appSidebar').classList.remove('sidebar-open'); this.classList.remove('show');"></div>

    <div class="bg-dark text-white p-3 vh-100" id="appSidebar" style="width: 250px; position: fixed; left: 0; top: 0;">
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
                <a href="assignment.php" class="nav-link text-white fw-normal active">Assignments</a>
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

    <div class="p-4" id="mainContent" style="margin-left: 250px; width: calc(100% - 250px);">
        <h2 class="mb-4">Assignments</h2>

        <div class="row">
            <?php if (mysqli_num_rows($result) > 0): ?>

                <?php while ($assignment = mysqli_fetch_assoc($result)): ?>

                    <?php
                    $assignment_id = (int) $assignment['id'];

                    $check_stmt = mysqli_prepare($conn, "SELECT id FROM submissions WHERE assignment_id = ? AND student_id = ? LIMIT 1");
                    mysqli_stmt_bind_param($check_stmt, "ii", $assignment_id, $student_id);
                    mysqli_stmt_execute($check_stmt);
                    $check_result = mysqli_stmt_get_result($check_stmt);

                    $already_submitted = mysqli_num_rows($check_result) > 0;
                    ?>

                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card shadow-sm border-0 h-100">
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
                    <div class="alert alert-info border-0 shadow-sm">
                        <h5>No Assignment Available</h5>
                        <p class="mb-0">There are no assignments available at the moment.</p>
                    </div>
                </div>

            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>