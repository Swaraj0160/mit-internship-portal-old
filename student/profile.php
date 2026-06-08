<?php

session_start();
include("../config.php");

if(!isset($_SESSION['role']) || $_SESSION['role'] != 'student')
{
    header("Location: ../login.php");
    exit();
}

$student_id = $_SESSION['user_id'];

$query = "SELECT * FROM users
          WHERE id='$student_id'";

$result = mysqli_query($conn, $query);

$user = mysqli_fetch_assoc($result);

if(isset($_POST['update']))
{
    $full_name = mysqli_real_escape_string(
        $conn,
        $_POST['full_name']
    );

    $phone = mysqli_real_escape_string(
        $conn,
        $_POST['phone']
    );

    $department = mysqli_real_escape_string(
        $conn,
        $_POST['department']
    );

    $photo_name = $_FILES['profile_photo']['name'];

    if($photo_name != "")
    {
        $photo_type = strtolower(
            pathinfo($photo_name, PATHINFO_EXTENSION)
        );

        $allowed_types = ['jpg','jpeg','png'];

        if(!in_array($photo_type, $allowed_types))
        {
            die("Only JPG, JPEG, PNG files allowed");
        }

        $new_photo_name = time()."_".$photo_name;

        $photo_tmp = $_FILES['profile_photo']['tmp_name'];

       if(move_uploaded_file(
    $photo_tmp,
    "../uploads/profile_photos/".$new_photo_name
       ))
{
    $update = "UPDATE users SET

               full_name='$full_name',
               phone='$phone',
               department='$department',
               profile_photo='$new_photo_name'

               WHERE id='$student_id'";
}
else
{
    die("Image Upload Failed");
}

        $update = "UPDATE users SET

                   full_name='$full_name',
                   phone='$phone',
                   department='$department',
                   profile_photo='$new_photo_name'

                   WHERE id='$student_id'";
    }
    else
    {
        $update = "UPDATE users SET

                   full_name='$full_name',
                   phone='$phone',
                   department='$department'

                   WHERE id='$student_id'";
    }

    mysqli_query($conn, $update);

    echo "<script>alert('Profile Updated Successfully');</script>";

    echo "<script>
            window.location='profile.php';
          </script>";
}

?>

<!DOCTYPE html>
<html>
<head>

<title>Student Profile</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>

body{
    background:#f1f5f9;
}

.profile-box{
    width:700px;
    margin:auto;
    margin-top:40px;
    background:white;
    padding:30px;
    border-radius:15px;
    box-shadow:0px 2px 10px rgba(0,0,0,0.1);
}

.profile-photo{
    width:120px;
    height:120px;
    border-radius:50%;
    object-fit:cover;
    border:4px solid #4b0082;
}

</style>

</head>

<body>

<div class="profile-box">

    <div class="text-center mb-4">

        <?php

        if($user['profile_photo'] != "")
        {

        ?>

        <img src="../uploads/profile_photos/<?php echo $user['profile_photo']; ?>"
             class="profile-photo">

        <?php

        }
        else
        {

        ?>

        <img src="https://cdn-icons-png.flaticon.com/512/149/149071.png"
             class="profile-photo">

        <?php

        }

        ?>

        <h3 class="mt-3">
            <?php echo $user['full_name']; ?>
        </h3>

    </div>

    <form method="POST" enctype="multipart/form-data">

        <div class="mb-3">

            <label>Full Name</label>

            <input type="text"
                   name="full_name"
                   class="form-control"
                   value="<?php echo $user['full_name']; ?>"
                   required>

        </div>

        <div class="mb-3">

            <label>Email</label>

            <input type="email"
                   class="form-control"
                   value="<?php echo $user['email']; ?>"
                   disabled>

        </div>

        <div class="mb-3">

            <label>Phone Number</label>

            <input type="text"
                   name="phone"
                   class="form-control"
                   value="<?php echo $user['phone']; ?>">

        </div>

        <div class="mb-3">

            <label>Department</label>

            <input type="text"
                   name="department"
                   class="form-control"
                   value="<?php echo $user['department']; ?>">

        </div>

        <div class="mb-3">

            <label>Profile Photo</label>

            <input type="file"
                   name="profile_photo"
                   class="form-control">

        </div>

        <button type="submit"
                name="update"
                class="btn btn-primary w-100">

                Update Profile

        </button>

    </form>

</div>

</body>
</html>