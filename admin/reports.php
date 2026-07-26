<?php
// admin/reports.php
$pageTitle = "Reports Module";
require_once __DIR__ . '/../includes/header.php';
requireAdmin();

$pdo = getDBConnection();

// Get filter parameters
$userId    = isset($_GET['user_id']) ? intval($_GET['user_id']) : 0;
$statusId  = isset($_GET['status_id']) ? intval($_GET['status_id']) : 0;
$startDate = isset($_GET['start_date']) ? trim($_GET['start_date']) : '';
$endDate   = isset($_GET['end_date']) ? trim($_GET['end_date']) : '';
$exportCsv = isset($_GET['export']) && $_GET['export'] === 'csv';

// Build Dynamic SQL Query
$whereClause = ["1=1"];
$params = [];

if ($userId > 0) {
    $whereClause[] = "l.assigned_to = :user_id";
    $params[':user_id'] = $userId;
}

if ($statusId > 0) {
    $whereClause[] = "l.lead_status_id = :status_id";
    $params[':status_id'] = $statusId;
}

if (!empty($startDate)) {
    $whereClause[] = "DATE(l.created_at) >= :start_date";
    $params[':start_date'] = $startDate;
}

if (!empty($endDate)) {
    $whereClause[] = "DATE(l.created_at) <= :end_date";
    $params[':end_date'] = $endDate;
}

$sql = "
    SELECT l.*, 
           c.country_name, s.state_name, ct.city_name,
           lt.type_name, 
           ls.status_name, ls.color_code,
           u_assigned.name AS assigned_user_name,
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
    WHERE " . implode(" AND ", $whereClause) . "
    ORDER BY l.id DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$reportData = $stmt->fetchAll();

// Handle CSV Export
if ($exportCsv) {
    ob_clean();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=lead_report_' . date('Y-m-d_H-i-s') . '.csv');
    $output = fopen('php://output', 'w');
    
    // CSV Header Row
    fputcsv($output, ['Lead ID', 'Company Name', 'Owner Name', 'Mobile', 'Official Email', 'City', 'State', 'Country', 'Lead Type', 'Lead Status', 'Follow Up Date', 'Assigned To', 'Created By', 'Created At']);
    
    foreach ($reportData as $row) {
        fputcsv($output, [
            'LD-' . sprintf('%04d', $row['id']),
            $row['company_name'],
            $row['owner_name'],
            $row['mobile'],
            $row['official_email'],
            $row['city_name'],
            $row['state_name'],
            $row['country_name'],
            $row['type_name'],
            $row['status_name'],
            $row['follow_up_date'] ?? 'N/A',
            $row['assigned_user_name'],
            $row['creator_name'],
            $row['created_at']
        ]);
    }
    fclose($output);
    exit();
}

// Fetch Filter Dropdown Data
$users = $pdo->query("SELECT id, name FROM users ORDER BY name ASC")->fetchAll();
$statuses = $pdo->query("SELECT id, status_name FROM lead_statuses ORDER BY id ASC")->fetchAll();
?>

<!-- Filter Card -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Lead Performance & Analytics Filters</h3>
    </div>
    <div class="card-body">
        <form action="" method="GET">
            <div class="form-grid">
                <!-- User Wise Filter -->
                <div class="form-group">
                    <label class="form-label" for="user_id">1. User Wise Filter</label>
                    <select id="user_id" name="user_id" class="form-select">
                        <option value="0">-- All Sales Representatives --</option>
                        <?php foreach ($users as $u): ?>
                            <option value="<?php echo $u['id']; ?>" <?php echo $userId == $u['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($u['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Status Wise Filter -->
                <div class="form-group">
                    <label class="form-label" for="status_id">2. Lead Status Filter</label>
                    <select id="status_id" name="status_id" class="form-select">
                        <option value="0">-- All Statuses --</option>
                        <?php foreach ($statuses as $st): ?>
                            <option value="<?php echo $st['id']; ?>" <?php echo $statusId == $st['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($st['status_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Date Wise Start -->
                <div class="form-group">
                    <label class="form-label" for="start_date">3. Start Creation Date</label>
                    <input type="date" id="start_date" name="start_date" class="form-control" value="<?php echo htmlspecialchars($startDate); ?>">
                </div>

                <!-- Date Wise End -->
                <div class="form-group">
                    <label class="form-label" for="end_date">4. End Creation Date</label>
                    <input type="date" id="end_date" name="end_date" class="form-control" value="<?php echo htmlspecialchars($endDate); ?>">
                </div>
            </div>

            <div style="display: flex; gap: 12px; margin-top: 16px; justify-content: flex-end;">
                <a href="/CRM%20P/admin/reports.php" class="btn btn-secondary">Reset Filters</a>
                <button type="submit" class="btn btn-primary">Generate Report</button>
            </div>
        </form>
    </div>
</div>

<!-- Report Data Output Card -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Generated Report Results</h3>
        <div style="display: flex; gap: 12px; align-items: center;">
            <span class="badge badge-fresh"><?php echo count($reportData); ?> Records Found</span>
            <?php if (!empty($reportData)): ?>
                <?php 
                    $exportUrl = "?user_id={$userId}&status_id={$statusId}&start_date={$startDate}&end_date={$endDate}&export=csv";
                ?>
                <a href="<?php echo $exportUrl; ?>" class="btn btn-sm btn-success">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                    Export to CSV
                </a>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body" style="padding: 0;">
        <div class="table-search-bar" style="padding: 16px 24px 0 24px;">
            <div class="search-input-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                <input type="text" id="tableSearchInput" class="form-control" placeholder="Search report rows...">
            </div>
        </div>
        <div class="table-responsive">
            <table class="custom-table">
                <thead>
                    <tr>
                        <th>Lead ID</th>
                        <th>Company & Owner</th>
                        <th>Mobile / Email</th>
                        <th>Location</th>
                        <th>Lead Type</th>
                        <th>Lead Status</th>
                        <th>Assigned Representative</th>
                        <th>Created Date</th>
                        <th class="text-right">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($reportData)): ?>
                        <tr><td colspan="9" style="text-align: center; color: var(--text-muted);">No records match your selected report filter criteria.</td></tr>
                    <?php else: ?>
                        <?php foreach ($reportData as $row): ?>
                            <tr>
                                <td><strong>#LD-<?php echo sprintf('%04d', $row['id']); ?></strong></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($row['company_name']); ?></strong>
                                    <div style="font-size: 11px; color: var(--text-muted);"><?php echo htmlspecialchars($row['owner_name']); ?></div>
                                </td>
                                <td>
                                    <div><?php echo htmlspecialchars($row['mobile']); ?></div>
                                    <div style="font-size: 11px; color: var(--text-muted);"><?php echo htmlspecialchars($row['official_email']); ?></div>
                                </td>
                                <td><?php echo htmlspecialchars($row['city_name']) . ", " . htmlspecialchars($row['state_name']); ?></td>
                                <td><span class="badge badge-fresh"><?php echo htmlspecialchars($row['type_name']); ?></span></td>
                                <td>
                                    <span class="badge" style="background-color: <?php echo htmlspecialchars($row['color_code']); ?>22; color: <?php echo htmlspecialchars($row['color_code']); ?>; border: 1px solid <?php echo htmlspecialchars($row['color_code']); ?>55;">
                                        <?php echo htmlspecialchars($row['status_name']); ?>
                                    </span>
                                </td>
                                <td><strong><?php echo htmlspecialchars($row['assigned_user_name']); ?></strong></td>
                                <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                                <td class="text-right">
                                    <a href="/CRM%20P/admin/lead_view.php?id=<?php echo $row['id']; ?>" class="btn btn-sm btn-secondary">
                                        View
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
