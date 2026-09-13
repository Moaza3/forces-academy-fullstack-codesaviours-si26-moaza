<?php
session_start();

include('../config/db.php');

$msg = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $student_id = isset($_POST['student_id']) ? (int) $_POST['student_id'] : 0;
    $amount = isset($_POST['amount']) ? (float) $_POST['amount'] : 0;
    $due_date = isset($_POST['due_date']) ? trim($_POST['due_date']) : '';
    $description = isset($_POST['description']) ? trim($_POST['description']) : '';

    if ($student_id <= 0) {
        $error = "Please select a student.";
    } elseif ($amount <= 0) {
        $error = "Please enter a valid fee amount.";
    } elseif (empty($due_date)) {
        $error = "Please select a due date.";
    } else {

        $check = $conn->prepare(
            "SELECT id FROM users WHERE id = ? AND role = 'student' LIMIT 1"
        );

        if ($check) {

            $check->bind_param("i", $student_id);
            $check->execute();

            $student_result = $check->get_result();

            if ($student_result->num_rows === 0) {

                $error = "Invalid student selected.";

            } else {

                $stmt = $conn->prepare(
                    "INSERT INTO fees
                    (student_id, amount, due_date, paid_date, status, description)
                    VALUES (?, ?, ?, NULL, 'pending', ?)"
                );

                if ($stmt) {

                    $stmt->bind_param(
                        "idss",
                        $student_id,
                        $amount,
                        $due_date,
                        $description
                    );

                    if ($stmt->execute()) {
                        $msg = "Fee record successfully added!";
                    } else {
                        $error = "Unable to add fee record. Please try again.";
                    }

                    $stmt->close();

                } else {
                    $error = "Database error. Please try again.";
                }
            }

            $check->close();

        } else {
            $error = "Database error. Please try again.";
        }
    }
}

$students = $conn->query(
    "SELECT id, name
     FROM users
     WHERE role = 'student'
     ORDER BY name ASC"
);

?>
<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin - Fee Management</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <style>

        :root {
            --butter: #FFEFB3;
            --green: #013E37;
            --green-dark: #012a25;
            --text-dark: #1a1a1a;
        }

        body {
            background-color: #f4f6f5;
            font-family: 'Segoe UI', 'Poppins', sans-serif;
        }

        .page-wrapper {
            max-width: 900px;
            margin: 0 auto;
        }

        .fee-card {
            border: none;
            border-radius: 16px;
        }

        .page-title {
            font-weight: 700;
            color: var(--green);
        }

        .form-label {
            font-weight: 600;
            color: var(--green);
        }

        .form-control,
        .form-select {
            min-height: 48px;
            border-radius: 10px;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--green);
            box-shadow: 0 0 0 0.2rem rgba(1, 62, 55, 0.15);
        }

        .btn-primary {
            background-color: var(--green) !important;
            border-color: var(--green) !important;
        }

        .btn-primary:hover {
            background-color: var(--green-dark) !important;
            border-color: var(--green-dark) !important;
        }

        .btn-add {
            min-height: 48px;
            border-radius: 10px;
            font-weight: 600;
        }

        .alert {
            border-radius: 10px;
        }

        .alert-success {
            border-left: 4px solid var(--green);
        }

        @media (max-width: 576px) {

            .page-wrapper {
                width: 100%;
            }

            .card-body {
                padding: 20px !important;
            }

            .page-title {
                font-size: 24px;
            }

        }

    </style>

</head>

<body>

<div class="container-fluid py-4">

    <div class="page-wrapper">

        <div class="mb-4">

            <h2 class="page-title mb-1">
                Fee Management
            </h2>

            <p class="text-muted mb-0">
                Add a new fee record for a student
            </p>

        </div>


        <?php if (!empty($msg)): ?>

            <div class="alert alert-success alert-dismissible fade show" role="alert">

                <strong>Success!</strong>
                <?php echo htmlspecialchars($msg); ?>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                ></button>

            </div>

        <?php endif; ?>


        <?php if (!empty($error)): ?>

            <div class="alert alert-danger alert-dismissible fade show" role="alert">

                <strong>Error!</strong>
                <?php echo htmlspecialchars($error); ?>

                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="alert"
                ></button>

            </div>

        <?php endif; ?>


        <div class="card fee-card shadow-sm">

            <div class="card-body p-4 p-md-5">

                <form method="POST" action="">

                    <div class="mb-4">

                        <label for="student_id" class="form-label">
                            Select Student
                        </label>

                        <select
                            name="student_id"
                            id="student_id"
                            class="form-select"
                            required
                        >

                            <option value="">
                                Select Student
                            </option>

                            <?php if ($students && $students->num_rows > 0): ?>

                                <?php while ($row = $students->fetch_assoc()): ?>

                                    <option
                                        value="<?php echo (int) $row['id']; ?>"
                                    >
                                        <?php echo htmlspecialchars($row['name']); ?>
                                    </option>

                                <?php endwhile; ?>

                            <?php else: ?>

                                <option value="" disabled>
                                    No students found
                                </option>

                            <?php endif; ?>

                        </select>

                    </div>


                    <div class="mb-4">

                        <label for="amount" class="form-label">
                            Amount (PKR)
                        </label>

                        <input
                            type="number"
                            name="amount"
                            id="amount"
                            class="form-control"
                            placeholder="Enter fee amount"
                            min="1"
                            step="0.01"
                            required
                        >

                    </div>


                    <div class="mb-4">

                        <label for="due_date" class="form-label">
                            Due Date
                        </label>

                        <input
                            type="date"
                            name="due_date"
                            id="due_date"
                            class="form-control"
                            required
                        >

                    </div>


                    <div class="mb-4">

                        <label for="description" class="form-label">
                            Description
                        </label>

                        <input
                            type="text"
                            name="description"
                            id="description"
                            class="form-control"
                            placeholder="e.g. Monthly Tuition Fee"
                            maxlength="255"
                        >

                    </div>


                    <div class="d-grid">

                        <button
                            type="submit"
                            class="btn btn-primary btn-add"
                        >
                            Add Fee Record
                        </button>

                    </div>

                </form>

            </div>

        </div>

    </div>

</div>


<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

</body>

</html>