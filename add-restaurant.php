<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: login.php");
    exit;
}

require_once "connect-db.php";

$message = '';
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $street = trim($_POST['street'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $zip = trim($_POST['zip'] ?? '');
    $vegetarian = isset($_POST['vegetarian']) ? 1 : 0;
    $vegan = isset($_POST['vegan']) ? 1 : 0;
    $glutenFree = isset($_POST['glutenfree']) ? 1 : 0;

    if ($name === '') {
        $errors[] = 'Restaurant name is required.';
    }
    if ($price === '') {
        $errors[] = 'Price level is required.';
    }
    if ($street === '' || $city === '' || $state === '' || $zip === '') {
        $errors[] = 'Street, city, state, and zip code are required.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare(
            "INSERT INTO Location (Zip_Code, City, State)
             VALUES (:zip, :city, :state)
             ON DUPLICATE KEY UPDATE City = VALUES(City), State = VALUES(State)"
        );
        $stmt->execute([
            'zip' => $zip,
            'city' => $city,
            'state' => $state,
        ]);

        $stmt = $pdo->prepare(
            "INSERT INTO Restaurant (Restaurant_Name, Price_Level, Street, Zip_Code, Vegetarian_Options, Vegan_Options, GlutenFree_Options)
             VALUES (:name, :price, :street, :zip, :vegetarian, :vegan, :glutenFree)"
        );
        $stmt->execute([
            'name' => $name,
            'price' => $price,
            'street' => $street,
            'zip' => $zip,
            'vegetarian' => $vegetarian,
            'vegan' => $vegan,
            'glutenFree' => $glutenFree,
        ]);

        $restaurantId = $pdo->lastInsertId();

        $phones = array_filter(array_map('trim', explode(',', $_POST['phones'] ?? '')));
        if (!empty($phones)) {
            $insertPhone = $pdo->prepare("INSERT INTO Restaurant_Phone (Restaurant_ID, Phone_Number) VALUES (:restaurantId, :phone)");
            foreach ($phones as $phone) {
                if ($phone !== '') {
                    $insertPhone->execute(['restaurantId' => $restaurantId, 'phone' => $phone]);
                }
            }
        }

        $cuisines = array_filter(array_map('trim', explode(',', $_POST['cuisines'] ?? '')));
        if (!empty($cuisines)) {
            $findCuisine = $pdo->prepare("SELECT Cuisine_ID FROM Cuisine WHERE Cuisine_Name = :name");
            $insertCuisine = $pdo->prepare("INSERT INTO Cuisine (Cuisine_Name) VALUES (:name)");
            $insertHasCuisine = $pdo->prepare("INSERT INTO Has_Cuisine (Restaurant_ID, Cuisine_ID) VALUES (:restaurantId, :cuisineId)");
            foreach ($cuisines as $cuisineName) {
                if ($cuisineName === '') {
                    continue;
                }

                $findCuisine->execute(['name' => $cuisineName]);
                $cuisine = $findCuisine->fetch(PDO::FETCH_ASSOC);
                if (!$cuisine) {
                    $insertCuisine->execute(['name' => $cuisineName]);
                    $cuisineId = $pdo->lastInsertId();
                } else {
                    $cuisineId = $cuisine['Cuisine_ID'];
                }

                $insertHasCuisine->execute(['restaurantId' => $restaurantId, 'cuisineId' => $cuisineId]);
            }
        }

        $insertHour = $pdo->prepare(
            "INSERT INTO Opening_Hours (Restaurant_ID, Day_Of_Week, Open_Time, Close_Time)
             VALUES (:restaurantId, :day, :open, :close)"
        );

        $hours = $_POST['hours'] ?? [];
        foreach ($hours as $day => $range) {
            $open = trim($range['open'] ?? '');
            $close = trim($range['close'] ?? '');
            if ($open !== '' && $close !== '') {
                $insertHour->execute([
                    'restaurantId' => $restaurantId,
                    'day' => $day,
                    'open' => $open,
                    'close' => $close,
                ]);
            }
        }

        header("Location: restaurant.php?id=$restaurantId");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Restaurant - CvilleEats</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php
    $headerSubtitle = 'Add a new restaurant';
    include 'header.php';
    ?>

    <div class="page-container">
        <div class="section-card">
            <h2>Add Restaurant</h2>
            <?php if (!empty($errors)): ?>
                <div class="review-card" style="border-left-color: #d9534f;">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form method="POST" class="add-restaurant-form">
                <label>Restaurant Name</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>

                <label>Price Level</label>
                <select name="price" required>
                    <option value="" disabled <?php echo empty($_POST['price']) ? 'selected' : ''; ?>>Select price</option>
                    <option value="$" <?php echo (($_POST['price'] ?? '') === '$') ? 'selected' : ''; ?>>$</option>
                    <option value="$$" <?php echo (($_POST['price'] ?? '') === '$$') ? 'selected' : ''; ?>>$$</option>
                    <option value="$$$" <?php echo (($_POST['price'] ?? '') === '$$$') ? 'selected' : ''; ?>>$$$</option>
                    <option value="$$$$" <?php echo (($_POST['price'] ?? '') === '$$$$') ? 'selected' : ''; ?>>$$$$</option>
                </select>

                <label>Street Address</label>
                <input type="text" name="street" value="<?php echo htmlspecialchars($_POST['street'] ?? ''); ?>" required>

                <label>City</label>
                <input type="text" name="city" value="<?php echo htmlspecialchars($_POST['city'] ?? ''); ?>" required>

                <label>State</label>
                <input type="text" name="state" value="<?php echo htmlspecialchars($_POST['state'] ?? ''); ?>" required>

                <label>Zip Code</label>
                <input type="text" name="zip" value="<?php echo htmlspecialchars($_POST['zip'] ?? ''); ?>" required>

                <div class="checkbox-row">
                    <label><input type="checkbox" name="vegetarian" <?php echo isset($_POST['vegetarian']) ? 'checked' : ''; ?>> Vegetarian</label>
                    <label><input type="checkbox" name="vegan" <?php echo isset($_POST['vegan']) ? 'checked' : ''; ?>> Vegan</label>
                    <label><input type="checkbox" name="glutenfree" <?php echo isset($_POST['glutenfree']) ? 'checked' : ''; ?>> Gluten-Free</label>
                </div>

                <label>Phone Numbers</label>
                <input type="text" name="phones" value="<?php echo htmlspecialchars($_POST['phones'] ?? ''); ?>" placeholder="e.g. 434-555-0123">

                <label>Cuisines</label>
                <input type="text" name="cuisines" value="<?php echo htmlspecialchars($_POST['cuisines'] ?? ''); ?>" placeholder="e.g. Italian, Seafood">

                <h3>Opening Hours</h3>
                <?php $days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday']; ?>
                <?php foreach ($days as $day): ?>
                    <div class="opening-row">
                        <label><?php echo $day; ?></label>
                        <div class="opening-inputs">
                            <input type="time" name="hours[<?php echo $day; ?>][open]" value="<?php echo htmlspecialchars($_POST['hours'][$day]['open'] ?? ''); ?>">
                            <span>to</span>
                            <input type="time" name="hours[<?php echo $day; ?>][close]" value="<?php echo htmlspecialchars($_POST['hours'][$day]['close'] ?? ''); ?>">
                        </div>
                    </div>
                <?php endforeach; ?>

                <button class="button" type="submit">Create restaurant</button>
                <a class="button" href="explore.php" style="margin-left: 12px;">Cancel</a>
            </form>
        </div>
    </div>
</body>
</html>
