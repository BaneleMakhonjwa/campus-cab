<?php
include 'db_connect.php';

echo "<h2>Connection test</h2>";

$result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM student");
$row = mysqli_fetch_assoc($result);

echo "Students in database: " . $row['total'];
?>