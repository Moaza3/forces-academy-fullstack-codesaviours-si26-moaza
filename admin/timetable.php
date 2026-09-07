<?php
require_once '../config/db.php';
session_start();

// Add timetable entry
if (isset($_POST['add_timetable'])) {

    $class = $_POST['class'];
    $day = $_POST['day'];
    $time_slot = $_POST['time_slot'];
    $subject = $_POST['subject'];
    $teacher = $_POST['teacher'];

    $stmt = mysqli_prepare($conn, "INSERT INTO timetable (`class`, day, time_slot, subject, teacher) VALUES (?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "sssss", $class, $day, $time_slot, $subject, $teacher);
    mysqli_stmt_execute($stmt);

    header("Location: timetable.php");
    exit;
}

// Delete timetable entry
if (isset($_GET['delete'])) {

    $id = intval($_GET['delete']);

    $stmt = mysqli_prepare($conn, "DELETE FROM timetable WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    header("Location: timetable.php");
    exit;
}

// Get all timetable entries
$result = mysqli_query($conn, "SELECT * FROM timetable ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Timetable Management</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
        }

        h1 {
            margin-bottom: 20px;
        }

        form {
            display: grid;
            gap: 10px;
            max-width: 500px;
            margin-bottom: 30px;
        }

        input, select, button {
            padding: 10px;
            font-size: 15px;
        }

        button {
            cursor: pointer;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: left;
        }

        th {
            background: #f2f2f2;
        }

        .delete {
            color: red;
            text-decoration: none;
        }
    </style>
</head>

<body>

<h1>Timetable Management</h1>

<h2>Add Timetable Entry</h2>

<form method="POST">

    <select name="class" required>
        <option value="">Select Class</option>
        <option value="Class 1">Class 1</option>
        <option value="Class 2">Class 2</option>
        <option value="Class 3">Class 3</option>
        <option value="Class 4">Class 4</option>
        <option value="Class 5">Class 5</option>
    </select>

    <select name="day" required>
        <option value="">Select Day</option>
        <option value="Monday">Monday</option>
        <option value="Tuesday">Tuesday</option>
        <option value="Wednesday">Wednesday</option>
        <option value="Thursday">Thursday</option>
        <option value="Friday">Friday</option>
        <option value="Saturday">Saturday</option>
    </select>

    <input type="text" name="time_slot" placeholder="Time Slot (e.g. 10:00 - 11:00)" required>

    <input type="text" name="subject" placeholder="Subject" required>

    <input type="text" name="teacher" placeholder="Teacher Name" required>

    <button type="submit" name="add_timetable">
        Add Timetable
    </button>

</form>

<h2>All Timetable Entries</h2>

<table>
    <tr>
        <th>ID</th>
        <th>Class</th>
        <th>Day</th>
        <th>Time Slot</th>
        <th>Subject</th>
        <th>Teacher</th>
        <th>Action</th>
    </tr>

    <?php while ($row = mysqli_fetch_assoc($result)): ?>

    <tr>
        <td><?= $row['id']; ?></td>
        <td><?= htmlspecialchars($row['class']); ?></td>
        <td><?= htmlspecialchars($row['day']); ?></td>
        <td><?= htmlspecialchars($row['time_slot']); ?></td>
        <td><?= htmlspecialchars($row['subject']); ?></td>
        <td><?= htmlspecialchars($row['teacher']); ?></td>

        <td>
            <a class="delete"
               href="timetable.php?delete=<?= $row['id']; ?>"
               onclick="return confirm('Are you sure you want to delete this entry?');">
                Delete
            </a>
        </td>
    </tr>

    <?php endwhile; ?>

</table>

</body>
</html>