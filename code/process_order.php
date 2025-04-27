<?php
include 'db.php';
session_start();

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) {
    echo "Вы не авторизованы.";
    exit;
}

// Получение данных из формы
$name = $_POST['name'] ?? '';
$phone = $_POST['phone'] ?? '';
$email = $_POST['email'] ?? '';
$address = $_POST['address'] ?? '';
$delivery_date = $_POST['delivery_date'] ?? '';

// Проверка обязательных полей
if (!$name || !$phone || !$email || !$address || !$delivery_date) {
    echo "Пожалуйста, заполните все поля.";
    exit;
}

// Получаем все товары из корзины
$queryInstruments = $pdo->prepare("SELECT i.Цена, i.IDI FROM Корзина_инструмент ci
                                   JOIN Инструменты i ON ci.IDI = i.IDI
                                   WHERE ci.IDK = ?");
$queryInstruments->execute([$user_id]);
$instruments = $queryInstruments->fetchAll();

$queryProducts = $pdo->prepare("SELECT t.Цена, t.IDT FROM Корзина_товар ct
                                JOIN Товары t ON ct.IDT = t.IDT
                                WHERE ct.IDK = ?");
$queryProducts->execute([$user_id]);
$products = $queryProducts->fetchAll();

// Подсчёт суммы
$totalPrice = 0;
foreach ($instruments as $item) {
    $totalPrice += $item['Цена'];
}
foreach ($products as $item) {
    $totalPrice += $item['Цена'];
}

// Вставка заказа
$insertOrder = $pdo->prepare("INSERT INTO Заказ (Дата, Статус, Сумма, IDK, Адрес_доставки) VALUES (NOW(), 'Оформлен', ?, ?, ?)");
$insertOrder->execute([$totalPrice, $user_id, $address]);

$orderId = $pdo->lastInsertId();

// Добавление в заказ_инструмент
$insertInstr = $pdo->prepare("INSERT INTO Заказ_Инструмент (IDZ, IDI) VALUES (?, ?)");
foreach ($instruments as $item) {
    $insertInstr->execute([$orderId, $item['IDI']]);
}

// Добавление в заказ_товар
$insertProd = $pdo->prepare("INSERT INTO Заказ_товар (IDZ, IDT) VALUES (?, ?)");
foreach ($products as $item) {
    $insertProd->execute([$orderId, $item['IDT']]);
}

// Очистка корзины
$pdo->prepare("DELETE FROM Корзина_инструмент WHERE IDK = ?")->execute([$user_id]);
$pdo->prepare("DELETE FROM Корзина_товар WHERE IDK = ?")->execute([$user_id]);

// Перенаправление на страницу подтверждения
header("Location: order_confirmation.php?order_id=" . $orderId);
exit;
?>
