<?php
header('Content-Type: application/json');
include '../includes/db_config.php';

$input = json_decode(file_get_contents('php://input'), true);

$apiKey = isset($_SERVER['HTTP_X_API_KEY']) ? $_SERVER['HTTP_X_API_KEY'] : '';
$validApiKey = 'your_secret_hardware_key_12345'; // PA-CHANGE DAW PO

if ($apiKey !== $validApiKey) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$updates = [];

if (isset($input['weight'])) {
    $weight = intval($input['weight']);
    $sql = "UPDATE device_settings SET setting_value = '$weight' WHERE setting_key = 'current_weight'";
    mysqli_query($conn, $sql);
    $updates[] = 'weight';
}

if (isset($input['battery'])) {
    $battery = intval($input['battery']);
    $sql = "UPDATE device_settings SET setting_value = '$battery' WHERE setting_key = 'battery_level'";
    mysqli_query($conn, $sql);
    $updates[] = 'battery';
}

$sql = "UPDATE device_settings SET setting_value = '1' WHERE setting_key = 'is_connected'";
mysqli_query($conn, $sql);

$now = date('Y-m-d H:i:s');
$sql = "UPDATE device_settings SET setting_value = '$now' WHERE setting_key = 'last_heartbeat'";
mysqli_query($conn, $sql);

if (isset($input['dispensed'])) {
    $rounds = intval($input['dispensed']);
    $date = date('Y-m-d');
    $time = date('H:i:s');
    $type = isset($input['type']) ? $input['type'] : 'Scheduled';
    
    $sql = "INSERT INTO history (feed_date, feed_time, rounds, type, status) 
            VALUES ('$date', '$time', $rounds, '$type', 'Success')";
    mysqli_query($conn, $sql);
    
    $alertMsg = "Device dispensed $rounds rounds automatically.";
    $alertSql = "INSERT INTO alerts (alert_type, message, is_read) 
                 VALUES ('Info', '$alertMsg', 0)";
    mysqli_query($conn, $alertSql);
}

echo json_encode([
    'success' => true, 
    'message' => 'Hardware update received',
    'updated' => $updates
]);

mysqli_close($conn);
?>