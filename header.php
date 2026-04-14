<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

if (!isset($headerSubtitle)) {
    $headerSubtitle = "Charlottesville Restaurant Finder & Reviews";
}
?>

<header class="site-header">
    <a class="site-brand" href="explore.php">
        <h1>CvilleEats</h1>
        <p><?php echo htmlspecialchars($headerSubtitle); ?></p>
    </a>
    <div class="profile-actions">
        <button class="profile-button">Account</button>
        <div class="dropdown-menu hidden">
            <a href="profile.php">My Ratings</a>
            <a href="saved-restaurants.php">Saved Restaurants</a>
            <a href="logout.php">Logout</a>
        </div>
    </div>
</header>
