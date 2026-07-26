<?php
// admin/lead_view.php
$pageTitle = "Lead Details";
require_once __DIR__ . '/../includes/header.php';
requireAdmin();

$pdo = getDBConnection();
$leadId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$msg = '';
$error = '';

if ($leadId <= 0) {
    header("Location: /CRM%20P/admin/lead_list.php");
    exit();
}

// Handle Status & Follow up update from Admin
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_lead') {
    $statusId = intval($_POST['lead_status_id'] ?? 0);
    $followUpDate = !empty($_POST['follow_up_date']) ? $_POST['follow_up_date'] : null;
    $remarks = trim($_POST['remarks'] ?? '');
    $assignedTo = intval($_POST['assigned_to'] ?? 0);

    if ($statusId > 0 && $assignedTo > 0) {
        $stmt = $pdo->prepare("
            UPDATE leads 
            SET lead_status_id = :status_id, 
                follow_up_date = :follow_up, 
                remarks = :remarks,
                assigned_to = :assigned_to
            WHERE id = :id
        ");
        $stmt->execute([
            ':status_id' => $statusId,
            ':follow_up' => $followUpDate,
            ':remarks' => $remarks,
            ':assigned_to' => $assignedTo,
            ':id' => $leadId
        ]);
        $msg = "Lead details updated successfully!";
    }
}

// Handle Upload Extra Documents
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_documents') {
    if (isset($_FILES['doc_files']) && is_array($_FILES['doc_files']['name'])) {
        $uploadDir = __DIR__ . '/../uploads/leads/';
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $docTitles = $_POST['doc_titles'] ?? [];
        $stmtDoc = $pdo->prepare("
            INSERT INTO lead_documents (
                lead_id, document_title, file_name, original_name, file_path, file_type, file_size, uploaded_by
            ) VALUES (
                :lead_id, :title, :file_name, :original_name, :file_path, :file_type, :file_size, :uploaded_by
            )
        ");

        $uploadedCount = 0;
        foreach ($_FILES['doc_files']['name'] as $i => $name) {
            if ($_FILES['doc_files']['error'][$i] === UPLOAD_ERR_OK) {
                $origName = $_FILES['doc_files']['name'][$i];
                $tmpName  = $_FILES['doc_files']['tmp_name'][$i];
                $fileSize = $_FILES['doc_files']['size'][$i];
                $fileType = $_FILES['doc_files']['type'][$i];
                $docTitle = !empty($docTitles[$i]) ? trim($docTitles[$i]) : "Document Attachment";

                $ext = pathinfo($origName, PATHINFO_EXTENSION);
                $newFileName = "lead_" . $leadId . "_" . time() . "_" . mt_rand(1000, 9999) . "." . $ext;
                $targetPath = $uploadDir . $newFileName;
                $relativePath = "uploads/leads/" . $newFileName;

                if (move_uploaded_file($tmpName, $targetPath)) {
                    $stmtDoc->execute([
                        ':lead_id'       => $leadId,
                        ':title'         => $docTitle,
                        ':file_name'     => $newFileName,
                        ':original_name' => $origName,
                        ':file_path'     => $relativePath,
                        ':file_type'     => $fileType,
                        ':file_size'     => $fileSize,
                        ':uploaded_by'   => $_SESSION['user_id']
                    ]);
                    $uploadedCount++;
                }
            }
        }
        if ($uploadedCount > 0) {
            $msg = "{$uploadedCount} document(s) uploaded successfully!";
        }
    }
}

// Fetch Lead Details
$stmt = $pdo->prepare("
    SELECT l.*, 
           c.country_name, s.state_name, ct.city_name,
           lt.type_name, 
           ls.status_name, ls.color_code,
           u_assigned.name AS assigned_user_name, u_assigned.username AS assigned_username,
           u_creator.name AS creator_name
    FROM leads l
    JOIN countries c ON l.country_id = c.id
    JOIN states s ON l.state_id = s.id
    JOIN cities ct ON l.city_id = ct.id
    JOIN lead_types lt ON l.lead_type_id = lt.id
    JOIN lead_statuses ls ON l.lead_status_id = ls.id
    JOIN users u_assigned ON l.assigned_to = u_assigned.id
    JOIN users u_creator ON l.created_by = u_creator.id
    WHERE l.id = :id
");
$stmt->execute([':id' => $leadId]);
$lead = $stmt->fetch();

if (!$lead) {
    header("Location: /CRM%20P/admin/lead_list.php");
    exit();
}

// Fetch attached documents
$stmtDocs = $pdo->prepare("
    SELECT d.*, u.name AS uploader_name 
    FROM lead_documents d 
    JOIN users u ON d.uploaded_by = u.id 
    WHERE d.lead_id = :lead_id 
    ORDER BY d.id DESC
");
$stmtDocs->execute([':lead_id' => $leadId]);
$documents = $stmtDocs->fetchAll();

$leadStatuses = $pdo->query("SELECT * FROM lead_statuses ORDER BY id ASC")->fetchAll();
$allUsers     = $pdo->query("SELECT id, name, username FROM users ORDER BY name ASC")->fetchAll();
?>

<?php if ($msg): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div>
<?php endif; ?>

<div class="d-flex align-center justify-between" style="margin-bottom: 20px;">
    <div>
        <h2 style="font-size: 20px; font-weight: 700; color: var(--text-main);">
            #LD-<?php echo sprintf('%04d', $lead['id']); ?>: <?php echo htmlspecialchars($lead['company_name']); ?>
        </h2>
        <span class="badge" style="background-color: <?php echo htmlspecialchars($lead['color_code']); ?>22; color: <?php echo htmlspecialchars($lead['color_code']); ?>; border: 1px solid <?php echo htmlspecialchars($lead['color_code']); ?>55; margin-top: 4px;">
            Status: <?php echo htmlspecialchars($lead['status_name']); ?>
        </span>
    </div>
    <a href="/CRM%20P/admin/lead_list.php" class="btn btn-secondary">&larr; Back to Lead Directory</a>
</div>

<div class="form-grid" style="grid-template-columns: 2fr 1fr;">
    <div>
        <!-- Company & Contact Info -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Company & Contact Master Information</h3>
            </div>
            <div class="card-body">
                <div class="form-grid" style="grid-template-columns: 1fr 1fr;">
                    <div>
                        <label class="form-label">Company Name</label>
                        <div style="font-size: 15px; font-weight: 600; color: var(--text-main);"><?php echo htmlspecialchars($lead['company_name']); ?></div>
                    </div>
                    <div>
                        <label class="form-label">Owner / Key Person</label>
                        <div style="font-size: 15px; font-weight: 600; color: var(--text-main);"><?php echo htmlspecialchars($lead['owner_name']); ?></div>
                    </div>
                    <div>
                        <label class="form-label">Mobile Number</label>
                        <div><?php echo htmlspecialchars($lead['mobile']); ?></div>
                    </div>
                    <div>
                        <label class="form-label">Official Email</label>
                        <div><a href="mailto:<?php echo htmlspecialchars($lead['official_email']); ?>"><?php echo htmlspecialchars($lead['official_email']); ?></a></div>
                    </div>
                    <div>
                        <label class="form-label">Personal Email</label>
                        <div><?php echo !empty($lead['personal_email']) ? htmlspecialchars($lead['personal_email']) : 'N/A'; ?></div>
                    </div>
                    <div>
                        <label class="form-label">Location (City, State, Country)</label>
                        <div><?php echo htmlspecialchars($lead['city_name']) . ", " . htmlspecialchars($lead['state_name']) . ", " . htmlspecialchars($lead['country_name']); ?></div>
                    </div>
                    <div>
                        <label class="form-label">Lead Channel / Type</label>
                        <div><span class="badge badge-fresh"><?php echo htmlspecialchars($lead['type_name']); ?></span></div>
                    </div>
                    <div>
                        <label class="form-label">Created By</label>
                        <div><?php echo htmlspecialchars($lead['creator_name']); ?></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attached Documents -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Attached Files & Documents (<?php echo count($documents); ?> Attached)</h3>
            </div>
            <div class="card-body" style="padding: 0;">
                <div class="table-responsive">
                    <table class="custom-table">
                        <thead>
                            <tr>
                                <th>Document Title</th>
                                <th>Original File Name</th>
                                <th>Size</th>
                                <th>Uploaded By</th>
                                <th>Upload Date</th>
                                <th class="text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($documents)): ?>
                                <tr><td colspan="6" style="text-align: center; color: var(--text-muted);">No documents attached to this lead yet.</td></tr>
                            <?php else: ?>
                                <?php foreach ($documents as $doc): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($doc['document_title']); ?></strong></td>
                                        <td><code><?php echo htmlspecialchars($doc['original_name']); ?></code></td>
                                        <td><?php echo round($doc['file_size'] / 1024, 1); ?> KB</td>
                                        <td><?php echo htmlspecialchars($doc['uploader_name']); ?></td>
                                        <td><?php echo date('d M Y, h:i A', strtotime($doc['uploaded_at'])); ?></td>
                                        <td class="text-right">
                                            <a href="/CRM%20P/<?php echo htmlspecialchars($doc['file_path']); ?>" target="_blank" class="btn btn-sm btn-primary" download>
                                                Download
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

        <!-- Form to Add More Documents -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">+ Upload Additional Documents</h3>
            </div>
            <div class="card-body">
                <form action="" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add_documents">
                    <div id="documents-wrapper">
                        <div class="doc-row" id="doc-row-add-1">
                            <div>
                                <input type="text" name="doc_titles[]" class="form-control" placeholder="Document Title (e.g. Contract, Invoice)" required>
                            </div>
                            <div>
                                <input type="file" name="doc_files[]" class="form-control" accept=".pdf,.png,.jpg,.jpeg,.doc,.docx,.xls,.xlsx" required>
                            </div>
                            <div>
                                <button type="button" class="btn-remove-doc" onclick="removeDocRow('add-1')" title="Remove Field">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                </button>
                            </div>
                        </div>
                    </div>
                    <div style="display: flex; gap: 10px; margin-top: 12px;">
                        <button type="button" id="btn-add-document" class="btn btn-sm btn-secondary">+ Add Another Field</button>
                        <button type="submit" class="btn btn-sm btn-success">Upload Files Now</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Admin Status Update & Lead Assignment Card -->
    <div>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Manage Lead & Assignment</h3>
            </div>
            <div class="card-body">
                <form action="" method="POST">
                    <input type="hidden" name="action" value="update_lead">

                    <div class="form-group">
                        <label class="form-label" for="assigned_to">Assigned Sales Representative</label>
                        <select id="assigned_to" name="assigned_to" class="form-select" required>
                            <?php foreach ($allUsers as $u): ?>
                                <option value="<?php echo $u['id']; ?>" <?php echo $u['id'] == $lead['assigned_to'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($u['name']) . " (@" . htmlspecialchars($u['username']) . ")"; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="lead_status_id">Lead Status</label>
                        <select id="lead_status_id" name="lead_status_id" class="form-select" required>
                            <?php foreach ($leadStatuses as $ls): ?>
                                <option value="<?php echo $ls['id']; ?>" <?php echo $ls['id'] == $lead['lead_status_id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($ls['status_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="follow_up_date">Next Follow Up Date</label>
                        <input type="date" id="follow_up_date" name="follow_up_date" class="form-control" value="<?php echo htmlspecialchars($lead['follow_up_date'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="remarks">Remarks / Notes</label>
                        <textarea id="remarks" name="remarks" class="form-control" style="min-height: 120px;"><?php echo htmlspecialchars($lead['remarks'] ?? ''); ?></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary" style="width: 100%;">
                        Update Lead Record
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
