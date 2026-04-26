<?php
session_start();
require_once "includes/connect-db.php";

// admin protection
if (empty($_SESSION["is_admin"])) {
    die("Access denied.");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Invalid request method.');
}

$csrfToken = $_POST['csrf_token'] ?? '';
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
    die('Invalid CSRF token.');
}

$id = $_POST['id'] ?? null;
if (!$id) die("Invalid request");

// get pending data
$stmt = $pdo->prepare("SELECT * FROM Pending_Restaurant WHERE Pending_ID = ?");
$stmt->execute([$id]);
$data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$data) die("Not found");

// insert into Location
$stmt = $pdo->prepare("
    INSERT INTO Location (Zip_Code, City, State)
    VALUES (?, ?, ?)
    ON DUPLICATE KEY UPDATE City=VALUES(City), State=VALUES(State)
");
$stmt->execute([$data['Zip_Code'], $data['City'], $data['State']]);

// insert into Restaurant
$stmt = $pdo->prepare("
    INSERT INTO Restaurant
    (Restaurant_Name, Price_Level, Street, Zip_Code, Vegetarian_Options, Vegan_Options, GlutenFree_Options)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");
$stmt->execute([
    $data['Name'],
    $data['Price_Level'],
    $data['Street'],
    $data['Zip_Code'],
    $data['Vegetarian'],
    $data['Vegan'],
    $data['GlutenFree']
]);

// delete from pending
$pdo->prepare("DELETE FROM Pending_Restaurant WHERE Pending_ID = ?")->execute([$id]);

header("Location: admin-approvals.php");
exit;