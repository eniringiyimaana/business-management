<?php
include 'db_config.php'; 

if (isset($_GET['id'])) {
    $livestock_id = mysqli_real_escape_string($conn, $_GET['id']);

    // Fetch livestock details
    $query = "SELECT * FROM livestock WHERE livestock_id = '$livestock_id'";
    $result = mysqli_query($conn, $query);
    $livestock = mysqli_fetch_assoc($result);

    if (!$livestock) {
        echo "<script>alert('Livestock record not found.'); window.location.href='livestock.php';</script>";
        exit();
    }
} else {
    echo "<script>alert('Invalid request!'); window.location.href='livestock.php';</script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Livestock Details</title>
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
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            color: var(--dark-color);
        }
        
        /* Navigation Bar */
        .navbar {
            background-color: var(--dark-color);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }
        
        .navbar-brand i {
            color: var(--secondary-color);
            margin-right: 10px;
        }
        
        /* Dashboard Header */
        .dashboard-header {
            background: linear-gradient(135deg, rgba(52, 152, 219, 0.1) 0%, rgba(46, 204, 113, 0.1) 100%);
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            position: relative;
            overflow: hidden;
        }
        
        .dashboard-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('https://images.unsplash.com/photo-1500382017468-9049fed747ef?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80') center/cover no-repeat;
            opacity: 0.1;
            z-index: 0;
        }
        
        .dashboard-header-content {
            position: relative;
            z-index: 1;
        }
        
        h1.dashboard-title {
            font-weight: 800;
            font-size: 2.8rem;
            background: linear-gradient(90deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.1);
            margin-bottom: 1rem;
        }
        
        .dashboard-subtitle {
            font-size: 1.2rem;
            color: var(--dark-color);
            opacity: 0.8;
            margin-bottom: 1.5rem;
        }
        
        /* Cards */
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            margin-bottom: 1.5rem;
            height: 100%;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 20px rgba(0, 0, 0, 0.15);
        }
        
        .card-header {
            background-color: var(--primary-color);
            color: white;
            font-weight: 600;
            border-top-left-radius: 15px !important;
            border-top-right-radius: 15px !important;
            padding: 1rem 1.5rem;
            display: flex;
            align-items: center;
        }
        
        .card-header i {
            margin-right: 10px;
            font-size: 1.2rem;
        }
        
        .card-body {
            padding: 1.5rem;
            background-color: white;
            border-bottom-left-radius: 15px;
            border-bottom-right-radius: 15px;
        }
        
        .card-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: var(--dark-color);
        }
        
        .card-value {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 1rem;
            color: var(--primary-color);
        }
        
        .card-text {
            font-size: 0.9rem;
            color: #6c757d;
            margin-bottom: 1.5rem;
        }
        
        /* Buttons */
        .btn {
            border-radius: 50px;
            padding: 0.5rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: #2980b9;
            border-color: #2980b9;
            transform: translateY(-2px);
        }
        
        .btn-success {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .btn-success:hover {
            background-color: #27ae60;
            border-color: #27ae60;
            transform: translateY(-2px);
        }
        
        /* Stats Cards */
        .stat-card {
            padding: 1.5rem;
            border-radius: 15px;
            color: white;
            margin-bottom: 1.5rem;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card i {
            font-size: 2.5rem;
            opacity: 0.3;
            position: absolute;
            right: 20px;
            top: 20px;
        }
        
        .stat-card h3 {
            font-weight: 700;
            margin-bottom: 0.5rem;
            font-size: 1.5rem;
        }
        
        .stat-card p {
            margin-bottom: 0;
            opacity: 0.9;
        }
        
        /* Charts */
        .chart-container {
            position: relative;
            height: 250px;
            margin-bottom: 1.5rem;
        }
        
        /* Activity Feed */
        .activity-item {
            padding: 0.75rem 0;
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
        }
        
        .activity-item:last-child {
            border-bottom: none;
        }
        
        .activity-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: rgba(52, 152, 219, 0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: var(--primary-color);
        }
        
        .activity-content {
            flex: 1;
        }
        
        .activity-time {
            font-size: 0.8rem;
            color: #6c757d;
        }
        
        /* Responsive Adjustments */
        @media (max-width: 768px) {
            h1.dashboard-title {
                font-size: 2rem;
            }
            
            .dashboard-subtitle {
                font-size: 1rem;
            }
            
            .card-value {
                font-size: 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <h2>Livestock Details</h2>
        <table class="table table-bordered">
            <tr>
                <th>ID</th>
                <td><?= htmlspecialchars($livestock['livestock_id']) ?></td>
            </tr>
            <tr>
                <th>Animal Type</th>
                <td><?= htmlspecialchars($livestock['animal_type']) ?></td>
            </tr>
            <tr>
                <th>Purchase Date</th>
                <td><?= htmlspecialchars($livestock['purchase_date']) ?></td>
            </tr>
            <tr>
                <th>Health Status</th>
                <td><?= htmlspecialchars($livestock['health_status']) ?></td>
            </tr>
        </table>
        <a href="livestock.php" class="btn btn-primary">Back to List</a>
    </div>
</body>
</html>
