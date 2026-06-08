<?php

session_start();
include("../config.php");

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'faculty')
{
    header("Location: ../login.php");
    exit();
}

$id = $_GET['id'];

$query = "SELECT internships.*,
                 users.full_name,
                 users.enrollment_no
          FROM internships
          JOIN users
          ON internships.student_id = users.id
          WHERE internships.id='$id'";

$result = mysqli_query($conn, $query);

$row = mysqli_fetch_assoc($result);

if(!$row)
{
    echo "
    <script>

    alert('This student has not submitted internship details yet.');

    window.location='student_evaluations.php';

    </script>
    ";

    exit();
}

if(isset($_POST['submit']))
{
    $ta1_marks = $_POST['ta1_marks'];
    $ta2_marks = $_POST['ta2_marks'];
    $attendance_marks = $_POST['attendance_marks'];
    $final_evaluation_marks = $_POST['final_evaluation_marks'];
    $remarks = $_POST['remarks'];

    $total_marks =
        $ta1_marks +
        $ta2_marks +
        $attendance_marks +
        $final_evaluation_marks;

    $check = "SELECT * FROM evaluations
              WHERE internship_id='$id'";

    $check_result = mysqli_query($conn, $check);

    if(mysqli_num_rows($check_result) > 0)
    {
        echo "<script>alert('Evaluation already submitted');</script>";
    }
    else
    {
        $insert = "INSERT INTO evaluations
        (
            internship_id,
            ta1_marks,
            ta2_marks,
            attendance_marks,
            final_evaluation_marks,
            total_marks,
            remarks
        )

        VALUES
        (
            '$id',
            '$ta1_marks',
            '$ta2_marks',
            '$attendance_marks',
            '$final_evaluation_marks',
            '$total_marks',
            '$remarks'
        )";

        mysqli_query($conn, $insert);

        echo "
        <script>

        alert('Evaluation Submitted Successfully');

        window.location='student_evaluations.php';

        </script>
        ";
    }
}

?>

<!DOCTYPE html>
<html>
<head>

<title>Evaluate Student</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
    background:#f5f5f5;
}

.form-box{
    width:900px;
    margin:auto;
    margin-top:40px;
    background:white;
    padding:40px;
    border-radius:15px;
}

</style>

</head>

<body>

<div class="form-box">

<h1 class="text-center mb-5">
    Student Evaluation
</h1>

<h4>
    Student:
    <?php echo $row['full_name']; ?>
</h4>

<h4 class="mt-3">
    Enrollment No:
    <?php echo $row['enrollment_no']; ?>
</h4>

<h4 class="mt-3 mb-5">
    Company:
    <?php echo $row['company_name']; ?>
</h4>

<form method="POST">

<div class="mb-4">

<label>TA-1 Marks (Out of 10)</label>

<input type="number"
       name="ta1_marks"
       class="form-control"
       min="0"
       max="10"
       required>

</div>

<div class="mb-4">

<label>TA-2 Marks (Out of 10)</label>

<input type="number"
       name="ta2_marks"
       class="form-control"
       min="0"
       max="10"
       required>

</div>

<div class="mb-4">

<label>Attendance Marks (Out of 5)</label>

<input type="number"
       name="attendance_marks"
       class="form-control"
       min="0"
       max="5"
       required>

</div>

<div class="mb-4">

<label>Final Evaluation Marks (Out of 25)</label>

<input type="number"
       name="final_evaluation_marks"
       class="form-control"
       min="0"
       max="25"
       required>

</div>

<div class="mb-4">

<label>Remarks</label>

<textarea name="remarks"
          class="form-control"
          rows="5"></textarea>

</div>

<button type="submit"
        name="submit"
        class="btn btn-primary w-100">

    Submit Evaluation

</button>

</form>

</div>

</body>
</html>