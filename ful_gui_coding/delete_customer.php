<?php
session_start();
include "db_connect.php";

// --- Authentication & Admin Check ---
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: index.php");
    exit();
}

$message = "";
$error = false;

// --- Deletion Logic ---
if (!isset($_POST['user_id'])) {
    $message = "No customer selected for deletion.";
    $error = true;
} else {
    $customer_id = intval($_POST['user_id']);

    // Disable FK checks to allow cascading manual cleanup
    $conn->query("SET FOREIGN_KEY_CHECKS=0");

    // Restore stock before deleting items
    $items_res = $conn->query("SELECT Product_ID, quantity FROM order_items WHERE Order_ID IN (SELECT Order_ID FROM orders WHERE Customer_ID = $customer_id)");
    while($item = $items_res->fetch_assoc()) {
        $pid = $item['Product_ID'];
        $qty = $item['quantity'];
        $conn->query("UPDATE products SET stock = stock + $qty WHERE Product_ID = $pid");
    }

    // Delete history, payments, and items
    $conn->query("DELETE FROM orderstatushistory WHERE Order_ID IN (SELECT Order_ID FROM orders WHERE Customer_ID = $customer_id)");

    $pay_res = $conn->query("SELECT Payment_ID FROM payments WHERE Order_ID IN (SELECT Order_ID FROM orders WHERE Customer_ID = $customer_id)");
    while($p = $pay_res->fetch_assoc()) {
        $pay_id = $p['Payment_ID'];
        $conn->query("DELETE FROM card WHERE Payment_ID = $pay_id");
        $conn->query("DELETE FROM cash WHERE Payment_ID = $pay_id");
    }
    
    $conn->query("DELETE FROM payments WHERE Order_ID IN (SELECT Order_ID FROM orders WHERE Customer_ID = $customer_id)");
    $conn->query("DELETE FROM order_items WHERE Order_ID IN (SELECT Order_ID FROM orders WHERE Customer_ID = $customer_id)");
    $conn->query("DELETE FROM orders WHERE Customer_ID = $customer_id");
    $conn->query("DELETE FROM customers WHERE Customer_ID=$customer_id");

    // Final deletion from users table
    if($conn->query("DELETE FROM users WHERE User_ID=$customer_id")) {
        $message = "Customer account deleted and stock levels restored successfully.";
        $error = false;
    } else {
        $message = "System error during deletion: " . $conn->error;
        $error = true;
    }

    $conn->query("SET FOREIGN_KEY_CHECKS=1");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Status | Admin System</title>
    <style>
        /* =========================================
           1. CORE RESET & THEME
           ========================================= */
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            position: relative;
            background-color: #0f172a;
        }

        /* =========================================
           2. BACKGROUND
           ========================================= */
        .bg-image {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: url('https://images.unsplash.com/photo-1542838132-92c53300491e?q=80&w=2074&auto=format&fit=crop') no-repeat center center;
            background-size: cover;
            filter: brightness(0.4);
            z-index: -2;
        }

        .bg-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: radial-gradient(circle at center, rgba(15, 23, 42, 0.5), #0f172a 95%);
            z-index: -1;
        }

        /* =========================================
           3. THE GLASS PANEL
           ========================================= */
        .glass-panel {
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 50px 40px;
            width: 100%;
            max-width: 550px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6);
            z-index: 10;
            text-align: center;
        }

        .icon-box {
            font-size: 3.5em;
            margin-bottom: 20px;
        }

        h2 { 
            color: #fff; 
            font-size: 2em; 
            margin-bottom: 15px; 
            font-weight: 700;
        }

        /* Status-specific Styling */
        .status-msg {
            padding: 20px;
            border-radius: 16px;
            font-size: 1.05em;
            line-height: 1.5;
            margin-bottom: 35px;
            border: 1px solid;
        }

        .success {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
            border-color: rgba(16, 185, 129, 0.3);
        }

        .failure {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            border-color: rgba(239, 68, 68, 0.3);
        }

        /* =========================================
           4. RETURN BUTTON (GREEN FRAME)
           ========================================= */
        .btn-green {
            display: inline-block;
            width: 100%;
            padding: 16px;
            background: rgba(16, 185, 129, 0.05);
            color: #10b981;
            border: 1px solid rgba(16, 185, 129, 0.4);
            border-radius: 14px;
            font-size: 1.1em;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-green:hover {
            background: #10b981;
            color: #0f172a;
            transform: translateY(-3px);
            box-shadow: 0 10px 20px -5px rgba(16, 185, 129, 0.4);
        }

        .fade-in {
            opacity: 0;
            transform: scale(0.95);
            animation: popIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
        }

        @keyframes popIn {
            to { opacity: 1; transform: scale(1); }
        }
    </style>
</head>
<body>

<div class="bg-image"></div>
<div class="bg-overlay"></div>

<div class="glass-panel fade-in">
    <div class="icon-box">
        <?= $error ? '⚠️' : '✅' ?>
    </div>
    
    <h2>Action Processed</h2>

    <div class="status-msg <?= $error ? 'failure' : 'success' ?>">
        <?= htmlspecialchars($message) ?>
    </div>

    <a href="view_all_customers.php" class="btn-green">
        Return to Customer List
    </a>
</div>

</body>
</html>