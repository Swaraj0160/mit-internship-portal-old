<?php

session_start();
include("../config.php");

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'student')
{
    header("Location: ../login.php");
    exit();
}

$student_id = $_SESSION['user_id'];

/* GET STUDENT DIVISION */

$getStudent = mysqli_query(
    $conn,
    "SELECT division
     FROM users
     WHERE id='$student_id'"
);

$studentData = mysqli_fetch_assoc($getStudent);

$division = $studentData['division'];

/* GET APPROVED INTERNSHIP */

$getApprovedInternship = mysqli_query(
    $conn,
    "SELECT *
     FROM internships
     WHERE student_id='$student_id'
     AND status='Approved'
     LIMIT 1"
);

$internship = mysqli_fetch_assoc($getApprovedInternship);

/* GET INTERNSHIP ID */

$internship_id = 0;

if($internship)
{
    $internship_id = $internship['id'];
}

/* FETCH DEADLINES */

$deadline_result = mysqli_query(
    $conn,
    "SELECT *
     FROM weekly_deadlines
     WHERE division='$division'
     ORDER BY week_no ASC"
);

/* SUBMIT UPDATE */

if(isset($_POST['submit']))
{
    if(!$internship)
    {
        echo "<script>alert('No approved internship found');</script>";
    }
    else
    {
        
$internship_id = $_POST['internship_id'];
$week_no = $_POST['week_no'];
$work_done = $_POST['work_done'];

if(empty($_FILES['update_file']['name']))
{
    echo "<script>alert('Please upload weekly report file');</script>";
}
else
{

$file_name = "";
    
        if(isset($_FILES['update_file']['name']) &&
           $_FILES['update_file']['name'] != "")
        {
            $file_name =
            time() . "_" .
            $_FILES['update_file']['name'];

            $temp_name =
            $_FILES['update_file']['tmp_name'];

            move_uploaded_file(
                $temp_name,
                "../uploads/weekly_reports/".$file_name
            );
        }

        /* CHECK DUPLICATE SUBMISSION */

        $check = mysqli_query(
            $conn,
            "SELECT *
             FROM weekly_updates
             WHERE internship_id='$internship_id'
             AND week_no='$week_no'"
        );

        if(mysqli_num_rows($check) > 0)
        {
            echo "<script>alert('Week already submitted');</script>";
        }
        else
        {
            $insert = "INSERT INTO weekly_updates
            (
                internship_id,
                week_no,
                work_done,
                update_file,
                submitted_at
            )

            VALUES
            (
                '$internship_id',
                '$week_no',
                '$work_done',
                '$file_name',
                NOW()
            )";

            mysqli_query($conn, $insert);

echo "<script>alert('Weekly Update Submitted Successfully');</script>";
        }
    }
}
}

?>

<!DOCTYPE html>
<html>
<head>

<title>Weekly Update</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
    background:#f5f5f5;
}

.form-box{
    width:850px;
    margin:auto;
    margin-top:40px;
    background:white;
    padding:30px;
    border-radius:15px;
    box-shadow:0px 2px 10px rgba(0,0,0,0.1);
}

</style>

</head>

<body>
<br>
    <a href="dashboard.php"
   class="btn btn-primary mb-3">
   ← Back
</a>

<div class="form-box">

    <h2 class="mb-4 text-center">
        Weekly Progress Update
    </h2>

    <!-- DEADLINES TABLE -->

    <h4 class="mb-3">
        Weekly Submission Deadlines
    </h4>

    <table class="table table-bordered">

        <thead class="table-dark">

            <tr>

                <th>Week</th>
                <th>Deadline</th>
                <th>Deadline Status</th>
                <th>Submission Status</th>

            </tr>

        </thead>

        <tbody>

        <?php

        if(mysqli_num_rows($deadline_result) > 0)
        {
            while($deadline = mysqli_fetch_assoc($deadline_result))
            {
                $week = $deadline['week_no'];

                $check_submission = mysqli_query(
                    $conn,
                    "SELECT *
                     FROM weekly_updates
                     WHERE internship_id='$internship_id'
                     AND week_no='$week'"
                );

                $submitted = mysqli_num_rows($check_submission);

                ?>

                <tr>

                    <td>
                        Week <?php echo $week; ?>
                    </td>

                    <td>
                        <?php
                        echo date(
                            "d M Y h:i A",
                            strtotime($deadline['deadline_date'])
                        );
                        ?>
                    </td>

                    <td>

                        <?php

                        if(
                            strtotime(date("Y-m-d H:i:s"))
                            > strtotime($deadline['deadline_date'])
                        )
                        {
                            echo "<span class='badge bg-danger'>
                                    Deadline Passed
                                  </span>";
                        }
                        else
                        {
                            echo "<span class='badge bg-success'>
                                    Active
                                  </span>";
                        }

                        ?>

                    </td>

                    <td>

                        <?php

                        if($submitted > 0)
                        {
                            $submission = mysqli_fetch_assoc($check_submission);

                            if(
                                strtotime($submission['submitted_at'])
                                <= strtotime($deadline['deadline_date'])
                            )
                            {
                                echo "<span class='badge bg-success'>
                                        Submitted On Time
                                      </span>";
                            }
                            else
                            {
                                echo "<span class='badge bg-warning text-dark'>
                                        Submitted Late
                                      </span>";
                            }
                        }
                        else
                        {
                            echo "<span class='badge bg-secondary'>
                                    Not Submitted
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

                <td colspan="4" class="text-center text-danger">

                    No deadlines added by faculty yet.

                </td>

            </tr>

            <?php
        }

        ?>

        </tbody>

    </table>

    <hr>

    <!-- UPDATE FORM -->

    <form method="POST" enctype="multipart/form-data">

        <div class="mb-3">

            <label>Internship Company</label>

            <?php if($internship) { ?>

            <input type="text"
                   class="form-control"
                   value="<?php echo $internship['company_name']; ?>"
                   readonly>

            <input type="hidden"
                   name="internship_id"
                   value="<?php echo $internship['id']; ?>">

            <?php } else { ?>

            <input type="text"
                   class="form-control"
                   value="No internship approved yet"
                   readonly>

            <?php } ?>

        </div>

        <div class="mb-3">

            <label>Week Number</label>

            <select name="week_no"
                    class="form-control"
                    required
                    <?php if(!$internship) echo "disabled"; ?>>

                <option value="">
                    Select Week
                </option>

                <?php

                for($i=1; $i<=4; $i++)
                {
                    ?>

                    <option value="<?php echo $i; ?>">

                        Week <?php echo $i; ?>

                    </option>

                    <?php
                }

                ?>

            </select>

        </div>

        <div class="mb-3">

            <label>Work Done(in 2-3 line)</label>

            <textarea name="work_done"
                      class="form-control"
                      rows="5"
                      required
                      <?php if(!$internship) echo "disabled"; ?>></textarea>

        </div>

        <div class="mb-3">

            <label>Upload File(1-2 pages file)</label>

            <input type="file"
                   name="update_file"
                   class="form-control"
                   required
                   <?php if(!$internship) echo "disabled"; ?>>

        </div>

        <?php if(!$internship) { ?>

        <div class="alert alert-danger">

            You cannot submit weekly updates until your internship is approved by faculty.

        </div>

        <?php } ?>

        <button type="submit"
                name="submit"
                class="btn btn-primary w-100"
                <?php if(!$internship) echo "disabled"; ?>>

                Submit Update

        </button>

    </form>

</div>

</body>
</html>