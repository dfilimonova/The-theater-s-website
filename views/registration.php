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

$error_messages = []; // Массив для хранения сообщений об ошибках

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    try {
        $check_username_query = "SELECT * FROM users WHERE username=:username";
        $stmt = $pdo->prepare($check_username_query);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $error_messages[] = "Логин уже занят.";
        }

        $check_email_query = "SELECT * FROM users WHERE email=:email";
        $stmt = $pdo->prepare($check_email_query);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            $error_messages[] = "Адрес почты уже используется.";
        }

        // Если есть ошибки, останавливаем процесс
        if (!empty($error_messages)) {
            // Не добавляем пользователя в базу данных
            // Мы просто выводим ошибки на экране
        } else {
            $insert_user_query = "INSERT INTO users (username, email, password_hash) VALUES (:username, :email, :password)";
            $stmt = $pdo->prepare($insert_user_query);
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':password', $password, PDO::PARAM_STR);
            $stmt->execute();

            $user_id = $pdo->lastInsertId();

            $insert_user_role_query = "INSERT INTO user_roles (user_id, role_id) VALUES (:user_id, 1)";
            $stmt = $pdo->prepare($insert_user_role_query);
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();

            header("Location: login.php");
            exit();
        }
    } catch (PDOException $e) {
        $error_messages[] = "Ошибка при регистрации: " . $e->getMessage();
    }
}

$pdo = null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Регистрация</title>
<style>
body {
  font-family: sans-serif;
  background-color: #f4f4f4; /* Светло-серый фон */
  margin: 0;
  padding: 0;
}

.container {
  max-width: 600px;
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
}

label {
  display: block;
  margin-bottom: 5px;
  color: #555;
}

input[type="text"],
input[type="email"],
input[type="password"] {
  width: 100%;
  padding: 10px;
  margin-bottom: 15px;
  border: 1px solid #ccc;
  border-radius: 5px;
  box-sizing: border-box;
}

.error {
  color: red;
  font-size: 0.9em;
  margin-top: 5px;
}

button[type="submit"] {
  background-color: #007bff; /* Синий цвет кнопки */
  color: white;
  padding: 10px 15px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  transition: background-color 0.3s ease; /* Добавлена анимация */
}

button[type="submit"]:hover {
  background-color: #0056b3; /* Темно-синий при наведении */
}
</style>
</head>
<body>
<?php include "layout.php" ?>
<div class="container">
<h2>Регистрация</h2>
<?php if (!empty($error_messages)): ?>
    <div class="error">
        <?php foreach ($error_messages as $message): ?>
            <p><?= htmlspecialchars($message) ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<form action="registration.php" method="post" onsubmit="return validateForm()">
    <label for="username">Логин:</label>
    <input type="text" id="username" name="username" required>
    <p id="usernameError" class="error"></p>
    <label for="email">Email:</label>
    <input type="email" id="email" name="email" required>
    <label for="password">Пароль:</label>
    <input type="password" id="password" name="password" required>
    <p id="passwordError" class="error"></p>
    <button type="submit">Зарегистрироваться</button>
</form>
</div>
<script>
function validateForm() {
    var username = document.getElementById('username').value;
    var password = document.getElementById('password').value;

    var usernameRegex = /^[a-zA-Z0-9_]{3,16}$/;
    if (!usernameRegex.test(username)) {
        document.getElementById('usernameError').innerText = 'Логин должн содержать от 3 до 16 символов: буквы, цифры, знаки подчеркивания';
        return false;
    } else {
        document.getElementById('usernameError').innerText = '';
    }

    var passwordRegex = /^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$/;
    if (!passwordRegex.test(password)) {
        document.getElementById('passwordError').innerText = 'Пароль должен содержать минимум 8 символов, хотя бы одну цифру и одну букву';
        return false;
    } else {
        document.getElementById('passwordError').innerText = '';
    }

    return true;
}
</script>
</body>
</html>