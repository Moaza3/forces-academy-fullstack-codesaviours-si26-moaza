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
        /* ============================================
           Theme: Butter & Green
           Butter: #FFEFB3   |   Green: #013E37
           Only visual styling — no structure/logic changed.
        ============================================ */

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

        /* Sidebar */
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

        .bg-dark .nav-link.text-danger {
            color: #ff6b6b !important;
        }

        .bg-dark .nav-link.text-danger:hover {
            background-color: rgba(255, 107, 107, 0.15);
            color: #ff8787 !important;
        }

        /* Heading */
        h2 {
            color: var(--green);
            font-weight: 700;
        }

        hr {
            border-top: 2px solid var(--butter);
            opacity: 1;
        }

        .badge.bg-primary {
            background-color: var(--green) !important;
        }

        /* Timetable table — recolored from blue to green/butter theme */
        .card-box { background: white; border-radius: 14px; padding: 20px; border: 1px solid #e3e6f0; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; min-width: 800px; }
        th, td { border: 1px solid #e2e8f0; padding: 12px; text-align: center; vertical-align: top; }
        th { background: var(--green); color: #fff; font-size: 14px; font-weight: 600; }
        .time-col { background: #f8fafc; font-weight: 600; color: var(--green-dark); width: 140px; vertical-align: middle; }
        .slot-card { background: var(--butter); border-left: 4px solid var(--green); padding: 8px 10px; border-radius: 6px; text-align: left; }
        .slot-subject { font-weight: 600; color: var(--green-dark); font-size: 14px; }
        .slot-teacher { font-size: 12px; color: #475569; margin-top: 4px; }
        .empty-cell { color: #adb5bd; font-size: 13px; }
    </style>
</head>
<body class="bg-light">

    <div class="bg-dark text-white p-3 vh-100" style="width: 250px; position: fixed; left: 0; top: 0;">
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

    <div class="p-4" style="margin-left: 250px; width: calc(100% - 250px);">
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