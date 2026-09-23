<?php
session_start();
require_once "../config/db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"]);
    $password = $_POST["password"];

    $sql = "SELECT id, username, password FROM admins WHERE username = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) === 1) {
            $admin = mysqli_fetch_assoc($result);

            $stored_password = $admin["password"];
            $login_success = false;

            if (password_verify($password, $stored_password)) {
                $login_success = true;
            }
            elseif ($password === $stored_password) {
                $login_success = true;
                $new_hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $update_sql = "UPDATE admins SET password = ? WHERE id = ?";
                $update_stmt = mysqli_prepare($conn, $update_sql);
                if ($update_stmt) {
                    mysqli_stmt_bind_param($update_stmt, "si", $new_hashed_password, $admin["id"]);
                    mysqli_stmt_execute($update_stmt);
                    mysqli_stmt_close($update_stmt);
                }
            }

            if ($login_success) {
                $_SESSION["admin_id"] = $admin["id"];
                $_SESSION["admin_username"] = $admin["username"];
                $_SESSION["admin_role"] = "admin";

                unset($_SESSION["student_id"]);
                unset($_SESSION["student_name"]);

                header("Location: dashboard.php");
                exit;
            }
        }

        mysqli_stmt_close($stmt);
    }

    $error = "Invalid username or password.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
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
        }

        html, body {
            height: 100%;
        }

        body {
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
            background: linear-gradient(135deg, #f4f6f5 0%, var(--butter) 150%);
            font-family: 'Segoe UI', 'Poppins', sans-serif;
        }

        .login-wrapper {
            width: 100%;
            max-width: 430px;
        }

        .login-card {
            background: #ffffff;
            border-radius: 22px;
            overflow: hidden;
            box-shadow:
                0 25px 45px rgba(1, 62, 55, 0.22),
                0 8px 15px rgba(1, 62, 55, 0.12),
                inset 0 1px 0 rgba(255, 255, 255, 0.4);
            transform: translateZ(0);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .login-card:hover {
            transform: translateY(-4px);
            box-shadow:
                0 30px 55px rgba(1, 62, 55, 0.26),
                0 10px 18px rgba(1, 62, 55, 0.14),
                inset 0 1px 0 rgba(255, 255, 255, 0.4);
        }

        .login-header {
            background: linear-gradient(160deg, var(--green) 0%, var(--green-dark) 100%);
            padding: 32px 30px 26px;
            text-align: center;
            position: relative;
        }

        .login-header::after {
            content: "";
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--butter), #ffe58f, var(--butter));
        }

        .login-header h3 {
            color: #ffffff;
            font-weight: 700;
            font-size: 1.6rem;
            margin: 0;
            letter-spacing: 0.3px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.25);
        }

        .login-body {
            padding: 34px 32px 32px;
        }

        .form-label {
            color: var(--green);
            font-weight: 600;
            font-size: 0.92rem;
        }

        .form-control {
            border-radius: 12px;
            padding: 12px 16px;
            border: 1px solid #dfe6e4;
            background: #fbfdfc;
            box-shadow: inset 0 2px 4px rgba(1, 62, 55, 0.05);
            transition: all 0.2s ease;
        }

        .form-control:focus {
            border-color: var(--green);
            background: #ffffff;
            box-shadow: 0 0 0 0.2rem rgba(1, 62, 55, 0.15), inset 0 2px 4px rgba(1, 62, 55, 0.05);
        }

        .btn-primary {
            background: linear-gradient(160deg, var(--green) 0%, var(--green-dark) 100%) !important;
            border: none !important;
            border-radius: 12px !important;
            font-weight: 600;
            padding: 12px 0;
            box-shadow: 0 8px 16px rgba(1, 62, 55, 0.3);
            transition: transform 0.15s ease, box-shadow 0.15s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 20px rgba(1, 62, 55, 0.35);
        }

        .btn-primary:active {
            transform: translateY(0);
            box-shadow: 0 4px 10px rgba(1, 62, 55, 0.3);
        }

        .alert-danger {
            border-radius: 10px;
            font-size: 0.9rem;
        }

        @media (max-width: 480px) {
            .login-header {
                padding: 26px 22px 22px;
            }

            .login-header h3 {
                font-size: 1.35rem;
            }

            .login-body {
                padding: 26px 22px 24px;
            }
        }
    </style>
</head>
<body>

    <div class="login-wrapper">
        <div class="login-card">
            <div class="login-header">
                <h3>Admin Login</h3>
            </div>

            <div class="login-body">
                <?php if ($error !== ""): ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" name="username" class="form-control" required>
                    </div>
                    <div class="mb-4">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Login</button>
                </form>
            </div>
        </div>
    </div>

</body>
</html>