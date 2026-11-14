<?php
// File: includes/sidebar_admin.php
?>
<nav class="col-md-2 d-none d-md-block bg-dark sidebar">
    <div class="sidebar-sticky pt-3">
        <div class="sidebar-header text-center text-white mb-4">
            <h4><i class="fas fa-chart-line"></i> Business Manager</h4>
            <small>Admin Dashboard</small>
        </div>
        
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'admin_dashboard.php' ? 'active' : ''; ?>" 
                   href="admin_dashboard.php">
                    <i class="fas fa-tachometer-alt me-2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'users.php' ? 'active' : ''; ?>" 
                   href="users.php">
                    <i class="fas fa-users me-2"></i> User Management
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'businesses.php' ? 'active' : ''; ?>" 
                   href="businesses.php">
                    <i class="fas fa-store me-2"></i> Businesses
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" data-bs-toggle="collapse" data-bs-target="#modulesCollapse">
                    <i class="fas fa-th-large me-2"></i> Business Modules
                    <i class="fas fa-angle-down float-end mt-1"></i>
                </a>
                <div class="collapse show" id="modulesCollapse">
                    <ul class="nav flex-column ps-3">
                        <li class="nav-item">
                            <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], 'farming') !== false ? 'active' : ''; ?>" 
                               href="../modules/farming/farming_dashboard.php">
                                <i class="fas fa-tractor me-2"></i> Farming
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], 'soap') !== false ? 'active' : ''; ?>" 
                               href="../modules/soap/dashboard.php">
                                <i class="fas fa-pump-soap me-2"></i> Soap Making
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], 'salon') !== false ? 'active' : ''; ?>" 
                               href="../modules/salon/dashboard.php">
                                <i class="fas fa-cut me-2"></i> Salon
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], 'electronics') !== false ? 'active' : ''; ?>" 
                               href="../modules/electronics/dashboard.php">
                                <i class="fas fa-laptop me-2"></i> Electronics
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], 'general') !== false ? 'active' : ''; ?>" 
                               href="../modules/general/dashboard.php">
                                <i class="fas fa-briefcase me-2"></i> General
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo strpos($_SERVER['REQUEST_URI'], 'home') !== false ? 'active' : ''; ?>" 
                               href="../modules/home/dashboard.php">
                                <i class="fas fa-home me-2"></i> Home
                            </a>
                        </li>
                    </ul>
                </div>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'reports.php' ? 'active' : ''; ?>" 
                   href="reports.php">
                    <i class="fas fa-chart-bar me-2"></i> Reports
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'settings.php' ? 'active' : ''; ?>" 
                   href="settings.php">
                    <i class="fas fa-cog me-2"></i> Settings
                </a>
            </li>
            <li class="nav-item mt-3">
                <a class="nav-link text-danger" href="../logout.php">
                    <i class="fas fa-sign-out-alt me-2"></i> Logout
                </a>
            </li>
        </ul>
    </div>
</nav>