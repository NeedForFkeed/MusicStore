<?php
include 'db.php';
session_start(); // предполагается, что ID клиента хранится в $_SESSION['user_id']

$user_id = $_SESSION['user_id'] ?? null;
$type = $_GET['type'] ?? '';
$id = $_GET['id'] ?? null;
$response = ['status' => 'error', 'message' => 'Произошла ошибка.'];

if ($user_id && $type && $id) {
    if ($type === 'instruments') {
        // Проверка, что инструмент не добавлен в корзину
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM Корзина_инструмент WHERE IDK = ? AND IDI = ?");
        $stmtCheck->execute([$user_id, $id]);
        $alreadyInCart = $stmtCheck->fetchColumn() > 0;

        if (!$alreadyInCart) {
            $stmt = $pdo->prepare("INSERT INTO Корзина_инструмент (IDK, IDI) VALUES (?, ?)");
            $stmt->execute([$user_id, $id]);
            $response = ['status' => 'success', 'message' => 'Инструмент добавлен в корзину.'];
        } else {
            $response = ['status' => 'info', 'message' => 'Инструмент уже в корзине.'];
        }
    } elseif ($type === 'products') {
        // Проверка, что товар не добавлен в корзину
        $stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM Корзина_товар WHERE IDK = ? AND IDT = ?");
        $stmtCheck->execute([$user_id, $id]);
        $alreadyInCart = $stmtCheck->fetchColumn() > 0;

        if (!$alreadyInCart) {
            $stmt = $pdo->prepare("INSERT INTO Корзина_товар (IDK, IDT) VALUES (?, ?)");
            $stmt->execute([$user_id, $id]);
            $response = ['status' => 'success', 'message' => 'Товар добавлен в корзину.'];
        } else {
            $response = ['status' => 'info', 'message' => 'Товар уже в корзине.'];
        }
    }
}

echo json_encode($response); // Отправляем ответ в формате JSON
exit;
?>
