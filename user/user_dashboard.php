<?php
session_start();

// Authentication check
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// In user_dashboard.php
require_once($_SERVER['DOCUMENT_ROOT'] . '/business management/includes/db_config.php');


// Rest of your code

// Get user-specific data
$user_id = $_SESSION['user_id'];
$user_data = [];
$result = mysqli_query($conn, "SELECT * FROM users WHERE id = $user_id");
if ($result && mysqli_num_rows($result) > 0) {
    $user_data = mysqli_fetch_assoc($result);
}

// Get assigned modules
$modules = [];
if ($_SESSION['role'] === 'user') {
    // For regular users, only show modules they have access to
    $modules = ['farming', 'salon']; // Example - should come from database
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Dashboard - Business Management System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #3498db;
            --secondary: #2c3e50;
            --success: #28a745;
            --light: #f8f9fa;
        }
        
        .module-card {
            transition: all 0.3s;
            border-left: 4px solid var(--primary);
        }
        
        .module-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .module-icon {
            font-size: 2.5rem;
            color: var(--primary);
        }
        
        .user-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-2 d-none d-md-block bg-dark sidebar">
                <div class="sidebar-sticky pt-3">
                    <div class="sidebar-header text-center text-white mb-4">
                        <h4><i class="fas fa-chart-line"></i> Business Manager</h4>
                        <small>User Dashboard</small>
                    </div>
                    
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="user_dashboard.php">
                                <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="profile.php">
                                <i class="fas fa-user mr-2"></i> My Profile
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="#" data-bs-toggle="collapse" data-bs-target="#modulesCollapse">
                                <i class="fas fa-th-large mr-2"></i> My Modules
                            </a>
                            <div class="collapse show" id="modulesCollapse">
                                <ul class="nav flex-column pl-3">
                                    <?php if (in_array('farming', $modules)): ?>
                                    <li class="nav-item">
                                        <a class="nav-link" href="farming_dashboard.php">
                                            <i class="fas fa-tractor mr-2"></i> Farming
                                        </a>
                                    </li>
                                    <?php endif; ?>
                                    <?php if (in_array('soap', $modules)): ?>
                                    <li class="nav-item">
                                        <a class="nav-link" href="soap_dashboard.php">
                                            <i class="fas fa-pump-soap mr-2"></i> Soap Making
                                        </a>
                                    </li>
                                    <?php endif; ?>
                                    <?php if (in_array('salon', $modules)): ?>
                                    <li class="nav-item">
                                        <a class="nav-link" href="salon_dashboard.php">
                                            <i class="fas fa-cut mr-2"></i> Salon
                                        </a>
                                    </li>
                                    <?php endif; ?>
                                    <?php if (in_array('electronics', $modules)): ?>
                                    <li class="nav-item">
                                        <a class="nav-link" href="electronics_dashboard.php">
                                            <i class="fas fa-laptop mr-2"></i> Electronics
                                        </a>
                                    </li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-danger" href="../logout.php">
                                <i class="fas fa-sign-out-alt mr-2"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">User Dashboard</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group mr-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary">Help</button>
                        </div>
                    </div>
                </div>

                <!-- Welcome Section -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-2 text-center">
                                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($user_data['username']); ?>&background=3498db&color=fff" class="user-avatar">
                                    </div>
                                    <div class="col-md-10">
                                        <h3>Welcome back, <?php echo htmlspecialchars($user_data['username']); ?>!</h3>
                                        <p class="lead">Here's what's happening with your assigned modules today.</p>
                                        <span class="badge bg-primary"><?php echo ucfirst($user_data['role']); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Assigned Modules -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5><i class="fas fa-th-large mr-2"></i> My Assigned Modules</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <?php foreach ($modules as $module): ?>
                                    <div class="col-md-4 mb-4">
                                        <div class="card module-card h-100">
                                            <div class="card-body text-center">
                                                <i class="fas 
                                                    <?php 
                                                        switch($module) {
                                                            case 'farming': echo 'fa-tractor'; break;
                                                            case 'soap': echo 'fa-pump-soap'; break;
                                                            case 'salon': echo 'fa-cut'; break;
                                                            case 'electronics': echo 'fa-laptop'; break;
                                                        }
                                                    ?> 
                                                module-icon mb-3"></i>
                                                <h5><?php echo ucfirst($module); ?> Module</h5>
                                                <p>Manage your <?php echo $module; ?> activities and tasks</p>
                                                <a href="<?php echo $module; ?>_dashboard.php" class="btn btn-primary">Enter Module</a>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                    
                                    <?php if (empty($modules)): ?>
                                    <div class="col-12 text-center py-4">
                                        <div class="alert alert-info">
                                            <h5>No modules assigned yet</h5>
                                            <p>Please contact your administrator to get access to business modules.</p>
                                        </div>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Activities -->
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h5><i class="fas fa-tasks mr-2"></i> My Recent Tasks</h5>
                            </div>
                            <div class="card-body">
                                <div class="list-group">
                                    <a href="#" class="list-group-item list-group-item-action">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1">Harvest Wheat Field A</h6>
                                            <span class="badge bg-warning">Pending</span>
                                        </div>
                                        <small>Due: Tomorrow</small>
                                    </a>
                                    <a href="#" class="list-group-item list-group-item-action">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1">Client Haircut</h6>
                                            <span class="badge bg-success">Completed</span>
                                        </div>
                                        <small>Completed: 2 hours ago</small>
                                    </a>
                                    <a href="#" class="list-group-item list-group-item-action">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1">Inventory Check</h6>
                                            <span class="badge bg-info">In Progress</span>
                                        </div>
                                        <small>Started: 1 day ago</small>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header bg-info text-white">
                                <h5><i class="fas fa-bell mr-2"></i> Notifications</h5>
                            </div>
                            <div class="card-body">
                                <div class="list-group">
                                    <a href="#" class="list-group-item list-group-item-action">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1">New Task Assigned</h6>
                                            <small>10 mins ago</small>
                                        </div>
                                        <p class="mb-1">You have been assigned to harvest corn field</p>
                                    </a>
                                    <a href="#" class="list-group-item list-group-item-action">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1">Inventory Alert</h6>
                                            <small>3 hours ago</small>
                                        </div>
                                        <p class="mb-1">Shampoo stock is running low</p>
                                    </a>
                                    <a href="#" class="list-group-item list-group-item-action">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1">System Update</h6>
                                            <small>1 day ago</small>
                                        </div>
                                        <p class="mb-1">New features added to farming module</p>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>