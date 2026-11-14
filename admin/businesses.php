<?php
// Set page title and required role
$page_title = "Business Management";
$required_role = "admin";

// Include necessary files
require_once '../includes/auth_check.php';
require_once '../includes/header.php';
require_once '../includes/db_config.php';

// Initialize variables
$error = '';
$success = '';
$businesses = [];
$users = [];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        if (isset($_POST['add_business'])) {
            // Add new business
            $stmt = $conn->prepare("INSERT INTO businesses (business_name, business_type, owner_id, established_date, address, contact_number, email, description) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssisssss", 
                $_POST['business_name'],
                $_POST['business_type'],
                $_POST['owner_id'],
                $_POST['established_date'],
                $_POST['address'],
                $_POST['contact_number'],
                $_POST['email'],
                $_POST['description']
            );
            $stmt->execute();
            $success = "Business added successfully!";
        } elseif (isset($_POST['update_business'])) {
            // Update existing business
            $stmt = $conn->prepare("UPDATE businesses SET 
                                  business_name = ?,
                                  business_type = ?,
                                  owner_id = ?,
                                  established_date = ?,
                                  address = ?,
                                  contact_number = ?,
                                  email = ?,
                                  description = ?
                                  WHERE business_id = ?");
            $stmt->bind_param("ssisssssi", 
                $_POST['business_name'],
                $_POST['business_type'],
                $_POST['owner_id'],
                $_POST['established_date'],
                $_POST['address'],
                $_POST['contact_number'],
                $_POST['email'],
                $_POST['description'],
                $_POST['business_id']
            );
            $stmt->execute();
            $success = "Business updated successfully!";
        } elseif (isset($_POST['delete_business'])) {
            // Delete business
            $stmt = $conn->prepare("DELETE FROM businesses WHERE business_id = ?");
            $stmt->bind_param("i", $_POST['business_id']);
            $stmt->execute();
            $success = "Business deleted successfully!";
        }
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}

// Get all businesses
try {
    $query = "SELECT b.*, u.username as owner_name 
              FROM businesses b 
              LEFT JOIN users u ON b.owner_id = u.id
              ORDER BY b.business_name";
    $result = $conn->query($query);
    
    if ($result === false) {
        throw new Exception("Failed to fetch businesses: " . $conn->error);
    }
    
    while ($row = $result->fetch_assoc()) {
        $businesses[] = $row;
    }
    $result->free();

    // Get all users for dropdown
    $user_query = "SELECT id, username FROM users";
    $user_result = $conn->query($user_query);
    
    if ($user_result === false) {
        throw new Exception("Failed to fetch users: " . $conn->error);
    }
    
    while ($row = $user_result->fetch_assoc()) {
        $users[] = $row;
    }
    $user_result->free();

} catch (Exception $e) {
    $error = "Error: " . $e->getMessage();
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
                <h1 class="h2">Business Management</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addBusinessModal">
                        <i class="fas fa-plus me-2"></i>Add Business
                    </button>
                </div>
            </div>

            <!-- Business List -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-store me-2"></i>Business List</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($businesses)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Business Name</th>
                                        <th>Type</th>
                                        <th>Owner</th>
                                        <th>Established</th>
                                        <th>Contact</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($businesses as $business): ?>
                                    <tr>
                                        <td><?php echo $business['business_id']; ?></td>
                                        <td><?php echo htmlspecialchars($business['business_name']); ?></td>
                                        <td><?php echo ucfirst($business['business_type']); ?></td>
                                        <td><?php echo $business['owner_name'] ?? 'Unassigned'; ?></td>
                                        <td><?php echo $business['established_date'] ? date('M Y', strtotime($business['established_date'])) : 'N/A'; ?></td>
                                        <td><?php echo $business['contact_number'] ?? 'N/A'; ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary edit-btn" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#editBusinessModal"
                                                    data-id="<?php echo $business['business_id']; ?>"
                                                    data-name="<?php echo htmlspecialchars($business['business_name']); ?>"
                                                    data-type="<?php echo $business['business_type']; ?>"
                                                    data-owner="<?php echo $business['owner_id']; ?>"
                                                    data-date="<?php echo $business['established_date']; ?>"
                                                    data-address="<?php echo htmlspecialchars($business['address']); ?>"
                                                    data-contact="<?php echo $business['contact_number']; ?>"
                                                    data-email="<?php echo $business['email']; ?>"
                                                    data-desc="<?php echo htmlspecialchars($business['description']); ?>">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="business_id" value="<?php echo $business['business_id']; ?>">
                                                <button type="submit" name="delete_business" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this business?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">No businesses found.</div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Add Business Modal -->
<div class="modal fade" id="addBusinessModal" tabindex="-1" aria-labelledby="addBusinessModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="addBusinessModalLabel">Add New Business</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="business_name" class="form-label">Business Name</label>
                            <input type="text" class="form-control" id="business_name" name="business_name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="business_type" class="form-label">Business Type</label>
                            <select class="form-select" id="business_type" name="business_type" required>
                                <option value="farming">Farming</option>
                                <option value="soap">Soap Making</option>
                                <option value="salon">Salon</option>
                                <option value="electronics">Electronics</option>
                                <option value="general">General</option>
                                <option value="home">Home</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="owner_id" class="form-label">Owner</label>
                            <select class="form-select" id="owner_id" name="owner_id">
                                <option value="">Select Owner</option>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['username']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="established_date" class="form-label">Established Date</label>
                            <input type="date" class="form-control" id="established_date" name="established_date">
                        </div>
                        <div class="col-12">
                            <label for="address" class="form-label">Address</label>
                            <textarea class="form-control" id="address" name="address" rows="2"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label for="contact_number" class="form-label">Contact Number</label>
                            <input type="tel" class="form-control" id="contact_number" name="contact_number">
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email">
                        </div>
                        <div class="col-12">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="add_business" class="btn btn-primary">Add Business</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Business Modal -->
<div class="modal fade" id="editBusinessModal" tabindex="-1" aria-labelledby="editBusinessModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" id="edit_business_id" name="business_id">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="editBusinessModalLabel">Edit Business</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="edit_business_name" class="form-label">Business Name</label>
                            <input type="text" class="form-control" id="edit_business_name" name="business_name" required>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_business_type" class="form-label">Business Type</label>
                            <select class="form-select" id="edit_business_type" name="business_type" required>
                                <option value="farming">Farming</option>
                                <option value="soap">Soap Making</option>
                                <option value="salon">Salon</option>
                                <option value="electronics">Electronics</option>
                                <option value="general">General</option>
                                <option value="home">Home</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_owner_id" class="form-label">Owner</label>
                            <select class="form-select" id="edit_owner_id" name="owner_id">
                                <option value="">Select Owner</option>
                                <?php foreach ($users as $user): ?>
                                    <option value="<?php echo $user['id']; ?>"><?php echo htmlspecialchars($user['username']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_established_date" class="form-label">Established Date</label>
                            <input type="date" class="form-control" id="edit_established_date" name="established_date">
                        </div>
                        <div class="col-12">
                            <label for="edit_address" class="form-label">Address</label>
                            <textarea class="form-control" id="edit_address" name="address" rows="2"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label for="edit_contact_number" class="form-label">Contact Number</label>
                            <input type="tel" class="form-control" id="edit_contact_number" name="contact_number">
                        </div>
                        <div class="col-md-6">
                            <label for="edit_email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="edit_email" name="email">
                        </div>
                        <div class="col-12">
                            <label for="edit_description" class="form-label">Description</label>
                            <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name="update_business" class="btn btn-primary">Update Business</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php 
// Custom JavaScript for the edit modal
$custom_js = "
document.addEventListener('DOMContentLoaded', function() {
    // Handle edit button clicks
    document.querySelectorAll('.edit-btn').forEach(button => {
        button.addEventListener('click', function() {
            const modal = document.getElementById('editBusinessModal');
            
            // Set values from data attributes
            modal.querySelector('#edit_business_id').value = this.dataset.id;
            modal.querySelector('#edit_business_name').value = this.dataset.name;
            modal.querySelector('#edit_business_type').value = this.dataset.type;
            modal.querySelector('#edit_owner_id').value = this.dataset.owner;
            modal.querySelector('#edit_established_date').value = this.dataset.date;
            modal.querySelector('#edit_address').value = this.dataset.address || '';
            modal.querySelector('#edit_contact_number').value = this.dataset.contact || '';
            modal.querySelector('#edit_email').value = this.dataset.email || '';
            modal.querySelector('#edit_description').value = this.dataset.desc || '';
        });
    });
});
";

// Include footer
require_once '../includes/footer.php';
?>