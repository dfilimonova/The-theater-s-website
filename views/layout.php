<?php
session_start();

$host = 'localhost';
$db_name = 'theater';
$name = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $name, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

function getUserRole($pdo, $userId) {
    $query = "SELECT roles.name
              FROM user_roles
              JOIN roles ON user_roles.role_id = roles.id
              WHERE user_roles.user_id = :user_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();

    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result['name'] ?? null;
}

if (isset($_SESSION['user_id'])) {
    $userRole = getUserRole($pdo, $_SESSION['user_id']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Зеркало Истории - Театр</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600&family=Roboto:wght@400&display=swap">
   <style>
    body {
        font-family: 'Roboto', sans-serif;
        margin: 0;
        padding: 0;
        background-image: url('https://avatars.mds.yandex.net/i?id=a10cd979d6349c2cf33b29d6eefafa81_l-3573951-images-thumbs&n=13'); /* Замените на вашу ссылку на фон */
        background-size: cover;
        background-attachment: fixed;
        color: #333;
    }

    nav {
        background-color: #111; /* Убираем прозрачность */
        padding: 15px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .nav-links {
        list-style: none;
        margin: 0;
        padding: 0;
        display: flex;
        justify-content: center; /* Центрирование ссылок */
        flex-grow: 1; /* Занимает доступное пространство */
    }

    .nav-links li {
        margin: 0 15px;
    }

    nav a {
        text-decoration: none;
        color: #fff; /* Цвет текста по умолчанию */
        font-weight: bold;
        padding: 8px 12px;
        border-radius: 5px;
        transition: background-color 0.3s, color 0.3s; /* Добавляем переход для цвета текста */
    }

    nav a:hover {
        background-color: #000; /* Фон при наведении */
        color: #fff; /* Текст белым при наведении */
    }

    .login-button {
        text-decoration: none;
        color: #fff;
        background-color: transparent; /* Прозрачный фон */
        border: 2px solid #e69900; /* Оранжевая рамка */
        padding: 8px 12px;
        border-radius: 5px;
        transition: background-color 0.3s, color 0.3s;
    }

    .login-button:hover {
        background-color: #e69900;
        color: #111;
    }

    .login-container {
        display: flex;
        align-items: center;
        justify-content: flex-end; /* Вход справа */
    }
</style>
</head>
<body>
    <nav>
        <ul class="nav-links">
            <li><a href="/index.php">Главная</a></li>
            <li><a href="/views/session.php">Сеансы</a></li>
            <li><a href="/views/about.php">О нас</a></li>
            <?php if (isset($_SESSION['user_id'])): ?>
                <?php if ($userRole == 'admin'): ?>
                    <li><a href="/views/admin_panel.php">Админка</a></li>
                <?php endif; ?>
                <li><a href="/views/profile.php">Личный кабинет</a></li>
            <?php endif; ?>
        </ul>
        <div class="login-container">
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a class="login-button" href="/views/login.php">Вход</a>
            <?php endif; ?>
        </div>
    </nav>
</body>
</html>