<?php
// admin/users.php
$pageTitle = "User Management";
require_once __DIR__ . '/../includes/header.php';
requireAdmin();

$pdo = getDBConnection();
$msg = '';
$error = '';

// Handle Create User
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $name = trim($_POST['name'] ?? '');
    $number = trim($_POST['number'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $roleId = intval($_POST['role_id'] ?? 2);

    if (!empty($name) && !empty($number) && !empty($email) && !empty($username) && !empty($password)) {
        try {
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("
                INSERT INTO users (name, number, email, username, password, role_id) 
                VALUES (:name, :number, :email, :username, :password, :role_id)
            ");
            $stmt->execute([
                ':name' => $name,
                ':number' => $number,
                ':email' => $email,
                ':username' => $username,
                ':password' => $hashedPassword,
                ':role_id' => $roleId
            ]);
            $msg = "User account '{$name}' created successfully!";
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $error = "Failed to create user: Email or Username is already registered.";
            } else {
                $error = "Error creating user: " . $e->getMessage();
            }
        }
    } else {
        $error = "All fields (Name, Mobile Number, Email, Username, Password) are required.";
    }
}

// Handle Delete User
if (isset($_GET['delete'])) {
    $deleteId = intval($_GET['delete']);
    // Prevent deleting self
    if ($deleteId === $_SESSION['user_id']) {
        $error = "You cannot delete your own active administrator account.";
    } else {
        try {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
            $stmt->execute([':id' => $deleteId]);
            $msg = "User account deleted successfully!";
        } catch (PDOException $e) {
            $error = "Cannot delete user: User is currently assigned to existing leads.";
        }
    }
}

// Fetch all roles & users
$roles = $pdo->query("SELECT * FROM roles ORDER BY id ASC")->fetchAll();
$users = $pdo->query("
    SELECT u.*, r.role_name, COUNT(l.id) AS assigned_leads_count 
    FROM users u 
    JOIN roles r ON u.role_id = r.id 
    LEFT JOIN leads l ON u.id = l.assigned_to 
    GROUP BY u.id 
    ORDER BY u.id DESC
")->fetchAll();
?>

<?php if ($msg): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="form-grid" style="grid-template-columns: 1fr 2fr;">
    <!-- Add User Form Card -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Create New User Account</h3>
        </div>
        <div class="card-body">
            <form action="" method="POST">
                <input type="hidden" name="action" value="add">
                
                <div class="form-group">
                    <label class="form-label" for="name">Full Name <span class="required">*</span></label>
                    <input type="text" id="name" name="name" class="form-control" placeholder="e.g., Alex Morgan" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="number">Mobile Number <span class="required">*</span></label>
                    <input type="text" id="number" name="number" class="form-control" placeholder="e.g., 9876543210" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="email">Email Address <span class="required">*</span></label>
                    <input type="email" id="email" name="email" class="form-control" placeholder="e.g., alex@company.com" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="username">Username <span class="required">*</span></label>
                    <input type="text" id="username" name="username" class="form-control" placeholder="e.g., alex_m" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="password">Password <span class="required">*</span></label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
                </div>

                <div class="form-group">
                    <label class="form-label" for="role_id">Account Role <span class="required">*</span></label>
                    <select id="role_id" name="role_id" class="form-select" required>
                        <?php foreach ($roles as $r): ?>
                            <option value="<?php echo $r['id']; ?>" <?php echo $r['role_name'] === 'user' ? 'selected' : ''; ?>>
                                <?php echo ucfirst(htmlspecialchars($r['role_name'])); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%;">
                    Create User
                </button>
            </form>
        </div>
    </div>

    <!-- Users Table Card -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">User Accounts Directory</h3>
            <span class="badge badge-fresh"><?php echo count($users); ?> Total Users</span>
        </div>
        <div class="card-body" style="padding: 0;">
            <div class="table-search-bar" style="padding: 16px 24px 0 24px;">
                <div class="search-input-wrap">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    <input type="text" id="tableSearchInput" class="form-control" placeholder="Search users by name, email, phone...">
                </div>
            </div>
            <div class="table-responsive">
                <table class="custom-table">
                    <thead>
                        <tr>
                            <th>User Name</th>
                            <th>Contact / Email</th>
                            <th>Username</th>
                            <th>Role</th>
                            <th>Assigned Leads</th>
                            <th class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $u): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($u['name']); ?></strong>
                                    <div style="font-size: 11px; color: var(--text-muted);">Joined <?php echo date('d M Y', strtotime($u['created_at'])); ?></div>
                                </td>
                                <td>
                                    <div><?php echo htmlspecialchars($u['email']); ?></div>
                                    <div style="font-size: 11px; color: var(--text-muted);"><?php echo htmlspecialchars($u['number']); ?></div>
                                </td>
                                <td><code><?php echo htmlspecialchars($u['username']); ?></code></td>
                                <td>
                                    <span class="role-badge <?php echo htmlspecialchars($u['role_name']); ?>">
                                        <?php echo strtoupper(htmlspecialchars($u['role_name'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-fresh"><?php echo $u['assigned_leads_count']; ?> Leads</span>
                                </td>
                                <td class="text-right">
                                    <?php if ($u['id'] !== $_SESSION['user_id']): ?>
                                        <a href="?delete=<?php echo $u['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete user <?php echo htmlspecialchars($u['name']); ?>?');">
                                            Delete
                                        </a>
                                    <?php else: ?>
                                        <span style="font-size: 11px; color: var(--text-muted);">(You)</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
