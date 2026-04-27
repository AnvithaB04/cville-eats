<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: auth/login.php");
    exit;
}

require_once "includes/connect-db.php";

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid restaurant ID.");
}

$restaurantId = (int) $_GET['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_like'])) {
    $reviewId = (int) $_POST['toggle_like'];

    $stmt = $pdo->prepare("SELECT 1 FROM Review_Like WHERE User_ID = :userId AND Review_ID = :reviewId");
    $stmt->execute(['userId' => $_SESSION['user_id'], 'reviewId' => $reviewId]);
    $liked = $stmt->fetchColumn();

    if ($liked) {
        $stmt = $pdo->prepare("DELETE FROM Review_Like WHERE User_ID = :userId AND Review_ID = :reviewId");
        $stmt->execute(['userId' => $_SESSION['user_id'], 'reviewId' => $reviewId]);
    } else {
        $stmt = $pdo->prepare("INSERT IGNORE INTO Review_Like (User_ID, Review_ID) VALUES (:userId, :reviewId)");
        $stmt->execute(['userId' => $_SESSION['user_id'], 'reviewId' => $reviewId]);
    }

    header("Location: restaurant.php?id=$restaurantId");
    exit;
}

$sql = "
    SELECT r.Restaurant_Name, r.Price_Level, r.Street, l.City, l.State,
           r.Vegetarian_Options, r.Vegan_Options, r.GlutenFree_Options,
           GROUP_CONCAT(DISTINCT Restaurant_Phone.Phone_Number SEPARATOR ', ') AS Phones,
           GROUP_CONCAT(DISTINCT Cuisine.Cuisine_Name SEPARATOR ', ') AS Cuisines,
           COALESCE(rv.Avg_Rating, 0) AS Avg_Rating,
           COALESCE(rv.Total_Rating_Count, 0) AS Total_Rating_Count,
           COALESCE(rv.Rating_Count, 0) AS Rating_Count
    FROM Restaurant r
    JOIN Location l ON r.Zip_Code = l.Zip_Code
    LEFT JOIN Restaurant_Phone ON Restaurant_Phone.Restaurant_ID = r.Restaurant_ID
    LEFT JOIN Has_Cuisine hc ON hc.Restaurant_ID = r.Restaurant_ID
    LEFT JOIN Cuisine ON Cuisine.Cuisine_ID = hc.Cuisine_ID
    LEFT JOIN (
        SELECT Restaurant_ID,
               AVG(Rating) AS Avg_Rating,
               COUNT(*) AS Total_Rating_Count,
               COUNT(CASE WHEN TRIM(COALESCE(Comment, '')) != '' THEN 1 END) AS Rating_Count
        FROM Review
        GROUP BY Restaurant_ID
    ) rv ON rv.Restaurant_ID = r.Restaurant_ID
    WHERE r.Restaurant_ID = :id
    GROUP BY r.Restaurant_ID, r.Restaurant_Name, r.Price_Level, r.Street, l.City, l.State,
             r.Vegetarian_Options, r.Vegan_Options, r.GlutenFree_Options
";

$stmt = $pdo->prepare($sql);
$stmt->execute(['id' => $restaurantId]);
$restaurant = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$restaurant) {
    die("Restaurant not found.");
}

$stmt = $pdo->prepare("SELECT Review_ID, Rating FROM Review WHERE User_ID = :userId AND Restaurant_ID = :restaurantId");
$stmt->execute(['userId' => $_SESSION['user_id'], 'restaurantId' => $restaurantId]);
$currentReview = $stmt->fetch(PDO::FETCH_ASSOC);

$hoursStmt = $pdo->prepare(
    "SELECT Day_Of_Week, Open_Time, Close_Time
     FROM Opening_Hours
     WHERE Restaurant_ID = :id
     ORDER BY FIELD(Day_Of_Week, 'Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday')"
);
$hoursStmt->execute(['id' => $restaurantId]);
$openingHours = $hoursStmt->fetchAll(PDO::FETCH_ASSOC);

$reviewsStmt = $pdo->prepare(
    "SELECT rv.Review_ID, rv.Rating, rv.Comment, rv.Review_Date, u.Username,
            COUNT(rl.User_ID) AS Like_Count,
            MAX(rl.User_ID = :currentUser) AS Liked_By_Current_User
     FROM Review rv
     JOIN `User` u ON rv.User_ID = u.User_ID
     LEFT JOIN Review_Like rl ON rl.Review_ID = rv.Review_ID
     WHERE rv.Restaurant_ID = :restaurantId
       AND TRIM(COALESCE(rv.Comment, '')) != ''
     GROUP BY rv.Review_ID, u.Username, rv.Rating, rv.Comment, rv.Review_Date
     ORDER BY rv.Review_Date DESC"
);
$reviewsStmt->execute(['currentUser' => $_SESSION['user_id'], 'restaurantId' => $restaurantId]);
$ratingList = $reviewsStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($restaurant['Restaurant_Name']); ?> - CvilleEats</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php
    $headerSubtitle = 'Restaurant details, ratings, and reviews';
    include 'includes/header.php';
    ?>

    <div class="page-container">
        <div class="detail-card">
            <div class="detail-grid">
                <div>
                    <h2><?php echo htmlspecialchars($restaurant['Restaurant_Name']); ?></h2>
                    <p class="price">Price: <?php echo htmlspecialchars($restaurant['Price_Level']); ?></p>
                    <p class="restaurant-address">
                        <?php echo htmlspecialchars($restaurant['Street']); ?><br>
                        <?php echo htmlspecialchars($restaurant['City']); ?>, <?php echo htmlspecialchars($restaurant['State']); ?>
                    </p>
                    <?php if (!empty($restaurant['Phones'])): ?>
                        <p class="restaurant-contact"><strong>Phone:</strong> <?php echo htmlspecialchars($restaurant['Phones']); ?></p>
                    <?php endif; ?>
                    <?php if (!empty($restaurant['Cuisines'])): ?>
                        <div class="tag-row">
                            <?php foreach (explode(', ', $restaurant['Cuisines']) as $cuisine): ?>
                                <span class="tag"><?php echo htmlspecialchars($cuisine); ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <div class="tag-row">
                        <?php if ($restaurant['Vegetarian_Options']): ?><span class="tag">Vegetarian</span><?php endif; ?>
                        <?php if ($restaurant['Vegan_Options']): ?><span class="tag">Vegan</span><?php endif; ?>
                        <?php if ($restaurant['GlutenFree_Options']): ?><span class="tag">Gluten-Free</span><?php endif; ?>
                        <?php if (!$restaurant['Vegetarian_Options'] && !$restaurant['Vegan_Options'] && !$restaurant['GlutenFree_Options']): ?><span class="tag">No dietary info</span><?php endif; ?>
                    </div>
                </div>

                <aside class="meta-panel">
                    <div class="meta-row"><strong>Average</strong><span><?php echo $restaurant['Total_Rating_Count'] > 0 ? number_format($restaurant['Avg_Rating'], 1) . ' / 5 (' . intval($restaurant['Total_Rating_Count']) . ')' : 'No ratings yet'; ?></span></div>
                    <?php if ($currentReview): ?>
                        <div class="meta-row"><strong>Your rating</strong><span><?php echo intval($currentReview['Rating']); ?> / 5</span></div>
                    <?php endif; ?>
                    <?php if (!empty($_SESSION['is_admin'])): ?>
                        <a class="button" href="edit-restaurant.php?id=<?php echo $restaurantId; ?>" style="margin-bottom: 10px;">Edit restaurant</a>
                    <?php endif; ?>
                    <a class="button" href="review.php?id=<?php echo $restaurantId; ?>">
                        <?php echo $currentReview ? 'Edit review' : 'Add review'; ?>
                    </a>
                </aside>
            </div>

            <div class="opening-hours-card">
                <h3 class="section-title">Opening hours</h3>
                <?php if (empty($openingHours)): ?>
                    <p class="no-results">Opening hours not available.</p>
                <?php else: ?>
                    <table class="hours-table">
                        <?php foreach ($openingHours as $hours): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($hours['Day_Of_Week']); ?></td>
                                <td><?php echo $hours['Open_Time'] && $hours['Close_Time'] ? htmlspecialchars(date('g:i A', strtotime($hours['Open_Time']))) . ' - ' . htmlspecialchars(date('g:i A', strtotime($hours['Close_Time']))) : 'Closed'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                <?php endif; ?>
            </div>
        </div>

        <div class="section-card">
            <h3 class="section-title">Community reviews (<?php echo intval($restaurant['Rating_Count']); ?>)</h3>
            <?php if (empty($ratingList)): ?>
                <p class="no-results">No reviews yet. Be the first to add one!</p>
            <?php else: ?>
                <?php foreach ($ratingList as $rating): ?>
                    <div class="review-card">
                        <div class="review-header">
                            <strong><?php echo htmlspecialchars($rating['Username']); ?></strong>
                            <span><?php echo intval($rating['Rating']); ?> / 5</span>
                        </div>
                        <?php if (trim($rating['Comment']) !== ''): ?>
                            <p><?php echo nl2br(htmlspecialchars($rating['Comment'])); ?></p>
                        <?php endif; ?>
                        <div class="review-footer">
                            <span class="review-count"><?php echo date('M j, Y', strtotime($rating['Review_Date'])); ?></span>
                            <form method="POST" class="review-like-form">
                                <button class="favorite-toggle<?php echo $rating['Liked_By_Current_User'] ? ' favorited' : ''; ?>" type="submit" name="toggle_like" value="<?php echo intval($rating['Review_ID']); ?>" aria-label="Like review">
                                    ♥ <?php echo intval($rating['Like_Count']); ?>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
