<?php
session_start();

if (!isset($_SESSION['student_id'])) {
    header("Location: login_student.php");
    exit();
}

include_once __DIR__ . '/db_connect.php';

$student_id = $_SESSION['student_id'];

// Fetch student's orders
$sql = "SELECT * FROM grocery_orders WHERE student_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $student_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Grocery Orders | Campus Cab</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container">
        <div class="page-header">
            <h1>📦 My Grocery Orders</h1>
            <div class="header-actions">
                <a href="request_grocery.php" class="btn-primary">🛒 New Request</a>
                <a href="index.php" class="btn-secondary">🏠 Back to Home</a>
            </div>
        </div>

        <?php if ($result->num_rows === 0): ?>
            <div class="alert alert-info">
                <p>You haven't placed any grocery orders yet.</p>
                <br>
                <a href="request_grocery.php" class="btn-primary">🛒 Place Your First Order</a>
            </div>
        <?php else: ?>

        <table class="orders-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Store</th>
                    <th>Items</th>
                    <th>Budget</th>
                    <th>Status</th>
                    <th>Receipt</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td>#<?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['store_name']); ?></td>
                        <td><?php echo htmlspecialchars(substr($row['items_list'], 0, 50)) . '...'; ?></td>
                        <td>R<?php echo number_format($row['max_budget'], 2); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo strtolower($row['status']); ?>">
                                <?php echo $row['status']; ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($row['receipt_image']): ?>
                                <a href="<?php echo htmlspecialchars($row['receipt_image']); ?>" target="_blank" class="btn-small">📄 View Receipt</a>
                            <?php else: ?>
                                <span style="color: #999;">Awaiting receipt</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>