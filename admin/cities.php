<?php
// admin/cities.php
$pageTitle = "Manage Cities";
require_once __DIR__ . '/../includes/header.php';
requireAdmin();

$pdo = getDBConnection();
$msg = '';
$error = '';

// Handle Create
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $stateId = intval($_POST['state_id'] ?? 0);
    $cityName = trim($_POST['city_name'] ?? '');

    if ($stateId > 0 && !empty($cityName)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO cities (state_id, city_name) VALUES (:state_id, :name)");
            $stmt->execute([':state_id' => $stateId, ':name' => $cityName]);
            $msg = "City '{$cityName}' added successfully!";
        } catch (PDOException $e) {
            $error = "Failed to add city: " . ($e->getCode() == 23000 ? "City name already exists for this state." : $e->getMessage());
        }
    } else {
        $error = "Please select a state and enter a valid city name.";
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    try {
        $stmt = $pdo->prepare("DELETE FROM cities WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $msg = "City deleted successfully!";
    } catch (PDOException $e) {
        $error = "Cannot delete city: It is currently linked to existing leads.";
    }
}

// Fetch states with country info for dropdown
$states = $pdo->query("
    SELECT s.id, s.state_name, c.country_name 
    FROM states s 
    JOIN countries c ON s.country_id = c.id 
    ORDER BY c.country_name ASC, s.state_name ASC
")->fetchAll();

// Fetch cities
$cities = $pdo->query("
    SELECT ct.*, s.state_name, c.country_name 
    FROM cities ct 
    JOIN states s ON ct.state_id = s.id 
    JOIN countries c ON s.country_id = c.id 
    ORDER BY c.country_name ASC, s.state_name ASC, ct.city_name ASC
")->fetchAll();
?>

<?php if ($msg): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="form-grid" style="grid-template-columns: 1fr 2fr;">
    <!-- Add City Form Card -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Add New City</h3>
        </div>
        <div class="card-body">
            <form action="" method="POST">
                <input type="hidden" name="action" value="add">
                
                <div class="form-group">
                    <label class="form-label" for="state_id">Select State <span class="required">*</span></label>
                    <select id="state_id" name="state_id" class="form-select" required>
                        <option value="">-- Choose State --</option>
                        <?php foreach ($states as $s): ?>
                            <option value="<?php echo $s['id']; ?>">
                                <?php echo htmlspecialchars($s['state_name']) . " (" . htmlspecialchars($s['country_name']) . ")"; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="city_name">City Name <span class="required">*</span></label>
                    <input type="text" id="city_name" name="city_name" class="form-control" placeholder="e.g., Mumbai, San Francisco" required>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    Save City
                </button>
            </form>
        </div>
    </div>

    <!-- Cities Table Card -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">City Master List</h3>
            <span class="badge badge-fresh"><?php echo count($cities); ?> Total</span>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-search-bar" style="padding: 16px 24px 0 24px;">
                <div class="search-input-wrap">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    <input type="text" id="tableSearchInput" class="form-control" placeholder="Search cities...">
                </div>
            </div>
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>City Name</th>
                            <th>State</th>
                            <th>Country</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($cities)): ?>
                            <tr><td colspan="5" style="text-align: center; color: var(--text-muted);">No cities found. Add state and cities to populate.</td></tr>
                        <?php else: ?>
                            <?php foreach ($cities as $c): ?>
                                <tr>
                                    <td>#<?php echo $c['id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($c['city_name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($c['state_name']); ?></td>
                                    <td><?php echo htmlspecialchars($c['country_name']); ?></td>
                                    <td class="text-right">
                                        <a href="?delete=<?php echo $c['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this city?');">
                                            Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
