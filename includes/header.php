<?php
// includes/header.php
require_once __DIR__ . '/auth_check.php';
requireLogin();
$currentUser = getCurrentUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) . ' - Lead Management System' : 'Lead Management System'; ?></title>
    <link rel="stylesheet" href="/CRM%20P/assets/css/style.css">
</head>
<body>
<div class="app-wrapper">
    <?php include __DIR__ . '/sidebar.php'; ?>
    <div class="main-content">
        <header class="topbar">
            <h1 class="topbar-title"><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Dashboard'; ?></h1>
            <div class="user-profile">
                <span class="role-badge <?php echo htmlspecialchars($currentUser['role_name']); ?>">
                    <?php echo htmlspecialchars(strtoupper($currentUser['role_name'])); ?>
                </span>
                <div class="user-dropdown">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($currentUser['name'], 0, 1)); ?>
                    </div>
                    <div>
                        <div style="font-weight: 600; font-size: 13.5px;"><?php echo htmlspecialchars($currentUser['name']); ?></div>
                        <div style="font-size: 11px; color: var(--text-muted);"><?php echo htmlspecialchars($currentUser['email']); ?></div>
                    </div>
                    <a href="/CRM%20P/logout.php" class="btn btn-sm btn-secondary" style="margin-left: 12px;" title="Logout">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                        Logout
                    </a>
                </div>
            </div>
        </header>
        <main class="page-container">
