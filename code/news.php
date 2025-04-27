<?php
include 'db.php';
include 'header.php';

$stmt = $pdo->query("
    SELECT n.ID, n.Заголовок, n.Дата_публикации, k.Имя, k.Фамилия 
    FROM новости n
    JOIN сотрудники k ON n.Автор_ID = k.IDC
    ORDER BY n.Дата_публикации DESC
");
$newsList = $stmt->fetchAll();
;
?>

<div class="container py-5">
    <h2 class="text-center mb-4">Новости</h2>

    <div class="row">
        <?php foreach ($newsList as $news): ?>
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        <h5 class="card-title"><?= htmlspecialchars($news['Заголовок']) ?></h5>
                        <p class="card-text">
                            Автор: <?= htmlspecialchars($news['Фамилия'] . ' ' . $news['Имя']) ?><br>
                            Дата: <?= date("d.m.Y", strtotime($news['Дата_публикации'])) ?>
                        </p>
                        <a href="news_detail.php?id=<?= $news['ID'] ?>" class="btn btn-primary">Читать полностью</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php include 'footer.php'; ?>
