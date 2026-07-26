<?php
require_once 'includes/db.php';
// Password reset adapted from Dave Hollingworth (2023).
// empty message to display the link
$message = "";

if (isset($_POST["reset_request_btn"])) {
    // get email from form
    $email = trim($_POST["email"]);

    // Check if email exists in the database
    // SQL query to check if email is found
    $query = "SELECT id FROM users WHERE email = ?";
    $stmt = mysqli_prepare($conn, $query);
    // bind sql statement with email
    mysqli_stmt_bind_param($stmt, "s", $email);
    // execute the search
    mysqli_stmt_execute($stmt);
    // store data records that matches the email
    mysqli_stmt_store_result($stmt);

    // If a record exists matching the email
    if (mysqli_stmt_num_rows($stmt) > 0) {
        // generate token
        $token = bin2hex(random_bytes(32));

        // Set expiration time of 1 hour from current time
        $expires = date("Y-m-d H:i:s", time() + 3600);

        // store the token and the expiration date in the user's row
        $update_query = "UPDATE users SET reset_token = ?, reset_expires = ? WHERE email = ?";
        $update_stmt = mysqli_prepare($conn, $update_query);
        mysqli_stmt_bind_param($update_stmt, "sss", $token, $expires, $email);
        mysqli_stmt_execute($update_stmt);

        // Generate the link, display it on the screen for local testing
        $reset_link = "http://localhost/eventpro/reset_password.php?token=" . $token;

        $message = "<div class='password_reset'>
                        Password reset requested! In a real app, this would be emailed to you.<br><br>
                        <strong>Click here to reset:</strong> <br>
                        <a href='$reset_link' style='color: white;'>$reset_link</a>
                    </div>";
    } else {
        // Do not say if the email exists or not, to not give any clues away to hackers
        $message = "<div class='success-messages'>
                        If that email is in our system, a reset link has been generated.
                    </div>";
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot password</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="container forgot-password">
        <h2 class="sign-log-header">Forgot Password</h2>
        <p>Demo Mode: Reset link will be shown on the screen. </p>
        <!-- Display the message with the link-->
        <?php if (!empty($message)) echo $message; ?>

        <form action="forgot_password.php" method="POST">
            <div class="form-group mt_20">
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
            </div>
            <button class="form-btn" type="submit" name="reset_request_btn">Generate Reset Link</button>
        </form>
    </div>
</body>

</html>