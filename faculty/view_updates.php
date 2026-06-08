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
$week_filter = "";

/* SEARCH */

if(isset($_GET['search']))
{
    $search = mysqli_real_escape_string(
        $conn,
        $_GET['search']
    );
}

/* WEEK FILTER */

if(isset($_GET['week']))
{
    $week_filter = mysqli_real_escape_string(
        $conn,
        $_GET['week']
    );
}

/* SAVE FACULTY FEEDBACK */

if(isset($_POST['save_feedback']))
{
    $update_id = $_POST['update_id'];

    $faculty_feedback = mysqli_real_escape_string(
        $conn,
        $_POST['faculty_feedback']
    );

    $update_query = "UPDATE weekly_updates
                     SET faculty_feedback='$faculty_feedback'
                     WHERE id='$update_id'";

    mysqli_query($conn, $update_query);

    echo "
    <script>
        alert('Feedback Saved Successfully');
        window.location='view_updates.php';
    </script>
    ";
}

/* ACCEPT REPORT */

if(isset($_POST['accept_report']))
{
    $update_id = (int)$_POST['update_id'];

    $faculty_feedback = mysqli_real_escape_string(
        $conn,
        $_POST['faculty_feedback']
    );

    mysqli_query(
        $conn,
        "UPDATE weekly_updates
         SET review_status='Accepted',
             faculty_feedback='$faculty_feedback'
         WHERE id='$update_id'"
    );

    echo "
    <script>
        alert('Weekly Report Accepted');
        window.location='view_updates.php';
    </script>
    ";

    exit();
}

/* REJECT REPORT */

if(isset($_POST['reject_report']))
{
    $update_id = (int)$_POST['update_id'];

    $faculty_feedback = mysqli_real_escape_string(
        $conn,
        $_POST['faculty_feedback']
    );

    mysqli_query(
        $conn,
        "UPDATE weekly_updates
         SET review_status='Rejected',
             faculty_feedback='$faculty_feedback'
         WHERE id='$update_id'"
    );

    echo "
    <script>
        alert('Weekly Report Rejected');
        window.location='view_updates.php';
    </script>
    ";

    exit();
}

/* MAIN QUERY */

$query = "

SELECT

weekly_updates.*,

internships.company_name,

users.full_name,
users.enrollment_no,
users.division,
users.roll_no

FROM weekly_updates

JOIN internships
ON weekly_updates.internship_id = internships.id

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

";

/* APPLY WEEK FILTER */

if($week_filter != "")
{
    $query .= "
    AND weekly_updates.week_no='$week_filter'
    ";
}

/* SORT BY ROLL NUMBER */

$query .= "
ORDER BY CAST(users.roll_no AS UNSIGNED) ASC
";

$result = mysqli_query($conn, $query);

?>

<!DOCTYPE html>
<html>

<head>

<title>Student Weekly Reports</title>

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
    border-radius:18px;
    box-shadow:0px 2px 10px rgba(0,0,0,0.08);
}

table{
    width:100%;
    font-size:12px;
}

.table th{
    vertical-align:middle;
    font-size:14px;
    padding:10px;
    white-space:normal;
}

.table td{
    vertical-align:top;
    padding:8px;
    word-wrap:break-word;
    white-space:normal;
    font-size:13px;
}

.feedback-box{
    width:100%;
    min-width:160px;
    height:80px;
    resize:none;
}

.work-column{
    max-width:250px;
}

.work-preview{
    max-height:90px;
    overflow:hidden;
    line-height:1.6;
    transition:0.3s;
}

.work-preview.expanded{
    max-height:none;
}

.read-more{
    color:#0d6efd;
    cursor:pointer;
    font-size:12px;
    font-weight:600;
    display:inline-block;
    margin-top:5px;
}

.read-more:hover{
    text-decoration:underline;
}


.small-btn{
    font-size:12px;
    padding:5px 10px;
}

.badge{
    font-size:12px;
}

</style>

</head>

<body>

<div class="container-box">

<div class="d-flex align-items-center mb-4">

    <a href="dashboard.php" class="btn btn-primary me-3">
        ← Back
    </a>

    <h1 class="mb-0">
        Student Weekly Reports
    </h1>

</div>

<form method="GET" class="mb-4">

<div class="row g-3">

<div class="col-md-4">

<input type="text"
       name="search"
       class="form-control"
       placeholder="Search Student / Enrollment / Company"
       value="<?php echo $search; ?>">

</div>

<div class="col-md-3">

<select name="week"
        class="form-select">

<option value="">
    Select Week
</option>

<?php

/* ONLY 4 WEEKS FOR 1 MONTH INTERNSHIP */

for($i=1; $i<=4; $i++)
{

?>

<option value="<?php echo $i; ?>"

<?php
if($week_filter == $i)
{
    echo "selected";
}
?>

>

Week <?php echo $i; ?>

</option>

<?php

}

?>

</select>

</div>

<div class="col-md-2">

<button type="submit"
        class="btn btn-primary w-100">

Search

</button>

</div>

<div class="col-md-2">

<a href="view_updates.php"
   class="btn btn-secondary w-100">

Reset

</a>

</div>

</div>

</form>
    
<div class="table-responsive">
    
<table class="table table-bordered table-striped">

<thead class="table-dark">

<tr>

<th style="width:60px;">Roll</th>
<th style="width:130px;">Student</th>
<th style="width:150px;">Enrollment</th>
<th style="width:100px;">Company</th>
<th style="width:70px;">Week</th>
<th style="width:300px;">Work Done</th>
<th style="width:150px;">Submission</th>
<th style="width:90px;">Status</th>
<th style="width:100px;">Review</th>
<th style="width:90px;">File</th>
<th style="width:220px;">Faculty Feedback</th>
<th style="width:80px;">Action</th>

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
<?php echo $row['company_name']; ?>
</td>

<td>
Week <?php echo $row['week_no']; ?>
</td>

<td class="work-column">

<div class="work-preview"
     id="work_<?php echo $row['id']; ?>">

    <?php echo nl2br($row['work_done']); ?>

</div>

<span class="read-more"
      onclick="toggleWork(<?php echo $row['id']; ?>, this)">

Read More

</span>

</td>

<td>

<?php

if($row['submitted_at'] != '')
{
    echo date(
        "d M Y h:i A",
        strtotime($row['submitted_at'])
    );
}
else
{
    echo "-";
}

?>

</td>

<td>

<?php

/* GET DEADLINE OF CURRENT WEEK */

$week_no = $row['week_no'];

$deadline_query = "SELECT deadline_date
                   FROM weekly_deadlines
                   WHERE week_no='$week_no'
                   LIMIT 1";

$deadline_result = mysqli_query($conn, $deadline_query);

$deadline_data = mysqli_fetch_assoc($deadline_result);

if($deadline_data)
{
    $deadline_time = strtotime($deadline_data['deadline_date']);
    $submission_time = strtotime($row['submitted_at']);

    if($submission_time <= $deadline_time)
    {
        echo "<span class='badge bg-success'>
                On Time
              </span>";
    }
    else
    {
        echo "<span class='badge bg-danger'>
                Late
              </span>";
    }
}
else
{
    echo "<span class='badge bg-secondary'>
            No Deadline
          </span>";
}

?>

</td>

<td>

<?php

if($row['review_status'] == 'Accepted')
{
    echo "<span class='badge bg-success'>
            Accepted
          </span>";
}
elseif($row['review_status'] == 'Rejected')
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

if($row['update_file'] != '')
{

?>

<a href="../uploads/weekly_reports/<?php echo $row['update_file']; ?>"
   target="_blank"
   class="btn btn-primary btn-sm small-btn">

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

<form method="POST">

<textarea name="faculty_feedback"
          class="form-control feedback-box"
          placeholder="Enter feedback"><?php echo $row['faculty_feedback']; ?></textarea>

<input type="hidden"
       name="update_id"
       value="<?php echo $row['id']; ?>">

</td>

<td>

<div class="d-flex flex-column gap-1">

<button type="submit"
        name="accept_report"
        class="btn btn-success btn-sm small-btn">

Accept

</button>

<button type="submit"
        name="reject_report"
        class="btn btn-danger btn-sm small-btn">

Reject

</button>

</div>

</form>

</td>

</tr>


<?php

}

}
else
{

?>

<tr>

<td colspan="12"
    class="text-center text-danger">

No weekly reports found.

</td>

</tr>

<?php

}

?>

</tbody>

</table>
    
</div>    

</div>
</script>
<script>

function toggleWork(id, element)
{
    let workBox =
        document.getElementById(
            "work_" + id
        );

    if(workBox.classList.contains("expanded"))
    {
        workBox.classList.remove("expanded");
        element.innerHTML = "Read More";
    }
    else
    {
        workBox.classList.add("expanded");
        element.innerHTML = "Show Less";
    }
}

</script>
</body>
</html>