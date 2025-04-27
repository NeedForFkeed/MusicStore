<?php
include 'db.php';
session_start();

$isLoggedIn = isset($_SESSION['user_id']);
$user_id = $_SESSION['user_id'] ?? null;

if (!$isLoggedIn) {
    header('Location: login.php');
    exit;
}

$type = $_GET['type'] ?? '';
$id = $_GET['id'] ?? null;

if ($type && $id) {
    if ($type === 'instruments') {
        $stmt = $pdo->prepare("DELETE FROM Корзина_инструмент WHERE IDK = ? AND IDI = ?");
        $stmt->execute([$user_id, $id]);
    } elseif ($type === 'products') {
        $stmt = $pdo->prepare("DELETE FROM Корзина_товар WHERE IDK = ? AND IDT = ?");
        $stmt->execute([$user_id, $id]);
    }
}

header('Location: cart.php');
exit;
