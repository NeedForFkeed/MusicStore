<?php
include 'db.php';
include 'header.php';
session_start();

// Проверка на авторизацию
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Получаем данные клиента
$stmt = $pdo->prepare("SELECT * FROM клиенты WHERE IDK = :user_id");
$stmt->execute(['user_id' => $user_id]);
$user = $stmt->fetch();

// Флаг редактирования
$editing = isset($_POST['edit']) && $_POST['edit'] == 'true';

// Проверка на отправку формы для редактирования данных
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $lastName = $_POST['last_name'] ?? '';
    $firstName = $_POST['first_name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $dob = $_POST['dob'] ?? '';
    $email = $_POST['email'] ?? '';

    // Обновление данных клиента
    $stmt = $pdo->prepare("UPDATE клиенты SET Фамилия = :last_name, Имя = :first_name, Номер_телефона = :phone, Дата_рождения = :dob, email = :email WHERE IDK = :user_id");
    $stmt->execute([
        'last_name' => $lastName,
        'first_name' => $firstName,
        'phone' => $phone,
        'dob' => $dob,
        'email' => $email,
        'user_id' => $user_id
    ]);

    // Обновляем данные в сессии
    $user['Фамилия'] = $lastName;
    $user['Имя'] = $firstName;
    $user['Номер_телефона'] = $phone;
    $user['Дата_рождения'] = $dob;
    $user['email'] = $email;
    $success = 'Данные успешно обновлены!';
}

// История заказов
$stmtOrders = $pdo->prepare("
    SELECT o.IDZ, o.Дата, o.Адрес_доставки, i.Название AS инструмент, t.Название AS товар
    FROM заказ o
    LEFT JOIN заказ_инструмент oi ON o.IDZ = oi.IDI
    LEFT JOIN инструменты i ON oi.IDZ = i.IDI
    LEFT JOIN заказ_товар ot ON o.IDZ = ot.IDT
    LEFT JOIN товары t ON ot.IDZ = t.IDT
    WHERE o.IDK = :user_id
");
$stmtOrders->execute(['user_id' => $user_id]);
$orders = $stmtOrders->fetchAll();
?>

<div class="py-5">
    <h2 class="text-center mb-4">Личный кабинет</h2>

    <!-- Уведомления об успехе -->
    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <!-- Секция с персональной информацией -->
    <div class="personal-info-section bg-white p-4 rounded shadow-sm">
        <h4>Ваши данные</h4>
        <form method="POST" action="account.php">
            <div class="mb-3">
                <label for="last_name" class="form-label">Фамилия</label>
                <?php if ($editing): ?>
                    <input type="text" name="last_name" id="last_name" class="form-control" value="<?= htmlspecialchars($user['Фамилия']) ?>" required>
                <?php else: ?>
                    <p><?= htmlspecialchars($user['Фамилия']) ?></p>
                <?php endif; ?>
            </div>
            <div class="mb-3">
                <label for="first_name" class="form-label">Имя</label>
                <?php if ($editing): ?>
                    <input type="text" name="first_name" id="first_name" class="form-control" value="<?= htmlspecialchars($user['Имя']) ?>" required>
                <?php else: ?>
                    <p><?= htmlspecialchars($user['Имя']) ?></p>
                <?php endif; ?>
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label">Номер телефона</label>
                <?php if ($editing): ?>
                    <input type="text" name="phone" id="phone" class="form-control" value="<?= htmlspecialchars($user['Номер_телефона']) ?>" required>
                <?php else: ?>
                    <p><?= htmlspecialchars($user['Номер_телефона']) ?></p>
                <?php endif; ?>
            </div>
            <div class="mb-3">
                <label for="dob" class="form-label">Дата рождения</label>
                <?php if ($editing): ?>
                    <input type="date" name="dob" id="dob" class="form-control" value="<?= htmlspecialchars($user['Дата_рождения']) ?>" required>
                <?php else: ?>
                    <p><?=date("d.m.Y", strtotime($user['Дата_рождения'])) ?></p>
                <?php endif; ?>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <?php if ($editing): ?>
                    <input type="email" name="email" id="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                <?php else: ?>
                    <p><?= htmlspecialchars($user['email']) ?></p>
                <?php endif; ?>
            </div>
            <?php if ($editing): ?>
                <button type="submit" name="save" class="btn btn-primary">Сохранить изменения</button>
            <?php else: ?>
                <button type="submit" name="edit" value="true" class="btn btn-warning">Редактировать</button>
            <?php endif; ?>
        </form>
    </div>

    <!-- Смена пароля -->
	<div class="personal-info-section bg-white p-4 rounded shadow-sm">
    <div class="mb-4">
        <h4>Смена пароля</h4>
        <form method="POST" action="account.php">
            <div class="mb-3">
                <label for="current_password" class="form-label">Текущий пароль</label>
                <input type="password" name="current_password" id="current_password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="new_password" class="form-label">Новый пароль</label>
                <input type="password" name="new_password" id="new_password" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="confirm_password" class="form-label">Подтвердите новый пароль</label>
                <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
            </div>
            <button type="submit" name="change_password" class="btn btn-warning">Изменить пароль</button>
        </form>
    </div>
	</div>

<div class="personal-info-section bg-white p-4 rounded shadow-sm mt-4">
    <h4>История заказов</h4>

    <?php
    // Получаем все заказы пользователя
    $stmt = $pdo->prepare("SELECT * FROM заказ WHERE IDK = :user_id ORDER BY Дата DESC");
    $stmt->execute(['user_id' => $user_id]);
    $orders = $stmt->fetchAll();

    if (count($orders) > 0):
    ?>
        <?php foreach ($orders as $order): ?>
            <div class="border rounded p-3 mb-3">
                <h5>Заказ №<?= $order['IDZ'] ?> от <?= date('d.m.Y', strtotime($order['Дата'])) ?></h5>
                <p><strong>Адрес доставки:</strong> <?= htmlspecialchars($order['Адрес_доставки']) ?></p>
                <p><strong>Статус:</strong> <?= htmlspecialchars($order['Статус']) ?></p>
                <p><strong>Сумма:</strong> <?= $order['Сумма'] ?> руб.</p>

                <h6>Инструменты:</h6>
                <ul>
                    <?php
                    $stmtInstr = $pdo->prepare("
                        SELECT i.Название 
                        FROM заказ_инструмент zi 
                        JOIN инструменты i ON zi.IDI = i.IDI 
                        WHERE zi.IDZ = ?
                    ");
                    $stmtInstr->execute([$order['IDZ']]);
                    $tools = $stmtInstr->fetchAll();
                    if (count($tools) > 0) {
                        foreach ($tools as $tool) {
                            echo "<li>" . htmlspecialchars($tool['Название']) . "</li>";
                        }
                    } else {
                        echo "<li>Нет</li>";
                    }
                    ?>
                </ul>

                <h6>Товары:</h6>
                <ul>
                    <?php
                    $stmtProd = $pdo->prepare("
                        SELECT t.Название 
                        FROM заказ_товар zt 
                        JOIN товары t ON zt.IDT = t.IDT 
                        WHERE zt.IDZ = ?
                    ");
                    $stmtProd->execute([$order['IDZ']]);
                    $products = $stmtProd->fetchAll();
                    if (count($products) > 0) {
                        foreach ($products as $product) {
                            echo "<li>" . htmlspecialchars($product['Название']) . "</li>";
                        }
                    } else {
                        echo "<li>Нет</li>";
                    }
                    ?>
                </ul>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>Вы ещё не сделали ни одного заказа.</p>
    <?php endif; ?>
</div>
</div>

<?php include 'footer.php'; ?>
