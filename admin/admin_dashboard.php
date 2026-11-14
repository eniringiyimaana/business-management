<?php
// Set page title and required role
$page_title = "Admin Dashboard";
$required_role = "admin";

// Include necessary files
require_once '../includes/auth_check.php';
require_once '../includes/header.php';
require_once '../includes/db_config.php';

// Initialize variables
$total_users = 0;
$total_businesses = 0;
$recent_activities = [];
$error = '';

try {
    // Get total users count
    $query = "SELECT COUNT(*) as total FROM users";
    $result = $conn->query($query);
    
    if ($result === false) {
        throw new Exception("Users count query failed: " . $conn->error);
    }
    $row = $result->fetch_assoc();
    $total_users = $row['total'];
    $result->free();

    // Get total businesses count
    $query = "SELECT COUNT(*) as total FROM businesses";
    $result = $conn->query($query);
    
    if ($result === false) {
        throw new Exception("Businesses count query failed: " . $conn->error);
    }
    $row = $result->fetch_assoc();
    $total_businesses = $row['total'];
    $result->free();

    // Get recent activities (last 5)
    $query = "SELECT * FROM reports ORDER BY generated_date DESC LIMIT 5";
    $result = $conn->query($query);
    
    if ($result === false) {
        throw new Exception("Recent activities query failed: " . $conn->error);
    }
    
    $recent_activities = [];
    while ($row = $result->fetch_assoc()) {
        $recent_activities[] = $row;
    }
    $result->free();

    // Get module statistics
    $modules = ['farming', 'soap', 'salon', 'electronics', 'general', 'home'];
    $module_stats = [];
    
    foreach ($modules as $module) {
        $module_stats[$module] = [
            'active' => 0,
            'inventory_items' => 0,
            'recent_sales' => 0
        ];
        
        // Count businesses for this module
        $query = "SELECT COUNT(*) FROM businesses WHERE type = '$module'";
        $result = $conn->query($query);
        if ($result !== false) {
            $module_stats[$module]['active'] = $result->fetch_row()[0];
            $result->free();
        }
        
        // Count inventory items
        $query = "SELECT COUNT(*) FROM inventory WHERE category = '$module'";
        $result = $conn->query($query);
        if ($result !== false) {
            $module_stats[$module]['inventory_items'] = $result->fetch_row()[0];
            $result->free();
        }
        
        // Get recent sales
        $query = "SELECT SUM(total_price) FROM sales WHERE business_id IN 
                 (SELECT business_id FROM businesses WHERE type = '$module') 
                 AND sale_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        $result = $conn->query($query);
        if ($result !== false) {
            $module_stats[$module]['recent_sales'] = $result->fetch_row()[0] ?? 0;
            $result->free();
        }
    }

} catch (Exception $e) {
    $error = "Error fetching dashboard data: " . $e->getMessage();
}


?>

<!-- Main Content -->
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
            
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Admin Dashboard</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <div class="btn-group me-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary">Export</button>
                    </div>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="card text-white bg-primary mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title">Total Users</h5>
                                    <h2 class="mb-0"><?php echo $total_users; ?></h2>
                                </div>
                                <i class="fas fa-users fa-3x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white bg-success mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title">Total Businesses</h5>
                                    <h2 class="mb-0"><?php echo $total_businesses; ?></h2>
                                </div>
                                <i class="fas fa-store fa-3x"></i>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white bg-info mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h5 class="card-title">Active Modules</h5>
                                    <h2 class="mb-0"><?php echo count($modules); ?></h2>
                                </div>
                                <i class="fas fa-th-large fa-3x"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Performance Chart -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i>Performance Overview</h5>
                </div>
                <div class="card-body">
                    <canvas id="performanceChart" height="250"></canvas>
                </div>
            </div>

            <!-- Recent Activities -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>Recent Activities</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($recent_activities)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Report ID</th>
                                        <th>Type</th>
                                        <th>Generated Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_activities as $activity): ?>
                                    <tr>
                                        <td><?php echo $activity['report_id']; ?></td>
                                        <td><?php echo $activity['report_type']; ?></td>
                                        <td><?php echo date('M j, Y H:i', strtotime($activity['generated_date'])); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">No recent activities found.</div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Module Statistics -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Module Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <?php foreach ($module_stats as $module => $stats): ?>
                        <div class="col-md-4 mb-3">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0 text-center">
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
                                        me-2"></i>
                                        <?php echo ucfirst($module); ?>
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <ul class="list-group list-group-flush">
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Active Businesses
                                            <span class="badge bg-primary rounded-pill"><?php echo $stats['active']; ?></span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Inventory Items
                                            <span class="badge bg-info rounded-pill"><?php echo $stats['inventory_items']; ?></span>
                                        </li>
                                        <li class="list-group-item d-flex justify-content-between align-items-center">
                                            Recent Sales (7 days)
                                            <span class="badge bg-success rounded-pill"><?php echo number_format($stats['recent_sales'], 2); ?></span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>





<?php 
// Custom JavaScript for charts
$custom_js = "
document.addEventListener('DOMContentLoaded', function() {
    // Performance Chart
    const ctx = document.getElementById('performanceChart').getContext('2d');
    const performanceChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Active Users',
                data: [12, 19, 15, 22, 24, 28],
                borderColor: '#28a745',
                backgroundColor: 'rgba(40, 167, 69, 0.1)',
                fill: true,
                tension: 0.3
            }, {
                label: 'New Businesses',
                data: [5, 8, 6, 10, 12, 15],
                borderColor: '#007bff',
                backgroundColor: 'rgba(0, 123, 255, 0.1)',
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
";

// Include footer
require_once '../includes/footer.php';
?>