<?php
require_once 'config/db.php';
session_start();

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit;
}

$student_id = (int)$_SESSION['student_id'];
$student_name = $_SESSION['student_name'] ?? 'Student';

$sql_pending = "SELECT COALESCE(SUM(amount), 0) AS total_pending
                 FROM fees
                 WHERE student_id = ?
                 AND status = 'pending'";

$stmt_pending = mysqli_prepare($conn, $sql_pending);
mysqli_stmt_bind_param($stmt_pending, 'i', $student_id);
mysqli_stmt_execute($stmt_pending);
$result_pending = mysqli_stmt_get_result($stmt_pending);
$row_pending = mysqli_fetch_assoc($result_pending);
$total_pending = $row_pending['total_pending'] ?? 0;
mysqli_stmt_close($stmt_pending);

$sql = "SELECT amount, due_date, paid_date, status, description
        FROM fees
        WHERE student_id = ?
        ORDER BY due_date DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $student_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>My Fees</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>
        :root {
            --butter: #FFEFB3;
            --green: #013E37;
            --green-dark: #012a25;
        }

        body {
            background-color: #f4f6f5;
            font-family: 'Segoe UI', 'Poppins', sans-serif;
        }

        .sidebar {
            background: linear-gradient(180deg, var(--green) 0%, var(--green-dark) 100%);
            width: 250px;
            min-height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
        }

        .sidebar h4 {
            color: var(--butter);
            font-weight: 700;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255, 239, 179, 0.2);
        }

        .sidebar .nav-link {
            color: white;
            border-radius: 8px;
            padding: 10px 14px;
            margin-bottom: 5px;
        }

        .sidebar .nav-link:hover {
            background-color: rgba(255, 239, 179, 0.15);
            color: var(--butter);
        }

        .sidebar .nav-link.active {
            background-color: var(--butter);
            color: var(--green-dark);
        }

        .main-content {
            margin-left: 250px;
            padding: 30px;
        }

        h2 {
            color: var(--green);
            font-weight: 700;
        }

        .pending-card {
            background: linear-gradient(135deg, var(--green), var(--green-dark));
            color: white;
            border-radius: 15px;
        }

        .pending-card h6 {
            color: var(--butter);
            font-weight: 600;
        }

        .pending-card h2 {
            color: white;
            font-size: 2.3rem;
        }

        .card {
            border: none;
            border-radius: 14px;
        }

        .table thead {
            background-color: var(--green);
            color: white;
        }

        .badge {
            padding: 8px 12px;
            border-radius: 20px;
        }

        @media (max-width: 991px) {
            .sidebar {
                position: static;
                width: 100%;
                min-height: auto;
            }

            .main-content {
                margin-left: 0;
                padding: 20px;
            }
        }
    </style>
</head>

<body>

<div class="sidebar p-3">
    <h4 class="text-center mb-4">Student Portal</h4>

    <ul class="nav flex-column">

        <li class="nav-item">
            <a href="dashboard.php" class="nav-link">Dashboard</a>
        </li>

        <li class="nav-item">
            <a href="profile.php" class="nav-link">My Profile</a>
        </li>

        <li class="nav-item">
            <a href="courses.php" class="nav-link">My Courses</a>
        </li>

        <li class="nav-item">
            <a href="timetable.php" class="nav-link">Timetable</a>
        </li>

        <li class="nav-item">
            <a href="assignment.php" class="nav-link">Assignments</a>
        </li>

        <li class="nav-item">
            <a href="results.php" class="nav-link">My Results</a>
        </li>

        <li class="nav-item">
            <a href="notices.php" class="nav-link">Notices</a>
        </li>

        <li class="nav-item">
            <a href="fees.php" class="nav-link active">My Fees</a>
        </li>

        <li class="nav-item mt-3">
            <a href="logout.php" class="nav-link text-danger">Logout</a>
        </li>

    </ul>
</div>

<div class="main-content">

    <h2>My Fees</h2>

    <p class="text-muted">
        Welcome, <?php echo htmlspecialchars($student_name); ?>
    </p>

    <hr>

    <div class="card pending-card shadow-sm mb-4">
        <div class="card-body">
            <h6>Total Pending Amount</h6>
            <h2>
                <?php echo number_format((float)$total_pending, 2); ?>
            </h2>
            <p class="mb-0">Amount currently pending</p>
        </div>
    </div>

    <div class="card shadow-sm">

        <div class="card-body">

            <h4 class="mb-4">Fee Records</h4>

            <?php if (mysqli_num_rows($result) > 0) { ?>

                <div class="table-responsive">

                    <table class="table table-bordered table-hover align-middle">

                        <thead>
                            <tr>
                                <th>Amount</th>
                                <th>Due Date</th>
                                <th>Paid Date</th>
                                <th>Status</th>
                                <th>Description</th>
                            </tr>
                        </thead>

                        <tbody>

                        <?php while ($fee = mysqli_fetch_assoc($result)) { ?>

                            <tr>

                                <td>
                                    <?php echo number_format((float)$fee['amount'], 2); ?>
                                </td>

                                <td>
                                    <?php echo date('d M Y', strtotime($fee['due_date'])); ?>
                                </td>

                                <td>
                                    <?php
                                    if (!empty($fee['paid_date'])) {
                                        echo date('d M Y', strtotime($fee['paid_date']));
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>

                                <td>

                                    <?php if ($fee['status'] === 'paid') { ?>

                                        <span class="badge bg-success">
                                            Paid
                                        </span>

                                    <?php } elseif ($fee['status'] === 'overdue') { ?>

                                        <span class="badge bg-danger">
                                            Overdue
                                        </span>

                                    <?php } else { ?>

                                        <span class="badge bg-warning text-dark">
                                            Pending
                                        </span>

                                    <?php } ?>

                                </td>

                                <td>
                                    <?php
                                    echo !empty($fee['description'])
                                        ? htmlspecialchars($fee['description'])
                                        : '-';
                                    ?>
                                </td>

                            </tr>

                        <?php } ?>

                        </tbody>

                    </table>

                </div>

            <?php } else { ?>

                <div class="alert alert-info">
                    No fee records found.
                </div>

            <?php } ?>

        </div>

    </div>

</div>

</body>
</html>

<?php
mysqli_stmt_close($stmt);
?>