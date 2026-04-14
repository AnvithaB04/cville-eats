<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

require_once "connect-db.php";

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid restaurant ID.");
}

$restaurantId = (int) $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['delete_review'])) {
        $stmt = $pdo->prepare(
            "DELETE FROM Review WHERE User_ID = :userId AND Restaurant_ID = :restaurantId"
        );
        $stmt->execute([
            'userId' => $_SESSION['user_id'],
            'restaurantId' => $restaurantId,
        ]);

        header("Location: restaurant.php?id=$restaurantId");
        exit;
    }

    if (isset($_POST['rating'])) {
        $rating = max(1, min(5, (int) $_POST['rating']));
        $comment = trim($_POST['review'] ?? '');

        $stmt = $pdo->prepare("SELECT Review_ID FROM Review WHERE User_ID = :userId AND Restaurant_ID = :restaurantId");
        $stmt->execute([
            'userId' => $_SESSION['user_id'],
            'restaurantId' => $restaurantId,
        ]);
        $existingReview = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($existingReview) {
            $stmt = $pdo->prepare(
                "UPDATE Review SET Rating = :rating, Comment = :comment, Review_Date = CURRENT_TIMESTAMP
                 WHERE Review_ID = :reviewId"
            );
            $stmt->execute([
                'rating' => $rating,
                'comment' => $comment,
                'reviewId' => $existingReview['Review_ID'],
            ]);
        } else {
            $stmt = $pdo->prepare(
                "INSERT INTO Review (User_ID, Restaurant_ID, Rating, Comment)
                 VALUES (:userId, :restaurantId, :rating, :comment)"
            );
            $stmt->execute([
                'userId' => $_SESSION['user_id'],
                'restaurantId' => $restaurantId,
                'rating' => $rating,
                'comment' => $comment,
            ]);
        }

        header("Location: restaurant.php?id=$restaurantId");
        exit;
    }
}

$stmt = $pdo->prepare(
    "SELECT r.Restaurant_Name, r.Price_Level, r.Street, l.City, l.State,
            r.Vegetarian_Options, r.Vegan_Options, r.GlutenFree_Options
     FROM Restaurant r
     JOIN Location l ON r.Zip_Code = l.Zip_Code
     WHERE r.Restaurant_ID = :id"
);
$stmt->execute(['id' => $restaurantId]);
$restaurant = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$restaurant) {
    die("Restaurant not found.");
}

$stmt = $pdo->prepare("SELECT Review_ID, Rating, Comment FROM Review WHERE User_ID = :userId AND Restaurant_ID = :restaurantId");
$stmt->execute([
    'userId' => $_SESSION['user_id'],
    'restaurantId' => $restaurantId,
]);
$currentReview = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review <?php echo htmlspecialchars($restaurant['Restaurant_Name']); ?> - CvilleEats</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php
    $headerSubtitle = 'Write a review';
    include 'header.php';
    ?>

    <div class="page-container">
        <div class="section-card">
            <h2>Review <?php echo htmlspecialchars($restaurant['Restaurant_Name']); ?></h2>
            <p class="restaurant-address">
                <?php echo htmlspecialchars($restaurant['Street']); ?><br>
                <?php echo htmlspecialchars($restaurant['City']); ?>, <?php echo htmlspecialchars($restaurant['State']); ?>
            </p>
            <p><strong>Price:</strong> <?php echo htmlspecialchars($restaurant['Price_Level']); ?></p>
            <div class="tag-row">
                <?php if ($restaurant['Vegetarian_Options']): ?><span class="tag">Vegetarian</span><?php endif; ?>
                <?php if ($restaurant['Vegan_Options']): ?><span class="tag">Vegan</span><?php endif; ?>
                <?php if ($restaurant['GlutenFree_Options']): ?><span class="tag">Gluten-Free</span><?php endif; ?>
            </div>
        </div>

        <div class="section-card review-form">
            <h3 class="section-title"><?php echo $currentReview ? 'Edit your review' : 'Add a review'; ?></h3>
            <form method="POST">
                <div class="rating-stars">
                    <?php for ($star = 5; $star >= 1; $star--): ?>
                        <input type="radio" name="rating" id="star-<?php echo $star; ?>" value="<?php echo $star; ?>" <?php echo ($currentReview && intval($currentReview['Rating']) === $star) ? 'checked' : ''; ?> required>
                        <label for="star-<?php echo $star; ?>">★</label>
                    <?php endfor; ?>
                </div>
                <textarea name="review" placeholder="Leave a short review (optional)"><?php echo htmlspecialchars($currentReview['Comment'] ?? ''); ?></textarea>
                <button class="button" type="submit"><?php echo $currentReview ? 'Update review' : 'Post review'; ?></button>
                <?php if ($currentReview): ?>
                    <button class="button" type="submit" name="delete_review" value="1" style="margin-left: 12px;">Delete review</button>
                <?php endif; ?>
                <a class="button" href="restaurant.php?id=<?php echo $restaurantId; ?>" style="margin-left: 12px;">Cancel</a>
            </form>
        </div>
    </div>
</body>
</html>
