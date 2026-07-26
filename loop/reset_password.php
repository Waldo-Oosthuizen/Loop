<?php
require_once "includes/db.php";

$message = "";
$valid_token = false;
$token = "";
// grab the token from the URL
if (isset($_GET["token"])) {
    $token = trim($_GET["token"]);
    // get current time so match it with expiry time
    $current_time = date("Y-m-d H:i:s");

    // search the database for the token and check that it has not expired yet. 
    $query = "SELECT id FROM users WHERE reset_token = ? AND reset_expires > ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ss", $token, $current_time);
    // run the search query
    mysqli_stmt_execute($stmt);
    // store data collection that matches token and has not expired yet. 

    // If a match is found, changed valid token to true. The form will be visible to the user based
    // on the boolean $valid_token
    $valid_token = true;
} else {
    $message = "<div class='error-messages'>
                        Error: This password reset link is invalid or has expired.
                </div>";
}

// Update the password
if (isset($_POST["reset_password_btn"])) {
    $token = $_POST["token"]; // get the token from the hidden input
    $new_password = $_POST["new_password"];
    $confirm_password = $_POST["confirm_password"];

    // Check if the two passwords match
    if ($new_password !== $confirm_password) {
        $message = "<div class='error-messages'>
                        Error: The passwords do not match, please try again.
                </div>";
        $valid_token = true; // keep the form visible so that the user can try again.
    } else {
        // hash new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        // Update the database and reset the token and token expiry to null
        $update_query = "UPDATE users SET password_hash = ?, reset_token = NULL, reset_expires = NULL WHERE reset_token = ?";
        $update_stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($update_stmt, "ss", $hashed_password, $token);
        // hide the form
        $valid_token = false;

        // execute the sql command, if it was update successfully, update the message and change $valid_token to false
        if (mysqli_stmt_execute($update_stmt)) {
            $message = "<div class='success-messages'>
                            SUCCESS: Password updated! <a href='index.php' style='color:#00FFFF;'>Click here to log in</a>.
                        </div>";
            $valid_token = false;
            // If it could not update, update message and allow user to try again
        } else {
            $message = "<div class='error-messages'>
                            Error: Could not update password in the database, try again.
                        </div>";
            $valid_token = true;
        }
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Password</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container">
        <h2 class="sign-log-header">Create New Password</h2>

        <?php if (!empty($message)) echo $message; ?>

        <!-- If the token is valid, the form will show-->
        <?php if ($valid_token): ?>
            <form action="reset_password.php" method="POST">
                <!-- Print the token into the hidden input with output escaping for web security-->
                <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

                <div class="form-group" style="margin-top: 20px;">
                    <label for="new_password">New Password:</label>
                    <input type="password" id="new_password" name="new_password" required>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Confirm New Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>

                <button class="form-btn" id="register_log_btn" type="submit" name="reset_password_btn">Save Password</button>
            </form>
        <?php endif; ?>

    </div>
</body>

</html>