<?php
require_once '../../includes/auth_check.php';
require_once '../../includes/db_config.php';
require_once '../../includes/header.php';

$business_id = $_SESSION['business_id'];
$action = $_GET['action'] ?? 'calendar';
$task_id = $_GET['id'] ?? null;

// Initialize task data
$task_data = [
    'task_type' => 'Planting',
    'field_id' => null,
    'assigned_employee_id' => null,
    'due_date' => date('Y-m-d'),
    'estimated_hours' => 1.00,
    'status' => 'Pending',
    'notes' => ''
];

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->begin_transaction();
        
        // Validate and process task data
        $task_data = array_merge($task_data, $_POST);
        $task_data['estimated_hours'] = max(0.25, min(24, (float)$_POST['estimated_hours']));

        if ($_POST['action'] === 'add') {
            $stmt = $conn->prepare("INSERT INTO tasks SET
                business_id = ?,
                field_id = ?,
                assigned_employee_id = ?,
                task_type = ?,
                due_date = ?,
                estimated_hours = ?,
                status = ?,
                notes = ?");
            $stmt->bind_param("iiissdss", 
                $business_id,
                $task_data['field_id'],
                $task_data['assigned_employee_id'],
                $task_data['task_type'],
                $task_data['due_date'],
                $task_data['estimated_hours'],
                $task_data['status'],
                $task_data['notes']);
        } 
        elseif ($_POST['action'] === 'update') {
            $stmt = $conn->prepare("UPDATE tasks SET
                field_id = ?,
                assigned_employee_id = ?,
                task_type = ?,
                due_date = ?,
                estimated_hours = ?,
                status = ?,
                notes = ?
                WHERE task_id = ? AND business_id = ?");
            $stmt->bind_param("iissdssii",
                $task_data['field_id'],
                $task_data['assigned_employee_id'],
                $task_data['task_type'],
                $task_data['due_date'],
                $task_data['estimated_hours'],
                $task_data['status'],
                $task_data['notes'],
                $task_id,
                $business_id);
        }
        elseif ($_POST['action'] === 'complete') {
            $stmt = $conn->prepare("UPDATE tasks SET
                status = 'Completed',
                actual_hours = ?,
                completion_date = NOW(),
                completion_notes = ?
                WHERE task_id = ? AND business_id = ?");
            $stmt->bind_param("dsii",
                $task_data['actual_hours'],
                $task_data['completion_notes'],
                $task_id,
                $business_id);
                
            // Process time logs
            foreach ($_POST['time_logs'] as $log) {
                if (!empty($log['start_time'])) {
                    $log_stmt = $conn->prepare("INSERT INTO task_time_logs SET
                        task_id = ?,
                        employee_id = ?,
                        start_time = ?,
                        end_time = ?,
                        logged_hours = ?,
                        notes = ?");
                    $log_stmt->bind_param("iissds",
                        $task_id,
                        $log['employee_id'],
                        $log['start_time'],
                        $log['end_time'],
                        $log['logged_hours'],
                        $log['notes']);
                    $log_stmt->execute();
                }
            }
        }

        $stmt->execute();
        $conn->commit();
        header("Location: tasks.php?success=1");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Error: " . $e->getMessage();
    }
}

// Fetch task data for editing
if (in_array($action, ['edit', 'view', 'complete']) && $task_id) {
    $stmt = $conn->prepare("SELECT * FROM tasks WHERE task_id = ? AND business_id = ?");
    $stmt->bind_param("ii", $task_id, $business_id);
    $stmt->execute();
    $task_data = $stmt->get_result()->fetch_assoc();
    
    // Fetch time logs if completing
    if ($action === 'complete') {
        $log_stmt = $conn->prepare("SELECT * FROM task_time_logs WHERE task_id = ?");
        $log_stmt->bind_param("i", $task_id);
        $log_stmt->execute();
        $time_logs = $log_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}

// Fetch dropdown data
$fields = $conn->query("SELECT field_id, field_name FROM fields WHERE business_id = $business_id")->fetch_all(MYSQLI_ASSOC);
$employees = $conn->query("SELECT employee_id, name FROM employees WHERE business_id = $business_id")->fetch_all(MYSQLI_ASSOC);
?>

<div class="container-fluid">
    <div class="row">
        <?php include '../../includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <!-- Task Form -->
            <div class="card my-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-tasks me-2"></i>
                        <?= ucfirst($action) ?> Task
                    </h5>
                </div>
                <div class="card-body">
                    <form method="post">
                        <input type="hidden" name="action" value="<?= $action ?>">
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Task Type</label>
                                <select class="form-select" name="task_type" required>
                                    <?php foreach (['Planting', 'Irrigation', 'Harvesting', 'Fertilization', 'Pest Control'] as $type): ?>
                                        <option value="<?= $type ?>" <?= $task_data['task_type'] === $type ? 'selected' : '' ?>>
                                            <?= $type ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-6">
                                <label class="form-label">Assigned To</label>
                                <select class="form-select" name="assigned_employee_id">
                                    <option value="">-- Unassigned --</option>
                                    <?php foreach ($employees as $emp): ?>
                                        <option value="<?= $emp['employee_id'] ?>" 
                                            <?= $task_data['assigned_employee_id'] == $emp['employee_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($emp['name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Field</label>
                                <select class="form-select" name="field_id">
                                    <option value="">-- Select Field --</option>
                                    <?php foreach ($fields as $field): ?>
                                        <option value="<?= $field['field_id'] ?>" 
                                            <?= $task_data['field_id'] == $field['field_id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($field['field_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">Due Date</label>
                                <input type="date" class="form-control" name="due_date" 
                                    value="<?= $task_data['due_date'] ?>" required>
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">Estimated Hours</label>
                                <input type="number" step="0.25" min="0.25" max="24" 
                                    class="form-control" name="estimated_hours" 
                                    value="<?= $task_data['estimated_hours'] ?>" required>
                            </div>
                        </div>
                        
                        <?php if ($action === 'complete'): ?>
                            <div class="card mb-3">
                                <div class="card-header bg-info text-white">
                                    <h6 class="mb-0">Completion Details</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3">
                                        <label class="form-label">Actual Hours</label>
                                        <input type="number" step="0.25" min="0.25" 
                                            class="form-control" name="actual_hours" 
                                            value="<?= $task_data['actual_hours'] ?? $task_data['estimated_hours'] ?>" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Completion Notes</label>
                                        <textarea class="form-control" name="completion_notes" rows="3" required></textarea>
                                    </div>
                                    
                                    <div id="timeLogsContainer">
                                        <h6>Time Logs</h6>
                                        <?php if (!empty($time_logs)): ?>
                                            <?php foreach ($time_logs as $i => $log): ?>
                                                <div class="time-log-entry border p-2 mb-2">
                                                    <div class="row">
                                                        <div class="col-md-4">
                                                            <label>Employee</label>
                                                            <select class="form-select" name="time_logs[<?= $i ?>][employee_id]" required>
                                                                <?php foreach ($employees as $emp): ?>
                                                                    <option value="<?= $emp['employee_id'] ?>"
                                                                        <?= $log['employee_id'] == $emp['employee_id'] ? 'selected' : '' ?>>
                                                                        <?= htmlspecialchars($emp['name']) ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label>Start Time</label>
                                                            <input type="datetime-local" class="form-control" 
                                                                name="time_logs[<?= $i ?>][start_time]" 
                                                                value="<?= date('Y-m-d\TH:i', strtotime($log['start_time'])) ?>" required>
                                                        </div>
                                                        <div class="col-md-3">
                                                            <label>End Time</label>
                                                            <input type="datetime-local" class="form-control" 
                                                                name="time_logs[<?= $i ?>][end_time]" 
                                                                value="<?= date('Y-m-d\TH:i', strtotime($log['end_time'])) ?>" required>
                                                        </div>
                                                        <div class="col-md-2">
                                                            <label>Hours</label>
                                                            <input type="number" step="0.25" class="form-control" 
                                                                name="time_logs[<?= $i ?>][logged_hours]" 
                                                                value="<?= $log['logged_hours'] ?>" required>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div class="time-log-entry border p-2 mb-2">
                                                <div class="row">
                                                    <div class="col-md-4">
                                                        <label>Employee</label>
                                                        <select class="form-select" name="time_logs[0][employee_id]" required>
                                                            <?php foreach ($employees as $emp): ?>
                                                                <option value="<?= $emp['employee_id'] ?>">
                                                                    <?= htmlspecialchars($emp['name']) ?>
                                                                </option>
                                                            <?php endforeach; ?>
                                                        </select>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label>Start Time</label>
                                                        <input type="datetime-local" class="form-control" 
                                                            name="time_logs[0][start_time]" 
                                                            value="<?= date('Y-m-d\TH:i') ?>" required>
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label>End Time</label>
                                                        <input type="datetime-local" class="form-control" 
                                                            name="time_logs[0][end_time]" 
                                                            value="<?= date('Y-m-d\TH:i', time()+3600) ?>" required>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <label>Hours</label>
                                                        <input type="number" step="0.25" class="form-control" 
                                                            name="time_logs[0][logged_hours]" value="1.00" required>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        <button type="button" id="addTimeLog" class="btn btn-sm btn-outline-primary mt-2">
                                            <i class="fas fa-plus"></i> Add Time Log
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                        
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" name="notes" rows="3"><?= 
                                htmlspecialchars($task_data['notes']) ?></textarea>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="tasks.php" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <?= $action === 'complete' ? 'Complete Task' : 'Save Task' ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Time log management
    let logCounter = <?= !empty($time_logs) ? count($time_logs) : 1 ?>;
    
    document.getElementById('addTimeLog').addEventListener('click', function() {
        const newEntry = document.createElement('div');
        newEntry.className = 'time-log-entry border p-2 mb-2';
        newEntry.innerHTML = `
            <div class="row">
                <div class="col-md-4">
                    <label>Employee</label>
                    <select class="form-select" name="time_logs[${logCounter}][employee_id]" required>
                        <?php foreach ($employees as $emp): ?>
                            <option value="<?= $emp['employee_id'] ?>">
                                <?= htmlspecialchars($emp['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label>Start Time</label>
                    <input type="datetime-local" class="form-control" 
                        name="time_logs[${logCounter}][start_time]" 
                        value="<?= date('Y-m-d\TH:i') ?>" required>
                </div>
                <div class="col-md-3">
                    <label>End Time</label>
                    <input type="datetime-local" class="form-control" 
                        name="time_logs[${logCounter}][end_time]" 
                        value="<?= date('Y-m-d\TH:i', time()+3600) ?>" required>
                </div>
                <div class="col-md-2">
                    <label>Hours</label>
                    <input type="number" step="0.25" class="form-control" 
                        name="time_logs[${logCounter}][logged_hours]" value="1.00" required>
                </div>
            </div>
        `;
        document.getElementById('timeLogsContainer').insertBefore(newEntry, this);
        logCounter++;
    });
});
</script>

<?php require_once '../../includes/footer.php'; ?>