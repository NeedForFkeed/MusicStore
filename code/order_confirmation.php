<?php
include 'db.php';
include 'header.php';
session_start();

$order_id = $_GET['order_id'] ?? null;
if (!$order_id) {
    echo "<p>Неверный номер заказа.</p>";
    exit;
}

// Получение информации о заказе
$stmt = $pdo->prepare("SELECT z.*, k.Фамилия, k.Имя FROM Заказ z
                       JOIN Клиенты k ON z.IDK = k.IDK
                       WHERE z.IDZ = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    echo "<p>Заказ не найден.</p>";
    exit;
}
?>

<div class="container mt-5">
    <div class="bg-light p-5 rounded">
        <h2>Спасибо за заказ!</h2>
        <p>Номер заказа: <strong>№<?= htmlspecialchars($order['IDZ']) ?></strong></p>
        <p>Статус: <strong><?= htmlspecialchars($order['Статус']) ?></strong></p>
        <p>ФИО: <strong><?= htmlspecialchars($order['Фамилия'] . ' ' . $order['Имя']) ?></strong></p>
        <p>Адрес доставки: <strong><?= htmlspecialchars($order['Адрес_доставки']) ?></strong></p>
        <p>Сумма: <strong><?= htmlspecialchars($order['Сумма']) ?> руб.</strong></p>
        <p>Дата заказа: <strong><?= date("d.m.Y", strtotime(htmlspecialchars($order['Дата']))) ?></strong></p>
		 <p><strong>Скоро наш менеджер свяжется с вами для выяснения детелей!</strong></p>

	</div>
</div>

<?php include 'footer.php'; ?>
