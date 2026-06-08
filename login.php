<?php

session_start();
include("config.php");

if(isset($_POST['login']))
{
    $email = $_POST['email'];
    $password = $_POST['password'];

    $query = "SELECT * FROM users
              WHERE email='$email'";

    $result = mysqli_query($conn, $query);

    if(mysqli_num_rows($result) > 0)
    {
        $row = mysqli_fetch_assoc($result);

        /* PASSWORD CHECK */

        if($password == $row['password'])
        {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['name'] = $row['full_name'];
            $_SESSION['role'] = $row['role'];

            if($row['role'] == 'student')
            {
                header("Location: student/dashboard.php");
                exit();
            }

            elseif($row['role'] == 'faculty')
            {
                header("Location: faculty/dashboard.php");
                exit();
            }

            elseif($row['role'] == 'admin')
            {
                header("Location: admin/dashboard.php");
                exit();
            }
        }
        else
        {
            echo "<script>alert('Invalid Password');</script>";
        }
    }
    else
    {
        echo "<script>alert('User Not Found');</script>";
    }
}

?>

<!DOCTYPE html>
<html>
<head>

<title>MIT Internship Portal</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
    background: linear-gradient(to right,#4b0082,#9370db);
    height:100vh;
    display:flex;
    justify-content:center;
    align-items:center;
}

.login-box{
    width:400px;
    background:white;
    padding:40px;
    border-radius:15px;
    box-shadow:0px 0px 20px rgba(0,0,0,0.2);
}

.logo{
    text-align:center;
    margin-bottom:20px;
}

.logo img{
    width:80px;
}

h2{
    text-align:center;
    margin-bottom:25px;
    font-weight:bold;
}

.btn-login{
    background:#4b0082;
    color:white;
    width:100%;
}

.btn-login:hover{
    background:#360061;
    color:white;
}

</style>

</head>

<body>

<div class="login-box">

    <div class="logo">

        <img src="assets/images/logo.png">

    </div>

    <h2>MIT Internship Portal</h2>

    <form method="POST">

        <div class="mb-3">

            <label>Email</label>

            <input type="email"
                   name="email"
                   class="form-control"
                   required>

        </div>

        <div class="mb-3">

            <label>Password</label>

            <input type="password"
                   name="password"
                   class="form-control"
                   required>

        </div>

        <button type="submit"
                name="login"
                class="btn btn-login">

            Login

        </button>

    </form>

</div>

</body>
</html>