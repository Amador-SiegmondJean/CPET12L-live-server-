<?php
header('Content-Type: application/json');
include '../includes/db_config.php';

$sql = "SELECT * FROM schedules WHERE is_active = 1 ORDER BY created_at DESC";
$result = mysqli_query($conn, $sql);

$schedules = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $schedules[] = [
            'id' => $row['id'],
            'interval' => $row['interval_type'],
            'time' => $row['start_time'],
            'rounds' => $row['rounds'],
            'frequency' => $row['frequency'],
            'customDays' => $row['custom_days']
        ];
    }
}

echo json_encode(['success' => true, 'schedules' => $schedules]);
mysqli_close($conn);
?>