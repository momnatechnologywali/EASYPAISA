<?php
include 'db.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit;
}
 
$user_id = $_SESSION['user_id'];
$error = $success = '';
 
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $recipient_phone = trim($_POST['recipient_phone']);
    $amount = floatval($_POST['amount']);
    $pin = $_POST['pin'];
 
    if (empty($recipient_phone) || $amount <= 0 || empty($pin)) {
        $error = 'All fields are required. Amount must be positive.';
    } elseif (strlen($pin) !== 4 || !ctype_digit($pin)) {
        $error = 'PIN must be 4 digits.';
    } else {
        // Verify PIN
        $sql_pin = "SELECT pin FROM users WHERE id = ?";
        $result_pin = executePrepared($conn, $sql_pin, 'i', [$user_id]);
        if ($result_pin && password_verify($pin, $result_pin->fetch_assoc()['pin'])) {
            // Find sender balance and recipient
            $sql_sender = "SELECT w.balance FROM wallets w WHERE w.user_id = ?";
            $result_sender = executePrepared($conn, $sql_sender, 'i', [$user_id]);
            $sender_balance = $result_sender->fetch_assoc()['balance'];
 
            if ($amount > $sender_balance) {
                $error = 'Insufficient balance.';
            } else {
                $sql_recipient = "SELECT id FROM users WHERE phone = ? AND is_active = 1";
                $result_recipient = executePrepared($conn, $sql_recipient, 's', [$recipient_phone]);
                if ($result_recipient && $result_recipient->num_rows === 1) {
                    $recipient_id = $result_recipient->fetch_assoc()['id'];
 
                    // Begin transaction (simulate with locks)
                    $conn->autocommit(FALSE);
 
                    // Update sender
                    $new_sender_balance = $sender_balance - $amount;
                    $sql_update_sender = "UPDATE wallets SET balance = ? WHERE user_id = ?";
                    $update_sender = executePrepared($conn, $sql_update_sender, 'di', [$new_sender_balance, $user_id]);
 
                    // Update recipient (fetch first)
                    $sql_recipient_balance = "SELECT balance FROM wallets WHERE user_id = ?";
                    $result_rec_bal = executePrepared($conn, $sql_recipient_balance, 'i', [$recipient_id]);
                    $rec_balance = $result_rec_bal->fetch_assoc()['balance'];
                    $new_rec_balance = $rec_balance + $amount;
                    $sql_update_rec = "UPDATE wallets SET balance = ? WHERE user_id = ?";
                    $update_rec = executePrepared($conn, $sql_update_rec, 'di', [$new_rec_balance, $recipient_id]);
 
                    if ($update_sender && $update_rec) {
                        // Log transactions
                        $ref = 'TXN_' . time();
                        $sql_txn_send = "INSERT INTO transactions (user_id, type, amount, counterparty, reference, status) VALUES (?, 'transfer_send', ?, ?, ?, 'completed')";
                        executePrepared($conn, $sql_txn_send, 'isdss', [$user_id, $amount, $recipient_phone, $ref]);
 
                        $sql_txn_receive = "INSERT INTO transactions (user_id, type, amount, counterparty, reference, status) VALUES (?, 'transfer_receive', ?, ?, ?, 'completed')";
                        executePrepared($conn, $sql_txn_receive, 'isdss', [$recipient_id, $amount, $recipient_phone, $ref]); // Counterparty as sender phone
 
                        $conn->commit();
                        $success = 'Transfer successful! PKR ' . number_format($amount, 2) . ' sent to ' . $recipient_phone;
                    } else {
                        $conn->rollback();
                        $error = 'Transfer failed. Try again.';
                    }
                } else {
                    $error = 'Recipient phone not found.';
                }
            }
        } else {
            $error = 'Invalid PIN.';
        }
        $conn->autocommit(TRUE);
    }
}
closeConn($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Money Transfer - Easypaisa</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Roboto', sans-serif; background: #f8f9fa; }
        .header { background: #007bff; color: white; padding: 15px 20px; text-align: center; }
        .container { max-width: 500px; margin: 20px auto; padding: 20px; background: white; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .form-group { margin-bottom: 20px; }
        label { display: block; color: #555; margin-bottom: 5px; }
        input { width: 100%; padding: 12px; border: 2px solid #e1e5e9; border-radius: 10px; transition: border-color 0.3s; }
        input:focus { border-color: #007bff; }
        button { width: 100%; padding: 12px; background: #28a745; color: white; border: none; border-radius: 10px; cursor: pointer; }
        .error { color: #dc3545; background: #f8d7da; padding: 10px; border-radius: 5px; margin-bottom: 20px; }
        .success { color: #155724; background: #d4edda; padding: 10px; border-radius: 5px; margin-bottom: 20px; }
        .back { text-align: center; margin-top: 20px; }
        .back a { color: #007bff; text-decoration: none; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Send Money</h2>
    </div>
    <div class="container">
        <?php if ($error): ?><div class="error"><?php echo $error; ?></div><?php endif; ?>
        <?php if ($success): ?><div class="success"><?php echo $success; ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <label for="recipient_phone">Recipient Phone Number</label>
                <input type="tel" id="recipient_phone" name="recipient_phone" required pattern="[0-9]{10,12}">
            </div>
            <div class="form-group">
                <label for="amount">Amount (PKR)</label>
                <input type="number" id="amount" name="amount" required min="1" step="0.01">
            </div>
            <div class="form-group">
                <label for="pin">Enter PIN for Verification</label>
                <input type="password" id="pin" name="pin" required maxlength="4" pattern="[0-9]{4}">
            </div>
            <button type="submit">Send Money</button>
        </form>
        <div class="back">
            <a href="dashboard.php">← Back to Dashboard</a>
        </div>
    </div>
    <script>
        document.querySelector('form').addEventListener('submit', function() {
            const pin = document.getElementById('pin').value;
            if (pin.length !== 4) {
                alert('Enter 4-digit PIN');
                return false;
            }
        });
        <?php if ($success): ?>
        setTimeout(() => { window.location.href = 'dashboard.php'; }, 3000);
        <?php endif; ?>
    </script>
</body>
</html>
