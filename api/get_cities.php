<?php
// api/get_cities.php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/auth_check.php';

requireLogin();

$stateId = isset($_GET['state_id']) ? intval($_GET['state_id']) : 0;

if ($stateId <= 0) {
    echo json_encode([]);
    exit();
}

$pdo = getDBConnection();
$stmt = $pdo->prepare("SELECT id, city_name FROM cities WHERE state_id = :state_id ORDER BY city_name ASC");
$stmt->execute([':state_id' => $stateId]);
$cities = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($cities);
