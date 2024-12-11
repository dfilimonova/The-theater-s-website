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
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Театр</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            overflow: hidden; /* Убираем прокрутку */
            background-color: #000000;
        }

        header {
            background: #333;
            color: #fff;
            padding: 20px;
            text-align: center; /* Центрируем текст в шапке */
        }

        .slider {
            position: relative;
            width: 100vw; /* Ширина на весь экран */
            height: 100vh; /* Высота на весь экран */
            overflow: hidden;
        }

        .slides {
            display: flex;
            transition: transform 0.5s ease;
            height: 100%; /* Убедитесь, что высота заполнена */
        }

        .slide {
            min-width: 100vw; /* Ширина на весь экран */
            height: 100vh; /* Высота на весь экран */
            box-sizing: border-box;
            position: relative; /* Позиционируем для абсолютного позиционирования текста */
        }

        .slide img {
            width: 100%;
            height: 100%; /* Картинка занимает полную высоту */
            object-fit: cover; /* Картинка заполняет контейнер */
        }

       .info {
    position: absolute;
    padding: 20px;
    color: #fff;
    border-radius: 8px;
    background: rgba(0, 0, 0, 0.6);
}

.info-1 {
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    text-align: center;
    font-size: 24px; /* Пример */
    padding: 30px 60px; /* Пример */
}

.info-2 {
    top: 20%;
    right: 10%;
    text-align: right;
    font-size: 18px; /* Пример */
    padding: 15px 30px; /* Пример */
}

.info-3 {
    bottom: 10%;
    left: 20%;
    text-align: left;
    font-size: 28px; /* Пример */
    padding: 30px 40px; /* Пример */
}

        /* Центрируем текст для первого слайда */
        .slide:nth-child(1) .info {
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%); /* Центрируем по горизонтали и вертикали */
            text-align: center; /* Центрируем текст */
        }

        /* Для второго слайда: текст слева по центру */
        .slide:nth-child(2) .info {
            top: 50%;
            left: 10%; /* Отступ слева */
            transform: translateY(-50%); /* Центрируем по вертикали */
            text-align: left; /* Текст слева */
        }

        /* Для третьего слайда: текст справа снизу */
        .slide:nth-child(3) .info {
            bottom: 30px; /* Отступ от нижнего края */
            right: 10%; /* Отступ справа */
            transform: translateY(0); /* Не сдвигаем */
            text-align: right; /* Текст вправо */
        }

        button {
            background: rgba(255, 255, 255, 0.8);
            border: none;
            cursor: pointer;
            padding: 10px;
            border-radius: 5px;
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            z-index: 1;
        }

        .prev {
            left: 10px;
        }

        .next {
            right: 10px;
        }

        button:hover {
            background: rgba(255, 255, 255, 1);
        }
    </style>
</head>
<body>
<?php include "views/layout.php"; ?>
<main>
    <div class="slider">
        <div class="slides">
            <div class="slide">
                <img src="https://static.tildacdn.com/tild3264-6638-4137-b363-626631323635/DSCF2116_1.jpg" alt="Слайд 1">
                <div class="info info-1">
                    <h2>Зеркало истории</h2>
                    <p>В каждом спектакле — частичка жизни!</p>
                </div>
            </div>
            <div class="slide">
                <img src="https://i.timeout.ru/pix/517360.jpeg" alt="Слайд 2">
                <div class="info info-2">
                    <h2>Сон в летнюю ночь</h2>
                    <p>Эта комедия перенесет вас в мир, где любовь и волшебство пересекаются...</p>
                </div>
            </div>
            <div class="slide">
                <img src="https://cdn.pbilet.com/origin/d444120a-8cfd-414e-ac69-1f83bada01fb.jpeg" alt="Слайд 3">
                <div class="info info-3">
                    <h2>Трагедия любви</h2>
                    <p>Наши спектакли погружают в бездну эмоций и чувств.</p>
                </div>
            </div>
        </div>
        <button class="prev" onclick="changeSlide(-1)">&#10094;</button>
        <button class="next" onclick="changeSlide(1)">&#10095;</button>
    </div>
</main>
<script>
let currentSlide = 0;

function changeSlide(direction) {
    const slides = document.querySelectorAll('.slide');
    const totalSlides = slides.length;

    // Удаляем класс .active у текущего слайда (только если он был)
    slides[currentSlide].classList.remove('active');

    // Изменяем индекс текущего слайда
    currentSlide = (currentSlide + direction + totalSlides) % totalSlides;

    // Добавляем класс .active на следующий слайд
    slides[currentSlide].classList.add('active');

    // Обновляем слайды
    updateSlides();
}

function updateSlides() {
    const slides = document.querySelector('.slides');
    const offset = currentSlide * -100; // Двигаем на 100% влево для нового слайда
    slides.style.transform = `translateX(${offset}%)`;
}

// Автоматическая смена слайдов
setInterval(() => changeSlide(1), 5000);
</script>
</body>
</html>