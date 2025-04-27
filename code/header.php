<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Музыкальный магазин</title>
    <!-- Подключение Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<header class="bg-dark text-light py-3">
    <div class="container d-flex justify-content-between align-items-center">
        <div class="logo d-flex align-items-center gap-2">
            <img src="images/logo.png" alt="Логотип" width="55" height="55">
            <span class="fs-4"><a class="nav-link text-light" href="index.php">MusicStore</a></span>
        </div>
        <nav>
            <ul class="nav">
				<li class="nav-item"><a class="nav-link text-light" href="index.php">Главная</a></li>
                <li class="nav-item"><a class="nav-link text-light" href="products.php">Все товары</a></li>
                <li class="nav-item"><a class="nav-link text-light" href="cart.php">Корзина</a></li>
                <li class="nav-item"><a class="nav-link text-light" href="news.php">Статьи</a></li>
                <li class="nav-item"><a class="nav-link text-light" href="about.php">О нас</a></li>
                <li class="nav-item"><a class="nav-link text-light" href="account.php">Аккаунт</a></li>
            </ul>
        </nav>
    </div>
</header>
<main class="container">
