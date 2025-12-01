<?php
header('Content-Type: application/json');
include '../includes/db_config.php';

$input = json_decode(file_get_contents('php://input'), true);

$rounds = intval($input['rounds']);
$type = mysqli_real_escape_string($conn, $input['type']);
$weightDispensed = intval($input['weightDispensed']);

$weightResult = mysqli_query($conn, "SELECT setting_value FROM device_settings WHERE setting_key = 'current_weight'");
$weightRow = mysqli_fetch_assoc($weightResult);
$currentWeight = intval($weightRow['setting_value']);

if ($currentWeight < $weightDispensed) {
    $now = date('Y-m-d');
    $time = date('H:i:s');
    $sql = "INSERT INTO history (feed_date, feed_time, rounds, type, status) 
            VALUES ('$now', '$time', $rounds, '$type', 'Failed (Low Feed)')";
    mysqli_query($conn, $sql);
    
    $alertMsg = "Dispense failed: Insufficient feed for $rounds rounds.";
    $alertSql = "INSERT INTO alerts (alert_type, message, is_read) VALUES ('Error', '$alertMsg', 0)";
    mysqli_query($conn, $alertSql);
    
    echo json_encode([
        'success' => false, 
        'message' => "Insufficient feed. Only {$currentWeight}g remaining.",
        'currentWeight' => $currentWeight
    ]);
    mysqli_close($conn);
    exit;
}

$newWeight = $currentWeight - $weightDispensed;
$updateWeightSql = "UPDATE device_settings SET setting_value = '$newWeight' WHERE setting_key = 'current_weight'";
mysqli_query($conn, $updateWeightSql);

$now = date('Y-m-d');
$time = date('H:i:s');
$sql = "INSERT INTO history (feed_date, feed_time, rounds, type, status) 
        VALUES ('$now', '$time', $rounds, '$type', 'Success')";
mysqli_query($conn, $sql);

$alertMsg = "Successfully dispensed $rounds rounds.";
$alertSql = "INSERT INTO alerts (alert_type, message, is_read) VALUES ('Info', '$alertMsg', 0)";
mysqli_query($conn, $alertSql);

if ($newWeight < 100) {
    $warningMsg = "Feed supply critically low (<100g).";
    $warningSql = "INSERT INTO alerts (alert_type, message, is_read) VALUES ('Warning', '$warningMsg', 0)";
    mysqli_query($conn, $warningSql);
}

echo json_encode([
    'success' => true, 
    'message' => "Successfully dispensed $rounds rounds ({$weightDispensed}g).",
    'currentWeight' => $newWeight
]);

mysqli_close($conn);
?>