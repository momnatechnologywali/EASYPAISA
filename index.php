<?php
include 'db.php';
session_start();
if (isset($_SESSION['user_id'])) {
    echo "<script>window.location.href='dashboard.php';</script>";
    exit;
}
closeConn($conn);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Easypaisa - Digital Payments Made Easy</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Roboto', sans-serif; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; color: #333; }
        .container { max-width: 1200px; margin: 0 auto; padding: 20px; }
        header { background: rgba(255,255,255,0.1); backdrop-filter: blur(10px); padding: 15px 0; border-bottom: 1px solid rgba(255,255,255,0.2); }
        nav { display: flex; justify-content: space-between; align-items: center; }
        .logo { font-size: 28px; font-weight: 700; color: white; text-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .cta { background: #28a745; color: white; padding: 10px 20px; border-radius: 25px; text-decoration: none; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(40,167,69,0.3); }
        .cta:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(40,167,69,0.4); }
        .hero { text-align: center; padding: 80px 0; color: white; }
        .hero h1 { font-size: 48px; margin-bottom: 20px; text-shadow: 0 4px 8px rgba(0,0,0,0.2); animation: fadeInUp 1s ease; }
        .hero p { font-size: 20px; margin-bottom: 40px; opacity: 0.9; animation: fadeInUp 1s ease 0.2s both; }
        .features { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px; padding: 60px 0; }
        .feature-card { background: rgba(255,255,255,0.95); padding: 30px; border-radius: 15px; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.1); transition: all 0.3s ease; backdrop-filter: blur(5px); }
        .feature-card:hover { transform: translateY(-5px); box-shadow: 0 15px 40px rgba(0,0,0,0.15); }
        .feature-card h3 { color: #007bff; font-size: 24px; margin-bottom: 15px; }
        .feature-card p { color: #666; line-height: 1.6; }
        .icon { font-size: 50px; margin-bottom: 20px; color: #007bff; }
        footer { background: rgba(0,0,0,0.1); color: white; text-align: center; padding: 20px; }
        @keyframes fadeInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }
        @media (max-width: 768px) { .hero h1 { font-size: 32px; } .features { grid-template-columns: 1fr; } }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <nav>
                <div class="logo">Easypaisa</div>
                <a href="login.php" class="cta">Login / Signup</a>
            </nav>
        </header>
        <section class="hero">
            <h1>Digital Payments, Simplified</h1>
            <p>Send money, pay bills, recharge mobile – all securely from your fingertips.</p>
            <a href="signup.php" class="cta" style="font-size: 18px; padding: 15px 30px;">Get Started Today</a>
        </section>
        <section class="features">
            <div class="feature-card">
                <div class="icon">💳</div>
                <h3>Money Transfer</h3>
                <p>Instant transfers to friends, family, or bank accounts using just a phone number.</p>
            </div>
            <div class="feature-card">
                <div class="icon">📱</div>
                <h3>Mobile Recharge</h3>
                <p>Top up your mobile balance in seconds with our easy recharge system.</p>
            </div>
            <div class="feature-card">
                <div class="icon">💡</div>
                <h3>Bill Payments</h3>
                <p>Pay utilities, subscriptions, and more without the hassle – all in one place.</p>
            </div>
        </section>
        <footer>
            <p>&copy; 2025 Easypaisa Clone. Secure & Reliable Digital Banking.</p>
        </footer>
    </div>
    <script>
        // Simple animation trigger on scroll (for engagement)
        window.addEventListener('scroll', () => {
            document.querySelectorAll('.feature-card').forEach((card, i) => {
                if (card.getBoundingClientRect().top < window.innerHeight) {
                    card.style.animation = `fadeInUp 0.6s ease ${i * 0.1}s both`;
                }
            });
        });
    </script>
</body>
</html>
