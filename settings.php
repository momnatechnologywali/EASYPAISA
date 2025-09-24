<?php
include 'db.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit;
}
 
$user_id = $_SESSION['user_id'];
 
// Fetch user details
$sql_user = "SELECT full_name, email, phone FROM users WHERE id = ?";
$result_user = executePrepared($conn, $sql_user, 'i', [$user_id]);
$user = $result_user->fetch_assoc();
 
$error = $success = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['reset_pin'])) {
        $new_pin = $_POST['new_pin'];
        $confirm_pin = $_POST['confirm_pin'];
        if (strlen($new_pin) === 4 && $new_pin === $confirm_pin && ctype_digit($new_pin)) {
            $hashed_pin = password_hash($new_pin, PASSWORD_DEFAULT);
            $sql_update = "UPDATE users SET pin = ? WHERE id = ?";
            if (executePrepared($conn, $sql_update, 'si', [$hashed_pin, $user_id])) {
                $success = 'PIN reset successfully.';
            } else {
                $error = 'Failed to reset PIN.';
            }
        } else {
            $error = 'PIN must be 4 digits and match confirmation.';
        }
    }
    // For other settings like name update, similar logic
    if (isset($_POST['update_name'])) {
        $new_name = trim($_POST['full_name']);
        if (!empty($new_name)) {
            $sql_update = "UPDATE users SET full_name = ? WHERE id = ?";
            if (executePrepared($conn, $sql_update, 'si', [$new_name, $user_id])) {
                $user['full_name'] = $new_name;
                $success = 'Name updated.';
            } else {
                $error = 'Failed to update name.';
            }
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
    <title>Settings - Easypaisa</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Roboto', sans-serif; background: #f8f9fa; }
        .header { background: #fd7e14; color: white; padding: 15px 20px; text-align: center; }
        .container { max-width: 500px; margin: 20px auto; padding: 20px; background: white; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .section { margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid #eee; }
        .section h3 { color: #fd7e14; margin-bottom: 15px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; color: #555; margin-bottom: 5px; }
        input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; }
        button { padding: 10px 20px; background: #fd7e14; color: white; border: none; border-radius: 5px; cursor: pointer; margin-right: 10px; }
        .error, .success { padding: 10px; border-radius: 5px; margin-bottom: 20px; }
        .error { color: #dc3545; background: #f8d7da; }
        .success { color: #155724; background: #d4edda; }
        .back a { color: #fd7e14; text-decoration: none; }
    </style>
</head>
<body>
    <div class="header">
        <h2>Account Settings</h2>
    </div>
    <div class="container">
        <?php if ($error): ?><div class="error"><?php echo $error; ?></div><?php endif; ?>
        <?php if ($success): ?><div class="success"><?php echo $success; ?></div><?php endif; ?>
 
        <div class="section">
            <h3>Update Name</h3>
            <form method="POST">
                <div class="form-group">
                    <label for="full_name">Full Name</label>
                    <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                </div>
                <button type="submit" name="update_name">Update Name</button>
            </form>
        </div>
 
        <div class="section">
            <h3>Reset PIN</h3>
            <form method="POST">
                <div class="form-group">
                    <label for="new_pin">New 4-Digit PIN</label>
                    <input type="password" id="new_pin" name="new_pin" maxlength="4" pattern="[0-9]{4}" required>
                </div>
                <div class="form-group">
                    <label for="confirm_pin">Confirm PIN</label>
                    <input type="password" id="confirm_pin" name="confirm_pin" maxlength="4" pattern="[0-9]{4}" required>
                </div>
                <button type="submit" name="reset_pin">Reset PIN</button>
            </form>
        </div>
 
        <div style="text-align: center; margin-top: 30px;">
            <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
            <p><strong>Phone:</strong> <?php echo htmlspecialchars($user['phone']); ?></p>
        </div>
 
        <div class="back" style="text-align: center; margin-top: 20px;">
            <a href="dashboard.php">← Back to Dashboard</a>
        </div>
    </div>
    <script>
        // Confirm PIN reset
        document.querySelector('button[name="reset_pin"]').addEventListener('click', function(e) {
            const newPin = document.getElementById('new_pin').value;
            const confirmPin = document.getElementById('confirm_pin').value;
            if (newPin !== confirmPin) {
                alert('PINs do not match!');
                e.preventDefault();
            }
        });
    </script>
</body>
</html>
