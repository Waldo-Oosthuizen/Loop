<?php
// start the session
session_start();

//Clear all the variables out of the session such as user_id
session_unset();

// Destroy the session
session_destroy();

// Redirect the user to the login page
header("Location: index.php");

// exit to stop the script
exit();
