<?php
header('Content-Type: application/json');
require_once '../includes/db_config.php';

$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');

$response = ['success' => false, 'message' => 'Invalid action'];

switch ($action) {
    
    case 'get_schedules':
        $sql = "SELECT * FROM schedules WHERE is_active = 1 ORDER BY created_at DESC";
        $result = mysqli_query($conn, $sql);
        $schedules = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $schedules[] = $row;
        }
        $response = ['success' => true, 'data' => $schedules];
        break;
    
    case 'add_schedule':
        $interval = mysqli_real_escape_string($conn, $_POST['interval']);
        $time = mysqli_real_escape_string($conn, $_POST['time']);
        $rounds = intval($_POST['rounds']);
        $frequency = mysqli_real_escape_string($conn, $_POST['frequency']);
        $customDays = isset($_POST['customDays']) ? mysqli_real_escape_string($conn, $_POST['customDays']) : '';
        
        $sql = "INSERT INTO schedules (interval_type, start_time, rounds, frequency, custom_days) 
                VALUES ('$interval', '$time', $rounds, '$frequency', '$customDays')";
        
        if (mysqli_query($conn, $sql)) {
            $response = ['success' => true, 'message' => 'Schedule added successfully', 'id' => mysqli_insert_id($conn)];
        } else {
            $response = ['success' => false, 'message' => 'Error: ' . mysqli_error($conn)];
        }
        break;
    
    case 'update_schedule':
        $id = intval($_POST['id']);
        $interval = mysqli_real_escape_string($conn, $_POST['interval']);
        $time = mysqli_real_escape_string($conn, $_POST['time']);
        $rounds = intval($_POST['rounds']);
        $frequency = mysqli_real_escape_string($conn, $_POST['frequency']);
        $customDays = isset($_POST['customDays']) ? mysqli_real_escape_string($conn, $_POST['customDays']) : '';
        
        $sql = "UPDATE schedules SET 
                interval_type = '$interval', 
                start_time = '$time', 
                rounds = $rounds, 
                frequency = '$frequency', 
                custom_days = '$customDays' 
                WHERE id = $id";
        
        if (mysqli_query($conn, $sql)) {
            $response = ['success' => true, 'message' => 'Schedule updated successfully'];
        } else {
            $response = ['success' => false, 'message' => 'Error: ' . mysqli_error($conn)];
        }
        break;
    
    case 'delete_schedule':
        $id = intval($_POST['id']);
        $sql = "UPDATE schedules SET is_active = 0 WHERE id = $id";
        
        if (mysqli_query($conn, $sql)) {
            $response = ['success' => true, 'message' => 'Schedule deleted successfully'];
        } else {
            $response = ['success' => false, 'message' => 'Error: ' . mysqli_error($conn)];
        }
        break;
    
    case 'get_history':
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 0;
        $search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
        
        $sql = "SELECT * FROM history WHERE 1=1";
        if ($search) {
            $sql .= " AND (feed_date LIKE '%$search%' OR type LIKE '%$search%' OR status LIKE '%$search%')";
        }
        $sql .= " ORDER BY feed_date DESC, feed_time DESC";
        if ($limit > 0) {
            $sql .= " LIMIT $limit";
        }
        
        $result = mysqli_query($conn, $sql);
        $history = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $history[] = $row;
        }
        $response = ['success' => true, 'data' => $history];
        break;
    
    case 'add_history':
        $date = mysqli_real_escape_string($conn, $_POST['date']);
        $time = mysqli_real_escape_string($conn, $_POST['time']);
        $rounds = intval($_POST['rounds']);
        $type = mysqli_real_escape_string($conn, $_POST['type']);
        $status = mysqli_real_escape_string($conn, $_POST['status']);
        
        $sql = "INSERT INTO history (feed_date, feed_time, rounds, type, status) 
                VALUES ('$date', '$time', $rounds, '$type', '$status')";
        
        if (mysqli_query($conn, $sql)) {
            $response = ['success' => true, 'message' => 'History added successfully', 'id' => mysqli_insert_id($conn)];
        } else {
            $response = ['success' => false, 'message' => 'Error: ' . mysqli_error($conn)];
        }
        break;

    case 'get_alerts':
        $limit = isset($_GET['limit']) ? intval($_GET['limit']) : 0;
        
        $sql = "SELECT * FROM alerts ORDER BY created_at DESC";
        if ($limit > 0) {
            $sql .= " LIMIT $limit";
        }
        
        $result = mysqli_query($conn, $sql);
        $alerts = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $alerts[] = $row;
        }
        $response = ['success' => true, 'data' => $alerts];
        break;
    
    case 'add_alert':
        $type = mysqli_real_escape_string($conn, $_POST['type']);
        $message = mysqli_real_escape_string($conn, $_POST['message']);
        
        $sql = "INSERT INTO alerts (alert_type, message) VALUES ('$type', '$message')";
        
        if (mysqli_query($conn, $sql)) {
            $response = ['success' => true, 'message' => 'Alert added successfully', 'id' => mysqli_insert_id($conn)];
        } else {
            $response = ['success' => false, 'message' => 'Error: ' . mysqli_error($conn)];
        }
        break;

    case 'get_settings':
        $sql = "SELECT * FROM device_settings";
        $result = mysqli_query($conn, $sql);
        $settings = [];
        while ($row = mysqli_fetch_assoc($result)) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        $response = ['success' => true, 'data' => $settings];
        break;
    
    case 'update_setting':
        $key = mysqli_real_escape_string($conn, $_POST['key']);
        $value = mysqli_real_escape_string($conn, $_POST['value']);
        
        $sql = "INSERT INTO device_settings (setting_key, setting_value) 
                VALUES ('$key', '$value') 
                ON DUPLICATE KEY UPDATE setting_value = '$value'";
        
        if (mysqli_query($conn, $sql)) {
            $response = ['success' => true, 'message' => 'Setting updated successfully'];
        } else {
            $response = ['success' => false, 'message' => 'Error: ' . mysqli_error($conn)];
        }
        break;
    
    case 'factory_reset':
        mysqli_query($conn, "DELETE FROM schedules");
        mysqli_query($conn, "DELETE FROM history");
        mysqli_query($conn, "DELETE FROM alerts");
        mysqli_query($conn, "UPDATE device_settings SET setting_value = '0' WHERE setting_key = 'current_weight'");
        mysqli_query($conn, "UPDATE device_settings SET setting_value = '0' WHERE setting_key = 'is_connected'");
        mysqli_query($conn, "UPDATE device_settings SET setting_value = 'Not Set' WHERE setting_key = 'wifi_ssid'");
        
        $response = ['success' => true, 'message' => 'Factory reset completed'];
        break;
    
    case 'change_password':
        $oldPassword = mysqli_real_escape_string($conn, $_POST['oldPassword']);
        $newPassword = mysqli_real_escape_string($conn, $_POST['newPassword']);
        $username = mysqli_real_escape_string($conn, $_POST['username']);
        
        $sql = "SELECT * FROM users WHERE username = '$username' AND password = '$oldPassword'";
        $result = mysqli_query($conn, $sql);
        
        if (mysqli_num_rows($result) === 1) {
            $updateSql = "UPDATE users SET password = '$newPassword' WHERE username = '$username'";
            if (mysqli_query($conn, $updateSql)) {
                $response = ['success' => true, 'message' => 'Password changed successfully'];
            } else {
                $response = ['success' => false, 'message' => 'Error updating password'];
            }
        } else {
            $response = ['success' => false, 'message' => 'Old password is incorrect'];
        }
        break;
    
    default:
        $response = ['success' => false, 'message' => 'Unknown action: ' . $action];
        break;
}

echo json_encode($response);
mysqli_close($conn);
?>