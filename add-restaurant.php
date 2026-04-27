<?php
session_start();

if (!isset($_SESSION["user_id"])) {
    header("Location: auth/login.php");
    exit;
}

require_once "includes/connect-db.php";

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
        // prepare data
$phones = $_POST['phones'] ?? '';
$cuisines = $_POST['cuisines'] ?? '';
$hours = json_encode($_POST['hours'] ?? []);

// insert into Pending_Restaurant
$stmt = $pdo->prepare("
    INSERT INTO Pending_Restaurant
    (Name, Price_Level, Street, City, State, Zip_Code, Vegetarian, Vegan, GlutenFree, Phones, Cuisines, Hours)
    VALUES (:name, :price, :street, :city, :state, :zip, :vegetarian, :vegan, :glutenFree, :phones, :cuisines, :hours)
");

$stmt->execute([
    'name' => $name,
    'price' => $price,
    'street' => $street,
    'city' => $city,
    'state' => $state,
    'zip' => $zip,
    'vegetarian' => $vegetarian,
    'vegan' => $vegan,
    'glutenFree' => $glutenFree,
    'phones' => $phones,
    'cuisines' => $cuisines,
    'hours' => $hours
]);

$message = "Restaurant submitted for admin approval!";}
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Restaurant - CvilleEats</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php
    $headerSubtitle = 'Add a new restaurant';
    include 'includes/header.php';
    ?>

    <div class="page-container">
        <a href="explore.php" class="button button-secondary" style="margin-bottom:15px; display:inline-block;">
        ← Back
    </a>
        <div class="section-card">
            <h2>Add Restaurant</h2>
            <?php if (!empty($message)): ?>
                <div class="review-card" style="border-left-color: green;">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>
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
