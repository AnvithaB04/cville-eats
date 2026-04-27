<?php
session_start();
require_once "includes/connect-db.php";

// admin protection
if (empty($_SESSION["is_admin"])) {
    die("Access denied.");
}

// only POST allowed
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die('Invalid request method.');
}

// CSRF check
$csrfToken = $_POST['csrf_token'] ?? '';
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $csrfToken)) {
    die('Invalid CSRF token.');
}

$id = $_POST['id'] ?? null;
if (!$id) die("Invalid request");

// delete from pending ONLY
$stmt = $pdo->prepare("DELETE FROM Pending_Restaurant WHERE Pending_ID = ?");
$stmt->execute([$id]);

header("Location: admin-approvals.php");
exit;