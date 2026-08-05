<?php
session_start();

if (!isset($_SESSION['student_id'])) {
    header('Location: login.php');
    exit;
}

$student_name = $_SESSION['student_name'];
?>

<!DOCTYPE html>
<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>Dashboard</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body class="bg-light">

<div class="container mt-5">

<div class="card shadow">

<div class="card-header text-center text-white" style="background-color:#e91e63;">

<h2>Student Dashboard</h2>

</div>

<div class="card-body text-center">

<h3>

Welcome,

<?php echo htmlspecialchars($student_name); ?>!

</h3>

<br>

<a href="logout.php" class="btn btn-danger">

Logout

</a>

</div>

</div>

</div>

</body>

</html>