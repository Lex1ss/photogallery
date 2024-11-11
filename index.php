<?php
session_start();
require 'includes/db.php';

// получаем поисковый запрос из GET-параметров
$search = isset($_GET['search']) ? $_GET['search'] : '';

// подготовка SQL-запроса для поиска фотографий
$stmt = $pdo->prepare("SELECT photos.*, users.username, categories.name AS category_name FROM photos 
                     JOIN users ON photos.user_id = users.id 
                     JOIN categories ON photos.category_id = categories.id 
                     WHERE photos.description LIKE ? OR users.username LIKE ? OR categories.name LIKE ? OR photos.tags LIKE ?
                     ORDER BY photos.id DESC");
$stmt->execute(['%' . $search . '%', '%' . $search . '%', '%' . $search . '%', '%' . $search . '%']);
$photos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Photo Gallery</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <div class="header-left">
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="profile.php">Профиль</a>
                <a href="upload.php">Загрузить фото</a>
                <a href="logout.php">Выйти</a>
            <?php else: ?>
                <a href="login.php">Войти</a>
                <a href="register.php">Зарегестрироваться</a>
            <?php endif; ?>
        </div>
        <form method="get" class="search-form">
            <input type="text" name="search" placeholder="Поиск по тегам, пользователю, описанию, категории" value="<?= htmlspecialchars($search) ?>">
            <button type="submit">Поиск</button>
        </form>
    </header>
    <div class="gallery">
        <?php foreach ($photos as $photo): ?>
            <div class="photo">
                <img src="uploads/<?= $photo['filename'] ?>" alt="<?= $photo['description'] ?>">
                <p>Загружен: <?= $photo['username'] ?></p>
                <p>Категория: <?= $photo['category_name'] ?></p>
                <p>Теги: <?= $photo['tags'] ?></p>
                <p><?= $photo['description'] ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>