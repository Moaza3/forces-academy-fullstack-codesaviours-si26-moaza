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
            font-weight: 500;
        }

        .bg-dark .nav-link:hover {
            background-color: rgba(255, 239, 179, 0.15);
            color: var(--butter) !important;
            padding-left: 20px;
        }

        .bg-dark .nav-link.text-danger {
            color: #ff6b6b !important;
            margin-top: 10px;
        }

        .bg-dark .nav-link.text-danger:hover {
            background-color: rgba(255, 107, 107, 0.15);
            color: #ff8787 !important;
        }

        h2.mb-4 {
            color: var(--green);
            font-weight: 700;
            position: relative;
            padding-bottom: 10px;
        }

        h2.mb-4::after {
            content: "";
            position: absolute;
            left: 0;
            bottom: 0;
            width: 60px;
            height: 4px;
            background-color: var(--butter);
            border-radius: 2px;
        }

        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
        }

        .btn-primary {
            background-color: var(--green) !important;
            border-color: var(--green) !important;
            font-weight: 600;
            padding: 10px 20px;
            border-radius: 8px;
        }

        .btn-primary:hover {
            background-color: var(--green-dark) !important;
            border-color: var(--green-dark) !important;
        }

        .table thead {
            background-color: var(--green);
            color: white;
        }

        .table thead th {
            border: none;
            padding: 12px;
        }

        .table tbody td {
            padding: 12px;
            vertical-align: middle;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(255, 239, 179, 0.2);
        }

        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-thumb {
            background: var(--green);
            border-radius: 4px;
        }

        /* Mobile Toggle Responsive Styles */
        @media (max-width: 768px) {
            #sidebarMenu {
                display: none;
                position: absolute;
                z-index: 1000;
                width: 100%;
                left: 0;
            }
            #sidebarMenu.show {
                display: block !important;
            }
        }
    </style>
</head>

<body class="bg-light">

<div class="container-fluid">
    <div class="row">

        <!-- Sidebar -->
        <div class="col-md-3 col-lg-2 bg-dark text-white min-vh-100 p-3" id="sidebarMenu">
            <h4 class="text-center mb-4">Admin Panel</h4>

            <div class="nav flex-column">
                <a href="dashboard.php" class="nav-link text-white mb-2">Dashboard</a>
                <a href="students.php" class="nav-link text-white mb-2">Manage Students</a>
                <a href="courses.php" class="nav-link text-white mb-2">Manage Courses</a>
                <a href="assignments.php" class="nav-link text-white mb-2">Manage Assignments</a>
                <a href="results.php" class="nav-link text-white mb-2">Upload Results</a>
                <a href="notices.php" class="nav-link text-white mb-2">Post Notice</a>
                <a href="fees.php" class="nav-link text-white mb-2 active" style="background-color: rgba(255, 239, 179, 0.15); color: var(--butter) !important;">Manage Fees</a>
                <a href="logout.php" class="nav-link text-danger">Logout</a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="col-md-9 col-lg-10 p-4">

            <!-- Mobile Hamburger Toggle Button -->
            <div class="d-md-none mb-3">
                <button class="btn text-white px-3 py-2 rounded-3 shadow-sm" id="mobileMenuBtn" style="background-color: var(--green);">
                    ☰ Menu
                </button>
            </div>

            <h2 class="mb-4">Manage Fees</h2>

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
                <div class="card-body p-4">
                    <h5 class="mb-4 text-secondary" style="font-size: 1.25rem; font-weight: 600;">Add Fee</h5>

                    <form method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold text-success">Student</label>
                                <select name="student_id" class="form-select" required>
                                    <option value="">Select Student</option>
                                    <?php while ($student = mysqli_fetch_assoc($students_result)): ?>
                                        <option value="<?php echo $student["id"]; ?>">
                                            <?php echo htmlspecialchars($student["full_name"] . " - " . $student["roll_number"]); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold text-success">Amount</label>
                                <input type="number" name="amount" class="form-control" step="0.01" min="1" placeholder="Enter fee amount" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold text-success">Due Date</label>
                                <input type="date" name="due_date" class="form-control" required>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold text-success">Description</label>
                                <input type="text" name="description" class="form-control" placeholder="e.g. September Tuition Fee">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary mt-2">Add Fee</button>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h5 class="mb-4 text-secondary" style="font-size: 1.25rem; font-weight: 600;">Fee Records</h5>

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
                                        <td><?php echo htmlspecialchars($fee["full_name"]); ?></td>
                                        <td><?php echo htmlspecialchars($fee["roll_number"]); ?></td>
                                        <td>Rs. <?php echo number_format($fee["amount"], 2); ?></td>
                                        <td><?php echo htmlspecialchars($fee["due_date"]); ?></td>
                                        <td>
                                            <?php if ($fee["status"] === "paid"): ?>
                                                <span class="badge bg-success">Paid</span>
                                            <?php elseif ($fee["status"] === "overdue"): ?>
                                                <span class="badge bg-danger">Overdue</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning text-dark">Pending</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($fee["description"] ?? ""); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center py-3">No fee records found.</td>
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
<script>
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');
    const sidebarMenu = document.getElementById('sidebarMenu');

    if (mobileMenuBtn && sidebarMenu) {
        mobileMenuBtn.addEventListener('click', function() {
            sidebarMenu.classList.toggle('show');
        });
    }
</script>

</body>
</html>