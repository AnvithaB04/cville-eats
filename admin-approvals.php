<?php
session_start();
require_once "includes/connect-db.php";

// (admin check)
if (empty($_SESSION["is_admin"])) {
    die("Access denied.");
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}


// get pending restaurants
$stmt = $pdo->query("SELECT * FROM Pending_Restaurant");
$pending = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Approvals</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<?php
$headerSubtitle = 'Admin approval queue';
include 'includes/header.php';
?>

<div class="page-container">
<div class="section-card">

<h2>Pending Restaurants</h2>

<?php if (empty($pending)): ?>
    <p>No pending restaurants.</p>
<?php else: ?>
    <?php foreach ($pending as $p): ?>
    <div style="border:1px solid #ccc; margin:10px; padding:10px;">
        <h3><?php echo htmlspecialchars($p['Name']); ?></h3>
        <p><?php echo htmlspecialchars($p['Street']); ?>, <?php echo htmlspecialchars($p['City']); ?></p>

        <form method="POST" action="approve.php">
            <input type="hidden" name="id" value="<?php echo $p['Pending_ID']; ?>">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
            <button type="submit">Approve</button>
        </form>
    </div>
<?php endforeach; ?>
<?php endif; ?>

</div>
</div>
</body>
</html>