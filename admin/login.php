<?php
session_start();
require_once "../config/db.php";

// If already logged in, redirect to dashboard
if (isset($_SESSION["admin_id"]) && $_SESSION["admin_role"] === "admin") {
    header("Location: dashboard.php");
    exit;
}

// Initialize rate limiting in session
if (!isset($_SESSION['login_attempts'])) {
    $_SESSION['login_attempts'] = 0;
    $_SESSION['lockout_time'] = 0;
}

$lockout_duration = 30; // Lockout duration in seconds
$error = "";
$submitted_username = "";

// Check if user is currently locked out
if ($_SESSION['login_attempts'] >= 5 && (time() - $_SESSION['lockout_time']) < $lockout_duration) {
    $remaining = $lockout_duration - (time() - $_SESSION['lockout_time']);
    $error = "Too many failed attempts. Please try again in {$remaining} seconds.";
} else {
    if ($_SESSION['login_attempts'] >= 5) {
        $_SESSION['login_attempts'] = 0; // Reset after lockout expires
    }

    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $username = trim($_POST["username"] ?? "");
        $password = $_POST["password"] ?? "";
        $submitted_username = $username;

        if (empty($username) || empty($password)) {
            $error = "Please fill in all fields.";
        } else {
            $sql = "SELECT id, username, password FROM admins WHERE username = ? LIMIT 1";
            $stmt = mysqli_prepare($conn, $sql);

            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "s", $username);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);

                if ($result && mysqli_num_rows($result) === 1) {
                    $admin = mysqli_fetch_assoc($result);

                    // Strictly use secure password verification (removed insecure plaintext/MD5 fallbacks)
                    if (password_verify($password, $admin["password"])) {
                        // Reset login attempts on success
                        $_SESSION['login_attempts'] = 0;

                        // Secure Session Setup
                        session_regenerate_id(true);
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

            // Increment failed attempts and handle lockout
            $_SESSION['login_attempts']++;
            if ($_SESSION['login_attempts'] >= 5) {
                $_SESSION['lockout_time'] = time();
                $error = "Too many failed attempts. Account temporarily locked for {$lockout_duration} seconds.";
            } else {
                $error = "Invalid username or password.";
            }
        }
    }
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

        body.bg-light {
            background: linear-gradient(135deg, #f4f6f5 0%, var(--butter) 150%);
            font-family: 'Segoe UI', 'Poppins', sans-serif;
        }

        .card {
            border: none !important;
            border-radius: 18px !important;
            overflow: hidden;
        }

        h3.text-center {
            color: var(--green);
            font-weight: 700;
        }

        .form-label {
            color: var(--green);
            font-weight: 600;
        }

        .form-control {
            border-radius: 10px;
            padding: 10px 14px;
        }

        .form-control:focus {
            border-color: var(--green);
            box-shadow: 0 0 0 0.2rem rgba(1, 62, 55, 0.15);
        }

        .btn-primary {
            background-color: var(--green) !important;
            border-color: var(--green) !important;
            border-radius: 10px !important;
            font-weight: 600;
            padding: 10px 0;
        }

        .btn-primary:hover {
            background-color: var(--green-dark) !important;
            border-color: var(--green-dark) !important;
        }

        .alert-danger {
            border-radius: 10px;
        }
    </style>
</head>
<body class="bg-light">

    <div class="container">
        <div class="row justify-content-center align-items-center" style="min-height: 100vh;">
            <div class="col-md-5">
                <div class="card shadow">
                    <div class="card-body p-4">
                        <h3 class="text-center mb-4">Admin Login</h3>

                        <?php if ($error !== ""): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="">
                            <div class="mb-3">
                                <label class="form-label" for="username">Username</label>
                                <input type="text" id="username" name="username" class="form-control" value="<?php echo htmlspecialchars($submitted_username); ?>" required autofocus>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="password">Password</label>
                                <input type="password" id="password" name="password" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100" <?php echo ($_SESSION['login_attempts'] >= 5 && (time() - $_SESSION['lockout_time']) < $lockout_duration) ? 'disabled' : ''; ?>>Login</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>