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

function fetchPerformancesWithSessions($pdo) {
    $performances = $pdo->query("SELECT * FROM performances")->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($performances as &$performance) {
        $performance['sessions'] = fetchSessionsForPerformance($pdo, $performance['id']);
    }
    
    return $performances;
}

function fetchSessionsForPerformance($pdo, $performanceId) {
    $sessions = $pdo->prepare("
        SELECT s.*, h.name AS hall_name 
        FROM sessions s 
        JOIN halls h ON s.hall_id = h.id 
        WHERE s.performance_id = :performance_id 
        ORDER BY s.date_time
    ");
    $sessions->bindParam(':performance_id', $performanceId, PDO::PARAM_INT);
    $sessions->execute();
    
    return $sessions->fetchAll(PDO::FETCH_ASSOC);
}

function isUserLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isSessionBooked($pdo, $userId, $sessionId) {
    $check_booking_query = "SELECT * FROM bookings WHERE user_id = :user_id AND session_id = :session_id";
    $stmt = $pdo->prepare($check_booking_query);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindParam(':session_id', $sessionId, PDO::PARAM_INT);
    $stmt->execute();
    
    return $stmt->rowCount() > 0;
}

function bookSession($pdo, $userId, $sessionId) {
    $insert_booking_query = "INSERT INTO bookings (user_id, session_id) VALUES (:user_id, :session_id)";
    $stmt = $pdo->prepare($insert_booking_query);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindParam(':session_id', $sessionId, PDO::PARAM_INT);
    $stmt->execute();
}

function cancelBooking($pdo, $userId, $sessionId) {
    $delete_booking_query = "DELETE FROM bookings WHERE user_id = :user_id AND session_id = :session_id";
    $stmt = $pdo->prepare($delete_booking_query);
    $stmt->bindParam(':user_id', $userId, PDO::PARAM_INT);
    $stmt->bindParam(':session_id', $sessionId, PDO::PARAM_INT);
    $stmt->execute();
}

$performancesWithSessions = fetchPerformancesWithSessions($pdo);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['book_session']) && isUserLoggedIn()) {
    $sessionIdToBook = $_POST['book_session'];
    bookSession($pdo, $_SESSION['user_id'], $sessionIdToBook);
    header("Location: session.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['cancel_booking']) && isUserLoggedIn()) {
    $sessionIdToCancel = $_POST['cancel_booking'];
    cancelBooking($pdo, $_SESSION['user_id'], $sessionIdToCancel);
    header("Location: session.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Главная страница</title>
    <style>
        /* Ваши стили здесь */
        body { font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 20px; }
        header { text-align: center; margin-bottom: 20px; padding: 10px; background-color: #007BFF; color: white; border-radius: 5px; }
        .performance-section { margin: 50px 80px; background-color: white; border: 1px solid #ccc; border-radius: 5px; padding: 30px; transition: box-shadow 0.3s ease; cursor: pointer; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); }
        .performance-section:hover { box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2); }
        h2 { margin: 0; font-size: 1.5em; display: flex; justify-content: space-between; align-items: center; }
        .arrow { width: 10px; height: 10px; border-left: 2px solid #333; border-bottom: 2px solid #333; transform: rotate(45deg); transition: transform 0.3s ease; }
        .down { transform: rotate(225deg); }
        .performance-content { display: none; margin-top: 10px; padding: 10px; border-top: 1px solid #ccc; }
        .performance-content.active { display: block; }
        .session-section { display: flex; justify-content: space-between; align-items: center; margin: 10px 0; padding: 10px; background-color: #f9f9f9; border-radius: 5px; }
        .session-date { display: flex; align-items: center; font-weight: bold; color: #333; }
        .session-date span { margin-right: 10px; background-color: #e7f1ff; padding: 5px; border-radius: 5px; }
        .action-container { display: flex; align-items: center; }
        .action-container a, .action-container form { margin-left: 10px; }
        .booking-button { background-color: #28a745; color: white; border: none; border-radius: 5px; padding: 5px 10px; cursor: pointer; transition: background-color 0.3s ease; box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); }
        .booking-button:hover { background-color: #218838; box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2); }
    </style>
</head>
<body>
    <?php include "layout.php"; ?>
  
    <?php foreach ($performancesWithSessions as $performance) : ?>
        <div class="performance-section" onclick="toggleContent('performance<?= $performance['id'] ?>')">
            <h2><?= $performance['title'] ?> <span class="arrow" id="arrowPerformance<?= $performance['id'] ?>"></span></h2>
            <div class="performance-content" id="performance<?= $performance['id'] ?>">
                <?php foreach ($performance['sessions'] as $session) : ?>
                    <div class="session-section">
                        <div class="session-date">
                            <span><?= date('d.m.Y', strtotime($session['date_time'])) ?></span>
                            <span><?= date('H:i', strtotime($session['date_time'])) ?></span>
                            <span>(<?= $session['hall_name'] ?>)</span>
                        </div>
                        <div class="action-container">
                            <a href="/views/session_details.php?session_id=<?= $session['id']; ?>">Подробнее</a>
                            <?php if (isUserLoggedIn()) : ?>
                                <?php if (!isSessionBooked($pdo, $_SESSION['user_id'], $session['id'])) : ?>
                                    <form action="session.php" method="post" style="display:inline;">
                                        <input type="hidden" name="book_session" value="<?= $session['id'] ?>">
                                        <button type="submit" class="booking-button">Забронировать</button>
                                    </form>                        
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endforeach; ?>

    <script>
        function toggleContent(sectionId) {
            var content = document.getElementById(sectionId);
            var arrow = document.getElementById('arrow' + sectionId.charAt(0).toUpperCase() + sectionId.slice(1));
            content.classList.toggle('active');
            arrow.classList.toggle('down');
        }
    </script>
</body>
</html>