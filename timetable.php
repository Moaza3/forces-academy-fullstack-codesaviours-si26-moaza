<?php
session_start();

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'config/db.php';

$student_id = (int)$_SESSION['student_id'];

$stmt = mysqli_prepare($conn, "SELECT class FROM students WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);
$student_res = mysqli_stmt_get_result($stmt);
$student_data = mysqli_fetch_assoc($student_res);

$student_class = $student_data['class'] ?? '';

$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

$timetable_data = [];
$time_slots = [];

if (!empty($student_class)) {
    $query = "SELECT * FROM timetable WHERE class = ? ORDER BY time_slot ASC";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $student_class);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    while ($row = mysqli_fetch_assoc($result)) {
        $slot = $row['time_slot'];
        $day = $row['day'];
        
        if (!in_array($slot, $time_slots)) {
            $time_slots[] = $slot;
        }
        $timetable_data[$slot][$day] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Timetable | Student Portal</title>
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

        .badge.bg-primary {
            background-color: var(--green) !important;
        }

        .card-box {
            background: white;
            border-radius: 14px;
            padding: 20px;
            border: 1px solid #e3e6f0;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 800px;
        }

        th, td {
            border: 1px solid #e2e8f0;
            padding: 12px;
            text-align: center;
            vertical-align: top;
        }

        th {
            background: var(--green);
            color: #fff;
            font-size: 14px;
            font-weight: 600;
        }

        .time-col {
            background: #f8fafc;
            font-weight: 600;
            color: var(--green-dark);
            width: 140px;
            vertical-align: middle;
        }

        .slot-card {
            background: var(--butter);
            border-left: 4px solid var(--green);
            padding: 8px 10px;
            border-radius: 6px;
            text-align: left;
        }

        .slot-subject {
            font-weight: 600;
            color: var(--green-dark);
            font-size: 14px;
        }

        .slot-teacher {
            font-size: 12px;
            color: #475569;
            margin-top: 4px;
        }

        .empty-cell {
            color: #adb5bd;
            font-size: 13px;
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
                <a href="timetable.php" class="nav-link text-white fw-normal active">Timetable</a>
            </li>
            <li class="nav-item mb-2">
                <a href="assignment.php" class="nav-link text-white fw-normal">Assignments</a>
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
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h2>Weekly Timetable</h2>
                <p class="text-muted mb-0">Class Schedule for <?= htmlspecialchars($student_class ?: 'N/A'); ?></p>
            </div>
            <span class="badge bg-primary fs-6">Class: <?= htmlspecialchars($student_class ?: 'N/A'); ?></span>
        </div>
        <hr>

        <div class="card-box shadow-sm mt-4">
            <?php if (!empty($time_slots)): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Time Slot</th>
                            <?php foreach ($days as $day): ?>
                                <th><?= $day; ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($time_slots as $slot): ?>
                            <tr>
                                <td class="time-col"><?= htmlspecialchars($slot); ?></td>
                                <?php foreach ($days as $day): ?>
                                    <td>
                                        <?php if (isset($timetable_data[$slot][$day])): ?>
                                            <div class="slot-card">
                                                <div class="slot-subject"><?= htmlspecialchars($timetable_data[$slot][$day]['subject']); ?></div>
                                                <div class="slot-teacher">👨‍🏫 <?= htmlspecialchars($timetable_data[$slot][$day]['teacher']); ?></div>
                                            </div>
                                        <?php else: ?>
                                            <span class="empty-cell">-</span>
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p class="text-center text-muted my-4">No timetable entries found for your class.</p>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>