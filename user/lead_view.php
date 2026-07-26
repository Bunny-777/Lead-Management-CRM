<?php
// user/lead_view.php
$pageTitle = "Lead Details & Documents";
require_once __DIR__ . '/../includes/header.php';

$userId = $_SESSION['user_id'];
$leadId = isset($_GET['id']) ? intval($_GET['id']) : 0;
$pdo = getDBConnection();
$msg = '';
$error = '';

if ($leadId <= 0) {
    header("Location: /CRM%20P/user/lead_list.php");
    exit();
}

// Handle Upload New Documents ONLY
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
                        ':uploaded_by'   => $userId
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

// Fetch Lead Details with strict ownership check
$stmt = $pdo->prepare("
    SELECT l.*, 
           c.country_name, s.state_name, ct.city_name,
           lt.type_name, 
           ls.status_name, ls.color_code,
           u_assigned.name AS assigned_user_name,
           u_creator.name AS creator_name
    FROM leads l
    JOIN countries c ON l.country_id = c.id
    JOIN states s ON l.state_id = s.id
    JOIN cities ct ON l.city_id = ct.id
    JOIN lead_types lt ON l.lead_type_id = lt.id
    JOIN lead_statuses ls ON l.lead_status_id = ls.id
    JOIN users u_assigned ON l.assigned_to = u_assigned.id
    JOIN users u_creator ON l.created_by = u_creator.id
    WHERE l.id = :id AND l.assigned_to = :uid
");
$stmt->execute([':id' => $leadId, ':uid' => $userId]);
$lead = $stmt->fetch();

if (!$lead) {
    header("Location: /CRM%20P/user/lead_list.php?error=" . urlencode("Access Denied: You can only view leads assigned to you."));
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
    <a href="/CRM%20P/user/lead_list.php" class="btn btn-secondary">&larr; Back to My Leads</a>
</div>

<div class="alert alert-success" style="background-color: #f0fdf4; border-color: #bbf7d0; color: #166534;">
    <strong>User Policy:</strong> Lead master information is locked. You can review all details and upload updated files or supporting documents below.
</div>

<div class="form-grid" style="grid-template-columns: 2fr 1fr;">
    <div>
        <!-- Read-Only Company Details -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Company & Contact Master Information (Read-Only)</h3>
            </div>
            <div class="card-body">
                <div class="form-grid" style="grid-template-columns: 1fr 1fr;">
                    <div>
                        <label class="form-label">Company Name</label>
                        <div style="font-size: 15px; font-weight: 600; color: var(--text-main);"><?php echo htmlspecialchars($lead['company_name']); ?></div>
                    </div>
                    <div>
                        <label class="form-label">Owner / Contact Person</label>
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
                        <label class="form-label">Lead Type</label>
                        <div><span class="badge badge-fresh"><?php echo htmlspecialchars($lead['type_name']); ?></span></div>
                    </div>
                    <div>
                        <label class="form-label">Follow Up Date</label>
                        <div><?php echo $lead['follow_up_date'] ? date('d M Y', strtotime($lead['follow_up_date'])) : 'Not set'; ?></div>
                    </div>
                </div>

                <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid var(--border-color);">
                    <label class="form-label">Remarks / Notes</label>
                    <p style="font-size: 13.5px; color: #334155; background: #f8fafc; padding: 12px; border-radius: var(--radius-sm); border: 1px solid var(--border-color);">
                        <?php echo !empty($lead['remarks']) ? nl2br(htmlspecialchars($lead['remarks'])) : 'No remarks added.'; ?>
                    </p>
                </div>
            </div>
        </div>

        <!-- Document Attachments List -->
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
                                <th>Date</th>
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
                                        <td><?php echo date('d M Y', strtotime($doc['uploaded_at'])); ?></td>
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
    </div>

    <!-- User Upload Document Permission Card -->
    <div>
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">+ Upload New Document</h3>
            </div>
            <div class="card-body">
                <form action="" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="add_documents">
                    
                    <div id="documents-wrapper">
                        <div class="doc-row" id="doc-row-add-1">
                            <div>
                                <input type="text" name="doc_titles[]" class="form-control" placeholder="Document Title (e.g. Revised Requirements, KYC)" required>
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

                    <div style="display: flex; flex-direction: column; gap: 10px; margin-top: 16px;">
                        <button type="button" id="btn-add-document" class="btn btn-secondary" style="width: 100%;">+ Add Another Field</button>
                        <button type="submit" class="btn btn-success" style="width: 100%;">Upload Documents</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
