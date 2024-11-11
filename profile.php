<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id'])) { // проверяем на авторизацию
    header('Location: login.php');
    exit;
}

$user_id = $_SESSION['user_id']; // получаем ID пользователя из сессии

// проверяем была ли отправлена форма для удаления фотографии
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete'])) {
    $photo_id = $_POST['delete'];
    $stmt = $pdo->prepare("SELECT filename FROM photos WHERE id = ? AND user_id = ?");
    $stmt->execute([$photo_id, $user_id]);
    $photo = $stmt->fetch();

    if ($photo) {
        $stmt = $pdo->prepare("DELETE FROM photos WHERE id = ? AND user_id = ?");
        $stmt->execute([$photo_id, $user_id]);
        unlink("uploads/" . $photo['filename']);
    }
}

// проверяем была ли отправлена форма для редактирования фотографии
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['edit'])) {
    $photo_id = $_POST['edit'];
    $description = $_POST['description'];
    $tags = $_POST['tags'];
    $stmt = $pdo->prepare("UPDATE photos SET description = ?, tags = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$description, $tags, $photo_id, $user_id]);
}

// подготовка SQL-запрос для получения фотографий пользователя
$stmt = $pdo->prepare("SELECT photos.*, categories.name AS category_name FROM photos 
                     JOIN categories ON photos.category_id = categories.id 
                     WHERE photos.user_id = ? 
                     ORDER BY photos.id DESC");
$stmt->execute([$user_id]);
$photos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Profile</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
        <div>
            <a href="index.php">Назад в галерею</a>
        </div>
    </header>
    <h1></h1>
    <div class="gallery">
        <?php foreach ($photos as $photo): ?>
            <div class="photo">
                <img src="uploads/<?= $photo['filename'] ?>" alt="<?= $photo['description'] ?>">
                <p>Категория: <?= $photo['category_name'] ?></p>
                <p>Теги: <?= $photo['tags'] ?></p>
                <p><?= $photo['description'] ?></p>
				<button class="edit" onclick="editPhoto(<?= $photo['id'] ?>, '<?= $photo['description'] ?>', '<?= $photo['tags'] ?>')">Редактировать</button>
                <form method="post">
					<input type="hidden" name="filename" value="<?= $photo['filename'] ?>">
						<div class="button-container">
							<button class="delete-button" type="submit" name="delete" value="<?= $photo['id'] ?>">Удалить</button>
						</div>
		</form>
            </div>
        <?php endforeach; ?>
    </div>

    <div id="editModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <form method="post" id="editForm">
                <input type="hidden" id="editPhotoId" name="edit">
                <label for="editDescription">Описание:</label>
                <textarea id="editDescription" name="description"></textarea>
                <label for="editTags">Теги:</label>
                <input type="text" id="editTags" name="tags">
                <button type="submit">Сохранить</button>
            </form>
        </div>
    </div>

    <script>
        function editPhoto(id, description, tags) {
            document.getElementById('editPhotoId').value = id;
            document.getElementById('editDescription').value = description;
            document.getElementById('editTags').value = tags;
            document.getElementById('editModal').style.display = 'block';
        }

        function closeModal() {
            document.getElementById('editModal').style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target == document.getElementById('editModal')) {
                closeModal();
            }
        }
    </script>
</body>
</html>