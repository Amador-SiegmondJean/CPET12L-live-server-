<?php
header('Content-Type: application/json');
include '../includes/db_config.php';

$sql = "SELECT * FROM device_settings";
$result = mysqli_query($conn, $sql);

$settings = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $settings[$row['setting_key']] = $row['setting_value'];
    }
}

echo json_encode(['success' => true, 'settings' => $settings]);
mysqli_close($conn);
?>