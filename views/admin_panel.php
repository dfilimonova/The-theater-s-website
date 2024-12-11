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
$user_id = $_SESSION['user_id'] ?? null;
if ($user_id) {
$check_admin_query = "SELECT * FROM user_roles WHERE user_id = :user_id AND role_id = (SELECT id FROM roles WHERE name = 'admin')";
$stmt = $pdo->prepare($check_admin_query);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();

return $stmt->rowCount() > 0;
}
return false;
}
function fetchPerformances($pdo)
{
return $pdo->query("SELECT * FROM performances")->fetchAll(PDO::FETCH_ASSOC);
}

function fetchSessions($pdo)
{
return $pdo->query("SELECT sessions.*, performances.title as performance_title, halls.name as hall_name 
        FROM sessions 
        JOIN performances ON sessions.performance_id = performances.id
        JOIN halls ON sessions.hall_id = halls.id")->fetchAll(PDO::FETCH_ASSOC);
}
function fetchUsers($pdo)
{
return $pdo->query("SELECT * FROM users")->fetchAll(PDO::FETCH_ASSOC);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_performance'])) {
$title = $_POST['title'];
$description = $_POST['description'];

if (!empty($title) && !empty($description)) {
$insert_performance_query = "INSERT INTO performances (title, description) VALUES (:title, :description)";
$stmt = $pdo->prepare($insert_performance_query);
$stmt->bindParam(':title', $title, PDO::PARAM_STR);
$stmt->bindParam(':description', $description, PDO::PARAM_STR);
$stmt->execute();

header("Location: {$_SERVER['PHP_SELF']}");
exit();
}
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_hall'])) {
$hall_name = $_POST['hall_name'];

$insert_hall_query = "INSERT INTO halls (name) VALUES (:hall_name)";
$stmt = $pdo->prepare($insert_hall_query);
$stmt->bindParam(':hall_name', $hall_name, PDO::PARAM_STR);
$stmt->execute();
header("Location: {$_SERVER['PHP_SELF']}");
exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_session'])) {
$performance_id = $_POST['performance_id'];
$hall_id = $_POST['hall_id'];
$date_time = $_POST['date_time'];

$insert_session_query = "INSERT INTO sessions (performance_id, hall_id, date_time) VALUES (:performance_id, :hall_id, :date_time)";
$stmt = $pdo->prepare($insert_session_query);
$stmt->bindParam(':performance_id', $performance_id, PDO::PARAM_INT);
$stmt->bindParam(':hall_id', $hall_id, PDO::PARAM_INT);
$stmt->bindParam(':date_time', $date_time, PDO::PARAM_STR);
$stmt->execute();
header("Location: {$_SERVER['PHP_SELF']}");
exit();
}
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_performance'])) {
$performance_id = $_POST['performance_id'];

if (!empty($performance_id)) {
try {
$pdo->beginTransaction();

$delete_reviews_query = "DELETE FROM reviews WHERE performance_id = :performance_id";
$stmt_reviews = $pdo->prepare($delete_reviews_query);
$stmt_reviews->bindParam(':performance_id', $performance_id, PDO::PARAM_INT);
$stmt_reviews->execute();

$delete_bookings_query = "DELETE FROM bookings WHERE session_id IN (SELECT id FROM sessions WHERE performance_id = :performance_id)";
$stmt_bookings = $pdo->prepare($delete_bookings_query);
$stmt_bookings->bindParam(':performance_id', $performance_id, PDO::PARAM_INT);
$stmt_bookings->execute();

$delete_sessions_query = "DELETE FROM sessions WHERE performance_id = :performance_id";
$stmt_sessions = $pdo->prepare($delete_sessions_query);
$stmt_sessions->bindParam(':performance_id', $performance_id, PDO::PARAM_INT);
$stmt_sessions->execute();

$delete_performance_query = "DELETE FROM performances WHERE id = :performance_id";
$stmt_performance = $pdo->prepare($delete_performance_query);
$stmt_performance->bindParam(':performance_id', $performance_id, PDO::PARAM_INT);
$stmt_performance->execute();

$pdo->commit();
} catch (PDOException $e) {
$pdo->rollBack();
echo "Ошибка удаления спектакля: " . $e->getMessage();
exit();
}
header("Location: {$_SERVER['PHP_SELF']}");
exit();
}
}




if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_session'])) {
$session_id = $_POST['delete_session'];
if (!empty($session_id)) {
$delete_bookings_query = "DELETE FROM bookings WHERE session_id = :session_id";
$stmt_bookings = $pdo->prepare($delete_bookings_query);
$stmt_bookings->bindParam(':session_id', $session_id, PDO::PARAM_INT);
$stmt_bookings->execute();
$delete_session_query = "DELETE FROM sessions WHERE id = :session_id";
$stmt_session = $pdo->prepare($delete_session_query);
$stmt_session->bindParam(':session_id', $session_id, PDO::PARAM_INT);

try {
$stmt_session->execute();
} catch (PDOException $e) {
echo "Ошибка удаления сеанса: " . $e->getMessage();
exit();
}
header("Location: {$_SERVER['PHP_SELF']}");
exit();
}
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_user'])) {
$user_id = $_POST['delete_user'];
if (!empty($user_id)) {
try {
$pdo->beginTransaction();
$delete_roles_query = "DELETE FROM user_roles WHERE user_id = :user_id";
$stmt_roles = $pdo->prepare($delete_roles_query);
$stmt_roles->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt_roles->execute();
$delete_bookings_query = "DELETE FROM bookings WHERE user_id = :user_id";
$stmt_bookings = $pdo->prepare($delete_bookings_query);
$stmt_bookings->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt_bookings->execute();
$delete_user_query = "DELETE FROM users WHERE id = :user_id";
$stmt_user = $pdo->prepare($delete_user_query);
$stmt_user->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt_user->execute();
$pdo->commit();
} catch (PDOException $e) {
$pdo->rollBack();
echo "Ошибка удаления пользователя: " . $e->getMessage();
exit();
}
header("Location: {$_SERVER['PHP_SELF']}");
exit();
}
}

$performances = fetchPerformances($pdo);
$sessions = fetchSessions($pdo);
$users = fetchUsers($pdo);
$halls = $pdo->query("SELECT * FROM halls")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f7f7f7;
            margin: 0;
            padding: 20px;
            color: #333;
        }

        h1 {
            text-align: center;
            color: #000000;
            margin-bottom: 20px;
        }

        .add-section {
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            margin: 15px 0;
            transition: transform 0.3s ease;
            overflow: hidden;
        }

        .add-section:hover {
            transform: translateY(-5px);
        }

        .add-section h2 {
            margin: 0;
            padding: 15px;
            font-size: 1.5em;
            color: #000000;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #f0e5ff;
        }

        .add-section-content {
            padding: 15px;
            display: none; /* Скрыто по умолчанию */
        }

        .add-section-content.active {
            display: block; /* Показать, когда активное */
        }

        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        input[type="text"],
        input[type="datetime-local"],
        textarea,
        select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 1em;
        }

        button {
            background-color: green;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1em;
            transition: background-color 0.3s ease, transform 0.2s ease;
        }

        button:hover {
            background-color: green;
            transform: scale(1.05);
        }

        .delete-button {
            background-color: #e74c3c;
            margin-left: 10px;
        }

        .delete-button:hover {
            background-color: #c0392b;
        }

        ul {
            list-style-type: none;
            padding: 0;
        }

        li {
            padding: 10px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background-color 0.2s ease;}

        li:hover {
            background-color: #f1f1f1;
        }

        .arrow {
            display: inline-block;
            width: 0;
            height: 0;
            border-left: 5px solid transparent;
            border-right: 5px solid transparent;
            border-bottom: 5px solid #8e44ad;
            transition: transform 0.3s ease;
        }

        .arrow.down {
            transform: rotate(180deg);
        }
    </style>
</head>
<body>
    <?php include "layout.php"; ?>
    <h1>Админ-панель</h1>

    <div class="add-section" onclick="toggleContent('performances')">
        <h2>Добавление спектаклей <span class="arrow" id="arrowPerformances"></span></h2>
        <div class="add-section-content" id="performances">
            <form action="admin_panel.php" method="post">
                <label for="title">Название представления:</label>
                <input type="text" id="title" name="title" required>

                <label for="description">Описание:</label>
                <textarea id="description" name="description" rows="4" required></textarea>

                <button type="submit" name="add_performance">Добавить</button>
            </form>
        </div>
    </div>

    <div class="add-section" onclick="toggleContent('halls')">
        <h2>Добавление залов <span class="arrow" id="arrowHalls"></span></h2>
        <div class="add-section-content" id="halls">
            <form action="admin_panel.php" method="post">
                <label for="hall_name">Название зала:</label>
                <input type="text" id="hall_name" name="hall_name" required>

                <button type="submit" name="add_hall">Добавить</button>
            </form>
        </div>
    </div>

    <div class="add-section" onclick="toggleContent('sessions')">
        <h2>Добавление сеансов <span class="arrow" id="arrowSessions"></span></h2>
        <div class="add-section-content" id="sessions">
            <form action="admin_panel.php" method="post">
                <label for="performance_id">Выберите представление:</label>
                <select id="performance_id" name="performance_id" required>
                    <?php foreach ($performances as $performance) : ?>
                        <option value="<?php echo $performance['id']; ?>"><?php echo $performance['title']; ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="hall_id">Выберите зал:</label>
                <select id="hall_id" name="hall_id" required>
                    <?php foreach ($halls as $hall) : ?>
                        <option value="<?php echo $hall['id']; ?>"><?php echo $hall['name']; ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="date_time">Дата и время:</label>
                <input type="datetime-local" id="date_time" name="date_time" required>

                <button type="submit" name="add_session">Добавить</button>
            </form>
        </div>
    </div>

    <div class="add-section" onclick="toggleContent('performancelist')">
        <h2>Спектакли <span class="arrow" id="arrowPerformancelist"></span></h2>
        <div class="add-section-content" id="performancelist">
            <ul>
                <?php foreach ($performances as $performance) : ?>
                <li>
                    <?php echo $performance['title']; ?>
                    <form action="admin_panel.php" method="post" style="display: inline;">
                        <input type="hidden" name="performance_id" value="<?php echo $performance['id']; ?>">
                        <button type="submit" name="delete_performance" class="delete-button">Удалить</button>
                    </form>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <div class="add-section" onclick="toggleContent('sessionlist')">
        <h2>Сеансы <span class="arrow" id="arrowSessionlist"></span></h2>
        <div class="add-section-content" id="sessionlist">
            <ul>
                <?php foreach ($sessions as $session) : ?>
                <li>
                    <?php echo $session['performance_title'] . ' - ' . $session['hall_name'] . ' - ' . $session['date_time']; ?>
                    <form action="admin_panel.php" method="post" style="display: inline;">
                        <input type="hidden" name="delete_session" value="<?php echo $session['id']; ?>">
                        <button type="submit" class="delete-button">Удалить</button>
                    </form>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <div class="add-section" onclick="toggleContent('users')">
        <h2>Пользователи <span class="arrow" id="arrowUsers"></span></h2>
        <div class="add-section-content" id="users">
            <ul>
                <?php foreach ($users as $user) : ?>
                <li>
                    <a href="/views/user_details.php?user_id=<?= $user['id'] ?>"><?= $user['username'] ?></a>
                    <form action="admin_panel.php" method="post" style="display: inline;">
                        <input type="hidden" name="delete_user" value="<?= $user['id'] ?>">
                        <button type="submit" class="delete-button">Удалить</button>
                    </form>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <script>
        function toggleContent(sectionId) {
            var content = document.getElementById(sectionId);
            var arrow = document.querySelector('#arrow' + sectionId.charAt(0).toUpperCase() + sectionId.slice(1));
            content.classList.toggle('active');
            arrow.classList.toggle('down');
        }
    </script>
</body>
</html>

<script>
document.addEventListener('DOMContentLoaded', function () {
document.querySelectorAll('.add-section').forEach(function (section) {
section.addEventListener('click', function (event) {
if (event.target.classList.contains('arrow')) {
    event.stopPropagation();
    var sectionId = this.id;
    toggleContent(sectionId);
}
});
section.querySelectorAll('.arrow, form, input, button').forEach(function (element) {
element.addEventListener('click', function (event) {
    event.stopPropagation();
});
});
});
});

function toggleContent(sectionId) {
var content = document.getElementById(sectionId);
var arrow = document.getElementById('arrow' + sectionId.charAt(0).toUpperCase() + sectionId.slice(1));

content.classList.toggle('active');
arrow.classList.toggle('down');
}
function toggleSection(sectionId) {
var content = document.getElementById(sectionId);
var arrow = document.getElementById('arrow' + sectionId.charAt(0).toUpperCase() + sectionId.slice(1));

content.classList.toggle('active');
arrow.classList.toggle('down');
}

</script>
</body>
</html>


