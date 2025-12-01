<?php
header('Content-Type: application/json');
include '../includes/db_config.php';

$input = json_decode(file_get_contents('php://input'), true);
$id = intval($input['id']);

$sql = "DELETE FROM schedules WHERE id = $id";

if (mysqli_query($conn, $sql)) {
    echo json_encode(['success' => true, 'message' => 'Schedule deleted successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error deleting schedule']);
}

mysqli_close($conn);
?>