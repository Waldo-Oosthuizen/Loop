<?php
// Start the session
session_start();
require_once 'includes/db.php';

// generate CSRF token if one does not exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Empty error message
$error = '';

if (isset($_POST['login_btn'])) {

    // Throw error and kill the process if CSRF token does not exist or does not match
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("<div class='error-messages'>
                SECURITY ALERT: Invalid CSRF Token. Request blocked.
             </div>");
    }

    // Collect form data
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Search query to search for email
    $query = "SELECT id, password_hash FROM users WHERE email = ?";
    $stmt = mysqli_prepare($conn, $query);

    // bind the email address
    mysqli_stmt_bind_param($stmt, "s", $email);
    // Execute the search query
    mysqli_stmt_execute($stmt);

    // Store the result in a variable
    $result = mysqli_stmt_get_result($stmt);

    // Check if any user with that email is found
    if ($row = mysqli_fetch_assoc($result)) {

        // use password verify to compare the typed password with the hashed password
        if (password_verify($password, $row['password_hash'])) {
            $_SESSION['user_id'] = $row['id'];

            header("Location: dashboard.php");
            exit();
        } else {
            $error = "Error: Invalid email or password";
        }
    } else {
        $error = "Error: Invalid email or password";
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loop - Login</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php include 'includes/navbar.php'; ?>
    <div class="demo_login">
    <strong >Demo Access:</strong><br>
    Email: <code>guest@example.com</code><br>
    Password: <code>demo123</code>
    </div>
    <div class="container">
        <h2 class="form-header">Login</h2>
        <?php if (!empty($error)): ?>
            <div class="error-messages">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        <form action="index.php" method="POST">
            <!-- Hidden CSRF token input with XSS prevention -->
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <div class="form-group">
                <label for="email">Email <span class="required">*</span></label>
                <input type="email" id="email" name="email" value="guest@example.com" required>
            </div>

            <div class="form-group">
                <label for="password">Password <span class="required">*</span></label>
                <input class="mb-15" type="password" id="password" name="password" value="demo123" required>
                <small>Forgot Password? <a style="color: white; text-decoration: none" href="forgot_password.php">Click here</a></small>
            </div>


            <button class="form-btn" type="submit" name="login_btn">Login</button>
        </form>

        <p class="link_text">Don't have an account? <a href="register.php" style="color:#00FFFF;">Register here</a></p>
    </div>
</body>

</html>