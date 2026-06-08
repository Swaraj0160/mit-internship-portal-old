<?php

session_start();
include("../config.php");

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'student')
{
    header("Location: ../login.php");
    exit();
}

$student_id = $_SESSION['user_id'];

/* FETCH STUDENT DIVISION */

$getStudent = mysqli_query(
    $conn,
    "SELECT division
     FROM users
     WHERE id='$student_id'"
);

$studentData = mysqli_fetch_assoc($getStudent);

$division = $studentData['division'];

/* FETCH STUDENT WEEKLY UPDATES */

$query = mysqli_query(
    $conn,
    "SELECT
weekly_updates.*,
internships.company_name,
internships.internship_role,
weekly_deadlines.deadline_date

     FROM weekly_updates

     INNER JOIN internships
     ON weekly_updates.internship_id = internships.id

    

     LEFT JOIN weekly_deadlines
     ON weekly_deadlines.week_no = weekly_updates.week_no
     AND weekly_deadlines.division = '$division'

     WHERE internships.student_id='$student_id'

     ORDER BY weekly_updates.week_no ASC"
);

?>

<!DOCTYPE html>
<html>
<head>

<title>My Weekly Work</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
    background:#f1f5f9;
    font-family:Arial;
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

.badge-status{
    padding:8px 14px;
    border-radius:8px;
    color:white;
    font-size:14px;
    font-weight:600;
}

.on-time{
    background:#198754;
}

.late{
    background:#dc3545;
}

.pending{
    background:#6c757d;
}

</style>

</head>

<body>

<div class="container-box">
    <a href="dashboard.php"
   class="btn btn-primary mb-3">
   ← Back
</a>
    <div class="d-flex justify-content-between align-items-center mb-4">

        <h2>
            My Weekly Work
        </h2>

        <a href="add_update.php"
           class="btn btn-primary">

            + Add Weekly Update

        </a>

    </div>

    <table class="table table-bordered table-striped align-middle">

        <thead class="table-dark">

            <tr>

                <th>Week</th>
                <th>Company</th>
                <th>Role</th>
                <th width="300">Work Done</th>
                <th>Submitted At</th>
                <th>Status</th>
                <th>Review Status</th>
                <th>File</th>
                <th>Faculty Feedback</th>
                <th>Action</th>

            </tr>

        </thead>

        <tbody>

        <?php

        if(mysqli_num_rows($query) > 0)
        {
            while($row = mysqli_fetch_assoc($query))
            {

                $status = "Late Submission";
                $status_class = "late";

                if(!empty($row['deadline_date']))
                {
                    if(
                        strtotime($row['submitted_at'])
                        <= strtotime($row['deadline_date'])
                    )
                    {
                        $status = "Submitted On Time";
                        $status_class = "on-time";
                    }
                }
                else
                {
                    $status = "No Deadline";
                    $status_class = "pending";
                }

                ?>

                <tr>

                    <td>
                        Week <?php echo $row['week_no']; ?>
                    </td>

                    <td>
                        <?php echo $row['company_name']; ?>
                    </td>

                    <td>
                        <?php echo $row['internship_role']; ?>
                    </td>

                    <td>
                        <?php echo nl2br($row['work_done']); ?>
                    </td>

                    <td>

                        <?php

                        echo date(
                            "d M Y h:i A",
                            strtotime($row['submitted_at'])
                        );

                        ?>

                    </td>

                   <td>

    <span class="badge-status <?php echo $status_class; ?>">

        <?php echo $status; ?>

    </span>

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
            Pending Review
          </span>";
}

?>

</td>

<td>
    <?php

  if($row['update_file'] != "")
   {
      ?>

     <a href="../uploads/weekly_reports/<?php echo $row['update_file']; ?>"
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

                    <td width="220">

                        <?php
if($row['faculty_feedback'] != "")
{
    echo $row['faculty_feedback'];
}
else
{
    echo "<span class='text-muted'>
            No Feedback Yet
          </span>";
}

                        ?>

                    </td>

                    <td>

<?php

if($row['review_status'] == 'Rejected')
{
?>

<a href="edit_update.php?id=<?php echo $row['id']; ?>"
   class="btn btn-warning btn-sm">

   Edit & Resubmit

</a>

<?php
}
elseif($row['review_status'] == 'Accepted')
{
    echo "<span class='text-success fw-bold'>
            Locked
          </span>";
}
else
{
    echo "<span class='text-secondary'>
            Under Review
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

                <td colspan="10"
                    class="text-center text-danger">

                    No weekly work submitted yet.

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