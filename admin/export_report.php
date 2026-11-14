<?php
require_once '../includes/db_config.php';
require_once '../includes/auth_check.php';

if (isset($_GET['id'])) {
    $report_id = intval($_GET['id']);
    $business_id = $_SESSION['business_id'] ?? 0;
    
    try {
        $query = "SELECT * FROM reports 
                 WHERE report_id = ? AND business_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $report_id, $business_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            // Simple CSV export - in a real app you'd parse the HTML or store raw data
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="report_' . $row['report_id'] . '.csv"');
            
            $output = fopen('php://output', 'w');
            
            // Basic header
            fputcsv($output, ['Report ID', 'Type', 'Generated Date']);
            fputcsv($output, [
                $row['report_id'],
                $row['report_type'],
                $row['generated_date']
            ]);
            
            fclose($output);
            exit();
        } else {
            $_SESSION['error'] = "Report not found or access denied.";
            header("Location: reports.php");
            exit();
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
        header("Location: reports.php");
        exit();
    }
} else {
    $_SESSION['error'] = "No report ID specified.";
    header("Location: reports.php");
    exit();
}
?>