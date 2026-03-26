<?php
session_start();
include "db_connect.php";

// التأكد إن المستخدم agent
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'agent') {
    header("Location: login.php");
    exit();
}

$agent_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

// تغيير حالة الأوردر
if (isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $current_status = $_POST['current_status'];

    // تحديد الحالة الجديدة
    if ($current_status == 'Prepared') {
        $new_status = 'IN Delivery';
    } elseif ($current_status == 'IN Delivery') {
        $new_status = 'Delivered';
    } else {
        $new_status = $current_status;
    }

    // تحديث جدول orders
    $stmt = $conn->prepare("UPDATE orders SET status=? WHERE Order_ID=?");
    $stmt->bind_param("si", $new_status, $order_id);
    $stmt->execute();

    // جلب Customer_ID من الأوردر
    $order_res = $conn->query("SELECT Customer_ID FROM orders WHERE Order_ID=$order_id");
    $order_row = $order_res->fetch_assoc();
    $customer_id = $order_row['Customer_ID'];

    // تسجيل التغيير في OrderStatusHistory
    $update_date = date("Y-m-d");
    $stmt_history = $conn->prepare("INSERT INTO OrderStatusHistory (Old_Status, New_Status, Update_Date, Customer_ID, Agent_ID, Order_ID)
                  VALUES (?, ?, ?, ?, ?, ?)");
    $stmt_history->bind_param("sssiii", $current_status, $new_status, $update_date, $customer_id, $agent_id, $order_id);
    $stmt_history->execute();
}

// جلب كل الأوردرات مع اسم العميل
$orders_res = $conn->query("
    SELECT o.Order_ID, o.status, o.order_date, u.First_Name, u.Last_Name
    FROM orders o
    JOIN customers c ON o.Customer_ID = c.Customer_ID
    JOIN users u ON c.Customer_ID = u.User_ID
    ORDER BY o.order_date ASC
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agent Dashboard | Emerald Portal</title>
    <style>
        /* =========================================
           CORE THEME (Emerald Glassmorphism)
           ========================================= */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        
        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background-color: #06110e; 
            color: #ecfdf5;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 40px 20px;
            opacity: 0; /* For fade-in effect */
        }

        .bg-image {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: url('https://images.unsplash.com/photo-1512418490979-92798cec1380?q=80&w=2070&auto=format&fit=crop') no-repeat center center;
            background-size: cover;
            filter: brightness(0.12) grayscale(0.4);
            z-index: -2;
        }

        .bg-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: radial-gradient(circle at center, rgba(16, 185, 129, 0.08), #06110e 95%);
            z-index: -1;
        }

        .glass-panel {
            background: rgba(6, 17, 14, 0.75);
            backdrop-filter: blur(25px);
            border: 1px solid rgba(16, 185, 129, 0.2);
            border-radius: 24px;
            padding: 40px;
            width: 100%;
            max-width: 1000px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7);
            margin-bottom: 30px;
        }

        h2 { font-size: 2.2em; margin-bottom: 10px; text-align: center; font-weight: 800; color: #fff; }
        h2 span { color: #10b981; }
        .subtitle { text-align: center; color: #64748b; margin-bottom: 40px; font-size: 0.8em; text-transform: uppercase; letter-spacing: 4px; }

        /* =========================================
           TABLE & STATUS BADGES
           ========================================= */
        .table-container { overflow-x: auto; width: 100%; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }

        th {
            text-align: left;
            padding: 16px;
            color: #10b981;
            font-size: 0.85em;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 2px solid rgba(16, 185, 129, 0.2);
        }

        td {
            padding: 18px 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            color: #cbd5e1;
            font-size: 0.95em;
        }

        tr:hover td { background: rgba(16, 185, 129, 0.04); }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.8em;
            font-weight: 700;
            background: rgba(255, 255, 255, 0.05);
            color: #94a3b8;
        }
        .status-prepared { color: #f59e0b; background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.2); }
        .status-delivery { color: #38bdf8; background: rgba(56, 189, 248, 0.1); border: 1px solid rgba(56, 189, 248, 0.2); }
        .status-delivered { color: #10b981; background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.2); }

        /* =========================================
           BUTTONS
           ========================================= */
        .btn {
            padding: 10px 18px;
            border-radius: 10px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.3s;
            border: none;
            font-size: 0.85em;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-action { background: #10b981; color: #06110e; }
        .btn-action:hover { background: #34d399; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(16, 185, 129, 0.3); }

        .btn-logout {
            background: transparent;
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #ef4444;
            padding: 12px 35px;
            border-radius: 50px;
            margin-top: 20px;
        }
        .btn-logout:hover { background: rgba(239, 68, 68, 0.1); border-color: #ef4444; transform: scale(1.05); }

        .completed-text { color: #475569; font-style: italic; font-size: 0.9em; }

        .fade-in { animation: fadeIn 0.8s forwards; }
        @keyframes fadeIn { to { opacity: 1; } }
    </style>
</head>
<body class="fade-in">

<div class="bg-image"></div>
<div class="bg-overlay"></div>

<div class="glass-panel">
    <h2>Welcome Agent: <span><?= htmlspecialchars($username) ?></span></h2>
    <div class="subtitle">Operational Logistics Command</div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Order ID</th>
                    <th>Customer Name</th>
                    <th>Date</th>
                    <th>Current Status</th>
                    <th style="text-align: center;">Update Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while($order = $orders_res->fetch_assoc()): 
                    // Dynamic CSS class for status
                    $status_class = '';
                    if($order['status'] == 'Prepared') $status_class = 'status-prepared';
                    elseif($order['status'] == 'IN Delivery') $status_class = 'status-delivery';
                    elseif($order['status'] == 'Delivered') $status_class = 'status-delivered';
                ?>
                <tr>
                    <td><span style="color: #475569;">#<?= $order['Order_ID'] ?></span></td>
                    <td style="color: #fff; font-weight: 600;"><?= htmlspecialchars($order['First_Name'] . ' ' . $order['Last_Name']) ?></td>
                    <td><?= $order['order_date'] ?></td>
                    <td><span class="status-badge <?= $status_class ?>"><?= $order['status'] ?></span></td>
                    <td style="text-align: center;">
                        <?php if($order['status'] != 'Delivered'): ?>
                            <form method="POST" style="margin:0;">
                                <input type="hidden" name="order_id" value="<?= $order['Order_ID'] ?>">
                                <input type="hidden" name="current_status" value="<?= $order['status'] ?>">
                                <button type="submit" name="update_status" class="btn btn-action">
                                    <?php
                                        if($order['status']=='Prepared') echo 'Ship Order';
                                        elseif($order['status']=='IN Delivery') echo 'Mark Delivered';
                                    ?>
                                </button>
                            </form>
                        <?php else: ?>
                            <span class="completed-text">Archived</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<form method="POST" action="logout.php">
    <button type="submit" class="btn btn-logout">Secure Sign Out</button>
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.body.style.opacity = '1';
        document.body.style.transition = 'opacity 0.6s ease';
    });
</script>

</body>
</html>