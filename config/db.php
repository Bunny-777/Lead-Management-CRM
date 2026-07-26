<?php
// config/db.php
// Database connection and auto-initialization module for Lead Management System

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'lead_management_db');

function getDBConnection() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            // First attempt connection to host without database name to ensure DB exists
            $dsnNoDb = "mysql:host=" . DB_HOST . ";charset=utf8mb4";
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];
            
            $rawPdo = new PDO($dsnNoDb, DB_USER, DB_PASS, $options);
            
            // Ensure Database exists
            $rawPdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
            $rawPdo->exec("USE `" . DB_NAME . "`;");
            
            // Connect to specific database
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
            
            // Auto run schema setup if tables don't exist
            autoInitializeSchema($pdo);
            
        } catch (PDOException $e) {
            die("<div style='padding:20px; font-family:sans-serif; background:#fef2f2; color:#991b1b; border:1px solid #f87171; margin:30px auto; max-width:600px; border-radius:8px;'>
                    <h3 style='margin-top:0;'>Database Connection Error</h3>
                    <p>Failed to connect to MySQL on <strong>" . DB_HOST . "</strong>.</p>
                    <p style='font-size:0.9em; color:#7f1d1d;'>Detail: " . htmlspecialchars($e->getMessage()) . "</p>
                    <hr>
                    <p style='font-size:0.85em;'>Make sure XAMPP MySQL server is running.</p>
                 </div>");
        }
    }
    return $pdo;
}

function autoInitializeSchema($pdo) {
    // Check if users table exists
    $tableCheck = $pdo->query("SHOW TABLES LIKE 'users'")->fetch();
    if (!$tableCheck) {
        $schemaFile = __DIR__ . '/../schema.sql';
        if (file_exists($schemaFile)) {
            $sql = file_get_contents($schemaFile);
            $pdo->exec($sql);
        }
    }
}
