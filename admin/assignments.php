<?php

include("../config/db.php");

// 1. ADD NEW ASSIGNMENT
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

// 2. UPDATE ASSIGNMENT
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

// 3. DELETE ASSIGNMENT
if (isset($_GET['delete'])) {
    $id = (int) $_GET['delete'];

    mysqli_query($conn, "DELETE FROM assignment WHERE id = $id");

    header("Location: assignments.php");
    exit();
}

// FETCH EDIT DATA
$edit_data = null;
if (isset($_GET['edit'])) {
    $edit_id = (int) $_GET['edit'];
    $edit_query = mysqli_query($conn, "SELECT * FROM assignment WHERE id = $edit_id");
    $edit_data = mysqli_fetch_assoc($edit_query);
}

// FETCH COURSES
$courses = mysqli_query($conn, "SELECT id, course_name FROM courses ORDER BY course_name ASC");

// FETCH ALL ASSIGNMENTS
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

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
            background: #f5f5f5;
        }

        .container {
            max-width: 1000px;
            margin: auto;
        }

        h1 {
            text-align: center;
        }

        form {
            background: white;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 8px;
        }

        input,
        textarea,
        select {
            width: 100%;
            padding: 10px;
            margin: 8px 0 15px;
            box-sizing: border-box;
        }

        button {
            padding: 10px 20px;
            background: #e91e63;
            color: white;
            border: none;
            cursor: pointer;
        }

        .cancel-btn {
            background: #666;
            text-decoration: none;
            padding: 10px 20px;
            color: white;
            display: inline-block;
            margin-left: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        th,
        td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background: #e91e63;
            color: white;
        }

        a {
            text-decoration: none;
            margin-right: 10px;
        }

        .delete {
            color: red;
        }

        .edit {
            color: blue;
        }
    </style>
</head>

<body>

    <div class="container">

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

</body>

</html>