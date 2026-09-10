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
            background-color: #f4f6f5 !important;
            font-family: 'Segoe UI', 'Poppins', sans-serif;
        }

        /* Sidebar */
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

        .bg-dark .nav-link.text-danger {
            color: #ff6b6b !important;
        }

        .bg-dark .nav-link.text-danger:hover {
            background-color: rgba(255, 107, 107, 0.15);
            color: #ff8787 !important;
        }

        /* Card */
        .card {
            border-radius: 16px !important;
        }

        h2.mb-4 {
            color: var(--green);
            font-weight: 700;
        }

        h4.text-primary {
            color: var(--green) !important;
            font-weight: 700;
        }

        .border-bottom {
            border-bottom: 2px solid var(--butter) !important;
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

        .btn-primary {
            background-color: var(--green) !important;
            border-color: var(--green) !important;
            border-radius: 8px !important;
            font-weight: 600;
        }

        .btn-primary:hover {
            background-color: var(--green-dark) !important;
            border-color: var(--green-dark) !important;
        }

        .btn-success {
            background-color: var(--green) !important;
            border-color: var(--green) !important;
            border-radius: 8px !important;
        }

        .btn-success:hover {
            background-color: var(--green-dark) !important;
            border-color: var(--green-dark) !important;
        }

        .btn-outline-secondary {
            border-radius: 8px !important;
        }

        .alert-success {
            border-left: 4px solid var(--green);
            border-radius: 10px;
        }

        .alert-danger {
            border-radius: 10px;
        }
    </style>
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