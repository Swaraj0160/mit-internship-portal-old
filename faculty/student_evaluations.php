<?php

session_start();
include("../config.php");

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'faculty')
{
    header("Location: ../login.php");
    exit();
}

$faculty_id = $_SESSION['user_id'];

$faculty_query = "SELECT faculty_division
                  FROM users
                  WHERE id='$faculty_id'";

$faculty_result = mysqli_query($conn, $faculty_query);

$faculty_data = mysqli_fetch_assoc($faculty_result);

$faculty_division = $faculty_data['faculty_division'];

$query = "SELECT
                 users.id,
                 users.roll_no,
                 users.full_name,
                 users.enrollment_no,

                 MAX(internships.id) AS internship_id,
                 MAX(internships.company_name) AS company_name,

                 MAX(evaluations.id) AS evaluation_id,
                 MAX(evaluations.total_marks) AS total_marks

          FROM users

          LEFT JOIN internships
ON internships.id =
(
    SELECT MAX(i.id)
    FROM internships i
    WHERE i.student_id = users.id
)

          LEFT JOIN evaluations
          ON internships.id = evaluations.internship_id

          WHERE users.role='student'
          AND users.division='$faculty_division'

          GROUP BY users.id

          ORDER BY CAST(users.roll_no AS UNSIGNED) ASC";

$result = mysqli_query($conn, $query);

?>

<!DOCTYPE html>
<html>
<head>

<title>Student Evaluations</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
    background:#f5f5f5;
}

.container-box{
    width:95%;
    margin:auto;
    margin-top:40px;
    background:white;
    padding:30px;
    border-radius:15px;
}

</style>

</head>

<body>

<div class="container-box">

    <a href="dashboard.php"
   class="btn btn-primary"
   style="border-radius:8px;margin-bottom:15px;">
   ← Back
</a>
    
<h1 class="mb-4">
    Student Evaluation
</h1>

<table class="table table-bordered">

<thead class="table-dark">

<tr>

<th>Roll No</th>
<th>Enrollment No</th>
<th>Student Name</th>
<th>Company</th>
<th>Total Marks</th>
<th>Action</th>

</tr>

</thead>

<tbody>

<?php

while($row = mysqli_fetch_assoc($result))
{

?>

<tr>

<td>
    <?php echo $row['roll_no']; ?>
</td>

<td>
    <?php echo $row['enrollment_no']; ?>
</td>

<td>
    <?php echo $row['full_name']; ?>
</td>

<td>
    <?php echo $row['company_name']; ?>
</td>

<td>

<?php

if($row['total_marks'] != "")
{
    echo $row['total_marks']." / 50";
}
else
{
    echo "Not Evaluated";
}

?>

</td>

<td>

<?php

if($row['evaluation_id'] != "")
{

?>

<a href="edit_evaluation.php?id=<?php echo $row['evaluation_id']; ?>"
class="btn btn-warning btn-sm">

Edit

</a>

<?php

}
else
{

?>

<a href="evaluate_student.php?id=<?php echo $row['internship_id']; ?>"
class="btn btn-primary btn-sm">

Evaluate

</a>

<?php

}

?>

</td>

</tr>

<?php

}

?>

</tbody>

</table>

</div>

</body>
</html>