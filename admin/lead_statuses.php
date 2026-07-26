<?php
// admin/lead_statuses.php
$pageTitle = "Manage Lead Statuses";
require_once __DIR__ . '/../includes/header.php';
requireAdmin();

$pdo = getDBConnection();
$msg = '';
$error = '';

// Handle Create
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $statusName = trim($_POST['status_name'] ?? '');
    $colorCode = trim($_POST['color_code'] ?? '#3b82f6');

    if (!empty($statusName)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO lead_statuses (status_name, color_code) VALUES (:name, :color)");
            $stmt->execute([':name' => $statusName, ':color' => $colorCode]);
            $msg = "Lead Status '{$statusName}' added successfully!";
        } catch (PDOException $e) {
            $error = "Failed to add lead status: " . ($e->getCode() == 23000 ? "Status name already exists." : $e->getMessage());
        }
    } else {
        $error = "Lead Status name cannot be empty.";
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    try {
        $stmt = $pdo->prepare("DELETE FROM lead_statuses WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $msg = "Lead Status deleted successfully!";
    } catch (PDOException $e) {
        $error = "Cannot delete status: It is currently linked to existing leads.";
    }
}

// Fetch all lead statuses
$statuses = $pdo->query("
    SELECT ls.*, COUNT(l.id) AS lead_count 
    FROM lead_statuses ls 
    LEFT JOIN leads l ON ls.id = l.lead_status_id 
    GROUP BY ls.id 
    ORDER BY ls.id ASC
")->fetchAll();
?>

<?php if ($msg): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="form-grid" style="grid-template-columns: 1fr 2fr;">
    <!-- Add Status Form Card -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Add New Lead Status</h3>
        </div>
        <div class="card-body">
            <form action="" method="POST">
                <input type="hidden" name="action" value="add">
                
                <div class="form-group">
                    <label class="form-label" for="status_name">Status Name <span class="required">*</span></label>
                    <input type="text" id="status_name" name="status_name" class="form-control" placeholder="e.g., Fresh, Follow Up, Matured, Closed" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="color_code">Color Tag</label>
                    <input type="color" id="color_code" name="color_code" class="form-control" value="#3b82f6" style="height: 42px; padding: 4px;">
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    Save Lead Status
                </button>
            </form>
        </div>
    </div>

    <!-- Lead Statuses Table Card -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Lead Statuses Master List</h3>
            <span class="badge badge-fresh"><?php echo count($statuses); ?> Total</span>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-search-bar" style="padding: 16px 24px 0 24px;">
                <div class="search-input-wrap">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    <input type="text" id="tableSearchInput" class="form-control" placeholder="Search statuses...">
                </div>
            </div>
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Status Name</th>
                            <th>Color Tag</th>
                            <th>Associated Leads</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($statuses)): ?>
                            <tr><td colspan="5" style="text-align: center; color: var(--text-muted);">No statuses defined.</td></tr>
                        <?php else: ?>
                            <?php foreach ($statuses as $st): ?>
                                <tr>
                                    <td>#<?php echo $st['id']; ?></td>
                                    <td>
                                        <span class="badge" style="background-color: <?php echo htmlspecialchars($st['color_code']); ?>22; color: <?php echo htmlspecialchars($st['color_code']); ?>; border: 1px solid <?php echo htmlspecialchars($st['color_code']); ?>55;">
                                            <?php echo htmlspecialchars($st['status_name']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 8px;">
                                            <span style="display: inline-block; width: 14px; height: 14px; border-radius: 50%; background-color: <?php echo htmlspecialchars($st['color_code']); ?>;"></span>
                                            <code><?php echo htmlspecialchars($st['color_code']); ?></code>
                                        </div>
                                    </td>
                                    <td><span class="badge badge-fresh"><?php echo $st['lead_count']; ?> Leads</span></td>
                                    <td class="text-right">
                                        <a href="?delete=<?php echo $st['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this status?');">
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
