<?php

session_start();
include("../config.php");

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'student')
{
    header("Location: ../login.php");
    exit();
}

$student_id = $_SESSION['user_id'];
$getDivision = mysqli_query($conn,"
SELECT division
FROM users
WHERE id='$student_id'
");

$userData = mysqli_fetch_assoc($getDivision);

$query = "

SELECT *

FROM internships

WHERE student_id='$student_id'

ORDER BY created_at DESC

";

$result = mysqli_query($conn, $query);

?>

<!DOCTYPE html>
<html>

<head>

<title>My Internships</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
    background:#f1f5f9;
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

.badge-approved{
    background:green;
    color:white;
    padding:6px 12px;
    border-radius:6px;
}

.badge-rejected{
    background:#dc3545;
    color:white;
    padding:6px 12px;
    border-radius:6px;
}

.badge-pending{
    background:orange;
    color:white;
    padding:6px 12px;
    border-radius:6px;
}

table{
    font-size:14px;
}

</style>

</head>

<body>

<div class="container-box">
   <a href="dashboard.php"
   class="btn btn-primary mb-3">
   ← Back
</a>

    <h1 class="mb-4">
        My Internship Applications
    </h1>

    <table class="table table-bordered table-striped">

        <thead class="table-dark">

            <tr>

                <th>Company</th>
                <th>Role</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Mode</th>
                <th>Mentor Name</th>
                <th>Internship Type</th>
                <th>Status</th>
                <th>Applied On</th>
                <th>Offer Letter</th>
                <th>Rejection Reason</th>
                <th>Action</th>

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
                <?php echo $row['company_name']; ?>
            </td>

            <td>
                <?php echo $row['internship_role']; ?>
            </td>

            <td>
                <?php echo $row['start_date']; ?>
            </td>

            <td>
                <?php echo $row['end_date']; ?>
            </td>

            <td>
                <?php echo $row['mode']; ?>
            </td>

            <td>
                <?php echo $row['mentor_name']; ?>
            </td>

            <td>
                <?php echo $row['internship_type']; ?>
            </td>

            <td>

                <?php

                if($row['status'] == 'Approved')
                {
                    echo "<span class='badge-approved'>Approved</span>";
                }
                else if($row['status'] == 'Rejected')
                {
                    echo "<span class='badge-rejected'>Rejected</span>";
                }
                else
                {
                    echo "<span class='badge-pending'>Pending</span>";
                }

                ?>

            </td>

            <td>

                <?php

                echo date('d-m-Y h:i A', strtotime($row['created_at']));

                ?>

            </td>

           <td>

<?php

if($row['offer_letter'] != "")
{

?>

<a href="<?php echo '../uploads/offer_letters/' .
$userData['division'] . '/' .
basename($row['offer_letter']); ?>"
   target="_blank"
   class="btn btn-primary btn-sm">

   View

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

if($row['rejection_reason'] != "")
{
    echo $row['rejection_reason'];
}
else
{
    echo "-";
}

?>

</td>

<td>

<?php

if($row['status'] == 'Pending')
{
?>

<a href="apply_internship.php?edit=<?php echo $row['id']; ?>"
   class="btn btn-warning btn-sm">

   Edit

</a>

<?php
}
else if($row['status'] == 'Rejected')
{
?>

<a href="apply_internship.php?edit=<?php echo $row['id']; ?>"
   class="btn btn-danger btn-sm">

   Edit & Resubmit

</a>

<?php
}
else
{
    echo "-";
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

            <td colspan="12" class="text-center">

                No Internship Applications Found

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