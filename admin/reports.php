<?php
// Set page title and required role
$page_title = "Business Reports";
$required_role = "admin"; // Can be adjusted based on your requirements

// Include necessary files
require_once '../includes/auth_check.php';
require_once '../includes/header.php';
require_once '../includes/db_config.php';

// Initialize variables
$reports = [];
$error = '';
$success = '';
$report_types = [
    'Financial' => 'Financial Transactions',
    'Inventory' => 'Inventory Status',
    'Employee' => 'Employee Activities',
    'Sales' => 'Sales Records',
    'Yield' => 'Crop Yields',
    'Production' => 'Production Batches'
];

// Handle form submission for generating new report
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['generate_report'])) {
    $report_type = $_POST['report_type'] ?? '';
    $start_date = $_POST['start_date'] ?? '';
    $end_date = $_POST['end_date'] ?? '';
    $business_id = $_SESSION['business_id'] ?? 0; // Assuming business_id is stored in session

    try {
        // Validate input
        if (empty($report_type)) {
            throw new Exception("Please select a report type.");
        }

        if (empty($start_date)) {
            throw new Exception("Please select a start date.");
        }

        // Generate report based on type
        $report_details = '';
        $report_title = $report_types[$report_type] . " Report";

        switch ($report_type) {
            case 'Financial':
                $query = "SELECT * FROM financial_transactions 
                         WHERE business_id = ? 
                         AND transaction_date BETWEEN ? AND ?
                         ORDER BY transaction_date DESC";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("iss", $business_id, $start_date, $end_date);
                $stmt->execute();
                $result = $stmt->get_result();
                
                $report_details = "<h4>Financial Transactions</h4>";
                $report_details .= "<table class='table table-striped'><thead><tr>
                    <th>Date</th>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Category</th>
                    <th>Description</th>
                    </tr></thead><tbody>";
                
                $total_income = 0;
                $total_expense = 0;
                
                while ($row = $result->fetch_assoc()) {
                    if ($row['transaction_type'] == 'Income') {
                        $total_income += $row['amount'];
                    } else {
                        $total_expense += $row['amount'];
                    }
                    
                    $report_details .= "<tr>
                        <td>" . htmlspecialchars($row['transaction_date']) . "</td>
                        <td>" . htmlspecialchars($row['transaction_type']) . "</td>
                        <td>" . htmlspecialchars(number_format($row['amount'], 2)) . "</td>
                        <td>" . htmlspecialchars($row['category']) . "</td>
                        <td>" . htmlspecialchars($row['description']) . "</td>
                        </tr>";
                }
                
                $report_details .= "</tbody></table>";
                $report_details .= "<div class='alert alert-info'>
                    <strong>Total Income:</strong> " . number_format($total_income, 2) . "<br>
                    <strong>Total Expenses:</strong> " . number_format($total_expense, 2) . "<br>
                    <strong>Net Profit:</strong> " . number_format($total_income - $total_expense, 2) . "
                    </div>";
                break;

            case 'Inventory':
                $query = "SELECT * FROM inventory 
                         WHERE business_id = ? 
                         AND category = ?
                         ORDER BY item_name";
                $stmt = $conn->prepare($query);
                $category = $_POST['inventory_category'] ?? 'General';
                $stmt->bind_param("is", $business_id, $category);
                $stmt->execute();
                $result = $stmt->get_result();
                
                $report_details = "<h4>Inventory Status - " . htmlspecialchars($category) . "</h4>";
                $report_details .= "<table class='table table-striped'><thead><tr>
                    <th>Item Name</th>
                    <th>Quantity</th>
                    <th>Unit Price</th>
                    <th>Supplier</th>
                    <th>Reorder Level</th>
                    </tr></thead><tbody>";
                
                $total_value = 0;
                
                while ($row = $result->fetch_assoc()) {
                    $item_value = $row['quantity'] * $row['unit_price'];
                    $total_value += $item_value;
                    
                    $row_class = $row['quantity'] <= $row['reorder_level'] ? 'class="table-warning"' : '';
                    
                    $report_details .= "<tr $row_class>
                        <td>" . htmlspecialchars($row['item_name']) . "</td>
                        <td>" . htmlspecialchars($row['quantity']) . "</td>
                        <td>" . htmlspecialchars(number_format($row['unit_price'], 2)) . "</td>
                        <td>" . htmlspecialchars($row['supplier']) . "</td>
                        <td>" . htmlspecialchars($row['reorder_level']) . "</td>
                        </tr>";
                }
                
                $report_details .= "</tbody></table>";
                $report_details .= "<div class='alert alert-info'>
                    <strong>Total Inventory Value:</strong> " . number_format($total_value, 2) . "
                    </div>";
                break;

            case 'Employee':
                $query = "SELECT e.*, COUNT(a.appointment_id) as appointments 
                         FROM employees e
                         LEFT JOIN appointments a ON e.employee_id = a.employee_id
                         WHERE e.business_id = ?
                         GROUP BY e.employee_id
                         ORDER BY e.name";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("i", $business_id);
                $stmt->execute();
                $result = $stmt->get_result();
                
                $report_details = "<h4>Employee Activities</h4>";
                $report_details .= "<table class='table table-striped'><thead><tr>
                    <th>Name</th>
                    <th>Role</th>
                    <th>Salary</th>
                    <th>Shift</th>
                    <th>Appointments</th>
                    </tr></thead><tbody>";
                
                while ($row = $result->fetch_assoc()) {
                    $report_details .= "<tr>
                        <td>" . htmlspecialchars($row['name']) . "</td>
                        <td>" . htmlspecialchars($row['role']) . "</td>
                        <td>" . htmlspecialchars(number_format($row['salary'], 2)) . "</td>
                        <td>" . htmlspecialchars($row['shift_start']) . " - " . htmlspecialchars($row['shift_end']) . "</td>
                        <td>" . htmlspecialchars($row['appointments']) . "</td>
                        </tr>";
                }
                
                $report_details .= "</tbody></table>";
                break;

            case 'Sales':
                $query = "SELECT s.*, c.name as customer_name 
                         FROM sales s
                         LEFT JOIN customers c ON s.customer_id = c.customer_id
                         WHERE s.business_id = ?
                         AND s.sale_date BETWEEN ? AND ?
                         ORDER BY s.sale_date DESC";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("iss", $business_id, $start_date, $end_date);
                $stmt->execute();
                $result = $stmt->get_result();
                
                $report_details = "<h4>Sales Records</h4>";
                $report_details .= "<table class='table table-striped'><thead><tr>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Item</th>
                    <th>Quantity</th>
                    <th>Total Price</th>
                    </tr></thead><tbody>";
                
                $total_sales = 0;
                
                while ($row = $result->fetch_assoc()) {
                    $total_sales += $row['total_price'];
                    
                    $report_details .= "<tr>
                        <td>" . htmlspecialchars(date('M j, Y H:i', strtotime($row['sale_date']))) . "</td>
                        <td>" . htmlspecialchars($row['customer_name'] ?? 'N/A') . "</td>
                        <td>" . htmlspecialchars($row['item_name']) . "</td>
                        <td>" . htmlspecialchars($row['quantity']) . "</td>
                        <td>" . htmlspecialchars(number_format($row['total_price'], 2)) . "</td>
                        </tr>";
                }
                
                $report_details .= "</tbody></table>";
                $report_details .= "<div class='alert alert-info'>
                    <strong>Total Sales:</strong> " . number_format($total_sales, 2) . "
                    </div>";
                break;

            case 'Yield':
                $query = "SELECT * FROM crops 
                         WHERE business_id = ? 
                         AND harvest_date BETWEEN ? AND ?
                         ORDER BY harvest_date DESC";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("iss", $business_id, $start_date, $end_date);
                $stmt->execute();
                $result = $stmt->get_result();
                
                $report_details = "<h4>Crop Yields</h4>";
                $report_details .= "<table class='table table-striped'><thead><tr>
                    <th>Crop Name</th>
                    <th>Variety</th>
                    <th>Planting Date</th>
                    <th>Harvest Date</th>
                    <th>Yield Quantity</th>
                    </tr></thead><tbody>";
                
                $total_yield = 0;
                
                while ($row = $result->fetch_assoc()) {
                    $total_yield += $row['yield_quantity'];
                    
                    $report_details .= "<tr>
                        <td>" . htmlspecialchars($row['crop_name']) . "</td>
                        <td>" . htmlspecialchars($row['crop_variety']) . "</td>
                        <td>" . htmlspecialchars($row['planting_date']) . "</td>
                        <td>" . htmlspecialchars($row['harvest_date']) . "</td>
                        <td>" . htmlspecialchars(number_format($row['yield_quantity'], 2)) . "</td>
                        </tr>";
                }
                
                $report_details .= "</tbody></table>";
                $report_details .= "<div class='alert alert-info'>
                    <strong>Total Yield:</strong> " . number_format($total_yield, 2) . "
                    </div>";
                break;

            case 'Production':
                $query = "SELECT * FROM production_batches 
                         WHERE business_id = ? 
                         AND production_date BETWEEN ? AND ?
                         ORDER BY production_date DESC";
                $stmt = $conn->prepare($query);
                $stmt->bind_param("iss", $business_id, $start_date, $end_date);
                $stmt->execute();
                $result = $stmt->get_result();
                
                $report_details = "<h4>Production Batches</h4>";
                $report_details .= "<table class='table table-striped'><thead><tr>
                    <th>Batch Code</th>
                    <th>Production Date</th>
                    <th>Quantity</th>
                    <th>Quality Check</th>
                    </tr></thead><tbody>";
                
                $total_quantity = 0;
                
                while ($row = $result->fetch_assoc()) {
                    $total_quantity += $row['quantity_produced'];
                    
                    $report_details .= "<tr>
                        <td>" . htmlspecialchars($row['batch_code']) . "</td>
                        <td>" . htmlspecialchars($row['production_date']) . "</td>
                        <td>" . htmlspecialchars($row['quantity_produced']) . "</td>
                        <td>" . htmlspecialchars($row['quality_check']) . "</td>
                        </tr>";
                }
                
                $report_details .= "</tbody></table>";
                $report_details .= "<div class='alert alert-info'>
                    <strong>Total Quantity Produced:</strong> " . number_format($total_quantity, 0) . "
                    </div>";
                break;
        }

        // Save the report to the database
        $insert_query = "INSERT INTO reports (business_id, report_type, report_details) 
                        VALUES (?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        $stmt->bind_param("iss", $business_id, $report_title, $report_details);
        $stmt->execute();
        
        $success = "Report generated successfully!";
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Get existing reports for the business
try {
    $business_id = $_SESSION['business_id'] ?? 0;
    $query = "SELECT * FROM reports 
              WHERE business_id = ? 
              ORDER BY generated_date DESC";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $business_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $reports = [];
    while ($row = $result->fetch_assoc()) {
        $reports[] = $row;
    }
} catch (Exception $e) {
    $error = "Error fetching reports: " . $e->getMessage();
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
                <h1 class="h2">Business Reports</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#generateReportModal">
                        <i class="fas fa-plus me-1"></i> Generate New Report
                    </button>
                </div>
            </div>

            <!-- Report Generation Modal -->
            <div class="modal fade" id="generateReportModal" tabindex="-1" aria-labelledby="generateReportModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-lg">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="generateReportModalLabel">Generate New Report</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <form method="POST" action="">
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label for="report_type" class="form-label">Report Type</label>
                                    <select class="form-select" id="report_type" name="report_type" required>
                                        <option value="">Select a report type</option>
                                        <?php foreach ($report_types as $key => $value): ?>
                                            <option value="<?php echo $key; ?>"><?php echo $value; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div id="inventory_category_container" class="mb-3" style="display: none;">
                                    <label for="inventory_category" class="form-label">Inventory Category</label>
                                    <select class="form-select" id="inventory_category" name="inventory_category">
                                        <option value="Farming">Farming</option>
                                        <option value="Soap">Soap</option>
                                        <option value="Salon">Salon</option>
                                        <option value="Electronics">Electronics</option>
                                        <option value="General" selected>General</option>
                                        <option value="Home">Home</option>
                                    </select>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="start_date" class="form-label">Start Date</label>
                                        <input type="date" class="form-control" id="start_date" name="start_date" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="end_date" class="form-label">End Date</label>
                                        <input type="date" class="form-control" id="end_date" name="end_date" required>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" name="generate_report" class="btn btn-primary">Generate Report</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Reports List -->
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i>Generated Reports</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($reports)): ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Report ID</th>
                                        <th>Type</th>
                                        <th>Generated Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($reports as $report): ?>
                                    <tr>
                                        <td><?php echo $report['report_id']; ?></td>
                                        <td><?php echo $report['report_type']; ?></td>
                                        <td><?php echo date('M j, Y H:i', strtotime($report['generated_date'])); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary view-report" 
                                                    data-report-id="<?php echo $report['report_id']; ?>"
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#viewReportModal">
                                                <i class="fas fa-eye"></i> View
                                            </button>
                                            <a href="export_report.php?id=<?php echo $report['report_id']; ?>" 
                                               class="btn btn-sm btn-outline-success">
                                                <i class="fas fa-download"></i> Export
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">No reports found. Generate a new report to get started.</div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- View Report Modal -->
<div class="modal fade" id="viewReportModal" tabindex="-1" aria-labelledby="viewReportModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="viewReportModalLabel">Report Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="reportDetailsContent">
                <!-- Report content will be loaded here via AJAX -->
                <div class="text-center my-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" onclick="window.print()">
                    <i class="fas fa-print me-1"></i> Print
                </button>
            </div>
        </div>
    </div>
</div>

<?php 
// Custom JavaScript
$custom_js = "
document.addEventListener('DOMContentLoaded', function() {
    // Show/hide inventory category based on report type
    document.getElementById('report_type').addEventListener('change', function() {
        const inventoryContainer = document.getElementById('inventory_category_container');
        if (this.value === 'Inventory') {
            inventoryContainer.style.display = 'block';
        } else {
            inventoryContainer.style.display = 'none';
        }
    });
    
    // Set default dates
    const today = new Date();
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
    
    document.getElementById('start_date').valueAsDate = firstDay;
    document.getElementById('end_date').valueAsDate = today;
    
    // Handle view report button clicks
    document.querySelectorAll('.view-report').forEach(button => {
        button.addEventListener('click', function() {
            const reportId = this.getAttribute('data-report-id');
            const modalContent = document.getElementById('reportDetailsContent');
            
            // Show loading spinner
            modalContent.innerHTML = `
                <div class='text-center my-5'>
                    <div class='spinner-border text-primary' role='status'>
                        <span class='visually-hidden'>Loading...</span>
                    </div>
                </div>
            `;
            
            // Fetch report details via AJAX
            fetch('get_report_details.php?id=' + reportId)
                .then(response => response.text())
                .then(data => {
                    modalContent.innerHTML = data;
                })
                .catch(error => {
                    modalContent.innerHTML = `
                        <div class='alert alert-danger'>
                            Error loading report details: ${error}
                        </div>
                    `;
                });
        });
    });
    
    // When the modal is shown, clear previous content
    document.getElementById('viewReportModal').addEventListener('show.bs.modal', function() {
        document.getElementById('reportDetailsContent').innerHTML = `
            <div class='text-center my-5'>
                <div class='spinner-border text-primary' role='status'>
                    <span class='visually-hidden'>Loading...</span>
                </div>
            </div>
        `;
    });
});
";

// Include footer
require_once '../includes/footer.php';
?>