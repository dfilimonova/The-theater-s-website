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

$user_id = $_SESSION['user_id'];
$user_query = "SELECT username, email FROM users WHERE id = :user_id";
$stmt = $pdo->prepare($user_query);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$user_data = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user_data) {
    header("Location: login.php");
    exit();
}

$username = $user_data['username'];
$user_email = $user_data['email'];

function fetchUserBookings($pdo, $userId) {
    $query = "SELECT bookings.*, sessions.date_time, performances.title as performance_title
              FROM bookings
              JOIN sessions ON bookings.session_id = sessions.id
              JOIN performances ON sessions.performance_id = performances.id
              WHERE bookings.user_id = :user_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function cancelBooking($pdo, $bookingId) {
    $query = "DELETE FROM bookings WHERE id = :booking_id";
    $stmt = $pdo->prepare($query);
    $stmt->bindParam(':booking_id', $bookingId, PDO::PARAM_INT);
    $stmt->execute();
}

$userBookings = fetchUserBookings($pdo, $_SESSION['user_id']);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cancel_booking'])) {
    $bookingIdToCancel = $_POST['cancel_booking'];
    cancelBooking($pdo, $bookingIdToCancel);
    header("Location: profile.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Личный кабинет</title>
<style>
/* profile.css */
body {
    font-family: 'Arial', sans-serif;
    background-color: #f4f4f4;
    margin: 0;
    padding: 0;
}

.container {
    max-width: 800px;
    margin: 20px auto;
    background-color: #fff;
    padding: 30px;
    border-radius: 8px;
    box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
}

h2 {
    color: #333;
    margin-bottom: 35px;
    text-align: center;
}

.booking-section {
    background-color: #f9f9f9;
    padding: 15px;
    margin-bottom: 10px;
    border-radius: 5px;
    border: 1px solid #eee;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.button {
    background-color: #007bff;
    color: white;
    padding: 8px 10px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.button:hover {
    background-color: #0056b3;
}

.alert {
    background-color: #ffcc00;
    padding: 10px;
    border: 1px solid #d5a600;
    border-radius: 5px;
    margin-top: 10px;
    text-align: center;
}
</style>
</head>
<body>
<?php include "layout.php"; ?>
<div class="container">
<h2>Личный кабинет</h2>
<p>Имя: <strong><?= htmlspecialchars($username); ?></strong>!</p>
<p>Email: <strong><?= htmlspecialchars($user_email); ?></strong></p>

<h2>Ваши бронирования</h2>
<?php foreach ($userBookings as $booking) : ?>
    <div class="booking-section">
        <p><?= htmlspecialchars($booking['performance_title']) ?> - <?= htmlspecialchars($booking['date_time']) ?></p>
        
        <?php 
        $bookingTime = new DateTime($booking['created_at']);
        $currentTime = new DateTime();
        $diff = $currentTime->diff($bookingTime);
        $timeLeft = (5 - $diff->i) . ' минут осталось для отмены';
        ?>

        <?php if ($diff->h == 0 && $diff->i < 5) : ?>
            <form action="profile.php" method="post" style="display: inline;">
                <input type="hidden" name="cancel_booking" value="<?= htmlspecialchars($booking['id']) ?>">
                <button type="submit" class="button">Отменить бронь</button>
            </form>
            <div class="alert"><?= $timeLeft ?></div>
        <?php else: ?>
            <div class="alert">Срок отмены брони истек.</div>
        <?php endif; ?>
    </div>
<?php endforeach; ?>
<a href="/methods/logout.php" class="button">Выйти</a>
</div>
</body>
</html>