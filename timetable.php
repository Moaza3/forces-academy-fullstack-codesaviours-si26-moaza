<?php
require_once 'config/db.php';
session_start();

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit;
}

$student_id = $_SESSION['student_id'];

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
    $query = "SELECT * FROM timetable WHERE class = ?";
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
    <title>My Timetable | Forces Academy LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background: #f4f7fb; color: #1e293b; }

        .card-box { background: white; border-radius: 18px; padding: 25px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); overflow-x: auto; }
        
        table { width: 100%; border-collapse: collapse; min-width: 800px; }
        th, td { border: 1px solid #e2e8f0; padding: 15px; text-align: center; vertical-align: top; }
        th { background: #f1f5fb; color: #123b70; font-size: 14px; }
        .time-col { background: #f8fafc; font-weight: bold; color: #334155; width: 140px; }
        
        .slot-card { background: #eff6ff; border-left: 4px solid #2563eb; padding: 10px; border-radius: 6px; text-align: left; }
        .slot-subject { font-weight: bold; color: #1e40af; font-size: 14px; }
        .slot-teacher { font-size: 12px; color: #475569; margin-top: 4px; }
        .empty-cell { color: #cbd5e1; font-size: 13px; }
    </style>
</head>
<body>

    <div class="bg-dark text-white p-3 vh-100" style="width: 250px; position: fixed; left: 0; top: 0;">
        <h4 class="text-center mb-4">Student Portal</h4>
        <ul class="nav flex-column">
            <li class="nav-item mb-2">
                <a href="dashboard.php" class="nav-link text-white">Dashboard</a>
            </li>
            <li class="nav-item mb-2">
                <a href="courses.php" class="nav-link text-white">My Courses</a>
            </li>
            <li class="nav-item mb-2">
                <a href="timetable.php" class="nav-link text-white">Timetable</a>
            </li>
            <li class="nav-item mb-2">
                <a href="assignment.php" class="nav-link text-white">Assignments</a>
            </li>
            <li class="nav-item mb-2">
                <a href="results.php" class="nav-link text-white">My Results</a>
            </li>
            <li class="nav-item mb-2">
                <a href="notices.php" class="nav-link text-white">Notices</a>
            </li>
            <li class="nav-item mt-3">
                <a href="logout.php" class="nav-link text-danger">Logout</a>
            </li>
        </ul>
    </div>

    <div class="p-4" style="margin-left: 250px; width: calc(100% - 250px);">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h2>Weekly Timetable Grid</h2>
                <p class="text-muted">Class Schedule for <?= htmlspecialchars($student_class); ?></p>
            </div>
            <span class="badge bg-primary fs-6">Class: <?= htmlspecialchars($student_class); ?></span>
        </div>
        <hr>

        <div class="card-box mt-4">
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
                <p style="text-align: center; color: #64748b; padding: 30px;">No timetable entries found for your class.</p>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>