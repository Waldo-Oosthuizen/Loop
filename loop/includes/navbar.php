<?php
require_once 'includes/db.php';
// Make sure the session is started at the very top if it isn't already handled by the page
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Initialize variables as empty defaults so they exist even if logged out
$nav_user_name = '';
$nav_profile_pic = 'images/default-avatar.png';
// Default image from FF&3, T. (2025)

if (isset($_SESSION['user_id'])) {
    // Fetch the user details
    $user_id = $_SESSION['user_id'];
    $query = "SELECT full_name, profile_picture FROM users WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);


    if ($user = mysqli_fetch_assoc($result)) {
        // Store the username
        $nav_user_name = $user['full_name'];

        // If they uploaded a picture, override the default avatar
        if (!empty($user['profile_picture'])) {
            $nav_profile_pic = $user['profile_picture'];
        }
    }
}

?>



<nav class="top-nav">
    <div class="nav-brand">
        <a href="index.php">Loop</a>
    </div>

    <!-- The hamburger button, only visible on mobile phones-->
    <button class="hamburger" id="hamburger-btn">
        <span></span>
        <span></span>
        <span></span>
    </button>

    <div class="nav-links" id="nav-links">
        <?php if (isset($_SESSION['user_id'])): ?>

            <div class="left-nav">
                <a href="dashboard.php" class="nav-btn">DASHBOARD</a>
                <a href="search.php" class="nav-btn">Search</a>
                <a href="messages.php" class="nav-btn">Message</a>
                <a href="create_event.php" class="nav-btn">Create Event</a>
            </div>

            <div class="right-nav">
                <a href="profile.php" class="user-profile">
                    <img src="<?php echo htmlspecialchars($nav_profile_pic); ?>" alt="Profile" class="nav-profile-pic">
                    <p><?php echo htmlspecialchars($nav_user_name); ?></p>
                </a>
                <a href="logout.php" class="logout-btn">

                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" width="20" height="20">
                        <path fill-rule="evenodd" d="M3 4.25A2.25 2.25 0 0 1 5.25 2h5.5A2.25 2.25 0 0 1 13 4.25v2a.75.75 0 0 1-1.5 0v-2a.75.75 0 0 0-.75-.75h-5.5a.75.75 0 0 0-.75.75v11.5c0 .414.336.75.75.75h5.5a.75.75 0 0 0 .75-.75v-2a.75.75 0 0 1 1.5 0v2A2.25 2.25 0 0 1 10.75 18h-5.5A2.25 2.25 0 0 1 3 15.75V4.25Z" clip-rule="evenodd" />
                        <path fill-rule="evenodd" d="M19 10a.75.75 0 0 0-.75-.75H8.704l1.048-.943a.75.75 0 1 0-1.004-1.114l-2.5 2.25a.75.75 0 0 0 0 1.114l2.5 2.25a.75.75 0 1 0 1.004-1.114l-1.048-.943h9.546A.75.75 0 0 0 19 10Z" clip-rule="evenodd" />
                    </svg>
                    LOGOUT</a>
            </div>

        <?php else: ?>
            <div class="right-nav">
                <a href="index.php" class="nav-btn outline">LOGIN</a>
                <a href="register.php" class="nav-btn">REGISTER</a>
            </div>

        <?php endif; ?>
    </div>
</nav>

<script>
    document.getElementById('hamburger-btn').addEventListener('click', function() {
        // Toggle the class mobile-active to hide and show the link lists when clicking
        document.getElementById('nav-links').classList.toggle('mobile-active');
    });
</script>