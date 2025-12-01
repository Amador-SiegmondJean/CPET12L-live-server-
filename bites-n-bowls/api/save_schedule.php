<?php
header('Content-Type: application/json');
include '../includes/db_config.php';

$input = json_decode(file_get_contents('php://input'), true);

$id = isset($input['id']) ? intval($input['id']) : 0;
$interval = mysqli_real_escape_string($conn, $input['interval']);
$time = mysqli_real_escape_string($conn, $input['time']);
$rounds = intval($input['rounds']);
$frequency = mysqli_real_escape_string($conn, $input['frequency']);
$customDays = isset($input['customDays']) ? mysqli_real_escape_string($conn, $input['customDays']) : '';

if ($id > 0) {
    $sql = "UPDATE schedules SET 
            interval_type = '$interval',
            start_time = '$time',
            rounds = $rounds,
            frequency = '$frequency',
            custom_days = '$customDays'
            WHERE id = $id";
} else {
    $sql = "INSERT INTO schedules (interval_type, start_time, rounds, frequency, custom_days, is_active) 
            VALUES ('$interval', '$time', $rounds, '$frequency', '$customDays', 1)";
}

if (mysqli_query($conn, $sql)) {
    echo json_encode(['success' => true, 'message' => 'Schedule saved successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error saving schedule: ' . mysqli_error($conn)]);
}

mysqli_close($conn);
?>