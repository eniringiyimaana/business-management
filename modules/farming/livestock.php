<?php
// Include database connection
require_once '../../includes/db_config.php'; // New version

// Handle new livestock entry
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['addLivestock'])) {
    $animalType = mysqli_real_escape_string($conn, $_POST['animalType']);
    $purchaseDate = mysqli_real_escape_string($conn, $_POST['purchaseDate']);
    $healthStatus = mysqli_real_escape_string($conn, $_POST['healthStatus']);
    $businessId = mysqli_real_escape_string($conn, $_POST['businessId']);

    // Check if business_id exists in the businesses table
    $checkBusiness = "SELECT business_id FROM businesses WHERE business_id = '$businessId'";
    $businessResult = mysqli_query($conn, $checkBusiness);

    if (mysqli_num_rows($businessResult) > 0) {
        $insertQuery = "INSERT INTO livestock (business_id, animal_type, purchase_date, health_status) 
                        VALUES ('$businessId', '$animalType', '$purchaseDate', '$healthStatus')";
        mysqli_query($conn, $insertQuery);
    }
}

// Handle update livestock
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['editLivestock'])) {
    $livestockId = mysqli_real_escape_string($conn, $_POST['livestockId']);
    $animalType = mysqli_real_escape_string($conn, $_POST['animalType']);
    $purchaseDate = mysqli_real_escape_string($conn, $_POST['purchaseDate']);
    $healthStatus = mysqli_real_escape_string($conn, $_POST['healthStatus']);

    $updateQuery = "UPDATE livestock SET 
                    animal_type='$animalType', 
                    purchase_date='$purchaseDate', 
                    health_status='$healthStatus' 
                    WHERE livestock_id='$livestockId'";

    mysqli_query($conn, $updateQuery);
}

// Handle delete livestock
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['deleteLivestock'])) {
    $livestockId = mysqli_real_escape_string($conn, $_POST['livestockId']);
    mysqli_query($conn, "DELETE FROM livestock WHERE livestock_id='$livestockId'");
}

// Fetch livestock data
$result = mysqli_query($conn, "SELECT * FROM livestock");
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Livestock - Farming Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-datepicker@1.9.0/dist/css/bootstrap-datepicker.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #2ecc71;
            --accent-color: #e74c3c;
            --dark-color: #2c3e50;
            --light-color: #ecf0f1;
        }
        
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: var(--dark-color);
        }
        
        /* Header Styles */
        .page-header {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
            border-radius: 0 0 15px 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        
        .page-title {
            font-weight: 700;
            text-shadow: 1px 1px 3px rgba(0, 0, 0, 0.2);
        }
        
        /* Form Styles */
        .form-container {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .form-label {
            font-weight: 600;
            color: var(--dark-color);
        }
        
        .form-control, .form-select, .select2-selection {
            border-radius: 8px;
            padding: 0.75rem 1rem;
            border: 1px solid #ced4da;
            transition: all 0.3s;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(52, 152, 219, 0.25);
        }
        
        /* Button Styles */
        .btn-submit {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            border-radius: 50px;
            transition: all 0.3s;
        }
        
        .btn-submit:hover {
            background-color: #27ae60;
            border-color: #27ae60;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }
        
        /* Table Styles */
        .table-container {
            background-color: white;
            border-radius: 15px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            padding: 2rem;
            overflow-x: auto;
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table thead th {
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
            border-bottom: none;
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(52, 152, 219, 0.05);
        }
        
        /* Action Buttons */
        .btn-action {
            border: none;
            background: none;
            padding: 0.5rem;
            border-radius: 50%;
            width: 35px;
            height: 35px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
        }
        
        .btn-edit {
            color: var(--primary-color);
        }
        
        .btn-edit:hover {
            background-color: rgba(52, 152, 219, 0.1);
            transform: scale(1.1);
        }
        
        .btn-delete {
            color: var(--accent-color);
        }
        
        .btn-delete:hover {
            background-color: rgba(231, 76, 60, 0.1);
            transform: scale(1.1);
        }
        
        /* Status Badges */
        .badge-status {
            padding: 0.5rem 0.75rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.8rem;
        }
        
        .badge-healthy {
            background-color: rgba(46, 204, 113, 0.1);
            color: var(--secondary-color);
        }
        
        .badge-sick {
            background-color: rgba(231, 76, 60, 0.1);
            color: var(--accent-color);
        }
        
        .badge-pregnant {
            background-color: rgba(155, 89, 182, 0.1);
            color: #9b59b6;
        }
        
        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .page-header {
                padding: 1.5rem 0;
            }
            
            .page-title {
                font-size: 1.5rem;
            }
            
            .form-container, .table-container {
                padding: 1.5rem;
            }
        }
    </style>
</head>

<body>
    <!-- Page Header -->
    <header class="page-header">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="page-title"><i class="fas fa-paw me-2"></i>Manage Livestock</h1>
                <p class="mb-0">Track and manage all your livestock and animal husbandry activities</p>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="farming_dashboard.php" class="btn btn-light me-2">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
                <button class="btn btn-light me-2"><i class="fas fa-file-export me-1"></i>Export</button>
                <button class="btn btn-light"><i class="fas fa-print me-1"></i>Print</button>
            </div>
        </div>
    </div>
</header>

    <div class="container">
        <!-- Livestock Entry Form -->
        <div class="form-container">
            <h3 class="mb-4"><i class="fas fa-plus-circle me-2"></i>Add New Livestock</h3>
            <form action="" method="POST">
    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="animalType" class="form-label">Animal Type *</label>
            <select class="form-select" name="animalType" required>
                <option value="" selected disabled>Select animal type</option>
                <option value="cattle">Cattle</option>
                <option value="poultry">Poultry</option>
                <option value="sheep">Sheep</option>
                <option value="goats">Goats</option>
                <option value="pigs">Pigs</option>
                <option value="other">Other (specify)</option>
            </select>
        </div>
        <div class="col-md-6 mb-3">
            <label for="purchaseDate" class="form-label">Purchase Date *</label>
            <input type="date" class="form-control" name="purchaseDate" required>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-3">
            <label for="healthStatus" class="form-label">Health Status *</label>
            <select class="form-select" name="healthStatus" required>
                <option value="healthy">Healthy</option>
                <option value="sick">Sick</option>
                <option value="injured">Injured</option>
                <option value="pregnant">Pregnant</option>
                <option value="other">Other</option>
            </select>
        </div>
        <div class="col-md-6 mb-3">
            <label for="businessId" class="form-label">Business ID *</label>
            <input type="text" class="form-control" name="businessId" placeholder="Enter business ID" required>
        </div>
    </div>

    <div class="d-flex justify-content-between">
        <button type="reset" class="btn btn-outline-secondary">Clear Form</button>
        <button type="submit" class="btn btn-submit"><i class="fas fa-save me-2"></i>Save Livestock</button>
    </div>
</form>

        </div>

        <!-- Livestock List Table -->
        <div class="table-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3><i class="fas fa-list me-2"></i>Current Livestock</h3>
            </div>

            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Type</th>
                            <th>Purchase Date</th>
                            <th>Health</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                            <tr>
                                <td><?= htmlspecialchars($row['livestock_id']) ?></td>
                                <td><?= htmlspecialchars($row['animal_type']) ?></td>
                                <td><?= htmlspecialchars($row['purchase_date']) ?></td>
                                <td><span class="badge-status"><?= htmlspecialchars($row['health_status']) ?></span></td>
                                <td>
                                    <a href="edit_livestock.php?id=<?= $row['livestock_id'] ?>" class="btn-action btn-edit" title="Edit"><i class="fas fa-edit"></i></a>
                                    <a href="delete_livestock.php?id=<?= $row['livestock_id'] ?>" class="btn-action btn-delete" title="Delete" onclick="return confirm('Are you sure?');"><i class="fas fa-trash"></i></a>
                                    <a href="view_livestock.php?id=<?= $row['livestock_id'] ?>" class="btn-action" title="View Details"><i class="fas fa-eye"></i></a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>


    <!-- External Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    
</body>

</html>
