<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: auth/login.php");
    exit;
}

require_once "includes/connect-db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_favorite'])) {
    $restaurantId = (int) $_POST['toggle_favorite'];

    $stmt = $pdo->prepare("DELETE FROM Favorite WHERE User_ID = :userId AND Restaurant_ID = :restaurantId");
    $stmt->execute(['userId' => $_SESSION['user_id'], 'restaurantId' => $restaurantId]);

    header("Location: saved-restaurants.php");
    exit;
}

$sql = "
    SELECT r.Restaurant_ID, r.Restaurant_Name, r.Price_Level, r.Street, l.City, l.State,
           r.Vegetarian_Options, r.Vegan_Options, r.GlutenFree_Options,
           COALESCE(AVG(rv.Rating), 0) AS Avg_Rating,
           COUNT(rv.Review_ID) AS Rating_Count
    FROM Favorite f
    JOIN Restaurant r ON f.Restaurant_ID = r.Restaurant_ID
    JOIN Location l ON r.Zip_Code = l.Zip_Code
    LEFT JOIN Review rv ON rv.Restaurant_ID = r.Restaurant_ID
    WHERE f.User_ID = :userId
    GROUP BY r.Restaurant_ID
    ORDER BY r.Restaurant_Name ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute(['userId' => $_SESSION['user_id']]);
$savedRestaurants = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Saved Restaurants - CvilleEats</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php
    $headerSubtitle = 'Saved Restaurants';
    include 'includes/header.php';
    ?>

    <div class="page-container">
        <a href="explore.php" class="button button-secondary" style="margin-bottom:15px; display:inline-block;">
        ← Back
    </a>
        <h2>Saved Restaurants</h2>

        <?php if (empty($savedRestaurants)): ?>
            <p class="no-results">You have no saved restaurants yet. Add favorites from Explore.</p>
        <?php else: ?>
            <div class="restaurant-grid saved-restaurants-grid">
                <?php foreach ($savedRestaurants as $restaurant): ?>
                    <div class="restaurant-card" data-name="<?php echo htmlspecialchars(strtolower($restaurant['Restaurant_Name'])); ?>">
                        <form method="POST" class="favorite-form">
                            <button type="submit" name="toggle_favorite" value="<?php echo intval($restaurant['Restaurant_ID']); ?>" class="favorite-toggle favorited" aria-label="Remove from saved restaurants">
                                ♥
                            </button>
                        </form>
                        <h3>
                            <a href="restaurant.php?id=<?php echo $restaurant['Restaurant_ID']; ?>">
                                <?php echo htmlspecialchars($restaurant['Restaurant_Name']); ?>
                            </a>
                        </h3>
                        <p>
                            <?php echo htmlspecialchars($restaurant['Street']); ?>,
                            <?php echo htmlspecialchars($restaurant['City']); ?>,
                            <?php echo htmlspecialchars($restaurant['State']); ?>
                        </p>
                        <p class="price">Price: <?php echo htmlspecialchars($restaurant['Price_Level']); ?></p>
                        <p class="rating-line">
                            <?php if ($restaurant['Rating_Count'] > 0): ?>
                                ⭐ <?php echo number_format($restaurant['Avg_Rating'], 1); ?> (<?php echo $restaurant['Rating_Count']; ?> ratings)
                            <?php else: ?>
                                No ratings yet
                            <?php endif; ?>
                        </p>
                        <div class="tag-row">
                            <?php if ($restaurant['Vegetarian_Options']): ?>
                                <span class="tag">Vegetarian</span>
                            <?php endif; ?>
                            <?php if ($restaurant['Vegan_Options']): ?>
                                <span class="tag">Vegan</span>
                            <?php endif; ?>
                            <?php if ($restaurant['GlutenFree_Options']): ?>
                                <span class="tag">Gluten-Free</span>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
