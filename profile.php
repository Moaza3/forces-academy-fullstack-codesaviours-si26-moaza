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
mysqli_stmt_close($stmt);

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
        mysqli_stmt_close($update_stmt);
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
            mysqli_stmt_close($pass_stmt);
        } else {
            $error_msg = "Incorrect current password.";
        }
    }
}

$initial = strtoupper(substr($student['full_name'] ?? 'S', 0, 1));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | Student Portal</title>
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
        }

        .bg-dark .nav-link:hover {
            background-color: rgba(255, 239, 179, 0.15);
            color: var(--butter) !important;
            padding-left: 20px;
        }

        .bg-dark .nav-link.active {
            background-color: var(--butter) !important;
            color: var(--green-dark) !important;
            font-weight: 400 !important;
        }

        .bg-dark .nav-link.active:hover {
            background-color: var(--butter) !important;
            color: var(--green-dark) !important;
        }

        .bg-dark .nav-link:focus,
        .bg-dark .nav-link:active {
            color: #fff !important;
        }

        .bg-dark .nav-link.active:focus,
        .bg-dark .nav-link.active:active {
            color: var(--green-dark) !important;
        }

        .bg-dark .nav-link.text-danger {
            color: #ff6b6b !important;
        }

        .bg-dark .nav-link.text-danger:hover {
            background-color: rgba(255, 107, 107, 0.15);
            color: #ff8787 !important;
        }

        h2 {
            color: var(--green);
            font-weight: 700;
            position: relative;
            padding-bottom: 10px;
        }

        h2::after {
            content: "";
            position: absolute;
            left: 0;
            bottom: 0;
            width: 60px;
            height: 4px;
            background-color: var(--butter);
            border-radius: 2px;
        }

        h4 {
            color: var(--green);
            font-weight: 700;
        }

        hr {
            border-top: 2px solid var(--butter);
            opacity: 1;
        }

        .card {
            border-radius: 14px !important;
            overflow: hidden;
        }

        .form-label {
            color: var(--green);
            font-weight: 600;
        }

        .form-control {
            border-radius: 8px;
        }

        .form-control:focus {
            border-color: var(--green);
            box-shadow: 0 0 0 0.2rem rgba(1, 62, 55, 0.15);
        }

        .btn {
            border-radius: 8px !important;
            font-weight: 600;
        }

        .btn-primary {
            background-color: var(--green) !important;
            border-color: var(--green) !important;
        }

        .btn-primary:hover {
            background-color: var(--green-dark) !important;
            border-color: var(--green-dark) !important;
        }

        .btn-outline-secondary {
            border-radius: 8px !important;
        }

        .alert-success {
            border-radius: 10px;
            border-left: 4px solid var(--green);
        }

        .alert-danger {
            border-radius: 10px;
        }

        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--green);
            border-radius: 4px;
        }

        /* Profile header banner */
        .profile-banner {
            background: linear-gradient(135deg, var(--green) 0%, var(--green-dark) 100%);
            border-radius: 16px;
            padding: 30px 32px;
            color: white;
            display: flex;
            align-items: center;
            gap: 22px;
            box-shadow: 0 8px 20px rgba(1, 62, 55, 0.18);
        }

        .profile-avatar {
            width: 76px;
            height: 76px;
            min-width: 76px;
            border-radius: 50%;
            background: var(--butter);
            color: var(--green-dark);
            font-size: 32px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 3px solid rgba(255,255,255,0.3);
        }

        .profile-banner h3 {
            margin: 0;
            font-weight: 700;
        }

        .profile-banner p {
            margin: 0;
            opacity: 0.85;
            font-size: 0.92rem;
        }

        /* Read-only info rows */
        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 13px 0;
            border-bottom: 1px solid #eef1f0;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .info-label {
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            color: #8a9a97;
        }

        .info-value {
            font-weight: 600;
            color: var(--text-dark);
            text-align: right;
        }

        .section-card-header {
            padding: 18px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            border-bottom: 1px solid #eef1f0;
        }

        .section-card-header h5 {
            margin: 0;
            color: var(--green);
            font-weight: 700;
        }

        .toggle-btn {
            background: transparent;
            border: 1px solid var(--green);
            color: var(--green);
            border-radius: 8px;
            padding: 6px 14px;
            font-weight: 600;
            font-size: 0.88rem;
            transition: all 0.2s ease;
        }

        .toggle-btn:hover {
            background: var(--green);
            color: white;
        }

        .mobile-toggle-btn {
            display: none;
            position: fixed;
            top: 15px;
            left: 15px;
            z-index: 1050;
            background: var(--green);
            color: #fff;
            border: none;
            border-radius: 8px;
            width: 44px;
            height: 44px;
            font-size: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.25);
            cursor: pointer;
        }

        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1030;
        }

        .sidebar-overlay.show {
            display: block;
        }

        @media (max-width: 991px) {
            .mobile-toggle-btn {
                display: block;
            }

            #appSidebar {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
                z-index: 1040;
            }

            #appSidebar.sidebar-open {
                transform: translateX(0);
            }

            #mainContent {
                margin-left: 0 !important;
                width: 100% !important;
                padding-top: 75px !important;
            }

            .profile-banner {
                flex-direction: column;
                text-align: center;
            }

            .info-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 4px;
            }

            .info-value {
                text-align: left;
            }
        }
    </style>

</head>
<body class="bg-light">

    <button class="mobile-toggle-btn" onclick="document.getElementById('appSidebar').classList.toggle('sidebar-open'); document.getElementById('sidebarOverlay').classList.toggle('show');">&#9776;</button>
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="document.getElementById('appSidebar').classList.remove('sidebar-open'); this.classList.remove('show');"></div>

    <div class="bg-dark text-white p-3 vh-100" id="appSidebar" style="width: 250px; position: fixed; left: 0; top: 0;">
        <h4 class="text-center mb-4">Student Portal</h4>
        <ul class="nav flex-column">
            <li class="nav-item mb-2"><a href="dashboard.php" class="nav-link text-white fw-normal">Dashboard</a></li>
            <li class="nav-item mb-2"><a href="profile.php" class="nav-link text-white fw-normal active">My Profile</a></li>
            <li class="nav-item mb-2"><a href="courses.php" class="nav-link text-white fw-normal">My Courses</a></li>
            <li class="nav-item mb-2"><a href="timetable.php" class="nav-link text-white fw-normal">Timetable</a></li>
            <li class="nav-item mb-2"><a href="assignment.php" class="nav-link text-white fw-normal">Assignments</a></li>
            <li class="nav-item mb-2"><a href="results.php" class="nav-link text-white fw-normal">My Results</a></li>
            <li class="nav-item mb-2"><a href="notices.php" class="nav-link text-white fw-normal">Notices</a></li>
            <li class="nav-item mb-2"><a href="fees.php" class="nav-link text-white fw-normal">My Fees</a></li>
            <li class="nav-item mt-3"><a href="logout.php" class="nav-link text-danger fw-normal">Logout</a></li>
        </ul>
    </div>

    <div class="p-4" id="mainContent" style="margin-left: 250px; width: calc(100% - 250px);">
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

        <div class="profile-banner mb-4">
            <div class="profile-avatar"><?php echo htmlspecialchars($initial); ?></div>
            <div>
                <h3><?php echo htmlspecialchars($student['full_name'] ?? 'Student'); ?></h3>
                <p>Roll No: <?php echo htmlspecialchars($student['roll_number'] ?? 'N/A'); ?> &nbsp;•&nbsp; Class: <?php echo htmlspecialchars($student['class'] ?? 'N/A'); ?></p>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="section-card-header">
                        <h5>Personal Details</h5>
                        <button type="button" class="toggle-btn" data-bs-toggle="collapse" data-bs-target="#editProfileForm">
                            Edit Profile
                        </button>
                    </div>

                    <div class="card-body">

                        <div class="info-row">
                            <span class="info-label">Roll Number</span>
                            <span class="info-value"><?php echo htmlspecialchars($student['roll_number'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Class</span>
                            <span class="info-value"><?php echo htmlspecialchars($student['class'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Full Name</span>
                            <span class="info-value"><?php echo htmlspecialchars($student['full_name'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="info-row">
                            <span class="info-label">Email Address</span>
                            <span class="info-value"><?php echo htmlspecialchars($student['email'] ?? 'N/A'); ?></span>
                        </div>

                        <div class="collapse mt-4" id="editProfileForm">
                            <hr>
                            <form method="POST" action="profile.php">
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
            </div>

            <div class="col-lg-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="section-card-header">
                        <h5>Security</h5>
                        <button type="button" class="toggle-btn" data-bs-toggle="collapse" data-bs-target="#changePasswordForm">
                            Change Password
                        </button>
                    </div>

                    <div class="card-body">

                        <div class="info-row">
                            <span class="info-label">Password</span>
                            <span class="info-value">••••••••</span>
                        </div>
                        <p class="text-muted mt-3 mb-0" style="font-size: 0.9rem;">
                            Keep your account secure by using a strong password and updating it regularly.
                        </p>

                        <!-- Collapsible Password Form -->
                        <div class="collapse mt-4" id="changePasswordForm">
                            <hr>
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
                                <button type="submit" name="change_password" class="btn btn-primary">Update Password</button>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>