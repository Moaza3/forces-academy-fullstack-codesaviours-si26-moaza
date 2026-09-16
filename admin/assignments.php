<?php
session_start();
require_once "../config/db.php";

if (!isset($_SESSION["admin_id"]) || $_SESSION["admin_role"] !== "admin") {
    header("Location: login.php");
    exit;
}

if (isset($_POST['add_assignment'])) {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $course_id = (int) $_POST['course_id'];
    $due_date = $_POST['due_date'];

    $sql = "INSERT INTO assignment (title, description, course_id, due_date)
            VALUES ('$title', '$description', $course_id, '$due_date')";

    mysqli_query($conn, $sql);

    header("Location: assignments.php");
    exit();
}

if (isset($_POST['update_assignment'])) {
    $id = (int) $_POST['assignment_id'];
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $description = mysqli_real_escape_string($conn, $_POST['description']);
    $course_id = (int) $_POST['course_id'];
    $due_date = $_POST['due_date'];

    $sql = "UPDATE assignment SET
            title='$title',
            description='$description',
            course_id=$course_id,
            due_date='$due_date'
            WHERE id=$id";

    mysqli_query($conn, $sql);

    header("Location: assignments.php");
    exit();
}

if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];

    mysqli_query($conn, "DELETE FROM assignment WHERE id = $id");

    header("Location: assignments.php");
    exit();
}

$edit_data = null;
if (isset($_GET['edit'])) {
    $edit_id = (int) $_GET['edit'];
    $edit_query = mysqli_query($conn, "SELECT * FROM assignment WHERE id = $edit_id");
    $edit_data = mysqli_fetch_assoc($edit_query);
}

$courses = mysqli_query($conn, "SELECT id, course_name FROM courses ORDER BY course_name ASC");

$assignments = mysqli_query($conn, "
    SELECT assignment.*, courses.course_name
    FROM assignment
    LEFT JOIN courses ON assignment.course_id = courses.id
    ORDER BY assignment.id DESC
");
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Assignments</title>
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

        form {
            background: white;
            padding: 25px;
            margin-bottom: 30px;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
        }

        label {
            font-weight: 600;
            color: var(--green);
        }

        input,
        textarea,
        select {
            width: 100%;
            padding: 10px;
            margin: 8px 0 15px;
            box-sizing: border-box;
            border: 1px solid #ddd;
            border-radius: 8px;
        }

        input:focus,
        textarea:focus,
        select:focus {
            outline: none;
            border-color: var(--green);
            box-shadow: 0 0 0 3px rgba(1, 62, 55, 0.12);
        }

        button[type="submit"] {
            padding: 10px 20px;
            background: var(--green);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
        }

        button[type="submit"]:hover {
            background: var(--green-dark);
        }

        .cancel-btn {
            background: #6c757d;
            text-decoration: none;
            padding: 10px 20px;
            color: white;
            display: inline-block;
            margin-left: 10px;
            border-radius: 8px;
            font-weight: 600;
        }

        .cancel-btn:hover {
            color: white;
            background: #5a6268;
        }

        .table-wrap {
            overflow-x: auto;
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.06);
            padding: 15px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            min-width: 700px;
        }

        th,
        td {
            padding: 12px;
            border-bottom: 1px solid #eee;
            text-align: left;
        }

        th {
            background: var(--green);
            color: white;
        }

        tr:hover td {
            background: rgba(255, 239, 179, 0.2);
        }

        table a {
            text-decoration: none;
            margin-right: 10px;
            font-weight: 600;
        }

        .delete {
            color: #dc3545;
        }

        .edit {
            color: var(--green);
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

            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 bg-dark text-white min-vh-100 p-3">
                <h4 class="text-center mb-4">Admin Panel</h4>

                <div class="nav flex-column">
                    <a href="dashboard.php" class="nav-link text-white mb-2">Dashboard</a>
                    <a href="students.php" class="nav-link text-white mb-2">Manage Students</a>
                    <a href="courses.php" class="nav-link text-white mb-2">Manage Courses</a>
                    <a href="assignments.php" class="nav-link text-white mb-2 active" style="background-color: rgba(255, 239, 179, 0.15); color: var(--butter) !important;">Manage Assignments</a>
                    <a href="results.php" class="nav-link text-white mb-2">Upload Results</a>
                    <a href="notices.php" class="nav-link text-white mb-2">Post Notice</a>
                    <a href="fees.php" class="nav-link text-white mb-2">Manage Fees</a>
                    <a href="logout.php" class="nav-link text-danger">Logout</a>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 p-4">
                <h2 class="mb-4">Manage Assignments</h2>

                <h4 class="mb-3 text-secondary" style="font-size: 1.25rem; font-weight: 600;">
                    <?php echo $edit_data ? 'Edit Assignment' : 'Add New Assignment'; ?>
                </h4>

                <form method="POST">

                    <?php if ($edit_data) { ?>
                        <input type="hidden" name="assignment_id" value="<?php echo $edit_data['id']; ?>">
                    <?php } ?>

                    <label>Title</label>
                    <input type="text" name="title" value="<?php echo $edit_data ? htmlspecialchars($edit_data['title']) : ''; ?>" required>

                    <label>Description</label>
                    <textarea name="description" rows="4" required><?php echo $edit_data ? htmlspecialchars($edit_data['description']) : ''; ?></textarea>

                    <label>Select Course</label>
                    <select name="course_id" required>
                        <option value="">-- Select Course --</option>

                        <?php
                        mysqli_data_seek($courses, 0);
                        while ($course = mysqli_fetch_assoc($courses)) {
                            $selected = ($edit_data && $edit_data['course_id'] == $course['id']) ? 'selected' : '';
                        ?>
                            <option value="<?php echo $course['id']; ?>" <?php echo $selected; ?>>
                                <?php echo htmlspecialchars($course['course_name']); ?>
                            </option>
                        <?php } ?>

                    </select>

                    <label>Due Date</label>
                    <input type="date" name="due_date" value="<?php echo $edit_data ? $edit_data['due_date'] : ''; ?>" required>

                    <?php if ($edit_data) { ?>
                        <button type="submit" name="update_assignment">Update Assignment</button>
                        <a href="assignments.php" class="cancel-btn">Cancel</a>
                    <?php } else { ?>
                        <button type="submit" name="add_assignment">Add Assignment</button>
                    <?php } ?>

                </form>

                <h4 class="mb-3 text-secondary" style="font-size: 1.25rem; font-weight: 600;">All Assignments</h4>

                <div class="table-wrap">
                    <table>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Description</th>
                            <th>Course</th>
                            <th>Due Date</th>
                            <th>Actions</th>
                        </tr>

                        <?php while ($assignment = mysqli_fetch_assoc($assignments)) { ?>
                            <tr>
                                <td><?php echo $assignment['id']; ?></td>
                                <td><?php echo htmlspecialchars($assignment['title']); ?></td>
                                <td><?php echo htmlspecialchars($assignment['description']); ?></td>
                                <td><?php echo htmlspecialchars($assignment['course_name']); ?></td>
                                <td><?php echo htmlspecialchars($assignment['due_date']); ?></td>
                                <td>
                                    <a class="edit" href="assignments.php?edit=<?php echo $assignment['id']; ?>">Edit</a>
                                    <a class="delete" href="assignments.php?delete=<?php echo $assignment['id']; ?>" onclick="return confirm('Are you sure you want to delete this assignment?');">Delete</a>
                                </td>
                            </tr>
                        <?php } ?>
                    </table>
                </div>

            </div>

        </div>
    </div>

</body>

</html>