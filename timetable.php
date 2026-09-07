<?php
require_once 'config/db.php';
session_start();

// Student Login Check
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit;
}

$student_id = $_SESSION['student_id'];

// Get Logged-in Student Class
$stmt = mysqli_prepare($conn, "SELECT class FROM students WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);
$student_res = mysqli_stmt_get_result($stmt);
$student_data = mysqli_fetch_assoc($student_res);

$student_class = $student_data['class'] ?? '';

// Days & Default Time Slots Definitions
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

// Fetch Timetable Entries for Student's Class
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
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: Arial, sans-serif; background: #f4f7fb; color: #1e293b; }
        
        .topbar {
            background: linear-gradient(135deg, #123b70, #2563eb);
            color: white; padding: 18px 40px; display: flex; align-items: center; justify-content: space-between;
        }
        .container { width: 92%; max-width: 1250px; margin: 35px auto; }
        .page-heading h1 { color: #123b70; margin-bottom: 8px; }
        .page-heading p { color: #64748b; font-size: 15px; margin-bottom: 25px; }

        .card { background: white; border-radius: 18px; padding: 25px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); overflow-x: auto; }
        
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

<header class="topbar">
    <h2>🎓 Forces Academy LMS</h2>
    <div>Class: <strong><?= htmlspecialchars($student_class); ?></strong></div>
</header>

<main class="container">
    <div class="page-heading">
        <h1>Weekly Timetable Grid</h1>
        <p>Class Schedule for <?= htmlspecialchars($student_class); ?></p>
    </div>

    <div class="card">
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
</main>

</body>
</html>