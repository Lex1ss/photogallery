<?php
session_start();
require 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Проверка пароля
    if (strlen($password) < 8 || !preg_match('/[a-zA-Z]/', $password)) {
        $error = "Пароль должен быть не менее 8 символов и содержать хотябы 1 латинскую букву.";
    } else {
        $password = password_hash($password, PASSWORD_BCRYPT); // хешируем пароли

        $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)"); // sql подготавливаем и выполняем запрос
        $stmt->execute([$username, $password]);

        $_SESSION['user_id'] = $pdo->lastInsertId();
        $_SESSION['success'] = "Вы успешно зарегестрировались!";
        header('Location: index.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
    <link rel="stylesheet" href="css/style.css">
    <script>
        function validateForm() {
            var password = document.getElementById("password").value;
            if (password.length < 8 || !/[a-zA-Z]/.test(password)) {
                alert("Пароль должен быть не менее 8 символов и содержать хотябы 1 латинскую букву.");
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
    <form method="post" onsubmit="return validateForm()">
        <label for="username">Логин:</label>
        <input type="text" id="username" name="username" required>
        <label for="password">Пароль:</label>
        <input type="password" id="password" name="password" required>
        <button type="submit">Зарегистрироваться</button>
    </form>
</body>
</html>