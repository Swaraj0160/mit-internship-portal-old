<?php

session_start();
include("../config.php");

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'student')
{
    header("Location: ../login.php");
    exit();
}

$student_id = $_SESSION['user_id'];

$query = "SELECT * FROM users
          WHERE id='$student_id'";

$result = mysqli_query($conn, $query);

$user = mysqli_fetch_assoc($result);

if(isset($_POST['change_password']))
{
    $old_password = trim($_POST['old_password']);
    $new_password = trim($_POST['new_password']);
    $confirm_password = trim($_POST['confirm_password']);

    /*
    CHECK OLD PASSWORD
    */

    if($old_password != $user['password'])
    {
        echo "<script>alert('Old Password Incorrect');</script>";
    }

    /*
    CHECK NEW PASSWORD MATCH
    */

    elseif($new_password != $confirm_password)
    {
        echo "<script>alert('New Passwords Do Not Match');</script>";
    }

    /*
    UPDATE PASSWORD
    */

    else
    {
        $update = "UPDATE users
                   SET password='$new_password'
                   WHERE id='$student_id'";

        mysqli_query($conn, $update);

        echo "<script>alert('Password Changed Successfully');</script>";

        echo "<script>
                window.location='dashboard.php';
              </script>";
    }
}

?>

<!DOCTYPE html>
<html>
<head>

<title>Change Password</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>

body{
    background:#f1f5f9;
}

.password-box{
    width:500px;
    margin:auto;
    margin-top:60px;
    background:white;
    padding:30px;
    border-radius:15px;
    box-shadow:0px 2px 10px rgba(0,0,0,0.1);
}

</style>

</head>

<body><br>
        <a href="dashboard.php"
   class="btn btn-primary mb-3">
   ← Back
</a>

<div class="password-box">

    <h3 class="text-center mb-4">
        Change Password
    </h3>

    <form method="POST">

        <div class="mb-3">

            <label>Old Password</label>

            <input type="password"
                   name="old_password"
                   class="form-control"
                   required>

        </div>

        <div class="mb-3">

            <label>New Password</label>

            <input type="password"
                   name="new_password"
                   class="form-control"
                   required>

        </div>

        <div class="mb-3">

            <label>Confirm Password</label>

            <input type="password"
                   name="confirm_password"
                   class="form-control"
                   required>

        </div>

        <button type="submit"
                name="change_password"
                class="btn btn-primary w-100">

                Change Password

        </button>

    </form>

</div>

</body>
</html>