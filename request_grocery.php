<?php
session_start();

// Check if student is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: login_student.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Personal shopper | Campus Cab</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Include header -->
    <?php include 'header.php'; ?>

    <div class="container">
        <h1>🛒 Request Personal shopper</h1>
        <p>Fill in the form below to request a driver to buy groceries for you.</p>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                ✅ Grocery request submitted successfully!
                <br><br>
                <a href="index.php" class="btn-primary">🏠 Back to Home</a>
            </div>
        <?php else: ?>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger">
                    ❌ Error: <?php echo htmlspecialchars($_GET['error']); ?>
                    <br><br>
                    <a href="request_grocery.php" class="btn-secondary">← Go Back</a>
                </div>
            <?php endif; ?>

            <form action="submit_grocery.php" method="POST">
                <div class="form-group">
                    <label for="store_name">Select Store:</label>
                    <select name="store_name" id="store_name" required>
                        <option value="">-- Select Store --</option>
                        <option value="Pick n Pay">Pick n Pay</option>
                        <option value="Spar">Spar</option>
                        <option value="Shoprite">Shoprite</option>
                        <option value="Checkers">Checkers</option>
                        <option value="Woolworths">Woolworths</option>
                        <option value="Local Corner Shop">Local Corner Shop</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="items_list">Shopping List (Be Specific):</label>
                    <textarea name="items_list" id="items_list" rows="6" placeholder="e.g., 2L Clover Full Cream Milk, 1 loaf Albany Brown Bread, 1kg Chicken Breast..." required></textarea>
                </div>

                <div class="form-group">
                    <label for="max_budget">Maximum Budget (ZAR R):</label>
                    <input type="number" name="max_budget" id="max_budget" min="10" step="0.01" placeholder="e.g., 250.00" required>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn-primary">✅ Submit Grocery Request</button>
                    <a href="index.php" class="btn-secondary">🏠 Back to Home</a>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>