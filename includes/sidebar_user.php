<nav class="col-md-2 d-none d-md-block bg-dark sidebar">
    <div class="sidebar-sticky pt-3">
        <div class="sidebar-header text-center text-white mb-4">
            <h4><i class="fas fa-chart-line"></i> Business Manager</h4>
            <small>User Dashboard</small>
        </div>
        
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>" 
                   href="dashboard.php">
                    <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" data-bs-toggle="collapse" data-bs-target="#modulesCollapse">
                    <i class="fas fa-th-large mr-2"></i> My Modules
                </a>
                <div class="collapse show" id="modulesCollapse">
                    <ul class="nav flex-column pl-3">
                        <?php if (in_array('farming', $user_modules)): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="../modules/farming/dashboard.php">
                                <i class="fas fa-tractor mr-2"></i> Farming
                            </a>
                        </li>
                        <?php endif; ?>
                        <!-- Other modules with privilege checks -->
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