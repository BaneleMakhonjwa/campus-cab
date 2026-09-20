<?php
header('Content-Type: application/json');
require_once 'db_connect.php';

$log = [];
$res = mysqli_query($conn, "SELECT * FROM user_activity_log ORDER BY created_at DESC LIMIT 100");
while ($row = mysqli_fetch_assoc($res)) {
    $log[] = $row;
}

echo json_encode($log);
mysqli_close($conn);
?>
