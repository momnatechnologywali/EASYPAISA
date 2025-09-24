<?php
include 'db.php';
session_start();
if (!isset($_SESSION['temp_user_id'])) {
    echo "<script>window.location.href='login.php';</script>";
    exit;
}
 
$user_id = $_SESSION['temp_user_id'];
$error = '';
 
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pin = $_POST['pin'];
 
    if (empty($pin) || strlen($pin) !== 4) {
        $error = 'Enter valid 4-digit PIN.';
    } else {
        $sql = "SELECT pin FROM users WHERE id = ?";
        $result = executePrepared($conn, $sql, 'i', [$user_id]);
        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($pin, $user['pin'])) {
                $_SESSION['user_id'] = $user_id;
                unset($_SESSION['temp_user_id']);
                echo "<script>window.location.href='dashboard.php';</script>";
                exit;
            } else {
                $error = 'Invalid PIN.';
            }
        } else {
            $error = 'Session expired. Please login again.';
            unset($_SESSION['temp_user_id']);
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
    <title>2FA Verification - Easypaisa</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Roboto', sans-serif; background: linear-gradient(135deg, #a8edea 0%, #fed6e3 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .form-container { background: white; padding: 40px; border-radius: 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.1); width: 100%; max-width: 400px; text-align: center; }
        h2 { color: #20bf6b; margin-bottom: 20px; }
        p { color: #666; margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; }
        input { width: 100%; padding: 15px; border: 2px solid #e1e5e9; border-radius: 10px; font-size: 18px; text-align: center; letter-spacing: 5px; }
        input:focus { border-color: #20bf6b; outline: none; }
        button { width: 100%; padding: 12px; background: linear-gradient(135deg, #20bf6b, #0f9d58); color: white; border: none; border-radius: 10px; cursor: pointer; }
        .error { color: #e74c3c; padding: 10px; background: #fadbd8; border-radius: 5px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Enter Your PIN</h2>
        <p>For added security, enter your 4-digit PIN to complete login.</p>
        <?php if ($error): ?><div class="error"><?php echo $error; ?></div><?php endif; ?>
        <form method="POST">
            <div class="form-group">
                <input type="password" name="pin" maxlength="4" pattern="[0-9]{4}" required placeholder="****">
            </div>
            <button type="submit">Verify</button>
        </form>
        <p style="margin-top: 20px;"><a href="login.php" style="color: #20bf6b;">Back to Login</a></p>
    </div>
    <script>
        // Auto-focus and PIN input enhancements
        const pinInput = document.querySelector('input[name="pin"]');
        pinInput.addEventListener('input', function(e) {
            if (e.target.value.length === 4) {
                document.querySelector('form').submit();
            }
        });
    </script>
</body>
</html>
