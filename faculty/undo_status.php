<?php

session_start();
include("../config.php");

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'faculty')
{
    header("Location: ../login.php");
    exit();
}

$id = intval($_GET['id']);

mysqli_query($conn,"
UPDATE internships
SET status='Pending',
    rejection_reason=NULL
WHERE id='$id'
");

header("Location: manage_applications.php");
exit();

?>