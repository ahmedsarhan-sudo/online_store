<?php
session_start();
include "db_connect.php";

// التأكد أن المستخدم admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: index.php");
    exit();
}

$message = "";
$msg_type = "";

// ==================== Add Product ====================
if (isset($_POST['add_product'])) {
    $name = $_POST['product_name'];
    $desc = $_POST['description'];
    $price = floatval($_POST['current_price']);
    $stock = intval($_POST['stock']);
    $category = $_POST['category_name'];
    $admin_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("INSERT INTO products (Product_Name, Description, Current_Price, Stock, Category_Name, Admin_ID)
            VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssdisi", $name, $desc, $price, $stock, $category, $admin_id);

    if ($stmt->execute()) {
        $message = "Product added successfully!";
        $msg_type = "success";
    } else {
        $message = "Error: " . $conn->error;
        $msg_type = "error";
    }
}

// ==================== Delete Product ====================
if (isset($_POST['delete_product'])) {
    $product_id = intval($_POST['product_id']);

    // 1) حذف أي item مرتبط بالمنتج في Order_Items
    $conn->query("DELETE FROM order_items WHERE Product_ID=$product_id");

    // 2) حذف المنتج نفسه
    $stmt = $conn->prepare("DELETE FROM products WHERE Product_ID=?");
    $stmt->bind_param("i", $product_id);

    if ($stmt->execute()) {
        $message = "Product deleted successfully!";
        $msg_type = "success";
    } else {
        $message = "Error deleting product: " . $conn->error;
        $msg_type = "error";
    }
}

// ==================== Fetch all products ====================
$products_res = $conn->query("SELECT p.*, c.Category_Name 
                              FROM products p
                              JOIN Categories c ON p.Category_Name = c.Category_Name
                              ORDER BY p.Product_ID");

// ==================== Fetch all categories for select ====================
$categories_res = $conn->query("SELECT Category_Name FROM Categories");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Inventory | Emerald Admin</title>
    <style>
        /* =========================================
           CORE THEME (Emerald Glassmorphism)
           ========================================= */
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background-color: #06110e; /* Deep Forest Black */
            color: #ecfdf5;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 40px 20px;
        }

        .bg-image {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: url('https://images.unsplash.com/photo-1542601906990-b4d3fb778b09?q=80&w=2013&auto=format&fit=crop') no-repeat center center;
            background-size: cover;
            filter: brightness(0.15) grayscale(0.5);
            z-index: -2;
        }

        .bg-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: radial-gradient(circle at center, rgba(16, 185, 129, 0.1), #06110e 90%);
            z-index: -1;
        }

        .glass-panel {
            background: rgba(6, 17, 14, 0.8);
            backdrop-filter: blur(25px);
            border: 1px solid rgba(16, 185, 129, 0.2);
            border-radius: 24px;
            padding: 40px;
            width: 100%;
            max-width: 1100px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7);
            margin-bottom: 30px;
        }

        h2 { font-size: 2.5em; margin-bottom: 30px; text-align: center; font-weight: 800; letter-spacing: -1px; color: #fff; }
        h2 span { color: #10b981; } /* Emerald accent */

        h3 {
            color: #34d399; /* Light Emerald */
            font-size: 1.2em;
            margin-bottom: 25px;
            text-transform: uppercase;
            letter-spacing: 2px;
            border-bottom: 1px solid rgba(16, 185, 129, 0.2);
            padding-bottom: 10px;
        }

        /* =========================================
           FORMS & INPUTS
           ========================================= */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .full-width { grid-column: span 2; }

        label {
            display: block;
            font-size: 0.85em;
            color: #10b981;
            margin-bottom: 8px;
            font-weight: 600;
            text-transform: uppercase;
        }

        input, textarea, select {
            width: 100%;
            background: rgba(20, 30, 25, 0.8);
            border: 1px solid rgba(16, 185, 129, 0.1);
            border-radius: 12px;
            padding: 14px 16px;
            color: #fff;
            font-size: 1em;
            transition: all 0.3s ease;
        }

        select option { background: #06110e; color: #fff; }

        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: #10b981;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15);
            background: rgba(20, 30, 25, 1);
        }

        /* =========================================
           TABLE STYLING
           ========================================= */
        .table-container { overflow-x: auto; width: 100%; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; min-width: 800px; }

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
            padding: 16px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            color: #cbd5e1;
            font-size: 0.95em;
        }

        tr:hover td { background: rgba(16, 185, 129, 0.05); }

        .price-tag { color: #34d399; font-weight: 700; }
        .stock-badge {
            background: rgba(16, 185, 129, 0.1);
            color: #10b981;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 0.85em;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        /* =========================================
           BUTTONS & ALERTS
           ========================================= */
        .btn {
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.3s;
            border: none;
            font-size: 0.9em;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-primary { background: #10b981; color: #06110e; width: 100%; margin-top: 10px; }
        .btn-primary:hover { background: #34d399; transform: translateY(-2px); box-shadow: 0 10px 20px -5px rgba(16, 185, 129, 0.4); }

        .btn-edit { background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.3); }
        .btn-edit:hover { background: #10b981; color: #06110e; }

        .btn-delete { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.3); }
        .btn-delete:hover { background: #ef4444; color: #fff; }

        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-weight: 600;
            text-align: center;
            border: 1px solid transparent;
        }
        .success { background: rgba(16, 185, 129, 0.1); color: #34d399; border-color: rgba(16, 185, 129, 0.2); }
        .error { background: rgba(239, 68, 68, 0.1); color: #ef4444; border-color: rgba(239, 68, 68, 0.2); }

        .back-nav { margin-top: 40px; }
        .btn-outline {
            background: transparent;
            border: 1px solid #1e293b;
            color: #94a3b8;
            padding: 14px 40px;
            border-radius: 50px;
            text-decoration: none;
            transition: 0.3s;
            font-weight: 600;
        }
        .btn-outline:hover { border-color: #10b981; color: #fff; background: rgba(16,185,129,0.05); }

        .fade-in { opacity: 0; animation: fadeIn 0.8s forwards; }
        @keyframes fadeIn { to { opacity: 1; } }
    </style>
</head>
<body>

<div class="bg-image"></div>
<div class="bg-overlay"></div>

<div class="glass-panel fade-in">
    <h2>Manage <span>Inventory</span></h2>

    <?php if($message != ""): ?>
        <div class="alert <?= $msg_type ?>"><?= $message ?></div>
    <?php endif; ?>

    <h3>Add New Product</h3>
    <form method="POST">
        <div class="form-grid">
            <div class="full-width">
                <label>Product Name</label>
                <input type="text" name="product_name" placeholder="Enter product name..." required>
            </div>
            
            <div class="full-width">
                <label>Description</label>
                <textarea name="description" placeholder="Technical specifications, features, etc..." required></textarea>
            </div>

            <div>
                <label>Current Price ($)</label>
                <input type="number" step="0.01" name="current_price" placeholder="0.00" required>
            </div>

            <div>
                <label>Stock Quantity</label>
                <input type="number" name="stock" placeholder="0" required>
            </div>

            <div class="full-width">
                <label>Category Classification</label>
                <select name="category_name" required>
                    <option value="" disabled selected>Select a category</option>
                    <?php 
                    $categories_res->data_seek(0); 
                    while($cat = $categories_res->fetch_assoc()): 
                    ?>
                        <option value="<?= htmlspecialchars($cat['Category_Name']) ?>"><?= htmlspecialchars($cat['Category_Name']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>
        <button type="submit" name="add_product" class="btn btn-primary">Create Inventory Item</button>
    </form>
</div>

<div class="glass-panel fade-in" style="animation-delay: 0.2s;">
    <h3>Active Stock List</h3>
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Product Name</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th style="text-align: center;">Control</th>
                </tr>
            </thead>
            <tbody>
                <?php while($prod = $products_res->fetch_assoc()): ?>
                <tr>
                    <td><span style="color:#475569">#<?= $prod['Product_ID'] ?></span></td>
                    <td style="color:#fff; font-weight:600;"><?= htmlspecialchars($prod['Product_Name']) ?></td>
                    <td><?= htmlspecialchars($prod['Category_Name']) ?></td>
                    <td class="price-tag">$<?= number_format($prod['Current_Price'], 2) ?></td>
                    <td><span class="stock-badge"><?= $prod['stock'] ?> In Stock</span></td>
                    <td>
                        <div style="display:flex; gap:8px; justify-content:center;">
                            <a href="edit_product.php?product_id=<?= $prod['Product_ID'] ?>" class="btn btn-edit" style="text-decoration:none;">Edit</a>
                            
                            <form action="" method="POST" style="margin:0;">
                                <input type="hidden" name="product_id" value="<?= $prod['Product_ID'] ?>">
                                <button type="submit" name="delete_product" class="btn btn-delete" onclick="return confirm('Are you sure you want to permanently delete this product?')">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="back-nav">
    <a href="admin_home.php" class="btn-outline">Back to Dashboard</a>
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