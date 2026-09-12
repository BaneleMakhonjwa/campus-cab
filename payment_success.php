<?php
$method = $_GET['method'] ?? 'Card';
$receipt = 'REC-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f0f4f8; display: flex; justify-content: center; align-items: center; min-height: 100vh; padding: 1rem; }
        .container { max-width: 450px; width: 100%; background: white; padding: 2.5rem 2rem; border-radius: 16px; text-align: center; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        .icon { font-size: 4rem; color: #059669; margin-bottom: 1rem; }
        h2 { color: #0b2b4a; margin-bottom: 0.5rem; }
        p { color: #475569; margin-bottom: 1.5rem; }
        .details { background: #f8fafc; padding: 1.5rem; border-radius: 8px; margin: 1.5rem 0; text-align: left; border: 1px solid #e2e8f0; }
        .details h3 { margin-bottom: 0.75rem; color: #0b2b4a; font-size: 1rem; }
        .row { display: flex; justify-content: space-between; padding: 0.5rem 0; border-bottom: 1px solid #e2e8f0; }
        .row:last-child { border-bottom: none; }
        .row strong { color: #0b2b4a; }
        .btn { background: #1d4ed8; color: white; border: none; padding: 0.6rem 1.5rem; border-radius: 8px; cursor: pointer; text-decoration: none; display: inline-block; margin: 0.25rem; font-weight: 600; transition: all 0.2s; }
        .btn:hover { background: #1e3f9e; }
        .btn-outline { background: transparent; color: #1d4ed8; border: 2px solid #1d4ed8; }
        .btn-outline:hover { background: #eff6ff; }
        .btn-group { display: flex; gap: 0.5rem; justify-content: center; flex-wrap: wrap; margin-top: 0.5rem; }
        @media (max-width: 480px) { .container { padding: 1.5rem; } }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">✅</div>
        <h2>Payment Successful!</h2>
        <p>Your payment has been processed successfully.</p>
        
        <div class="details">
            <h3>Payment Details</h3>
            <div class="row"><span>Receipt Number</span><strong><?= htmlspecialchars($receipt) ?></strong></div>
            <div class="row"><span>Amount</span><strong>R 50.00</strong></div>
            <div class="row"><span>Payment Method</span><strong><?= htmlspecialchars($method) ?></strong></div>
            <div class="row"><span>Date</span><strong><?= date('d M Y H:i') ?></strong></div>
        </div>
        
        <div class="btn-group">
            <a href="process_payment.php" class="btn">💰 Pay Again</a>
            <a href="../index.php" class="btn btn-outline">🏠 Home</a>
        </div>
    </div>
</body>
</html>