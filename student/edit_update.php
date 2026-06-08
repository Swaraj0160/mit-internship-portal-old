<?php

session_start();
include("../config.php");

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'student')
{
    header("Location: ../login.php");
    exit();
}

$student_id = $_SESSION['user_id'];

if(!isset($_GET['id']))
{
    header("Location: my_updates.php");
    exit();
}

$update_id = $_GET['id'];

/* FETCH UPDATE */

$query = mysqli_query(
    $conn,
    "SELECT 
        weekly_updates.*,
        internships.company_name

     FROM weekly_updates

     INNER JOIN internships
     ON weekly_updates.internship_id = internships.id

     WHERE weekly_updates.id='$update_id'
     AND internships.student_id='$student_id'"
);

if(mysqli_num_rows($query) == 0)
{
    header("Location: my_updates.php");
    exit();
}

$data = mysqli_fetch_assoc($query);

/* UPDATE WORK */

if(isset($_POST['update_work']))
{
    $work_done = $_POST['work_done'];

    $file_name = $data['update_file'];

    /* CHECK NEW FILE */

    if($_FILES['update_file']['name'] != "")
    {
        $file_name = $_FILES['update_file']['name'];

        $temp_name = $_FILES['update_file']['tmp_name'];

        move_uploaded_file(
            $temp_name,
            "../uploads/weekly_reports/".$file_name
        );
    }

    mysqli_query(
    $conn,
    "UPDATE weekly_updates

     SET

     work_done='$work_done',
     update_file='$file_name',
     submitted_at=NOW(),
     review_status='Pending',
     faculty_feedback=''

     WHERE id='$update_id'"
);
    echo "<script>alert('Weekly Work Updated Successfully');</script>";

    echo "<script>
            window.location='my_updates.php';
          </script>";
}

?>

<!DOCTYPE html>
<html>
<head>

<title>Edit Weekly Work</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
    background:#f1f5f9;
}

.form-box{
    width:800px;
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

<div class="form-box">

    <h2 class="mb-4 text-center">

        Edit Weekly Work

    </h2>

    <div class="mb-4">

        <strong>Company:</strong>

        <?php echo $data['company_name']; ?>

        <br><br>

        <strong>Week:</strong>

        Week <?php echo $data['week_no']; ?>

    </div>

    <form method="POST" enctype="multipart/form-data">

        <div class="mb-3">

            <label class="form-label">

                Work Done

            </label>

            <textarea name="work_done"
                      class="form-control"
                      rows="8"
                      required><?php echo $data['work_done']; ?></textarea>

        </div>

        <div class="mb-3">

            <label class="form-label">

                Current File

            </label>

            <br>

            <?php

            if($data['update_file'] != "")
            {
            ?>

            <a href="../uploads/weekly_reports/<?php echo $data['update_file']; ?>"
               target="_blank">

               View Current File

            </a>

            <?php
            }
            else
            {
                echo "No File Uploaded";
            }

            ?>

        </div>

        <div class="mb-3">

            <label class="form-label">

                Upload New File (Optional)

            </label>

            <input type="file"
                   name="update_file"
                   class="form-control">

        </div>

        <button type="submit"
        name="update_work"
        class="btn btn-success w-100">

    Resubmit Weekly Work

</button>

    </form>

</div>

</body>
</html>