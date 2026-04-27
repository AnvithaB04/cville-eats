<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: auth/login.php");
    exit;
}

require_once "includes/connect-db.php";

$userId = $_SESSION["user_id"];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_review'])) {
    $reviewId = (int) $_POST['delete_review'];
    $stmt = $pdo->prepare("DELETE FROM Review WHERE Review_ID = :reviewId AND User_ID = :userId");
    $stmt->execute(['reviewId' => $reviewId, 'userId' => $userId]);
    header("Location: profile.php");
    exit;
}

$sql = "
    SELECT rv.Review_ID, rv.Rating, rv.Comment AS Review, rv.Review_Date AS Created_At, r.Restaurant_ID, r.Restaurant_Name
    FROM Review rv
    JOIN Restaurant r ON rv.Restaurant_ID = r.Restaurant_ID
    WHERE rv.User_ID = :userId
    ORDER BY rv.Review_Date DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute(['userId' => $userId]);
$ratings = $stmt->fetchAll(PDO::FETCH_ASSOC);

$sql = "
    SELECT COUNT(*) AS total, COALESCE(AVG(Rating), 0) AS avg_rating
    FROM Review
    WHERE User_ID = :userId
";
$stmt = $pdo->prepare($sql);
$stmt->execute(['userId' => $userId]);
$summary = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Ratings - CvilleEats</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php
    $headerSubtitle = 'My Ratings and Profile';
    include 'includes/header.php';
    ?>

    <div class="page-container">
        <a href="explore.php" class="button button-secondary" style="margin-bottom:15px; display:inline-block;">
        ← Back
    </a>
        <div class="detail-card profile-summary">
            <h2>Hi, <?php echo htmlspecialchars($_SESSION["username"]); ?></h2>
            <p>You have rated <strong><?php echo htmlspecialchars($summary['total']); ?></strong> restaurant<?php echo $summary['total'] == 1 ? '' : 's'; ?>.</p>
            <p>Average rating: <strong><?php echo number_format($summary['avg_rating'], 1); ?></strong></p>
        </div>

        <div id="ratings" class="section-card">
            <h3 class="section-title">My Ratings</h3>

            <?php if (empty($ratings)): ?>
                <p class="no-results">You haven't rated any restaurants yet. Visit Explore to add your first rating.</p>
            <?php else: ?>
                <?php foreach ($ratings as $rating): ?>
                    <div class="review-card">
                        <p><a href="restaurant.php?id=<?php echo $rating['Restaurant_ID']; ?>"><?php echo htmlspecialchars($rating['Restaurant_Name']); ?></a></p>
                        <p>Rating: <?php echo intval($rating['Rating']); ?> / 5</p>
                        <?php if (trim($rating['Review']) !== ''): ?>
                            <p><?php echo nl2br(htmlspecialchars($rating['Review'])); ?></p>
                        <?php endif; ?>
                        <div style="display:flex;justify-content:space-between;align-items:center;gap:12px;flex-wrap:wrap;">
                            <p style="font-size:0.9rem;color:#555;margin:0;">Rated on <?php echo date('M j, Y', strtotime($rating['Created_At'])); ?></p>
                            <div style="display:flex;gap:8px;">
                                <a href="review.php?id=<?php echo intval($rating['Restaurant_ID']); ?>" class="button button-secondary button-small">Update review</a>
                                <form method="POST" class="delete-review-form" style="margin:0;">
                                    <button class="button button-secondary" type="submit" name="delete_review" value="<?php echo intval($rating['Review_ID'] ?? 0); ?>">Delete review</button>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

</body>
</html>
