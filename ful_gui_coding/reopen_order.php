<?php
include "db_connect.php";
session_start();

$customer_id = $_SESSION['user_id'] ?? 0;
$order_id = $_POST['order_id'] ?? 0;

if (!$customer_id || !$order_id) {
    header("Location: show_orders.php");
    exit;
}

// Check if order is Prepared
$order_res = $conn->query("SELECT * FROM orders WHERE Order_ID = $order_id AND Customer_ID = $customer_id AND Status='Prepared'");
if ($order_res->num_rows == 0) {
    header("Location: show_orders.php");
    exit;
}

$secondary_msg = "";

// Check for current Preparing order
$preparing_res = $conn->query("SELECT * FROM orders WHERE Customer_ID = $customer_id AND Status='Preparing'");
if ($preparing_res->num_rows > 0) {
    $current_preparing = $preparing_res->fetch_assoc();
    $current_preparing_id = $current_preparing['Order_ID'];

    $conn->query("UPDATE orders SET Status='Waiting' WHERE Order_ID = $current_preparing_id");

    $stmt_history = $conn->prepare("INSERT INTO OrderStatusHistory (Old_Status, New_Status, Update_Date, Customer_ID, Agent_ID, Order_ID) VALUES (?, ?, CURDATE(), ?, NULL, ?)");
    $old_s = 'Preparing'; $new_s = 'Waiting';
    $stmt_history->bind_param("ssii", $old_s, $new_s, $customer_id, $current_preparing_id);
    $stmt_history->execute();

    $secondary_msg = "Order #$current_preparing_id was moved to 'Waiting' to prioritize this one.";
}

// Reopen the requested order
$conn->query("UPDATE orders SET Status='Preparing' WHERE Order_ID = $order_id");

$stmt_history = $conn->prepare("INSERT INTO OrderStatusHistory (Old_Status, New_Status, Update_Date, Customer_ID, Agent_ID, Order_ID) VALUES (?, ?, CURDATE(), ?, NULL, ?)");
$old_r = 'Prepared'; $new_r = 'Preparing';
$stmt_history->bind_param("ssii", $old_r, $new_r, $customer_id, $order_id);
$stmt_history->execute();

$main_msg = "Order #$order_id is now back in Preparation.";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Reopened | Premium Marketplace</title>
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

        /* 3D Moving Background Effect */
        .bg-image {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: url('https://images.unsplash.com/photo-1581414211938-e772a180c7ab?q=80&w=1332&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D') no-repeat center center;
            background-size: cover;
            filter:  brightness(0.4);
            opacity: 0.5;
            z-index: -2;
            transform: scale(1.05);
        }

        .bg-overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: radial-gradient(circle at center, rgba(56, 189, 248, 0.05) 0%, #020617 100%);
            z-index: -1;
        }

        /* Glass Card */
        .glass-card {
            width: 90%;
            max-width: 480px;
            background: rgba(30, 41, 59, 0.4);
            backdrop-filter: blur(25px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 40px;
            padding: 50px 40px;
            text-align: center;
            box-shadow: 0 40px 80px -15px rgba(0, 0, 0, 0.6);
            animation: slideUp 0.7s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(40px) scale(0.95); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* Reopen Icon (Rotating Cycle) */
        .reopen-icon {
            width: 80px;
            height: 80px;
            background: rgba(14, 165, 233, 0.1);
            border: 2px solid #0ea5e9;
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 25px;
            color: #0ea5e9;
            font-size: 35px;
            animation: rotate 20s linear infinite;
        }

        @keyframes rotate { from { transform: rotate(0deg); } to { transform: rotate(360deg); } }

        h1 { font-size: 26px; font-weight: 800; color: #fff; margin-bottom: 12px; }
        .sub-message { color: #94a3b8; font-size: 15px; margin-bottom: 30px; line-height: 1.6; }

        /* Status Update Banner */
        .status-alert {
            background: rgba(255, 255, 255, 0.03);
            border: 1px dashed rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 15px;
            margin-bottom: 35px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .alert-dot { width: 8px; height: 8px; background: #fbbf24; border-radius: 50%; box-shadow: 0 0 10px #fbbf24; }
        .alert-text { font-size: 13px; color: #cbd5e1; text-align: left; }

        /* Button Styling */
        .btn-primary {
            display: block;
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, #0ea5e9 0%, #2563eb 100%);
            color: white;
            text-decoration: none;
            border-radius: 16px;
            font-weight: 700;
            font-size: 15px;
            transition: 0.3s ease;
            box-shadow: 0 10px 25px rgba(37, 99, 235, 0.2);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(37, 99, 235, 0.4);
            filter: brightness(1.1);
        }

        .btn-link {
            display: inline-block;
            margin-top: 20px;
            color: #94a3b8;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: 0.2s;
        }

        .btn-link:hover { color: #fff; }
    </style>
</head>
<body>

    <div class="bg-image"></div>
    <div class="bg-overlay"></div>

    <div class="glass-card">
        <div class="reopen-icon">🔄</div>
        <h1>Order Reopened</h1>
        <p class="sub-message"><?php echo $main_msg; ?></p>

        <?php if ($secondary_msg): ?>
            <div class="status-alert">
                <div class="alert-dot"></div>
                <div class="alert-text"><?php echo $secondary_msg; ?></div>
            </div>
        <?php endif; ?>
        
        <a href="customer_order.php" class="btn-primary">Add More Products</a>
        <a href="show_orders.php" class="btn-link">Back to My Orders</a>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.body.style.opacity = '0';
            setTimeout(() => {
                document.body.style.transition = 'opacity 0.6s ease';
                document.body.style.opacity = '1';
            }, 50);
        });
    </script>

</body>
</html>