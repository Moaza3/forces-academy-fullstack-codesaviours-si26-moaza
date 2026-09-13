<?php
session_start();
require_once "../config/db.php";

if (!isset($_SESSION["admin_id"]) || $_SESSION["admin_role"] !== "admin") {
    header("Location: login.php");
    exit;
}

$error = "";
$success = "";
$title_val = "";
$content_val = "";

// Handle Notice Posting
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["post_notice"])) {
    $title = trim($_POST["title"] ?? "");
    $content = trim($_POST["content"] ?? "");
    $title_val = $title;
    $content_val = $content;

    if (empty($title) || empty($content)) {
        $error = "All fields are required.";
    } else {
        $posted_by = $_SESSION["admin_username"] ?? "Admin";

        $sql = "INSERT INTO notices (title, content, posted_by) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "sss", $title, $content, $posted_by);

            if (mysqli_stmt_execute($stmt)) {
                mysqli_stmt_close($stmt);
                header("Location: notices.php?success=1");
                exit;
            } else {
                $error = "Notice could not be posted. Please try again.";
                mysqli_stmt_close($stmt);
            }
        } else {
            $error = "Database error. Please try again.";
        }
    }
}

// Handle Notice Deletion (POST Method for Security)
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["delete_notice"])) {
    $notice_id = (int) ($_POST["notice_id"] ?? 0);

    if ($notice_id > 0) {
        $sql = "DELETE FROM notices WHERE id = ?";
        $stmt = mysqli_prepare($conn, $sql);
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $notice_id);
            mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            header("Location: notices.php?deleted=1");
            exit;
        }
    }
}

if (isset($_GET["success"])) {
    $success = "Notice posted successfully.";
}

if (isset($_GET["deleted"])) {
    $success = "Notice deleted successfully.";
}

$sql = "SELECT id, title, content, posted_by, created_at FROM notices ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Notice</title>
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
            border: none !important;
            border-radius: 14px !important;
            overflow: hidden;
        }

        .card-title {
            color: var(--green);
            font-weight: 700;
        }

        .card-body h4 {
            color: var(--green);
            font-weight: 700;
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

        .btn-danger {
            border-radius: 8px !important;
            font-weight: 600;
        }

        .btn-sm {
            border-radius: 6px !important;
        }

        .alert-info {
            background-color: var(--butter);
            border: none;
            border-radius: 10px;
            color: var(--text-dark);
        }

        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--green);
            border-radius: 4px;
        }
    </style>
</head>
<body class="bg-light">

    <div class="container-fluid">
        <div class="row">

            <div class="col-md-3 col-lg-2 bg-dark text-white min-vh-100 p-3">
                <h4 class="text-center mb-4">Admin Panel</h4>

                <div class="nav flex-column">
                    <a href="dashboard.php" class="nav-link text-white mb-2">Dashboard</a>
                    <a href="students.php" class="nav-link text-white mb-2">Manage Students</a>
                    <a href="courses.php" class="nav-link text-white mb-2">Manage Courses</a>
                    <a href="assignments.php" class="nav-link text-white mb-2">Manage Assignments</a>
                    <a href="results.php" class="nav-link text-white mb-2">Upload Results</a>
                    <a href="notices.php" class="nav-link text-white mb-2 fw-bold active">Post Notice</a>
                    <a href="logout.php" class="nav-link text-danger">Logout</a>
                </div>
            </div>

            <div class="col-md-9 col-lg-10 p-4">
                <h2 class="mb-4">Post Notice</h2>

                <?php if ($error !== ""): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($error); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if ($success !== ""): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <?php echo htmlspecialchars($success); ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h4 class="mb-3">Add New Notice</h4>

                        <form method="POST" action="">
                            <input type="hidden" name="post_notice" value="1">
                            <div class="mb-3">
                                <label class="form-label" for="title">Title</label>
                                <input type="text" id="title" name="title" class="form-control" value="<?php echo htmlspecialchars($title_val); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="content">Content</label>
                                <textarea id="content" name="content" class="form-control" rows="5" required><?php echo htmlspecialchars($content_val); ?></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary">Post Notice</button>
                        </form>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <h4 class="mb-3">All Notices</h4>

                        <?php if ($result && mysqli_num_rows($result) > 0): ?>
                            <?php while ($notice = mysqli_fetch_assoc($result)): ?>
                                <div class="card mb-3 border shadow-sm">
                                    <div class="card-body">
                                        <h5 class="card-title">
                                            <?php echo htmlspecialchars($notice["title"]); ?>
                                        </h5>

                                        <p class="card-text">
                                            <?php echo nl2br(htmlspecialchars($notice["content"])); ?>
                                        </p>

                                        <p class="text-muted mb-3 small">
                                            Posted by: <strong><?php echo htmlspecialchars($notice["posted_by"]); ?></strong> | 
                                            <?php echo date("d M Y, h:i A", strtotime($notice["created_at"])); ?>
                                        </p>

                                        <form method="POST" action="" onsubmit="return confirm('Are you sure you want to delete this notice?');">
                                            <input type="hidden" name="notice_id" value="<?php echo (int) $notice["id"]; ?>">
                                            <button type="submit" name="delete_notice" class="btn btn-sm btn-danger">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="alert alert-info">No notices found.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>