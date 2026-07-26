<?php
// generate CSRF token if one does not exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// start the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// connect to the database
require_once 'includes/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';

// fetch the existing event
// check if the id was passed in the URL, if not, go back to dashboard
if (!isset($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$event_id = $_GET['id'];

// fetch the event if the user id matches. Only the event host can delete or edit events
$fetch_query = "SELECT * FROM events WHERE id = ? AND user_id = ?";
$fetch_stmt = mysqli_prepare($conn, $fetch_query);
mysqli_stmt_bind_param($fetch_stmt, "ii", $event_id, $user_id);
mysqli_stmt_execute($fetch_stmt);
$result = mysqli_stmt_get_result($fetch_stmt);

// If the event does not exist send user back to dashboard
if (mysqli_num_rows($result) === 0) {
    header("Location: dashboard.php");
    exit();
}

// Store event in array
$event = mysqli_fetch_assoc($result);

// Update the event
if (isset($_POST['update_event_btn'])) {
    // check token first
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Security Check Failed: Invalid CSRF token."); // end script give error
    }
    // Collect form information for event update
    $event_id = $_POST['event_id'];
    $title = trim($_POST['title']);
    $category = $_POST['category'];
    $event_date = $_POST['event_date'];
    $location = trim($_POST['location']);
    $description = trim($_POST['description']);

    // Update query, can only update if the ticket id and the user_id matches
    $update_query = "UPDATE events SET title = ?, category = ?, event_date = ?, location = ?, description = ? WHERE id = ? AND user_id = ? ";
    $update_stmt = mysqli_prepare($conn, $update_query);

    // Bind the 5 strings and two integers 'sssssii'
    mysqli_stmt_bind_param($update_stmt, "sssssii", $title, $category, $event_date, $location, $description, $event_id, $user_id);
    // if sqli execution is successful, send user back to dashboard
    if (mysqli_stmt_execute($update_stmt)) {
        header("Location: dashboard.php");
        exit();
    } else {
        $message = "<div class='error_messages' >Error: Could not update event.</div>";
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EventPro - Create Event</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php require_once 'includes/navbar.php'; ?>

    <div class="container create-edit-event">
        <h2 class="form-header">Edit Event</h2>

        <?php if (!empty($message)) echo $message; ?>
        <!-- Form link with event id -->
        <form action="edit_event.php?id=<?php echo $event['id']; ?>" method="POST">
            <!-- Hidden CSRF TOKEN-->
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <!-- Hidden id-->
            <input type="hidden" name="event_id" value="<?php echo $event['id']; ?>">
            <div class="form-group">
                <label for="title">Event Title <span class="required">*</span></label>
                <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($event['title']); ?>" required>
            </div>

            <div class="form-group">
                <label for="category">Category <span class="required">*</span></label>
                <select id="category" name="category" required class="select-btn">
                    <option value="" disabled selected>Select a category...</option>
                    <option value="Music & Concerts" <?php if ($event['category'] === 'Music & Concerts') echo 'selected'; ?>>Music & Concerts</option>
                    <option value="Tech & Coding" <?php if ($event['category'] === 'Tech & Coding') echo 'selected'; ?>>Tech & Coding</option>
                    <option value="Education" <?php if ($event['category'] === 'Education') echo 'selected'; ?>>Education</option>
                    <option value="Sports & Fitness" <?php if ($event['category'] === 'Sports & Fitness') echo 'selected'; ?>>Sports & Fitness</option>
                    <option value="Business & Networking" <?php if ($event['category'] === 'Business & Networking') echo 'selected'; ?>>Business & Networking</option>
                    <option value="Food & Drink" <?php if ($event['category'] === 'Food & Drink') echo 'selected'; ?>>Food & Drink</option>
                    <option value="Other" <?php if ($event['category'] === 'Other') echo 'selected'; ?>>Other</option>
                </select>
            </div>

            <div class="form-group">
                <label for="event_date">Date <span class="required">*</span></label>
                <input type="date" id="event_date" name="event_date" value="<?php echo htmlspecialchars($event['event_date']); ?>" required>
            </div>

            <div class="form-group">
                <label for="location">Location <span class="required">*</span></label>
                <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($event['location']); ?>" required>
            </div>



            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="4" class="text-area"><?php echo htmlspecialchars($event['description']); ?></textarea>
            </div>



            <button class="form-btn" type="submit" name="update_event_btn">Save Edit</button>
            <a href="dashboard.php" class="view-dash-btn">Back to Dashboard</a>
        </form>
    </div>
</body>

</html>