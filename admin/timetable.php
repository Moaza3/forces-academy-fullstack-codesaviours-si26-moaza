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

    header("Location: timetable.php");
    exit;
}


// Delete timetable entry
if (isset($_GET['delete'])) {

    $id = intval($_GET['delete']);

    $stmt = mysqli_prepare(
        $conn,
        "DELETE FROM timetable WHERE id = ?"
    );

    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);

    header("Location: timetable.php");
    exit;
}


// Get all timetable entries
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

    <style>

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            background: #f4f7fb;
            color: #1e293b;
        }


        /* TOP HEADER */

        .topbar {
            background: linear-gradient(135deg, #123b70, #2563eb);
            color: white;
            padding: 18px 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.12);
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo-icon {
            width: 42px;
            height: 42px;
            background: white;
            color: #2563eb;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 21px;
            font-weight: bold;
        }

        .logo h2 {
            font-size: 20px;
        }

        .admin-badge {
            background: rgba(255,255,255,0.15);
            padding: 9px 15px;
            border-radius: 20px;
            font-size: 14px;
        }


        /* MAIN */

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
            color: #123b70;
            margin-bottom: 8px;
        }

        .page-heading p {
            color: #64748b;
            font-size: 15px;
        }


        /* CARDS */

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
        }

        .card-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: #e8f0ff;
            color: #2563eb;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 21px;
        }

        .card-header h2 {
            font-size: 20px;
            color: #123b70;
        }


        /* FORM */

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
            border-color: #2563eb;
            background: white;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.10);
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
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: white;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: 0.2s;
        }

        .add-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 6px 15px rgba(37, 99, 235, 0.25);
        }


        /* TABLE */

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
            background: #f8fbff;
        }

        .id-badge {
            background: #e8f0ff;
            color: #2563eb;
            padding: 5px 9px;
            border-radius: 7px;
            font-weight: 600;
            font-size: 12px;
        }

        .class-badge {
            background: #eef6ff;
            color: #1d4ed8;
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


        /* EMPTY STATE */

        .empty-state {
            text-align: center;
            padding: 55px 20px;
            color: #64748b;
        }

        .empty-icon {
            width: 65px;
            height: 65px;
            margin: 0 auto 15px;
            background: #eaf1ff;
            color: #2563eb;
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


        /* FOOTER */

        footer {
            text-align: center;
            padding: 25px;
            color: #94a3b8;
            font-size: 13px;
        }


        /* RESPONSIVE */

        @media (max-width: 850px) {

            .topbar {
                padding: 15px 20px;
            }

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


<!-- HEADER -->

<header class="topbar">

    <div class="logo">

        <div class="logo-icon">🎓</div>

        <h2>Forces Academy LMS</h2>

    </div>

    <div class="admin-badge">
        👤 Admin Panel
    </div>

</header>



<!-- MAIN CONTENT -->

<main class="container">


    <div class="page-heading">

        <h1>📅 Timetable Management</h1>

        <p>
            Add, view and manage class timetable entries easily.
        </p>

    </div>



    <!-- ADD TIMETABLE CARD -->

    <div class="card">

        <div class="card-header">

            <div class="card-icon">＋</div>

            <h2>Add Timetable Entry</h2>

        </div>


        <div class="form-body">

            <form method="POST">

                <div class="form-grid">


                    <!-- CLASS -->

                    <div class="form-group">

                        <label>Select Class</label>

                        <input
                            type="text"
                            name="class"
                            placeholder="Enter class"
                            required
                        >

                    </div>



                    <!-- DAY -->

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



                    <!-- TIME -->

                    <div class="form-group">

                        <label>Time Slot</label>

                        <input
                            type="text"
                            name="time_slot"
                            placeholder="e.g. 10:00 - 11:00"
                            required
                        >

                    </div>



                    <!-- SUBJECT -->

                    <div class="form-group">

                        <label>Subject</label>

                        <input
                            type="text"
                            name="subject"
                            placeholder="Enter subject"
                            required
                        >

                    </div>



                    <!-- TEACHER -->

                    <div class="form-group">

                        <label>Teacher Name</label>

                        <input
                            type="text"
                            name="teacher"
                            placeholder="Enter teacher name"
                            required
                        >

                    </div>



                    <!-- BUTTON -->

                    <div class="form-group button-area">

                        <button
                            type="submit"
                            name="add_timetable"
                            class="add-btn"
                        >
                            ＋ Add Timetable
                        </button>

                    </div>


                </div>

            </form>

        </div>

    </div>



    <!-- TABLE CARD -->

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

                                    <span class="id-badge">
                                        #<?= $row['id']; ?>
                                    </span>

                                </td>


                                <td>

                                    <span class="class-badge">
                                        <?= htmlspecialchars($row['class']); ?>
                                    </span>

                                </td>


                                <td>

                                    <span class="day-badge">
                                        <?= htmlspecialchars($row['day']); ?>
                                    </span>

                                </td>


                                <td>
                                    <?= htmlspecialchars($row['time_slot']); ?>
                                </td>


                                <td>

                                    <strong>
                                        <?= htmlspecialchars($row['subject']); ?>
                                    </strong>

                                </td>


                                <td>
                                    <?= htmlspecialchars($row['teacher']); ?>
                                </td>


                                <td>

                                    <a
                                        class="delete-btn"
                                        href="timetable.php?delete=<?= $row['id']; ?>"
                                        onclick="return confirm('Are you sure you want to delete this timetable entry?');"
                                    >
                                        🗑 Delete
                                    </a>

                                </td>

                            </tr>

                        <?php endwhile; ?>

                    </tbody>

                </table>

            <?php else: ?>

                <div class="empty-state">

                    <div class="empty-icon">
                        📅
                    </div>

                    <h3>No Timetable Entries Found</h3>

                    <p>
                        Add your first timetable entry using the form above.
                    </p>

                </div>

            <?php endif; ?>

        </div>

    </div>


</main>



<footer>

    Forces Academy LMS © 2026

</footer>


</body>

</html>