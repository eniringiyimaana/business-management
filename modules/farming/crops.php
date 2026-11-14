<?php
// Database connection
require_once '../../includes/db_config.php';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_crop'])) {
    // Input validation and sanitization
    $crop_name = trim($_POST["crop_name"] ?? "");
    $other_crop = trim($_POST["other_crop"] ?? "");
    $crop_variety = trim($_POST["crop_variety"] ?? "");
    $planting_date = $_POST["planting_date"] ?? "";
    $harvest_date = $_POST["harvest_date"] ?? "";
    $yield_quantity = $_POST["expected_yield"] ?? 0;

    // If "Other" is selected, use the specified name
    if ($crop_name === "other") {
        $crop_name = $other_crop;
    }

    // Validate required fields
    if (empty($crop_name) || empty($planting_date) || empty($harvest_date)) {
        $_SESSION['message'] = '<div class="alert alert-danger">All required fields must be filled!</div>';
        header("Location: crops.php");
        exit();
    }

    try {
        // Insert into database using prepared statement
        $stmt = $conn->prepare("INSERT INTO crops (business_id, crop_name, planting_date, harvest_date, yield_quantity, crop_variety) 
                              VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("isssis", $business_id, $crop_name, $planting_date, $harvest_date, $yield_quantity, $crop_variety);

        if ($stmt->execute()) {
            $_SESSION['message'] = '<div class="alert alert-success">Crop added successfully!</div>';
        } else {
            $_SESSION['message'] = '<div class="alert alert-danger">Error adding crop: ' . $conn->error . '</div>';
        }
        
        $stmt->close();
        header("Location: crops.php");
        exit();
    } catch (Exception $e) {
        $_SESSION['message'] = '<div class="alert alert-danger">Database error: ' . $e->getMessage() . '</div>';
        header("Location: crops.php");
        exit();
    }
}

// Include header
require_once '../../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Crops - Farming Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
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
        
        .badge-planted {
            background-color: rgba(52, 152, 219, 0.1);
            color: var(--primary-color);
        }
        
        .badge-growing {
            background-color: rgba(46, 204, 113, 0.1);
            color: var(--secondary-color);
        }
        
        .badge-harvested {
            background-color: rgba(241, 196, 15, 0.1);
            color: #f39c12;
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
<header class="bg-primary text-white p-3">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h1><i class="fas fa-seedling"></i> Manage Crops</h1>
                <p>Track and manage all your crop cultivation activities</p>
            </div>
            <a href="farming_dashboard.php" class="btn btn-light">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>
    </div>
</header>

<div class="container mt-4">
    <!-- Display session message -->
    <?php
    if (isset($_SESSION['message'])) {
        echo $_SESSION['message'];
        unset($_SESSION['message']); // Clear message after displaying
    }
    ?>

    <!-- Crop Entry Form -->
    <div class="card p-4 mb-4">
        <h3><i class="fas fa-plus-circle"></i> Add New Crop</h3>
        <form id="cropForm" method="POST" action="crops.php">
            <div class="row">
                <div class="col-md-6">
                    <label class="form-label">Crop Name *</label>
                    <select class="form-select" name="crop_name" id="cropName" required>
                        <option value="" selected disabled>Select crop</option>
                        <option value="maize">Maize</option>
                        <option value="wheat">Wheat</option>
                        <option value="rice">Rice</option>
                        <option value="soybeans">Soybeans</option>
                        <option value="other">Other (specify)</option>
                    </select>
                </div>

                <div class="col-md-6" id="otherCropContainer" style="display: none;">
                    <label class="form-label">Specify Crop Name *</label>
                    <input type="text" class="form-control" name="other_crop" id="otherCrop">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Variety/Type</label>
                    <input type="text" class="form-control" name="crop_variety" placeholder="e.g., Hybrid, Organic">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Planting Date *</label>
                    <input type="date" class="form-control" name="planting_date" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Expected Harvest Date *</label>
                    <input type="date" class="form-control" name="harvest_date" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Expected Yield (kg) *</label>
                    <input type="number" class="form-control" name="expected_yield" step="1" required>
                </div>
            </div>

            <button type="submit" name="submit_crop" class="btn btn-success mt-3"><i class="fas fa-save"></i> Save Crop</button>
        </form>
    </div>

    <!-- Crop Table -->
    <div class="card p-4">
        <h3><i class="fas fa-table"></i> All Crops</h3>
        <table class="table table-striped">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Crop Name</th>
                    <th>Variety</th>
                    <th>Planting Date</th>
                    <th>Harvest Date</th>
                    <th>Yield (kg)</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT * FROM crops";
                $result = $conn->query($sql);
                $counter = 1;

                if ($result->num_rows > 0) {
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>{$counter}</td>
                                <td>{$row['crop_name']}</td>
                                <td>{$row['crop_variety']}</td>
                                <td>{$row['planting_date']}</td>
                                <td>{$row['harvest_date']}</td>
                                <td>{$row['yield_quantity']}</td>
                                <td>
                                    <a href='edit_crop.php?id={$row['crop_id']}' class='btn btn-primary btn-sm'><i class='fas fa-edit'></i> Edit</a>
                                    <a href='delete_crop.php?id={$row['crop_id']}' class='btn btn-danger btn-sm' onclick='return confirm(\"Are you sure?\")'><i class='fas fa-trash'></i> Delete</a>
                                </td>
                              </tr>";
                        $counter++;
                    }
                } else {
                    echo "<tr><td colspan='7' class='text-center'>No crops added yet.</td></tr>";
                }

                $conn->close();
                ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    document.getElementById('cropName').addEventListener('change', function() {
        document.getElementById('otherCropContainer').style.display = (this.value === 'other') ? 'block' : 'none';
    });
</script>
</body>


</html>
