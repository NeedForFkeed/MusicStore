<?php
include 'db.php';
include 'header.php';
session_start();
$isLoggedIn = isset($_SESSION['user_id']);

// Получение параметров
$id = $_GET['id'] ?? null;
$type = $_GET['type'] ?? null;

if (!$id || !in_array($type, ['products', 'instruments'])) {
    echo "<div class='container py-4'><h4 class='text-danger'>Некорректный запрос.</h4></div>";
    include 'footer.php';
    exit;
}

// Запрос данных в зависимости от типа
if ($type === 'products') {
    $stmt = $pdo->prepare("SELECT * FROM Товары WHERE IDT = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();
} else {
    $stmt = $pdo->prepare("SELECT * FROM Инструменты WHERE IDI = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();
}

if (!$item) {
    echo "<div class='container py-4'><h4 class='text-danger'>Продукт не найден.</h4></div>";
    include 'footer.php';
    exit;
}

// Проверка, добавлен ли товар в корзину
$user_id = $_SESSION['user_id'] ?? null;
$alreadyInCart = false;

if ($user_id) {
    if ($type === 'products') {
        $stmtCheckCart = $pdo->prepare("SELECT COUNT(*) FROM Корзина_товар WHERE IDK = ? AND IDT = ?");
        $stmtCheckCart->execute([$user_id, $id]);
        $alreadyInCart = $stmtCheckCart->fetchColumn() > 0;
    } elseif ($type === 'instruments') {
        $stmtCheckCart = $pdo->prepare("SELECT COUNT(*) FROM Корзина_инструмент WHERE IDK = ? AND IDI = ?");
        $stmtCheckCart->execute([$user_id, $id]);
        $alreadyInCart = $stmtCheckCart->fetchColumn() > 0;
    }
}
?>

<div class="container py-5">
    <div class="row">
        <div class="col-md-5">
            <img src="<?= htmlspecialchars($item['image_path']) ?>" alt="<?= htmlspecialchars($item['Название']) ?>" class="img-fluid rounded shadow">
        </div>
        <div class="col-md-7">
            <h2><?= htmlspecialchars($item['Название']) ?></h2>

            <?php if ($type === 'products'): ?>
                <p class="text-muted">Жанр: <?= htmlspecialchars($item['Жанр']) ?> | Год выпуска: <?= htmlspecialchars($item['Год_выпуска']) ?></p>
            <?php else: ?>
                <p class="text-muted">Тип: <?= htmlspecialchars($item['Тип_инструмента']) ?> | Производитель: <?= htmlspecialchars($item['Производитель']) ?></p>
            <?php endif; ?>

            <h4 class="text-success">Цена: <?= htmlspecialchars($item['Цена']) ?> руб.</h4>
            <p><?= nl2br(htmlspecialchars($item['Описание'])) ?></p>

            <?php if ($isLoggedIn): ?>
                <?php if ($alreadyInCart): ?>
                    <button class="btn btn-secondary mt-3" disabled>Товар уже в корзине</button>
                <?php else: ?>
					<button id="addToCartBtn" class="btn btn-success mt-3">Добавить в корзину</button>
                <?php endif; ?>
            <?php else: ?>
                <button class="btn btn-secondary mt-3" onclick="showLoginAlert()">Добавить в корзину</button>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Alert -->
<div id="loginAlert" class="alert alert-warning alert-dismissible fade show position-fixed bottom-0 start-50 translate-middle-x" style="z-index: 9999; display: none;" role="alert">
    Пожалуйста, авторизуйтесь для добавления товаров в корзину.
    <button type="button" class="btn-close" onclick="hideLoginAlert()"></button>
</div>

<!-- Уведомление об успешном добавлении товара в корзину -->
<div id="addToCartAlert" class="alert alert-success alert-dismissible fade show position-fixed bottom-0 start-50 translate-middle-x" style="z-index: 9999; display: none;" role="alert">
    Товар добавлен в корзину!
    <button type="button" class="btn-close" onclick="hideAddToCartAlert()"></button>
</div>

<div id="alreadyInCart" class="alert alert-warning alert-dismissible fade show position-fixed bottom-0 start-50 translate-middle-x" style="z-index: 9999; display: none;" role="alert">
    Товар уже добавлен в корзину!
    <button type="button" class="btn-close" onclick="hideAddToCartAlert()"></button>
</div>

<script>
function showLoginAlert() {
    const alert = document.getElementById('loginAlert');
    alert.style.display = 'block';
    setTimeout(() => {
        hideLoginAlert();
    }, 4000);
}

function hideLoginAlert() {
    document.getElementById('loginAlert').style.display = 'none';
}

function showAlreadyAlert() {
    const alert = document.getElementById('alreadyInCart');
    alert.style.display = 'block';
    setTimeout(() => {
        hideAlreadyAlert();
    }, 2000);
}

function hideAlreadyAlert() {
    document.getElementById('alreadyInCart').style.display = 'none';
}

function showAddToCartAlert() {
    const alert = document.getElementById('addToCartAlert');
    alert.style.display = 'block';
    setTimeout(() => {
        hideAddToCartAlert();
    }, 2000);
}

function hideAddToCartAlert() {
    document.getElementById('addToCartAlert').style.display = 'none';
}

function hideAddToCartAlert() {
    document.getElementById('addToCartAlert').style.display = 'none';
}

// Обработчик клика по кнопке "Добавить в корзину"
document.getElementById('addToCartBtn')?.addEventListener('click', function() {
    const type = '<?= $type ?>';
    const id = '<?= $id ?>';
    
    fetch(`add_to_cart.php?type=${type}&id=${id}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                document.getElementById('addToCartAlert').style.display = 'block';
                document.getElementById('addToCartBtn').innerText = 'Товар уже в корзине';
                document.getElementById('addToCartBtn').classList.add('btn-secondary');
                document.getElementById('addToCartBtn').classList.remove('btn-success');
            } else {
                showAlreadyAlert(); // Например, если товар уже в корзине
            }
        })
        .catch(error => {
            console.error('Ошибка:', error);
        });
});
</script>

<?php include 'footer.php'; ?>
