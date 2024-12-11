<?php
session_start();
$host = 'localhost';
$db_name = 'theater';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['profile_picture'])) {
    $file = $_FILES['profile_picture'];
    $uploadDirectory = 'uploads/'; // Директория для загрузки
    $filePath = $uploadDirectory . basename($file['name']);

    // Проверка на ошибки
    if ($file['error'] === UPLOAD_ERR_OK) {
        move_uploaded_file($file['tmp_name'], $filePath);

        // Обновление пути в БД
        $user_id = $_SESSION['user_id'];
        $updateQuery = "UPDATE users SET profile_picture = :profile_picture WHERE id = :user_id";
        $stmt = $pdo->prepare($updateQuery);
        $stmt->bindParam(':profile_picture', $filePath);
        $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();

        header("Location: profile.php");
        exit();
    } else {
        die("Ошибка загрузки файла.");
    }
}
?>