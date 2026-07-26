<?php
// index.php
require_once __DIR__ . '/includes/auth_check.php';

if (!isLoggedIn()) {
    header("Location: /CRM%20P/login.php");
    exit();
}

if (isAdmin()) {
    header("Location: /CRM%20P/admin/dashboard.php");
} else {
    header("Location: /CRM%20P/user/dashboard.php");
}
exit();
