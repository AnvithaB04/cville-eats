<?php

session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: auth/login.php");
    exit;
}

require_once "includes/connect-db.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_favorite'])) {
    $restaurantId = (int) $_POST['toggle_favorite'];

    $stmt = $pdo->prepare("SELECT 1 FROM Favorite WHERE User_ID = :userId AND Restaurant_ID = :restaurantId");
    $stmt->execute(['userId' => $_SESSION['user_id'], 'restaurantId' => $restaurantId]);
    $favorited = $stmt->fetchColumn();

    if ($favorited) {
        $stmt = $pdo->prepare("DELETE FROM Favorite WHERE User_ID = :userId AND Restaurant_ID = :restaurantId");
        $stmt->execute(['userId' => $_SESSION['user_id'], 'restaurantId' => $restaurantId]);
    } else {
        $stmt = $pdo->prepare("INSERT IGNORE INTO Favorite (User_ID, Restaurant_ID) VALUES (:userId, :restaurantId)");
        $stmt->execute(['userId' => $_SESSION['user_id'], 'restaurantId' => $restaurantId]);
    }

    header("Location: explore.php");
    exit;
}

$currentDay = date('l'); // e.g., 'Monday'
$currentTime = date('H:i:s');

$search = trim($_GET['search'] ?? '');
$sort = $_GET['sort'] ?? 'name';
$diet = $_GET['diet'] ?? 'all';
$openNow = isset($_GET['open_now']);

$sql = "
    SELECT r.Restaurant_ID, r.Restaurant_Name, r.Price_Level, r.Street, l.City, l.State,
           r.Vegetarian_Options, r.Vegan_Options, r.GlutenFree_Options,
           COALESCE(AVG(rv.Rating), 0) AS Avg_Rating,
           COUNT(rv.Review_ID) AS Rating_Count,
           MAX(f.User_ID IS NOT NULL) AS Is_Favorite,
           CASE WHEN EXISTS (
               SELECT 1 FROM Opening_Hours oh
               WHERE oh.Restaurant_ID = r.Restaurant_ID
               AND oh.Day_Of_Week = '$currentDay'
               AND '$currentTime' BETWEEN oh.Open_Time AND oh.Close_Time
           ) THEN 1 ELSE 0 END AS Is_Open_Now
    FROM Restaurant r
    JOIN Location l ON r.Zip_Code = l.Zip_Code
    LEFT JOIN Review rv ON rv.Restaurant_ID = r.Restaurant_ID
    LEFT JOIN Favorite f ON f.Restaurant_ID = r.Restaurant_ID AND f.User_ID = :userId
    GROUP BY r.Restaurant_ID
    ORDER BY r.Restaurant_Name ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute(['userId' => $_SESSION['user_id']]);
$restaurants = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Explore Restaurants - CvilleEats</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="page-container">
        <div class="controls">
            <div>
                <label for="search">Search</label>
                <input type="text" id="search" placeholder="Restaurant name">
            </div>

            <div>
                <label for="sort">Sort By</label>
                <select id="sort">
                    <option value="name">Restaurant Name</option>
                    <option value="price">Price</option>
                    <option value="rating">Rating</option>
                </select>
            </div>

            <div>
                <label for="diet">Dietary</label>
                <select id="diet">
                    <option value="all">All</option>
                    <option value="vegetarian">Vegetarian</option>
                    <option value="vegan">Vegan</option>
                    <option value="glutenfree">Gluten-Free</option>
                </select>
            </div>

            <div>
                <label><input type="checkbox" id="openNow"> Open Now</label>
            </div>

            <div class="action-button">
                <a class="button button-secondary" href="add-restaurant.php">Add Restaurant</a>
            </div>
        </div>

        <div class="restaurant-grid" id="restaurantGrid">
            <?php foreach ($restaurants as $restaurant): ?>
                <?php
                    $dietTags = [];
                    if ($restaurant['Vegetarian_Options']) $dietTags[] = 'vegetarian';
                    if ($restaurant['Vegan_Options']) $dietTags[] = 'vegan';
                    if ($restaurant['GlutenFree_Options']) $dietTags[] = 'glutenfree';
                    $dietString = implode(' ', $dietTags);
                ?>
                <div class="restaurant-card" data-name="<?php echo htmlspecialchars(strtolower($restaurant['Restaurant_Name'])); ?>" data-price="<?php echo htmlspecialchars($restaurant['Price_Level']); ?>" data-rating="<?php echo htmlspecialchars($restaurant['Avg_Rating']); ?>" data-diet="<?php echo htmlspecialchars($dietString); ?>" data-open="<?php echo htmlspecialchars($restaurant['Is_Open_Now']); ?>">
                    <form method="POST" class="favorite-form">
                        <button type="submit" name="toggle_favorite" value="<?php echo intval($restaurant['Restaurant_ID']); ?>" class="favorite-toggle<?php echo $restaurant['Is_Favorite'] ? ' favorited' : ''; ?>" aria-label="Toggle favorite">
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

                        <?php if (
                            !$restaurant['Vegetarian_Options'] &&
                            !$restaurant['Vegan_Options'] &&
                            !$restaurant['GlutenFree_Options']
                        ): ?>
                            <span class="tag">No dietary info</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <script>
        function filterRestaurants() {
            const searchTerm = document.getElementById('search').value.toLowerCase();
            const sortBy = document.getElementById('sort').value;
            const dietFilter = document.getElementById('diet').value;
            const openNow = document.getElementById('openNow').checked;

            const cards = document.querySelectorAll('.restaurant-card');
            const cardsArray = Array.from(cards);

            // Filter
            const filteredCards = cardsArray.filter(card => {
                const name = card.dataset.name;
                const diet = card.dataset.diet;
                const isOpen = card.dataset.open === '1';

                const matchesSearch = name.includes(searchTerm);
                const matchesDiet = dietFilter === 'all' || diet.includes(dietFilter);
                const matchesOpen = !openNow || isOpen;

                return matchesSearch && matchesDiet && matchesOpen;
            });

            // Sort
            filteredCards.sort((a, b) => {
                if (sortBy === 'name') {
                    return a.dataset.name.localeCompare(b.dataset.name);
                } else if (sortBy === 'price') {
                    return a.dataset.price.length - b.dataset.price.length;
                } else if (sortBy === 'rating') {
                    return parseFloat(b.dataset.rating) - parseFloat(a.dataset.rating); // Descending for rating
                }
                return 0;
            });

            // Hide all, then show filtered and sorted
            cards.forEach(card => card.style.display = 'none');
            filteredCards.forEach(card => card.style.display = 'block');

            // Reorder in DOM
            const grid = document.getElementById('restaurantGrid');
            filteredCards.forEach(card => grid.appendChild(card));
        }

        // Attach event listeners
        document.getElementById('search').addEventListener('input', filterRestaurants);
        document.getElementById('sort').addEventListener('change', filterRestaurants);
        document.getElementById('diet').addEventListener('change', filterRestaurants);
        document.getElementById('openNow').addEventListener('change', filterRestaurants);

        // Initial filter
        filterRestaurants();
    </script>

</body>
</html> 

