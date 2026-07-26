<?php
// admin/lead_create.php
$pageTitle = "Create New Lead";
require_once __DIR__ . '/../includes/header.php';
requireAdmin();

$pdo = getDBConnection();
$msg = '';
$error = '';

// Handle Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $companyName   = trim($_POST['company_name'] ?? '');
    $ownerName     = trim($_POST['owner_name'] ?? '');
    $mobile        = trim($_POST['mobile'] ?? '');
    $officialEmail = trim($_POST['official_email'] ?? '');
    $personalEmail = trim($_POST['personal_email'] ?? '');
    $countryId     = intval($_POST['country_id'] ?? 0);
    $stateId       = intval($_POST['state_id'] ?? 0);
    $cityId        = intval($_POST['city_id'] ?? 0);
    $leadTypeId    = intval($_POST['lead_type_id'] ?? 0);
    $leadStatusId  = intval($_POST['lead_status_id'] ?? 0);
    $followUpDate  = !empty($_POST['follow_up_date']) ? $_POST['follow_up_date'] : null;
    $remarks       = trim($_POST['remarks'] ?? '');
    $assignedTo    = intval($_POST['assigned_to'] ?? $_SESSION['user_id']);

    if (empty($companyName) || empty($ownerName) || empty($mobile) || empty($officialEmail) || 
        $countryId <= 0 || $stateId <= 0 || $cityId <= 0 || $leadTypeId <= 0 || $leadStatusId <= 0) {
        $error = "Please fill in all mandatory fields marked with an asterisk (*).";
    } else {
        try {
            $pdo->beginTransaction();

            $stmtLead = $pdo->prepare("
                INSERT INTO leads (
                    company_name, owner_name, mobile, official_email, personal_email,
                    country_id, state_id, city_id, lead_type_id, lead_status_id,
                    follow_up_date, remarks, created_by, assigned_to
                ) VALUES (
                    :company_name, :owner_name, :mobile, :official_email, :personal_email,
                    :country_id, :state_id, :city_id, :lead_type_id, :lead_status_id,
                    :follow_up_date, :remarks, :created_by, :assigned_to
                )
            ");

            $stmtLead->execute([
                ':company_name'   => $companyName,
                ':owner_name'     => $ownerName,
                ':mobile'         => $mobile,
                ':official_email' => $officialEmail,
                ':personal_email' => $personalEmail,
                ':country_id'     => $countryId,
                ':state_id'       => $stateId,
                ':city_id'        => $cityId,
                ':lead_type_id'   => $leadTypeId,
                ':lead_status_id' => $leadStatusId,
                ':follow_up_date' => $followUpDate,
                ':remarks'       => $remarks,
                ':created_by'     => $_SESSION['user_id'],
                ':assigned_to'    => $assignedTo
            ]);

            $leadId = $pdo->lastInsertId();

            // Process Up to 30 Document Uploads
            if (isset($_FILES['doc_files']) && is_array($_FILES['doc_files']['name'])) {
                $uploadDir = __DIR__ . '/../uploads/leads/';
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $docTitles = $_POST['doc_titles'] ?? [];
                $fileCount = count($_FILES['doc_files']['name']);
                
                // Enforce max 30 documents limit
                $maxToProcess = min($fileCount, 30);

                $stmtDoc = $pdo->prepare("
                    INSERT INTO lead_documents (
                        lead_id, document_title, file_name, original_name, file_path, file_type, file_size, uploaded_by
                    ) VALUES (
                        :lead_id, :title, :file_name, :original_name, :file_path, :file_type, :file_size, :uploaded_by
                    )
                ");

                for ($i = 0; $i < $maxToProcess; $i++) {
                    if ($_FILES['doc_files']['error'][$i] === UPLOAD_ERR_OK) {
                        $origName = $_FILES['doc_files']['name'][$i];
                        $tmpName  = $_FILES['doc_files']['tmp_name'][$i];
                        $fileSize = $_FILES['doc_files']['size'][$i];
                        $fileType = $_FILES['doc_files']['type'][$i];
                        $docTitle = !empty($docTitles[$i]) ? trim($docTitles[$i]) : "Document #" . ($i + 1);

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
                        }
                    }
                }
            }

            $pdo->commit();
            $msg = "Lead for '{$companyName}' created successfully with attachments!";
        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Error creating lead: " . $e->getMessage();
        }
    }
}

// Fetch Dropdown Options
$countries    = $pdo->query("SELECT * FROM countries ORDER BY country_name ASC")->fetchAll();
$leadTypes    = $pdo->query("SELECT * FROM lead_types ORDER BY type_name ASC")->fetchAll();
$leadStatuses = $pdo->query("SELECT * FROM lead_statuses ORDER BY id ASC")->fetchAll();
$users        = $pdo->query("SELECT id, name, username FROM users ORDER BY name ASC")->fetchAll();
?>

<?php if ($msg): ?>
    <div class="alert alert-success">
        <?php echo htmlspecialchars($msg); ?>
        <a href="/CRM%20P/admin/lead_list.php" style="margin-left: 12px; font-weight: 600; text-decoration: underline;">View All Leads &rarr;</a>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<form action="" method="POST" enctype="multipart/form-data">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">1. Company & Contact Details</h3>
        </div>
        <div class="card-body">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="company_name">Company Name <span class="required">*</span></label>
                    <input type="text" id="company_name" name="company_name" class="form-control" placeholder="e.g., Acme Tech Solutions" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="owner_name">Owner / Contact Person Name <span class="required">*</span></label>
                    <input type="text" id="owner_name" name="owner_name" class="form-control" placeholder="e.g., Robert Johnson" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="mobile">Mobile Number <span class="required">*</span></label>
                    <input type="text" id="mobile" name="mobile" class="form-control" placeholder="e.g., +91 9876543210" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="official_email">Official Email <span class="required">*</span></label>
                    <input type="email" id="official_email" name="official_email" class="form-control" placeholder="contact@acme.com" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="personal_email">Personal Email</label>
                    <input type="email" id="personal_email" name="personal_email" class="form-control" placeholder="robert.personal@gmail.com">
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">2. Geographic & Categorization Details</h3>
        </div>
        <div class="card-body">
            <div class="form-grid">
                <div class="form-group">
                    <label class="form-label" for="country_id">Country <span class="required">*</span></label>
                    <select id="country_id" name="country_id" class="form-select" required>
                        <option value="">-- Select Country --</option>
                        <?php foreach ($countries as $c): ?>
                            <option value="<?php echo $c['id']; ?>"><?php echo htmlspecialchars($c['country_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="state_id">State <span class="required">*</span></label>
                    <select id="state_id" name="state_id" class="form-select" required>
                        <option value="">-- Select Country First --</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="city_id">City <span class="required">*</span></label>
                    <select id="city_id" name="city_id" class="form-select" required>
                        <option value="">-- Select State First --</option>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="lead_type_id">Lead Type <span class="required">*</span></label>
                    <select id="lead_type_id" name="lead_type_id" class="form-select" required>
                        <option value="">-- Select Lead Type --</option>
                        <?php foreach ($leadTypes as $lt): ?>
                            <option value="<?php echo $lt['id']; ?>"><?php echo htmlspecialchars($lt['type_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="lead_status_id">Lead Status <span class="required">*</span></label>
                    <select id="lead_status_id" name="lead_status_id" class="form-select" required>
                        <option value="">-- Select Initial Status --</option>
                        <?php foreach ($leadStatuses as $ls): ?>
                            <option value="<?php echo $ls['id']; ?>"><?php echo htmlspecialchars($ls['status_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label" for="follow_up_date">Follow Up Date</label>
                    <input type="date" id="follow_up_date" name="follow_up_date" class="form-control">
                </div>

                <div class="form-group">
                    <label class="form-label" for="assigned_to">Assign To Sales Representative <span class="required">*</span></label>
                    <select id="assigned_to" name="assigned_to" class="form-select" required>
                        <?php foreach ($users as $u): ?>
                            <option value="<?php echo $u['id']; ?>" <?php echo $u['id'] == $_SESSION['user_id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($u['name']) . " (@" . htmlspecialchars($u['username']) . ")"; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group full-width" style="margin-top: 16px;">
                <label class="form-label" for="remarks">Remarks / Conversation Notes</label>
                <textarea id="remarks" name="remarks" class="form-control" placeholder="Enter any initial client notes, requirements, or discussion summary..."></textarea>
            </div>
        </div>
    </div>

    <!-- Up to 30 Dynamic Document Uploads Card -->
    <div class="card">
        <div class="card-header">
            <div style="display: flex; align-items: center; justify-content: space-between; width: 100%;">
                <div>
                    <h3 class="card-title">3. Attach Documents (Up to 30 Files Supported)</h3>
                    <p style="font-size: 12px; color: var(--text-muted); margin-top: 2px;">Supported types: PDF, PNG, JPEG, DOCX, XLSX, etc.</p>
                </div>
                <div style="display: flex; align-items: center; gap: 12px;">
                    <span id="doc-counter-label" class="badge badge-fresh">0 / 30 Attached</span>
                    <button type="button" id="btn-add-document" class="btn btn-sm btn-secondary">
                        + Add Document File
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div id="documents-wrapper">
                <!-- Initial 1st Document Field -->
                <div class="doc-row" id="doc-row-initial">
                    <div>
                        <input type="text" name="doc_titles[]" class="form-control" placeholder="Document Title (e.g. Requirement Brief, ID Proof)">
                    </div>
                    <div>
                        <input type="file" name="doc_files[]" class="form-control" accept=".pdf,.png,.jpg,.jpeg,.doc,.docx,.xls,.xlsx">
                    </div>
                    <div>
                        <button type="button" class="btn-remove-doc" onclick="removeDocRow('initial')" title="Remove Field">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div style="margin-bottom: 40px; text-align: right;">
        <a href="/CRM%20P/admin/lead_list.php" class="btn btn-secondary" style="margin-right: 12px;">Cancel</a>
        <button type="submit" class="btn btn-primary" style="padding: 12px 28px;">
            Save Lead Record
        </button>
    </div>
</form>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
