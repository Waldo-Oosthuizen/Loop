<?php


// start the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'includes/db.php';
// Check if the user is actually logged in
// If the 'user_id' session variable does not exist, kick them out
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch the user details
$user_id = $_SESSION['user_id'];
$query = "SELECT full_name FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Store the username
$user = mysqli_fetch_assoc($result);


// Fetch the user's events and order by date in ascending orders
$event_query = "SELECT * FROM events WHERE user_id = ? ORDER BY event_date ASC";
$event_stmt = mysqli_prepare($conn, $event_query);
mysqli_stmt_bind_param($event_stmt, "i", $user_id);
mysqli_stmt_execute($event_stmt);

// store events in array
$events_result = mysqli_stmt_get_result($event_stmt);



?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EventPro - Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php require_once 'includes/navbar.php'; ?>

    <div class="dashboard-container">
        <div class="dash-banner">
            <div class="left-banner">
                <h2 class="dashboard-header">Welcome to your Dashboard!</h2>
                <p>Manage events, search users/events, and message others.</p>
                <div class="dash-btns">
                    <a class="dash-btn" href="create_event.php">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                        </svg>
                        Create Event
                    </a>

                    <a class="dash-btn" href="search.php">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                        Search
                    </a>

                    <a href="messages.php" class="dash-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 9.75a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375m-13.5 3.01c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.184-4.183a1.14 1.14 0 0 1 .778-.332 48.294 48.294 0 0 0 5.83-.498c1.585-.233 2.708-1.626 2.708-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
                        </svg>
                        Message
                    </a>
                </div>

            </div>

            <div class="right-banner">
                <h2>Security Checklist</h2>
                <div>
                    <p><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20" class="svg_mb">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                        </svg>
                        Prepared SQL statements for SQL injection prevention.
                    </p>

                    <p><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20" class="svg_mb">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                        </svg>
                        Output escaping to prevent XSS.
                    </p>
                    <p><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20" class="svg_mb">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                        </svg> CSRF Tokens on forms.</p>
                    <p><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20" class="svg_mb">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                        </svg> Validated image uploads.</p>
                    <p><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20" class="svg_mb">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                        </svg> Session protected pages.</p>
                </div>
            </div>

        </div>


        <hr class="hr-style">

        <div>
            <h3 class="header-style">
                My Upcoming Events
            </h3>

            <div class="events-container">

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
                            <div class="event-actions">

                                <a href="edit_event.php?id=<?php echo $event['id']; ?>" class="nav-btn outline edit-btn">
                                    EDIT
                                </a>

                                <form action="delete_event.php" method="POST" onsubmit="return confirm('Are you sure you want to permanently delete this event?');" style="display: contents;">
                                    <!-- Hidden CSRF TOKEN-->
                                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                                    <!-- Hidden event ID-->
                                    <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">

                                    <button type="submit" class="del-btn">
                                        DELETE
                                    </button>
                                </form>

                            </div>
                        </div>

                    <?php endwhile; ?>
                <?php else: ?>
                    <!-- If there are no events in the database -->
                    <div class="no-events-container">
                        <p class="no-events-txt">You have not created any events yet.</p>
                        <a href="create_event.php" class="nav-btn inline-block">Create an Event</a>
                    </div>
                <?php endif; ?>

            </div>
        </div>


    </div>
</body>

</html>