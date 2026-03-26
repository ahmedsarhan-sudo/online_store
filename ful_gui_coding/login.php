<?php
include "db_connect.php"; // اتصال بالداتابيز
session_start();

$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = $_POST["username"];
    $password = $_POST["password"];

    // استدعاء بيانات المستخدم من جدول users
    $sql = "SELECT * FROM users WHERE User_Name='$username' LIMIT 1";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();

        // التحقق من الباسورد
        if (password_verify($password, $user['password'])) {

            $user_id = $user['User_ID'];

            // تحديد نوع المستخدم
            $type = "";
            // Check if in Customers
            $check_customer = $conn->query("SELECT * FROM customers WHERE Customer_ID=$user_id");
            if ($check_customer->num_rows > 0) {
                $type = "customer";
            }
            // Check if in Agents
            $check_agent = $conn->query("SELECT * FROM agents WHERE Agent_ID=$user_id");
            if ($check_agent->num_rows > 0) {
                $type = "agent";
            }
            // Check if in Administrators
            $check_admin = $conn->query("SELECT * FROM administrators WHERE Admin_ID=$user_id");
            if ($check_admin->num_rows > 0) {
                $type = "admin";
            }

            $_SESSION['user_id'] = $user_id;
            $_SESSION['user_type'] = $type;
            $_SESSION['username'] = $username;

            // توجيه حسب النوع
            if ($type == "customer") {
                header("Location: customer_home.php");
                exit();
            } elseif ($type == "agent") {
                header("Location: agent_home.php");
                exit();
            } elseif ($type == "admin") {
                header("Location: admin_home.php");
                exit();
            } else {
                $error_message = "User type not found!";
            }

        } else {
            $error_message = "Wrong password!";
        }

    } else {
        $error_message = "Username not found!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marketplace Login</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* CSS Reset & Base Styles */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            background-color: #020617; /* Deep Slate */
            color: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        /* 3D Supermarket/Market Background */
        

        .bg-image {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: url('https://images.unsplash.com/photo-1578916171728-46686eac8d58?q=80&w=2070&auto=format&fit=crop') no-repeat center center/cover;
            opacity: 0.5; /* شفافية مناسبة */
            z-index: -2;
            transform: scale(1.1);
        }

        /* Dark Gradient Overlay for readability */
        .bg-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: radial-gradient(circle at center, rgba(2, 6, 23, 0.5) 0%, rgba(2, 6, 23, 0.9) 100%);
            z-index: -1;
        }

        /* 3D Floating Elements */
        .floating-element {
            position: absolute;
            z-index: 0;
            filter: drop-shadow(0 20px 15px rgba(0,0,0,0.6));
            opacity: 0.8;
            will-change: transform;
        }

        .float-1 {
            top: 15%;
            left: 10%;
            font-size: 80px;
            animation: floatSlow 6s ease-in-out infinite;
        }

        .float-2 {
            bottom: 15%;
            right: 12%;
            font-size: 100px;
            animation: floatFast 4s ease-in-out infinite reverse;
        }

        @keyframes floatSlow {
            0%, 100% { transform: translateY(0) rotate(0deg) scale(1); }
            50% { transform: translateY(-30px) rotate(10deg) scale(1.05); }
        }

        @keyframes floatFast {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(-15deg); }
        }

        /* Glassmorphism Card */
        .glass-container {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 420px;
            background: rgba(15, 23, 42, 0.4); /* Slate 900 semi-transparent */
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 25px 50px rgba(0,0,0,0.5), inset 0 0 0 1px rgba(255,255,255,0.05);
            animation: fadeIn 1s ease-out forwards;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h2 {
            font-size: 28px;
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 8px;
        }

        .header p {
            font-size: 14px;
            color: #94a3b8;
        }

        /* Error Message */
        .error-msg {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
            padding: 12px;
            border-radius: 12px;
            text-align: center;
            font-size: 14px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        /* Form Elements */
        .input-group {
            margin-bottom: 20px;
            text-align: left;
        }

        .input-group label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #cbd5e1;
            margin-bottom: 8px;
        }

        .input-group input {
            width: 100%;
            padding: 14px 16px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: #ffffff;
            font-size: 15px;
            outline: none;
            transition: all 0.3s ease;
        }

        .input-group input:focus {
            background: rgba(255, 255, 255, 0.1);
            border-color: #10b981; /* Emerald 500 */
            box-shadow: 0 0 15px rgba(16, 185, 129, 0.2);
        }

        .submit-btn {
            width: 100%;
            padding: 16px;
            background: #10b981; /* Emerald 500 */
            color: #020617;
            border: none;
            border-radius: 12px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.3);
        }

        .submit-btn:hover {
            background: #34d399; /* Emerald 400 */
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(16, 185, 129, 0.4);
        }

        .footer-links {
            margin-top: 25px;
            text-align: center;
            font-size: 14px;
            color: #94a3b8;
        }

        .footer-links p {
            margin-bottom: 10px;
        }

        .footer-links a {
            color: #34d399; /* Emerald 400 */
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .footer-links a:hover {
            color: #6ee7b7;
        }

        /* Autofill override for dark theme */
        input:-webkit-autofill {
            -webkit-box-shadow: 0 0 0 30px #0f172a inset !important;
            -webkit-text-fill-color: white !important;
        }
    </style>
</head>
<body>

    <div class="bg-image"></div>
    <div class="bg-overlay"></div>

    <div class="floating-element float-1">🛒</div>
    <div class="floating-element float-2">🛍️</div>

    <div class="glass-container">
        
        <div class="header">
            <h2>Market Login</h2>
            <p>Access your premium marketplace dashboard</p>
        </div>

        <?php if (!empty($error_message)): ?>
            <div class="error-msg">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="" autocomplete="off" onsubmit="return validateForm()">
            <div class="input-group">
                <label for="username">Username</label>
                <input type="text" name="username" id="username" placeholder="Enter your username" required>
            </div>

            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" placeholder="••••••••" required>
            </div>

            <button type="submit" class="submit-btn">Secure Login</button>
        </form>

        <div class="footer-links">
            <p>Don't have an account? <a href="register.php">Register here</a></p>
            <p><a href="index.php" style="color: #94a3b8;">← Back to Welcome Page</a></p>
        </div>
    </div>

    <script>
        // Form validation before sending to PHP
        function validateForm() {
            let username = document.getElementById('username').value.trim();
            let password = document.getElementById('password').value;

            if (username.length < 1) {
                alert('Please enter a username.');
                return false;
            }
            if (password.length < 1) {
                alert('Please enter a password.');
                return false;
            }
            return true;
        }
    </script>
</body>
</html>