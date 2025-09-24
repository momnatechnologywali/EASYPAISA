<?php
include 'db.php';
session_start();
if (isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='dashboard.php';</script>";
    exit;
}
 
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];
 
    if (empty($email) || empty($password)) {
        $error = 'Email and password are required.';
    } else {
        $sql = "SELECT id, password FROM users WHERE email = ? AND is_active = 1";
        $result = executePrepared($conn, $sql, 's', [$email]);
        if ($result && $result->num_rows === 1) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['temp_user_id'] = $user['id'];
                echo "<script>window.location.href='2fa.php';</script>";
                exit;
            } else {
                $error = 'Invalid email or password.';
            }
        } else {
            $error = 'Invalid email or password.';
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
    <title>Login - Easypaisa</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Roboto', sans-serif; background: linear-gradient(135deg, #74b9ff 0%, #0984e3 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .form-container { background: white; padding: 40px; border-radius: 20px; box-shadow: 0 15px 35px rgba(0,0,0,0.1); width: 100%; max-width: 400px; }
        h2 { text-align: center; color: #0984e3; margin-bottom: 30px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; color: #555; margin-bottom: 5px; }
        input { width: 100%; padding: 12px; border: 2px solid #e1e5e9; border-radius: 10px; transition: border-color 0.3s; }
        input:focus { border-color: #0984e3; outline: none; }
        button { width: 100%; padding: 12px; background: linear-gradient(135deg, #0984e3, #074ea0); color: white; border: none; border-radius: 10px; cursor: pointer; transition: transform 0.3s; }
        button:hover { transform: translateY(-1px); }
        .error { color: #e74c3c; text-align: center; padding: 10px; background: #fadbd8; border-radius: 5px; margin-bottom: 20px; }
        .back-link { text-align: center; margin-top: 20px; }
        .back-link a { color: #0984e3; text-decoration: none; }
    </style>
</head>
<body>
    <div class="form-container">
        <h2>Welcome Back</h2>
        <?php if ($error): ?><div class="error"><?php echo $error; ?></div><?php endif; ?>
        <form method="POST" id="loginForm">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo isset($_SESSION['temp_user_email']) ? $_SESSION['temp_user_email'] : ''; ?>" required>
                <?php unset($_SESSION['temp_user_email']); ?>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit">Login</button>
        </form>
        <div class="back-link">
            <a href="index.php">← Back to Home</a> | <a href="signup.php">Don't have account? Sign Up</a>
        </div>
    </div>
    <script>
        // Basic form handling
        document.getElementById('loginForm').addEventListener('submit', function() {
            // No additional JS validation needed, PHP handles
        });
    </script>
</body>
</html>
