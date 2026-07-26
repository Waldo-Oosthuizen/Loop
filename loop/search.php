<?php
// start the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'includes/db.php';

// check if the user is logged in, otherwise kick them out
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get the search term, fall back to empty string if nothing has been searched
// This results in the database matching all events and users, showing everything
$search_term = $_GET['search'] ?? '';

// use wildcards (%) to concatenate the search term with. %search_term%
// This allows the us to find the search term anywhere inside of text
$search_param = '%' . $search_term . '%';

// Search query to fetch all users and left join their events so that everyone is searchable.
//  Results are filtered by the search term nd sorted into users two groups, users with events at the
// top sorted by date and users without events at the bottom sorted by name
$query = "SELECT events.*, users.id AS host_id, users.full_name, users.profile_picture 
FROM users LEFT JOIN events  ON events.user_id = users.id
WHERE events.title LIKE ? 
 OR events.category LIKE ? 
 OR events.location LIKE ? 
 OR users.full_name LIKE ? 
 OR users.email LIKE ? 
ORDER BY (events.id IS NULL) ASC, 
       events.event_date ASC, 
       users.full_name ASC";

$stmt = mysqli_prepare($conn, $query);
// bind the search term to all 5 placeholders in the sql statement
mysqli_stmt_bind_param($stmt, "sssss", $search_param, $search_param, $search_param, $search_param, $search_param);
mysqli_stmt_execute($stmt);
$events_result = mysqli_stmt_get_result($stmt); // store result

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EventPro - Search </title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php require_once 'includes/navbar.php'; ?>

    <div class="container search-container">
        <h2 class="form-header" style="text-align: left;">Explore All Users And Events</h2>

        <form action="search.php" method="GET" class="search-form">
            <!-- The search term gets attached to the URL-->
            <input type="text" name="search" placeholder="Search by title, category, or location..." value="<?php echo htmlspecialchars($search_term); ?>" class="search-input">
            <button type="submit" class="nav-btn">Search</button>

            <!-- If something has been searched, show clear button -->
            <?php if (!empty($search_term)): ?>
                <!-- Reload page, search query is back to and empty string ''-->
                <a href="search.php" class="nav-btn outline">Clear</a>

            <?php endif; ?>
        </form>

        <div class="search-result">
            <?php if (mysqli_num_rows($events_result) > 0): ?>

                <?php while ($event = mysqli_fetch_assoc($events_result)): ?>

                    <div class="event">

                        <div class="user-section">
                            <?php
                            $host_pic = !empty($event['profile_picture']) ? $event['profile_picture'] : 'images/default-avatar.png';
                            ?>
                            <img src="<?php echo htmlspecialchars($host_pic); ?>" alt="Host" class="host-profile">

                            <div class="user-section-inner">
                                <strong><?php echo htmlspecialchars($event['full_name']); ?></strong>
                                <a href="host_profile.php?id=<?php echo $event['host_id']; ?>" class="dash-btn">View Profile</a>
                            </div>
                        </div>

                        <?php if (!empty($event['title'])): ?>

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
                                <?php echo nl2br(htmlspecialchars($event['description'])); ?> </p>

                        <?php else: ?>
                            <div class="empty-event">
                                <p>This user hasn't hosted any events yet.</p>
                            </div>
                        <?php endif; ?>

                    </div>

                <?php endwhile; ?>

            <?php else: ?>
                <div class="empty-search">
                    <h3 class="empty-search-txt">No events found</h3>
                    <p>Try adjusting your search terms, or check back later for new events!</p>
                </div>
            <?php endif; ?>

        </div>
    </div>
</body>

</html>