<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit;
}

if (empty($_SESSION['is_admin'])) {
    header('Location: explore.php');
    exit;
}

require_once 'includes/connect-db.php';

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die('Invalid restaurant ID.');
}

$restaurantId = (int) $_GET['id'];
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errors = [];
$message = '';

function normalizePhoneNumber(string $raw): string
{
    return preg_replace('/\D+/', '', $raw) ?? '';
}

function normalizeCuisineKey(string $value): string
{
    if (function_exists('mb_strtolower')) {
        return mb_strtolower($value, 'UTF-8');
    }

    return strtolower($value);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrfToken = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $csrfToken)) {
        $errors[] = 'Invalid request token. Please refresh and try again.';
    }

    $name = trim($_POST['name'] ?? '');
    $price = trim($_POST['price'] ?? '');
    $street = trim($_POST['street'] ?? '');
    $zip = trim($_POST['zip'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $state = trim($_POST['state'] ?? '');
    $phonesInput = trim($_POST['phones'] ?? '');
    $cuisinesInput = trim($_POST['cuisines'] ?? '');
    $vegetarian = isset($_POST['vegetarian']) ? 1 : 0;
    $vegan = isset($_POST['vegan']) ? 1 : 0;
    $glutenFree = isset($_POST['glutenfree']) ? 1 : 0;

    $phoneNumbers = [];
    if ($phonesInput !== '') {
        $phoneParts = array_filter(array_map('trim', explode(',', $phonesInput)));
        foreach ($phoneParts as $phonePart) {
            $normalizedPhone = normalizePhoneNumber($phonePart);
            if ($normalizedPhone === '' || strlen($normalizedPhone) !== 10) {
                $errors[] = 'Each phone number must contain exactly 10 digits.';
                break;
            }
            $phoneNumbers[] = $normalizedPhone;
        }
        $phoneNumbers = array_values(array_unique($phoneNumbers));
    }

    $cuisineNames = [];
    if ($cuisinesInput !== '') {
        $cuisineNames = array_values(array_unique(array_filter(array_map('trim', explode(',', $cuisinesInput)))));
    }

    if ($name === '' || $price === '' || $street === '' || $zip === '' || $city === '' || $state === '') {
        $errors[] = 'Name, price, address, city, state, and zip are required.';
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            $locationExistsStmt = $pdo->prepare(
                "SELECT 1
                 FROM Location
                 WHERE Zip_Code = :zip
                 LIMIT 1"
            );
            $locationExistsStmt->execute(['zip' => $zip]);

            if ($locationExistsStmt->fetchColumn()) {
                $updateLocationStmt = $pdo->prepare(
                    "UPDATE Location
                     SET City = :city,
                         State = :state
                     WHERE Zip_Code = :zip"
                );
                $updateLocationStmt->execute([
                    'zip' => $zip,
                    'city' => $city,
                    'state' => $state,
                ]);
            } else {
                $insertLocationStmt = $pdo->prepare(
                    "INSERT INTO Location (Zip_Code, City, State)
                     VALUES (:zip, :city, :state)"
                );
                $insertLocationStmt->execute([
                    'zip' => $zip,
                    'city' => $city,
                    'state' => $state,
                ]);
            }

            $updateRestaurantStmt = $pdo->prepare(
                "UPDATE Restaurant
                 SET Restaurant_Name = :name,
                     Price_Level = :price,
                     Street = :street,
                     Zip_Code = :zip,
                     Vegetarian_Options = :vegetarian,
                     Vegan_Options = :vegan,
                     GlutenFree_Options = :glutenfree
                 WHERE Restaurant_ID = :id"
            );
            $updateRestaurantStmt->execute([
                'name' => $name,
                'price' => $price,
                'street' => $street,
                'zip' => $zip,
                'vegetarian' => $vegetarian,
                'vegan' => $vegan,
                'glutenfree' => $glutenFree,
                'id' => $restaurantId,
            ]);

            $deletePhoneStmt = $pdo->prepare('DELETE FROM Restaurant_Phone WHERE Restaurant_ID = :id');
            $deletePhoneStmt->execute(['id' => $restaurantId]);

            if (!empty($phoneNumbers)) {
                $insertPhoneStmt = $pdo->prepare(
                    'INSERT INTO Restaurant_Phone (Restaurant_ID, Phone_Number) VALUES (:restaurantId, :phone)'
                );
                foreach ($phoneNumbers as $phoneNumber) {
                    $insertPhoneStmt->execute([
                        'restaurantId' => $restaurantId,
                        'phone' => $phoneNumber,
                    ]);
                }
            }

            $deleteCuisineStmt = $pdo->prepare('DELETE FROM Has_Cuisine WHERE Restaurant_ID = :id');
            $deleteCuisineStmt->execute(['id' => $restaurantId]);

            if (!empty($cuisineNames)) {
                $cuisinePlaceholder = implode(',', array_fill(0, count($cuisineNames), '?'));
                $findCuisineStmt = $pdo->prepare(
                    "SELECT Cuisine_ID, Cuisine_Name
                     FROM Cuisine
                     WHERE Cuisine_Name IN ($cuisinePlaceholder)"
                );
                $findCuisineStmt->execute($cuisineNames);
                $foundCuisines = $findCuisineStmt->fetchAll(PDO::FETCH_KEY_PAIR);

                $foundCuisineLookup = [];
                foreach ($foundCuisines as $foundCuisineName) {
                    $foundCuisineLookup[normalizeCuisineKey($foundCuisineName)] = true;
                }

                $missingCuisines = [];
                foreach ($cuisineNames as $cuisineName) {
                    if (!isset($foundCuisineLookup[normalizeCuisineKey($cuisineName)])) {
                        $missingCuisines[] = $cuisineName;
                    }
                }

                if (!empty($missingCuisines)) {
                    throw new RuntimeException('Unknown cuisine: ' . implode(', ', $missingCuisines));
                }

                $insertCuisineStmt = $pdo->prepare(
                    'INSERT INTO Has_Cuisine (Restaurant_ID, Cuisine_ID) VALUES (:restaurantId, :cuisineId)'
                );
                foreach (array_keys($foundCuisines) as $cuisineId) {
                    $insertCuisineStmt->execute([
                        'restaurantId' => $restaurantId,
                        'cuisineId' => (int) $cuisineId,
                    ]);
                }
            }

            $deleteHoursStmt = $pdo->prepare('DELETE FROM Opening_Hours WHERE Restaurant_ID = :id');
            $deleteHoursStmt->execute(['id' => $restaurantId]);

            $insertHoursStmt = $pdo->prepare(
                "INSERT INTO Opening_Hours (Restaurant_ID, Day_Of_Week, Open_Time, Close_Time)
                 VALUES (:restaurantId, :day, :open, :close)"
            );

            foreach ($days as $day) {
                $open = trim($_POST['hours'][$day]['open'] ?? '');
                $close = trim($_POST['hours'][$day]['close'] ?? '');

                if ($open !== '' && $close !== '') {
                    $insertHoursStmt->execute([
                        'restaurantId' => $restaurantId,
                        'day' => $day,
                        'open' => $open,
                        'close' => $close,
                    ]);
                }
            }

            $pdo->commit();
            $message = 'Restaurant details updated successfully.';
        } catch (RuntimeException $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            $errors[] = $e->getMessage();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            error_log('Edit restaurant failed for Restaurant_ID ' . $restaurantId . ': ' . $e->getMessage());
            $errors[] = 'Failed to update restaurant. Please try again.';
        }
    }
}

$restaurantStmt = $pdo->prepare(
    "SELECT r.Restaurant_ID, r.Restaurant_Name, r.Price_Level, r.Street, r.Zip_Code,
            r.Vegetarian_Options, r.Vegan_Options, r.GlutenFree_Options,
            l.City, l.State
     FROM Restaurant r
     JOIN Location l ON l.Zip_Code = r.Zip_Code
     WHERE r.Restaurant_ID = :id"
);
$restaurantStmt->execute(['id' => $restaurantId]);
$restaurant = $restaurantStmt->fetch(PDO::FETCH_ASSOC);

if (!$restaurant) {
    die('Restaurant not found.');
}

$hoursStmt = $pdo->prepare(
    "SELECT Day_Of_Week, Open_Time, Close_Time
     FROM Opening_Hours
     WHERE Restaurant_ID = :id"
);
$hoursStmt->execute(['id' => $restaurantId]);
$hoursRows = $hoursStmt->fetchAll(PDO::FETCH_ASSOC);

$phoneStmt = $pdo->prepare(
    "SELECT Phone_Number
     FROM Restaurant_Phone
     WHERE Restaurant_ID = :id"
);
$phoneStmt->execute(['id' => $restaurantId]);
$phoneRows = $phoneStmt->fetchAll(PDO::FETCH_COLUMN);

$cuisineStmt = $pdo->prepare(
    "SELECT c.Cuisine_Name
     FROM Has_Cuisine hc
     JOIN Cuisine c ON c.Cuisine_ID = hc.Cuisine_ID
     WHERE hc.Restaurant_ID = :id
     ORDER BY c.Cuisine_Name"
);
$cuisineStmt->execute(['id' => $restaurantId]);
$cuisineRows = $cuisineStmt->fetchAll(PDO::FETCH_COLUMN);

$hoursByDay = [];
foreach ($hoursRows as $row) {
    $hoursByDay[$row['Day_Of_Week']] = [
        'open' => substr((string) $row['Open_Time'], 0, 5),
        'close' => substr((string) $row['Close_Time'], 0, 5),
    ];
}

$prefill = [
    'name' => $_POST['name'] ?? $restaurant['Restaurant_Name'],
    'price' => $_POST['price'] ?? $restaurant['Price_Level'],
    'street' => $_POST['street'] ?? $restaurant['Street'],
    'zip' => $_POST['zip'] ?? $restaurant['Zip_Code'],
    'city' => $_POST['city'] ?? $restaurant['City'],
    'state' => $_POST['state'] ?? $restaurant['State'],
    'phones' => $_POST['phones'] ?? implode(', ', $phoneRows),
    'cuisines' => $_POST['cuisines'] ?? implode(', ', $cuisineRows),
    'vegetarian' => isset($_POST['vegetarian']) ? 1 : (int) $restaurant['Vegetarian_Options'],
    'vegan' => isset($_POST['vegan']) ? 1 : (int) $restaurant['Vegan_Options'],
    'glutenfree' => isset($_POST['glutenfree']) ? 1 : (int) $restaurant['GlutenFree_Options'],
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Restaurant - CvilleEats</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<?php
$headerSubtitle = 'Admin: edit restaurant';
include 'includes/header.php';
?>

<div class="page-container">
    <a href="restaurant.php?id=<?php echo $restaurantId; ?>" class="button button-secondary" style="margin-bottom:15px; display:inline-block;">
        ← Back
    </a>
    <div class="section-card">
        <h2>Edit Restaurant</h2>

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
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

            <label>Restaurant Name</label>
            <input type="text" name="name" value="<?php echo htmlspecialchars($prefill['name']); ?>" required>

            <label>Price Level</label>
            <select name="price" required>
                <option value="" disabled <?php echo empty($prefill['price']) ? 'selected' : ''; ?>>Select price</option>
                <option value="$" <?php echo $prefill['price'] === '$' ? 'selected' : ''; ?>>$</option>
                <option value="$$" <?php echo $prefill['price'] === '$$' ? 'selected' : ''; ?>>$$</option>
                <option value="$$$" <?php echo $prefill['price'] === '$$$' ? 'selected' : ''; ?>>$$$</option>
                <option value="$$$$" <?php echo $prefill['price'] === '$$$$' ? 'selected' : ''; ?>>$$$$</option>
            </select>

            <label>Street Address</label>
            <input type="text" name="street" value="<?php echo htmlspecialchars($prefill['street']); ?>" required>

            <label>City</label>
            <input type="text" name="city" value="<?php echo htmlspecialchars($prefill['city']); ?>" required>

            <label>State</label>
            <input type="text" name="state" value="<?php echo htmlspecialchars($prefill['state']); ?>" required>

            <label>Zip Code</label>
            <input type="text" name="zip" value="<?php echo htmlspecialchars($prefill['zip']); ?>" required>

            <div class="checkbox-row">
                <label><input type="checkbox" name="vegetarian" <?php echo $prefill['vegetarian'] ? 'checked' : ''; ?>> Vegetarian</label>
                <label><input type="checkbox" name="vegan" <?php echo $prefill['vegan'] ? 'checked' : ''; ?>> Vegan</label>
                <label><input type="checkbox" name="glutenfree" <?php echo $prefill['glutenfree'] ? 'checked' : ''; ?>> Gluten-Free</label>
            </div>

            <label>Phone Numbers</label>
            <input type="text" name="phones" value="<?php echo htmlspecialchars($prefill['phones']); ?>" placeholder="e.g. 434-555-0123, 434-555-0456">

            <label>Cuisines</label>
            <input type="text" name="cuisines" value="<?php echo htmlspecialchars($prefill['cuisines']); ?>" placeholder="e.g. Italian, Seafood">

            <h3>Opening Hours</h3>
            <?php foreach ($days as $day): ?>
                <?php
                    $dayOpen = $_POST['hours'][$day]['open'] ?? ($hoursByDay[$day]['open'] ?? '');
                    $dayClose = $_POST['hours'][$day]['close'] ?? ($hoursByDay[$day]['close'] ?? '');
                ?>
                <div class="opening-row">
                    <label><?php echo $day; ?></label>
                    <div class="opening-inputs">
                        <input type="time" name="hours[<?php echo $day; ?>][open]" value="<?php echo htmlspecialchars($dayOpen); ?>">
                        <span>to</span>
                        <input type="time" name="hours[<?php echo $day; ?>][close]" value="<?php echo htmlspecialchars($dayClose); ?>">
                    </div>
                </div>
            <?php endforeach; ?>

            <button class="button" type="submit">Save changes</button>
            <a class="button" href="restaurant.php?id=<?php echo $restaurantId; ?>" style="margin-left: 12px;">Cancel</a>
        </form>
    </div>
</div>
</body>
</html>
