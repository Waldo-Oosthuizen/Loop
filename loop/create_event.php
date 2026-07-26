<?php
// generate CSRF token if one does not exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// start the session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once 'includes/db.php';

// if user is not logged in, kick them out
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$message = '';

if (isset($_POST['create_event_btn'])) {
    // check token first
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Security Check Failed: Invalid CSRF token."); // end script give error
    }

    // get the user's id from the session storage
    $user_id = $_SESSION['user_id'];

    // collect the form information and clean the data
    $title = trim($_POST['title']);
    $category = $_POST['category'];
    $event_date = $_POST['event_date'];
    $location = $_POST['location'];
    $description = trim($_POST['description']);

    // Insert the data into the database
    $query = "INSERT INTO events (user_id, title, category, event_date, location, description) VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $query);
    // bind the integer and four strings (issss)
    mysqli_stmt_bind_param($stmt, "isssss", $user_id, $title, $category,  $event_date, $location, $description);

    // If sql gets execute without any error
    if (mysqli_stmt_execute($stmt)) {
        $message = "<div class='success-messages'>
                        SUCCESS: Event created! <a href='dashboard.php' >View Dashboard</a>
                    </div>";
    }
    // if there was a problem with ticket creation
    else {
        $message = "<div class='error_messages'>
                        Error: Could not save the event.
                    </div>";
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
        <h2 class="form-header">Create a New Event</h2>

        <?php if (!empty($message)) echo $message; ?>

        <form action="create_event.php" method="POST">
            <!-- Hidden CSRF TOKEN-->
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <div class="form-group">
                <label for="title">Event Title <span class="required">*</span></label>
                <input type="text" id="title" name="title" required>
            </div>

            <div class="form-group">
                <label for="category">Category <span class="required">*</span></label>
                <select id="category" name="category" required class="select-btn">
                    <option value="" disabled selected>Select a category...</option>
                    <option value="Music & Concerts">Music & Concerts</option>
                    <option value="Tech & Coding">Tech & Coding</option>
                    <option value="Education">Education</option>
                    <option value="Sports & Fitness">Sports & Fitness</option>
                    <option value="Business & Networking">Business & Networking</option>
                    <option value="Food & Drink">Food & Drink</option>
                    <option value="Other">Other</option>
                </select>
            </div>

            <div class="form-group">
                <label for="event_date">Date <span class="required">*</span></label>
                <input type="date" id="event_date" name="event_date" required>
            </div>

            <div class="form-group">
                <label for="location">Location <span class="required">*</span></label>
                <input type="text" id="location" name="location" required>
            </div>



            <div class="form-group">
                <label for="description">Description</label>
                <textarea id="description" name="description" rows="4" class="text-area"></textarea>
            </div>



            <button class="form-btn" type="submit" name="create_event_btn">Save Event</button>
            <a href="dashboard.php" class="view-dash-btn">Back to Dashboard</a>
        </form>
    </div>
</body>

</html>