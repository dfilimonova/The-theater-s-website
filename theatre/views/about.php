<?php
session_start();

// Информация о театре и актерах
$theater_info = [
    'name' => 'Зеркало истории',
    'description' => 'Театр "Зеркало истории" — это уникальное место в сердце города, где каждый найдет вдохновение и радость от встреч с искусством. Наша основная цель — дарить зрителям незабываемые эмоции через театральное искусство. Мы предлагаем множество спектаклей, от классических до современных, которые погружают в атмосферу искусства и культуры.',
    'address' => 'ул. Искусств, д. 12, Москва',
    'phone' => '+7 (495) 123-45-67',
    'email' => 'info@teatrgrez.ru',
    'director' => 'Иван Петрович Смирнов',
    'actors' => [
        [
            'name' => 'Анна Сергеевна Кузнецова',
            'photo' => 'https://i.pinimg.com/736x/25/c9/df/25c9df6ca92913f22f3cb1cc309c97f3.jpg' // Замените на фактический путь к фото
        ],
        [
            'name' => 'Дмитрий Александрович Ильин',
            'photo' => 'https://i.pinimg.com/736x/e2/45/53/e24553633ecd8d05e7fc63d49c2c4429.jpg' // Замените на фактический путь к фото
        ],
        [
            'name' => 'Мария Николаевна Фролова',
            'photo' => 'https://i.pinimg.com/736x/6b/0f/09/6b0f098169fbd03670ba865447ee8e53.jpg' // Замените на фактический путь к фото
        ],
        [
            'name' => 'Сергей Владимирович Лебедев',
            'photo' => 'https://i.pinimg.com/736x/c2/42/86/c24286043bf8026ed669fd76ecd669df.jpg' // Замените на фактический путь к фото
        ],
        [
            'name' => 'Елена Васильевна Чернова',
            'photo' => 'https://i.pinimg.com/736x/d7/75/e2/d775e2eaf6a57cd24611fd4bddf67ab8.jpg' // Замените на фактический путь к фото
        ],
        [
            'name' => 'Артем Игоревич Васильев',
            'photo' => 'https://i.pinimg.com/736x/54/e7/63/54e7630248431696115a4ca2f054196b.jpg' // Замените на фактический путь к фото
        ],
    ]
];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>О нас - <?= htmlspecialchars($theater_info['name']); ?></title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            color: #333;
        }
        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        h1, h2 {
            text-align: center;
margin-bottom: 20px;
        }
        .info {
            margin: 20px 0;
            text-align: justify;
        }
        .contact-info {
            margin-top: 30px;
            padding: 15px;
            background: #e9ecef;
            border-radius: 5px;
        }
        .actors {
            margin-top: 30px;
        }
        .actor-circle {
            display: inline-block;
            margin-left: 120px;
            text-align: center;
        }
        .actor-photo {
            width: 100px; /* Размер круга профиля */
            height: 100px; /* Размер круга профиля */
            border-radius: 50%;
            overflow: hidden;
            border: 2px solid #ccc; /* Граница круга */
margin-left: 50px;
        }
        .actor-photo img {
            width: 100%; /* Адаптация картинки под круг */
            height: auto;
        }
        ul {
            list-style-type: none;
            padding: 0;
        }
        li {
            padding: 5px 0;
        }
    </style>
</head>
<body>

<?php include "layout.php"; ?>

<div class="container">
    <div class="info">
        <h2><?= htmlspecialchars($theater_info['name']); ?></h2>
        <p><?= htmlspecialchars($theater_info['description']); ?></p>
    </div>

    <div class="actors">
        <h2>Наши актеры</h2>
        <div class="actor-list">
            <?php foreach ($theater_info['actors'] as $actor): ?>
                <div class="actor-circle">
                    <div class="actor-photo">
                        <img src="<?= htmlspecialchars($actor['photo']); ?>" alt="Фото <?= htmlspecialchars($actor['name']); ?>">
                    </div>
                    <p><?= htmlspecialchars($actor['name']); ?></p>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="contact-info">
        <h2>Контактная информация</h2>
        <p><strong>Адрес:</strong> <?= htmlspecialchars($theater_info['address']); ?></p>
        <p><strong>Телефон:</strong> <?= htmlspecialchars($theater_info['phone']); ?></p>
        <p><strong>Email:</strong> <a href="mailto:<?= htmlspecialchars($theater_info['email']); ?>"><?= htmlspecialchars($theater_info['email']); ?></a></p>
        <p><strong>Директор театра:</strong> <?= htmlspecialchars($theater_info['director']); ?></p>
    </div>
</div>

</body>
</html>