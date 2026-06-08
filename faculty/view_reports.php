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

$search = "";

if(isset($_GET['search']))
{
    $search = mysqli_real_escape_string(
        $conn,
        $_GET['search']
    );
}

$query = "

SELECT 
final_reports.*,

internships.company_name,

users.full_name,
users.enrollment_no,
users.division,
users.roll_no

FROM final_reports

JOIN internships
ON final_reports.internship_id = internships.id

JOIN users
ON internships.student_id = users.id

WHERE users.division='$faculty_division'

AND
(
    users.full_name LIKE '%$search%'
    OR
    users.enrollment_no LIKE '%$search%'
    OR
    internships.company_name LIKE '%$search%'
)

ORDER BY CAST(users.roll_no AS UNSIGNED) ASC

";

$result = mysqli_query($conn, $query);

?>

<!DOCTYPE html>
<html>
<head>

<title>Final Certificates</title>

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
    box-shadow:0px 2px 10px rgba(0,0,0,0.1);
}

.table th{
    vertical-align:middle;
}

.table td{
    vertical-align:middle;
}

.badge-updated{
    background:#0d6efd;
    color:white;
    padding:6px 10px;
    border-radius:6px;
    font-size:12px;
}

</style>

</head>

<body>

<div class="container-box">

<h2 class="mb-4">
    Internship Certificates
</h2>

<form method="GET" class="mb-4">

    <div class="row g-3">

        <div class="col-md-5">

            <input type="text"
                   name="search"
                   class="form-control"
                   placeholder="Search Student / Enrollment / Company"
                   value="<?php echo $search; ?>">

        </div>

        <div class="col-md-2">

            <button type="submit"
                    class="btn btn-primary w-100">

                Search

            </button>

        </div>

        <div class="col-md-2">

            <a href="view_reports.php"
               class="btn btn-secondary w-100">

               Reset

            </a>

        </div>

    </div>

</form>

<table class="table table-bordered table-striped">

<thead class="table-dark">

<tr>

<th>Roll No</th>
<th>Student</th>
<th>Enrollment No</th>
<th>Division</th>
<th>Company</th>
<th>Certificate</th>
<th>Status</th>

</tr>

</thead>

<tbody>

<?php

if(mysqli_num_rows($result) > 0)
{

while($row = mysqli_fetch_assoc($result))
{

?>

<tr>

<td>
    <?php echo $row['roll_no']; ?>
</td>

<td>
    <?php echo $row['full_name']; ?>
</td>

<td>
    <?php echo $row['enrollment_no']; ?>
</td>

<td>
    <?php echo $row['division']; ?>
</td>

<td>
    <?php echo $row['company_name']; ?>
</td>

<td>

<?php

if($row['certificate_file'] != '')
{

?>

<a href="../uploads/certificates/<?php echo $row['certificate_file']; ?>"
   target="_blank"
   class="btn btn-success btn-sm">

    View Certificate

</a>

<?php

}
else
{
    echo "-";
}

?>

</td>

<td>

<?php

if(isset($row['updated_at']) && $row['updated_at'] != NULL)
{
    echo "<span class='badge-updated'>
            Updated Certificate
          </span>";
}
else
{
    echo "<span class='badge bg-success'>
            Uploaded
          </span>";
}

?>

</td>

</tr>

<?php

}

}
else
{

?>

<tr>

<td colspan="7"
    class="text-center text-danger">

    No certificates uploaded.

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