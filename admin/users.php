<?php
// Set page title and required role first
$page_title = "User Management";
$required_role = "admin"; // Only admins should access this page

// Then include auth_check which will handle session
// Then include auth_check which will handle session
require_once '../includes/auth_check.php';
require_once '../includes/header.php';
require_once '../includes/sidebar_admin.php';  // Add this line
require_once '../includes/db_config.php';

// Check if user is admin
$is_admin = ($_SESSION['role'] === 'admin');

// Initialize variables
$users = [];
$error = '';
$success = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_user'])) {
        // Add new user
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $role = $_POST['role'];
        
        try {
            $stmt = $conn->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $username, $email, $password, $role);
            $stmt->execute();
            $success = "User added successfully!";
        } catch (Exception $e) {
            $error = "Error adding user: " . $e->getMessage();
        }
    } elseif (isset($_POST['update_user'])) {
        // Update user
        $user_id = $_POST['user_id'];
        $username = trim($_POST['username']);
        $email = trim($_POST['email']);
        $role = $_POST['role'];
        
        try {
            $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, role = ? WHERE id = ?");
            $stmt->bind_param("sssi", $username, $email, $role, $user_id);
            $stmt->execute();
            $success = "User updated successfully!";
        } catch (Exception $e) {
            $error = "Error updating user: " . $e->getMessage();
        }
    } elseif (isset($_POST['delete_user'])) {
        // Delete user (soft delete)
        $user_id = $_POST['user_id'];
        
        try {
            $stmt = $conn->prepare("UPDATE users SET is_active = 0 WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $success = "User deactivated successfully!";
        } catch (Exception $e) {
            $error = "Error deactivating user: " . $e->getMessage();
        }
    } elseif (isset($_POST['activate_user'])) {
        // Activate user
        $user_id = $_POST['user_id'];
        
        try {
            $stmt = $conn->prepare("UPDATE users SET is_active = 1 WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $success = "User activated successfully!";
        } catch (Exception $e) {
            $error = "Error activating user: " . $e->getMessage();
        }
    } elseif (isset($_POST['update_privileges'])) {
        // Update user privileges
        $user_id = $_POST['user_id'];
        $modules = isset($_POST['modules']) ? $_POST['modules'] : [];
        
        try {
            // First clear existing privileges
            $conn->query("DELETE FROM user_privileges WHERE user_id = $user_id");
            
            // Add new privileges
            foreach ($modules as $module) {
                $conn->query("INSERT INTO user_privileges (user_id, module) VALUES ($user_id, '$module')");
            }
            
            $success = "Privileges updated successfully!";
        } catch (Exception $e) {
            $error = "Error updating privileges: " . $e->getMessage();
        }
    }
}

// Get all users (including inactive ones for admin)
$query = $is_admin ? "SELECT * FROM users ORDER BY created_at DESC" : "SELECT * FROM users WHERE is_active = 1 ORDER BY created_at DESC";
$users_result = $conn->query($query);
if ($users_result) {
    $users = $users_result->fetch_all(MYSQLI_ASSOC);
}

// Get all available modules
$modules = ['farming', 'soap', 'salon', 'electronics', 'general', 'home'];
?>

<!-- Main Content -->
<main role="main" class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2"><i class="fas fa-users-cog me-2"></i>User Management</h1>
        <div class="btn-toolbar mb-2 mb-md-0">
            <?php if ($is_admin): ?>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                <i class="fas fa-user-plus me-1"></i> Add User
            </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Alerts -->
    <?php if ($error): ?>
    <div class="alert alert-danger alert-dismissible fade show">
        <?php echo $error; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <?php if ($success): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?php echo $success; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- Users Table -->
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fas fa-users me-2"></i>System Users</h5>
            <?php if ($is_admin): ?>
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" id="showInactiveToggle">
                <label class="form-check-label text-white" for="showInactiveToggle">Show Inactive</label>
            </div>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>User</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Joined</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                        <tr class="<?php echo ($user['is_active'] ?? 1) ? '' : 'table-secondary'; ?>">
                            <td><?php echo $user['id']; ?></td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user['username']); ?>&background=<?php echo $user['role'] === 'admin' ? 'dc3545' : '3498db'; ?>&color=fff" 
                                         class="user-avatar me-2">
                                    <div>
                                        <div><?php echo htmlspecialchars($user['username']); ?></div>
                                        <small class="text-muted">Last login: <?php echo isset($user['last_login']) ? date('M j, Y', strtotime($user['last_login'])) : 'Never'; ?></small>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <span class="badge bg-<?php echo $user['role'] === 'admin' ? 'danger' : 'primary'; ?>">
                                    <?php echo ucfirst($user['role']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <span class="badge rounded-pill status-badge bg-<?php echo ($user['is_active'] ?? 1) ? 'success' : 'secondary'; ?>">
                                    <?php echo ($user['is_active'] ?? 1) ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td class="action-buttons">
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#privilegesModal<?php echo $user['id']; ?>">
                                        <i class="fas fa-key"></i>
                                    </button>
                                    <?php if ($is_admin): ?>
                                    <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editUserModal<?php echo $user['id']; ?>">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <?php if ($user['is_active'] ?? 1): ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" name="delete_user" class="btn btn-sm btn-outline-danger">
                                            <i class="fas fa-user-slash"></i>
                                        </button>
                                    </form>
                                    <?php else: ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <button type="submit" name="activate_user" class="btn btn-sm btn-outline-success">
                                            <i class="fas fa-user-check"></i>
                                        </button>
                                    </form>
                                    <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<!-- Add User Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Add New User</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" name="username" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control" name="password" id="passwordField" required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="form-text">Minimum 8 characters</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select class="form-select" name="role" required>
                            <option value="user">User</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="add_user" class="btn btn-primary">Add User</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit User Modals -->
<?php foreach ($users as $user): ?>
<div class="modal fade" id="editUserModal<?php echo $user['id']; ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Edit User: <?php echo htmlspecialchars($user['username']); ?></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Username</label>
                        <input type="text" class="form-control" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select class="form-select" name="role" required>
                            <option value="user" <?php echo $user['role'] === 'user' ? 'selected' : ''; ?>>User</option>
                            <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <div>
                            <span class="badge bg-<?php echo ($user['is_active'] ?? 1) ? 'success' : 'secondary'; ?>">
                                <?php echo ($user['is_active'] ?? 1) ? 'Active' : 'Inactive'; ?>
                            </span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_user" class="btn btn-primary">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endforeach; ?>

<!-- Privileges Modals -->
<?php foreach ($users as $user): 
    // Get user's current privileges
    $privileges_result = $conn->query("SELECT module FROM user_privileges WHERE user_id = {$user['id']}");
    $user_modules = [];
    if ($privileges_result) {
        while ($row = $privileges_result->fetch_assoc()) {
            $user_modules[] = $row['module'];
        }
    }
?>
<div class="modal fade" id="privilegesModal<?php echo $user['id']; ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-key me-2"></i>
                        Manage Privileges for <?php echo htmlspecialchars($user['username']); ?>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Select the modules this user should have access to.
                    </div>
                    <div class="row g-3">
                        <?php foreach ($modules as $module): ?>
                        <div class="col-md-4">
                            <div class="card privilege-card h-100">
                                <div class="card-body">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="modules[]" 
                                               value="<?php echo $module; ?>" 
                                               id="module_<?php echo $user['id']; ?>_<?php echo $module; ?>"
                                               <?php echo in_array($module, $user_modules) ? 'checked' : ''; ?>>
                                        <label class="form-check-label d-flex align-items-center" for="module_<?php echo $user['id']; ?>_<?php echo $module; ?>">
                                            <i class="fas 
                                                <?php 
                                                    switch($module) {
                                                        case 'farming': echo 'fa-tractor'; break;
                                                        case 'soap': echo 'fa-pump-soap'; break;
                                                        case 'salon': echo 'fa-cut'; break;
                                                        case 'electronics': echo 'fa-laptop'; break;
                                                        case 'general': echo 'fa-briefcase'; break;
                                                        case 'home': echo 'fa-home'; break;
                                                    }
                                                ?> 
                                            module-icon"></i>
                                            <span><?php echo ucfirst($module); ?></span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="update_privileges" class="btn btn-primary">Save Privileges</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endforeach; ?>

<?php 
// Custom JavaScript for this page
$custom_js = "
document.addEventListener('DOMContentLoaded', function() {
    // Toggle password visibility
    const togglePassword = document.getElementById('togglePassword');
    if (togglePassword) {
        togglePassword.addEventListener('click', function() {
            const passwordField = document.getElementById('passwordField');
            const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordField.setAttribute('type', type);
            this.innerHTML = type === 'password' ? '<i class=\"fas fa-eye\"></i>' : '<i class=\"fas fa-eye-slash\"></i>';
        });
    }
    
    // Toggle inactive users visibility
    const showInactiveToggle = document.getElementById('showInactiveToggle');
    if (showInactiveToggle) {
        showInactiveToggle.addEventListener('change', function() {
            const rows = document.querySelectorAll('tbody tr.table-secondary');
            rows.forEach(row => {
                row.style.display = this.checked ? '' : 'none';
            });
        });
        
        // Initialize to hide inactive users
        const rows = document.querySelectorAll('tbody tr.table-secondary');
        rows.forEach(row => {
            row.style.display = showInactiveToggle.checked ? '' : 'none';
        });
    }
});
";

// Include footer
require_once '../includes/footer.php';
?>