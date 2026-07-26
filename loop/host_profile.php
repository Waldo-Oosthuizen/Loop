<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once 'includes/db.php';

// Get the user ID from the URL
// If someone tries to visit profile.php without an ID, kick them back to search page
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: explore.php");
    exit();
}

$profile_id = $_GET['id'];

// Fetch the User's Information
$user_query = "SELECT full_name, email, profile_picture FROM users WHERE id = ?";
$stmt_user = mysqli_prepare($conn, $user_query);

// bind the params
mysqli_stmt_bind_param($stmt_user, "i", $profile_id);
mysqli_stmt_execute($stmt_user); // execute the sql command
$user_result = mysqli_stmt_get_result($stmt_user); // store user information

// Check if the user actually exists in the database
if (!$profile_user = mysqli_fetch_assoc($user_result)) {
    die("<div style='color: white; text-align: center; padding: 50px;'><h2>User not found.</h2><a href='explore.php' style='color: #00FFFF;'>Go back</a></div>");
}

// Fetch all Events hosted by this specific user
$events_query = "SELECT * FROM events WHERE user_id = ? ORDER BY event_date ASC";
$stmt_events = mysqli_prepare($conn, $events_query);
mysqli_stmt_bind_param($stmt_events, "i", $profile_id);
mysqli_stmt_execute($stmt_events); // execute sql
$events_result = mysqli_stmt_get_result($stmt_events); // store results
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!--Add username to title -->
    <title>EventPro - <?php echo htmlspecialchars($profile_user['full_name']); ?>'s Profile</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php require_once 'includes/navbar.php'; ?>

    <div class="container view-profile-container">

        <div class="profile">
            <?php
            // Default image path if use has no profile pic
            $pic = !empty($profile_user['profile_picture']) ? $profile_user['profile_picture'] : 'images/default-avatar.png';
            ?>
            <img src="<?php echo htmlspecialchars($pic); ?>" alt="Profile Picture" style="width: 120px; height: 120px; border-radius: 50%; object-fit: cover; border: 3px solid #333; margin-bottom: 15px;">

            <h2 class="view-profile-txt">
                <?php echo htmlspecialchars($profile_user['full_name']); ?>
            </h2>
            <p><?php echo htmlspecialchars($profile_user['email']); ?></p>
            <!-- Pass user_id to URL on messages page-->
            <a href=" messages.php?user_id=<?php echo $profile_id; ?>" class="dash-btn outline">
                Message User
            </a>
        </div>


        <h3 class="sign-log-header mb-15">Events Hosted</h3>

        <div class="events-container">
            <!--Check how many rows are in the events result var, if result is greater than 0 execute the while loop -->
            <?php if (mysqli_num_rows($events_result) > 0): ?>
                <?php while ($event = mysqli_fetch_assoc($events_result)): ?>
                    <div class="event">

                        <h4 class="ticket-title">
                            <?php echo htmlspecialchars($event['title']); ?>
                        </h4>

                        <span class="category">
                            <?php echo htmlspecialchars($event['category']); ?>
                        </span>

                        <p class="date-location">
                            <strong>Date:</strong> <?php echo date("F j, Y", strtotime($event['event_date'])); ?> <br>
                            <strong>Location:</strong> <?php echo htmlspecialchars($event['location']); ?>
                        </p>

                        <p class="ticket-desc">
                            <?php echo nl2br(htmlspecialchars($event['description'])); ?>
                        </p>
                    </div>
                <?php endwhile; ?>

            <?php else: ?>
                <!-- If there are no events, display this container-->
                <div class="no-events-container">
                    <p class="no-events-txt">This user hasn't hosted any events yet.</p>
                </div>
            <?php endif; ?>
        </div>

    </div>
</body>

</html>