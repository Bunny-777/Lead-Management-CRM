<?php
// db_test.php - Run this to verify your database is connected and all tables exist
// DELETE THIS FILE after testing!

$host = 'localhost';
$user = 'root';
$pass = '';
$dbName = 'lead_management_db';

echo "<!DOCTYPE html>
<html>
<head>
<title>DB Connection Test</title>
<style>
  body { font-family: monospace; background: #0f172a; color: #e2e8f0; padding: 30px; }
  h2 { color: #818cf8; }
  .ok { color: #34d399; font-weight: bold; }
  .fail { color: #f87171; font-weight: bold; }
  .box { background: #1e293b; border: 1px solid #334155; border-radius: 8px; padding: 20px; margin: 16px 0; }
  table { width: 100%; border-collapse: collapse; }
  th { background: #334155; padding: 8px 14px; text-align: left; color: #94a3b8; }
  td { padding: 8px 14px; border-bottom: 1px solid #334155; }
</style>
</head>
<body>
<h2>&#x1F4CB; Lead Management System - Database Diagnostics</h2>";

// --- Step 1: Test MySQL Connection ---
echo "<div class='box'><strong>Step 1: MySQL Server Connection</strong><br><br>";
try {
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "<span class='ok'>&#x2714; CONNECTED</span> — MySQL server is running on <code>$host</code> as user <code>$user</code>";
} catch (PDOException $e) {
    echo "<span class='fail'>&#x2718; FAILED</span> — Could not connect to MySQL: " . $e->getMessage();
    echo "</div></body></html>";
    exit();
}
echo "</div>";

// --- Step 2: Test Database Exists ---
echo "<div class='box'><strong>Step 2: Database Exists — <code>$dbName</code></strong><br><br>";
$dbCheck = $pdo->query("SHOW DATABASES LIKE '$dbName'")->fetch();
if ($dbCheck) {
    echo "<span class='ok'>&#x2714; DATABASE FOUND</span> — <code>$dbName</code> exists.";
    $pdo->exec("USE `$dbName`");
} else {
    echo "<span class='fail'>&#x2718; DATABASE MISSING</span> — <code>$dbName</code> not found. Import schema.sql via phpMyAdmin first.";
    echo "</div></body></html>";
    exit();
}
echo "</div>";

// --- Step 3: Check All Tables Exist ---
$expectedTables = ['roles', 'users', 'countries', 'states', 'cities', 'lead_types', 'lead_statuses', 'leads', 'lead_documents'];

echo "<div class='box'><strong>Step 3: Table Verification</strong><br><br>";
echo "<table><tr><th>Table Name</th><th>Status</th><th>Row Count</th></tr>";

foreach ($expectedTables as $table) {
    $tableCheck = $pdo->query("SHOW TABLES LIKE '$table'")->fetch();
    if ($tableCheck) {
        $count = $pdo->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
        echo "<tr><td><code>$table</code></td><td><span class='ok'>&#x2714; Exists</span></td><td>$count rows</td></tr>";
    } else {
        echo "<tr><td><code>$table</code></td><td><span class='fail'>&#x2718; MISSING</span></td><td>—</td></tr>";
    }
}

echo "</table></div>";

// --- Step 4: Verify Seed Users ---
echo "<div class='box'><strong>Step 4: Seed User Accounts</strong><br><br>";
$users = $pdo->query("SELECT u.id, u.name, u.username, u.email, r.role_name FROM users u JOIN roles r ON u.role_id = r.id")->fetchAll(PDO::FETCH_ASSOC);
if (!empty($users)) {
    echo "<table><tr><th>ID</th><th>Name</th><th>Username</th><th>Email</th><th>Role</th></tr>";
    foreach ($users as $u) {
        echo "<tr><td>{$u['id']}</td><td>{$u['name']}</td><td><code>{$u['username']}</code></td><td>{$u['email']}</td><td>{$u['role_name']}</td></tr>";
    }
    echo "</table>";
} else {
    echo "<span class='fail'>&#x2718; No users found</span> — Run the seed INSERT queries from schema.sql in phpMyAdmin.";
}
echo "</div>";

// --- Step 5: Foreign Key Check ---
echo "<div class='box'><strong>Step 5: Foreign Key Integrity Check</strong><br><br>";
$fkCheck = $pdo->query("SELECT * FROM information_schema.TABLE_CONSTRAINTS WHERE CONSTRAINT_TYPE = 'FOREIGN KEY' AND TABLE_SCHEMA = '$dbName'")->fetchAll();
if (!empty($fkCheck)) {
    echo "<span class='ok'>&#x2714; " . count($fkCheck) . " foreign key constraints found</span> — Relational integrity is properly configured.";
} else {
    echo "<span class='fail'>&#x2718; No foreign keys found</span> — Schema may not be imported correctly.";
}
echo "</div>";

echo "<div class='box' style='border-color:#34d399;'><strong>&#x2714; All checks done!</strong><br>
<span style='color:#f59e0b;'>&#x26A0; IMPORTANT: Delete this file (<code>db_test.php</code>) after testing — never leave diagnostic files on a live server.</span>
</div>";

echo "</body></html>";
