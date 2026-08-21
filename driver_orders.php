<?php
session_start();

// Check if user is a driver
if (!isset($_SESSION['driver_id'])) {
    header("Location: login_driver.php");
    exit();
}

include_once __DIR__ . '/db_connect.php';

$driver_id = $_SESSION['driver_id'];

// Handle status update with receipt upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    $receipt_image = null;
    
    // Handle receipt upload for DELIVERING status
    if ($action === 'DELIVERING' && isset($_FILES['receipt_image']) && $_FILES['receipt_image']['error'] === 0) {
        $target_dir = __DIR__ . '/uploads/receipts/';
        
        // Create folder if it doesn't exist
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }
        
        $file_extension = strtolower(pathinfo($_FILES['receipt_image']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = ['jpg', 'jpeg', 'png', 'pdf'];
        
        if (in_array($file_extension, $allowed_extensions)) {
            $new_filename = 'receipt_' . $order_id . '_' . time() . '.' . $file_extension;
            $target_file = $target_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['receipt_image']['tmp_name'], $target_file)) {
                $receipt_image = 'uploads/receipts/' . $new_filename;
            } else {
                header("Location: driver_orders.php?error=Failed to upload receipt image");
                exit();
            }
        } else {
            header("Location: driver_orders.php?error=Invalid file type. Please upload JPG, PNG, or PDF.");
            exit();
        }
    }
    
    // Update order status
    $valid_actions = ['ACCEPTED', 'BUYING', 'DELIVERING', 'COMPLETED'];
    if (in_array($action, $valid_actions)) {
        if ($action === 'DELIVERING' && $receipt_image) {
            $update_sql = "UPDATE grocery_orders SET status = ?, driver_id = ?, receipt_image = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("sisi", $action, $driver_id, $receipt_image, $order_id);
        } else {
            $update_sql = "UPDATE grocery_orders SET status = ?, driver_id = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("sii", $action, $driver_id, $order_id);
        }
        
        if ($update_stmt->execute()) {
            header("Location: driver_orders.php?success=1");
        } else {
            header("Location: driver_orders.php?error=" . urlencode($conn->error));
        }
        $update_stmt->close();
        exit();
    }
}

// Fetch pending and accepted orders
$sql = "SELECT * FROM grocery_orders WHERE driver_id = ? OR status = 'PENDING' ORDER BY created_at ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $driver_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Driver - Grocery Orders | Campus Cab</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'header.php'; ?>

    <div class="container">
        <div class="page-header">
            <h1>🚗 Grocery Order Management</h1>
            <a href="index.php" class="btn-secondary">🏠 Back to Home</a>
        </div>
        
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">✅ Order status updated successfully!</div>
        <?php endif; ?>
        
        <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-danger">❌ Error: <?php echo htmlspecialchars($_GET['error']); ?></div>
        <?php endif; ?>

        <?php if ($result->num_rows === 0): ?>
            <div class="alert alert-info">
                <p>No grocery orders available right now.</p>
                <br>
                <a href="index.php" class="btn-secondary">🏠 Back to Home</a>
            </div>
        <?php else: ?>

        <table class="orders-table">
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Student ID</th>
                    <th>Store</th>
                    <th>Items</th>
                    <th>Budget</th>
                    <th>Status</th>
                    <th>Receipt</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td>#<?php echo $row['id']; ?></td>
                        <td><?php echo $row['student_id']; ?></td>
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
                                <a href="<?php echo htmlspecialchars($row['receipt_image']); ?>" target="_blank" class="btn-small">📄 View</a>
                            <?php else: ?>
                                <span style="color: #999;">No receipt</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($row['status'] === 'PENDING'): ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" name="action" value="ACCEPTED" class="btn-small">Accept</button>
                                </form>
                                
                            <?php elseif ($row['status'] === 'ACCEPTED'): ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" name="action" value="BUYING" class="btn-small">Start Buying</button>
                                </form>
                                
                            <?php elseif ($row['status'] === 'BUYING'): ?>
                                <form method="POST" enctype="multipart/form-data" style="display:inline;">
                                    <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
                                    <input type="file" name="receipt_image" accept="image/*,.pdf" required style="font-size:12px; max-width:120px;">
                                    <button type="submit" name="action" value="DELIVERING" class="btn-small">📄 Deliver</button>
                                </form>
                                
                            <?php elseif ($row['status'] === 'DELIVERING'): ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="order_id" value="<?php echo $row['id']; ?>">
                                    <button type="submit" name="action" value="COMPLETED" class="btn-small">Complete</button>
                                </form>
                                
                            <?php else: ?>
                                <span>✅ Completed</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>