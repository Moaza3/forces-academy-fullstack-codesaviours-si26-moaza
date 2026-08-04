<?php
require_once 'config/db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $sql = "SELECT id, full_name, password FROM students WHERE email = ?";

    $stmt = mysqli_prepare($conn, $sql);

    mysqli_stmt_bind_param($stmt, 's', $email);

    mysqli_stmt_execute($stmt);

    $result = mysqli_stmt_get_result($stmt);

    $student = mysqli_fetch_assoc($result);

    if ($student && password_verify($password, $student['password'])) {

        $_SESSION['student_id'] = $student['id'];
        $_SESSION['student_name'] = $student['full_name'];

        header("Location: dashboard.php");
        exit;

    } else {

        $error = "Invalid email or password.";

    }

}
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Student Login</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="bg-light">

<div class="container mt-5">

<div class="row justify-content-center">

<div class="col-md-6">

<div class="card shadow">

<div class="card-header text-center text-white" style="background-color:#e91e63;">

<h3>Student Login</h3>

</div>

<div class="card-body">

<?php if(isset($error)){ ?>

<div class="alert alert-danger">

<?php echo $error; ?>

</div>

<?php } ?>

<form action="login.php" method="POST">

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

<button
type="submit"
class="btn w-100 text-white"
style="background-color:#e91e63;">

Login

</button>

</form>

<hr>

<p class="text-center">

Don't have an account?

<a href="register.php">Register Here</a>

</p>

</div>

</div>

</div>

</div>

</div>

</body>

</html>