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
if (!isUserAdmin($pdo)) {
header("Location: login.php");
exit();
}
function isUserAdmin($pdo)
{
return isset($_SESSION['user_id']) && isAdmin($pdo, $_SESSION['user_id']);
}
function isAdmin($pdo, $userId)
{
$query = "SELECT * FROM user_roles WHERE user_id = :user_id AND role_id = (SELECT id FROM roles WHERE name = 'admin')";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
$stmt->execute();

return $stmt->rowCount() > 0;
}
function fetchUserData($pdo, $userId)
{
$query = "SELECT users.*, roles.name as role, 
GROUP_CONCAT(performances.title ORDER BY bookings.id SEPARATOR ', ') as booked_performances,
GROUP_CONCAT(bookings.id ORDER BY bookings.id SEPARATOR ', ') as booked_performances_ids,
GROUP_CONCAT(performances.title ORDER BY bookings.id SEPARATOR ', ') as performance_titles
FROM users
LEFT JOIN user_roles ON users.id = user_roles.user_id
LEFT JOIN roles ON user_roles.role_id = roles.id
LEFT JOIN bookings ON users.id = bookings.user_id
LEFT JOIN sessions ON bookings.session_id = sessions.id
LEFT JOIN performances ON sessions.performance_id = performances.id
WHERE users.id = :user_id
GROUP BY users.id, roles.name";

$stmt = $pdo->prepare($query);
$stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
if (!empty($result['booked_performances_ids'])) {
$bookedPerformancesIds = explode(', ', $result['booked_performances_ids']);
$performanceTitles = explode(', ', $result['performance_titles']);

$bookedPerformancesData = array();
foreach ($bookedPerformancesIds as $index => $bookingId) {
    $bookedPerformancesData[] = array(
        'booking_id' => $bookingId,
        'performance_title' => $performanceTitles[$index]
    );
}

$result['booked_performances_data'] = $bookedPerformancesData;
} else {
// Если бронирований нет, устанавливаем пустой массив
$result['booked_performances_data'] = array();
}

return $result;
}
function updateUserData($pdo, $userId, $username, $email, $roleId)
{
$query = "UPDATE users SET username = :username, email = :email WHERE id = :user_id";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
$stmt->bindParam(':username', $username, PDO::PARAM_STR);
$stmt->bindParam(':email', $email, PDO::PARAM_STR);
$stmt->execute();

// Обновляем роль пользователя
$query = "UPDATE user_roles SET role_id = :role_id WHERE user_id = :user_id";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
$stmt->bindParam(':role_id', $roleId, PDO::PARAM_INT);
$stmt->execute();
}
function deleteUser($pdo, $userId)
{
$query = "DELETE FROM bookings WHERE user_id = :user_id";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
$stmt->execute();


$query = "DELETE FROM users WHERE id = :user_id";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
$stmt->execute();


$query = "DELETE FROM user_roles WHERE user_id = :user_id";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
$stmt->execute();
}
function cancelBooking($pdo, $bookingId)
{
$query = "DELETE FROM bookings WHERE id = :booking_id";
$stmt = $pdo->prepare($query);
$stmt->bindParam(':booking_id', $bookingId, PDO::PARAM_INT);
$stmt->execute();
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
if (isset($_POST['update_user'])) {
$userIdToUpdate = $_POST['update_user'];
$newUsername = $_POST['new_username'];
$newEmail = $_POST['new_email'];
$newRoleId = $_POST['new_role'];

updateUserData($pdo, $userIdToUpdate, $newUsername, $newEmail, $newRoleId);
header("Location: user_details.php?user_id=$userIdToUpdate");
exit();
} elseif (isset($_POST['delete_user'])) {
$userIdToDelete = $_POST['delete_user'];

deleteUser($pdo, $userIdToDelete);
header("Location: admin_panel.php");
exit();
} elseif (isset($_POST['cancel_booking'])) {
$bookingIdToCancel = $_POST['cancel_booking'];

cancelBooking($pdo, $bookingIdToCancel);
header("Location: user_details.php?user_id=$_GET[user_id]");
exit();
}
}
if (isset($_GET['user_id'])) {
$userId = $_GET['user_id'];
$userData = fetchUserData($pdo, $userId);
} else {
header("Location: admin_panel.php");
exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Детали пользователя</title>

</head>
<body>

<style>


body {
font-family: Arial, sans-serif;
background-color: #f4f4f4;
margin: 0;
padding: 0;
}

.container {
max-width: 800px;
margin: 20px auto;
background-color: #fff;
padding: 20px;
box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
}

h1 {
color: #333;
}

h2 {
color: #555;
}

form {
margin-top: 20px;
}

label {
display: block;
margin-bottom: 8px;
color: #777;
}

input, select {
width: 100%;
padding: 10px;
margin-bottom: 15px;
box-sizing: border-box;
}

button {
background-color: #8e44ad;
color: #fff;
padding: 10px 15px;
border: none;
cursor: pointer;
}

button:hover {
background-color: #6c3483;
}

</style>
<?php include "layout.php";?>
<h1>Детали пользователя</h1>

<h2>Информация о пользователе</h2>
<p>Имя: <?= $userData['username'] ?></p>
<p>Почта: <?= $userData['email'] ?></p>
<p>Роль: <?= $userData['role'] ?></p>

<h2>Бронирования пользователя</h2>
<?php if (!empty($userData['booked_performances'])) : ?>
<ul>
<?php foreach (explode(', ', $userData['booked_performances']) as $performance) : ?>
    <li><?= $performance ?></li>
<?php endforeach; ?>
</ul>
<?php else : ?>
<p>Пользователь не сделал бронирований.</p>
<?php endif; ?>

<h2>Управление пользователем</h2>
<form action="user_details.php?user_id=<?= $userId ?>" method="post">
<label for="new_username">Новое имя:</label>
<input type="text" id="new_username" name="new_username" value="<?= $userData['username'] ?>" required>
<br>
<label for="new_email">Новая почта:</label>
<input type="email" id="new_email" name="new_email" value="<?= $userData['email'] ?>" required>
<br>
<label for="new_role">Новая роль:</label>
<select id="new_role" name="new_role" required>
<option value="1" <?= ($userData['role'] == 'Пользователь') ? 'selected' : '' ?>>Пользователь</option>
<option value="2" <?= ($userData['role'] == 'Администратор') ? 'selected' : '' ?>>Администратор</option>
</select>
<br>
<button type="submit" name="update_user" value="<?= $userId ?>">Обновить данные</button>
</form>

<form action="user_details.php" method="post" onsubmit="return confirm('Вы уверены, что хотите удалить этого пользователя?');">
<button type="submit" name="delete_user" value="<?= $userId ?>">Удалить пользователя</button>
</form>


<h2>Отмена бронирования</h2>
<form action="user_details.php?user_id=<?= $userId ?>" method="post">
<label for="cancel_booking">Выберите бронь для отмены:</label>
<select id="cancel_booking" name="cancel_booking" required>
<?php foreach ($userData['booked_performances_data'] as $booking) : ?>
    <option value="<?= $booking['booking_id'] ?>">
        <?= $booking['performance_title'] ?>
    </option>
<?php endforeach; ?>
</select>
<br>
<button type="submit">Отменить бронь</button>
</form>
</body>
</html>
