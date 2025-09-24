<?php
include 'db.php';
session_start();
if (isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='dashboard.php';</script>";
    exit;
}
 
$error = $success = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $password = $_POST['password'];
    $pin = $_POST['pin'];
 
    if (empty($full_name) || empty($email) || empty($phone) || empty($password) || empty($pin)) {
        $error = 'All fields are required.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif (strlen($pin) !== 4 || !ctype_digit($pin)) {
        $error = 'PIN must be exactly 4 digits.';
    } else {
        // Check if email or phone exists
        $sql = "SELECT id FROM users WHERE email = ? OR phone = ?";
        $result = executePrepared($conn, $sql, 'ss', [$email, $phone]);
        if ($result && $result->num_rows > 0) {
            $error = 'Email or phone already registered.';
        } else {
            // Hash password and PIN
            $hashed_pass = password_hash($password, PASSWORD_DEFAULT);
            $hashed_pin = password_hash($pin, PASSWORD_DEFAULT);
 
            // Insert user
            $sql = "INSERT INTO users (full_name, email, phone, password, pin) VALUES (?, ?, ?, ?, ?)";
            $result = executePrepared($conn, $sql, 'sssss', [$full_name, $email, $phone, $hashed_pass, $hashed_pin]);
            if ($result) {
                $user_id = $conn->insert_id;
                // Create wallet
                $sql_wallet = "INSERT INTO wallets (user_id, balance) VALUES (?, 0.00)";
                executePrepared($conn, $sql_wallet, 'i', [$user_id]);
                $success = 'Account created successfully! Please login.';
                $_SESSION['temp_user_email'] = $email; // For auto-fill in login
            } else {
                $error = 'Signup failed. Try again.';
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
    <title>Signup - Easypaisa</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Roboto', sans-serif; background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .form-container { background: white; padding: 40px; border-radius: 20px; box-shadow: 0 20px 60px rgba(0,0,0,0.1); width: 100%; max-width: 400px; animation: slideIn 0.5s ease; }
        @keyframes slideIn { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
        h2 { text-align: center; color: #007bff; margin-bottom: 30px; font-weight: 500; }
        .form-group { margin-bottom: 20px; }
        label { display: block; color: #555; margin-bottom: 5px; font-weight: 400; }
        input { width: 100%; padding: 12px; border: 2px solid #e1e5e9; border-radius: 10px; font-size: 16px; transition: all 0.3s ease; }
        input:focus { outline: none; border-color: #007bff; box-shadow: 0 0 0 3px rgba(0,123,255,0.1); }
        button { width: 100%; padding: 12px; background: linear-gradient(135deg, #007bff, #0056b3); color: white; border: none; border-radius: 10px; font-size: 16px; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(0,123,255,0.2); }
        button:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,123,255,0.3); }
        .error { color: #dc3545; text-align: center; padding: 10px; background: #f8d7da; border-radius: 5px; margin-bottom: 20px; }
        .success { color: #155724; text-align: center; padding: 10px; background: #d4edda; border-radius: 5px; margin-bottom: 20px; }
        .back-link { text-align: center; margin-top: 20px; }
        .back-link a { color: #007bff; text-decoration: none; }
        @media (max-width: 480px) { .form-container { margin: 20px; padding: 30px 20px; } }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Create Your Easypaisa Account</h2>
        <?php if ($error): ?><div class="error"><?php echo $error; ?></div><?php endif; ?>
        <?php if ($success): ?><div class="success"><?php echo $success; ?></div><?php endif; ?>
        <form method="POST" id="signupForm">
            <div class="form-group">
                <label for="full_name">Full Name</label>
                <input type="text" id="full_name" name="full_name" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="phone">Phone Number</label>
                <input type="tel" id="phone" name="phone" required pattern="[0-9]{10,12}">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required minlength="6">
            </div>
            <div class="form-group">
                <label for="pin">4-Digit PIN (for 2FA & Transactions)</label>
                <input type="password" id="pin" name="pin" required maxlength="4" pattern="[0-9]{4}">
            </div>
            <button type="submit">Sign Up</button>
        </form>
        <div class="back-link">
            <a href="index.php">← Back to Home</a> | <a href="login.php">Already have account? Login</a>
        </div>
    </div>
    <script>
        // Form validation and JS redirect (though PHP handles, for UX)
        document.getElementById('signupForm').addEventListener('submit', function(e) {
            const pin = document.getElementById('pin').value;
            if (pin.length !== 4 || !/^\d{4}$/.test(pin)) {
                e.preventDefault();
                alert('PIN must be exactly 4 digits.');
            }
        });
        <?php if ($success): ?>
        setTimeout(() => { window.location.href = 'login.php'; }, 2000);
        <?php endif; ?>
    </script>
</body>
</html>
