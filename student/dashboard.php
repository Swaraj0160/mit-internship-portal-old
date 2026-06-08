<?php 

session_start();
include("../config.php");

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'student')
{
    header("Location: ../login.php");
    exit();
}

$student_id = $_SESSION['user_id'];

/* GET STUDENT DIVISION */

$getStudent = mysqli_query(
    $conn,
    "SELECT division
     FROM users
     WHERE id='$student_id'"
);

$studentData = mysqli_fetch_assoc($getStudent);

$student_division = $studentData['division'];

/* TOTAL APPLICATIONS */

$total_query = "SELECT * FROM internships
                WHERE student_id='$student_id'";

$total_result = mysqli_query($conn, $total_query);

$total_applications = mysqli_num_rows($total_result);

/* APPROVED */

$approved_query = "SELECT * FROM internships
                   WHERE student_id='$student_id'
                   AND status='Approved'";

$approved_result = mysqli_query($conn, $approved_query);

$approved_count = mysqli_num_rows($approved_result);

/* PENDING */

$pending_query = "SELECT * FROM internships
                  WHERE student_id='$student_id'
                  AND status='Pending'";

$pending_result = mysqli_query($conn, $pending_query);

$pending_count = mysqli_num_rows($pending_result);

/* WEEKLY WORK COUNT */

$work_query = mysqli_query(
    $conn,
    "SELECT * FROM weekly_updates

     INNER JOIN internships
     ON weekly_updates.internship_id = internships.id

     WHERE internships.student_id='$student_id'"
);

$total_work = mysqli_num_rows($work_query);

/* ADMIN NOTIFICATIONS */

$admin_notifications = mysqli_query(
    $conn,
    "SELECT *
     FROM admin_notifications

     WHERE
     (
        target_role='student'
        OR target_role='all'
     )

     ORDER BY id DESC"
);

/* FACULTY NOTIFICATIONS */

$faculty_notifications = mysqli_query(
    $conn,
    "SELECT *
     FROM faculty_notifications

     WHERE division='$student_division'

     ORDER BY id DESC"
);

/* TOTAL NOTIFICATIONS */

$total_admin_notifications =
mysqli_num_rows($admin_notifications);

$total_faculty_notifications =
mysqli_num_rows($faculty_notifications);

/* UNREAD ADMIN NOTIFICATIONS */

$admin_unread_query = mysqli_query(
    $conn,
    "SELECT *
     FROM admin_notifications

     WHERE
     (
        target_role='student'
        OR target_role='all'
     )

     AND id NOT IN
     (
        SELECT notification_id

        FROM student_notification_reads

        WHERE student_id='$student_id'
        AND notification_type='admin'
     )"
);

$admin_unread_count =
mysqli_num_rows($admin_unread_query);

/* UNREAD FACULTY NOTIFICATIONS */

$faculty_unread_query = mysqli_query(
    $conn,
    "SELECT *
     FROM faculty_notifications

     WHERE division='$student_division'

     AND id NOT IN
     (
        SELECT notification_id

        FROM student_notification_reads

        WHERE student_id='$student_id'
        AND notification_type='faculty'
     )"
);

$faculty_unread_count =
mysqli_num_rows($faculty_unread_query);

/* TOTAL UNREAD */

$total_notifications =
$admin_unread_count
+
$faculty_unread_count;

?>

<!DOCTYPE html>
<html>
<head>

<title>Student Dashboard</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>

body{
    margin:0;
    font-family:Arial;
    background:#f1f5f9;
}

.sidebar{
    width:260px;
    height:100vh;
    background:#1e293b;
    position:fixed;
    color:white;
    overflow-y:auto;
}

.logo-section{
    text-align:center;
    padding:20px;
    border-bottom:1px solid #334155;
}

.logo-section img{
    width:70px;
}

.logo-section h4{
    margin-top:10px;
    font-size:18px;
}

.sidebar a{
    display:block;
    color:white;
    padding:15px 20px;
    text-decoration:none;
    transition:0.3s;
}

.sidebar a:hover{
    background:#4b0082;
    padding-left:25px;
}

.sidebar i{
    margin-right:10px;
}

.main{
    margin-left:260px;
}

.topbar{
    background:white;
    padding:15px 30px;
    box-shadow:0px 2px 10px rgba(0,0,0,0.1);
    display:flex;
    justify-content:space-between;
    align-items:center;
}

.content{
    padding:30px;
}

.card{
    border:none;
    border-radius:15px;
    box-shadow:0px 2px 10px rgba(0,0,0,0.1);
}

.card h2{
    font-weight:bold;
}

.quick-card{
    border-radius:15px;
    transition:0.3s;
}

.quick-card:hover{
    transform:translateY(-5px);
}

.notification-btn{
    background:none;
    border:none;
    position:relative;
}

.notification-box{
    width:350px;
    max-height:400px;
    overflow-y:auto;
    padding:15px;
}

.notification-item{
    border-bottom:1px solid #ddd;
    padding-bottom:10px;
    margin-bottom:10px;
}

</style>

</head>

<body>

<div class="sidebar">

    <div class="logo-section">

        <img src="../assets/images/logo.png">

        <h4>MIT-ADT University</h4>

    </div>

    <a href="dashboard.php">
        <i class="fa fa-home"></i>
        Dashboard
    </a>

    <a href="apply_internship.php">
        <i class="fa fa-briefcase"></i>
        Apply Internship Form
    </a>

    <a href="my_internships.php">
        <i class="fa fa-list"></i>
        My Internships
    </a>

    <a href="add_update.php">
        <i class="fa fa-calendar"></i>
        Weekly Updates
    </a>

    <a href="my_updates.php">
        <i class="fa fa-tasks"></i>
        My Weekly Work
    </a>

    <a href="upload_report.php">
        <i class="fa fa-upload"></i>
        Upload Certificate
    </a>

    <a href="profile.php">
        <i class="fa fa-user"></i>
        Profile
    </a>

    <a href="change_password.php">
        <i class="fa fa-lock"></i>
        Change Password
    </a>

    <a href="../logout.php">
        <i class="fa fa-sign-out-alt"></i>
        Logout
    </a>

</div>

<div class="main">

    <div class="topbar">

        <h4>
            Internship Management Portal
        </h4>

        <div class="d-flex align-items-center gap-4">

            <div class="dropdown">

                <button class="notification-btn"
                        data-bs-toggle="dropdown"
                        onclick="markNotificationsRead()">

                    <i class="fa fa-bell fa-lg"></i>

                    <?php
                    if($total_notifications > 0)
                    {
                    ?>

                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">

                        <?php echo $total_notifications; ?>

                    </span>

                    <?php
                    }
                    ?>

                </button>

                <div class="dropdown-menu dropdown-menu-end notification-box">

                    <h5 class="mb-3">
                        Notifications
                    </h5>

                    <?php

                    mysqli_data_seek($admin_notifications, 0);

                    while($admin = mysqli_fetch_assoc($admin_notifications))
                    {
                    ?>

                    <div class="notification-item">

                        <strong>
                            <?php echo $admin['title']; ?>
                        </strong>

                        <br>

                        <small>
                            <?php echo $admin['message']; ?>
                        </small>

                        <?php
                        if($admin['attachment'] != "")
                        {
                        ?>

                        <br><br>

                        <a href="../uploads/notifications/<?php echo $admin['attachment']; ?>"
                           target="_blank"
                           class="btn btn-sm btn-primary">

                           <i class="fa fa-download"></i>

                           Download Attachment

                        </a>

                        <?php
                        }
                        ?>

                        <br>

                        <small class="text-muted">

                            <?php
                            echo date(
                                "d M Y h:i A",
                                strtotime($admin['created_at'])
                            );
                            ?>

                        </small>

                    </div>

                    <?php
                    }

                    mysqli_data_seek($faculty_notifications, 0);

                    while($faculty = mysqli_fetch_assoc($faculty_notifications))
                    {
                    ?>

                    <div class="notification-item">

                        <strong>
                            <?php echo $faculty['title']; ?>
                        </strong>

                        <br>

                        <small>
                            <?php echo $faculty['message']; ?>
                        </small>

                        <?php
                        if($faculty['attachment'] != "")
                        {
                        ?>

                        <br><br>

                        <a href="../uploads/notifications/<?php echo $faculty['attachment']; ?>"
                           target="_blank"
                           class="btn btn-sm btn-primary">

                           <i class="fa fa-download"></i>

                           Download Attachment

                        </a>

                        <?php
                        }
                        ?>

                        <br>

                        <small class="text-muted">

                            <?php
                            echo date(
                                "d M Y h:i A",
                                strtotime($faculty['created_at'])
                            );
                            ?>

                        </small>

                    </div>

                    <?php
                    }

                    if(
                        $total_admin_notifications == 0
                        &&
                        $total_faculty_notifications == 0
                    )
                    {
                    ?>

                    <p class="text-muted">
                        No notifications available.
                    </p>

                    <?php
                    }
                    ?>

                </div>

            </div>

            <div>

                Welcome,
                <?php echo $_SESSION['name']; ?>

            </div>

        </div>

    </div>

    <div class="content">

        <!-- DASHBOARD CARDS -->

        <div class="row g-4">

            <div class="col-md-3">

                <div class="card bg-primary text-white p-4">

                    <h5>Total Applications</h5>

                    <h2><?php echo $total_applications; ?></h2>

                </div>

            </div>

            <div class="col-md-3">

                <div class="card bg-success text-white p-4">

                    <h5>Approved</h5>

                    <h2><?php echo $approved_count; ?></h2>

                </div>

            </div>

            <div class="col-md-3">

                <div class="card bg-warning text-white p-4">

                    <h5>Pending</h5>

                    <h2><?php echo $pending_count; ?></h2>

                </div>

            </div>

            <div class="col-md-3">

                <div class="card bg-info text-white p-4">

                    <h5>Weekly Work Submitted</h5>

                    <h2><?php echo $total_work; ?></h2>

                </div>

            </div>

        </div>

        <!-- QUICK ACCESS -->

        <div class="mt-5">

            <h4 class="mb-4">
                Quick Access
            </h4>

            <div class="row g-4">

                <div class="col-md-4">

                    <div class="card quick-card p-4">

                        <h5>
                            Submit Weekly Work
                        </h5>

                        <p>
                            Upload your weekly internship progress.
                        </p>

                        <a href="add_update.php"
                           class="btn btn-primary">

                            Open

                        </a>

                    </div>

                </div>

                <div class="col-md-4">

                    <div class="card quick-card p-4">

                        <h5>
                            My Weekly Work
                        </h5>

                        <p>
                            View and edit submitted weekly work.
                        </p>

                        <a href="my_updates.php"
                           class="btn btn-success">

                            Open

                        </a>

                    </div>

                </div>

                <div class="col-md-4">

                    <div class="card quick-card p-4">

                        <h5>
                             My Internship 
                        </h5>

                        <p>
                            View your internship applications.
                        </p>

                        <a href="my_internships.php"
                           class="btn btn-dark">

                            Open

                        </a>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>

function markNotificationsRead()
{
    fetch("mark_notifications_read.php")
    .then(response => response.text())
    .then(data => {

        let badge =
        document.querySelector(".badge");

        if(badge)
        {
            badge.style.display = "none";
        }

    });
}

</script>

</body>
</html>