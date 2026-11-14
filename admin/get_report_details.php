<?php
require_once '../includes/db_config.php';
require_once '../includes/auth_check.php';

if (isset($_GET['id'])) {
    $report_id = intval($_GET['id']);
    $business_id = $_SESSION['business_id'] ?? 0;
    
    try {
        $query = "SELECT report_details FROM reports 
                 WHERE report_id = ? AND business_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ii", $report_id, $business_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            echo $row['report_details'];
        } else {
            echo "<div class='alert alert-danger'>Report not found or access denied.</div>";
        }
    } catch (Exception $e) {
        echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
} else {
    echo "<div class='alert alert-danger'>No report ID specified.</div>";
}
?>