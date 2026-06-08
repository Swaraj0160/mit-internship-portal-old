<?php

session_start();
include("../config.php");

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'student')
{
    header("Location: ../login.php");
    exit();
}

$student_id = $_SESSION['user_id'];

$getStudent = mysqli_query(
    $conn,
    "SELECT division
     FROM users
     WHERE id='$student_id'"
);

$studentData = mysqli_fetch_assoc($getStudent);

$division = $studentData['division'];

$query = "SELECT * FROM internships
          WHERE student_id='$student_id'
          AND status='Approved'";

$result = mysqli_query($conn, $query);

if(isset($_POST['submit']))
{
    $internship_id = $_POST['internship_id'];

    $check = mysqli_query(
        $conn,
        "SELECT *
         FROM final_reports
         WHERE internship_id='$internship_id'"
    );

    if(mysqli_num_rows($check) > 0)
    {
        echo "<script>
                alert('Certificate already submitted');
              </script>";
    }
    else
    {
        $certificate_file =
        time().'_'.$_FILES['certificate_file']['name'];

        $upload_folder =
        "../uploads/certificates/".$division."/";

        if(!file_exists($upload_folder))
        {
            mkdir($upload_folder,0777,true);
        }

        if(
            move_uploaded_file(
                $_FILES['certificate_file']['tmp_name'],
                $upload_folder.$certificate_file
            )
        )
        {
            mysqli_query(
                $conn,
                "INSERT INTO final_reports
                (
                    internship_id,
                    certificate_file
                )
                VALUES
                (
                    '$internship_id',
                    '$certificate_file'
                )"
            );

            echo "<script>
                    alert('Certificate Uploaded Successfully');
                    window.location='upload_report.php';
                  </script>";
        }
        else
        {
            echo "<script>
                    alert('File Upload Failed');
                  </script>";
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>

<title>Upload Internship Certificate</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<style>

body{
    background:#f5f5f5;
}

.form-box{
    width:700px;
    margin:auto;
    margin-top:40px;
    background:white;
    padding:30px;
    border-radius:15px;
}

</style>

</head>

<body><br>
        <a href="dashboard.php"
   class="btn btn-primary mb-3">
   ← Back
</a>

<div class="form-box">

    <h2 class="mb-4 text-center">
        Internship Certificate Submission
    </h2>
    
    
    <?php

$alreadySubmitted = mysqli_query(
    $conn,
    "SELECT final_reports.*
     FROM final_reports
     JOIN internships
     ON final_reports.internship_id = internships.id
     WHERE internships.student_id='$student_id'"
);

if(mysqli_num_rows($alreadySubmitted) > 0)
{
?>

<div class="alert alert-success text-center">

    <h5>
        ✓ Internship Certificate Already Submitted
    </h5>

    <p class="mb-0">
        Your certificate has been uploaded successfully.
    </p>

</div>

<?php
}
else
{
?>

    <form method="POST" enctype="multipart/form-data">

        <div class="mb-3">

            <label>Select Internship</label>

            <select name="internship_id"
                    class="form-control"
                    required>

                <?php

                while($row = mysqli_fetch_assoc($result))
                {

                ?>

                <option value="<?php echo $row['id']; ?>">

                    <?php echo $row['company_name']; ?>

                </option>

                <?php

                }

                ?>

            </select>

        </div>

        <div class="mb-3">

            <label>Upload Internship Certificate</label>

            <input type="file"
                   name="certificate_file"
                   class="form-control"
                   required>

        </div>

        <button type="submit"
                name="submit"
                class="btn btn-primary w-100">

                Upload Certificate

        </button>

    </form>

    <?php
}
?>
    
</div>

</body>
</html>