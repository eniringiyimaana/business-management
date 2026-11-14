<?php
require_once '../../includes/db_config.php';
require_once '../../includes/auth_check.php';

header('Content-Type: application/json');

$business_id = $_SESSION['business_id'] ?? null;
$start = $_GET['start'] ?? date('Y-m-01');
$end = $_GET['end'] ?? date('Y-m-t');

$stmt = $conn->prepare("
    SELECT 
        t.task_id AS id,
        CONCAT(t.task_type, ': ', IFNULL(f.field_name, 'General')) AS title,
        t.due_date AS start,
        t.due_date AS end,
        t.status,
        t.task_type,
        f.field_name,
        t.notes
    FROM tasks t
    LEFT JOIN fields f ON t.field_id = f.field_id
    WHERE t.business_id = ? 
    AND t.due_date BETWEEN ? AND ?
    ORDER BY t.due_date
");
$stmt->bind_param('iss', $business_id, $start, $end);
$stmt->execute();
$result = $stmt->get_result();

$events = [];
while ($row = $result->fetch_assoc()) {
    $events[] = [
        'id' => $row['id'],
        'title' => $row['title'],
        'start' => $row['start'],
        'end' => $row['end'],
        'color' => $row['status'] === 'Completed' ? '#6c757d' : 
                 ($row['task_type'] === 'Planting' ? '#007bff' :
                 ($row['task_type'] === 'Irrigation' ? '#17a2b8' :
                 ($row['task_type'] === 'Harvesting' ? '#28a745' :
                 ($row['task_type'] === 'Fertilization' ? '#ffc107' : '#dc3545')))),
        'extendedProps' => [
            'task_type' => $row['task_type'],
            'status' => $row['status'],
            'field_name' => $row['field_name'],
            'notes' => $row['notes']
        ]
    ];
}

echo json_encode($events);
?>