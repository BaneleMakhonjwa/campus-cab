<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment History</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f0f4f8; padding: 2rem; display: flex; justify-content: center; }
        .container { max-width: 700px; width: 100%; background: white; padding: 2rem; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
        .header h2 { color: #0b2b4a; }
        .btn { background: #1d4ed8; color: white; border: none; padding: 0.5rem 1rem; border-radius: 8px; cursor: pointer; text-decoration: none; display: inline-block; font-weight: 600; transition: all 0.2s; }
        .btn:hover { background: #1e3f9e; }
        .btn-outline { background: transparent; color: #1d4ed8; border: 2px solid #1d4ed8; }
        .btn-outline:hover { background: #eff6ff; }
        .stats { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-bottom: 1.5rem; }
        .stat-card { background: #f8fafc; padding: 1rem; border-radius: 8px; text-align: center; border: 1px solid #e2e8f0; }
        .stat-card .number { font-size: 1.5rem; font-weight: bold; color: #1d4ed8; }
        .stat-card .label { font-size: 0.8rem; color: #64748b; margin-top: 0.2rem; }
        .empty { text-align: center; color: #64748b; padding: 2rem; border: 1px dashed #e2e8f0; border-radius: 8px; }
        .empty .icon { font-size: 2rem; display: block; margin-bottom: 0.5rem; }
        .btn-group { text-align: center; margin-top: 1.5rem; }
        @media (max-width: 480px) { body { padding: 1rem; } .container { padding: 1rem; } .stats { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>📜 Payment History</h2>
            <a href="process_payment.php" class="btn">💰 Pay Now</a>
        </div>
        
        <div class="stats">
            <div class="stat-card">
                <div class="number">R 0.00</div>
                <div class="label">Total Spent</div>
            </div>
            <div class="stat-card">
                <div class="number">0</div>
                <div class="label">Card Payments</div>
            </div>
            <div class="stat-card">
                <div class="number">0</div>
                <div class="label">Cash Payments</div>
            </div>
        </div>
        
        <div class="empty">
            <span class="icon">📭</span>
            No payment history found.<br>
            Make a payment to see it here.
        </div>
        
        <div class="btn-group">
            <a href="process_payment.php" class="btn">💰 Make Payment</a>
            <a href="../index.php" class="btn btn-outline">🏠 Home</a>
        </div>
    </div>
</body>
</html>
