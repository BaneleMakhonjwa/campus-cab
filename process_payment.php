<?php
// Simple standalone payment page - no includes needed!

$error = '';
$amount = 50.00;
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paymentMethod = $_POST['payment_method'] ?? '';
    
    if ($paymentMethod === 'cash') {
        $success = true;
        $method = 'Cash';
    } elseif ($paymentMethod === 'card') {
        $cardNumber = str_replace(' ', '', $_POST['card_number'] ?? '');
        $expiryMonth = $_POST['expiry_month'] ?? '';
        $expiryYear = $_POST['expiry_year'] ?? '';
        $cvv = $_POST['cvv'] ?? '';
        
        if (strlen($cardNumber) >= 15 && $expiryMonth && $expiryYear && $cvv) {
            $success = true;
            $method = 'Card';
            $receipt = 'REC-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
        } else {
            $error = 'Please enter valid card details.';
        }
    } else {
        $error = 'Please select a payment method.';
    }
    
    if ($success) {
        header('Location: payment_success.php?method=' . $method);
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - Campus Cab</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f0f4f8; padding: 2rem; display: flex; justify-content: center; align-items: center; min-height: 100vh; }
        .container { max-width: 500px; width: 100%; background: white; padding: 2rem; border-radius: 16px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
        h2 { color: #0b2b4a; margin-bottom: 0.5rem; }
        .subtitle { color: #64748b; margin-bottom: 1.5rem; font-size: 0.95rem; }
        .summary { background: #f8fafc; padding: 1rem 1.5rem; border-radius: 8px; margin-bottom: 1.5rem; border: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; }
        .summary .amount { font-size: 1.8rem; font-weight: bold; color: #1d4ed8; }
        .payment-options { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem; }
        .payment-option { padding: 1rem; border: 2px solid #e2e8f0; border-radius: 8px; text-align: center; cursor: pointer; transition: all 0.2s; }
        .payment-option:hover { border-color: #1d4ed8; }
        .payment-option.selected { border-color: #1d4ed8; background: #eff6ff; }
        .payment-option h4 { font-size: 1rem; }
        .payment-option p { font-size: 0.8rem; color: #64748b; }
        .card-details { background: #f8fafc; padding: 1.5rem; border-radius: 8px; margin-bottom: 1.5rem; display: none; }
        .card-details.active { display: block; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.3rem; font-weight: 600; color: #1e293b; font-size: 0.9rem; }
        .form-group input, .form-group select { width: 100%; padding: 0.6rem; border: 1px solid #d1d5db; border-radius: 6px; font-size: 1rem; }
        .form-group input:focus, .form-group select:focus { outline: none; border-color: #1d4ed8; box-shadow: 0 0 0 3px rgba(29,78,216,0.1); }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        .btn { background: #1d4ed8; color: white; border: none; padding: 0.75rem; border-radius: 8px; cursor: pointer; font-size: 1rem; width: 100%; font-weight: 600; transition: all 0.2s; }
        .btn:hover { background: #1e3f9e; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(29,78,216,0.3); }
        .alert-danger { background: #fee2e2; color: #991b1b; padding: 0.75rem 1rem; border-radius: 8px; margin-bottom: 1rem; font-size: 0.9rem; }
        .checkbox-group { display: flex; align-items: center; gap: 0.5rem; }
        .checkbox-group input { width: auto; }
        .checkbox-group label { margin: 0; cursor: pointer; }
        @media (max-width: 480px) { body { padding: 1rem; } .container { padding: 1.5rem; } }
    </style>
</head>
<body>
    <div class="container">
        <h2>💳 Make Payment</h2>
        <p class="subtitle">Complete your payment securely</p>
        
        <?php if ($error): ?>
            <div class="alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <div class="summary">
            <span><strong>Amount</strong></span>
            <span class="amount">R <?= number_format($amount, 2) ?></span>
        </div>
        
        <form method="POST">
            <div class="payment-options">
                <div class="payment-option selected" onclick="selectPayment('card')">
                    <h4>💳 Card</h4>
                    <p>Visa, Mastercard</p>
                </div>
                <div class="payment-option" onclick="selectPayment('cash')">
                    <h4>💰 Cash</h4>
                    <p>Pay after service</p>
                </div>
            </div>
            
            <input type="hidden" name="payment_method" id="payment_method" value="card">
            
            <div class="card-details active" id="cardDetails">
                <div class="form-group">
                    <label>Card Number</label>
                    <input type="text" name="card_number" placeholder="4111 1111 1111 1111" required>
                </div>
                <div class="form-group">
                    <label>Name on Card</label>
                    <input type="text" name="card_name" placeholder="John Doe">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Expiry Month</label>
                        <select name="expiry_month" required>
                            <option value="">Month</option>
                            <?php for ($m = 1; $m <= 12; $m++): ?>
                                <option value="<?= $m ?>"><?= str_pad($m, 2, '0', STR_PAD_LEFT) ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Expiry Year</label>
                        <select name="expiry_year" required>
                            <option value="">Year</option>
                            <?php for ($y = date('Y'); $y <= date('Y') + 10; $y++): ?>
                                <option value="<?= $y ?>"><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>CVV</label>
                    <input type="password" name="cvv" placeholder="123" maxlength="4" required>
                </div>
                <div class="form-group checkbox-group">
                    <input type="checkbox" name="save_card" value="1">
                    <label>Save this card for future payments</label>
                </div>
            </div>
            
            <div class="card-details" id="cashDetails">
                <div style="padding: 1.5rem; text-align: center; background: #fef3c7; border-radius: 8px;">
                    <h3>💰 Cash Payment</h3>
                    <p style="margin-top: 0.5rem;">You will pay the driver in cash after the service.</p>
                    <p style="font-size: 0.9rem; color: #64748b; margin-top: 0.5rem;">Please have the exact amount ready.</p>
                </div>
            </div>
            
            <button type="submit" class="btn">Complete Payment</button>
        </form>
    </div>
    
    <script>
        function selectPayment(method) {
            document.getElementById('payment_method').value = method;
            document.querySelectorAll('.payment-option').forEach(el => el.classList.remove('selected'));
            document.querySelectorAll('.payment-option').forEach(el => {
                if (el.querySelector('h4').textContent.toLowerCase().includes(method)) {
                    el.classList.add('selected');
                }
            });
            document.getElementById('cardDetails').classList.toggle('active', method === 'card');
            document.getElementById('cashDetails').classList.toggle('active', method === 'cash');
        }
    </script>
</body>
</html>