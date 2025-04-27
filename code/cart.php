<?php
include 'db.php';
include 'header.php';
session_start();

$isLoggedIn = isset($_SESSION['user_id']);
$user_id = $_SESSION['user_id'] ?? null;

if (!$isLoggedIn) {
    echo "<p>Пожалуйста, авторизуйтесь для просмотра корзины.</p>";
    exit;
}

// Получаем товары и инструменты из корзины
$cartItems = [];
$totalPrice = 0;

$queryInstruments = $pdo->prepare("SELECT i.*, ci.IDK FROM Корзина_инструмент ci
                                    JOIN Инструменты i ON ci.IDI = i.IDI
                                    WHERE ci.IDK = ?");
$queryInstruments->execute([$user_id]);
$instruments = $queryInstruments->fetchAll();

$queryProducts = $pdo->prepare("SELECT t.*, ct.IDK FROM Корзина_товар ct
                                JOIN Товары t ON ct.IDT = t.IDT
                                WHERE ct.IDK = ?");
$queryProducts->execute([$user_id]);
$products = $queryProducts->fetchAll();

$cartItems = array_merge($instruments, $products);

// Рассчитываем общую стоимость
foreach ($cartItems as $item) {
    if (isset($item['Цена'])) {
        $totalPrice += $item['Цена'];
    }
}

// Получаем информацию о пользователе
$queryUser = $pdo->prepare("SELECT Фамилия, Имя, Номер_телефона, Email FROM Клиенты WHERE IDK = ?");
$queryUser->execute([$user_id]);
$userData = $queryUser->fetch();

?>

<div class="container">
<div class="personal-info-section bg-white p-4 rounded shadow-sm">
    <h2 class="text-center my-4">Корзина</h2>

    <?php if (empty($cartItems)) { ?>
        <p>Ваша корзина пуста.</p>
    <?php } else { ?>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Название</th>
                    <th>Цена</th>
                    <th>Действия</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cartItems as $item) { 
                    $itemName = isset($item['Название']) ? $item['Название'] : (isset($item['Тип_инструмента']) ? $item['Тип_инструмента'] : 'Неизвестный товар');
                    $itemPrice = isset($item['Цена']) ? $item['Цена'] : 0;
                    $itemId = isset($item['IDI']) ? $item['IDI'] : (isset($item['IDT']) ? $item['IDT'] : null);
                    $removeLink = (isset($item['IDI']))
                        ? "remove_from_cart.php?type=instruments&id={$itemId}"
                        : "remove_from_cart.php?type=products&id={$itemId}";
                ?>
                    <tr>
                        <td><?= htmlspecialchars($itemName) ?></td>
                        <td><?= htmlspecialchars($itemPrice) ?> руб.</td>
                        <td><a href="<?= $removeLink ?>" class="btn btn-danger">Удалить</a></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <h4>Общая стоимость: <?= $totalPrice ?> руб.</h4>
		</div>
        <!-- Кнопка оформления заказа -->
        <button id="orderBtn" class="btn btn-primary mt-3">Оформить заказ</button>
		
        <div id="orderForm" class="mt-4" style="display: none;">
		<div class="personal-info-section bg-white p-4 rounded shadow-sm">
            <h4>Оформление заказа</h4>
            <form action="process_order.php" method="POST">
                <div class="mb-3">
                    <label for="name" class="form-label">ФИО</label>
                    <input type="text" class="form-control" id="name" name="name" required value="<?= htmlspecialchars($userData['Фамилия'] . ' ' . $userData['Имя']) ?>">
                </div>
                <div class="mb-3">
                    <label for="phone" class="form-label">Номер телефона</label>
                    <input type="text" class="form-control" id="phone" name="phone" required value="<?= htmlspecialchars($userData['Номер_телефона']) ?>">
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required value="<?= htmlspecialchars($userData['Email']) ?>">
                </div>
                <div class="mb-3">
                    <label for="address" class="form-label">Адрес доставки</label>
                    <input type="text" class="form-control" id="address" name="address" required>
                </div>
                <div class="mb-3">
                    <label for="delivery_date" class="form-label">Дата доставки</label>
                    <input type="date" class="form-control" id="delivery_date" name="delivery_date" required min="<?= date('Y-m-d', strtotime('+3 days')) ?>">
                </div>

                <button type="submit" class="btn btn-success">Оформить заказ</button>
            </form>
			</div>
        </div>

    <?php } ?>

</div>

<script>
    // Показать форму оформления заказа
    document.getElementById('orderBtn').addEventListener('click', function () {
        document.getElementById('orderForm').style.display = 'block';
    });
</script>

<?php include 'footer.php'; ?>
