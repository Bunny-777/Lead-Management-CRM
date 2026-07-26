<?php
// api/get_states.php
header('Content-Type: application/json');
require_once __DIR__ . '/../includes/auth_check.php';

requireLogin();

$countryId = isset($_GET['country_id']) ? intval($_GET['country_id']) : 0;

if ($countryId <= 0) {
    echo json_encode([]);
    exit();
}

$pdo = getDBConnection();
$stmt = $pdo->prepare("SELECT id, state_name FROM states WHERE country_id = :country_id ORDER BY state_name ASC");
$stmt->execute([':country_id' => $countryId]);
$states = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($states);
