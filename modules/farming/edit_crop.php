<?php
// Database connection
require_once '../../includes/db_config.php';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_crop'])) {
    $crop_id = $_POST['crop_id'];
    $crop_name = mysqli_real_escape_string($conn, $_POST['crop_name']);
    $planting_date = mysqli_real_escape_string($conn, $_POST['planting_date']);
    $harvest_date = mysqli_real_escape_string($conn, $_POST['harvest_date']);
    $yield_quantity = mysqli_real_escape_string($conn, $_POST['yield_quantity']);

    $query = "UPDATE crops SET 
              crop_name = '$crop_name', 
              planting_date = '$planting_date', 
              harvest_date = '$harvest_date', 
              yield_quantity = '$yield_quantity' 
              WHERE crop_id = $crop_id";

    if (mysqli_query($conn, $query)) {
        $success = "Crop updated successfully!";
    } else {
        $error = "Error: " . mysqli_error($conn);
    }
}

// Get crop data if ID is provided
$crop = [];
if (isset($_GET['edit_id'])) {
    $crop_id = $_GET['edit_id'];
    $query = "SELECT * FROM crops WHERE crop_id = $crop_id";
    $result = mysqli_query($conn, $query);
    $crop = mysqli_fetch_assoc($result);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crop Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .crop-card {
            transition: transform 0.2s;
        }
        .crop-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-md-12">
                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <h2 class="mb-4">Your Crops</h2>
                
                <!-- Crop Listing -->
                <div class="row">
                    <?php
                    $query = "SELECT * FROM crops";
                    $result = mysqli_query($conn, $query);
                    while ($row = mysqli_fetch_assoc($result)):
                    ?>
                    <div class="col-md-4 mb-4">
                        <div class="card crop-card">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($row['crop_name']); ?></h5>
                                <p class="card-text">
                                    Planted: <?php echo htmlspecialchars($row['planting_date']); ?><br>
                                    Harvest: <?php echo htmlspecialchars($row['harvest_date']); ?><br>
                                    Yield: <?php echo htmlspecialchars($row['yield_quantity']); ?> kg
                                </p>
                                <button class="btn btn-sm btn-primary" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editModal"
                                        onclick="setEditForm(<?php echo $row['crop_id']; ?>, '<?php echo addslashes($row['crop_name']); ?>', '<?php echo $row['planting_date']; ?>', '<?php echo $row['harvest_date']; ?>', <?php echo $row['yield_quantity']; ?>)">
                                    Edit
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Crop</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="crop_id" id="edit_crop_id">
                        
                        <div class="mb-3">
                            <label class="form-label">Crop Name</label>
                            <input type="text" class="form-control" name="crop_name" id="edit_crop_name" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Planting Date</label>
                            <input type="date" class="form-control" name="planting_date" id="edit_planting_date" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Harvest Date</label>
                            <input type="date" class="form-control" name="harvest_date" id="edit_harvest_date" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Yield Quantity (kg)</label>
                            <input type="number" step="0.01" class="form-control" name="yield_quantity" id="edit_yield_quantity" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="update_crop" class="btn btn-primary">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function setEditForm(id, name, plantingDate, harvestDate, yield) {
        document.getElementById('edit_crop_id').value = id;
        document.getElementById('edit_crop_name').value = name;
        document.getElementById('edit_planting_date').value = plantingDate;
        document.getElementById('edit_harvest_date').value = harvestDate;
        document.getElementById('edit_yield_quantity').value = yield;
    }
    </script>
</body>
</html>

<?php
mysqli_close($conn);
?>