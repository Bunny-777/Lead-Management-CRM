<?php
// user/dashboard.php
$pageTitle = "User Dashboard";
require_once __DIR__ . '/../includes/header.php';

$userId = $_SESSION['user_id'];
$pdo = getDBConnection();

// Stats for current user's leads
$totalLeads = $pdo->prepare("SELECT COUNT(*) FROM leads WHERE assigned_to = :uid");
$totalLeads->execute([':uid' => $userId]);
$myTotalLeads = $totalLeads->fetchColumn();

$freshLeads = $pdo->prepare("SELECT COUNT(*) FROM leads l JOIN lead_statuses s ON l.lead_status_id = s.id WHERE l.assigned_to = :uid AND LOWER(s.status_name) = 'fresh'");
$freshLeads->execute([':uid' => $userId]);
$myFreshLeads = $freshLeads->fetchColumn();

$followUpLeads = $pdo->prepare("SELECT COUNT(*) FROM leads l JOIN lead_statuses s ON l.lead_status_id = s.id WHERE l.assigned_to = :uid AND LOWER(s.status_name) = 'follow up'");
$followUpLeads->execute([':uid' => $userId]);
$myFollowUpLeads = $followUpLeads->fetchColumn();

$maturedLeads = $pdo->prepare("SELECT COUNT(*) FROM leads l JOIN lead_statuses s ON l.lead_status_id = s.id WHERE l.assigned_to = :uid AND LOWER(s.status_name) = 'matured'");
$maturedLeads->execute([':uid' => $userId]);
$myMaturedLeads = $maturedLeads->fetchColumn();

// Fetch recent assigned leads
$stmtMyLeads = $pdo->prepare("
    SELECT l.*, 
           c.country_name, s.state_name, ct.city_name,
           lt.type_name, 
           ls.status_name, ls.color_code
    FROM leads l
    JOIN countries c ON l.country_id = c.id
    JOIN states s ON l.state_id = s.id
    JOIN cities ct ON l.city_id = ct.id
    JOIN lead_types lt ON l.lead_type_id = lt.id
    JOIN lead_statuses ls ON l.lead_status_id = ls.id
    WHERE l.assigned_to = :uid
    ORDER BY l.id DESC
    LIMIT 5
");
$stmtMyLeads->execute([':uid' => $userId]);
$myRecentLeads = $stmtMyLeads->fetchAll();
?>

<!-- KPI Stat Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div>
            <div class="stat-title">My Total Leads</div>
            <div class="stat-value"><?php echo number_format($myTotalLeads); ?></div>
        </div>
        <div class="stat-icon indigo">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-title">Fresh Leads</div>
            <div class="stat-value"><?php echo number_format($myFreshLeads); ?></div>
        </div>
        <div class="stat-icon blue">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-title">Follow-up Pending</div>
            <div class="stat-value"><?php echo number_format($myFollowUpLeads); ?></div>
        </div>
        <div class="stat-icon amber">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-title">Matured Leads</div>
            <div class="stat-value"><?php echo number_format($myMaturedLeads); ?></div>
        </div>
        <div class="stat-icon emerald">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">My Assigned Leads Overview</h3>
        <div style="display: flex; gap: 10px;">
            <a href="/CRM%20P/user/lead_create.php" class="btn btn-sm btn-primary">+ Create New Lead</a>
            <a href="/CRM%20P/user/lead_list.php" class="btn btn-sm btn-secondary">View All My Leads</a>
        </div>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Lead ID</th>
                        <th>Company Name</th>
                        <th>Owner Name</th>
                        <th>Location</th>
                        <th>Type</th>
                        <th>Status</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($myRecentLeads)): ?>
                        <tr><td colspan="7" style="text-align: center; color: var(--text-muted);">No leads assigned to you yet. Use "+ Create New Lead" to submit a lead.</td></tr>
                    <?php else: ?>
                        <?php foreach ($myRecentLeads as $l): ?>
                            <tr>
                                <td><strong>#LD-<?php echo sprintf('%04d', $l['id']); ?></strong></td>
                                <td><strong><?php echo htmlspecialchars($l['company_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($l['owner_name']); ?></td>
                                <td><?php echo htmlspecialchars($l['city_name']) . ", " . htmlspecialchars($l['state_name']); ?></td>
                                <td><span class="badge badge-fresh"><?php echo htmlspecialchars($l['type_name']); ?></span></td>
                                <td>
                                    <span class="badge" style="background-color: <?php echo htmlspecialchars($l['color_code']); ?>22; color: <?php echo htmlspecialchars($l['color_code']); ?>; border: 1px solid <?php echo htmlspecialchars($l['color_code']); ?>55;">
                                        <?php echo htmlspecialchars($l['status_name']); ?>
                                    </span>
                                </td>
                                <td class="text-right">
                                    <a href="/CRM%20P/user/lead_view.php?id=<?php echo $l['id']; ?>" class="btn btn-sm btn-secondary">
                                        View & Upload Docs
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
