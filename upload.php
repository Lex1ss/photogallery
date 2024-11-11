<?php
session_start();
require 'includes/db.php';

if (!isset($_SESSION['user_id'])) { // проверяем на авторизацию
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {   // проверка была ли отправлена форма
    $user_id = $_SESSION['user_id'];
    $category_id = $_POST['category_id'];
    $description = $_POST['description'];
    $tags = $_POST['tags'];

    $target_dir = "uploads/"; // директория для загрузки файлов (хранение)
    $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) { // перемещение файла в директорию
        $stmt = $pdo->prepare("INSERT INTO photos (user_id, category_id, filename, description, tags) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$user_id, $category_id, basename($target_file), $description, $tags]);
        header('Location: index.php');
        exit;
    } else {
        $error = "Извините, произошла ошибка при загрузке вашего файла.";
    }
}

$stmt = $pdo->query("SELECT * FROM categories");
$categories = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Upload Photo</title>
    <link rel="stylesheet" href="css/style.css">
    <script>
        function validateForm() {
            var fileInput = document.getElementById("fileToUpload");
            var filePath = fileInput.value;
            var allowedExtensions = /(\.jpg|\.jpeg|\.png|\.gif)$/i;
            if (!allowedExtensions.exec(filePath)) {
                alert('Пожалуйста, загрузите файл с расширениями .jpeg/.jpg/.png/.gif.');
                fileInput.value = '';
                return false;
            }
            return true;
        }
    </script>
</head>
<body>
    <header>
        <div>
            <a href="index.php">Назад в галерею</a>
        </div>
    </header>
	<h1></h1>
    <?php if (isset($error)) echo "<p class='error'>$error</p>"; ?>
    <form method="post" enctype="multipart/form-data" onsubmit="return validateForm()">
        <label for="category_id">Категория:</label>
        <select id="category_id" name="category_id" required>
            <?php foreach ($categories as $category): ?>
                <option value="<?= $category['id'] ?>"><?= $category['name'] ?></option>
            <?php endforeach; ?>
        </select>
        <label for="description">Описание:</label>
        <textarea id="description" name="description"></textarea>
        <label for="tags">Теги:</label>
        <input type="text" id="tags" name="tags">
        <label for="fileToUpload">Выберите фото:</label>
        <input type="file" id="fileToUpload" name="fileToUpload" required>
        <button type="submit">Загрузить</button>
    </form>
</body>
</html>