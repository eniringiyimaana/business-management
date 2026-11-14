
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Management - Business Management System</title>
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
        
        .badge-paid {
            background-color: rgba(46, 204, 113, 0.1);
            color: var(--secondary-color);
        }
        
        .badge-pending {
            background-color: rgba(241, 196, 15, 0.1);
            color: #f39c12;
        }
        
        .badge-overdue {
            background-color: rgba(231, 76, 60, 0.1);
            color: var(--accent-color);
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
                    <h1 class="page-title"><i class="fas fa-cash-register me-2"></i>Sales Management</h1>
                    <p class="mb-0">Track and manage all your sales transactions</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <button class="btn btn-light me-2"><i class="fas fa-file-export me-1"></i>Export</button>
                    <button class="btn btn-light"><i class="fas fa-print me-1"></i>Print</button>
                </div>
            </div>
        </div>
    </header>

    <div class="container">
        <!-- Sales Entry Form -->
        <div class="form-container">
            <h3 class="mb-4"><i class="fas fa-plus-circle me-2"></i>Record New Sale</h3>
            <form id="salesForm">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="customer" class="form-label">Customer *</label>
                        <select class="form-select" id="customer" required>
                            <option value="" selected disabled>Select customer</option>
                            <option value="customer1">John Doe</option>
                            <option value="customer2">Jane Smith</option>
                            <option value="customer3">Local Market</option>
                            <option value="new">+ Add New Customer</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3" id="newCustomerContainer" style="display: none;">
                        <label for="newCustomer" class="form-label">New Customer Name *</label>
                        <input type="text" class="form-control" id="newCustomer">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="saleDate" class="form-label">Sale Date *</label>
                        <input type="text" class="form-control datepicker" id="saleDate" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="invoiceNumber" class="form-label">Invoice Number</label>
                        <input type="text" class="form-control" id="invoiceNumber" value="INV-2024-001" readonly>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="product" class="form-label">Product *</label>
                        <select class="form-select" id="product" required>
                            <option value="" selected disabled>Select product</option>
                            <option value="milk">Fresh Milk</option>
                            <option value="eggs">Eggs</option>
                            <option value="maize">Maize</option>
                            <option value="soap">Handmade Soap</option>
                            <option value="other">Other Product</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3" id="otherProductContainer" style="display: none;">
                        <label for="otherProduct" class="form-label">Specify Product *</label>
                        <input type="text" class="form-control" id="otherProduct">
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="quantity" class="form-label">Quantity *</label>
                        <input type="number" class="form-control" id="quantity" min="1" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="unit" class="form-label">Unit</label>
                        <select class="form-select" id="unit">
                            <option value="kg">kg</option>
                            <option value="liters">liters</option>
                            <option value="pieces">pieces</option>
                            <option value="boxes">boxes</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="price" class="form-label">Price per Unit *</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="price" min="0" step="0.01" required>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label for="totalAmount" class="form-label">Total Amount</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="totalAmount" readonly>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="paymentMethod" class="form-label">Payment Method *</label>
                        <select class="form-select" id="paymentMethod" required>
                            <option value="cash">Cash</option>
                            <option value="credit">Credit</option>
                            <option value="mobile">Mobile Money</option>
                            <option value="bank">Bank Transfer</option>
                        </select>
                    </div>
                </div>
                
                <div class="row" id="creditTermsContainer" style="display: none;">
                    <div class="col-md-6 mb-3">
                        <label for="dueDate" class="form-label">Due Date</label>
                        <input type="text" class="form-control datepicker" id="dueDate">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label for="paymentStatus" class="form-label">Payment Status</label>
                        <select class="form-select" id="paymentStatus">
                            <option value="pending">Pending</option>
                            <option value="partial">Partial Payment</option>
                            <option value="paid">Paid</option>
                        </select>
                    </div>
                </div>
                
                <div class="mb-3">
                    <label for="notes" class="form-label">Notes</label>
                    <textarea class="form-control" id="notes" rows="2" placeholder="Additional notes about this sale"></textarea>
                </div>
                
                <div class="d-flex justify-content-between">
                    <button type="reset" class="btn btn-outline-secondary">Clear Form</button>
                    <button type="submit" class="btn btn-submit"><i class="fas fa-save me-2"></i>Record Sale</button>
                </div>
            </form>
        </div>
        
        <!-- Sales Records Table -->
        <div class="table-container">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3><i class="fas fa-list me-2"></i>Sales Records</h3>
                <div class="input-group" style="max-width: 300px;">
                    <input type="text" class="form-control" placeholder="Search sales...">
                    <button class="btn btn-outline-secondary" type="button"><i class="fas fa-search"></i></button>
                </div>
            </div>
            
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Invoice #</th>
                            <th>Date</th>
                            <th>Customer</th>
                            <th>Product</th>
                            <th>Qty</th>
                            <th>Unit Price</th>
                            <th>Total</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>INV-2024-1001</td>
                            <td>2024-03-15</td>
                            <td>John Doe</td>
                            <td>Fresh Milk</td>
                            <td>20 liters</td>
                            <td>$2.50</td>
                            <td>$50.00</td>
                            <td>Cash</td>
                            <td><span class="badge-status badge-paid">Paid</span></td>
                            <td>
                                <button class="btn-action btn-edit" title="Edit"><i class="fas fa-edit"></i></button>
                                <button class="btn-action btn-delete" title="Delete"><i class="fas fa-trash"></i></button>
                                <button class="btn-action" title="View Details" style="color: var(--primary-color);"><i class="fas fa-eye"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>INV-2024-1002</td>
                            <td>2024-03-14</td>
                            <td>Local Market</td>
                            <td>Eggs</td>
                            <td>5 boxes</td>
                            <td>$8.00</td>
                            <td>$40.00</td>
                            <td>Bank Transfer</td>
                            <td><span class="badge-status badge-paid">Paid</span></td>
                            <td>
                                <button class="btn-action btn-edit" title="Edit"><i class="fas fa-edit"></i></button>
                                <button class="btn-action btn-delete" title="Delete"><i class="fas fa-trash"></i></button>
                                <button class="btn-action" title="View Details" style="color: var(--primary-color);"><i class="fas fa-eye"></i></button>
                            </td>
                        </tr>
                        <tr>
                            <td>INV-2024-1003</td>
                            <td>2024-03-10</td>
                            <td>Jane Smith</td>
                            <td>Maize</td>
                            <td>50 kg</td>
                            <td>$1.20</td>
                            <td>$60.00</td>
                            <td>Credit</td>
                            <td><span class="badge-status badge-pending">Pending</span></td>
                            <td>
                                <button class="btn-action btn-edit" title="Edit"><i class="fas fa-edit"></i></button>
                                <button class="btn-action btn-delete" title="Delete"><i class="fas fa-trash"></i></button>
                                <button class="btn-action" title="View Details" style="color: var(--primary-color);"><i class="fas fa-eye"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            
            <nav aria-label="Sales pagination" class="mt-4">
                <ul class="pagination justify-content-center">
                    <li class="page-item disabled">
                        <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Previous</a>
                    </li>
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                    <li class="page-item">
                        <a class="page-link" href="#">Next</a>
                    </li>
                </ul>
            </nav>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-datepicker@1.9.0/dist/js/bootstrap-datepicker.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize Select2
            $('.form-select').select2({
                minimumResultsForSearch: 6,
                width: '100%'
            });
            
            // Initialize datepicker
            $('.datepicker').datepicker({
                format: 'yyyy-mm-dd',
                autoclose: true,
                todayHighlight: true
            });
            
            // Show/hide new customer field
            $('#customer').change(function() {
                if ($(this).val() === 'new') {
                    $('#newCustomerContainer').show();
                    $('#newCustomer').prop('required', true);
                } else {
                    $('#newCustomerContainer').hide();
                    $('#newCustomer').prop('required', false);
                }
            });
            
            // Show/hide other product field
            $('#product').change(function() {
                if ($(this).val() === 'other') {
                    $('#otherProductContainer').show();
                    $('#otherProduct').prop('required', true);
                } else {
                    $('#otherProductContainer').hide();
                    $('#otherProduct').prop('required', false);
                }
            });
            
            // Show/hide credit terms
            $('#paymentMethod').change(function() {
                if ($(this).val() === 'credit') {
                    $('#creditTermsContainer').show();
                } else {
                    $('#creditTermsContainer').hide();
                }
            });
            
            // Calculate total amount
            $('#quantity, #price').on('input', function() {
                const quantity = parseFloat($('#quantity').val()) || 0;
                const price = parseFloat($('#price').val()) || 0;
                const total = quantity * price;
                $('#totalAmount').val(total.toFixed(2));
            });
            
            // Form submission
            $('#salesForm').submit(function(e) {
                e.preventDefault();
                
                // Show loading state
                $('button[type="submit"]').html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Processing...').prop('disabled', true);
                
                // Simulate form submission
                setTimeout(function() {
                    alert('Sale successfully recorded!');
                    $('#salesForm')[0].reset();
                    $('button[type="submit"]').html('<i class="fas fa-save me-2"></i>Record Sale').prop('disabled', false);
                    
                    // In a real application, you would add the new sale to the table
                }, 1500);
            });
            
            // Delete button confirmation
            $('.btn-delete').click(function() {
                if (confirm('Are you sure you want to delete this sales record?')) {
                    $(this).closest('tr').fadeOut(300, function() {
                        $(this).remove();
                        // In a real application, you would make an AJAX call to delete from the database
                    });
                }
            });
            
            // Edit button functionality
            $('.btn-edit').click(function() {
                // In a real application, this would populate the form with the selected sale's data
                alert('Edit functionality would load the sale data into the form for editing');
            });
        });
    </script>
</body>
</html>
