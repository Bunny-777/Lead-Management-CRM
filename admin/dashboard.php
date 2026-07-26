<?php
// admin/dashboard.php
$pageTitle = "Admin Dashboard";
require_once __DIR__ . '/../includes/header.php';
requireAdmin();

$pdo = getDBConnection();

// KPI Stats Aggregation Queries
$totalLeads = $pdo->query("SELECT COUNT(*) FROM leads")->fetchColumn();
$totalUsers = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();

// Status counts
$freshLeads    = $pdo->query("SELECT COUNT(*) FROM leads l JOIN lead_statuses s ON l.lead_status_id = s.id WHERE LOWER(s.status_name) = 'fresh'")->fetchColumn();
$followUpLeads = $pdo->query("SELECT COUNT(*) FROM leads l JOIN lead_statuses s ON l.lead_status_id = s.id WHERE LOWER(s.status_name) = 'follow up'")->fetchColumn();
$maturedLeads  = $pdo->query("SELECT COUNT(*) FROM leads l JOIN lead_statuses s ON l.lead_status_id = s.id WHERE LOWER(s.status_name) = 'matured'")->fetchColumn();
$closedLeads   = $pdo->query("SELECT COUNT(*) FROM leads l JOIN lead_statuses s ON l.lead_status_id = s.id WHERE LOWER(s.status_name) = 'closed'")->fetchColumn();

// Recent 5 leads
$recentLeads = $pdo->query("
    SELECT l.*, 
           c.country_name, s.state_name, ct.city_name,
           lt.type_name, 
           ls.status_name, ls.color_code,
           u.name AS assigned_user_name
    FROM leads l
    JOIN countries c ON l.country_id = c.id
    JOIN states s ON l.state_id = s.id
    JOIN cities ct ON l.city_id = ct.id
    JOIN lead_types lt ON l.lead_type_id = lt.id
    JOIN lead_statuses ls ON l.lead_status_id = ls.id
    JOIN users u ON l.assigned_to = u.id
    ORDER BY l.id DESC
    LIMIT 5
")->fetchAll();
?>

<!-- KPI Stat Cards -->
<div class="stats-grid">
    <div class="stat-card">
        <div>
            <div class="stat-title">Total Leads</div>
            <div class="stat-value"><?php echo number_format($totalLeads); ?></div>
        </div>
        <div class="stat-icon indigo">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-title">Fresh Leads</div>
            <div class="stat-value"><?php echo number_format($freshLeads); ?></div>
        </div>
        <div class="stat-icon blue">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-title">Follow-up Pending</div>
            <div class="stat-value"><?php echo number_format($followUpLeads); ?></div>
        </div>
        <div class="stat-icon amber">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-title">Matured Leads</div>
            <div class="stat-value"><?php echo number_format($maturedLeads); ?></div>
        </div>
        <div class="stat-icon emerald">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-title">Closed Leads</div>
            <div class="stat-value"><?php echo number_format($closedLeads); ?></div>
        </div>
        <div class="stat-icon rose">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
        </div>
    </div>

    <div class="stat-card">
        <div>
            <div class="stat-title">System Users</div>
            <div class="stat-value"><?php echo number_format($totalUsers); ?></div>
        </div>
        <div class="stat-icon indigo">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
        </div>
    </div>
</div>

<!-- Recent Leads Quick Overview -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Recently Created Leads</h3>
        <a href="/CRM%20P/admin/lead_list.php" class="btn btn-sm btn-secondary">View All Leads &rarr;</a>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Lead ID</th>
                        <th>Company Name</th>
                        <th>Owner / Contact</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Assigned To</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($recentLeads)): ?>
                        <tr><td colspan="7" style="text-align: center; color: var(--text-muted);">No leads created yet.</td></tr>
                    <?php else: ?>
                        <?php foreach ($recentLeads as $rl): ?>
                            <tr>
                                <td><strong>#LD-<?php echo sprintf('%04d', $rl['id']); ?></strong></td>
                                <td><strong><?php echo htmlspecialchars($rl['company_name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($rl['owner_name']); ?></td>
                                <td><?php echo htmlspecialchars($rl['city_name']) . ", " . htmlspecialchars($rl['state_name']); ?></td>
                                <td>
                                    <span class="badge" style="background-color: <?php echo htmlspecialchars($rl['color_code']); ?>22; color: <?php echo htmlspecialchars($rl['color_code']); ?>; border: 1px solid <?php echo htmlspecialchars($rl['color_code']); ?>55;">
                                        <?php echo htmlspecialchars($rl['status_name']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($rl['assigned_user_name']); ?></td>
                                <td class="text-right">
                                    <a href="/CRM%20P/admin/lead_view.php?id=<?php echo $rl['id']; ?>" class="btn btn-sm btn-primary">
                                        View Details
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
