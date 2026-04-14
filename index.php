<?php

session_start();

if (isset($_SESSION["user_id"])) {
    header("Location: home.php");
    exit;
}

header("Location: login.php");
exit;
?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CvilleEats</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="topbar">
        <h1>CvilleEats</h1>
        <p>Charlottesville Restaurant Finder & Reviews</p>
    </div>

    <div class="page-container">
        <div class="controls">
            <div>
                <label for="search">Search</label>
                <input type="text" id="search" placeholder="Restaurant name">
            </div>

            <div>
                <label for="sort">Sort By</label>
                <select id="sort">
                    <option>Restaurant Name</option>
                    <option>Price</option>
                    <option>Rating</option>
                </select>
            </div>

            <div>
                <label for="diet">Dietary</label>
                <select id="diet">
                    <option>All</option>
                    <option>Vegetarian</option>
                    <option>Vegan</option>
                    <option>Gluten-Free</option>
                </select>
            </div>
        </div>

        <div class="restaurant-grid">
            <?php foreach ($restaurants as $restaurant): ?>
                <div class="restaurant-card">
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
</body>
</html>