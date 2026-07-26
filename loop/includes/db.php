<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$db_server = "localhost";
$db_user = "fretnotm_waldo";
$db_pass = "@reUS1rious";
$db_name = "fretnotm_loop";
$conn = "";

try {
    $conn = mysqli_connect($db_server, $db_user, $db_pass, $db_name);
} catch (mysqli_sql_exception) {
    echo "Could not connect";
    die("MySQLi Connection failed: " . mysqli_connect_error() . " (Error Code: " . mysqli_connect_errno() . ")");
}
if ($conn) {
    // echo "You are connected!";
}

