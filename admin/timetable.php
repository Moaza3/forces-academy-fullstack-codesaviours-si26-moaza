<?php
session_start();
require_once '../config/db.php';
if (!isset($_SESSION["admin_id"]) || $_SESSION["admin_role"] !== "admin") {
    header("Location: login.php");
    exit;
}

if (isset($_POST['add_timetable'])) {
    $class = $_POST['class'];
    $day = $_POST['day'];
    $time_slot = $_POST['time_slot'];
    $subject = $_POST['subject'];
    $teacher = $_POST['teacher'];

    $stmt = mysqli_prepare(
        $conn,
        "INSERT INTO timetable (`class`, day, time_slot, subject, teacher)
         VALUES (?, ?, ?, ?, ?)"
    );

    mysqli_stmt_bind_param(
        $stmt,
        "sssss",
        $class,
        $day,
        $time_slot,
        $subject,
        $teacher
    );

    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header("Location: timetable.php");
    exit;
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    $stmt = mysqli_prepare(
        $conn,
        "DELETE FROM timetable WHERE id = ?"
    );

    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header("Location: timetable.php");
    exit;
}

$result = mysqli_query(
    $conn,
    "SELECT * FROM timetable ORDER BY id DESC"
);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Timetable Management | Forces Academy LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        :root {
            --butter: #FFEFB3;
            --green: #013E37;
            --green-dark: #012a25;
            --text-dark: #1a1a1a;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', 'Poppins', Arial, sans-serif;
            background: #f4f6f5;
            color: #1e293b;
        }

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

        .container {
            width: 92%;
            max-width: 1250px;
            margin: 35px auto;
        }

        .page-heading {
            margin-bottom: 25px;
        }

        .page-heading h1 {
            font-size: 32px;
            color: var(--green);
            margin-bottom: 8px;
        }

        .page-heading p {
            color: #64748b;
            font-size: 15px;
        }

        .card {
            background: white;
            border-radius: 18px;
            box-shadow: 0 5px 20px rgba(15, 23, 42, 0.07);
            border: 1px solid #e5eaf2;
            margin-bottom: 28px;
            overflow: hidden;
        }

        .card-header {
            padding: 20px 25px;
            border-bottom: 1px solid #e8edf4;
            display: flex;
            align-items: center;
            gap: 12px;
            background: #ffffff;
        }

        .card-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: var(--butter);
            color: var(--green);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 21px;
            font-weight: bold;
        }

        .card-header h2 {
            font-size: 20px;
            color: var(--green);
            margin-bottom: 0;
        }

        .form-body {
            padding: 25px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-group label {
            font-size: 14px;
            font-weight: 600;
            color: #334155;
        }

        input,
        select {
            width: 100%;
            padding: 13px 14px;
            border: 1px solid #d6deea;
            border-radius: 10px;
            background: #fbfcfe;
            color: #334155;
            font-size: 14px;
            outline: none;
            transition: 0.2s;
        }

        input:focus,
        select:focus {
            border-color: var(--green);
            background: white;
            box-shadow: 0 0 0 3px rgba(1, 62, 55, 0.10);
        }

        .button-area {
            display: flex;
            justify-content: flex-end;
            align-items: end;
        }

        .add-btn {
            width: 100%;
            padding: 13px 20px;
            border: none;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--green), var(--green-dark));
            color: white;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
        }

        .add-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 15px rgba(1, 62, 55, 0.25);
        }

        .table-body {
            padding: 0 25px 25px;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            min-width: 750px;
        }

        th {
            background: #f1f5fb;
            color: #334155;
            font-size: 13px;
            font-weight: 700;
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #dfe6ef;
        }

        td {
            padding: 15px;
            font-size: 14px;
            border-bottom: 1px solid #edf1f6;
            color: #475569;
        }

        tr:hover td {
            background: rgba(255, 239, 179, 0.2);
        }

        .id-badge {
            background: var(--butter);
            color: var(--green);
            padding: 5px 9px;
            border-radius: 7px;
            font-weight: 600;
            font-size: 12px;
        }

        .class-badge {
            background: #eaf5f2;
            color: var(--green);
            padding: 6px 10px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 12px;
        }

        .day-badge {
            background: #f1f5f9;
            color: #475569;
            padding: 6px 10px;
            border-radius: 8px;
            font-size: 12px;
            font-weight: 600;
        }

        .delete-btn {
            display: inline-block;
            padding: 7px 12px;
            background: #fff1f2;
            color: #dc2626;
            border: 1px solid #fecdd3;
            border-radius: 8px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 600;
            transition: 0.2s;
        }

        .delete-btn:hover {
            background: #fee2e2;
        }

        .empty-state {
            text-align: center;
            padding: 55px 20px;
            color: #64748b;
        }

        .empty-icon {
            width: 65px;
            height: 65px;
            margin: 0 auto 15px;
            background: #eaf5f2;
            color: var(--green);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 28px;
        }

        .empty-state h3 {
            color: #334155;
            margin-bottom: 7px;
        }

        .empty-state p {
            font-size: 14px;
        }

        footer {
            text-align: center;
            padding: 25px;
            color: #94a3b8;
            font-size: 13px;
        }

        @media (max-width: 991px) {
            .container {
                width: 94%;
                margin: 25px auto;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .page-heading h1 {
                font-size: 27px;
            }

            .button-area {
                justify-content: stretch;
            }
        }
    </style>
</head>

<body>

    <!-- Top Navigation Bar -->
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
                        <a class="nav-link" href="students.php">Students</a>
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
                        <a class="nav-link active" href="timetable.php">Timetable</a>
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

    <main class="container">
        <div class="page-heading">
            <h1>Timetable Management</h1>
            <p>Add, view and manage class timetable entries easily.</p>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="card-icon">＋</div>
                <h2>Add Timetable Entry</h2>
            </div>

            <div class="form-body">
                <form method="POST">
                    <div class="form-grid">
                        <div class="form-group">
                            <label>Select Class</label>
                            <input type="text" name="class" placeholder="Enter class" required>
                        </div>

                        <div class="form-group">
                            <label>Select Day</label>
                            <select name="day" required>
                                <option value="">Choose Day</option>
                                <option value="Monday">Monday</option>
                                <option value="Tuesday">Tuesday</option>
                                <option value="Wednesday">Wednesday</option>
                                <option value="Thursday">Thursday</option>
                                <option value="Friday">Friday</option>
                                <option value="Saturday">Saturday</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label>Time Slot</label>
                            <input type="text" name="time_slot" placeholder="e.g. 10:00 - 11:00" required>
                        </div>

                        <div class="form-group">
                            <label>Subject</label>
                            <input type="text" name="subject" placeholder="Enter subject" required>
                        </div>

                        <div class="form-group">
                            <label>Teacher Name</label>
                            <input type="text" name="teacher" placeholder="Enter teacher name" required>
                        </div>

                        <div class="form-group button-area">
                            <button type="submit" name="add_timetable" class="add-btn">
                                ＋ Add Timetable
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div class="card-icon">☷</div>
                <h2>All Timetable Entries</h2>
            </div>

            <div class="table-body">
                <?php if (mysqli_num_rows($result) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Class</th>
                                <th>Day</th>
                                <th>Time Slot</th>
                                <th>Subject</th>
                                <th>Teacher</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td>
                                        <span class="id-badge">#<?= $row['id']; ?></span>
                                    </td>
                                    <td>
                                        <span class="class-badge"><?= htmlspecialchars($row['class']); ?></span>
                                    </td>
                                    <td>
                                        <span class="day-badge"><?= htmlspecialchars($row['day']); ?></span>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($row['time_slot']); ?>
                                    </td>
                                    <td>
                                        <strong><?= htmlspecialchars($row['subject']); ?></strong>
                                    </td>
                                    <td>
                                        <?= htmlspecialchars($row['teacher']); ?>
                                    </td>
                                    <td>
                                        <a class="delete-btn" href="timetable.php?delete=<?= $row['id']; ?>" onclick="return confirm('Are you sure you want to delete this timetable entry?');">
                                            Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon"></div>
                        <h3>No Timetable Entries Found</h3>
                        <p>Add your first timetable entry using the form above.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer>
        Forces Academy LMS © 2026
    </footer>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>