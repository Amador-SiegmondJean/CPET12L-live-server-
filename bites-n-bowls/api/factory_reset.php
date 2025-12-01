<?php
header('Content-Type: application/json');
include '../includes/db_config.php';

mysqli_query($conn, "TRUNCATE TABLE schedules");
mysqli_query($conn, "TRUNCATE TABLE history");
mysqli_query($conn, "TRUNCATE TABLE alerts");

mysqli_query($conn, "UPDATE device_settings SET setting_value = '0' WHERE setting_key = 'current_weight'");
mysqli_query($conn, "UPDATE device_settings SET setting_value = 'Not Set' WHERE setting_key = 'wifi_ssid'");
mysqli_query($conn, "UPDATE device_settings SET setting_value = '0' WHERE setting_key = 'battery_level'");
mysqli_query($conn, "UPDATE device_settings SET setting_value = '0' WHERE setting_key = 'is_connected'");

mysqli_query($conn, "UPDATE users SET password = '1234' WHERE username = 'admin'");

echo json_encode(['success' => true, 'message' => 'Factory reset complete']);
mysqli_close($conn);
?>