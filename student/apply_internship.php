<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include("../config.php");

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'student')
{
    header("Location: ../login.php");
    exit();
}

$student_id = $_SESSION['user_id'];

/* EDIT MODE */

$edit_mode = false;
$edit_data = null;

if(isset($_GET['edit']))
{
    $edit_id = (int)$_GET['edit'];

    $edit_query = mysqli_query(
        $conn,
        "SELECT *
         FROM internships
         WHERE id='$edit_id'
         AND student_id='$student_id'"
    );

    if(mysqli_num_rows($edit_query) > 0)
    {
        $edit_mode = true;
        $edit_data = mysqli_fetch_assoc($edit_query);
    }
}


/*
CHECK IF STUDENT ALREADY HAS APPROVED INTERNSHIP
*/

$check_query = "

SELECT * FROM internships

WHERE student_id='$student_id'
AND status='Approved'

ORDER BY id DESC
LIMIT 1

";

$check_result = mysqli_query($conn, $check_query);

$already_approved = mysqli_num_rows($check_result);

if(isset($_POST['submit']))
{

    /*
    BLOCK NEW APPLICATION
    */

    if($already_approved > 0)
    {
        echo "

        <script>

        alert('You already have an approved internship. Multiple internships are not allowed at the same time.');

        window.location.href='dashboard.php';

        </script>

        ";

        exit();
    }

    $company_name = $_POST['company_name'];

    $company_contact = $_POST['company_contact'];

    $internship_role = $_POST['internship_role'];

    $start_date = $_POST['start_date'];

    $end_date = $_POST['end_date'];

    $stipend = $_POST['stipend'];

    $mode = $_POST['mode'];

    $mentor_name = $_POST['mentor_name'];

    $internship_type = $_POST['internship_type'];

/*
FILE UPLOAD
*/

$getStudent = mysqli_query($conn,"
SELECT full_name,enrollment_no,division
FROM users
WHERE id='$student_id'
");

$student = mysqli_fetch_assoc($getStudent);

$full_name = strtoupper(
    preg_replace('/[^A-Za-z0-9]/','_',$student['full_name'])
);

$enrollment = $student['enrollment_no'];
$division = $student['division'];
$base_name = $enrollment."_".$full_name;


$extension = strtolower(
    pathinfo(
        $_FILES['offer_letter']['name'],
        PATHINFO_EXTENSION
    )
);

$allowed_types = ['pdf','jpg','jpeg','png','docx'];

if(!in_array($extension, $allowed_types))
{
    die("Invalid File Type");
}

$upload_dir = "../uploads/offer_letters/".$division."/";

if(!file_exists($upload_dir))
{
    mkdir($upload_dir,0777,true);
}

$file_name =
$enrollment."_".$full_name.".".$extension;

$temp_name = $_FILES['offer_letter']['tmp_name'];

if(!move_uploaded_file(
    $temp_name,
    $upload_dir.$file_name
))
{
    die("File Upload Failed");
}
    
if(isset($_POST['edit_id']))
{
    $old_extension = '';

    $oldQuery = mysqli_query($conn,"
        SELECT offer_letter
        FROM internships
        WHERE id='".(int)$_POST['edit_id']."'
        AND student_id='$student_id'
    ");

    if($oldRow = mysqli_fetch_assoc($oldQuery))
    {
        $oldFile =
        "../uploads/offer_letters/".$division."/".
        $oldRow['offer_letter'];

        if(
            file_exists($oldFile) &&
            basename($oldFile) != $file_name
        )
        {
            unlink($oldFile);
        }
    }
}

    
    /*
    REMOVE OLD REJECTED RECORDS
    */

if(isset($_POST['edit_id']))
{
    $edit_id = (int)$_POST['edit_id'];

    $query = "

UPDATE internships

SET

company_name='$company_name',
company_contact='$company_contact',
internship_role='$internship_role',
start_date='$start_date',
end_date='$end_date',
stipend='$stipend',
mode='$mode',
mentor_name='$mentor_name',
internship_type='$internship_type',
offer_letter='$file_name',
status='Pending',
rejection_reason=''

WHERE id='$edit_id'
AND student_id='$student_id'

    ";

    mysqli_query($conn,$query);

    echo "

    <script>

    alert('Application Updated Successfully');

    window.location.href='my_internships.php';

    </script>

    ";

    exit();
}
else
{
    $check = mysqli_query(
    $conn,
    "SELECT id
     FROM internships
     WHERE student_id='$student_id'
     LIMIT 1"
);

if(mysqli_num_rows($check) > 0)
{
    $existing = mysqli_fetch_assoc($check);

    $internship_id = $existing['id'];

    $query = "
    UPDATE internships
    SET
        company_name='$company_name',
        company_contact='$company_contact',
        internship_role='$internship_role',
        start_date='$start_date',
        end_date='$end_date',
        stipend='$stipend',
        mode='$mode',
        mentor_name='$mentor_name',
        internship_type='$internship_type',
        offer_letter='$offer_letter_name',
        status='Pending'
    WHERE id='$internship_id'
    ";
}
else
{
    $query = "
    INSERT INTO internships
    (
        student_id,
        company_name,
        company_contact,
        internship_role,
        start_date,
        end_date,
        stipend,
        mode,
        mentor_name,
        internship_type,
        offer_letter,
        status
    )
    VALUES
    (
        '$student_id',
        '$company_name',
        '$company_contact',
        '$internship_role',
        '$start_date',
        '$end_date',
        '$stipend',
        '$mode',
        '$mentor_name',
        '$internship_type',
        '$offer_letter_name',
        'Pending'
    )
    ";
}

mysqli_query($conn, $query);

    echo "

    <script>

    alert('Internship Applied Successfully');

    window.location.href='dashboard.php';

    </script>

    ";

    exit();
}

    echo "

    <script>

    alert('Internship Applied Successfully');

    window.location.href='dashboard.php';

    </script>

    ";
}

?>

<!DOCTYPE html>
<html>
<head>

<title>Apply Internship</title>

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
    box-shadow:0px 0px 10px rgba(0,0,0,0.1);
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

<?php
if($edit_mode)
{
    echo "Edit Internship Application";
}
else
{
    echo "Internship Application Form";
}
?>

</h2>

<?php

if($already_approved > 0)
{

?>

<div class="alert alert-success text-center">

    <strong>
        Internship Already Approved
    </strong>

    <br><br>

    You already have an approved internship.
    You cannot apply for another internship until the current internship is completed.

</div>

<?php

}
else
{

?>

<form method="POST" enctype="multipart/form-data">
    <?php if($edit_mode){ ?>

<input type="hidden"
       name="edit_id"
       value="<?php echo $edit_data['id']; ?>">

<?php } ?>

    <div class="mb-3">

        <label>Company Name</label>

        <input type="text"
       name="company_name"
       class="form-control"
       value="<?php echo $edit_mode ? $edit_data['company_name'] : ''; ?>"
       required>

    </div>

    <div class="mb-3">

        <label>Company Contact Number</label>

        <input type="text"
       name="company_contact"
       class="form-control"
       placeholder="9876543210"
       value="<?php echo $edit_mode ? $edit_data['company_contact'] : ''; ?>"
       required>

    </div>

    <div class="mb-3">

        <label>Internship Role</label>

        <input type="text"
       name="internship_role"
       class="form-control"
       value="<?php echo $edit_mode ? $edit_data['internship_role'] : ''; ?>"
       required>

    </div>  

    <div class="row">

        <div class="col-md-6">

            <div class="mb-3">

                <label>Start Date</label>

                <input type="date"
       name="start_date"
       class="form-control"
       value="<?php echo $edit_mode ? $edit_data['start_date'] : ''; ?>"
       required>

            </div>

        </div>

        <div class="col-md-6">

            <div class="mb-3">

                <label>End Date</label>

                <input type="date"
       name="end_date"
       class="form-control"
       value="<?php echo $edit_mode ? $edit_data['end_date'] : ''; ?>"
       required>

            </div>

        </div>

    </div>

    <div class="mb-3">

        <label>Stipend</label>

        <input type="text"
       name="stipend"
       class="form-control"
       value="<?php echo $edit_mode ? $edit_data['stipend'] : ''; ?>">

    </div>

    <div class="mb-3">

        <label>Mentor Name</label>

        <input type="text"
       name="mentor_name"
       class="form-control"
       placeholder="Enter Mentor Name"
       value="<?php echo $edit_mode ? $edit_data['mentor_name'] : ''; ?>"
       required>

    </div>

    <div class="mb-3">

        <label>Internship Type</label>

       <select name="internship_type"
        class="form-control"
        required>

<option value="">Select Internship Type</option>

<option value="Problem-based Internship"
<?php if($edit_mode && $edit_data['internship_type']=="Problem-based Internship") echo "selected"; ?>>
Problem-based Internship
</option>

<option value="Training + Mini Project"
<?php if($edit_mode && $edit_data['internship_type']=="Training + Mini Project") echo "selected"; ?>>
Training + Mini Project
</option>

<option value="Real-world Exposure Internship (Field Work)"
<?php if($edit_mode && $edit_data['internship_type']=="Real-world Exposure Internship (Field Work)") echo "selected"; ?>>
Real-world Exposure Internship (Field Work)
</option>

<option value="Research Internship"
<?php if($edit_mode && $edit_data['internship_type']=="Research Internship") echo "selected"; ?>>
Research Internship
</option>

<option value="International Internship / Visit (Short-term programs)"
<?php if($edit_mode && $edit_data['internship_type']=="International Internship / Visit (Short-term programs)") echo "selected"; ?>>
International Internship / Visit (Short-term programs)
</option>

</select>

    </div>

    <div class="mb-3">

        <label>Mode</label>

        <select name="mode"
        class="form-control"
        required>

<option value="">Select Mode</option>

<option value="Online"
<?php if($edit_mode && $edit_data['mode']=="Online") echo "selected"; ?>>
Online
</option>

<option value="Offline"
<?php if($edit_mode && $edit_data['mode']=="Offline") echo "selected"; ?>>
Offline
</option>

<option value="Hybrid"
<?php if($edit_mode && $edit_data['mode']=="Hybrid") echo "selected"; ?>>
Hybrid
</option>

</select>

    </div>

    <div class="mb-3">

        <label>Upload Offer Letter</label>

        <input type="file"
               name="offer_letter"
               class="form-control"
               required>

    </div>

    <button type="submit"
        name="submit"
        class="btn btn-primary w-100">

<?php
if($edit_mode)
{
    echo "Update Application";
}
else
{
    echo "Submit Application";
}
?>

</button>

</form>

<?php

}

?>

</div>

</body>
</html>