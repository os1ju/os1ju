<?php

/**
 * Запуск сессии, если не запущена
 */
function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

/**
 * Установка данных пользователя в сессию
 */
function setUserSession($user) {
    startSession();
    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['username'] = $user['username'];
    $_SESSION['name'] = $user['name'];
    $_SESSION['role_id'] = $user['role_id'];
    $_SESSION['role_name'] = $user['role_name'] ?? getUserRole($user['role_id']);
}

/**
 * Получение текущего пользователя из сессии
 */
function getCurrentUser() {
    startSession();
    if (isset($_SESSION['user_id'])) {
        return [
            'user_id' => $_SESSION['user_id'],
            'username' => $_SESSION['username'],
            'name' => $_SESSION['name'],
            'role_id' => $_SESSION['role_id'],
            'role_name' => $_SESSION['role_name']
        ];
    }
    return null;
}

/**
 * Проверка, авторизован ли пользователь
 */
function isLoggedIn() {
    return getCurrentUser() !== null;
}

/**
 * Проверка роли пользователя
 */
function hasRole($roleName) {
    $user = getCurrentUser();
    return $user && $user['role_name'] === $roleName;
}

/**
 * Проверка, является ли пользователь админом
 */
function isAdmin() {
    return hasRole('admin');
}

/**
 * Выход из системы
 */
function logout() {
    startSession();
    $_SESSION = array();
    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }
    session_destroy();
}

/**
 * Получение названия роли по ID
 */
function getUserRole($roleId, $pdo) {
    $stmt = $pdo->prepare("SELECT role_name FROM Roles WHERE role_id = ?");
    $stmt->execute([$roleId]);
    $result = $stmt->fetch();
    return $result ? $result['role_name'] : 'guest';
}

/**
 * Требование авторизации (редирект на страницу входа)
 */
function requireAuth() {
    if (!isLoggedIn()) {
        header('Location: authorization.php');
        exit;
    }
}

/**
 * Требование роли админа
 */
function requireAdmin() {
    requireAuth();
    if (!isAdmin()) {
        header('HTTP/1.0 403 Forbidden');
        die('Доступ запрещен');
    }
}