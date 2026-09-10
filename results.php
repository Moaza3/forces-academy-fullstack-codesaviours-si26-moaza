<?php
session_start();

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit;
}

require_once "config/db.php";

$student_id = (int) $_SESSION['student_id'];

$sql = "SELECT
            results.subject,
            results.marks,
            results.total_marks,
            results.grade,
            results.exam_type,
            courses.course_name
        FROM results
        LEFT JOIN courses
        ON results.course_id = courses.id
        WHERE results.student_id = ?
        ORDER BY results.id DESC";

$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $student_id);
mysqli_stmt_execute($stmt);
$results = mysqli_stmt_get_result($stmt);

if (!$results) {
    die("Database Error: " . mysqli_error($conn));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Results | Student Portal</title>
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

        /* Card + Table */
        .card {
            border-radius: 14px !important;
            overflow: hidden;
        }

        .table-dark {
            --bs-table-bg: var(--green);
            --bs-table-color: #fff;
        }

        .table thead th {
            font-weight: 600;
            letter-spacing: 0.4px;
            text-transform: uppercase;
            font-size: 0.85rem;
            border: none;
        }

        .table-hover tbody tr:hover {
            background-color: rgba(255, 239, 179, 0.25);
        }

        .table tbody td {
            vertical-align: middle;
            padding-top: 14px;
            padding-bottom: 14px;
        }

        /* Grade badges recolored to theme where it makes sense */
        .badge {
            font-size: 0.8rem;
            padding: 6px 10px;
            border-radius: 6px;
        }

        .badge.bg-success {
            background-color: var(--green) !important;
        }

        .badge.bg-primary {
            background-color: #2f6f63 !important;
        }

        .badge.bg-warning {
            background-color: var(--butter) !important;
            color: var(--text-dark) !important;
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
        <h2 class="mb-4">My Results</h2>

        <?php if (mysqli_num_rows($results) > 0): ?>

            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th class="ps-3">Subject</th>
                                    <th>Course</th>
                                    <th>Marks Obtained</th>
                                    <th>Total Marks</th>
                                    <th>Grade</th>
                                    <th>Exam Type</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($row = mysqli_fetch_assoc($results)): ?>
                                    <?php
                                    $grade = strtoupper(trim($row['grade']));
                                    $badge_class = 'bg-secondary';
                                    
                                    if (in_array($grade, ['A+', 'A', 'A-'])) {
                                        $badge_class = 'bg-success';
                                    } elseif (in_array($grade, ['B+', 'B', 'B-'])) {
                                        $badge_class = 'bg-primary';
                                    } elseif (in_array($grade, ['C+', 'C', 'C-'])) {
                                        $badge_class = 'bg-info text-dark';
                                    } elseif (in_array($grade, ['D+', 'D'])) {
                                        $badge_class = 'bg-warning text-dark';
                                    } elseif ($grade === 'F') {
                                        $badge_class = 'bg-danger';
                                    }
                                    ?>
                                    <tr>
                                        <td class="ps-3 fw-semibold"><?php echo htmlspecialchars($row['subject']); ?></td>
                                        <td><?php echo htmlspecialchars($row['course_name'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($row['marks']); ?></td>
                                        <td><?php echo htmlspecialchars($row['total_marks']); ?></td>
                                        <td>
                                            <span class="badge <?php echo $badge_class; ?>">
                                                <?php echo htmlspecialchars($row['grade']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars($row['exam_type']); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        <?php else: ?>

            <div class="alert alert-info border-0 shadow-sm">
                <h5 class="alert-heading">No Results Available</h5>
                <p class="mb-0">Your results have not been added yet.</p>
            </div>

        <?php endif; ?>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"></script>
</body>
</html>