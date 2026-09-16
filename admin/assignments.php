<?php
include("../config/db.php");
session_start();
if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit();
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

        body {
            font-family: 'Segoe UI', 'Poppins', sans-serif;
            background: #f4f6f5;
            margin: 0;
            display: flex;
        }

        /* Sidebar container styling matching other admin pages */
        .sidebar {
            width: 250px;
            background: var(--green);
            min-height: 100vh;
            color: white;
            position: fixed;
        }

        .main-content {
            margin-left: 250px;
            padding: 30px;
            width: calc(100% - 250px);
        }

        h1 {
            color: var(--green);
            font-weight: 700;
            margin-bottom: 25px;
        }

        form {
            background: white;
            padding: 25px;
            margin-bottom: 30px;
            border-radius: 14px;
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

        button {
            padding: 10px 20px;
            background: var(--green);
            color: white;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
        }

        button:hover {
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
        }

        .cancel-btn:hover {
            color: white;
        }

        .table-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 12px;
            overflow: hidden;
            min-width: 700px;
        }

        th,
        td {
            padding: 12px;
            border: 1px solid #eee;
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

        @media (max-width: 768px) {
            body {
                flex-direction: column;
            }
            .sidebar {
                width: 100%;
                min-height: auto;
                position: relative;
            }
            .main-content {
                margin-left: 0;
                width: 100%;
                padding: 15px;
            }
        }
    </style>
</head>

<body>

    <!-- Include Sidebar (Agar aapke paas sidebar ki alag file hai jaise sidebar.php toh yeh line kaam karegi, warna agar aapne directly layout likha hai toh niche wala sidebar use hoga) -->
    <?php 
    if(file_exists('sidebar.php')) {
        include('sidebar.php');
    } else {
    ?>
        <div class="sidebar p-3">
            <h3 class="text-white mb-4">Admin Panel</h3>
            <ul class="nav flex-column">
                <li class="nav-item mb-2"><a href="dashboard.php" class="nav-link text-white">Dashboard</a></li>
                <li class="nav-item mb-2"><a href="students.php" class="nav-link text-white">Manage Students</a></li>
                <li class="nav-item mb-2"><a href="courses.php" class="nav-link text-white">Manage Courses</a></li>
                <li class="nav-item mb-2"><a href="assignments.php" class="nav-link text-white active">Manage Assignments</a></li>
                <li class="nav-item mb-2"><a href="fees.php" class="nav-link text-white">Manage Fees</a></li>
                <li class="nav-item mt-4"><a href="logout.php" class="nav-link text-danger">Logout</a></li>
            </ul>
        </div>
    <?php } ?>

    <div class="main-content">

        <h1>Manage Assignments</h1>

        <h2><?php echo $edit_data ? 'Edit Assignment' : 'Add New Assignment'; ?></h2>

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

        <h2>All Assignments</h2>

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

                        <td>
                            <?php echo htmlspecialchars($assignment['title']); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($assignment['description']); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($assignment['course_name']); ?>
                        </td>

                        <td>
                            <?php echo htmlspecialchars($assignment['due_date']); ?>
                        </td>

                        <td>
                            <a class="edit" href="assignments.php?edit=<?php echo $assignment['id']; ?>">
                                Edit
                            </a>

                            <a class="delete"
                               href="assignments.php?delete=<?php echo $assignment['id']; ?>"
                               onclick="return confirm('Are you sure you want to delete this assignment?');">
                                Delete
                            </a>
                        </td>
                    </tr>

                <?php } ?>

            </table>
        </div>

    </div>

</body>

</html>