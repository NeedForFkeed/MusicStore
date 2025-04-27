<?php
include 'db.php';
include 'header.php';

$newsId = $_GET['id'] ?? null;

if (!$newsId) {
    echo "<p>Новость не найдена.</p>";
    exit;
}

$stmt = $pdo->prepare("
    SELECT n.*, k.Имя, k.Фамилия 
    FROM новости n
    JOIN сотрудники k ON n.Автор_ID = k.IDC
    WHERE n.ID = ?
");
$stmt->execute([$newsId]);
$news = $stmt->fetch();

if (!$news) {
    echo "<p>Новость не найдена.</p>";
    exit;
}

// Получение изображений из директории
$imageDir = $news['image_path'];
$images = [];
if (is_dir($imageDir)) {
    $files = scandir($imageDir);
    foreach ($files as $file) {
        if (in_array(strtolower(pathinfo($file, PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif'])) {
            $images[] = $imageDir . '/' . $file;
        }
    }
}
?>

<div class="container py-5">
    <h2><?= htmlspecialchars($news['Заголовок']) ?></h2>
    <p><strong>Автор:</strong> <?= htmlspecialchars($news['Фамилия'] . ' ' . $news['Имя']) ?> | 
       <strong>Дата:</strong> <?= date("d.m.Y", strtotime($news['Дата_публикации'])) ?></p>

    <?php if ($images): ?>
        <div id="newsCarousel" class="carousel slide mb-4" data-bs-ride="carousel">
            <div class="carousel-inner">
                <?php foreach ($images as $index => $img): ?>
                    <div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
                        <img src="<?= $img ?>" class="d-block w-100" alt="Изображение">
                    </div>
                <?php endforeach; ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#newsCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#newsCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon"></span>
            </button>
        </div>
    <?php endif; ?>

    <p><?= $news['Содержимое'] ?></p>
</div>

<?php include 'footer.php'; ?>
