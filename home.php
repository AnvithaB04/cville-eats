<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: auth/login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CvilleEats</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container home-hero">
        <h1>CvilleEats</h1>
        <p>Welcome, <?php echo htmlspecialchars($_SESSION["username"]); ?> 👋</p>

        <a class="button" href="explore.php">
            Explore Restaurants →
        </a>

    </div>

</body>
</html>