<?php
header('Content-Type: application/json');
include '../includes/db_config.php';

$search = isset($_GET['search']) 
    ? mysqli_real_escape_string($conn, $_GET['search']) 
    : '';

$sql = "
    SELECT * FROM history 
    WHERE 
        feed_date LIKE '%$search%' OR
        feed_time LIKE '%$search%' OR
        type LIKE '%$search%' OR
        status LIKE '%$search%'
    ORDER BY created_at DESC
";

$result = mysqli_query($conn, $sql);

$history = [];

if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $history[] = [
            'id' => $row['id'],
            'date' => $row['feed_date'],
            'time' => $row['feed_time'],
            'rounds' => $row['rounds'],
            'type' => $row['type'],
            'status' => $row['status']
        ];
    }
}

echo json_encode([
    'success' => true, 
    'history' => $history
]);

mysqli_close($conn);
?>