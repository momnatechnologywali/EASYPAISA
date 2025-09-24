<?php
include 'db.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit;
}
 
$user_id = $_SESSION['user_id'];
 
// Fetch recent transactions (last 20)
$sql_txns = "SELECT * FROM transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT 20";
$result_txns = executePrepared($conn, $sql_txns, 'i', [$user_id]);
$transactions = [];
while ($row = $result_txns->fetch_assoc()) {
    $transactions[] = $row;
}
 
// Fetch balance
$sql_balance = "SELECT balance FROM wallets WHERE user_id = ?";
$result_balance = executePrepared($conn, $sql_balance, 'i', [$user_id]);
$balance = $result_balance->fetch_assoc()['balance'] ?? 0;
closeConn($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction History - Easypaisa</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Roboto', sans-serif; background: #f8f9fa; }
        .header { background: #6f42c1; color: white; padding: 15px 20px; display: flex; justify-content: space-between; }
        .balance { background: #28a745; color: white; padding: 10px; text-align: center; font-weight: 500; }
        .container { max-width: 800px; margin: 20px auto; padding: 20px; background: white; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #f8f9fa; font-weight: 500; }
        .type-send { color: #dc3545; }
        .type-receive { color: #28a745; }
        .type-deposit { color: #28a745; }
        .type-withdraw { color: #dc3545; }
        .type-bill { color: #ffc107; }
        .back { text-align: center; margin-top: 20px; }
        .back a { color: #6f42c1; text-decoration: none; padding: 10px 20px; background: #f8f9fa; border-radius: 5px; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Transaction History</h2>
        <a href="logout.php" style="color: white; text-decoration: none;">Logout</a>
    </div>
    <div class="balance">Current Balance: PKR <?php echo number_format($balance, 2); ?></div>
    <div class="container">
        <?php if (empty($transactions)): ?>
            <p style="text-align: center; color: #666;">No transactions yet. Start by making a payment!</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Amount</th>
                        <th>Counterparty/Reference</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($transactions as $txn): ?>
                    <tr>
                        <td><?php echo date('Y-m-d H:i', strtotime($txn['created_at'])); ?></td>
                        <td class="type-<?php echo $txn['type']; ?>"><?php echo ucfirst(str_replace('_', ' ', $txn['type'])); ?></td>
                        <td><?php echo $txn['type'] === 'transfer_send' || $txn['type'] === 'withdraw' || $txn['type'] === 'bill_payment' ? '-' : '+'; ?> PKR <?php echo number_format($txn['amount'], 2); ?></td>
                        <td><?php echo htmlspecialchars($txn['counterparty'] ?? $txn['reference'] ?? 'N/A'); ?></td>
                        <td><?php echo ucfirst($txn['status']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        <div class="back">
            <a href="dashboard.php">← Back to Dashboard</a>
        </div>
    </div>
</body>
</html>
