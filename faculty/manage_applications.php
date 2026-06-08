<?php

session_start();
include("../config.php");

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'faculty')
{
    header("Location: ../login.php");
    exit();
}

$faculty_id = $_SESSION['user_id'];

$faculty_query = "SELECT * FROM users
                  WHERE id='$faculty_id'";

$faculty_result = mysqli_query($conn, $faculty_query);

$faculty = mysqli_fetch_assoc($faculty_result);

$faculty_division = $faculty['faculty_division'];

$search = "";
$status_filter = "";
$type_filter = "";



$query = "

SELECT
internships.*,
users.full_name,
users.enrollment_no,
users.division,
users.roll_no

FROM internships

JOIN users
ON internships.student_id = users.id

WHERE internships.id IN
(
    SELECT MAX(id)
    FROM internships
    GROUP BY student_id
)

AND users.division='$faculty_division'

";

if(isset($_GET['search']) && $_GET['search'] != "")
{
    $search = mysqli_real_escape_string(
        $conn,
        $_GET['search']
    );

    $query .= " AND (
                    users.full_name LIKE '%$search%'
                    OR internships.company_name LIKE '%$search%'
                    OR users.enrollment_no LIKE '%$search%'
                    OR internships.internship_type LIKE '%$search%'
                )";
}

if(isset($_GET['status']) && $_GET['status'] != "")
{
    $status_filter = $_GET['status'];

    $query .= " AND internships.status='$status_filter'";
}

if(isset($_GET['internship_type']) && $_GET['internship_type'] != "")
{
    $type_filter = $_GET['internship_type'];

    $query .= " AND internships.internship_type='$type_filter'";
}

$query .= " ORDER BY
            CAST(TRIM(users.roll_no) AS UNSIGNED) ASC,
            users.full_name ASC";

$result = mysqli_query($conn, $query);

$count_query = "

SELECT COUNT(*) as total

FROM internships

JOIN users
ON internships.student_id = users.id

WHERE internships.id IN
(
    SELECT MAX(id)
    FROM internships
    GROUP BY student_id
)

AND users.division='$faculty_division'

";

$count_result = mysqli_query($conn, $count_query);

$count_row = mysqli_fetch_assoc($count_result);

$total_records = $count_row['total'];


/* INTERNSHIP TYPE COUNTS - ONLY LATEST APPLICATION OF EACH STUDENT */

$total_applications = mysqli_num_rows(mysqli_query($conn,"
SELECT internships.id
FROM internships
JOIN users ON internships.student_id = users.id
WHERE internships.id IN
(
    SELECT MAX(id)
    FROM internships
    GROUP BY student_id
)
AND users.division='$faculty_division'
"));

$problem_count = mysqli_num_rows(mysqli_query($conn,"
SELECT internships.id
FROM internships
JOIN users ON internships.student_id = users.id
WHERE internships.id IN
(
    SELECT MAX(id)
    FROM internships
    GROUP BY student_id
)
AND users.division='$faculty_division'
AND internships.status='Approved'
AND internships.internship_type='Problem-based Internship'
"));

$training_count = mysqli_num_rows(mysqli_query($conn,"
SELECT internships.id
FROM internships
JOIN users ON internships.student_id = users.id
WHERE internships.id IN
(
    SELECT MAX(id)
    FROM internships
    GROUP BY student_id
)
AND users.division='$faculty_division'
AND internships.status='Approved'
AND internships.internship_type='Training + Mini Project'
"));

$realworld_count = mysqli_num_rows(mysqli_query($conn,"
SELECT internships.id
FROM internships
JOIN users ON internships.student_id = users.id
WHERE internships.id IN
(
    SELECT MAX(id)
    FROM internships
    GROUP BY student_id
)
AND users.division='$faculty_division'
AND internships.status='Approved'
AND internships.internship_type='Real-world Exposure Internship (Field Work)'
"));

$research_count = mysqli_num_rows(mysqli_query($conn,"
SELECT internships.id
FROM internships
JOIN users ON internships.student_id = users.id
WHERE internships.id IN
(
    SELECT MAX(id)
    FROM internships
    GROUP BY student_id
)
AND users.division='$faculty_division'
AND internships.status='Approved'
AND internships.internship_type='Research Internship'
"));

$international_count = mysqli_num_rows(mysqli_query($conn,"
SELECT internships.id
FROM internships
JOIN users ON internships.student_id = users.id
WHERE internships.id IN
(
    SELECT MAX(id)
    FROM internships
    GROUP BY student_id
)
AND users.division='$faculty_division'
AND internships.status='Approved'
AND internships.internship_type='International Internship / Visit (Short-term programs)'
"));




?>


<!DOCTYPE html>
<html>
<head>

<title>Manage Applications</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
rel="stylesheet">

<style>

body{
    background:#f5f5f5;
    font-family:Arial, sans-serif;
}

.container-box{
    width:99%;
    margin:auto;
    margin-top:20px;
    background:white;
    padding:20px;
    border-radius:15px;
    box-shadow:0px 0px 10px rgba(0,0,0,0.1);
}

/* TABLE */

.table{
    width:100%;
    font-size:12px;
    table-layout:auto;
    vertical-align:middle;
}

.table th{
    white-space:nowrap;
    text-align:center;
    vertical-align:middle;
    padding:6px;
    font-size:12px;
}

.table td{
    padding:6px;
    vertical-align:middle;
    word-break:break-word;
}

/* STATUS BADGES */

.badge{
    font-size:10px;
    padding:4px 8px;
}

/* ACTION BUTTONS */

.action-buttons{
    display:flex;
    flex-direction:column;
    gap:4px;
}

.action-buttons .btn{
    font-size:11px;
    padding:3px 6px;
}

/* REJECTION REASON */

.rejection-box{
    max-width:150px;
    font-size:11px;
    word-wrap:break-word;
}

/* INTERNSHIP COUNT CARDS */

.type-card{
    border-radius:12px;
    padding:12px;
    text-align:center;
    color:white;
    font-weight:bold;
    height:100%;
}

.type-card h6{
    font-size:12px;
    margin-bottom:8px;
}

.type-card h3{
    font-size:32px;
    margin:0;
}

/* COMPACT COLUMN WIDTHS */

.table th:nth-child(1),
.table td:nth-child(1){
    width:130px;
}

.table td{
    padding:6px;
    vertical-align:middle;
    word-break:break-word;
}

.table th:nth-child(5),
.table td:nth-child(5){
    width:120px;
}

.table th:nth-child(7),
.table td:nth-child(7){
    width:120px;
}

.table th:nth-child(10),
.table td:nth-child(10){
    width:120px;
}

.table th:nth-child(11),
.table td:nth-child(11){
    width:140px;
}

.table th:nth-child(12),
.table td:nth-child(12){
    width:140px;
}

.table th:nth-child(13),
.table td:nth-child(13){
    width:90px;
}

/* LINKS */

a{
    font-size:12px;
}

/* MOBILE */

@media(max-width:1200px){

    .table{
        font-size:11px;
    }

    .table th,
    .table td{
        padding:4px;
    }

    .type-card h3{
        font-size:24px;
    }
}

</style>

</head>

<body>

<div class="container-box">

<div class="d-flex align-items-center mb-4">

    <a href="dashboard.php" class="btn btn-primary me-3">
        ← Back
    </a>

    <h2 class="mb-0">
        Internship Applications
    </h2>

</div>

<h5 class="mb-4">

Division:
<?php echo $faculty_division; ?>

</h5>

<div class="row g-3 mb-4">

    <div class="col-md-2">
    <div class="type-card bg-dark">
        <h6>Total Applications</h6>
        <h3><?php echo $total_applications; ?></h3>
    </div>
</div>

    <div class="col-md-2">
        <div class="type-card bg-primary">
            <h6>Problem Based</h6>
            <h3><?php echo $problem_count; ?></h3>
        </div>
    </div>

    <div class="col-md-2">
        <div class="type-card bg-success">
            <h6>Training + Project</h6>
            <h3><?php echo $training_count; ?></h3>
        </div>
    </div>

    <div class="col-md-2">
        <div class="type-card bg-info">
            <h6>Real World</h6>
            <h3><?php echo $realworld_count; ?></h3>
        </div>
    </div>

    <div class="col-md-2">
        <div class="type-card bg-warning text-dark">
            <h6>Research</h6>
            <h3><?php echo $research_count; ?></h3>
        </div>
    </div>

    <div class="col-md-2">
        <div class="type-card bg-danger">
            <h6>International</h6>
            <h3><?php echo $international_count; ?></h3>
        </div>
    </div>



</div>

<form method="GET" class="row mb-4 g-3">

<div class="col-md-4">

<input type="text"
       name="search"
       class="form-control"
       placeholder="Search Student / Company / Enrollment / Internship Type"
       value="<?php echo $search; ?>">

</div>

<div class="col-md-2">

<select name="status"
        class="form-control">

<option value="">All Status</option>

<option value="Pending"
<?php if($status_filter=="Pending") echo "selected"; ?>>
Pending
</option>

<option value="Approved"
<?php if($status_filter=="Approved") echo "selected"; ?>>
Approved
</option>

<option value="Rejected"
<?php if($status_filter=="Rejected") echo "selected"; ?>>
Rejected
</option>

</select>

</div>

<div class="col-md-3">

<select name="internship_type"
        class="form-control">

<option value="">All Internship Types</option>

<option value="Problem-based Internship"
<?php if($type_filter=="Problem-based Internship") echo "selected"; ?>>
Problem-based Internship
</option>

<option value="Training + Mini Project Internship"
<?php if($type_filter=="Training + Mini Project Internship") echo "selected"; ?>>
Training + Mini Project Internship
</option>

<option value="Real-world Exposure Internship (Field Work)"
<?php if($type_filter=="Real-world Exposure Internship (Field Work)") echo "selected"; ?>>
Real-world Exposure Internship (Field Work)
</option>

<option value="Research Internship"
<?php if($type_filter=="Research Internship") echo "selected"; ?>>
Research Internship
</option>

<option value="International Internship / Visit (Short-term programs)"
<?php if($type_filter=="International Internship / Visit (Short-term programs)") echo "selected"; ?>>
International Internship / Visit
</option>


</select>

</div>

<div class="col-md-1">

<button type="submit"
        class="btn btn-primary w-100">

Search

</button>

</div>

<div class="col-md-2">

<a href="manage_applications.php"
class="btn btn-secondary w-100">

Reset

</a>

</div>

</form>

<div class="table-responsive" style="overflow-x:hidden;">

<table class="table table-bordered table-hover align-middle">

<thead class="table-dark">

<tr>

<th>Student</th>
<th>Enrollment No</th>
<th>Division</th>
<th>Roll No</th>
<th>Company</th>
<th>Company Contact</th>
<th>Role</th>
<th>Status</th>
<th>Offer Letter</th>
<th>Mentor Name</th>
<th>Internship Type</th>
<th>Rejection Reason</th>
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
<?php echo $row['full_name']; ?>
</td>

<td>
<?php echo $row['enrollment_no']; ?>
</td>

<td>
<?php echo $row['division']; ?>
</td>

<td>
<?php echo $row['roll_no']; ?>
</td>

<td>
<?php echo $row['company_name']; ?>
</td>

<td>
<?php echo $row['company_contact']; ?>
</td>

<td>
<?php echo $row['internship_role']; ?>
</td>

<td>

<?php

if($row['status'] == "Approved")
{
    echo "<span class='badge bg-success'>
          Approved
          </span>";
}
else if($row['status'] == "Rejected")
{
    echo "<span class='badge bg-danger'>
          Rejected
          </span>";
}
else
{
    echo "<span class='badge bg-warning text-dark'>
          Pending
          </span>";
}

?>

</td>

<td>

<?php

if($row['offer_letter'] != "")
{

?>

<a href="../uploads/offer_letters/<?php echo $row['division']; ?>/<?php echo $row['offer_letter']; ?>"
target="_blank">

View File

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
<?php echo $row['mentor_name']; ?>
</td>

<td>
<?php echo $row['internship_type']; ?>
</td>

<td class="rejection-box">

<?php

if($row['rejection_reason'] == "")
{
    echo "-";
}
else
{
    echo $row['rejection_reason'];
}

?>

</td>

<td>

<?php

if($row['status'] == "Pending")
{
?>

<div class="action-buttons">

<a href="approve.php?id=<?php echo $row['id']; ?>"
class="btn btn-success btn-sm">

Approve

</a>

<a href="reject_internship.php?id=<?php echo $row['id']; ?>"
class="btn btn-danger btn-sm">

Reject

</a>

</div>

<?php

}
else if($row['status'] == "Approved")
{
?>

<div class="text-center">

<a href="undo_status.php?id=<?php echo $row['id']; ?>"
class="btn btn-warning btn-sm">

Undo

</a>

</div>

<?php

}
else if($row['status'] == "Rejected")
{
?>

<div class="text-center">

<a href="undo_status.php?id=<?php echo $row['id']; ?>"
class="btn btn-secondary btn-sm">

Undo

</a>

</div>

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



</div>

</body>
</html>