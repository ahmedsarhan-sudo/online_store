<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: index.php");
    exit();
}

$message = "";
$error = false;

if (!isset($_POST['order_id'])) {
    $message = "No order selected.";
    $error = true;
} else {

    $order_id = intval($_POST['order_id']);

    // ✅ 1) هات البيانات قبل الحذف
    $order_res = $conn->query("
        SELECT o.Order_ID, o.Order_Date, o.Status, u.First_Name, u.Last_Name
        FROM orders o
        JOIN users u ON o.Customer_ID = u.User_ID
        WHERE o.Order_ID = $order_id
    ");

    if ($order_res->num_rows == 0) {
        $message = "Order not found.";
        $error = true;
    } else {

        $order = $order_res->fetch_assoc();

        $items_res = $conn->query("
            SELECT oi.Product_ID, p.Product_Name, oi.quantity, oi.unit_price, oi.total_price
            FROM order_items oi
            JOIN products p ON oi.Product_ID = p.Product_ID
            WHERE oi.Order_ID = $order_id
        ");

        // خزّن البيانات في array عشان هتتمسح
        $items = [];
        while ($row = $items_res->fetch_assoc()) {
            $items[] = $row;
        }

        // ✅ 2) ابدأ الحذف
        $errors = [];

        // payments IDs
        $pay_res = $conn->query("SELECT Payment_ID FROM payments WHERE Order_ID = $order_id");
        $payment_ids = [];

        while ($p = $pay_res->fetch_assoc()) {
            $payment_ids[] = $p['Payment_ID'];
        }

        foreach ($payment_ids as $pid) {
            $stmt = $conn->prepare("DELETE FROM cash WHERE Payment_ID = ?");
            $stmt->bind_param("i", $pid);
            if (!$stmt->execute()) {
                $errors[] = "Error deleting cash";
            }
        }

        $stmt = $conn->prepare("DELETE FROM orderstatushistory WHERE Order_ID = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();

        // restore stock
        foreach ($items as $item) {
            $conn->query("UPDATE products SET stock = stock + {$item['quantity']} WHERE Product_ID = {$item['Product_ID']}");
        }

        $stmt = $conn->prepare("DELETE FROM order_items WHERE Order_ID = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();

        $stmt = $conn->prepare("DELETE FROM payments WHERE Order_ID = ?");
        $stmt->bind_param("i", $order_id);
        $stmt->execute();

        $stmt = $conn->prepare("DELETE FROM orders WHERE Order_ID = ?");
        $stmt->bind_param("i", $order_id);

        if ($stmt->execute()) {
            $message = "✅ Order deleted successfully!";
        } else {
            $message = "❌ Error deleting order";
            $error = true;
        }

        if (!empty($errors)) {
            $message .= "<br>" . implode("<br>", $errors);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Detail #<?= $order_id ?? 'Error' ?> | Admin</title>
    <style>
        /* =========================================
           1. CORE THEME & BACKGROUND
           ========================================= */
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', sans-serif;
            color: #f8fafc;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 40px 20px;
            background-color: #0f172a;
        }

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
           2. GLASS PANELS
           ========================================= */
        .glass-panel {
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 40px;
            width: 100%;
            max-width: 900px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6);
            margin-bottom: 30px;
        }

        h2 { font-size: 2.2em; margin-bottom: 25px; text-align: center; color: #fff; }
        h2 span { color: #10b981; }

        /* Summary Header */
        .order-meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
            background: rgba(16, 185, 129, 0.05);
            padding: 20px;
            border-radius: 16px;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .meta-item label {
            display: block;
            font-size: 0.8em;
            text-transform: uppercase;
            color: #94a3b8;
            letter-spacing: 1px;
            margin-bottom: 5px;
        }

        .meta-item p { font-size: 1.1em; font-weight: 600; color: #fff; }

        /* Table Styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th {
            text-align: left;
            padding: 15px;
            color: #10b981;
            font-size: 0.9em;
            border-bottom: 1px solid rgba(16, 185, 129, 0.3);
        }

        td {
            padding: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            color: #cbd5e1;
        }

        .grand-total-row td {
            border-top: 2px solid #10b981;
            border-bottom: none;
            padding-top: 25px;
            font-size: 1.3em;
            color: #fff;
        }

        /* =========================================
           3. BUTTONS & UI
           ========================================= */
        .btn-back {
            display: inline-block;
            padding: 14px 35px;
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
            border: 1px solid rgba(16, 185, 129, 0.4);
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: 0.3s ease;
        }

        .btn-back:hover {
            background: #10b981;
            color: #0f172a;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -5px rgba(16, 185, 129, 0.4);
        }

        .alert {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            padding: 20px;
            border-radius: 12px;
            border: 1px solid rgba(239, 68, 68, 0.3);
            text-align: center;
        }

        .fade-in { opacity: 0; animation: fadeIn 0.8s forwards; }
        @keyframes fadeIn { to { opacity: 1; } }
    </style>
</head>
<body>

<div class="bg-image"></div>
<div class="bg-overlay"></div>

<div class="glass-panel fade-in">
    <?php if($error): ?>
        <div class="alert"><?= $message ?></div>
    <?php else: ?>
        <h2>Invoice <span>#<?= $order['Order_ID'] ?></span></h2>

        <div class="order-meta">
            <div class="meta-item">
                <label>Customer</label>
                <p><?= htmlspecialchars($order['First_Name'] . " " . $order['Last_Name']) ?></p>
            </div>
            <div class="meta-item">
                <label>Date Placed</label>
                <p><?= date("F j, Y", strtotime($order['Order_Date'])) ?></p>
            </div>
            <div class="meta-item">
                <label>Order Status</label>
                <p style="color: #10b981;"><?= $order['Status'] ?></p>
            </div>
        </div>

        <table>
            <thead>
                <tr>
                    <th>SKU / ID</th>
                    <th>Product Name</th>
                    <th style="text-align: center;">Qty</th>
                    <th style="text-align: right;">Unit Price</th>
                    <th style="text-align: right;">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $grand_total = 0;
                while($item = $items_res->fetch_assoc()): 
                    $grand_total += $item['total_price'];
                ?>
                <tr>
                    <td style="color: #64748b;">#<?= $item['Product_ID'] ?></td>
                    <td style="color: #fff; font-weight: 500;"><?= $item['Product_Name'] ?></td>
                    <td style="text-align: center;"><?= $item['quantity'] ?></td>
                    <td style="text-align: right;">$<?= number_format($item['unit_price'], 2) ?></td>
                    <td style="text-align: right; color: #fff;">$<?= number_format($item['total_price'], 2) ?></td>
                </tr>
                <?php endwhile; ?>

                <tr class="grand-total-row">
                    <td colspan="3"></td>
                    <td style="text-align: right; font-weight: bold; color: #10b981;">Total Amount</td>
                    <td style="text-align: right; font-weight: bold;">$<?= number_format($grand_total, 2) ?></td>
                </tr>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<a href="view_all_orders.php" class="btn-back">Return to Orders</a>

</body>
</html>