<?php

// generate CSRF token if one does not exist
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// connect to the database
require_once 'includes/db.php';

// start the session if it has not been started yet
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// kick out user if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit(); // stop the script
}

$sender_id = $_SESSION['user_id'];

// get the id of the host profile from the URL
$receiver_id = $_GET['user_id'] ?? null;
$receiver = null;
$messages_result = null;

// If there is a receiver_id
if ($receiver_id) {
    $receiver_query = "SELECT full_name, profile_picture FROM users WHERE id = ?";
    $stmt_receiver = mysqli_prepare($conn, $receiver_query);
    mysqli_stmt_bind_param($stmt_receiver, "i", $receiver_id); // fill in the place holder with receiver_id
    mysqli_stmt_execute($stmt_receiver); // execute the sql
    // store the results variable
    $receiver = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_receiver));
    // safety check if user typed random id in the URL that does not exist, send them back to search
    if (!$receiver) {
        header("Location: search.php");
        exit();
    }
}
// if there is no receiver
else {
    // query all the messages that involves the logged in user
    // The users and the messages tables are joined together. The combined table is filtered for messages
    // where the logged in user is the sender or the receiver. The logged in user's id is filtered out
    // to ensure that they do not show up in their own inbox and then MAX(created_at) is used 
    // to find the last timestamp in the conversation. This is used to arrange the messages so that the
    // newest conversations appear first.
    $inbox_query = "SELECT users.id AS contact_id, users.full_name, users.profile_picture, MAX(messages.created_at) AS last_interaction
    FROM users
    JOIN messages ON (users.id = messages.sender_id OR users.id = messages.receiver_id)
    WHERE (messages.sender_id = ? OR messages.receiver_id = ?)
    AND users.id != ?
    GROUP BY users.id, users.full_name, users.profile_picture
    ORDER BY last_interaction DESC";

    $stmt_inbox = mysqli_prepare($conn, $inbox_query);
    // bind logged in user id to the 3 placeholders
    mysqli_stmt_bind_param($stmt_inbox, "iii", $sender_id, $sender_id, $sender_id);
    mysqli_stmt_execute($stmt_inbox); // execute the sql command
    $inbox_result = mysqli_stmt_get_result($stmt_inbox);
}


// Insert the message from the form into the database
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message_text']) && $receiver_id) {
    // check token first
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die("Security Check Failed: Invalid CSRF token."); // end script give error
    }

    // get the message from the post request
    $message_text = trim($_POST['message_text']);

    // if the form is not empty
    if (!empty($message_text)) {



        $insert_query = "INSERT INTO messages (sender_id, receiver_id, message) VALUES (?, ?, ?)";
        $stmt_insert = mysqli_prepare($conn, $insert_query);
        // fill in the place holders with variables two integers (ii) and one string (s)
        mysqli_stmt_bind_param($stmt_insert, "iis", $sender_id, $receiver_id, $message_text);
        mysqli_stmt_execute($stmt_insert); // run the sql query

        // Refresh the page to clear the POST data and the show the new message
        header("Location: messages.php?user_id=" . $receiver_id);
        exit();
    }
}

// Fetch the chat history
if ($receiver_id) {
    $chat_query = "SELECT * FROM messages WHERE (sender_id = ? AND receiver_id = ?)
    OR (sender_id = ? AND receiver_id = ?)
    ORDER BY created_at ASC";

    $stmt_chat = mysqli_prepare($conn, $chat_query);

    // bind four integers (iiii)
    mysqli_stmt_bind_param($stmt_chat, "iiii", $sender_id, $receiver_id, $receiver_id, $sender_id);
    mysqli_stmt_execute($stmt_chat); // execute the sql command
    $messages_result = mysqli_stmt_get_result($stmt_chat); // store result
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages</title>
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <?php require_once 'includes/navbar.php'; ?>
    <div class="container messages-container">
        <?php if (!$receiver_id): ?>

            <div>
                <h2 style="color: white; margin-top: 0; margin-bottom: 25px; border-bottom: 2px solid #333; padding-bottom: 10px;">Recent Conversations</h2>
                <!-- If there are previously sent messages  -->
                <?php if (mysqli_num_rows($inbox_result) > 0): ?>
                    <div class="inbox-container">

                        <?php while ($contact = mysqli_fetch_assoc($inbox_result)): ?>
                            <!-- when user clicks on this card, the url will update with the receiver's id, allowing them to message the user-->
                            <a href="messages.php?user_id=<?php echo $contact['contact_id']; ?>" class="inbox-contact">
                                <!-- Default profile picture path if the user does not have a profile picture-->
                                <?php $pic = !empty($contact['profile_picture']) ? $contact['profile_picture'] : 'images/default-avatar.png'; ?>
                                <img src="<?php echo htmlspecialchars($pic); ?>" alt="Profile" class="inbox-profile-pic">

                                <div style="flex-grow: 1;">
                                    <strong style="color: white; font-size: 18px; display: block; margin-bottom: 4px;">
                                        <?php echo htmlspecialchars($contact['full_name']); ?>
                                    </strong>
                                    <span style="color: #aaa; font-size: 13px;">
                                        Last active: <?php echo date("F j, Y, g:i a", strtotime($contact['last_interaction'])); ?>
                                    </span>
                                </div>

                                <span class="dash-btn outline" style="font-size: 12px; padding: 6px 12px;">Open Chat</span>
                            </a>
                        <?php endwhile; ?>

                    </div>

                <?php else: ?>
                    <div class="empty-search" style="text-align: center; padding: 40px;">
                        <h3 class="mb-15" style="color: white;">Your inbox is empty.</h3>
                        <p class="mb-15" style="color: #aaa;">Search for a user on the Search page and click "View Profile" and then "Message" to start a conversation.</p>
                        <a href="search.php" class="dash-btn">Go to Search</a>
                    </div>
                <?php endif; ?>

            </div>
        <?php else: ?>
            <div class="chat-window">
                <div class="chat-header">
                    <?php $pic = !empty($receiver['profile_picture']) ? $receiver['profile_picture'] : 'images/default-avatar.png'; ?>
                    <img src="<?php echo htmlspecialchars($pic); ?>" alt="Profile" style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;">
                    <strong style="color: white; font-size: 18px;"><?php echo htmlspecialchars($receiver['full_name']); ?></strong>
                </div>
                <div class="chat-history">
                    <?php if (mysqli_num_rows($messages_result) > 0): ?>
                        <?php while ($msg = mysqli_fetch_assoc($messages_result)): ?>
                            <?php
                            // check who sent the message
                            $is_me = ($msg['sender_id'] == $sender_id); // True or False
                            ?>
                            <div class="message-bubble" style="<?php echo $is_me ? 'background-color: #00FFFF; color: black; align-self: flex-end;' : 'background-color: #333; color: white; align-self: flex-start;'; ?>">
                                <p style="margin: 0; font-size: 15px;">
                                    <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                                </p>
                                <!-- Time message was sent/created-->
                                <span class="created-at">
                                    <?php echo date("g:i A", strtotime($msg['created_at'])); ?>
                                </span>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="no-msg-txt">No messages yet. Send a message to start the conversation!</p>
                    <?php endif; ?>
                </div>
                <div class="chat-input">
                    <form class="chat-input-form" action="messages.php?user_id=<?php echo htmlspecialchars($receiver_id); ?>" method="POST">
                        <!-- Hidden CSRF TOKEN-->
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
                        <input type="text" name="message_text" placeholder="Type a message..." required class="msg-input">
                        <div> <button type="submit" class="dash-btn send-btn">Send</button>
                            <a href="messages.php" class="dash-btn outline">
                                Back to Inbox
                            </a>
                        </div>


                    </form>
                </div>

            </div>
        <?php endif; ?>
    </div>
</body>

</html>