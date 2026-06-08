<?php
session_start();
include("../config.php");

if($_SESSION['role'] != 'admin')
{
    header("Location: ../login.php");
}
$student_query = "SELECT * FROM users
                  WHERE role='student'";

$student_result = mysqli_query($conn, $student_query);

$total_students = mysqli_num_rows($student_result);

$faculty_query = "SELECT * FROM users
                  WHERE role='faculty'";

$faculty_result = mysqli_query($conn, $faculty_query);

$total_faculty = mysqli_num_rows($faculty_result);

$internship_query = "

SELECT COUNT(*) AS total

FROM internships

WHERE id IN
(
    SELECT MAX(id)
    FROM internships
    GROUP BY student_id
)

";

$internship_result = mysqli_query($conn, $internship_query);

$row = mysqli_fetch_assoc($internship_result);

$total_internships = $row['total'];
?>

<!DOCTYPE html>
<html>
<head>

<title>Admin Dashboard</title>

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

    <a href="manage_students.php">
        <i class="fa fa-users"></i>
        Manage Students
    </a>

    <a href="manage_faculty.php">
        <i class="fa fa-user-tie"></i>
        Manage Faculty
    </a>

    <a href="all_internships.php">
        <i class="fa fa-briefcase"></i>
        All Internships
    </a>

    <a href="reports.php">
        <i class="fa fa-chart-bar"></i>
        Reports
    </a>

    <a href="manage_notifications.php">
    <i class="fa fa-bell"></i>
    Notifications
   </a>
    
       <a href="change_password.php">
    <i class="fa fa-key"></i>
    Change Password
</a>

    <a href="settings.php">
        <i class="fa fa-cog"></i>
        Settings
    </a>

    <a href="../logout.php">
        <i class="fa fa-sign-out-alt"></i>
        Logout
    </a>

</div>

<div class="main">

    <div class="topbar">

        <h4>
            Admin Dashboard
        </h4>

        <div>

            Welcome Admin

        </div>

    </div>

    <div class="content">

        <div class="row">

            <div class="col-md-4">

                <div class="card bg-primary text-white p-4">

                    <h5>Total Students</h5>

                    <h2><?php echo $total_students; ?></h2>

                </div>

            </div>

            <div class="col-md-4">

                <div class="card bg-success text-white p-4">

                    <h5>Total Faculty</h5>

                    <h2><?php echo $total_faculty; ?></h2>

                </div>

            </div>

            <div class="col-md-4">

                <div class="card bg-warning text-white p-4">

                    <h5>Total Internships</h5>

                    <h2><?php echo $total_internships; ?></h2>

                </div>

            </div>

        </div>

    </div>

</div>
</body>
</html>