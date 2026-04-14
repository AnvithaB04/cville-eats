<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

require_once "connect-db.php";

$userId = $_SESSION["user_id"];
$reviewId = (int) ($_GET['id'] ?? 0);

if ($reviewId <= 0) {
    header("Location: profile.php");
    exit;
}

// Fetch the review and restaurant details
$stmt = $pdo->prepare("
    SELECT rv.Review_ID, rv.Rating, rv.Comment, r.Restaurant_ID, r.Restaurant_Name, r.Price_Level, r.Street, l.City, l.State,
           r.Vegetarian_Options, r.Vegan_Options, r.GlutenFree_Options
    FROM Review rv
    JOIN Restaurant r ON rv.Restaurant_ID = r.Restaurant_ID
    JOIN Location l ON r.Zip_Code = l.Zip_Code
    WHERE rv.Review_ID = :reviewId AND rv.User_ID = :userId
");
$stmt->execute(['reviewId' => $reviewId, 'userId' => $userId]);
$review = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$review) {
    header("Location: profile.php");
    exit;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_review'])) {
        $stmt = $pdo->prepare("DELETE FROM Review WHERE Review_ID = :reviewId AND User_ID = :userId");
        $stmt->execute(['reviewId' => $reviewId, 'userId' => $userId]);
        header("Location: profile.php");
        exit;
    }

    $rating = (int) ($_POST['rating'] ?? 0);
    $comment = trim($_POST['review'] ?? '');

    if ($rating < 1 || $rating > 5) {
        $errors[] = 'Rating must be between 1 and 5.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("
            UPDATE Review
            SET Rating = :rating, Comment = :comment, Review_Date = CURRENT_TIMESTAMP
            WHERE Review_ID = :reviewId AND User_ID = :userId
        ");
        $stmt->execute([
            'rating' => $rating,
            'comment' => $comment,
            'reviewId' => $reviewId,
            'userId' => $userId
        ]);
        header("Location: profile.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Review - CvilleEats</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php
    $headerSubtitle = 'Edit your review';
    include 'header.php';
    ?>

    <div class="page-container">
        <div class="section-card">
            <h2>Edit Review for <?php echo htmlspecialchars($review['Restaurant_Name']); ?></h2>
            <p class="restaurant-address">
                <?php echo htmlspecialchars($review['Street']); ?><br>
                <?php echo htmlspecialchars($review['City']); ?>, <?php echo htmlspecialchars($review['State']); ?>
            </p>
            <p><strong>Price:</strong> <?php echo htmlspecialchars($review['Price_Level']); ?></p>
            <div class="tag-row">
                <?php if ($review['Vegetarian_Options']): ?><span class="tag">Vegetarian</span><?php endif; ?>
                <?php if ($review['Vegan_Options']): ?><span class="tag">Vegan</span><?php endif; ?>
                <?php if ($review['GlutenFree_Options']): ?><span class="tag">Gluten-Free</span><?php endif; ?>
            </div>
        </div>

        <div class="section-card review-form">
            <h3 class="section-title">Edit your review</h3>

            <?php if (!empty($errors)): ?>
                <div class="error-messages">
                    <?php foreach ($errors as $error): ?>
                        <p><?php echo htmlspecialchars($error); ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="rating-stars">
                    <?php for ($star = 5; $star >= 1; $star--): ?>
                        <input type="radio" name="rating" id="star-<?php echo $star; ?>" value="<?php echo $star; ?>" <?php echo (intval($review['Rating']) === $star) ? 'checked' : ''; ?> required>
                        <label for="star-<?php echo $star; ?>">★</label>
                    <?php endfor; ?>
                </div>
                <textarea name="review" placeholder="Leave a short review (optional)"><?php echo htmlspecialchars($review['Comment']); ?></textarea>
                <button class="button" type="submit">Update review</button>
                <button class="button" type="submit" name="delete_review" value="1" style="margin-left: 12px;">Delete review</button>
                <a class="button" href="profile.php" style="margin-left: 12px;">Cancel</a>
            </form>
        </div>
    </div>

</body>
</html>