<?php
include "db_connect.php";
session_start();

$customer_id = $_SESSION['user_id'] ?? 0;
if (!$customer_id) {
    echo "Please login first.";
    exit;
}

$order_id = $_POST['order_id'] ?? 0;
if (!$order_id) {
    header("Location: show_orders.php"); // Redirect if accessed directly without ID
    exit;
}

// Logic variables for the UI
$main_message = "";
$secondary_message = "";

// 1. Process the main order
$stmt = $conn->prepare("SELECT * FROM orders WHERE Order_ID = ? AND Customer_ID = ? AND Status = 'Preparing'");
$stmt->bind_param("ii", $order_id, $customer_id);
$stmt->execute();
$order_res = $stmt->get_result();

if ($order_res->num_rows > 0) {
    $update_stmt = $conn->prepare("UPDATE orders SET Status = 'Prepared' WHERE Order_ID = ?");
    $update_stmt->bind_param("i", $order_id);
    $update_stmt->execute();

    $insert_history = $conn->prepare("INSERT INTO OrderStatusHistory (Old_Status, New_Status, Update_Date, Customer_ID, Agent_ID, Order_ID) VALUES (?, ?, CURDATE(), ?, NULL, ?)");
    $old_s = 'Preparing'; $new_s = 'Prepared';
    $insert_history->bind_param("ssii", $old_s, $new_s, $customer_id, $order_id);
    $insert_history->execute();

    $main_message = "Order #$order_id is now Prepared!";
} else {
    header("Location: show_orders.php");
    exit;
}

// 2. Check for Waiting orders
$waiting_stmt = $conn->prepare("SELECT * FROM orders WHERE Customer_ID = ? AND Status='Waiting'");
$waiting_stmt->bind_param("i", $customer_id);
$waiting_stmt->execute();
$waiting_res = $waiting_stmt->get_result();

if ($waiting_res->num_rows > 0) {
    $waiting_order = $waiting_res->fetch_assoc();
    $waiting_id = $waiting_order['Order_ID'];

    $restore_stmt = $conn->prepare("UPDATE orders SET Status = 'Preparing' WHERE Order_ID = ?");
    $restore_stmt->bind_param("i", $waiting_id);
    $restore_stmt->execute();

    $old_w = 'Waiting'; $new_p = 'Preparing';
    $insert_history->bind_param("ssii", $old_w, $new_p, $customer_id, $waiting_id);
    $insert_history->execute();

    $secondary_message = "Order #$waiting_id has been moved to Preparing.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout Success | Premium Market</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;800&display=swap" rel="stylesheet">
    
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', sans-serif; }

        body {
            background-color: #020617;
            color: #f8fafc;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        /* 3D Marketplace Background */
        .bg-image {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: url('https://images.unsplash.com/photo-1542838132-92c53300491e?q=80&w=2074&auto=format&fit=crop') no-repeat center center;
            background-size: cover;
            filter: brightness(0.5);
            opacity: 0.5;
            transform: scale(1.05);
            z-index: -2;
        }

        .bg-overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: radial-gradient(circle at center, rgba(16, 185, 129, 0.05) 0%, #020617 100%);
            z-index: -1;
        }

        /* Glass Success Card */
        .glass-card {
            width: 100%;
            max-width: 500px;
            background: rgba(30, 41, 59, 0.4);
            backdrop-filter: blur(25px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 40px;
            padding: 60px 40px;
            text-align: center;
            box-shadow: 0 50px 100px -20px rgba(0, 0, 0, 0.6);
            animation: popIn 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        @keyframes popIn {
            from { opacity: 0; transform: scale(0.9); }
            to { opacity: 1; transform: scale(1); }
        }

        /* Success Checkmark Icon */
        .success-icon {
            width: 90px;
            height: 90px;
            background: rgba(16, 185, 129, 0.1);
            border: 2px solid #10b981;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            color: #10b981;
            font-size: 45px;
            box-shadow: 0 0 30px rgba(16, 185, 129, 0.2);
        }

        h1 { font-size: 28px; font-weight: 800; color: #fff; margin-bottom: 12px; }
        .sub-message { color: #94a3b8; font-size: 16px; margin-bottom: 30px; }

        /* Notification for Waiting Orders */
        .promo-box {
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: 16px;
            padding: 15px;
            margin-bottom: 35px;
            display: flex;
            align-items: center;
            gap: 12px;
            text-align: left;
            animation: slideIn 0.8s ease;
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-20px); }
            to { opacity: 1; transform: translateX(0); }
        }

        .promo-icon { font-size: 20px; }
        .promo-text { font-size: 13px; color: #93c5fd; font-weight: 600; line-height: 1.4; }

        /* Button Group */
        .btn-stack { display: flex; flex-direction: column; gap: 15px; }

        .btn {
            padding: 18px;
            border-radius: 16px;
            font-weight: 700;
            font-size: 15px;
            text-decoration: none;
            transition: 0.3s;
            text-align: center;
        }

        .btn-main { background: #10b981; color: #020617; }
        .btn-main:hover { transform: translateY(-3px); box-shadow: 0 15px 30px rgba(16, 185, 129, 0.3); }

        .btn-outline { background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #fff; }
        .btn-outline:hover { background: rgba(255,255,255,0.1); border-color: #ffd700; }
    </style>
</head>
<body>

    <div class="bg-image"></div>
    <div class="bg-overlay"></div>

    <div class="glass-card">
        <div class="success-icon">✓</div>
        <h1>All Set!</h1>
        <p class="sub-message"><?php echo $main_message; ?></p>

        <?php if ($secondary_message): ?>
            <div class="promo-box">
                <span class="promo-icon">🔄</span>
                <span class="promo-text"><?php echo $secondary_message; ?><br><small style="opacity: 0.7; font-weight: 400;">You can now add products to this order.</small></span>
            </div>
        <?php endif; ?>
        
        <div class="btn-stack">
            <a href="show_orders.php" class="btn btn-main">View My Order History</a>
            <a href="customer_home.php" class="btn btn-outline">Return to Marketplace</a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.body.style.opacity = '0';
            setTimeout(() => {
                document.body.style.transition = 'opacity 0.8s';
                document.body.style.opacity = '1';
            }, 100);
        });
    </script>

</body>
</html>