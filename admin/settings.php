<?php
// Set page title and required role
$page_title = "Settings";
$required_role = "admin"; // Can be adjusted based on your requirements

// Include necessary files
require_once '../includes/auth_check.php';
require_once '../includes/header.php';
require_once '../includes/db_config.php';

// Initialize variables
$error = '';
$success = '';
$user_data = [];
$business_data = [];
$password_error = '';

// Get current user data
try {
    $user_id = $_SESSION['user_id'];
    $query = "SELECT * FROM users WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user_data = $result->fetch_assoc();
    
    // Get business data if available
    if (isset($_SESSION['business_id'])) {
        $business_id = $_SESSION['business_id'];
        $query = "SELECT * FROM businesses WHERE business_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $business_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $business_data = $result->fetch_assoc();
    }
} catch (Exception $e) {
    $error = "Error fetching user data: " . $e->getMessage();
}

// Handle account settings form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_account'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    try {
        // Validate inputs
        if (empty($name) || empty($email)) {
            throw new Exception("Name and email are required.");
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Please enter a valid email address.");
        }
        
        // Check if email is already taken by another user
        $query = "SELECT id FROM users WHERE email = ? AND id != ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $email, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            throw new Exception("This email is already registered to another account.");
        }
        
        // Handle password change if requested
        $password_update = '';
        if (!empty($current_password)) {
            if (!password_verify($current_password, $user_data['password'])) {
                throw new Exception("Current password is incorrect.");
            }
            
            if (empty($new_password) || strlen($new_password) < 8) {
                throw new Exception("New password must be at least 8 characters long.");
            }
            
            if ($new_password !== $confirm_password) {
                throw new Exception("New passwords do not match.");
            }
            
            $password_update = ", password = ?";
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        }
        
        // Update user data
        if (empty($password_update)) {
            $query = "UPDATE users SET username = ?, email = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("ssi", $name, $email, $user_id);
        } else {
            $query = "UPDATE users SET username = ?, email = ?, password = ? WHERE id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssi", $name, $email, $hashed_password, $user_id);
        }
        
        $stmt->execute();
        
        // Update session data
        $_SESSION['username'] = $name;
        $_SESSION['email'] = $email;
        
        $success = "Account settings updated successfully!";
        
        // Refresh user data
        $query = "SELECT * FROM users WHERE id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $user_data = $result->fetch_assoc();
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Handle business settings form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_business'])) {
    $business_name = trim($_POST['business_name'] ?? '');
    $business_type = $_POST['business_type'] ?? '';
    $business_address = trim($_POST['business_address'] ?? '');
    $business_phone = trim($_POST['business_phone'] ?? '');
    $business_email = trim($_POST['business_email'] ?? '');
    
    try {
        // Validate inputs
        if (empty($business_name)) {
            throw new Exception("Business name is required.");
        }
        
        if (!empty($business_email) && !filter_var($business_email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Please enter a valid business email address.");
        }
        
        // Check if we're updating or creating a new business
        if (isset($_SESSION['business_id'])) {
            // Update existing business
            $query = "UPDATE businesses SET 
                     name = ?, 
                     type = ?, 
                     address = ?, 
                     phone = ?, 
                     email = ? 
                     WHERE business_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssssi", $business_name, $business_type, $business_address, 
                            $business_phone, $business_email, $_SESSION['business_id']);
        } else {
            // Create new business
            $query = "INSERT INTO businesses (name, type, address, phone, email, owner_id) 
                     VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("sssssi", $business_name, $business_type, $business_address, 
                            $business_phone, $business_email, $user_id);
        }
        
        $stmt->execute();
        
        // If new business, get the ID and store in session
        if (!isset($_SESSION['business_id'])) {
            $_SESSION['business_id'] = $conn->insert_id;
        }
        
        $success = "Business settings updated successfully!";
        
        // Refresh business data
        $query = "SELECT * FROM businesses WHERE business_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $_SESSION['business_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $business_data = $result->fetch_assoc();
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!-- Main Content -->
<div class="container-fluid">
    <div class="row">
        <?php include '../includes/sidebar_admin.php'; ?>
        
        <main role="main" class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
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
            
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Settings</h1>
            </div>

            <!-- Account Settings -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-user-cog me-2"></i>Account Settings</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?php echo htmlspecialchars($user_data['username'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($user_data['email'] ?? ''); ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Change Password (leave blank to keep current password)</label>
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <input type="password" class="form-control" name="current_password" 
                                           placeholder="Current Password">
                                </div>
                                <div class="col-md-4">
                                    <input type="password" class="form-control" name="new_password" 
                                           placeholder="New Password">
                                </div>
                                <div class="col-md-4">
                                    <input type="password" class="form-control" name="confirm_password" 
                                           placeholder="Confirm New Password">
                                </div>
                            </div>
                            <?php if ($password_error): ?>
                                <small class="text-danger"><?php echo $password_error; ?></small>
                            <?php endif; ?>
                            <small class="text-muted">Password must be at least 8 characters long.</small>
                        </div>
                        
                        <div class="d-flex justify-content-end">
                            <button type="submit" name="update_account" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Save Account Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Business Settings -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-building me-2"></i>Business Settings</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="business_name" class="form-label">Business Name</label>
                                <input type="text" class="form-control" id="business_name" name="business_name" 
                                       value="<?php echo htmlspecialchars($business_data['name'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6">
                                <label for="business_type" class="form-label">Business Type</label>
                                <select class="form-select" id="business_type" name="business_type" required>
                                    <option value="">Select business type</option>
                                    <option value="Farming" <?php echo (isset($business_data['type']) && $business_data['type'] == 'Farming') ? 'selected' : ''; ?>>Farming</option>
                                    <option value="Soap" <?php echo (isset($business_data['type']) && $business_data['type'] == 'Soap') ? 'selected' : ''; ?>>Soap Production</option>
                                    <option value="Salon" <?php echo (isset($business_data['type']) && $business_data['type'] == 'Salon') ? 'selected' : ''; ?>>Salon</option>
                                    <option value="Electronics" <?php echo (isset($business_data['type']) && $business_data['type'] == 'Electronics') ? 'selected' : ''; ?>>Electronics</option>
                                    <option value="General" <?php echo (isset($business_data['type']) && $business_data['type'] == 'General') ? 'selected' : ''; ?>>General Business</option>
                                    <option value="Home" <?php echo (isset($business_data['type']) && $business_data['type'] == 'Home') ? 'selected' : ''; ?>>Home Business</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="business_address" class="form-label">Business Address</label>
                            <textarea class="form-control" id="business_address" name="business_address" 
                                      rows="2"><?php echo htmlspecialchars($business_data['address'] ?? ''); ?></textarea>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="business_phone" class="form-label">Business Phone</label>
                                <input type="tel" class="form-control" id="business_phone" name="business_phone" 
                                       value="<?php echo htmlspecialchars($business_data['phone'] ?? ''); ?>">
                            </div>
                            <div class="col-md-6">
                                <label for="business_email" class="form-label">Business Email</label>
                                <input type="email" class="form-control" id="business_email" name="business_email" 
                                       value="<?php echo htmlspecialchars($business_data['email'] ?? ''); ?>">
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end">
                            <button type="submit" name="update_business" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Save Business Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- System Preferences -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-cog me-2"></i>System Preferences</h5>
                </div>
                <div class="card-body">
                    <form>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Theme Preference</label>
                                <select class="form-select">
                                    <option>Light</option>
                                    <option>Dark</option>
                                    <option>System Default</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Language</label>
                                <select class="form-select">
                                    <option>English</option>
                                    <option>Spanish</option>
                                    <option>French</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="notifications" checked>
                                <label class="form-check-label" for="notifications">Enable Email Notifications</label>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end">
                            <button type="button" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i> Save Preferences
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<?php 
// Include footer
require_once '../includes/footer.php';
?>