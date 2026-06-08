<?php session_start(); 
include("../config.php"); 
if(!isset($_SESSION['role']) || $_SESSION['role'] != 'faculty') { 
    header("Location: ../login.php"); 
    exit(); 
} 
$id = $_GET['id']; 
$query = "UPDATE internships SET status='Rejected' WHERE id='$id'"; 
mysqli_query($conn, $query); 
header("Location: manage_applications.php"); 
exit(); 
?>