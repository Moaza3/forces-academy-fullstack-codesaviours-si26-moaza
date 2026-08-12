<?php

session_start();


// ==================================================
// CHECK LOGIN
// ==================================================

if (!isset($_SESSION['student_id'])) {

    header("Location: login.php");

    exit;
}


// ==================================================
// DATABASE CONNECTION
// ==================================================

require_once "config/db.php";


// ==================================================
// GET ALL COURSES
// ==================================================

$query = "SELECT * FROM courses ORDER BY created_at DESC";

$result = mysqli_query($conn, $query);

?>


<!DOCTYPE html>

<html lang="en">


<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>My Courses</title>


    <!-- Bootstrap CSS -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>


<body>


<!-- ==================================================
     SIDEBAR
================================================== -->

<div
    class="bg-dark text-white p-3 vh-100"
    style="width: 250px; position: fixed; left: 0; top: 0;"
>


    <h4 class="text-center mb-4">

        Student Portal

    </h4>


    <ul class="nav flex-column">


        <!-- Dashboard -->

        <li class="nav-item mb-2">

            <a
                href="dashboard.php"
                class="nav-link text-white"
            >

                Dashboard

            </a>

        </li>


        <!-- My Courses -->

        <li class="nav-item mb-2">

            <a
                href="courses.php"
                class="nav-link text-white"
            >

                My Courses

            </a>

        </li>


        <!-- Assignments -->

        <li class="nav-item mb-2">

            <a
                href="assignment.php"
                class="nav-link text-white"
            >

                Assignments

            </a>

        </li>


        <!-- My Results -->

        <li class="nav-item mb-2">

            <a
                href="result.php"
                class="nav-link text-white"
            >

                My Results

            </a>

        </li>


        <!-- Notices -->

        <li class="nav-item mb-2">

            <a
                href="notices.php"
                class="nav-link text-white"
            >

                Notices

            </a>

        </li>


        <!-- Logout -->

        <li class="nav-item mt-3">

            <a
                href="logout.php"
                class="nav-link text-danger"
            >

                Logout

            </a>

        </li>


    </ul>


</div>



<!-- ==================================================
     MAIN CONTENT
================================================== -->

<div
    class="p-4"
    style="margin-left: 250px;"
>


    <h2 class="mb-4">

        My Courses

    </h2>



    <!-- ==================================================
         COURSES
    ================================================== -->

    <div class="row">


        <?php if (mysqli_num_rows($result) > 0) { ?>


            <?php while ($course = mysqli_fetch_assoc($result)) { ?>


                <!-- COURSE CARD -->

                <div class="col-md-4 mb-4">


                    <div class="card shadow-sm h-100">


                        <div class="card-body">


                            <!-- Course Name -->

                            <h5 class="card-title">

                                <?php

                                echo htmlspecialchars(
                                    $course['course_name']
                                );

                                ?>

                            </h5>



                            <!-- Course Description -->

                            <p class="card-text">

                                <?php

                                echo htmlspecialchars(
                                    $course['discription']
                                );

                                ?>

                            </p>



                            <!-- Teacher Name -->

                            <p>

                                <strong>
                                    Teacher:
                                </strong>

                                <?php

                                echo htmlspecialchars(
                                    $course['teacher_name']
                                );

                                ?>

                            </p>


                        </div>


                    </div>


                </div>


            <?php } ?>


        <?php } else { ?>


            <!-- ==================================================
                 EMPTY STATE
            ================================================== -->

            <div class="col-12">


                <div class="alert alert-info">


                    <h5>

                        No Courses Available

                    </h5>


                    <p class="mb-0">

                        There are no courses available
                        at the moment.

                        Please check again later.

                    </p>


                </div>


            </div>


        <?php } ?>


    </div>


</div>


</body>


</html>