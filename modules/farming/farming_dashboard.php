<?php
// Set required module and include auth check
$required_module = "farming";
require_once '../../includes/auth_check.php';

// Set page title
$page_title = "Farming Dashboard";

// Include database connection
require_once '../../includes/db_config.php';

// Get business ID from session or URL parameter
$business_id = $_SESSION['business_id'] ?? null; // You'll need to set this during login

// Fetch farming data for this specific business
$cropsQuery = "SELECT COUNT(DISTINCT crop_name) AS total_crops FROM crops WHERE business_id = ?";
$stmt = $conn->prepare($cropsQuery);
$stmt->bind_param("i", $business_id);
$stmt->execute();
$cropsResult = $stmt->get_result();
$cropsRow = $cropsResult->fetch_assoc();
$totalCrops = $cropsRow['total_crops'] ?? 0;

// Similarly for livestock
$livestockQuery = "SELECT COUNT(*) AS total_livestock FROM livestock WHERE business_id = ?";
$stmt = $conn->prepare($livestockQuery);
$stmt->bind_param("i", $business_id);
$stmt->execute();
$livestockResult = $stmt->get_result();
$livestockRow = $livestockResult->fetch_assoc();
$totalLivestock = $livestockRow['total_livestock'] ?? 0;

// Include the main header
require_once '../../includes/header.php';
?>

<!-- Main Content -->
<div class="container-fluid">
    <div class="row">
        <?php 
        // Include the appropriate sidebar based on user role
        if ($_SESSION['role'] === 'admin') {
            require_once '../../includes/sidebar_admin.php';
        } else {
            require_once '../../includes/sidebar_user.php';
        }
        ?>
        
        <main role="main" class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
            <!-- Dashboard Header -->
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Farming Dashboard</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary">Export</button>
                    </div>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card text-white bg-primary mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title">Total Crop Types</h5>
                                    <h2 class="mb-0"><?php echo $totalCrops; ?></h2>
                                </div>
                                <i class="fas fa-seedling fa-3x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white bg-success mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title">Total Livestock</h5>
                                    <h2 class="mb-0"><?php echo $totalLivestock; ?></h2>
                                </div>
                                <i class="fas fa-paw fa-3x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white bg-info mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title">Active Fields</h5>
                                    <h2 class="mb-0">5</h2>
                                </div>
                                <i class="fas fa-tractor fa-3x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Farming Sections -->
            <!-- In your HTML where the charts are defined -->
<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-seedling me-2"></i>Crop Management</h5>
            </div>
            <div class="card-body text-center"> <!-- Added text-center -->
    <div style="height: 300px; width: 300px; margin: 0 auto;"> <!-- Constrained container -->
        <canvas id="cropChart"></canvas>
    </div>
    <a href="crops.php" class="btn btn-primary mt-3">Manage Crops</a>
</div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-paw me-2"></i>Livestock Management</h5>
            </div>
            <div class="card-body">
                <!-- Reduced height from 200 to 150 -->
                <canvas id="livestockChart" height="150"></canvas>
                <a href="livestock.php" class="btn btn-primary mt-3">Manage Livestock</a>
            </div>
        </div>
    </div>
</div>

            <!-- Recent Activity -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Farming Activity</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Activity</th>
                                    <th>Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><?php echo date('Y-m-d'); ?></td>
                                    <td>Crop Planting</td>
                                    <td>Maize planted in Field A</td>
                                </tr>
                                <tr>
                                    <td><?php echo date('Y-m-d', strtotime('-1 day')); ?></td>
                                    <td>Livestock Check</td>
                                    <td>Cattle health inspection completed</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php
// Custom JavaScript for farming dashboard
$custom_js = "
document.addEventListener('DOMContentLoaded', function() {
    // Crop Chart - Now much smaller
    const cropCtx = document.getElementById('cropChart').getContext('2d');
    const cropChart = new Chart(cropCtx, {
        type: 'doughnut',
        data: {
            labels: ['Planted', 'Growing', 'Harvest Ready'],
            datasets: [{
                data: [30, 45, 25],
                backgroundColor: ['#3498db', '#2ecc71', '#f39c12'],
                borderWidth: 0 // Removes border lines
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '75%', // Larger hole = smaller visible chart
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        boxWidth: 8,  // Tiny legend boxes
                        padding: 5,   // Compact spacing
                        font: {
                            size: 9    // Smaller text
                        }
                    }
                }
            }
        }
    });

    // Livestock Chart
    const livestockCtx = document.getElementById('livestockChart').getContext('2d');
    const livestockChart = new Chart(livestockCtx, {
        type: 'bar',
        data: {
            labels: ['Cattle', 'Poultry', 'Sheep', 'Goats'],
            datasets: [{
                label: 'Count',
                data: [45, 120, 60, 40],
                backgroundColor: '#2ecc71'
            }]
        }
    });
});
";

// Include the main footer
require_once '../../includes/footer.php';
?>