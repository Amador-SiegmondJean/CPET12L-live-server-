<?php
header('Content-Type: application/json');
include '../includes/db_config.php';

$apiKey = isset($_SERVER['HTTP_X_API_KEY']) ? $_SERVER['HTTP_X_API_KEY'] : '';
$validApiKey = 'your_secret_hardware_key_12345'; // CHANGE THIS PO

if ($apiKey !== $validApiKey) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$currentDay = date('D'); 
$currentTime = date('H:i:s');

$sql = "SELECT * FROM schedules WHERE is_active = 1";
$result = mysqli_query($conn, $sql);

$activeSchedules = [];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $shouldDispense = false;
        
        switch($row['frequency']) {
            case 'daily':
                $shouldDispense = true;
                break;
            case 'weekdays':
                $shouldDispense = in_array($currentDay, ['Mon', 'Tue', 'Wed', 'Thu', 'Fri']);
                break;
            case 'weekends':
                $shouldDispense = in_array($currentDay, ['Sat', 'Sun']);
                break;
            case 'custom':
                $customDays = explode(',', $row['custom_days']);
                $customDays = array_map('trim', $customDays);
                $shouldDispense = in_array($currentDay, $customDays);
                break;
        }
        
        if ($shouldDispense) {
            $activeSchedules[] = [
                'id' => $row['id'],
                'interval' => $row['interval_type'],
                'start_time' => $row['start_time'],
                'rounds' => $row['rounds']
            ];
        }
    }
}

echo json_encode(['success' => true, 'schedules' => $activeSchedules, 'current_time' => $currentTime]);
mysqli_close($conn);
?>