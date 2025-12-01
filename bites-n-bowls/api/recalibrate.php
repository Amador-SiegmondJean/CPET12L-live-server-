<?php
header('Content-Type: application/json');
include '../includes/db_config.php';

$sql = "UPDATE device_settings SET setting_value = '1000' WHERE setting_key = 'current_weight'";
mysqli_query($conn, $sql);

$now = date('Y-m-d H:i:s');
$sql2 = "UPDATE device_settings SET setting_value = '$now', updated_at = '$now' WHERE setting_key = 'last_calibration'";
mysqli_query($conn, $sql2);

$date = date('Y-m-d');
$time = date('H:i:s');
$historySql = "INSERT INTO history (feed_date, feed_time, rounds, type, status) 
               VALUES ('$date', '$time', 0, 'Recalibrate', 'Success')";
mysqli_query($conn, $historySql);

$alertSql = "INSERT INTO alerts (alert_type, message, is_read) 
             VALUES ('Info', 'Sensor recalibrated. Weight reset to 1000g.', 0)";
mysqli_query($conn, $alertSql);

echo json_encode(['success' => true, 'currentWeight' => 1000]);
mysqli_close($conn);
?>