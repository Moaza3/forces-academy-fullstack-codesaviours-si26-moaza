<?php
session_start();

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

require_once "config/db.php";

$sql = "SELECT id, title, content, posted_by, created_at FROM notices ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);

if (!$result) {
    die("Database query failed: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notice Board | Student Portal</title>
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

        /* Heading */
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

        /* Notice Cards */
        .card {
            border-radius: 14px !important;
            border-left: 5px solid var(--green) !important;
            transition: box-shadow 0.25s ease;
        }

        .card:hover {
            box-shadow: 0 8px 20px rgba(1, 62, 55, 0.12) !important;
        }

        .card-title.text-primary {
            color: var(--green) !important;
            font-weight: 700;
        }

        .badge.bg-light {
            background-color: var(--butter) !important;
            color: var(--text-dark) !important;
            border-color: var(--butter) !important;
            font-weight: 500;
        }

        .card-text.text-secondary {
            color: #555 !important;
        }

        .border-top {
            border-top: 1px solid #eee !important;
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
        <h2 class="mb-4">Notice Board</h2>

        <?php if (mysqli_num_rows($result) > 0): ?>

            <div class="row">
                <?php while ($notice = mysqli_fetch_assoc($result)): ?>
                    <div class="col-12 mb-3">
                        <div class="card shadow-sm border-0">
                            <div class="card-body p-4">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h5 class="card-title text-primary mb-0">
                                        <?php echo htmlspecialchars($notice['title']); ?>
                                    </h5>
                                    <span class="badge bg-light text-dark border">
                                        <?php echo date('d M Y, h:i A', strtotime($notice['created_at'])); ?>
                                    </span>
                                </div>
                                <p class="card-text text-secondary mb-3" style="white-space: pre-line;">
                                    <?php echo htmlspecialchars($notice['content']); ?>
                                </p>
                                <div class="border-top pt-2 text-muted small">
                                    <strong>Posted by:</strong> <?php echo htmlspecialchars($notice['posted_by'] ?? 'Administration'); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>

        <?php else: ?>

            <div class="alert alert-info border-0 shadow-sm">
                <h5 class="alert-heading">No Notices Available</h5>
                <p class="mb-0">There are no notices available at the moment. Please check again later.</p>
            </div>

        <?php endif; ?>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>