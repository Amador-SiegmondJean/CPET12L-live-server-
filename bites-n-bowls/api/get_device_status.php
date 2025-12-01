<?php
header('Content-Type: application/json');
include '../includes/db_config.php';

$sql = "SELECT * FROM device_settings";
$result = mysqli_query($conn, $sql);

$status = [
    'online' => false,
    'weight' => 0,
    'battery' => 0,
    'wifi_signal' => 0,
    'last_heartbeat' => null
];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $key = $row['setting_key'];
        $value = $row['setting_value'];
        
        switch($key) {
            case 'is_connected':
                $status['online'] = $value == '1';
                break;
            case 'current_weight':
                $status['weight'] = intval($value);
                break;
            case 'battery_level':
                $status['battery'] = intval($value);
                break;
            case 'last_heartbeat':
                $status['last_heartbeat'] = $value;
                break;
        }
    }
}

if ($status['last_heartbeat']) {
    $lastBeat = strtotime($status['last_heartbeat']);
    $now = time();
    if (($now - $lastBeat) > 30) {
        $status['online'] = false;
    }
}

echo json_encode(['success' => true, 'status' => $status]);
mysqli_close($conn);
?>