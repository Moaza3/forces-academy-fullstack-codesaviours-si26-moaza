<?php
require_once 'config/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm_password'];
    $roll_number = trim($_POST['roll_number']);
    $class = trim($_POST['class']);

    if (
        empty($full_name) ||
        empty($email) ||
        empty($password) ||
        empty($roll_number) ||
        empty($class)
    ) {

        $error = "All fields are required.";

    } elseif ($password !== $confirm) {

        $error = "Passwords do not match.";

    } else {

        $hashed = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO students (full_name, email, password, roll_number, class)
                VALUES (?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($conn, $sql);

        mysqli_stmt_bind_param(
            $stmt,
            "sssss",
            $full_name,
            $email,
            $hashed,
            $roll_number,
            $class
        );

        if (mysqli_stmt_execute($stmt)) {

            header("Location: login.php?registered=1");
            exit;

        } else {

            $error = "Registration failed. Email or Roll Number may already exist.";

        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Student Registration</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="bg-light">

<div class="container mt-5">

<div class="row justify-content-center">

<div class="col-md-6">

<div class="card shadow">

<div class="card-header text-center text-white" style="background-color:#e91e63;">

<h3>Student Registration</h3>

</div>

<div class="card-body">

<?php if(isset($error)){ ?>

<div class="alert alert-danger">

<?php echo $error; ?>

</div>

<?php } ?>

<form action="register.php" method="POST">

<div class="mb-3">

<label class="form-label">Full Name</label>

<input
type="text"
name="full_name"
class="form-control"
required>

</div>

<div class="mb-3">

<label class="form-label">Email</label>

<input
type="email"
name="email"
class="form-control"
required>

</div>

<div class="mb-3">

<label class="form-label">Password</label>

<input
type="password"
name="password"
class="form-control"
required>

</div>

<div class="mb-3">

<label class="form-label">Confirm Password</label>

<input
type="password"
name="confirm_password"
class="form-control"
required>

</div>

<div class="mb-3">

<label class="form-label">Roll Number</label>

<input
type="text"
name="roll_number"
class="form-control"
required>

</div>

<div class="mb-3">

<label class="form-label">Class</label>

<input
type="text"
name="class"
class="form-control"
required>

</div>

<button
type="submit"
class="btn w-100 text-white"
style="background-color:#e91e63;">


Register

</button>

</form>

<hr>

<p class="text-center">

Already have an account?

<a href="login.php">

Login

</a>

</p>

</div>

</div>

</div>

</div>

</div>

</body>

</html>