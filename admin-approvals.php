<?php
session_start();
require_once "includes/connect-db.php";

// (admin check)
if (empty($_SESSION["is_admin"])) {
    die("Access denied.");
}


// get pending restaurants
$stmt = $pdo->query("SELECT * FROM Pending_Restaurant");
$pending = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Approvals</title>
</head>
<body>

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
            <button type="submit">Approve</button>
        </form>
    </div>
<?php endforeach; ?>
<?php endif; ?>

</body>
</html>