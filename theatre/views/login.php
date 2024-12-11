<?php
session_start();
if (isset($_SESSION['user_id'])) {
header("Location: profile.php");
exit();
}
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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
$username = $_POST['username'];
$password = $_POST['password'];

try {
    $find_user_query = "SELECT * FROM users WHERE username=:username";
    $stmt = $pdo->prepare($find_user_query);
    $stmt->bindParam(':username', $username, PDO::PARAM_STR);
    $stmt->execute();

if ($stmt->rowCount() == 1) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);
            if (password_verify($password, $user['password_hash'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['user_email'] = $user['email'];
                header("Location: profile.php");
                exit();
            } else {
                $error_message = "Неверный логин или пароль."; // Более точное сообщение
            }
        } else {
            $error_message = "Пользователь с таким именем не найден."; // Отдельное сообщение
        }
    } catch (PDOException $e) {
        die("Ошибка при авторизации: " . $e->getMessage());
    }
}

$pdo = null;
?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Авторизация</title>
<style>
body {
  font-family: sans-serif;
  background-color: #f4f4f4; /* Светло-серый фон */
  margin: 0;
  padding: 0;
}

.container {
  max-width: 500px;
  margin: 100px auto;
  background-color: #fff;
  padding: 30px;
  border-radius: 10px;
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}


h2 {
  text-align: center;
  color: #333;
  margin-bottom: 20px;
  font-weight: bold; /* Усиление жирности */
}

label {
  display: block;
  margin-bottom: 5px;
  color: #555;
  font-weight: normal;
}

input[type="text"],
input[type="password"] {
  width: 100%;
  padding: 10px;
  margin-bottom: 15px;
  border: 1px solid #ddd;
  border-radius: 5px;
  box-sizing: border-box;
  font-size: 16px;
}

.error {
  color: red;
  font-size: 0.9em;
  margin-top: 5px;
  margin-left: 15px;
}

button[type="submit"] {
  background-color: #007bff;
  color: white;
  padding: 12px 15px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  transition: background-color 0.3s ease;
}

button[type="submit"]:hover {
  background-color: #0056b3;
}

</style>
</head>
<body>
    <?php include "layout.php"; ?>
    <div class="container">
        <h2>Авторизация</h2>
        <?php if (isset($error_message)) : ?>
            <p class="error"><?php echo $error_message; ?></p>
        <?php endif; ?>
        <form action="login.php" method="post">
            <label for="username">Имя пользователя:</label>
            <input type="text" id="username" name="username" required>
            <label for="password">Пароль:</label>
            <input type="password" id="password" name="password" required>
            <button type="submit">Войти</button>
        </form>
        <p>Нет аккаунта? <a href="registration.php">Зарегистрируйтесь</a></p> </div>
</body>
</html>
