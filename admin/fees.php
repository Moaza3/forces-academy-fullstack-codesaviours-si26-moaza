<?php
session_start();
require_once "../config/db.php";

if (!isset($_SESSION["admin_id"]) || $_SESSION["admin_role"] !== "admin") {
    header("Location: login.php");
    exit;
}

$message = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $student_id = (int) $_POST["student_id"];
    $amount = (float) $_POST["amount"];
    $due_date = $_POST["due_date"];
    $description = trim($_POST["description"]);

    if ($student_id <= 0 || $amount <= 0 || empty($due_date)) {
        $error = "Please fill all required fields.";
    } else {

        $sql = "INSERT INTO fees
                (student_id, amount, due_date, status, description)
                VALUES (?, ?, ?, 'pending', ?)";

        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {

            mysqli_stmt_bind_param(
                $stmt,
                "idss",
                $student_id,
                $amount,
                $due_date,
                $description
            );

            if (mysqli_stmt_execute($stmt)) {
                $message = "Fee added successfully!";
            } else {
                $error = "Fee could not be added.";
            }

            mysqli_stmt_close($stmt);

        } else {
            $error = "Database error.";
        }
    }
}

$students_sql = "SELECT id, full_name, roll_number
                 FROM students
                 ORDER BY full_name ASC";

$students_result = mysqli_query($conn, $students_sql);

$fees_sql = "SELECT
                fees.id,
                fees.amount,
                fees.due_date,
                fees.status,
                fees.description,
                students.full_name,
                students.roll_number
             FROM fees
             INNER JOIN students
             ON fees.student_id = students.id
             ORDER BY fees.due_date DESC";

$fees_result = mysqli_query($conn, $fees_sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Manage Fees</title>

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
            background: linear-gradient(
                180deg,
                var(--green) 0%,
                var(--green-dark) 100%
            );
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
            margin-bottom: 6px;
            font-weight: 500;
        }

        .sidebar .nav-link:hover {
            background-color: rgba(255, 239, 179, 0.15);
            color: var(--butter);
        }

        .sidebar .logout {
            color: #ff6b6b;
        }

        h2 {
            color: var(--green);
            font-weight: 700;
        }

        .card {
            border: none;
            border-radius: 14px;
        }

        .btn-primary {
            background-color: var(--green) !important;
            border-color: var(--green) !important;
        }

        .btn-primary:hover {
            background-color: var(--green-dark) !important;
        }

        .table thead {
            background-color: var(--green);
            color: white;
        }

        .table thead th {
            border: none;
        }

    </style>

</head>

<body>

<div class="container-fluid">

    <div class="row">

        <div class="col-md-3 col-lg-2 sidebar text-white min-vh-100 p-3">

            <h4 class="text-center mb-4">
                Admin Panel
            </h4>

            <div class="nav flex-column">

                <a href="dashboard.php" class="nav-link">
                    Dashboard
                </a>

                <a href="students.php" class="nav-link">
                    Manage Students
                </a>

                <a href="courses.php" class="nav-link">
                    Manage Courses
                </a>

                <a href="assignments.php" class="nav-link">
                    Manage Assignments
                </a>

                <a href="results.php" class="nav-link">
                    Upload Results
                </a>

                <a href="notice.php" class="nav-link">
                    Post Notice
                </a>

                <a href="fees.php" class="nav-link">
                    Manage Fees
                </a>

                <a href="logout.php" class="nav-link logout">
                    Logout
                </a>

            </div>

        </div>

        <div class="col-md-9 col-lg-10 p-4">

            <h2 class="mb-4">
                Manage Fees
            </h2>

            <?php if ($message !== ""): ?>

                <div class="alert alert-success">
                    <?php echo htmlspecialchars($message); ?>
                </div>

            <?php endif; ?>

            <?php if ($error !== ""): ?>

                <div class="alert alert-danger">
                    <?php echo htmlspecialchars($error); ?>
                </div>

            <?php endif; ?>

            <div class="card shadow-sm mb-4">

                <div class="card-body">

                    <h5 class="mb-4">
                        Add Fee
                    </h5>

                    <form method="POST">

                        <div class="row">

                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    Student
                                </label>

                                <select
                                    name="student_id"
                                    class="form-select"
                                    required
                                >

                                    <option value="">
                                        Select Student
                                    </option>

                                    <?php while ($student = mysqli_fetch_assoc($students_result)): ?>

                                        <option value="<?php echo $student["id"]; ?>">

                                            <?php
                                            echo htmlspecialchars(
                                                $student["full_name"] .
                                                " - " .
                                                $student["roll_number"]
                                            );
                                            ?>

                                        </option>

                                    <?php endwhile; ?>

                                </select>

                            </div>

                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    Amount
                                </label>

                                <input
                                    type="number"
                                    name="amount"
                                    class="form-control"
                                    step="0.01"
                                    min="1"
                                    placeholder="Enter fee amount"
                                    required
                                >

                            </div>

                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    Due Date
                                </label>

                                <input
                                    type="date"
                                    name="due_date"
                                    class="form-control"
                                    required
                                >

                            </div>

                            <div class="col-md-6 mb-3">

                                <label class="form-label">
                                    Description
                                </label>

                                <input
                                    type="text"
                                    name="description"
                                    class="form-control"
                                    placeholder="e.g. September Tuition Fee"
                                >

                            </div>

                        </div>

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >
                            Add Fee
                        </button>

                    </form>

                </div>

            </div>

            <div class="card shadow-sm">

                <div class="card-body">

                    <h5 class="mb-4">
                        Fee Records
                    </h5>

                    <div class="table-responsive">

                        <table class="table table-bordered table-hover">

                            <thead>

                                <tr>
                                    <th>Student</th>
                                    <th>Roll Number</th>
                                    <th>Amount</th>
                                    <th>Due Date</th>
                                    <th>Status</th>
                                    <th>Description</th>
                                </tr>

                            </thead>

                            <tbody>

                            <?php if ($fees_result && mysqli_num_rows($fees_result) > 0): ?>

                                <?php while ($fee = mysqli_fetch_assoc($fees_result)): ?>

                                    <tr>

                                        <td>
                                            <?php echo htmlspecialchars($fee["full_name"]); ?>
                                        </td>

                                        <td>
                                            <?php echo htmlspecialchars($fee["roll_number"]); ?>
                                        </td>

                                        <td>
                                            Rs.
                                            <?php echo number_format($fee["amount"], 2); ?>
                                        </td>

                                        <td>
                                            <?php echo htmlspecialchars($fee["due_date"]); ?>
                                        </td>

                                        <td>

                                            <?php if ($fee["status"] === "paid"): ?>

                                                <span class="badge bg-success">
                                                    Paid
                                                </span>

                                            <?php elseif ($fee["status"] === "overdue"): ?>

                                                <span class="badge bg-danger">
                                                    Overdue
                                                </span>

                                            <?php else: ?>

                                                <span class="badge bg-warning text-dark">
                                                    Pending
                                                </span>

                                            <?php endif; ?>

                                        </td>

                                        <td>
                                            <?php echo htmlspecialchars($fee["description"] ?? ""); ?>
                                        </td>

                                    </tr>

                                <?php endwhile; ?>

                            <?php else: ?>

                                <tr>
                                    <td colspan="6" class="text-center">
                                        No fee records found.
                                    </td>
                                </tr>

                            <?php endif; ?>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

</body>
</html>
