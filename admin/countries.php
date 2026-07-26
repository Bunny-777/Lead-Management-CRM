<?php
// admin/countries.php
$pageTitle = "Manage Countries";
require_once __DIR__ . '/../includes/header.php';
requireAdmin();

$pdo = getDBConnection();
$msg = '';
$error = '';

// Handle Create
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $countryName = trim($_POST['country_name'] ?? '');
    if (!empty($countryName)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO countries (country_name) VALUES (:name)");
            $stmt->execute([':name' => $countryName]);
            $msg = "Country '{$countryName}' added successfully!";
        } catch (PDOException $e) {
            $error = "Failed to add country: " . ($e->getCode() == 23000 ? "Country name already exists." : $e->getMessage());
        }
    } else {
        $error = "Country name cannot be empty.";
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    try {
        $stmt = $pdo->prepare("DELETE FROM countries WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $msg = "Country deleted successfully!";
    } catch (PDOException $e) {
        $error = "Cannot delete country: It is currently linked to existing states or leads.";
    }
}

// Fetch all countries
$countries = $pdo->query("SELECT c.*, COUNT(s.id) AS state_count FROM countries c LEFT JOIN states s ON c.id = s.country_id GROUP BY c.id ORDER BY c.country_name ASC")->fetchAll();
?>

<?php if ($msg): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="form-grid" style="grid-template-columns: 1fr 2fr;">
    <!-- Add Country Form Card -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Add New Country</h3>
        </div>
        <div class="card-body">
            <form action="" method="POST">
                <input type="hidden" name="action" value="add">
                <div class="form-group">
                    <label class="form-label" for="country_name">Country Name <span class="required">*</span></label>
                    <input type="text" id="country_name" name="country_name" class="form-control" placeholder="e.g., India, United States" required>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    Save Country
                </button>
            </form>
        </div>
    </div>

    <!-- Countries Table Card -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Country Master List</h3>
            <span class="badge badge-fresh"><?php echo count($countries); ?> Total</span>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-search-bar" style="padding: 16px 24px 0 24px;">
                <div class="search-input-wrap">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    <input type="text" id="tableSearchInput" class="form-control" placeholder="Search countries...">
                </div>
            </div>
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Country Name</th>
                            <th>Linked States</th>
                            <th>Created At</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($countries)): ?>
                            <tr><td colspan="5" style="text-align: center; color: var(--text-muted);">No countries found. Add your first country using the form.</td></tr>
                        <?php else: ?>
                            <?php foreach ($countries as $c): ?>
                                <tr>
                                    <td>#<?php echo $c['id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($c['country_name']); ?></strong></td>
                                    <td><span class="badge badge-fresh"><?php echo $c['state_count']; ?> States</span></td>
                                    <td><?php echo date('d M Y', strtotime($c['created_at'])); ?></td>
                                    <td class="text-right">
                                        <a href="?delete=<?php echo $c['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this country?');">
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
