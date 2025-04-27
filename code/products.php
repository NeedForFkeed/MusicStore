<?php
include 'db.php';
include 'header.php';
session_start();
$isLoggedIn = isset($_SESSION['user_id']);
$user_id = $_SESSION['user_id'] ?? null;
?>

<div class="py-4">
    <h2 class="text-center mb-4">Каталог продукции</h2>

    <!-- Выбор категории -->
    <form method="get" class="mb-4 text-center">
        <select name="category" class="form-select w-50 mx-auto" onchange="this.form.submit()">
            <option value="">-- Выберите категорию --</option>
            <option value="instruments" <?= ($_GET['category'] ?? '') === 'instruments' ? 'selected' : '' ?>>Инструменты</option>
            <option value="products" <?= ($_GET['category'] ?? '') === 'products' ? 'selected' : '' ?>>Товары</option>
        </select>
    </form>

    <?php
    $category = $_GET['category'] ?? '';

    if ($category === 'instruments') {
        $types = $pdo->query("SELECT DISTINCT Тип_инструмента FROM Инструменты WHERE Тип_инструмента IS NOT NULL")->fetchAll();
        $manufacturers = $pdo->query("SELECT DISTINCT Производитель FROM Инструменты WHERE Производитель IS NOT NULL")->fetchAll();
        ?>
        <form method="get" class="mb-4 d-flex justify-content-center gap-3 flex-wrap">
            <input type="hidden" name="category" value="instruments">
            <select name="type" class="form-select">
                <option value="">Тип инструмента</option>
                <?php foreach ($types as $row): ?>
                    <option value="<?= $row['Тип_инструмента'] ?>" <?= ($_GET['type'] ?? '') === $row['Тип_инструмента'] ? 'selected' : '' ?>>
                        <?= $row['Тип_инструмента'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <select name="manufacturer" class="form-select">
                <option value="">Производитель</option>
                <?php foreach ($manufacturers as $row): ?>
                    <option value="<?= $row['Производитель'] ?>" <?= ($_GET['manufacturer'] ?? '') === $row['Производитель'] ? 'selected' : '' ?>>
                        <?= $row['Производитель'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button class="btn btn-primary">Фильтровать</button>
            <a href="?category=instruments" class="btn btn-outline-secondary">Сбросить фильтры</a>
        </form>

        <?php
        $query = "SELECT * FROM Инструменты WHERE 1=1";
        if (!empty($_GET['type'])) {
            $query .= " AND Тип_инструмента = " . $pdo->quote($_GET['type']);
        }
        if (!empty($_GET['manufacturer'])) {
            $query .= " AND Производитель = " . $pdo->quote($_GET['manufacturer']);
        }

        $items = $pdo->query($query)->fetchAll();
    }

    elseif ($category === 'products') {
        $genres = $pdo->query("SELECT DISTINCT Жанр FROM Товары WHERE Жанр IS NOT NULL")->fetchAll();
        $years = $pdo->query("SELECT DISTINCT Год_выпуска FROM Товары ORDER BY Год_выпуска DESC")->fetchAll();
        ?>
        <form method="get" class="mb-4 d-flex justify-content-center gap-3 flex-wrap">
            <input type="hidden" name="category" value="products">
            <select name="genre" class="form-select">
                <option value="">Жанр</option>
                <?php foreach ($genres as $row): ?>
                    <option value="<?= $row['Жанр'] ?>" <?= ($_GET['genre'] ?? '') === $row['Жанр'] ? 'selected' : '' ?>>
                        <?= $row['Жанр'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <select name="year" class="form-select">
                <option value="">Год выпуска</option>
                <?php foreach ($years as $row): ?>
                    <option value="<?= $row['Год_выпуска'] ?>" <?= ($_GET['year'] ?? '') == $row['Год_выпуска'] ? 'selected' : '' ?>>
                        <?= $row['Год_выпуска'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button class="btn btn-primary">Фильтровать</button>
            <a href="?category=products" class="btn btn-outline-secondary">Сбросить фильтры</a>
        </form>

        <?php
        $query = "SELECT * FROM Товары WHERE 1=1";
        if (!empty($_GET['genre'])) {
            $query .= " AND Жанр = " . $pdo->quote($_GET['genre']);
        }
        if (!empty($_GET['year'])) {
            $query .= " AND Год_выпуска = " . (int)$_GET['year'];
        }

        $items = $pdo->query($query)->fetchAll();
    }

    if (!empty($category)) {
        echo '<div class="row row-cols-1 row-cols-md-3 g-4">';

        foreach ($items as $item) {
            $productLink = ($category === 'instruments') 
                ? "product.php?type=instruments&id={$item['IDI']}" 
                : "product.php?type=products&id={$item['IDT']}";
            $addToCartLink = ($category === 'instruments') 
                ? "add_to_cart.php?type=instruments&id={$item['IDI']}" 
                : "add_to_cart.php?type=products&id={$item['IDT']}";

            // Проверка, добавлен ли товар в корзину
            $stmtCheckCart = ($category === 'instruments') 
                ? $pdo->prepare("SELECT COUNT(*) FROM Корзина_инструмент WHERE IDK = ? AND IDI = ?") 
                : $pdo->prepare("SELECT COUNT(*) FROM Корзина_товар WHERE IDK = ? AND IDT = ?");
            $stmtCheckCart->execute([$user_id, $item['IDI'] ?? $item['IDT']]);
            $alreadyInCart = $stmtCheckCart->fetchColumn() > 0;

            echo '<div class="col">';
            echo '  <div class="card h-100 shadow-sm">';
            echo "      <a href='$productLink'>";
            echo "          <img src='" . htmlspecialchars($item['image_path']) . "' class='card-img-top' alt='" . htmlspecialchars($item['Название']) . "'>";
            echo "      </a>";
            echo '      <div class="card-body d-flex flex-column justify-content-between">';
            echo "          <h5 class='card-title'>" . htmlspecialchars($item['Название']) . "</h5>";
            echo "          <p class='card-text'>Цена: " . htmlspecialchars($item['Цена']) . " руб.</p>";

            if ($isLoggedIn) {
                if ($alreadyInCart) {
                    echo "<button class='btn btn-secondary mt-2' disabled>Товар уже в корзине</button>";
                } else {
					echo "<button id='addToCartBtn' class='btn btn-success mt-3' data-type='{$category}' data-id='" . ($item['IDI'] ?? $item['IDT']) . "'>Добавить в корзину</button>";
                }
            } else {
                echo "  <button class='btn btn-secondary mt-2' onclick='showLoginAlert()'>Добавить в корзину</button>";
            }

            echo '      </div>';
            echo '  </div>';
            echo '</div>';
        }

        echo '</div>';
    }

    ?>
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
// Скрипт для уведомлений
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
document.querySelectorAll('#addToCartBtn').forEach(button => {
    button.addEventListener('click', function() {
        const type = this.getAttribute('data-type');
        const id = this.getAttribute('data-id');
        
        fetch(`add_to_cart.php?type=${type}&id=${id}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    this.innerText = 'Товар уже в корзине';
                    this.classList.add('btn-secondary');
                    this.classList.remove('btn-success');
                } else {
                    showAlreadyAlert();
                }
            })
            .catch(error => {
                console.error('Ошибка:', error);
            });
    });
});
</script>

