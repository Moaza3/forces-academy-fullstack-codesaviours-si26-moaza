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

/* Get Assignment Details */
$sql = "SELECT
            assignment.id,
            assignment.title,
            assignment.description,
            assignment.due_date,
            courses.course_name
        FROM assignment
        LEFT JOIN courses
        ON assignment.course_id = courses.id
        WHERE assignment.id = $assignment_id
        LIMIT 1";

$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Database Error: " . mysqli_error($conn));
}

if (mysqli_num_rows($result) == 0) {
    die("Assignment not found.");
}

$assignment = mysqli_fetch_assoc($result);

/* Check If Student Already Submitted */
$check_sql = "SELECT id
              FROM submissions
              WHERE assignment_id = $assignment_id
              AND student_id = $student_id
              LIMIT 1";

$check_result = mysqli_query($conn, $check_sql);

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
            /* Allowed File Types */
            $allowed_extensions = ["pdf", "jpg", "jpeg", "png", "gif", "webp"];

            /* Get File Extension */
            $original_name = basename($file['name']);
            $file_extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));

            /* Validate File Extension */
            if (!in_array($file_extension, $allowed_extensions)) {
                $error = "Only PDF and image files are allowed.";
            } else {
                $upload_folder = "uploads/";

                /* Create uploads Folder If It Doesn't Exist */
                if (!is_dir($upload_folder)) {
                    if (!mkdir($upload_folder, 0777, true)) {
                        $error = "Could not create uploads folder.";
                    }
                }

                /* Continue If No Error */
                if (!isset($error)) {
                    /* Create Unique Filename */
                    $unique_filename = $student_id . "_" . $assignment_id . "_" . time() . "_" . uniqid() . "." . $file_extension;
                    $file_path = $upload_folder . $unique_filename;

                    /* Move Uploaded File */
                    if (move_uploaded_file($file['tmp_name'], $file_path)) {
                        /* Insert Submission Into Database */
                        $insert_sql = "INSERT INTO submissions
                                       (assignment_id, student_id, file_path, status)
                                       VALUES
                                       ($assignment_id, $student_id, '$file_path', 'submitted')";

                        if (mysqli_query($conn, $insert_sql)) {
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
    <title>Submit Assignment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-7">
                <div class="card shadow">
                    <div class="card-body">
                        <h2 class="mb-4">Submit Assignment</h2>

                        <!-- Assignment Title -->
                        <h5>
                            <?php echo htmlspecialchars($assignment['title']); ?>
                        </h5>

                        <!-- Course -->
                        <p>
                            <strong>Course:</strong>
                            <?php echo htmlspecialchars($assignment['course_name'] ?? 'N/A'); ?>
                        </p>

                        <!-- Due Date -->
                        <p>
                            <strong>Due Date:</strong>
                            <?php echo date("d M Y", strtotime($assignment['due_date'])); ?>
                        </p>

                        <!-- Description -->
                        <p>
                            <strong>Description:</strong>
                            <?php echo htmlspecialchars($assignment['description']); ?>
                        </p>

                        <!-- Success Message -->
                        <?php if (isset($success)): ?>
                            <div class="alert alert-success">
                                <?php echo htmlspecialchars($success); ?>
                                <br><br>
                                <a href="assignment.php" class="btn btn-success">Back to Assignments</a>
                            </div>
                        <?php endif; ?>

                        <!-- Error Message -->
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger">
                                <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>

                        <!-- Upload Form -->
                        <?php if (!isset($success)): ?>
                            <form method="POST" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label for="assignment_file" class="form-label">Select Assignment File</label>
                                    <input
                                        type="file"
                                        name="assignment_file"
                                        id="assignment_file"
                                        class="form-control"
                                        accept=".pdf,.jpg,.jpeg,.png,.gif,.webp"
                                        required
                                    >
                                    <small class="text-muted">Allowed: PDF, JPG, JPEG, PNG, GIF, WEBP</small>
                                </div>

                                <button type="submit" name="submit_assignment" class="btn btn-primary">
                                    Submit Assignment
                                </button>

                                <a href="assignment.php" class="btn btn-secondary">Back</a>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>