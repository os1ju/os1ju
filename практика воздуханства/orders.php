<?php
require_once 'src/config/database.php';
require_once 'src/helpers/escape.php';
require_once 'src/helpers/session.php';

requireAuth();

$userId = $_SESSION['user_id'];

// Получаем ВСЕ заказы пользователя
$sql = "SELECT o.*, s.service_name, s.price as service_price, st.status_name 
        FROM Orders o
        JOIN Services s ON o.service_id = s.service_id
        JOIN Statuses st ON o.status_id = st.status_id
        WHERE o.user_id = ?
        ORDER BY o.created_at DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute([$userId]);
$orders = $stmt->fetchAll();

// Статистика по статусам
$statusStats = [];
foreach ($orders as $order) {
    $statusStats[$order['status_name']] = ($statusStats[$order['status_name']] ?? 0) + 1;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мои заказы | ДомФото</title>
    <link rel="stylesheet" href="profile.css">
    <style>
        .orders-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .orders-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        .orders-header h1 {
            font-size: 36px;
            color: white;
        }
        .back-link {
            color: #960303;
            text-decoration: none;
            font-size: 16px;
        }
        .stats-bar {
            display: flex;
            gap: 20px;
            margin-bottom: 40px;
            flex-wrap: wrap;
        }
        .stat-item {
            background: #2a2a2a;
            padding: 15px 25px;
            border-radius: 10px;
            text-align: center;
        }
        .stat-count {
            font-size: 28px;
            font-weight: bold;
            color: #960303;
        }
        .stat-label {
            font-size: 12px;
            color: #888;
            margin-top: 5px;
        }
        .orders-list {
            background: #2a2a2a;
            border-radius: 10px;
            overflow: hidden;
        }
        .order-item {
            border-bottom: 1px solid #444;
            padding: 20px;
            transition: 0.2s;
        }
        .order-item:hover {
            background: #333;
        }
        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .order-id {
            font-weight: bold;
            color: #960303;
        }
        .order-status {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
        }
        .status-новая { background: rgb(243, 33, 33); }
        .status-подтверждена { background: #4CAF50; }
        .status-в-процессе { background: #FF9800; }
        .status-завершена { background: #9E9E9E; }
        .status-отменена { background: #f44336; }
        .order-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 15px;
            font-size: 14px;
            color: #ccc;
        }
        .order-details p {
            margin: 5px 0;
        }
        .empty-orders {
            text-align: center;
            padding: 60px;
            background: #2a2a2a;
            border-radius: 10px;
        }
        .empty-orders h3 {
            font-size: 24px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body class="body">
    <header>
        <div class="logo">
            <img src="img/logo.png" alt="logo">
            <span>ДомФото</span>
        </div>
        <nav>
            <a href="photographers.php">фотографы</a>
            <a href="profile.php">личный кабинет</a>
            <a href="main.php">главная</a>
        </nav>
    </header>

    <div class="orders-container">
        <div class="orders-header">
            <h1>Мои заказы</h1>
            <a href="profile.php" class="back-link">← Вернуться в профиль</a>
        </div>

        <?php if (!empty($orders)): ?>
            <div class="stats-bar">
                <div class="stat-item">
                    <div class="stat-count"><?= count($orders) ?></div>
                    <div class="stat-label">Всего заказов</div>
                </div>
                <?php foreach ($statusStats as $status => $count): ?>
                    <div class="stat-item">
                        <div class="stat-count"><?= $count ?></div>
                        <div class="stat-label"><?= $status ?></div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="orders-list">
                <?php foreach ($orders as $order): ?>
                    <div class="order-item">
                        <div class="order-header">
                            <span class="order-id">Заказ #<?= $order['order_id'] ?></span>
                            <span class="order-status status-<?= strtolower($order['status_name']) ?>"><?= e($order['status_name']) ?></span>
                        </div>
                        <h3 style="margin: 10px 0;"><?= e($order['service_name']) ?></h3>
                        <div class="order-details">
                            <div>
                                <p><strong>Дата создания:</strong> <?= date('d.m.Y', strtotime($order['created_at'])) ?></p>
                                <p><strong>Время создания:</strong> <?= date('H:i', strtotime($order['created_at'])) ?></p>
                            </div>
                            <div>
                                <p><strong>Дата съемки:</strong> <?= e($order['booking_date'] ?? 'не указана') ?></p>
                                <p><strong>Время съемки:</strong> <?= e($order['booking_time'] ?? 'не указано') ?></p>
                            </div>
                            <div>
                                <p><strong>Стоимость:</strong> <?= e(number_format($order['total_price'] ?? $order['service_price'], 0, ',', ' ')) ?> руб</p>
                            </div>
                        </div>
                        <?php if ($order['notes']): ?>
                            <p style="margin-top: 10px; font-size: 12px; color: #888;"><strong>Примечание:</strong> <?= e($order['notes']) ?></p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-orders">
                <h3>📋 У вас пока нет заказов</h3>
                <p>Выберите понравившуюся услугу и запишитесь на фотосессию!</p>
                <button class="btn" onclick="window.location.href='main.php'" style="margin-top: 20px;">Перейти в каталог</button>
            </div>
        <?php endif; ?>
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
</body>
</html>