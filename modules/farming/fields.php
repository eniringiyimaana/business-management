<?php
// Authentication and setup
$required_module = "farming";
$page_title = "Field Management";
require_once '../../includes/auth_check.php';
require_once '../../includes/db_config.php';
require_once '../../includes/header.php';

$business_id = $_SESSION['business_id'] ?? null;
$action = $_GET['action'] ?? 'list';
$field_id = $_GET['id'] ?? null;

// Initialize variables
$field_data = [
    'field_name' => '',
    'size_acres' => '',
    'soil_type' => 'Loam',
    'status' => 'Active'
];
$error = '';

// Handle form actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    
    try {
        // Validate input
        $field_name = trim($_POST['field_name']);
        $size_acres = (float)$_POST['size_acres'];
        $soil_type = $_POST['soil_type'];
        $status = $_POST['status'];
        
        if (empty($field_name)) {
            throw new Exception("Field name is required");
        }
        
        if ($size_acres <= 0) {
            throw new Exception("Field size must be positive");
        }

        if ($action === 'add' || $action === 'edit') {
            if ($action === 'add') {
                $stmt = $conn->prepare("INSERT INTO fields 
                    (business_id, field_name, size_acres, soil_type, status) 
                    VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("issss", $business_id, $field_name, $size_acres, $soil_type, $status);
            } else {
                $stmt = $conn->prepare("UPDATE fields SET 
                    field_name = ?, size_acres = ?, soil_type = ?, status = ?
                    WHERE field_id = ? AND business_id = ?");
                $stmt->bind_param("sdssii", $field_name, $size_acres, $soil_type, $status, $field_id, $business_id);
            }
            
            if (!$stmt->execute()) {
                throw new Exception("Database error: " . $stmt->error);
            }
            
            header("Location: fields.php?success=" . ($action === 'add' ? 'Field added' : 'Field updated'));
            exit();
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
        $field_data = $_POST;
    }
}

// Fetch field data for editing
if ($action === 'edit' && $field_id) {
    $stmt = $conn->prepare("SELECT * FROM fields WHERE field_id = ? AND business_id = ?");
    $stmt->bind_param("ii", $field_id, $business_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $field_data = $result->fetch_assoc();
    
    if (!$field_data) {
        header("Location: fields.php?error=Field not found");
        exit();
    }
}

// Handle deletion
if ($action === 'delete' && $field_id) {
    $stmt = $conn->prepare("DELETE FROM fields WHERE field_id = ? AND business_id = ?");
    $stmt->bind_param("ii", $field_id, $business_id);
    
    if ($stmt->execute()) {
        header("Location: fields.php?success=Field deleted");
    } else {
        header("Location: fields.php?error=Cannot delete field with existing tasks");
    }
    exit();
}
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../../includes/sidebar_admin.php'; ?>
        
        <main role="main" class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <!-- Success/Error Messages -->
            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?= htmlspecialchars($_GET['success']) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['error']) || $error): ?>
                <div class="alert alert-danger alert-dismissible fade show">
                    <?= htmlspecialchars($_GET['error'] ?? $error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Field Form (for Add/Edit) -->
            <?php if ($action === 'add' || $action === 'edit'): ?>
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-map-marked-alt me-2"></i>
                            <?= $action === 'add' ? 'Add New Field' : 'Edit Field' ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <input type="hidden" name="action" value="<?= $action ?>">
                            <?php if ($action === 'edit'): ?>
                                <input type="hidden" name="field_id" value="<?= $field_id ?>">
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <label for="field_name" class="form-label">Field Name</label>
                                <input type="text" class="form-control" id="field_name" name="field_name" 
                                    value="<?= htmlspecialchars($field_data['field_name']) ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="size_acres" class="form-label">Size (Acres)</label>
                                <input type="number" step="0.01" class="form-control" id="size_acres" name="size_acres" 
                                    value="<?= htmlspecialchars($field_data['size_acres']) ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="soil_type" class="form-label">Soil Type</label>
                                <select class="form-select" id="soil_type" name="soil_type" required>
                                    <option value="Loam" <?= $field_data['soil_type'] === 'Loam' ? 'selected' : '' ?>>Loam</option>
                                    <option value="Clay" <?= $field_data['soil_type'] === 'Clay' ? 'selected' : '' ?>>Clay</option>
                                    <option value="Sand" <?= $field_data['soil_type'] === 'Sand' ? 'selected' : '' ?>>Sand</option>
                                    <option value="Silt" <?= $field_data['soil_type'] === 'Silt' ? 'selected' : '' ?>>Silt</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="status" class="form-label">Status</label>
                                <select class="form-select" id="status" name="status" required>
                                    <option value="Active" <?= $field_data['status'] === 'Active' ? 'selected' : '' ?>>Active</option>
                                    <option value="Fallow" <?= $field_data['status'] === 'Fallow' ? 'selected' : '' ?>>Fallow</option>
                                    <option value="Maintenance" <?= $field_data['status'] === 'Maintenance' ? 'selected' : '' ?>>Maintenance</option>
                                </select>
                            </div>
                            
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <a href="fields.php" class="btn btn-secondary me-md-2">Cancel</a>
                                <button type="submit" class="btn btn-primary">Save Field</button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Fields List -->
            <?php if ($action === 'list'): ?>
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Field Management</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <a href="fields.php?action=add" class="btn btn-sm btn-outline-primary">
                            <i class="fas fa-plus me-1"></i> Add Field
                        </a>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Field Name</th>
                                        <th>Size (Acres)</th>
                                        <th>Soil Type</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $stmt = $conn->prepare("SELECT * FROM fields WHERE business_id = ? ORDER BY field_name");
                                    $stmt->bind_param("i", $business_id);
                                    $stmt->execute();
                                    $fields = $stmt->get_result();
                                    
                                    if ($fields->num_rows > 0):
                                        while ($field = $fields->fetch_assoc()):
                                    ?>
                                        <tr>
                                            <td><?= htmlspecialchars($field['field_name']) ?></td>
                                            <td><?= number_format($field['size_acres'], 2) ?></td>
                                            <td><?= htmlspecialchars($field['soil_type']) ?></td>
                                            <td>
                                                <span class="badge bg-<?= 
                                                    $field['status'] === 'Active' ? 'success' : 
                                                    ($field['status'] === 'Fallow' ? 'warning' : 'danger') 
                                                ?>">
                                                    <?= htmlspecialchars($field['status']) ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="fields.php?action=edit&id=<?= $field['field_id'] ?>" 
                                                   class="btn btn-sm btn-outline-primary me-1" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="fields.php?action=delete&id=<?= $field['field_id'] ?>" 
                                                   class="btn btn-sm btn-outline-danger" title="Delete"
                                                   onclick="return confirm('Are you sure you want to delete this field?')">
                                                    <i class="fas fa-trash-alt"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php 
                                        endwhile;
                                    else:
                                    ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-4">No fields found. <a href="fields.php?action=add">Add your first field</a></td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </main>
    </div>
</div>

<?php
// Custom JavaScript for field management
$custom_js = "
document.addEventListener('DOMContentLoaded', function() {
    // Confirm before deleting
    const deleteButtons = document.querySelectorAll('.btn-outline-danger');
    deleteButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            if (!confirm('Are you sure you want to delete this field?')) {
                e.preventDefault();
            }
        });
    });
    
    // Form validation
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', (e) => {
            const fieldName = form.querySelector('#field_name').value.trim();
            const sizeAcres = parseFloat(form.querySelector('#size_acres').value);
            
            if (!fieldName) {
                alert('Field name is required');
                e.preventDefault();
                return;
            }
            
            if (isNaN(sizeAcres) || sizeAcres <= 0) {
                alert('Please enter a valid positive number for field size');
                e.preventDefault();
                return;
            }
        });
    });
});
";

require_once '../../includes/footer.php';
?>