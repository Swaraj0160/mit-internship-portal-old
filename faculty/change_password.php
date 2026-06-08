<?php

session_start();
include("../config.php");

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'faculty')
{
    header("Location: ../login.php");
    exit();
}

$message = "";

if(isset($_POST['change_password']))
{
    $faculty_id = $_SESSION['user_id'];

    $current_password = mysqli_real_escape_string(
        $conn,
        $_POST['current_password']
    );

    $new_password = mysqli_real_escape_string(
        $conn,
        $_POST['new_password']
    );

    $confirm_password = mysqli_real_escape_string(
        $conn,
        $_POST['confirm_password']
    );

    $query = "SELECT password
              FROM users
              WHERE id='$faculty_id'";

    $result = mysqli_query($conn,$query);

    $user = mysqli_fetch_assoc($result);

    if($current_password != $user['password'])
    {
        $message = "
        <div class='alert alert-danger'>
            Current Password is Incorrect
        </div>";
    }
    else if($new_password != $confirm_password)
    {
        $message = "
        <div class='alert alert-danger'>
            New Password and Confirm Password do not match
        </div>";
    }
    else
    {
        mysqli_query($conn,"
        UPDATE users
        SET password='$new_password'
        WHERE id='$faculty_id'
        ");

        $message = "
        <div class='alert alert-success'>
            Password Changed Successfully
        </div>";
    }
}

?>

<!DOCTYPE html>
<html>

<head>

<title>Change Password</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

<style>

body{
    background:#f5f5f5;
    font-family:Arial;
}

.password-box{
    width:500px;
    margin:auto;
    margin-top:60px;
    background:white;
    padding:30px;
    border-radius:15px;
    box-shadow:0px 0px 10px rgba(0,0,0,0.1);
}

h2{
    text-align:center;
    margin-bottom:25px;
}

</style>

</head>

<body><br>
    <a href="dashboard.php"
   class="btn btn-primary"
   style="border-radius:8px;margin-bottom:15px;">
   ← Back
</a>
   

<div class="password-box">

<h2>
Change Password
</h2>

<?php echo $message; ?>

<form method="POST">

<div class="mb-3">

<label class="form-label">
Current Password
</label>

<input type="password"
       name="current_password"
       class="form-control"
       required>

</div>

<div class="mb-3">

<label class="form-label">
New Password
</label>

<input type="password"
       name="new_password"
       class="form-control"
       required>

</div>

<div class="mb-3">

<label class="form-label">
Confirm New Password
</label>

<input type="password"
       name="confirm_password"
       class="form-control"
       required>

</div>

<div class="d-flex gap-2">

<button type="submit"
        name="change_password"
        class="btn btn-primary">

Change Password

</button>

<a href="dashboard.php"
   class="btn btn-secondary">

Back

</a>

</div>

</form>

</div>

</body>
</html>