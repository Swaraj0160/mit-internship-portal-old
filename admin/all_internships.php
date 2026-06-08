<?php

session_start();
include("../config.php");

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin')
{
    header("Location: ../login.php");
    exit();
}

$division = "";

$search = "";
$type = "";

if(isset($_GET['search']))
{
    $search = trim($_GET['search']);
}

if(isset($_GET['type']))
{
    $type = trim($_GET['type']);
}


if(isset($_GET['division']))
{
    $division = $_GET['division'];
}

$where_division = "";

if($division != "")
{
    $where_division = " AND users.division='$division'";
}



/* ===========================
   Statistics Cards
=========================== */

$total_query = mysqli_query($conn,"
SELECT COUNT(*) AS total
FROM internships
JOIN users ON internships.student_id = users.id
WHERE internships.id IN
(
    SELECT MAX(id)
    FROM internships
    GROUP BY student_id
)
$where_division
");

$total_applications = mysqli_fetch_assoc($total_query)['total'];

/* APPROVED COUNT */

$approved_query = mysqli_query($conn,"
SELECT COUNT(*) AS total
FROM internships
JOIN users ON internships.student_id = users.id
WHERE internships.id IN
(
    SELECT MAX(id)
    FROM internships
    GROUP BY student_id
)
AND internships.status='Approved'
$where_division
");

$approved_count = mysqli_fetch_assoc($approved_query)['total'];

/* PENDING COUNT */

$pending_query = mysqli_query($conn,"
SELECT COUNT(*) AS total
FROM internships
JOIN users ON internships.student_id = users.id
WHERE internships.id IN
(
    SELECT MAX(id)
    FROM internships
    GROUP BY student_id
)
AND internships.status='Pending'
$where_division
");

$pending_count = mysqli_fetch_assoc($pending_query)['total'];

/* REJECTED COUNT */

$rejected_query = mysqli_query($conn,"
SELECT COUNT(*) AS total
FROM internships
JOIN users ON internships.student_id = users.id
WHERE internships.id IN
(
    SELECT MAX(id)
    FROM internships
    GROUP BY student_id
)
AND internships.status='Rejected'
$where_division
");

$rejected_count = mysqli_fetch_assoc($rejected_query)['total'];

$problem_query = mysqli_query($conn,"
SELECT COUNT(*) AS total
FROM internships
JOIN users ON internships.student_id = users.id
WHERE internships.id IN
(
    SELECT MAX(id)
    FROM internships
    GROUP BY student_id
)
AND internship_type='Problem-based Internship'
AND internships.status='Approved'
$where_division
");

$problem_count = mysqli_fetch_assoc($problem_query)['total'];

$training_query = mysqli_query($conn,"
SELECT COUNT(*) AS total
FROM internships
JOIN users ON internships.student_id = users.id
WHERE internships.id IN
(
    SELECT MAX(id)
    FROM internships
    GROUP BY student_id
)
AND internship_type='Training + Mini Project'
AND internships.status='Approved'
$where_division
");

$training_count = mysqli_fetch_assoc($training_query)['total'];

$research_query = mysqli_query($conn,"
SELECT COUNT(*) AS total
FROM internships
JOIN users ON internships.student_id = users.id
WHERE internships.id IN
(
    SELECT MAX(id)
    FROM internships
    GROUP BY student_id
)
AND internship_type='Research Internship'
AND internships.status='Approved'
$where_division
");

$research_count = mysqli_fetch_assoc($research_query)['total'];

$international_query = mysqli_query($conn,"
SELECT COUNT(*) AS total
FROM internships
JOIN users ON internships.student_id = users.id
WHERE internships.id IN
(
    SELECT MAX(id)
    FROM internships
    GROUP BY student_id
)
AND internship_type='International Internship / Visit (Short-term programs)'
AND internships.status='Approved'
$where_division
");

$international_count = mysqli_fetch_assoc($international_query)['total'];

$realworld_count = 0;

/* ===========================
   Main Table Query
=========================== */

$query = "

SELECT internships.*, users.full_name, users.division, users.enrollment_no
FROM internships
JOIN users ON internships.student_id = users.id

WHERE internships.id IN
(
    SELECT MAX(id)
    FROM internships
    GROUP BY student_id
)

";

if($division != "")
{
    $query .= " AND users.division='$division'";
}

if($search != "")
{
    $query .= " AND users.enrollment_no LIKE '%$search%'";
}

if($type != "")
{
    $query .= " AND internships.internship_type='$type'";
}

$query .= " ORDER BY users.division ASC, users.roll_no ASC";

$result = mysqli_query($conn, $query);

?>

<!DOCTYPE html>
<html>

<head>

<title>All Internships</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
    background:#f1f5f9;
}
    
.container-box{
    width:98%;
    margin:auto;
    margin-top:25px;
    background:white;
    padding:20px;
    border-radius:15px;
}

.table{
    font-size:13px;
}

.table th{
    white-space:nowrap;
    padding:8px;
}

.table td{
    padding:8px;
    vertical-align:middle;
}

.badge{
    font-size:11px;
}

.btn-sm{
    font-size:11px;
    padding:4px 8px;
    white-space: nowrap;
}

.stat-card{
    border-radius:12px;
    color:white;
    text-align:center;
    padding:15px;
    margin-bottom:20px;
    min-height:130px;
    display:flex;
    flex-direction:column;
    justify-content:center;
}
    
.offer-btn{
    white-space: nowrap;
    font-size:11px;
    padding:4px 8px;
}

.stat-card h6{
    font-size:13px;
    margin-bottom:8px;
}

.stat-card h3{
    margin:0;
    font-size:28px;
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

<h2 class="mb-4">
    All Internships
</h2>

<div class="row mb-4">

  <div class="col-md-2">
    <div class="stat-card bg-dark">

        <h6>Total Applications</h6>

        <h3><?php echo $total_applications; ?></h3>

        <hr style="margin:8px 0;border-color:rgba(255,255,255,0.2);">

        <div style="
    font-size:11px;
    white-space:nowrap;
    text-align:center;
">

    <span style="color:#4ade80;">
        Approved: <?php echo $approved_count; ?>
    </span>

    <span style="color:white;"> | </span>

    <span style="color:#facc15;">
        Pending: <?php echo $pending_count; ?>
    </span>

    <span style="color:white;"> | </span>

    <span style="color:#f87171;">
        Rejected: <?php echo $rejected_count; ?>
    </span>

</div>

    </div>
</div>
    <div class="col-md-2">
        <div class="stat-card bg-primary">
            <h6>Problem Based</h6>
            <h3><?php echo $problem_count; ?></h3>
        </div>
    </div>

    <div class="col-md-2">
        <div class="stat-card bg-success">
            <h6>Training + Project</h6>
            <h3><?php echo $training_count; ?></h3>
        </div>
    </div>

    <div class="col-md-2">
        <div class="stat-card bg-info">
            <h6>Real World</h6>
            <h3><?php echo $realworld_count; ?></h3>
        </div>
    </div>

    <div class="col-md-2">
        <div class="stat-card bg-warning text-dark">
            <h6>Research</h6>
            <h3><?php echo $research_count; ?></h3>
        </div>
    </div>

    <div class="col-md-2">
        <div class="stat-card bg-danger">
            <h6>International</h6>
            <h3><?php echo $international_count; ?></h3>
        </div>
    </div>

</div>

<form method="GET" class="row mb-4">

    <div class="col-md-3">
        <select name="division" class="form-select">

            <option value="">All Divisions</option>

            <option value="SY-1" <?php if($division=="SY-1") echo "selected"; ?>>SY-1</option>
            <option value="SY-2" <?php if($division=="SY-2") echo "selected"; ?>>SY-2</option>
            <option value="SY-3" <?php if($division=="SY-3") echo "selected"; ?>>SY-3</option>
            <option value="SY-4" <?php if($division=="SY-4") echo "selected"; ?>>SY-4</option>
            <option value="SY-5" <?php if($division=="SY-5") echo "selected"; ?>>SY-5</option>
            <option value="SY-6" <?php if($division=="SY-6") echo "selected"; ?>>SY-6</option>
            <option value="SY-7" <?php if($division=="SY-7") echo "selected"; ?>>SY-7</option>
            <option value="SY-8" <?php if($division=="SY-8") echo "selected"; ?>>SY-8</option>
            <option value="SY-9" <?php if($division=="SY-9") echo "selected"; ?>>SY-9</option>
            <option value="SY-10" <?php if($division=="SY-10") echo "selected"; ?>>SY-10</option>
            <option value="SY-11" <?php if($division=="SY-11") echo "selected"; ?>>SY-11</option>
            <option value="SY-12" <?php if($division=="SY-12") echo "selected"; ?>>SY-12</option>
            <option value="SY-13" <?php if($division=="SY-13") echo "selected"; ?>>SY-13</option>
            <option value="SY-14" <?php if($division=="SY-14") echo "selected"; ?>>SY-14</option>
            <option value="SY-15" <?php if($division=="SY-15") echo "selected"; ?>>SY-15</option>
            <option value="SY-16" <?php if($division=="SY-16") echo "selected"; ?>>SY-16</option>
            <option value="SY-17" <?php if($division=="SY-17") echo "selected"; ?>>SY-17</option>
            <option value="SY-18" <?php if($division=="SY-18") echo "selected"; ?>>SY-18</option>
            <option value="SY-19" <?php if($division=="SY-19") echo "selected"; ?>>SY-19</option>
            <option value="SY-20" <?php if($division=="SY-20") echo "selected"; ?>>SY-20</option>
            <option value="SY-21" <?php if($division=="SY-21") echo "selected"; ?>>SY-21</option>
            <option value="SY-22" <?php if($division=="SY-22") echo "selected"; ?>>SY-22</option>
        </select>
    </div>

    <div class="col-md-3">
        <input type="text"
               name="search"
               class="form-control"
               placeholder="Enrollment Number"
               value="<?php echo $search; ?>">
    </div>

    <div class="col-md-3">
        <select name="type" class="form-select">

            <option value="">All Internship Types</option>

            <option value="Problem-based Internship"
            <?php if($type=="Problem-based Internship") echo "selected"; ?>>
                Problem-based
            </option>

            <option value="Training + Mini Project"
            <?php if($type=="Training + Mini Project") echo "selected"; ?>>
                Training + Mini Project
            </option>

            <option value="Research Internship"
            <?php if($type=="Research Internship") echo "selected"; ?>>
                Research
            </option>

            <option value="International Internship / Visit (Short-term programs)"
            <?php if($type=="International Internship / Visit (Short-term programs)") echo "selected"; ?>>
                International
            </option>

        </select>
    </div>

    <div class="col-md-3">

        <button type="submit" class="btn btn-success">
            Search
        </button>

        <a href="all_internships.php"
           class="btn btn-secondary">
            Reset
        </a>

    </div>

</form>

<table class="table table-bordered table-striped">

<thead class="table-dark">

<tr>
    <th>Division</th>
    <th>Student</th>
    <th>Enrollment No</th>
    <th>Company</th>
    <th>Role</th>
    <th>Internship Type</th>
    <th>Offer Letter</th>
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

<td><?php echo $row['division']; ?></td>

<td><?php echo $row['full_name']; ?></td>

<td><?php echo $row['enrollment_no']; ?></td>

<td><?php echo $row['company_name']; ?></td>

<td><?php echo $row['internship_role']; ?></td>

<td>
<?php
if($row['status'] == 'Approved')
{
    echo $row['internship_type'];
}
else
{
    echo "-";
}
?>
</td>

<td>

<?php

if($row['status']=='Approved' && !empty($row['offer_letter']))
{
?>

<a href="../uploads/offer_letters/<?php echo $row['division']; ?>/<?php echo $row['offer_letter']; ?>"
target="_blank"
class="btn btn-sm btn-primary offer-btn">

View Offer

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

if($row['status']=='Approved')
{
    echo "<span class='badge bg-success'>Approved</span>";
}
elseif($row['status']=='Rejected')
{
    echo "<span class='badge bg-danger'>Rejected</span>";
}
else
{
    echo "<span class='badge bg-warning text-dark'>Pending</span>";
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
<td colspan="7" class="text-center">
No Internship Records Found
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