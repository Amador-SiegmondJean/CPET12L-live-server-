<?php
header('Content-Type: application/json');
include '../includes/db_config.php';

$input = json_decode(file_get_contents('php://input'), true);
$rounds = intval($input['rounds']);

$sql = "INSERT INTO command_queue (command_type, command_data, status, created_at) 
        VALUES ('dispense', '$rounds', 'pending', NOW())";

if (mysqli_query($conn, $sql)) {
    $commandId = mysqli_insert_id($conn);
    echo json_encode(['success' => true, 'command_id' => $commandId]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to queue command']);
}

mysqli_close($conn);
?>