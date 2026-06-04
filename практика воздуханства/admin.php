<?php
require_once 'C:\Users\polyteh\Documents\практика воздуханства\src\config\database.php';
require_once 'C:\Users\polyteh\Documents\практика воздуханства\src\helpers\session.php';
require_once 'C:\Users\polyteh\Documents\практика воздуханства\src\helpers\escape.php';

// Требуем права администратора
requireAdmin();

// Получение статистики
$stats = [];

// Количество пользователей
$stmt = $pdo->query("SELECT COUNT(*) FROM Users");
$stats['users'] = $stmt->fetchColumn();

// Количество заказов
$stmt = $pdo->query("SELECT COUNT(*) FROM Orders");
$stats['orders'] = $stmt->fetchColumn();

// Количество услуг
$stmt = $pdo->query("SELECT COUNT(*) FROM Services WHERE is_active = 1");
$stats['services'] = $stmt->fetchColumn();

// Последние заказы
$sql = "SELECT o.*, u.username, s.service_name, st.status_name 
        FROM Orders o
        JOIN Users u ON o.user_id = u.user_id
        JOIN Services s ON o.service_id = s.service_id
        JOIN Statuses st ON o.status_id = st.status_id
        ORDER BY o.created_at DESC
        LIMIT 10";
$recentOrders = $pdo->query($sql)->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-панель | ДомФото</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            background: #1a1a1a;
            color: #fff;
        }
        header {
            background: #2a2a2a;
            padding: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #960303;
        }
        nav a {
            color: #fff;
            text-decoration: none;
            margin-left: 20px;
        }
        nav a:hover {
            color: #960303;
        }
        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        .stat-card {
            background: #2a2a2a;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
        }
        .stat-card h3 {
            font-size: 14px;
            color: #888;
            margin-bottom: 10px;
        }
        .stat-card .number {
            font-size: 36px;
            font-weight: bold;
            color: #960303;
        }
        .orders-table {
            background: #2a2a2a;
            border-radius: 10px;
            overflow: hidden;
        }
        .orders-table h2 {
            padding: 20px;
            border-bottom: 1px solid #444;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #444;
        }
        th {
            background: #333;
            color: #960303;
        }
        .status {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
        }
        .status-new { background: #2196F3; }
        .status-confirmed { background: #4CAF50; }
        .status-progress { background: #FF9800; }
        .status-completed { background: #9E9E9E; }
        .status-cancelled { background: #f44336; }
        .btn-logout {
            background: #960303;
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
        }
        .btn-logout:hover {
            background: #731D1D;
        }
    </style>
</head>
<body>
    <header>
        <div class="logo">ДомФото | Админ-панель</div>
        <nav>
            <a href="../public/main.php">На сайт</a>
            <a href="../public/logout.php" class="btn-logout">Выйти</a>
        </nav>
    </header>

    <div class="container">
        <div class="stats">
            <div class="stat-card">
                <h3>Пользователей</h3>
                <div class="number"><?= $stats['users'] ?></div>
            </div>
            <div class="stat-card">
                <h3>Заказов</h3>
                <div class="number"><?= $stats['orders'] ?></div>
            </div>
            <div class="stat-card">
                <h3>Активных услуг</h3>
                <div class="number"><?= $stats['services'] ?></div>
            </div>
        </div>

        <div class="orders-table">
            <h2>Последние заказы</h2>
            <table>
                <thead>
                    <tr><th>ID</th><th>Клиент</th><th>Услуга</th><th>Дата</th><th>Статус</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($recentOrders as $order): ?>
                    <tr>
                        <td>#<?= $order['order_id'] ?></td>
                        <td><?= e($order['username']) ?></td>
                        <td><?= e($order['service_name']) ?></td>
                        <td><?= e($order['booking_date'] ?? $order['order_date']) ?></td>
                        <td><span class="status status-<?= strtolower($order['status_name']) ?>"><?= e($order['status_name']) ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>