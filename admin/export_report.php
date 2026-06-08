<?php

session_start();
include("../config.php");

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'admin')
{
    header("Location: ../login.php");
    exit();
}

$division = $_GET['division'];

$query = "

SELECT 
users.full_name,
users.division,
users.roll_no,
internships.company_name,
internships.internship_role,
internships.internship_type,
internships.start_date,
internships.end_date,
internships.status

FROM users

LEFT JOIN internships
ON internships.student_id = users.id

WHERE users.division='$division'
AND users.role='student'

ORDER BY CAST(users.roll_no AS UNSIGNED) ASC

";

$result = mysqli_query($conn, $query);

?>

<!DOCTYPE html>
<html>
<head>

<title>Division Report</title>

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
}

@media print{

    .no-print{
        display:none;
    }

}

</style>

</head>

<body>

<div class="container-box">
    

    <h2 class="mb-4">
        Internship Report - <?php echo $division; ?>
    </h2>

    <div class="mb-3 no-print">

        <button onclick="window.print()"
                class="btn btn-dark">

            Print Report

        </button>

        <a href="download_excel.php?division=<?php echo $division; ?>"
           class="btn btn-success">

           Download Excel

        </a>

    </div>

    <table class="table table-bordered table-striped">

        <thead class="table-dark">

            <tr>

                <th>Roll No</th>
                <th>Student</th>
                <th>Division</th>
                <th>Company</th>
                <th>Role</th>
                <th>Internship Type</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Status</th>

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
                <?php echo $row['full_name']; ?>
            </td>

            <td>
                <?php echo $row['division']; ?>
            </td>

            <td>
                <?php echo $row['company_name'] ? $row['company_name'] : '-'; ?>
            </td>

            <td>
                <?php echo $row['internship_role'] ? $row['internship_role'] : '-'; ?>
            </td>

            <td>
                <?php echo $row['internship_type'] ? $row['internship_type'] : '-'; ?>
            </td>

            <td>
                <?php echo $row['start_date'] ? $row['start_date'] : '-'; ?>
            </td>

            <td>
                <?php echo $row['end_date'] ? $row['end_date'] : '-'; ?>
            </td>

            <td>

                <?php

                if($row['status'] == 'Approved')
                {
                    echo "<span class='badge bg-success'>Approved</span>";
                }
                elseif($row['status'] == 'Pending')
                {
                    echo "<span class='badge bg-warning text-dark'>Pending</span>";
                }
                elseif($row['status'] == 'Rejected')
                {
                    echo "<span class='badge bg-danger'>Rejected</span>";
                }
                else
                {
                    echo "<span class='badge bg-secondary'>Not Applied</span>";
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