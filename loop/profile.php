<?php

// generate CSRF token if one does not exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Start the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'includes/db.php';

// check if the user is logged in otherwise kick them out
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';

// Fetch the current user details
$fetch_query = "SELECT full_name, email, profile_picture FROM users WHERE id = ?";
$fetch_stmt = mysqli_prepare($conn, $fetch_query);
mysqli_stmt_bind_param($fetch_stmt, "i", $user_id);
// execute the sql command
if (mysqli_stmt_execute($fetch_stmt)) {
    $user_profile = mysqli_fetch_assoc(mysqli_stmt_get_result($fetch_stmt));
} else {
    $message = $message = "<div class='error-messages'>Error: Could not fetch user data.</div>";
}

// if current pic is empty, show user profile pic
$current_pic = !empty($user_profile['profile_picture']) ? $user_profile['profile_picture'] : 'images/default-avatar.png';

// Handle the profile update
if (isset($_POST['update_profile_btn'])) {
    // check token first
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Security Check Failed: Invalid CSRF token."); // end script give error
    }


    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);

    // Update query
    $update_query = "UPDATE users SET full_name = ?, email = ? WHERE id = ?";
    $update_stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($update_stmt, "ssi", $full_name, $email, $user_id);

    if (mysqli_stmt_execute($update_stmt)) {
        $message = "<div class='success-messages'>Profile updated successfully!</div>";
    } else {
        $message = "<div class='error-messages'>Error updating profile details.</div>";
    }

    // Handle the Image Upload
    if (!empty($_FILES['profile_picture']['name'])) {

        $file_name = basename($_FILES['profile_picture']['name']); // using basename for security
        $temp_file_path = $_FILES['profile_picture']['tmp_name'];
        $target_dir = "images/";

        // Generate a unique name so that the file does not override some else's file
        $unique_name = uniqid() . "_" . $file_name;
        $target_file = $target_dir . $unique_name;

        if (move_uploaded_file($temp_file_path, $target_file)) {
            // Save the new image path to the database
            $pic_query = "UPDATE users SET profile_picture = ? WHERE id = ?";
            $pic_stmt = mysqli_prepare($conn, $pic_query);
            mysqli_stmt_bind_param($pic_stmt, "si", $target_file, $user_id);
            mysqli_stmt_execute($pic_stmt);

            $message = "<div class='success-messages'>Profile and picture updated successfully!</div>";
        } else {
            $message = "<div class='error-messages'>Error: Could not save the image file.</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EventPro - My Profile</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php require_once 'includes/navbar.php'; ?>

    <div class="container">
        <h2 class="sign-log-header">My Profile</h2>

        <?php if (!empty($message)) echo $message; ?>

        <form action="profile.php" method="POST" enctype="multipart/form-data">
            <!-- Hidden CSRF TOKEN-->
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <div class="form-group">
                <label for="full_name">Full Name <span class="required">*</span></label>
                <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user_profile['full_name']); ?>" required>
            </div>

            <div class="form-group">
                <label for="email">Email Address <span class="required">*</span></label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_profile['email']); ?>" required>
            </div>


            <div class="form-group">
                <span class="input-label">Profile Picture (optional)</span>

                <label class="custom-file-upload" for="profile_picture">

                    <svg id='upload_icon' xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                    </svg>

                    <span>Change / Upload Image</span>
                </label>

                <input type="file" id="profile_picture" name="profile_picture" accept="image/png, image/jpeg, image/webp">

                <small class="file-help-text">Max 2MB. JPG/PNG/WEBP only.</small>

                <!-- Profile Picture preview -->
                <div style="text-align: center; margin-top: 15px;">
                    <img id="preview_img" src="<?php echo htmlspecialchars($current_pic); ?>" alt="Profile Preview" style="width: 250px;  border-radius: 5px; object-fit: cover; border: 1px solid rgba(255, 255, 255, 0.3)">
                </div>
            </div>

            <button class="form-btn" type="submit" name="update_profile_btn" style="width: 100%; margin-top: 10px;">Save Profile</button>
            <a href="dashboard.php" class="back-to-dash">Back to Dashboard</a>
        </form>
    </div>
    <script>
        // Profile pic preview adapted from dcode (2019).
        // Event listener to listen for when the file is uploaded
        document.getElementById('profile_picture').addEventListener('change', function(event) {
            // store the file in a variable
            const file = event.target.files[0];

            if (file) {
                const reader = new FileReader();
                // wait for image to load first before showing preview
                reader.addEventListener("load", function(e) {
                    // find the image tag with no source
                    const preview = document.getElementById('preview_img');
                    preview.src = e.target.result; // set the image url to preview src
                    preview.style.display = 'inline-block'; // change display none to inline block
                })

                // trigger the reader to read to file url
                reader.readAsDataURL(file);
            }
        })
    </script>
</body>

</html>