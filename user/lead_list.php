<?php
// user/lead_list.php
$pageTitle = "My Assigned Leads";
require_once __DIR__ . '/../includes/header.php';

$userId = $_SESSION['user_id'];
$pdo = getDBConnection();

// Fetch strictly logged in user's assigned leads
$stmt = $pdo->prepare("
    SELECT l.*, 
           c.country_name, s.state_name, ct.city_name,
           lt.type_name, 
           ls.status_name, ls.color_code,
           (SELECT COUNT(*) FROM lead_documents WHERE lead_id = l.id) AS doc_count
    FROM leads l
    JOIN countries c ON l.country_id = c.id
    JOIN states s ON l.state_id = s.id
    JOIN cities ct ON l.city_id = ct.id
    JOIN lead_types lt ON l.lead_type_id = lt.id
    JOIN lead_statuses ls ON l.lead_status_id = ls.id
    WHERE l.assigned_to = :uid
    ORDER BY l.id DESC
");
$stmt->execute([':uid' => $userId]);
$myLeads = $stmt->fetchAll();
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">My Assigned Leads</h3>
        <a href="/CRM%20P/user/lead_create.php" class="btn btn-sm btn-primary">+ Create New Lead</a>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-search-bar" style="padding: 16px 24px 0 24px;">
            <div class="search-input-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                <input type="text" id="tableSearchInput" class="form-control" placeholder="Search my leads...">
            </div>
        </div>
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Lead ID</th>
                        <th>Company Name</th>
                        <th>Owner / Contact</th>
                        <th>Official Email</th>
                        <th>Location</th>
                        <th>Lead Type</th>
                        <th>Status</th>
                        <th>Follow Up Date</th>
                        <th>Docs</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($myLeads)): ?>
                        <tr><td colspan="10" style="text-align: center; color: var(--text-muted);">No leads currently assigned to you. Click "+ Create New Lead" to submit a lead.</td></tr>
                    <?php else: ?>
                        <?php foreach ($myLeads as $l): ?>
                            <tr>
                                <td><strong>#LD-<?php echo sprintf('%04d', $l['id']); ?></strong></td>
                                <td><strong><?php echo htmlspecialchars($l['company_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($l['owner_name']); ?></td>
                                <td><?php echo htmlspecialchars($l['official_email']); ?></td>
                                <td><?php echo htmlspecialchars($l['city_name']) . ", " . htmlspecialchars($l['state_name']); ?></td>
                                <td><span class="badge badge-fresh"><?php echo htmlspecialchars($l['type_name']); ?></span></td>
                                <td>
                                    <span class="badge" style="background-color: <?php echo htmlspecialchars($l['color_code']); ?>22; color: <?php echo htmlspecialchars($l['color_code']); ?>; border: 1px solid <?php echo htmlspecialchars($l['color_code']); ?>55;">
                                        <?php echo htmlspecialchars($l['status_name']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo $l['follow_up_date'] ? date('d M Y', strtotime($l['follow_up_date'])) : '-'; ?>
                                </td>
                                <td><span class="badge badge-fresh"><?php echo $l['doc_count']; ?> Files</span></td>
                                <td class="text-right">
                                    <a href="/CRM%20P/user/lead_view.php?id=<?php echo $l['id']; ?>" class="btn btn-sm btn-primary">
                                        View & Add Docs
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
