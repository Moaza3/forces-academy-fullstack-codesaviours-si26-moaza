<?php
session_start();
require_once "../config/db.php";

if (!isset($_SESSION["admin_id"]) || $_SESSION["admin_role"] !== "admin") {
    header("Location: login.php");
    exit;
}

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = trim($_POST["title"]);
    $content = trim($_POST["content"]);

    if (empty($title) || empty($content)) {
        $error = "All fields are required.";
    } else {
        $posted_by = $_SESSION["admin_username"];

        $sql = "INSERT INTO notices (title, content, posted_by) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sss", $title, $content, $posted_by);

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            header("Location: notices.php?success=1");
            exit;
        } else {
            $error = "Notice could not be posted.";
            mysqli_stmt_close($stmt);
        }
    }
}

if (isset($_GET["delete"])) {
    $notice_id = (int) $_GET["delete"];

    $sql = "DELETE FROM notices WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $notice_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header("Location: notices.php?deleted=1");
    exit;
}

if (isset($_GET["success"])) {
    $success = "Notice posted successfully.";
}

if (isset($_GET["deleted"])) {
    $success = "Notice deleted successfully.";
}

$sql = "SELECT id, title, content, posted_by, created_at
        FROM notices
        ORDER BY created_at DESC";

$result = mysqli_query($conn, $sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Notice</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
                    <a href="notices.php" class="nav-link text-white mb-2">Post Notice</a>
                    <a href="logout.php" class="nav-link text-danger">Logout</a>
                </div>
            </div>

            <div class="col-md-9 col-lg-10 p-4">
                <h2 class="mb-4">Post Notice</h2>

                <?php if ($error !== ""): ?>
                    <div class="alert alert-danger">
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <?php if ($success !== ""): ?>
                    <div class="alert alert-success">
                        <?php echo htmlspecialchars($success); ?>
                    </div>
                <?php endif; ?>

                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <h4 class="mb-3">Add New Notice</h4>

                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Title</label>
                                <input type="text" name="title" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Content</label>
                                <textarea name="content" class="form-control" rows="5" required></textarea>
                            </div>

                            <button type="submit" class="btn btn-primary">Post Notice</button>
                        </form>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <h4 class="mb-3">All Notices</h4>

                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while ($notice = mysqli_fetch_assoc($result)): ?>
                                <div class="card mb-3">
                                    <div class="card-body">
                                        <h5 class="card-title">
                                            <?php echo htmlspecialchars($notice["title"]); ?>
                                        </h5>

                                        <p class="card-text">
                                            <?php echo nl2br(htmlspecialchars($notice["content"])); ?>
                                        </p>

                                        <p class="text-muted mb-2">
                                            Posted by: <?php echo htmlspecialchars($notice["posted_by"]); ?>
                                            | <?php echo htmlspecialchars($notice["created_at"]); ?>
                                        </p>

                                        <a
                                            href="notices.php?delete=<?php echo $notice["id"]; ?>"
                                            class="btn btn-sm btn-danger"
                                            onclick="return confirm('Are you sure you want to delete this notice?');"
                                        >
                                            Delete
                                        </a>
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

</body>
</html>