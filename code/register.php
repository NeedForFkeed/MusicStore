<?php
include 'db.php';
include 'header.php';
session_start();

// Переменные для ошибок
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем данные из формы
    $lastName = $_POST['last_name'] ?? '';
    $firstName = $_POST['first_name'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $dob = $_POST['dob'] ?? '';
    $password = $_POST['password'] ?? '';
    $email = $_POST['email'] ?? '';

    // Проверка на пустые поля
    if (empty($lastName) || empty($firstName) || empty($phone) || empty($dob) || empty($password) || empty($email)) {
        $error = 'Пожалуйста, заполните все поля.';
    } else {
        // Проверка формата email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Некорректный формат email.';
        } else {
            // Проверка уникальности email
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM клиенты WHERE email = :email");
            $stmt->execute(['email' => $email]);
            $emailExists = $stmt->fetchColumn();

            if ($emailExists) {
                $error = 'Этот email уже зарегистрирован.';
            }
        }

        // Проверка формата номера телефона
        if (!preg_match('/^\d{10}$/', $phone)) {
            $error = 'Номер телефона должен быть в формате 9998009999.';
        }

        // Если ошибок нет, хэшируем пароль и добавляем пользователя в базу
        if (empty($error)) {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // Вставка данных в таблицу клиенты
            $stmt = $pdo->prepare("INSERT INTO клиенты (Фамилия, Имя, Номер_телефона, Дата_рождения, password, email) VALUES (:last_name, :first_name, :phone, :dob, :password, :email)");
            $stmt->execute([
                'last_name' => $lastName,
                'first_name' => $firstName,
                'phone' => $phone,
                'dob' => $dob,
                'password' => $hashedPassword,
                'email' => $email
            ]);

            // Уведомление об успешной регистрации
            $success = 'Регистрация прошла успешно! Теперь вы можете войти в свой аккаунт.';
        }
    }
}
?>

<div class="py-5">
    <h2 class="text-center mb-4">Регистрация</h2>

    <!-- Форма регистрации -->
    <div class="col-md-6 mx-auto">
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>
        <form method="POST" action="register.php">
            <div class="mb-3">
                <label for="last_name" class="form-label">Фамилия</label>
                <input type="text" name="last_name" id="last_name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="first_name" class="form-label">Имя</label>
                <input type="text" name="first_name" id="first_name" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label">Номер телефона</label>
                <input type="text" name="phone" id="phone" class="form-control" pattern="\d{10}" required placeholder="9998009999">
            </div>
            <div class="mb-3">
                <label for="dob" class="form-label">Дата рождения</label>
                <input type="date" name="dob" id="dob" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Пароль</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Зарегистрироваться</button>
        </form>

        <!-- Ссылка на авторизацию -->
        <div class="mt-3 text-center">
            <p>Уже есть аккаунт? <a href="login.php">Войти</a></p>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
