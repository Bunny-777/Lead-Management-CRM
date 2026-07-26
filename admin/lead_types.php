<?php
// admin/lead_types.php
$pageTitle = "Manage Lead Types";
require_once __DIR__ . '/../includes/header.php';
requireAdmin();

$pdo = getDBConnection();
$msg = '';
$error = '';

// Handle Create
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $typeName = trim($_POST['type_name'] ?? '');

    if (!empty($typeName)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO lead_types (type_name) VALUES (:name)");
            $stmt->execute([':name' => $typeName]);
            $msg = "Lead Type '{$typeName}' added successfully!";
        } catch (PDOException $e) {
            $error = "Failed to add lead type: " . ($e->getCode() == 23000 ? "Lead Type name already exists." : $e->getMessage());
        }
    } else {
        $error = "Lead Type name cannot be empty.";
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    try {
        $stmt = $pdo->prepare("DELETE FROM lead_types WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $msg = "Lead Type deleted successfully!";
    } catch (PDOException $e) {
        $error = "Cannot delete lead type: It is linked to existing leads.";
    }
}

// Fetch all lead types
$leadTypes = $pdo->query("
    SELECT lt.*, COUNT(l.id) AS lead_count 
    FROM lead_types lt 
    LEFT JOIN leads l ON lt.id = l.lead_type_id 
    GROUP BY lt.id 
    ORDER BY lt.type_name ASC
")->fetchAll();
?>

<?php if ($msg): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="form-grid" style="grid-template-columns: 1fr 2fr;">
    <!-- Add Lead Type Form Card -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Add New Lead Type</h3>
        </div>
        <div class="card-body">
            <form action="" method="POST">
                <input type="hidden" name="action" value="add">
                
                <div class="form-group">
                    <label class="form-label" for="type_name">Lead Type Name <span class="required">*</span></label>
                    <input type="text" id="type_name" name="type_name" class="form-control" placeholder="e.g., Inbound Web, Referral, Cold Call" required>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    Save Lead Type
                </button>
            </form>
        </div>
    </div>

    <!-- Lead Types Table Card -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Lead Types List</h3>
            <span class="badge badge-fresh"><?php echo count($leadTypes); ?> Total</span>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-search-bar" style="padding: 16px 24px 0 24px;">
                <div class="search-input-wrap">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    <input type="text" id="tableSearchInput" class="form-control" placeholder="Search lead types...">
                </div>
            </div>
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Lead Type Name</th>
                            <th>Associated Leads</th>
                            <th>Created At</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($leadTypes)): ?>
                            <tr><td colspan="5" style="text-align: center; color: var(--text-muted);">No lead types defined.</td></tr>
                        <?php else: ?>
                            <?php foreach ($leadTypes as $lt): ?>
                                <tr>
                                    <td>#<?php echo $lt['id']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($lt['type_name']); ?></strong></td>
                                    <td><span class="badge badge-fresh"><?php echo $lt['lead_count']; ?> Leads</span></td>
                                    <td><?php echo date('d M Y', strtotime($lt['created_at'])); ?></td>
                                    <td class="text-right">
                                        <a href="?delete=<?php echo $lt['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this lead type?');">
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
