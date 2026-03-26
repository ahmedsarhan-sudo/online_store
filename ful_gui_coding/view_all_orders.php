<?php
session_start();
include "db_connect.php";

// --- Authentication & Admin Check ---
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: index.php");
    exit();
}

// جلب كل الأوردرات مع اسم العميل وعدد المنتجات والإجمالي
$sql = "
SELECT o.Order_ID, o.Order_Date, o.Status, u.First_Name, u.Last_Name,
       COUNT(oi.Product_ID) AS total_products,
       IFNULL(SUM(oi.total_price),0) AS order_total
FROM orders o
JOIN users u ON o.Customer_ID = u.User_ID
LEFT JOIN order_items oi ON o.Order_ID = oi.Order_ID
GROUP BY o.Order_ID
ORDER BY o.Order_Date DESC
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Management | Admin</title>
    <style>
        /* =========================================
           1. CORE RESET & THEME
           ========================================= */
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #f8fafc;
            min-height: 100vh;
            padding: 40px 20px;
            position: relative;
            background-color: #0f172a;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* =========================================
           2. BACKGROUND
           ========================================= */
        .bg-image {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: url('https://images.unsplash.com/photo-1542838132-92c53300491e?q=80&w=2074&auto=format&fit=crop') no-repeat center center;
            background-size: cover;
            filter: brightness(0.5);
            z-index: -2;
        }

        .bg-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: radial-gradient(circle at center, rgba(15, 23, 42, 0.6), #0f172a 98%);
            z-index: -1;
        }

        /* =========================================
           3. CONTENT & TABLE STRUCTURE
           ========================================= */
        h2 { 
            font-size: 2.5em; 
            margin-bottom: 30px; 
            color: #fff;
            text-shadow: 0 4px 10px rgba(0,0,0,0.5);
        }
        
        h2 span { color: #10b981; }

        .table-container {
            width: 100%;
            max-width: 1200px;
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
        }

        th {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
            padding: 20px;
            font-size: 0.9em;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 1px solid rgba(16, 185, 129, 0.3);
            text-align: center;
        }

        td {
            padding: 18px 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            color: #cbd5e1;
            text-align: center;
        }

        tr:hover td {
            background: rgba(255, 255, 255, 0.03);
            color: #fff;
        }

        /* =========================================
           4. BADGES & ACTION BUTTONS
           ========================================= */
        .badge {
            padding: 6px 12px;
            border-radius: 100px;
            font-size: 0.8em;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .status-Preparing { background: rgba(59, 130, 246, 0.2); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.3); }
        .status-Prepared { background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3); }
        .status-Waiting { background: rgba(245, 158, 11, 0.2); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.3); }
        .status-Declined { background: rgba(239, 68, 68, 0.2); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.3); }

        .btn-group { display: flex; gap: 8px; justify-content: center; }

        .btn-action {
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 0.85em;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 1px solid transparent;
        }

        .btn-view {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
            border-color: rgba(16, 185, 129, 0.3);
        }

        .btn-view:hover {
            background: #10b981;
            color: #0f172a;
        }

        .btn-delete {
            background: rgba(239, 68, 68, 0.1);
            color: #ef4444;
            border-color: rgba(239, 68, 68, 0.3);
        }

        .btn-delete:hover {
            background: #ef4444;
            color: #fff;
        }

        .btn-home {
            margin-top: 30px;
            padding: 15px 40px;
            background: transparent;
            color: #94a3b8;
            border: 1px solid rgba(148, 163, 184, 0.3);
            border-radius: 50px;
            font-weight: 600;
            transition: 0.3s;
            cursor: pointer;
        }

        .btn-home:hover {
            border-color: #fff;
            color: #fff;
            background: rgba(255, 255, 255, 0.05);
        }

        /* Fade-in */
        .fade-in {
            opacity: 0;
            transform: translateY(10px);
            animation: fadeIn 0.6s forwards;
        }

        @keyframes fadeIn {
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>

<div class="bg-image"></div>
<div class="bg-overlay"></div>

<h2 class="fade-in">System <span>Orders</span></h2>

<div class="table-container fade-in">
    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Customer</th>
                <th>Order Date</th>
                <th>Status</th>
                <th>Products</th>
                <th>Total Value</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><span style="color: #64748b;">#</span><?= $row['Order_ID'] ?></td>
                    <td style="font-weight: 600; color: #fff;"><?= $row['First_Name'] . " " . $row['Last_Name'] ?></td>
                    <td><?= date("M d, Y", strtotime($row['Order_Date'])) ?></td>
                    <td>
                        <span class="badge status-<?= $row['Status'] ?>">
                            <?= $row['Status'] ?>
                        </span>
                    </td>
                    <td><?= $row['total_products'] ?></td>
                    <td style="font-weight: bold; color: #10b981;">$<?= number_format($row['order_total'], 2) ?></td>
                    <td>
                        <div class="btn-group">
                            <form action="view_order_details.php" method="GET">
                                <input type="hidden" name="order_id" value="<?= $row['Order_ID'] ?>">
                                <button type="submit" class="btn-action btn-view">Details</button>
                            </form>
                            <form action="delete_order.php" method="POST">
                                <input type="hidden" name="order_id" value="<?= $row['Order_ID'] ?>">
                                <button type="submit" class="btn-action btn-delete" onclick="return confirm('Are you sure you want to delete this order permanently?')">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
</div>

<form action="admin_home.php" method="GET">
    <button type="submit" class="btn-home">Return to Admin Home</button>
</form>

</body>
</html>