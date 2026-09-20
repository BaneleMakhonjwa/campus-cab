<?php
header('Content-Type: application/json');
require_once 'db_connect.php';

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

function log_activity($conn, $actor_name, $action_type, $target_type, $target_id, $description) {
    $stmt = mysqli_prepare($conn, "INSERT INTO user_activity_log (actor_type, actor_name, action_type, target_type, target_id, description) VALUES ('Admin', ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "sssss", $actor_name, $action_type, $target_type, $target_id, $description);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
}

$adminName = $input['adminName'] ?? 'Admin';

switch ($action) {

    case 'approve_driver':
        $id = (int) $input['driverId'];
        mysqli_query($conn, "UPDATE driver SET driverAccount_Status = 'Active', rejection_reason = NULL WHERE DriverID = $id");
        log_activity($conn, $adminName, 'driver_approved', 'driver', $id, "Driver ID $id approved by $adminName.");
        echo json_encode(['success' => true]);
        break;

    case 'reject_driver':
        $id = (int) $input['driverId'];
        $reason = mysqli_real_escape_string($conn, $input['reason'] ?? '');
        if (trim($reason) === '') {
            echo json_encode(['success' => false, 'error' => 'A rejection reason is required.']);
            break;
        }
        mysqli_query($conn, "UPDATE driver SET driverAccount_Status = 'Rejected', rejection_reason = '$reason' WHERE DriverID = $id");
        log_activity($conn, $adminName, 'driver_rejected', 'driver', $id, "Driver ID $id rejected. Reason: $reason");
        echo json_encode(['success' => true]);
        break;

    case 'resolve_complaint':
        $id = (int) $input['complaintId'];
        $resolution = mysqli_real_escape_string($conn, $input['resolution'] ?? '');
        if (trim($resolution) === '') {
            echo json_encode(['success' => false, 'error' => 'A resolution note is required.']);
            break;
        }
        mysqli_query($conn, "UPDATE complaint SET status = 'Resolved', resolution = '$resolution', resolved_at = NOW() WHERE complaint_id = $id");
        log_activity($conn, $adminName, 'complaint_resolved', 'complaint', $id, "Complaint #$id resolved. Note: $resolution");
        echo json_encode(['success' => true]);
        break;

    case 'resolve_claim':
        $id = (int) $input['claimId'];
        $note = mysqli_real_escape_string($conn, $input['note'] ?? '');
        if (trim($note) === '') {
            echo json_encode(['success' => false, 'error' => 'A resolution note is required.']);
            break;
        }
        mysqli_query($conn, "UPDATE driver_claim SET status = 'Resolved', resolution_note = '$note', resolved_at = NOW() WHERE claim_id = $id");
        log_activity($conn, $adminName, 'claim_resolved', 'driver_claim', $id, "Claim #$id resolved. Note: $note");
        echo json_encode(['success' => true]);
        break;

    case 'save_setting':
        $key = mysqli_real_escape_string($conn, $input['key'] ?? '');
        $value = mysqli_real_escape_string($conn, $input['value'] ?? '');
        if ($key === '') {
            echo json_encode(['success' => false, 'error' => 'Missing setting key.']);
            break;
        }
        mysqli_query($conn, "
            INSERT INTO system_settings (setting_key, setting_value)
            VALUES ('$key', '$value')
            ON DUPLICATE KEY UPDATE setting_value = '$value'
        ");
        log_activity($conn, $adminName, 'setting_updated', 'system_settings', $key, "Setting '$key' updated to '$value'.");
        echo json_encode(['success' => true]);
        break;

    default:
        echo json_encode(['success' => false, 'error' => 'Unknown action.']);
}

mysqli_close($conn);
?>
