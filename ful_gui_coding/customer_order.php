<?php
include "db_connect.php";
session_start();

$customer_id = $_SESSION['user_id'] ?? 0;
if (!$customer_id) {
    echo "Please login first.";
    exit;
}

$status_message = "";
$status_type = ""; // 'success' or 'error'

// الحصول على الأوردر الحالي
$order_sql = "SELECT * FROM orders WHERE Customer_ID = $customer_id AND Status = 'Preparing'";
$res = $conn->query($order_sql);

if ($res->num_rows > 0) {
    $order = $res->fetch_assoc();
    $order_id = $order['Order_ID'];
} else {
    $date = date("Y-m-d");
    $conn->query("INSERT INTO orders (Order_Date, Status, Customer_ID) VALUES ('$date','Preparing',$customer_id)");
    $order_id = $conn->insert_id;
}

// إضافة منتج
if ($_SERVER['REQUEST_METHOD'] == "POST" && isset($_POST['product_id'], $_POST['quantity'])) {

    $product_id = $_POST['product_id'];
    $quantity = intval($_POST['quantity']);

    // جلب بيانات المنتج
    $p = $conn->query("SELECT Product_Name, Current_Price, stock FROM products WHERE Product_ID=$product_id");
    if ($p->num_rows == 0) {
        $status_message = "Product does not exist.";
        $status_type = "error";
    } else {
        $product = $p->fetch_assoc();

        if ($product['stock'] < $quantity) {
            $status_message = "Not enough stock for {$product['Product_Name']}!";
            $status_type = "error";
        } else {
            $check = $conn->query("SELECT quantity, total_price FROM order_items WHERE Order_ID=$order_id AND Product_ID=$product_id");

            if ($check->num_rows > 0) {
                $conn->query("UPDATE order_items SET quantity = quantity + $quantity, total_price = total_price + ($quantity * {$product['Current_Price']}) WHERE Order_ID=$order_id AND Product_ID=$product_id");
            } else {
                $conn->query("INSERT INTO order_items (Order_ID, Product_ID, unit_price, quantity, total_price) VALUES ($order_id, $product_id, {$product['Current_Price']}, $quantity, ($quantity * {$product['Current_Price']}))");
            }

            $conn->query("UPDATE products SET stock = stock - $quantity WHERE Product_ID=$product_id");
            $status_message = "Product added successfully!";
            $status_type = "success";
        }
    }
}

$products_res = $conn->query("SELECT * FROM products");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Marketplace | Add Product</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', sans-serif; }

        body {
            background-color: #020617;
            color: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow-x: hidden;
            padding: 40px 20px;
        }

        /* 3D Background Expressing Market */
        .bg-image {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: url('https://images.pexels.com/photos/1759484/pexels-photo-1759484.jpeg') no-repeat center center/cover;
            opacity: 0.5; /* شفافية مناسبة */
            z-index: -2;
            transform: scale(1.1);
        }

        .bg-overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: radial-gradient(circle at center, rgba(2, 6, 23, 0.4) 0%, rgba(2, 6, 23, 0.85) 100%);
            z-index: -1;
        }

        /* 3D Floating Elements */
        .float-icon {
            position: absolute;
            z-index: 0; opacity: 0.6;
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-30px) rotate(10deg); }
        }

        /* Glass Container */
        .glass-card {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 550px;
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(25px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 32px;
            padding: 40px;
            box-shadow: 0 40px 100px rgba(0,0,0,0.6);
            text-align: center;
        }

        h2 { font-size: 32px; font-weight: 800; color: #ffd700; margin-bottom: 10px; text-shadow: 0 4px 10px rgba(0,0,0,0.3); }
        .sub-text { color: #94a3b8; margin-bottom: 30px; font-size: 15px; }

        /* Status Messages */
        .status-alert {
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-weight: 600;
            font-size: 14px;
        }
        .success { background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3); }
        .error { background: rgba(239, 68, 68, 0.15); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.3); }

        /* Form Styling */
        form { text-align: left; }
        .input-group { margin-bottom: 20px; }
        label { display: block; font-size: 13px; font-weight: 700; color: #cbd5e1; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px; }
        
        select, input {
            width: 100%;
            padding: 15px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: #fff;
            font-size: 15px;
            transition: 0.3s;
        }
        
        select:focus, input:focus {
            outline: none;
            border-color: #10b981;
            background: rgba(255, 255, 255, 0.1);
            box-shadow: 0 0 15px rgba(16, 185, 129, 0.2);
        }

        select option { background: #1e293b; }

        .btn-add {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #020617;
            border: none;
            border-radius: 14px;
            font-size: 16px;
            font-weight: 800;
            cursor: pointer;
            transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            margin-top: 10px;
        }

        .btn-add:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(16, 185, 129, 0.4);
        }

        /* Bottom Navigation */
        .nav-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
            padding-top: 25px;
            border-top: 1px solid rgba(255,255,255,0.05);
        }

        .btn-nav {
            flex: 1;
            padding: 12px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            color: #94a3b8;
            font-size: 13px;
            font-weight: 600;
            text-decoration: none;
            transition: 0.3s;
            text-align: center;
        }

        .btn-nav:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            border-color: #ffd700;
        }
    </style>
</head>
<body>

    <div class="bg-image"></div>
    <div class="bg-overlay"></div>
    
    <div class="float-icon" style="top: 15%; right: 10%; font-size: 70px;">🍎</div>
    <div class="float-icon" style="bottom: 15%; left: 10%; font-size: 50px; animation-delay: 2s;">🥛</div>

    <div class="glass-card">
        <h2>Stock the Cart</h2>
        <p class="sub-text">Order ID: #<?= $order_id ?> | Fill your basket with the best deals</p>

        <?php if ($status_message): ?>
            <div class="status-alert <?= $status_type ?>">
                <?= $status_message ?>
            </div>
        <?php endif; ?>

        <form method="POST" onsubmit="return validateForm()">
            <div class="input-group">
                <label>Select Your Item</label>
                <select name="product_id" required>
                    <option value="" disabled selected>-- Browse Market Catalog --</option>
                    <?php while($prod = $products_res->fetch_assoc()): ?>
                        <option value="<?= $prod['Product_ID'] ?>">
                            <?= $prod['Product_Name'] ?> — $<?= number_format($prod['Current_Price'], 2) ?> (In Stock: <?= $prod['stock'] ?>)
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="input-group">
                <label>Desired Quantity</label>
                <input type="number" name="quantity" min="1" placeholder="How many do you need?" required>
            </div>

            <button type="submit" class="btn-add">Add to My Order</button>
        </form>

        <div class="nav-group">
            <a href="show_orders.php" class="btn-nav">View My Orders</a>
            <a href="customer_home.php" class="btn-nav">Market Home</a>
        </div>
    </div>

    <script>
        function validateForm() {
            let productId = document.querySelector('select[name="product_id"]').value;
            let quantity = document.querySelector('input[name="quantity"]').value;

            if (!productId) {
                alert('Please select a product from the shelf!');
                return false;
            }
            if (quantity < 1) {
                alert('Quantity must be at least 1!');
                return false;
            }
            return true;
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.body.style.opacity = '0';
            setTimeout(() => {
                document.body.style.transition = 'opacity 1s';
                document.body.style.opacity = '1';
            }, 100);
        });
    </script>
</body>
</html>