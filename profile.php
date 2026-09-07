<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit;
}

require_once "config/db.php";

$student_id = (int)$_SESSION['student_id'];
$success_msg = "";
$error_msg = "";

$stmt = mysqli_prepare($conn, "SELECT id, full_name, email, roll_number, class, password FROM students WHERE id = ?");
if (!$stmt) {
    die("Prepare Error: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$student = mysqli_fetch_assoc($result);

if (!$student) {
    die("Student record not found for ID: " . $student_id);
}

if (isset($_POST['update_profile'])) {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);

    if (!empty($full_name) && !empty($email)) {
        $update_stmt = mysqli_prepare($conn, "UPDATE students SET full_name = ?, email = ? WHERE id = ?");
        mysqli_stmt_bind_param($update_stmt, "ssi", $full_name, $email, $student_id);

        if (mysqli_stmt_execute($update_stmt)) {
            $student['full_name'] = $full_name;
            $student['email'] = $email;
            $_SESSION['student_name'] = $full_name;
            $success_msg = "Profile updated successfully!";
        } else {
            $error_msg = "Failed to update profile. Email might already exist.";
        }
    } else {
        $error_msg = "All fields are required.";
    }
}

if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error_msg = "Please fill all password fields.";
    } elseif ($new_password !== $confirm_password) {
        $error_msg = "New password and Confirm password do not match.";
    } else {
        $password_matches = password_verify($current_password, $student['password']) || ($current_password === $student['password']);

        if ($password_matches) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $pass_stmt = mysqli_prepare($conn, "UPDATE students SET password = ? WHERE id = ?");
            mysqli_stmt_bind_param($pass_stmt, "si", $hashed_password, $student_id);

            if (mysqli_stmt_execute($pass_stmt)) {
                $student['password'] = $hashed_password;
                $success_msg = "Password changed successfully!";
            } else {
                $error_msg = "Failed to update password.";
            }
        } else {
            $error_msg = "Incorrect current password.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | Student Portal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

    <div class="bg-dark text-white p-3 vh-100" style="width: 250px; position: fixed; left: 0; top: 0;">
        <h4 class="text-center mb-4">Student Portal</h4>
        <ul class="nav flex-column">
            <li class="nav-item mb-2">
                <a href="dashboard.php" class="nav-link text-white fw-normal">Dashboard</a>
            </li>
            <li class="nav-item mb-2">
                <a href="profile.php" class="nav-link text-white fw-normal">My Profile</a>
            </li>
            <li class="nav-item mb-2">
                <a href="courses.php" class="nav-link text-white fw-normal">My Courses</a>
            </li>
            <li class="nav-item mb-2">
                <a href="timetable.php" class="nav-link text-white fw-normal">Timetable</a>
            </li>
            <li class="nav-item mb-2">
                <a href="assignment.php" class="nav-link text-white fw-normal">Assignments</a>
            </li>
            <li class="nav-item mb-2">
                <a href="results.php" class="nav-link text-white fw-normal">My Results</a>
            </li>
            <li class="nav-item mb-2">
                <a href="notices.php" class="nav-link text-white fw-normal">Notices</a>
            </li>
            <li class="nav-item mt-3">
                <a href="logout.php" class="nav-link text-danger fw-normal">Logout</a>
            </li>
        </ul>
    </div>

    <div class="p-4" style="margin-left: 250px; width: calc(100% - 250px);">
        <h2 class="mb-4">Student Profile</h2>

        <?php if (!empty($success_msg)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($success_msg); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($error_msg)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($error_msg); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-md-6">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-primary text-white">
                        <h5 class="card-title mb-0">Personal Details</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="profile.php">
                            <div class="mb-3">
                                <label class="form-label text-muted">Roll Number</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($student['roll_number'] ?? 'N/A'); ?>" disabled>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Class</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($student['class'] ?? 'N/A'); ?>" disabled>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Full Name</label>
                                <input type="text" name="full_name" class="form-control" value="<?php echo htmlspecialchars($student['full_name'] ?? ''); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email Address</label>
                                <input type="email" name="email" class="form-control" value="<?php echo htmlspecialchars($student['email'] ?? ''); ?>" required>
                            </div>
                            <button type="submit" name="update_profile" class="btn btn-primary">Save Changes</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-dark text-white">
                        <h5 class="card-title mb-0">Change Password</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="profile.php">
                            <div class="mb-3">
                                <label class="form-label">Current Password</label>
                                <input type="password" name="current_password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">New Password</label>
                                <input type="password" name="new_password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                            <button type="submit" name="change_password" class="btn btn-warning">Update Password</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>