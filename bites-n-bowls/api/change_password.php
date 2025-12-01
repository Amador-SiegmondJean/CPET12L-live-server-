<?php
header('Content-Type: application/json');
include '../includes/db_config.php';

$input = json_decode(file_get_contents('php://input'), true);

$oldPassword = $input['oldPassword'];
$newPassword = $input['newPassword'];
$username = $_SESSION['username'];

$sql = "SELECT password FROM users WHERE username = '$username'";
$result = mysqli_query($conn, $sql);
$user = mysqli_fetch_assoc($result);

if ($user['password'] !== $oldPassword) {
    echo json_encode(['success' => false, 'message' => 'Old password is incorrect']);
    mysqli_close($conn);
    exit;
}

$updateSql = "UPDATE users SET password = '$newPassword' WHERE username = '$username'";
if (mysqli_query($conn, $updateSql)) {
    echo json_encode(['success' => true, 'message' => 'Password updated successfully']);
} else {
    echo json_encode(['success' => false, 'message' => 'Error updating password']);
}

mysqli_close($conn);
?>