<?php
// admin/states.php
$pageTitle = "Manage States";
require_once __DIR__ . '/../includes/header.php';
requireAdmin();

$pdo = getDBConnection();
$msg = '';
$error = '';

// Handle Create
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $countryId = intval($_POST['country_id'] ?? 0);
    $stateName = trim($_POST['state_name'] ?? '');

    if ($countryId > 0 && !empty($stateName)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO states (country_id, state_name) VALUES (:country_id, :name)");
            $stmt->execute([':country_id' => $countryId, ':name' => $stateName]);
            $msg = "State '{$stateName}' added successfully!";
        } catch (PDOException $e) {
            $error = "Failed to add state: " . ($e->getCode() == 23000 ? "State already exists for this country." : $e->getMessage());
        }
    } else {
        $error = "Please select a country and enter a valid state name.";
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    try {
        $stmt = $pdo->prepare("DELETE FROM states WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $msg = "State deleted successfully!";
    } catch (PDOException $e) {
        $error = "Cannot delete state: It is currently linked to existing cities or leads.";
    }
}

// Fetch countries for dropdown
$countries = $pdo->query("SELECT * FROM countries ORDER BY country_name ASC")->fetchAll();

// Fetch states with country names & city counts
$states = $pdo->query("
    SELECT s.*, c.country_name, COUNT(ct.id) AS city_count 
    FROM states s 
    JOIN countries c ON s.country_id = c.id 
    LEFT JOIN cities ct ON s.id = ct.state_id 
    GROUP BY s.id 
    ORDER BY c.country_name ASC, s.state_name ASC
")->fetchAll();
?>

<?php if ($msg): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="form-grid" style="grid-template-columns: 1fr 2fr;">
    <!-- Add State Form Card -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Add New State</h3>
        </div>
        <div class="card-body">
            <form action="" method="POST">
                <input type="hidden" name="action" value="add">
                
                <div class="form-group">
                    <label class="form-label" for="country_id">Select Country <span class="required">*</span></label>
                    <select id="country_id" name="country_id" class="form-select" required>
                        <option value="">-- Choose Country --</option>
                        <?php foreach ($countries as $c): ?>
                            <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['country_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="state_name">State Name <span class="required">*</span></label>
                    <input type="text" id="state_name" name="state_name" class="form-control" placeholder="e.g., Maharashtra, California" required>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    Save State
                </button>
            </form>
        </div>
    </div>

    <!-- States Table Card -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">State Master List</h3>
            <span class="badge badge-fresh"><?php echo count($states); ?> Total</span>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-search-bar" style="padding: 16px 24px 0 24px;">
                <div class="search-input-wrap">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    <input type="text" id="tableSearchInput" class="form-control" placeholder="Search states...">
                </div>
            </div>
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>State Name</th>
                            <th>Country</th>
                            <th>Cities</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($states)): ?>
                            <tr><td colspan="5" style="text-align: center; color: var(--text-muted);">No states found. Select a country and add states.</td></tr>
                        <?php else: ?>
                            <?php foreach ($states as $s): ?>
                                <tr>
                                    <td>#<?php echo $s['id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($s['state_name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($s['country_name']); ?></td>
                                    <td><span class="badge badge-fresh"><?php echo $s['city_count']; ?> Cities</span></td>
                                    <td class="text-right">
                                        <a href="?delete=<?php echo $s['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this state?');">
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
