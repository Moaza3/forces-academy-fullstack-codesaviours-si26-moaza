<?php
session_start();
include('config/db.php');

$student_id = $_SESSION['user_id']; // Logged in student ID

// Total Pending Amount Calculate karein
$pending_query = $conn->prepare("SELECT SUM(amount) AS total_pending FROM fees WHERE student_id = ? AND status != 'paid'");
$pending_query->bind_param("i", $student_id);
$pending_query->execute();
$total_pending = $pending_query->get_result()->fetch_assoc()['total_pending'] ?? 0;

// Student ke tamam fee records fetch karein
$stmt = $conn->prepare("SELECT * FROM fees WHERE student_id = ? ORDER BY due_date DESC");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$fees = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Fees</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container py-4">
    <!-- Top Prominent Total Pending Card -->
    <div class="card bg-danger text-white mb-4 shadow">
        <div class="card-body">
            <h4 class="card-title">Total Pending Amount</h4>
            <h2 class="display-6 font-weight-bold">PKR <?= number_format($total_pending, 2) ?></h2>
        </div>
    </div>

    <h3>Fee History & Status</h3>
    <table class="table table-bordered table-striped mt-3">
        <thead class="table-dark">
            <tr>
                <th>Description</th>
                <th>Amount</th>
                <th>Due Date</th>
                <th>Paid Date</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $fees->fetch_assoc()): ?>
            <tr>
                <td><?= $row['description'] ?></td>
                <td>PKR <?= number_format($row['amount'], 2) ?></td>
                <td><?= $row['due_date'] ?></td>
                <td><?= $row['paid_date'] ? $row['paid_date'] : '-' ?></td>
                <td>
                    <?php 
                        if($row['status'] == 'paid') echo '<span class="badge bg-success">Paid</span>';
                        elseif($row['status'] == 'overdue') echo '<span class="badge bg-danger">Overdue</span>';
                        else echo '<span class="badge bg-warning text-dark">Pending</span>';
                    ?>
                </td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</body>
</html>