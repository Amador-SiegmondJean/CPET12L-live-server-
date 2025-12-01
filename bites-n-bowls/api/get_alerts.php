<?php
header('Content-Type: application/json');
include '../includes/db_config.php';

$sql = "SELECT * FROM alerts ORDER BY created_at DESC LIMIT 50";
$result = mysqli_query($conn, $sql);

$alerts = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $alerts[] = [
            'id' => $row['id'],
            'type' => $row['alert_type'],
            'message' => $row['message'],
            'timestamp' => $row['created_at']
        ];
    }
}

echo json_encode(['success' => true, 'alerts' => $alerts]);
mysqli_close($conn);
?>