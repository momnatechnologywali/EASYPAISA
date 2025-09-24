<?php
include 'db.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit;
}
 
$user_id = $_SESSION['user_id'];
 
// Fetch user and balance
$sql_user = "SELECT u.full_name, u.phone, w.balance FROM users u JOIN wallets w ON u.id = w.user_id WHERE u.id = ?";
$result_user = executePrepared($conn, $sql_user, 'i', [$user_id]);
$user = $result_user ? $result_user->fetch_assoc() : null;
 
if (!$user) {
    session_destroy();
    echo "<script>window.location.href='login.php';</script>";
    exit;
}
 
// Handle deposit/withdraw (simple admin-like, in real integrate payment gateway)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $amount = floatval($_POST['amount']);
    $type = $_POST['action'];
    if ($amount > 0) {
        $current_balance = $user['balance'];
        $new_balance = $type === 'deposit' ? $current_balance + $amount : ($current_balance - $amount);
        if ($new_balance >= 0) {
            // Update wallet
            $sql_update = "UPDATE wallets SET balance = ? WHERE user_id = ?";
            executePrepared($conn, $sql_update, 'di', [$new_balance, $user_id]);
            // Log transaction
            $txn_type = $type === 'deposit' ? 'deposit' : 'withdraw';
            $sql_txn = "INSERT INTO transactions (user_id, type, amount, status) VALUES (?, ?, ?, 'completed')";
            executePrepared($conn, $sql_txn, 'isd', [$user_id, $txn_type, $amount]);
            // Refresh user data
            $result_user = executePrepared($conn, $sql_user, 'i', [$user_id]);
            $user = $result_user->fetch_assoc();
        }
    }
    echo "<script>window.location.href='dashboard.php';</script>"; // Reload to show updated balance
}
closeConn($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Easypaisa</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Roboto', sans-serif; background: #f8f9fa; color: #333; }
        .header { background: linear-gradient(135deg, #007bff, #0056b3); color: white; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .user-info { display: flex; align-items: center; gap: 10px; }
        .balance { font-size: 24px; font-weight: 700; margin: 20px auto; text-align: center; color: #28a745; }
        .options { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; padding: 40px 20px; max-width: 1200px; margin: 0 auto; }
        .option-card { background: white; padding: 20px; border-radius: 15px; text-align: center; box-shadow: 0 5px 15px rgba(0,0,0,0.08); transition: all 0.3s ease; cursor: pointer; }
        .option-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.15); }
        .option-card a { text-decoration: none; color: inherit; }
        .option-icon { font-size: 40px; margin-bottom: 10px; }
        .wallet-actions { background: white; padding: 20px; border-radius: 15px; margin: 20px; box-shadow: 0 5px 15px rgba(0,0,0,0.08); max-width: 400px; margin: 20px auto; }
        .wallet-actions form { display: flex; gap: 10px; margin-bottom: 10px; }
        .wallet-actions input { flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        .wallet-actions button { padding: 10px 15px; background: #28a745; color: white; border: none; border-radius: 5px; cursor: pointer; }
        .logout { background: #dc3545; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none; transition: background 0.3s; }
        .logout:hover { background: #c82333; }
        @media (max-width: 768px) { .options { grid-template-columns: 1fr; } .header { flex-direction: column; gap: 10px; } }
    </style>
</head>
<body>
    <div class="header">
        <h2>Welcome, <?php echo htmlspecialchars($user['full_name']); ?>!</h2>
        <div class="user-info">
            <span><?php echo htmlspecialchars($user['phone']); ?></span>
            <a href="logout.php" class="logout">Logout</a>
        </div>
    </div>
    <div class="balance">Wallet Balance: PKR <?php echo number_format($user['balance'], 2); ?></div>
    <div class="wallet-actions">
        <h3>Quick Actions: Deposit / Withdraw</h3>
        <form method="POST">
            <input type="number" name="amount" placeholder="Amount" min="1" step="0.01" required>
            <button type="submit" name="action" value="deposit">Deposit</button>
            <button type="submit" name="action" value="withdraw">Withdraw</button>
        </form>
    </div>
    <section class="options">
        <div class="option-card">
            <div class="option-icon">💸</div>
            <h3>Money Transfer</h3>
            <p>Send money instantly</p>
            <a href="transfer.php"><button style="background: #007bff; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">Transfer Now</button></a>
        </div>
        <div class="option-card">
            <div class="option-icon">📱</div>
            <h3>Mobile Recharge</h3>
            <p>Recharge your mobile</p>
            <a href="bill_payment.php?type=mobile"><button style="background: #28a745; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">Recharge</button></a>
        </div>
        <div class="option-card">
            <div class="option-icon">💡</div>
            <h3>Pay Bills</h3>
            <p>Utilities & Subscriptions</p>
            <a href="bill_payment.php?type=utility"><button style="background: #ffc107; color: #000; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">Pay Bill</button></a>
        </div>
        <div class="option-card">
            <div class="option-icon">📊</div>
            <h3>Transaction History</h3>
            <p>View your transactions</p>
            <a href="history.php"><button style="background: #6f42c1; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">View History</button></a>
        </div>
        <div class="option-card">
            <div class="option-icon">⚙️</div>
            <h3>Account Settings</h3>
            <p>Manage your account</p>
            <a href="settings.php"><button style="background: #fd7e14; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer;">Settings</button></a>
        </div>
    </section>
    <script>
        // Hover effects for cards
        document.querySelectorAll('.option-card').forEach(card => {
            card.addEventListener('mouseenter', () => card.style.transform = 'translateY(-5px)');
            card.addEventListener('mouseleave', () => card.style.transform = 'translateY(0)');
        });
    </script>
</body>
</html>
