<?php
// generate CSRF token if one does not exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Start the session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// connect to database
require_once 'includes/db.php';

// make sure user is logged in otherwise kick user out to login page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if the form was submitted 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['event_id'])) {
    // check token first
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Security Check Failed: Invalid CSRF token."); // end script give error
    }

    // collect the event ID from the hidden form input in the delete form
    $event_id = $_POST['event_id'];

    // get the user ID from the session
    $user_id = $_SESSION['user_id'];

    // Match the query with the event's id and the user_id. If those match, the event can be deleted. 
    // This prevents malicious users who might try and delete other people's events
    $query = "DELETE FROM events WHERE id = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $query);

    // Bind the integers two integers = 'ii'
    mysqli_stmt_bind_param($stmt, "ii", $event_id, $user_id);

    // Execute the query
    if (mysqli_stmt_execute($stmt)) {
        // if it worked, go back to dashboard
        header("Location: dashboard.php");
        exit(); // kill the script
    } else {
        die("Error: Event could not be delete from the database."); // show error and kill the script
    }
} else {
    // avoid someone from just typing delete_event.php in the browser without clicking on the form
    header("Location: dashboard.php");
    exit(); // kill the script
}
