<?php
session_start();

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit;
}

require_once "config/db.php";

$student_id = (int) $_SESSION['student_id'];

/* Get Assignment ID */
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: assignment.php");
    exit;
}

$assignment_id = (int) $_GET['id'];

/* Get Assignment Details using Prepared Statement */
$sql = "SELECT
            assignment.id,
            assignment.title,
            assignment.description,
            assignment.due_date,
            courses.course_name
        FROM assignment
        LEFT JOIN courses
        ON assignment.course_id = courses.id
        WHERE assignment.id = ?
        LIMIT 1";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $assignment_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result) {
    die("Database Error: " . mysqli_error($conn));
}

if (mysqli_num_rows($result) == 0) {
    die("Assignment not found.");
}

$assignment = mysqli_fetch_assoc($result);

/* Check If Student Already Submitted */
$check_stmt = mysqli_prepare($conn, "SELECT id FROM submissions WHERE assignment_id = ? AND student_id = ? LIMIT 1");
mysqli_stmt_bind_param($check_stmt, "ii", $assignment_id, $student_id);
mysqli_stmt_execute($check_stmt);
$check_result = mysqli_stmt_get_result($check_stmt);

if (!$check_result) {
    die("Submission Check Error: " . mysqli_error($conn));
}

if (mysqli_num_rows($check_result) > 0) {
    die("You have already submitted this assignment.");
}

/* Submit Assignment */
if (isset($_POST['submit_assignment'])) {
    if (!isset($_FILES['assignment_file'])) {
        $error = "Please select a file.";
    } else {
        $file = $_FILES['assignment_file'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $error = "File upload failed.";
        } else {
            /* Allowed File Extensions & MIME Types */
            $allowed_extensions = ["pdf", "jpg", "jpeg", "png", "gif", "webp"];
            $allowed_mimes = ["application/pdf", "image/jpeg", "image/png", "image/gif", "image/webp"];

            /* Get File Extension & MIME type */
            $original_name = basename($file['name']);
            $file_extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);

            /* Validate File Extension & MIME Type */
            if (!in_array($file_extension, $allowed_extensions) || !in_array($mime_type, $allowed_mimes)) {
                $error = "Only PDF and image files are allowed.";
            } else {
                $upload_folder = "uploads/";

                /* Create uploads Folder If It Doesn't Exist */
                if (!is_dir($upload_folder)) {
                    if (!mkdir($upload_folder, 0755, true)) {
                        $error = "Could not create uploads folder.";
                    }
                }

                /* Continue If No Error */
                if (!isset($error)) {
                    /* Create Unique Filename */
                    $unique_filename = $student_id . "_" . $assignment_id . "_" . time() . "_" . bin2hex(random_bytes(4)) . "." . $file_extension;
                    $file_path = $upload_folder . $unique_filename;

                    /* Move Uploaded File */
                    if (move_uploaded_file($file['tmp_name'], $file_path)) {
                        /* Insert Submission Into Database via Prepared Statement */
                        $insert_sql = "INSERT INTO submissions (assignment_id, student_id, file_path, status) VALUES (?, ?, ?, 'submitted')";
                        $insert_stmt = mysqli_prepare($conn, $insert_sql);
                        mysqli_stmt_bind_param($insert_stmt, "iis", $assignment_id, $student_id, $file_path);

                        if (mysqli_stmt_execute($insert_stmt)) {
                            $success = "Assignment submitted successfully!";
                        } else {
                            /* Delete Uploaded File If Database Insert Fails */
                            if (file_exists($file_path)) {
                                unlink($file_path);
                            }
                            $error = "Database Error: " . mysqli_error($conn);
                        }
                    } else {
                        $error = "Could not upload the file.";
                    }
                }
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
    <title>Submit Assignment | Student Portal</title>
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

        .btn-success {
            background-color: var(--green) !important;
            border-color: var(--green) !important;
        }

        .btn-success:hover {
            background-color: var(--green-dark) !important;
            border-color: var(--green-dark) !important;
        }

        .btn-warning {
            background-color: var(--butter) !important;
            border-color: var(--butter) !important;
            color: var(--text-dark) !important;
        }

        .btn-warning:hover {
            background-color: #ffe58f !important;
            border-color: #ffe58f !important;
        }

        .btn-info {
            background-color: var(--butter) !important;
            border-color: var(--butter) !important;
            color: var(--text-dark) !important;
        }

        .btn-info:hover {
            background-color: #ffe58f !important;
            border-color: #ffe58f !important;
            color: var(--text-dark) !important;
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

        .alert-info {
            background-color: var(--butter);
            border: none;
            border-radius: 12px;
            color: var(--text-dark);
        }

        .alert-info .alert-heading {
            color: var(--green-dark);
            font-weight: 700;
        }

        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--green);
            border-radius: 4px;
        }

        h4.text-primary {
            color: var(--green) !important;
            font-weight: 700;
        }

        .border-bottom {
            border-bottom: 2px solid var(--butter) !important;
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
        }
    </style>

</head>
<body class="bg-light">

    <button class="mobile-toggle-btn" onclick="document.getElementById('appSidebar').classList.toggle('sidebar-open'); document.getElementById('sidebarOverlay').classList.toggle('show');">&#9776;</button>
    <div class="sidebar-overlay" id="sidebarOverlay" onclick="document.getElementById('appSidebar').classList.remove('sidebar-open'); this.classList.remove('show');"></div>

    <div class="bg-dark text-white p-3 vh-100" id="appSidebar" style="width: 250px; position: fixed; left: 0; top: 0;">
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
                <a href="assignment.php" class="nav-link text-white fw-normal active">Assignments</a>
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

    <div class="p-4" id="mainContent" style="margin-left: 250px; width: calc(100% - 250px);">
        <div class="row justify-content-center">
            <div class="col-md-9 col-lg-8">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h2 class="mb-4">Submit Assignment</h2>

                        <div class="mb-4 pb-3 border-bottom">
                            <h4 class="text-primary"><?php echo htmlspecialchars($assignment['title']); ?></h4>
                            <p class="mb-1"><strong>Course:</strong> <?php echo htmlspecialchars($assignment['course_name'] ?? 'N/A'); ?></p>
                            <p class="mb-1"><strong>Due Date:</strong> <?php echo date("d M Y", strtotime($assignment['due_date'])); ?></p>
                            <p class="mb-0 text-muted"><strong>Description:</strong> <?php echo htmlspecialchars($assignment['description']); ?></p>
                        </div>

                        <?php if (isset($success)): ?>
                            <div class="alert alert-success border-0 shadow-sm">
                                <?php echo htmlspecialchars($success); ?>
                                <div class="mt-3">
                                    <a href="assignment.php" class="btn btn-success">Back to Assignments</a>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger border-0 shadow-sm">
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!isset($success)): ?>
                            <form method="POST" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label for="assignment_file" class="form-label font-weight-bold">Select Assignment File</label>
                                    <input
                                        type="file"
                                        name="assignment_file"
                                        id="assignment_file"
                                        class="form-control"
                                        accept=".pdf,.jpg,.jpeg,.png,.gif,.webp"
                                        required
                                    >
                                    <small class="text-muted d-block mt-1">Allowed formats: PDF, JPG, JPEG, PNG, GIF, WEBP</small>
                                </div>

                                <div class="d-flex gap-2">
                                    <button type="submit" name="submit_assignment" class="btn btn-primary">
                                        Submit Assignment
                                    </button>
                                    <a href="assignment.php" class="btn btn-outline-secondary">Back</a>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>