<?php
require_once 'C:\Users\polyteh\Documents\практика воздуханства\src\config\database.php';
require_once 'C:\Users\polyteh\Documents\практика воздуханства\src\helpers\session.php';
require_once 'C:\Users\polyteh\Documents\практика воздуханства\src\helpers\escape.php';


// Получаем ID услуги из URL
$serviceId = $_GET['id'] ?? 0;

// Получаем данные услуги
$sql = "SELECT s.*, c.category_name, p.name as photographer_name, p.phone as photographer_phone
        FROM Services s
        LEFT JOIN Categories c ON s.category_id = c.category_id
        LEFT JOIN Photographers p ON s.photographer_id = p.photographer_id
        WHERE s.service_id = ? AND s.is_active = 1";
$stmt = $pdo->prepare($sql);
$stmt->execute([$serviceId]);
$service = $stmt->fetch();

// Если услуга не найдена, перенаправляем на главную
if (!$service) {
    header('Location: main.php');
    exit;
}

$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($service['service_name']) ?> | ДомФото</title>
    <link rel="stylesheet" href="photo.css">
</head>
<body class="body">
    <header>
        <div class="logo">
            <img src="img/logo.png" alt="logo">
            <span>ДомФото</span>
        </div>
        <nav>
            <a href="photographers.html">фотографы</a>
            <a href="profile.php">личный кабинет</a>
            <?php if (!$currentUser): ?>
                <a href="authorization.php">вход</a>
            <?php endif; ?>
        </nav>
    </header>

    <p class="title"><?= e($service['service_name']) ?></p>
    
    <section class="info">
        <img class="img" src="<?= e($service['image_url'] ?? 'img/card_photo.png') ?>" alt="тут банер">
        <p class="text"><?= e($service['description']) ?></p>
        <?php if ($service['photographer_name']): ?>
            <p class="photographer-info"><strong>Фотограф:</strong> <?= e($service['photographer_name']) ?></p>
        <?php endif; ?>
        <button class="btn" onclick="window.location.href='form.php?id=<?= $service['service_id'] ?>'">Записаться</button>
        <p class="cost"><?= e(number_format($service['price'], 0, ',', ' ')) ?> руб/час</p>
    </section>

    <p class="small_title">Примеры фото</p>

    <div class="slider">
        <div class="slider-track">
            <div class="slide"><img src="img/photo_1.png" alt=""></div>
            <div class="slide"><img src="img/photo_2.png" alt=""></div>
            <div class="slide"><img src="img/photo_3.png" alt=""></div>
            <div class="slide"><img src="img/photo_5.png" alt=""></div>
            <div class="slide"><img src="img/photo_6.png" alt=""></div>
            <div class="slide"><img src="img/photo_7.png" alt=""></div>
        </div>
    </div>

    <footer>
        <div>
            <h2>наши контакты</h2>
            <p>+7 996 670 65 65</p>
            <p>PHOTOs@gmail.com</p>
            <p>Прокофьева ул. д 12</p>
        </div>
        <div class="vk">
            <h2>группа vk</h2>
            <img src="img/vk.png" alt="QR">
        </div>
    </footer>
    
    <script src="photo.js"></script>
</body>
</html>