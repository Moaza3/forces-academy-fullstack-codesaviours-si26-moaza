<?php

session_start();

/* ==================================================
   SESSION CHECK
================================================== */

if (!isset($_SESSION['student_id'])) {

    header("Location: login.php");
    exit();

}


/* ==================================================
   DATABASE CONNECTION
================================================== */

require_once "config/db.php";


/* ==================================================
   GET ALL NOTICES
   NEWEST NOTICE FIRST
================================================== */

$sql = "SELECT * FROM notices ORDER BY created_at DESC";

$result = mysqli_query($conn, $sql);


/* ==================================================
   CHECK QUERY
================================================== */

if (!$result) {

    die("Database query failed: " . mysqli_error($conn));

}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Notice Board</title>


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
    style="
        width: 250px;
        position: fixed;
        left: 0;
        top: 0;
    "
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
    style="
        margin-left: 250px;
        width: calc(100% - 250px);
    "
>


    <h2 class="mb-4">

        Notice Board

    </h2>



    <!-- ==================================================
         NOTICES
    ================================================== -->

    <?php if (mysqli_num_rows($result) > 0) { ?>


        <?php while ($notice = mysqli_fetch_assoc($result)) { ?>


            <!-- Notice Card -->

            <div class="card shadow-sm mb-4">


                <div class="card-body">


                    <!-- Title -->

                    <h5 class="card-title">

                        <?php

                        echo htmlspecialchars(
                            $notice['title']
                        );

                        ?>

                    </h5>


                    <!-- Content -->

                    <p class="card-text">

                        <?php

                        echo htmlspecialchars(
                            $notice['content']
                        );

                        ?>

                    </p>


                    <!-- Posted By -->

                    <p class="mb-1">

                        <strong>
                            Posted by:
                        </strong>

                        <?php

                        echo htmlspecialchars(
                            $notice['posted_by']
                        );

                        ?>

                    </p>


                    <!-- Date -->

                    <small class="text-muted">

                        Posted on:

                        <?php

                        echo date(
                            'd M Y',
                            strtotime(
                                $notice['created_at']
                            )
                        );

                        ?>

                    </small>


                </div>


            </div>


        <?php } ?>


    <?php } else { ?>


        <!-- Empty State -->

        <div class="alert alert-info">

            <h5>

                No Notices Available

            </h5>


            <p class="mb-0">

                There are no notices available at the moment.
                Please check again later.

            </p>

        </div>


    <?php } ?>


</div>


</body>

</html>