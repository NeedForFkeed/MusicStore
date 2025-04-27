<?php
include 'db.php';
include 'header.php';
session_start();

// Проверка, если пользователь уже авторизован, перенаправляем на главную страницу
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Переменные для ошибок
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем данные из формы
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Проверяем, что email и пароль не пустые
    if (empty($email) || empty($password)) {
        $error = 'Пожалуйста, заполните все поля.';
    } else {
        // Проверяем наличие пользователя в базе данных
        $stmt = $pdo->prepare("SELECT * FROM клиенты WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Успешная авторизация, сохраняем ID пользователя в сессии
            $_SESSION['user_id'] = $user['IDK'];
            $_SESSION['user_email'] = $user['email'];
            header('Location: index.php'); // Перенаправляем на главную страницу
            exit();
        } else {
            // Если данные не совпали, выводим ошибку
            $error = 'Неверный email или пароль.';
        }
    }
}
?>

<div class="py-5">
    <h2 class="text-center mb-4">Авторизация</h2>

    <!-- Форма авторизации -->
    <div class="col-md-6 mx-auto">
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="POST" action="login.php">
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" name="email" id="email" class="form-control" required>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Пароль</label>
                <input type="password" name="password" id="password" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Войти</button>
        </form>

        <!-- Ссылка на регистрацию -->
        <div class="mt-3 text-center">
            <p>Еще нет аккаунта? <a href="register.php">Зарегистрироваться</a></p>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
