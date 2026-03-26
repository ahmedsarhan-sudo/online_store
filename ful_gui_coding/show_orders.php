<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'customer') {
    header("Location: index.php");
    exit();
}

$customer_id = $_SESSION['user_id'];

// جلب كل الأوردرات الخاصة بالعميل
$orders = $conn->query("SELECT * FROM orders WHERE Customer_ID=$customer_id ORDER BY Order_Date DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders | Premium Marketplace</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', sans-serif; }

        body {
            background-color: #020617;
            color: #f8fafc;
            min-height: 100vh;
            padding: 60px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* 3D Background */
        .bg-image {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: url('https://images.unsplash.com/photo-1550989460-0adf9ea622e2?w=1200&auto=format&fit=crop&q=80&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxzZWFyY2h8MTl8fHN1cGVybWFya2V0fGVufDB8fDB8fHww') 
                        no-repeat center center;
            background-size: cover;
            opacity: 0.5;
            z-index: -2;
            transform: scale(1.05);
        }

        .bg-overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: radial-gradient(circle at center, rgba(2, 6, 23, 0.4) 0%, rgba(2, 6, 23, 0.95) 100%);
            z-index: -1;
        }

        h2 { 
            font-size: 42px; 
            font-weight: 800; 
            color: #fff; 
            margin-bottom: 40px; 
            text-align: center;
            background: linear-gradient(to right, #fff, #94a3b8);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Order Card */
        .order-card {
            width: 100%;
            max-width: 900px;
            background: rgba(30, 41, 59, 0.5);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 30px;
            margin-bottom: 40px;
            box-shadow: 0 20px 50px rgba(0,0,0,0.4);
            transition: transform 0.3s ease;
        }

        .order-card:hover { transform: translateY(-5px); border-color: rgba(16, 185, 129, 0.3); }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .order-id { font-size: 20px; font-weight: 700; color: #ffd700; }
        .order-date { color: #94a3b8; font-size: 14px; }

        /* Status Badges */
        .badge {
            padding: 6px 16px;
            border-radius: 100px;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .status-Preparing { background: rgba(59, 130, 246, 0.2); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.3); }
        .status-Prepared { background: rgba(16, 185, 129, 0.2); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3); }
        .status-Waiting { background: rgba(245, 158, 11, 0.2); color: #fbbf24; border: 1px solid rgba(245, 158, 11, 0.3); }
        .status-Declined { background: rgba(239, 68, 68, 0.2); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.3); }

        /* Table Styling */
        table { width: 100%; border-collapse: collapse; margin-bottom: 25px; }
        th { text-align: left; padding: 12px; font-size: 12px; text-transform: uppercase; color: #64748b; letter-spacing: 1px; }
        td { padding: 16px 12px; font-size: 15px; border-bottom: 1px solid rgba(255, 255, 255, 0.03); }
        .total-row td { border: none; padding-top: 25px; font-size: 18px; font-weight: 800; color: #fff; }

        /* Buttons Area */
        .actions-container { display: flex; gap: 12px; justify-content: flex-end; flex-wrap: wrap; }
        
        .btn {
            padding: 12px 24px;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
        }

        .btn-primary { background: #10b981; color: #020617; }
        .btn-primary:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(16, 185, 129, 0.3); }

        .btn-outline { background: transparent; border: 1px solid rgba(255,255,255,0.1); color: #fff; }
        .btn-outline:hover { background: rgba(255,255,255,0.05); border-color: #fff; }

        .btn-danger { background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3); color: #fca5a5; }
        .btn-danger:hover { background: #ef4444; color: #fff; }

        .back-btn { margin-top: 20px; }

        .warning-text { font-size: 12px; color: #fbbf24; display: block; margin-top: 5px; }
    </style>
</head>
<body>

    <div class="bg-image"></div>
    <div class="bg-overlay"></div>

    <h2>Order History</h2>

    <?php while($order = $orders->fetch_assoc()): ?>
        <div class="order-card">
            <div class="order-header">
                <div>
                    <span class="order-id">Order #<?php echo $order['Order_ID']; ?></span>
                    <div class="order-date"><?php echo date("M d, Y", strtotime($order['Order_Date'])); ?></div>
                </div>
                <div>
                    <span class="badge status-<?php echo $order['Status']; ?>">
                        <?php echo $order['Status']; ?>
                    </span>
                    <?php if($order['Status'] == 'Waiting'): ?>
                        <span class="warning-text">Awaiting current session completion</span>
                    <?php endif; ?>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Product Details</th>
                        <th>Price</th>
                        <th>Qty</th>
                        <th style="text-align: right;">Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $items = $conn->query("SELECT oi.*, p.Product_Name, p.Current_Price 
                                           FROM order_items oi 
                                           JOIN products p ON oi.Product_ID=p.Product_ID
                                           WHERE oi.Order_ID=".$order['Order_ID']);
                    $total = 0;
                    while($item = $items->fetch_assoc()):
                        $subtotal = $item['Current_Price'] * $item['quantity'];
                        $total += $subtotal;
                    ?>
                        <tr>
                            <td style="font-weight: 600;"><?php echo $item['Product_Name']; ?></td>
                            <td style="color: #94a3b8;">$<?php echo number_format($item['Current_Price'], 2); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td style="text-align: right; font-weight: 600;">$<?php echo number_format($subtotal, 2); ?></td>
                        </tr>
                    <?php endwhile; ?>
                    
                    <tr class="total-row">
                        <td colspan="2"></td>
                        <td style="color: #64748b; font-size: 14px;">Grand Total</td>
                        <td style="text-align: right; color: #10b981;">$<?php echo number_format($total, 2); ?></td>
                    </tr>
                </tbody>
            </table>

            <div class="actions-container">
                <?php if($order['Status'] == 'Preparing'): ?>
                    <form method="POST" action="end_shopping.php">
                        <input type="hidden" name="order_id" value="<?php echo $order['Order_ID']; ?>">
                        <button type="submit" class="btn btn-primary">Complete Checkout</button>
                    </form>
                <?php endif; ?>

                <?php if($order['Status'] == 'Prepared'): ?>
                    <form method="POST" action="reopen_order.php">
                        <input type="hidden" name="order_id" value="<?php echo $order['Order_ID']; ?>">
                        <button type="submit" class="btn btn-outline">Reopen Items</button>
                    </form>
                <?php endif; ?>

                <?php if($order['Status'] != 'Declined'): ?>
                <form method="POST" action="decline_order.php">
                    <input type="hidden" name="order_id" value="<?php echo $order['Order_ID']; ?>">
                    <button type="submit" class="btn btn-danger">Cancel Order</button>
                </form>
                <?php endif; ?>
            </div>
        </div>
    <?php endwhile; ?>

    <a href="customer_home.php" class="btn btn-outline back-btn">← Back to Shopping</a>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.body.style.opacity = '0';
            setTimeout(() => {
                document.body.style.transition = 'opacity 0.8s ease-in';
                document.body.style.opacity = '1';
            }, 100);
        });
    </script>

</body>
</html>