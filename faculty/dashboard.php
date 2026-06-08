<?php

session_start();
include("../config.php");

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'faculty')
{
    header("Location: ../login.php");
    exit();
}

$faculty_id = $_SESSION['user_id'];

$faculty_query = "SELECT * FROM users
                  WHERE id='$faculty_id'";

$faculty_result = mysqli_query($conn, $faculty_query);

$faculty = mysqli_fetch_assoc($faculty_result);

$faculty_division = $faculty['faculty_division'];

/* TOTAL STUDENTS */

$student_query = "SELECT * FROM users
                  WHERE role='student'
                  AND division='$faculty_division'";

$student_result = mysqli_query($conn, $student_query);

$total_students = mysqli_num_rows($student_result);

/* CURRENT INTERNSHIP DATA */

$current_query = "

SELECT
u.id,
i.status

FROM users u

LEFT JOIN internships i
ON i.id = (

    SELECT id

    FROM internships

    WHERE student_id = u.id

    ORDER BY created_at DESC

    LIMIT 1

)

WHERE u.role='student'
AND u.division='$faculty_division'

";

$current_result = mysqli_query($conn, $current_query);

$total_applications = 0;
$approved_count = 0;
$pending_count = 0;
$rejected_count = 0;

while($row = mysqli_fetch_assoc($current_result))
{
    if($row['status'] != "")
    {
        $total_applications++;
    }

    if($row['status'] == "Approved")
    {
        $approved_count++;
    }

    if($row['status'] == "Pending")
    {
        $pending_count++;
    }
    
    if($row['status'] == "Rejected")
    {
    $rejected_count++;
    }
}

/* ADMIN NOTIFICATIONS */

$admin_notifications = mysqli_query(
    $conn,
    "SELECT * FROM admin_notifications

     WHERE target_role='faculty'
     OR target_role='all'

     ORDER BY id DESC"
);

/* UNREAD ADMIN NOTIFICATIONS */

$unread_notifications = mysqli_query(
    $conn,
    "SELECT *
     FROM admin_notifications

     WHERE
     (
        target_role='faculty'
        OR target_role='all'
     )

     AND id NOT IN
     (
        SELECT notification_id

        FROM faculty_notification_reads

        WHERE faculty_id='$faculty_id'
     )"
);

$notification_count =
    mysqli_num_rows($unread_notifications);

?>

<!DOCTYPE html>
<html>
<head>

<title>Faculty Dashboard</title>

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
    min-height:140px;
}

.badge{
    font-size:14px;
    padding:8px 12px;
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

<!-- SIDEBAR -->

<div class="sidebar">

    <div class="logo-section">

        <img src="../assets/images/logo.png">

        <h4>MIT-ADT University</h4>

        <p>
            <?php echo $faculty_division; ?>
        </p>

    </div>

    <a href="dashboard.php">
        <i class="fa fa-home"></i>
        Dashboard
    </a>

    <a href="manage_applications.php">
        <i class="fa fa-check-circle"></i>
        Manage Applications
    </a>

    <a href="view_updates.php">
        <i class="fa fa-calendar"></i>
        Weekly Reports
    </a>

    <a href="manage_deadlines.php">
        <i class="fa fa-clock"></i>
        Manage Deadlines
    </a>

    <a href="manage_notifications.php">
        <i class="fa fa-bell"></i>
        Notifications
    </a>

    <a href="view_reports.php">
        <i class="fa fa-file"></i>
        Final Reports
    </a>

   <a href="export_report.php">

    <i class="fa fa-file-export"></i>

    Export Division Report

</a>

    <a href="student_evaluations.php">
        <i class="fa fa-star"></i>
        Student Evaluation
    </a>
    
    <a href="change_password.php" class="nav-link">
    <i class="fas fa-key"></i>
    Change Password
</a>

    <a href="../logout.php">
        <i class="fa fa-sign-out-alt"></i>
        Logout
    </a>

</div>

<!-- MAIN -->

<div class="main">

    <!-- TOPBAR -->

    <div class="topbar">

        <h4>
            Faculty Dashboard
        </h4>

        <div class="d-flex align-items-center gap-4">

            <!-- NOTIFICATION BELL -->

            <div class="dropdown">

                <button class="notification-btn"
        data-bs-toggle="dropdown"
        onclick="markNotificationsRead()">

                    <i class="fa fa-bell fa-lg"></i>

                    <?php
                    if($notification_count > 0)
                    {
                    ?>

                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">

                        <?php echo $notification_count; ?>

                    </span>

                    <?php
                    }
                    ?>

                </button>

                <!-- DROPDOWN -->

                <div class="dropdown-menu dropdown-menu-end notification-box">

                    <h5 class="mb-3">
                        Notifications
                    </h5>

                    <?php

                    mysqli_data_seek($admin_notifications, 0);

                    if(mysqli_num_rows($admin_notifications) > 0)
                    {
                        while($notification =
                            mysqli_fetch_assoc($admin_notifications))
                        {
                    ?>

                    <div class="notification-item">

                        <strong>

                            <?php
                            echo $notification['title'];
                            ?>

                        </strong>

                        <br>

                        <small>

                            <?php
                            echo $notification['message'];
                            ?>

                        </small>

                        <?php

if($notification['attachment'] != "")
{
?>

<br><br>

<a href="../uploads/notifications/<?php echo $notification['attachment']; ?>"
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
                                strtotime(
                                    $notification['created_at']
                                )
                            );

                            ?>

                        </small>

                    </div>

                    <?php
                        }
                    }
                    else
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

            <!-- WELCOME -->

            <div>
                Welcome Faculty
            </div>

        </div>

    </div>

    <!-- CONTENT -->

    <div class="content">

        <!-- CARDS -->


<div class="row g-4 mb-4">

    <div class="col-lg col-md-6 col-12">
        <div class="card bg-primary text-white p-4 h-100">
            <h5 class="mb-3">Class Strength</h5>
            <h2><?php echo $total_students; ?></h2>
        </div>
    </div>

    <div class="col-lg col-md-6 col-12">
        <div class="card bg-info text-white p-4 h-100">
            <h5 class="mb-3">Total Applications</h5>
            <h2><?php echo $total_applications; ?></h2>
        </div>
    </div>

    <div class="col-lg col-md-6 col-12">
        <div class="card bg-success text-white p-4 h-100">
            <h5 class="mb-3">Approved</h5>
            <h2><?php echo $approved_count; ?></h2>
        </div>
    </div>

    <div class="col-lg col-md-6 col-12">
        <div class="card bg-warning text-white p-4 h-100">
            <h5 class="mb-3">Pending</h5>
            <h2><?php echo $pending_count; ?></h2>
        </div>
    </div>

    <div class="col-lg col-md-6 col-12">
        <div class="card bg-danger text-white p-4 h-100">
            <h5 class="mb-3">Rejected</h5>
            <h2><?php echo $rejected_count; ?></h2>
        </div>
    </div>

</div>

        <!-- STUDENT TABLE -->

        <div class="card mt-5 p-4">

            <h4 class="mb-4">
                Division Student Internship Status
            </h4>

            <div class="table-responsive">

                <table class="table table-bordered table-hover">

                    <thead class="table-dark">

                    <tr>

                        <th>Roll No</th>
                        <th>Enrollment No</th>
                        <th>Student Name</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th>Internship Type</th>
                        <th>Mentor Name</th>
                        <th>Company</th>
                        <th>Company Contact</th>

                    </tr>

                    </thead>

                    <tbody>

                    <?php

                    $student_table_query = "

                    SELECT
                    u.*,
                    i.status,
                    i.internship_type,
                    i.mentor_name,
                    i.company_name,
                    i.company_contact

                    FROM users u

                    LEFT JOIN internships i
                    ON i.id = (

                        SELECT id

                        FROM internships

                        WHERE student_id = u.id

                        ORDER BY created_at DESC

                        LIMIT 1

                    )

                    WHERE u.role='student'
                    AND u.division='$faculty_division'

                    ORDER BY CAST(u.roll_no AS UNSIGNED)

                    ";

                    $student_table_result = mysqli_query(
                        $conn,
                        $student_table_query
                    );

                    while($student = mysqli_fetch_assoc($student_table_result))
                    {

                    ?>

                    <tr>

                        <td>
                            <?php echo $student['roll_no']; ?>
                        </td>

                        <td>
                            <?php echo $student['enrollment_no']; ?>
                        </td>

                        <td>
                            <?php echo $student['full_name']; ?>
                        </td>

                        <td>
                            <?php echo $student['phone']; ?>
                        </td>

                        <td>

                        <?php

                        if($student['status'] == "")
                        {
                            echo "<span class='badge bg-secondary'>
                                  Not Applied
                                  </span>";
                        }
                        else if($student['status'] == "Approved")
                        {
                            echo "<span class='badge bg-success'>
                                  Approved
                                  </span>";
                        }
                        else if($student['status'] == "Pending")
                        {
                            echo "<span class='badge bg-warning text-dark'>
                                  Pending
                                  </span>";
                        }
                        else
                        {
                            echo "<span class='badge bg-danger'>
                                  Rejected
                                  </span>";
                        }

                        ?>

                        </td>

                        <td>

                        <?php

                        echo ($student['internship_type'] != "")
                             ? $student['internship_type']
                             : "-";

                        ?>

                        </td>

                        <td>

                        <?php

                        echo ($student['mentor_name'] != "")
                             ? $student['mentor_name']
                             : "-";

                        ?>

                        </td>

                        <td>

                        <?php

                        echo ($student['company_name'] != "")
                             ? $student['company_name']
                             : "-";

                        ?>

                        </td>

                        <td>

                        <?php

                        echo ($student['company_contact'] != "")
                             ? $student['company_contact']
                             : "-";

                        ?>

                        </td>

                    </tr>

                    <?php

                    }

                    ?>

                    </tbody>

                </table>

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