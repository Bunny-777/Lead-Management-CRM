<?php
// admin/lead_list.php
$pageTitle = "All Leads Directory";
require_once __DIR__ . '/../includes/header.php';
requireAdmin();

$pdo = getDBConnection();
$msg = '';
$error = '';

if (isset($_GET['msg'])) {
    $msg = htmlspecialchars($_GET['msg']);
}
if (isset($_GET['error'])) {
    $error = htmlspecialchars($_GET['error']);
}

// Handle Delete Lead
if (isset($_GET['delete'])) {
    $deleteId = intval($_GET['delete']);
    try {
        $stmt = $pdo->prepare("DELETE FROM leads WHERE id = :id");
        $stmt->execute([':id' => $deleteId]);
        $msg = "Lead record deleted successfully!";
    } catch (PDOException $e) {
        $error = "Failed to delete lead: " . $e->getMessage();
    }
}

// Handle Direct Lead Re-Assignment Post
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'assign_lead') {
    $leadId     = intval($_POST['lead_id'] ?? 0);
    $assignToId = intval($_POST['assigned_to'] ?? 0);

    if ($leadId > 0 && $assignToId > 0) {
        $stmt = $pdo->prepare("UPDATE leads SET assigned_to = :assigned_to WHERE id = :id");
        $stmt->execute([':assigned_to' => $assignToId, ':id' => $leadId]);
        $msg = "Lead #{$leadId} assigned to representative successfully!";
    }
}

// Fetch all leads with full foreign key details
$leads = $pdo->query("
    SELECT l.*, 
           c.country_name, s.state_name, ct.city_name,
           lt.type_name, 
           ls.status_name, ls.color_code,
           u_assigned.name AS assigned_user_name, u_assigned.username AS assigned_username,
           u_creator.name AS creator_name,
           (SELECT COUNT(*) FROM lead_documents WHERE lead_id = l.id) AS doc_count
    FROM leads l
    JOIN countries c ON l.country_id = c.id
    JOIN states s ON l.state_id = s.id
    JOIN cities ct ON l.city_id = ct.id
    JOIN lead_types lt ON l.lead_type_id = lt.id
    JOIN lead_statuses ls ON l.lead_status_id = ls.id
    JOIN users u_assigned ON l.assigned_to = u_assigned.id
    JOIN users u_creator ON l.created_by = u_creator.id
    ORDER BY l.id DESC
")->fetchAll();

$allUsers = $pdo->query("SELECT id, name, username FROM users ORDER BY name ASC")->fetchAll();
?>

<?php if ($msg): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">All Master Leads Directory</h3>
        <a href="/CRM%20P/admin/lead_create.php" class="btn btn-sm btn-primary">
            + Create New Lead
        </a>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-search-bar" style="padding: 16px 24px 0 24px;">
            <div class="search-input-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                <input type="text" id="tableSearchInput" class="form-control" placeholder="Search by company, owner, email, status...">
            </div>
        </div>
        
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Lead ID</th>
                        <th>Company & Owner</th>
                        <th>Contact / Email</th>
                        <th>Location</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th>Follow Up</th>
                        <th>Assigned To</th>
                        <th>Files</th>
                        <th class="text-right">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($leads)): ?>
                        <tr><td colspan="10" style="text-align: center; color: var(--text-muted);">No lead records found. Click "+ Create New Lead" to get started.</td></tr>
                    <?php else: ?>
                        <?php foreach ($leads as $l): ?>
                            <tr>
                                <td><strong>#LD-<?php echo sprintf('%04d', $l['id']); ?></strong></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($l['company_name']); ?></strong>
                                    <div style="font-size: 11.5px; color: var(--text-muted);"><?php echo htmlspecialchars($l['owner_name']); ?></div>
                                </td>
                                <td>
                                    <div><?php echo htmlspecialchars($l['mobile']); ?></div>
                                    <div style="font-size: 11px; color: var(--text-muted);"><?php echo htmlspecialchars($l['official_email']); ?></div>
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($l['city_name']) . ", " . htmlspecialchars($l['state_name']); ?>
                                </td>
                                <td><span class="badge badge-fresh"><?php echo htmlspecialchars($l['type_name']); ?></span></td>
                                <td>
                                    <span class="badge" style="background-color: <?php echo htmlspecialchars($l['color_code']); ?>22; color: <?php echo htmlspecialchars($l['color_code']); ?>; border: 1px solid <?php echo htmlspecialchars($l['color_code']); ?>55;">
                                        <?php echo htmlspecialchars($l['status_name']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($l['follow_up_date']): ?>
                                        <div style="font-size: 12px; font-weight: 600; color: #334155;"><?php echo date('d M Y', strtotime($l['follow_up_date'])); ?></div>
                                    <?php else: ?>
                                        <span style="font-size: 11px; color: var(--text-muted);">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form action="" method="POST" style="display: flex; gap: 4px; align-items: center;">
                                        <input type="hidden" name="action" value="assign_lead">
                                        <input type="hidden" name="lead_id" value="<?php echo $l['id']; ?>">
                                        <select name="assigned_to" class="form-select" style="font-size: 11px; padding: 4px 6px; width: 130px;" onchange="this.form.submit()">
                                            <?php foreach ($allUsers as $u): ?>
                                                <option value="<?php echo $u['id']; ?>" <?php echo $u['id'] == $l['assigned_to'] ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($u['name']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </form>
                                </td>
                                <td>
                                    <span class="badge badge-fresh"><?php echo $l['doc_count']; ?> Files</span>
                                </td>
                                <td class="text-right">
                                    <a href="/CRM%20P/admin/lead_view.php?id=<?php echo $l['id']; ?>" class="btn btn-sm btn-secondary" title="View Full Details">
                                        View
                                    </a>
                                    <a href="?delete=<?php echo $l['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete lead #<?php echo $l['id']; ?>? This cannot be undone.');">
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
