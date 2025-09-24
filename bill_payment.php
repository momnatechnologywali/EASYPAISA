<?php
include 'db.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit;
}
 
$user_id = $_SESSION['user_id'];
$type = $_GET['type'] ?? 'mobile'; // mobile, utility, subscription
$error = $success = '';
 
// Fetch providers
$sql_providers = "SELECT * FROM service_providers WHERE type = ?";
$result_providers = executePrepared($conn, $sql_providers, 's', [$type]);
$providers = [];
while ($row = $result_providers->fetch_assoc()) {
    $providers[] = $row;
}
 
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $provider_id = intval($_POST['provider_id']);
    $account_number = trim($_POST['account_number']); // Phone for mobile, bill no for utility
    $amount = floatval($_POST['amount']);
    $pin = $_POST['pin'];
 
    if (empty($provider_id) || empty($account_number) || $amount <= 0 || empty($pin)) {
        $error = 'All fields required.';
    } elseif (strlen($pin) !== 4 || !ctype_digit($pin)) {
        $error = 'Invalid PIN.';
    } else {
        // Verify PIN
        $sql_pin = "SELECT pin FROM users WHERE id = ?";
        $result_pin = executePrepared($conn, $sql_pin, 'i', [$user_id]);
        if ($result_pin && password_verify($pin, $result_pin->fetch_assoc()['pin'])) {
            // Check balance
            $sql_balance = "SELECT balance FROM wallets WHERE user_id = ?";
            $result_balance = executePrepared($conn, $sql_balance, 'i', [$user_id]);
            $balance = $result_balance->fetch_assoc()['balance'];
 
            if ($amount > $balance) {
                $error = 'Insufficient balance.';
            } else {
                // Update balance
                $new_balance = $balance - $amount;
                $sql_update = "UPDATE wallets SET balance = ? WHERE user_id = ?";
                executePrepared($conn, $sql_update, 'di', [$new_balance, $user_id]);
 
                // Log transaction
                $sql_provider = "SELECT name FROM service_providers WHERE id = ?";
                $result_provider = executePrepared($conn, $sql_provider, 'i', [$provider_id]);
                $provider_name = $result_provider->fetch_assoc()['name'];
                $ref = 'BILL_' . time();
                $sql_txn = "INSERT INTO transactions (user_id, type, amount, counterparty, reference, status) VALUES (?, 'bill_payment', ?, ?, ?, 'completed')";
                executePrepared($conn, $sql_txn, 'isdss', [$user_id, $amount, $provider_name, $ref]);
 
                $success = 'Payment successful! PKR ' . number_format($amount, 2) . ' paid to ' . $provider_name;
            }
        } else {
            $error = 'Invalid PIN.';
        }
    }
}
closeConn($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bill Payment - Easypaisa</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Roboto', sans-serif; background: #f8f9fa; }
        .header { background: #ffc107; color: #000; padding: 15px 20px; text-align: center; }
        .container { max-width: 500px; margin: 20px auto; padding: 20px; background: white; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .provider-select { margin-bottom: 20px; }
        select { width: 100%; padding: 12px; border: 2px solid #e1e5e9; border-radius: 10px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; color: #555; margin-bottom: 5px; }
        input { width: 100%; padding: 12px; border: 2px solid #e1e5e9; border-radius: 10px; }
        button { width: 100%; padding: 12px; background: #ffc107; color: #000; border: none; border-radius: 10px; cursor: pointer; font-weight: 500; }
        .error, .success { padding: 10px; border-radius: 5px; margin-bottom: 20px; }
        .error { color: #dc3545; background: #f8d7da; }
        .success { color: #155724; background: #d4edda; }
        .back a { color: #ffc107; text-decoration: none; }
    </style>
</head>
<body>
    <div class="header">
        <h2><?php echo ucfirst($type); ?> Payment</h2>
    </div>
    <div class="container">
        <div class="provider-select">
            <label for="provider_id">Select Provider</label>
            <select id="provider_id" name="provider_id" required>
                <option value="">Choose...</option>
                <?php foreach ($providers as $prov): ?>
                <option value="<?php echo $prov['id']; ?>"><?php echo htmlspecialchars($prov['name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php if ($error): ?><div class="error"><?php echo $error; ?></div><?php endif; ?>
        <?php if ($success): ?><div class="success"><?php echo $success; ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="account_number">Account/Phone Number</label>
                <input type="text" id="account_number" name="account_number" required>
            </div>
            <div class="form-group">
                <label for="amount">Amount (PKR)</label>
                <input type="number" id="amount" name="amount" required min="1" step="0.01">
            </div>
            <div class="form-group">
                <label for="pin">Enter PIN</label>
                <input type="password" id="pin" name="pin" required maxlength="4" pattern="[0-9]{4}">
            </div>
            <button type="submit">Pay Now</button>
        </form>
        <div style="text-align: center; margin-top: 20px;">
            <a href="dashboard.php">← Back to Dashboard</a>
        </div>
    </div>
    <script>
        <?php if ($success): ?>
        setTimeout(() => { window.location.href = 'dashboard.php'; }, 3000);
        <?php endif; ?>
    </script>
</body>
</html>
