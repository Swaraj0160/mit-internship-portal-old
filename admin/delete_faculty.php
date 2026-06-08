<?php

session_start();
include("../config.php");

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin')
{
    header("Location: ../login.php");
    exit();
}

$id = $_GET['id'];

$query = "DELETE FROM users
          WHERE id='$id'";

mysqli_query($conn, $query);

header("Location: manage_faculty.php");
exit();

?>