<?php
// start session
session_start();
require_once 'includes/db.php';

// Generate CSRF security token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// empty variables for error and success messages
$error = null;
$success = null;


if (isset($_POST["register_btn"])) {

    // CSRF Security check
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("<div class='error-messages'>
                SECURITY ALERT: Invalid CSRF Token. Request blocked.
             </div>");
    }

    // Collect form information 
    $full_name = trim($_POST["full_name"]);
    $email = trim($_POST["email"]);
    $password = $_POST["password"];

    // Architecture based on the  PHP manual PHP (2019)
    // check if the email already exists
    $query = "SELECT id FROM users WHERE email = ?";
    $stmt = mysqli_prepare($conn, $query);
    // binding the query with the variable that is searched ($email)
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt); // Executing the sql query
    mysqli_stmt_store_result($stmt); // store the data records that matches with email variable
    // if there is at least one email that matches, throw error
    if (mysqli_stmt_num_rows($stmt) > 0) {
        $error = "Error: Email is already active.";
    } else {

        // Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $pic_path = NULL; // Default to null if the user does not upload a profile picture

        // Check if the user attempted to upload a profile pic
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] !== UPLOAD_ERR_NO_FILE) {

            // Catch PHP's internal server limits
            if ($_FILES['profile_picture']['error'] === UPLOAD_ERR_INI_SIZE || $_FILES['profile_picture']['error'] === UPLOAD_ERR_FORM_SIZE) {
                $error = "Error: Your file is too big.";
            }
            // Catch unknown errors
            elseif ($_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK) {
                $error = "Error: An unknown error occurred during upload.";
            } else {
                $file_temp_path = $_FILES['profile_picture']['tmp_name']; // File temporary destination
                $file_name = basename($_FILES['profile_picture']['name']); // using basename for security
                $file_size = $_FILES['profile_picture']['size']; // File size
                $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION)); // get file extension
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'webp'];

                // Check if extension is in allowed array
                if (!in_array($file_extension, $allowed_extensions)) {
                    $error = "Error: Invalid file type. Only JPG, PNG, and WEBP allowed.";
                }
                // Check that file is not bigger than 2MB (2,097,152 bytes = 2MB)
                elseif ($file_size > 2097152) {
                    $error = "Error: Your file is too big.";
                }
                // If extension and file size checks are passed then the file can be moved
                else {
                    // Give file a new name to avoid another profile pic to be overridden
                    $new_file_name = uniqid('profile_', true) . "." . $file_extension;

                    // file pathway to upload the file to uploads folder
                    $upload_path = 'uploads/' . $new_file_name;

                    // Upload the file into uploads folder, if it cant, throw error
                    if (move_uploaded_file($file_temp_path, $upload_path)) {
                        $pic_path = $upload_path; // update original pic path variable
                    } else {
                        $error = "Error: Failed to save the file to the server.";
                    }
                }
            }

            // Profile pic upload logic adapted from Vincy (2024). 
        }

        // Only insert user to database if there are no errors
        if ($error === null) {
            // Store the user in the database
            $insert_query = "INSERT INTO users (full_name, email, password_hash, profile_picture) VALUES (?,?,?,?)";
            $insert_stmt = mysqli_prepare($conn, $insert_query);

            // bind the 4 pieces of data
            mysqli_stmt_bind_param($insert_stmt, "ssss", $full_name, $email, $hashed_password, $pic_path);

            if (mysqli_stmt_execute($insert_stmt)) {
                $success = "SUCCESS: Account registered. Proceed to login.";
            } else {
                $error = "Error: Registration failed.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EventPro - Register</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php require_once 'includes/navbar.php'; ?>

    <div class="container">

        <h2 class="sign-log-header">Create Account</h2>
        <?php if (!empty($error)): ?>
            <div class="error-messages">
                <!-- Output escaping to for web security-->
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        <?php if (!empty($success)): ?>
            <div class="success-messages">
                <!-- Output escaping to for web security-->
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        <div id="js-errors" class="error-messages mb-15" style="display: none;"></div>
        <form id="registerForm" action="register.php" method="POST" enctype="multipart/form-data">
            <!-- Hidden csrf token input with XSS attack prevention -->
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

            <div class="form-group">
                <label for="full_name">Full Name <span class="required">*</span></label>
                <input type="text" id="full_name" name="full_name" required>
            </div>

            <div class="form-group">
                <label for="email">Email <span class="required">*</span></label>
                <input type="email" id="email" name="email" required>
            </div>

            <div class="form-group">
                <label for="password">Password <span class="required">*</span></label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="form-group">
                <span class="input-label">Profile Picture (optional)</span>

                <label class="custom-file-upload" for="profile_picture">

                    <svg id='upload_icon' xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                    </svg>

                    <span>Upload Image</span>
                </label>

                <input type="file" id="profile_picture" name="profile_picture" accept="image/png, image/jpeg, image/webp">

                <small class="file-help-text">Max 2MB. JPG/PNG/WEBP only.</small>

                <!-- Profile Picture preview -->
                <div style="text-align: center; margin-top: 15px;">
                    <img id="preview_img" src="#" alt="Profile Preview" style="display: none; width: 250px;  border-radius: 5px; object-fit: cover; border: 1px solid rgba(255, 255, 255, 0.3)">
                </div>
            </div>

            <button class="form-btn" type="submit" name="register_btn">Register</button>
        </form>

        <p class="link_text">Already have an account? <a href="index.php" style="color:#00FFFF">Log in here</a></p>
    </div>
    <script src="js/validation.js"></script>
    <script>
        // Profile pic preview adapted from dcode (2019).
        // Event listener to listen for when the file is uploaded
        document
            .getElementById('profile_picture')
            .addEventListener('change', function(event) {
                // store the file in a variable
                const file = event.target.files[0];

                if (file) {
                    const reader = new FileReader();
                    // wait for image to load first before showing preview
                    reader.addEventListener('load', function(e) {
                        // find the image tag with no source
                        const preview = document.getElementById('preview_img');
                        preview.src = e.target.result; // set the image url to preview src
                        preview.style.display = 'inline-block'; // change display none to inline block
                    });

                    // trigger the reader to read to file url
                    reader.readAsDataURL(file);
                }
            });
    </script>

</body>

</html>