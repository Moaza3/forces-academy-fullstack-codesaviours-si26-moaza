<?php
require_once 'config/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT id, full_name, password FROM students WHERE email = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $student = mysqli_fetch_assoc($result);

    if ($student && password_verify($password, $student['password'])) {
        $_SESSION['student_id'] = $student['id'];
        $_SESSION['student_name'] = $student['full_name'];
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Invalid email or password.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        /* ============================================
           Theme: Butter & Green
           Butter: #FFEFB3   |   Green: #013E37
           Only visual styling — no structure/logic changed.
        ============================================ */

        :root {
            --butter: #FFEFB3;
            --green: #013E37;
            --green-dark: #012a25;
            --text-dark: #1a1a1a;
        }

        body.bg-light {
            background: linear-gradient(135deg, #f4f6f5 0%, var(--butter) 150%);
            font-family: 'Segoe UI', 'Poppins', sans-serif;
            min-height: 100vh;
        }

        .card {
            border: none !important;
            border-radius: 18px !important;
            overflow: hidden;
        }

        .card-header {
            background: linear-gradient(135deg, var(--green) 0%, var(--green-dark) 100%) !important;
            border-bottom: 4px solid var(--butter);
            padding: 22px 0;
        }

        .card-header h3 {
            font-weight: 700;
            letter-spacing: 0.5px;
            margin: 0;
        }

        .card-body {
            padding: 30px 35px;
        }

        .form-label {
            color: var(--green);
            font-weight: 600;
        }

        .form-control {
            border-radius: 10px;
            border: 1px solid #ddd;
            padding: 10px 14px;
        }

        .form-control:focus {
            border-color: var(--green);
            box-shadow: 0 0 0 0.2rem rgba(1, 62, 55, 0.15);
        }

        .btn {
            background-color: var(--green) !important;
            border-color: var(--green) !important;
            border-radius: 10px !important;
            font-weight: 600;
            padding: 10px 0;
            transition: all 0.25s ease;
        }

        .btn:hover {
            background-color: var(--green-dark) !important;
            border-color: var(--green-dark) !important;
        }

        hr {
            border-top: 2px solid var(--butter);
            opacity: 1;
        }

        a {
            color: var(--green);
            font-weight: 600;
            text-decoration: none;
        }

        a:hover {
            color: var(--green-dark);
            text-decoration: underline;
        }

        .alert-danger {
            border-radius: 10px;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header text-center text-white">
                        <h3>Student Login</h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)) { ?>
                            <div class="alert alert-danger">
                                <?php echo $error; ?>
                            </div>
                        <?php } ?>
                        <form action="login.php" method="POST">
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input
                                    type="email"
                                    name="email"
                                    class="form-control"
                                    required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input
                                    type="password"
                                    name="password"
                                    class="form-control"
                                    required>
                            </div>
                            <button
                                type="submit"
                                class="btn w-100 text-white">
                                Login
                            </button>
                        </form>
                        <hr>
                        <p class="text-center">
                            Don't have an account?
                            <a href="register.php">Register Here</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>