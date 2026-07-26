<?php
// includes/auth_check.php
// Authentication & Role-Based Access Control Middleware

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: /CRM%20P/login.php");
        exit();
    }
}

function isAdmin() {
    return isLoggedIn() && isset($_SESSION['role_name']) && strtolower($_SESSION['role_name']) === 'admin';
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header("Location: /CRM%20P/user/dashboard.php?error=" . urlencode("Access Denied: Admin Privileges Required"));
        exit();
    }
}

function getCurrentUser() {
    if (!isLoggedIn()) return null;
    return [
        'id' => $_SESSION['user_id'],
        'name' => $_SESSION['user_name'] ?? 'User',
        'email' => $_SESSION['user_email'] ?? '',
        'username' => $_SESSION['username'] ?? '',
        'role_id' => $_SESSION['role_id'] ?? 2,
        'role_name' => $_SESSION['role_name'] ?? 'user'
    ];
}
