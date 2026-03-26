<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'customer') {
    header("Location: index.php");
    exit();
}

$customer_id = $_SESSION['user_id'];
$order_id = intval($_POST['order_id'] ?? 0);

if (!$order_id) {
    echo "Invalid order!";
    exit();
}

// تأكيد أن الأوردر يخص نفس العميل
$stmt = $conn->prepare("SELECT Status FROM orders WHERE Order_ID=? AND Customer_ID=?");
$stmt->bind_param("ii", $order_id, $customer_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo "Order not found.";
    exit();
}

$order = $result->fetch_assoc();
$old_status = $order['Status'];
$status_class = "error"; // For styling the result icon

// لو الأوردر مش Declined بالفعل
if ($old_status != 'Declined') {
    $update = $conn->prepare("UPDATE orders SET Status='Declined' WHERE Order_ID=?");
    $update->bind_param("i", $order_id);
    $update->execute();

    $agent_res = $conn->query("SELECT Agent_ID FROM orderstatushistory WHERE Order_ID=$order_id ORDER BY History_ID DESC LIMIT 1");
    $agent_id = null;
    if ($agent_res->num_rows > 0) {
        $row = $agent_res->fetch_assoc();
        $agent_id = $row['Agent_ID'];
    }

    $today = date('Y-m-d');
    $new_status = 'Declined';
    $history_stmt = $conn->prepare("
        INSERT INTO orderstatushistory (Old_Status, New_Status, Update_Date, Customer_ID, Agent_ID, Order_ID) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $history_stmt->bind_param("sssiii", $old_status, $new_status, $today, $customer_id, $agent_id, $order_id);
    $history_stmt->execute();

    $message = "Order #$order_id has been canceled.";
    $sub_message = "All items have been removed from your active queue.";
} else {
    $message = "Order #$order_id was already declined.";
    $sub_message = "No further action is required for this order.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Canceled | Premium Market</title>
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

        /* 3D Background & Overlay */
        .bg-image {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: url('https://images.unsplash.com/photo-1542838132-92c53300491e?q=80&w=2074&auto=format&fit=crop') no-repeat center center;
            background-size: cover;
            filter: brightness(0.4);
            opacity: 0.5;
            transform: scale(1.05);
            z-index: -2;
        }

        .bg-overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: radial-gradient(circle at center, rgba(15, 23, 42, 0) 0%, #020617 100%);
            z-index: -1;
        }

        /* Glass Card */
        .glass-card {
            width: 100%;
            max-width: 450px;
            background: rgba(30, 41, 59, 0.4);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 32px;
            padding: 50px 40px;
            text-align: center;
            box-shadow: 0 50px 100px -20px rgba(0, 0, 0, 0.5);
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Status Icon Styling */
        .status-icon {
            width: 80px;
            height: 80px;
            background: rgba(239, 68, 68, 0.1);
            border: 2px solid #ef4444;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 30px;
            color: #ef4444;
            font-size: 40px;
            font-weight: 800;
        }

        h1 {
            font-size: 26px;
            font-weight: 800;
            color: #fff;
            margin-bottom: 15px;
        }

        .sub-message {
            color: #94a3b8;
            font-size: 15px;
            line-height: 1.6;
            margin-bottom: 40px;
        }

        /* Button Styling */
        .btn-back {
            display: inline-block;
            width: 100%;
            padding: 16px;
            background: #fff;
            color: #020617;
            text-decoration: none;
            border-radius: 14px;
            font-weight: 700;
            font-size: 15px;
            transition: 0.3s;
        }

        .btn-back:hover {
            background: #f1f5f9;
            transform: scale(1.02);
            box-shadow: 0 10px 20px rgba(255, 255, 255, 0.1);
        }
    </style>
</head>
<body>

    <div class="bg-image"></div>
    <div class="bg-overlay"></div>

    <div class="glass-card">
        <div class="status-icon">×</div>
        <h1><?php echo $message; ?></h1>
        <p class="sub-message"><?php echo $sub_message; ?></p>
        
        <a href="show_orders.php" class="btn-back">Back to My Orders</a>
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