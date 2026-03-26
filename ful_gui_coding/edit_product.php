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
$error_state = false;

// جلب الـ product_id من GET
if (!isset($_GET['product_id'])) {
    $message = "Access Denied: No product selected.";
    $msg_type = "error";
    $error_state = true;
} else {
    $product_id = intval($_GET['product_id']);

    // جلب بيانات المنتج الحالي
    $product_res = $conn->query("SELECT * FROM Products WHERE Product_ID=$product_id");
    if ($product_res->num_rows == 0) {
        $message = "Product not found in database.";
        $msg_type = "error";
        $error_state = true;
    } else {
        $product = $product_res->fetch_assoc();

        // جلب جميع التصنيفات للاختيار
        $categories_res = $conn->query("SELECT Category_Name FROM Categories");

        // معالجة التعديل
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_product'])) {
            $name = $_POST['product_name'];
            $desc = $_POST['description'];
            $price = floatval($_POST['price']);
            $stock = intval($_POST['stock']);
            $category = $_POST['category'];

            $stmt = $conn->prepare("UPDATE Products 
                                    SET Product_Name=?,
                                        Description=?,
                                        Current_Price=?,
                                        stock=?,
                                        Category_Name=?
                                    WHERE Product_ID=?");
            $stmt->bind_param("ssdisi", $name, $desc, $price, $stock, $category, $product_id);

            if ($stmt->execute()) {
                $message = "Product details updated successfully!";
                $msg_type = "success";
                // تحديث البيانات بعد الحفظ
                $product_res = $conn->query("SELECT * FROM Products WHERE Product_ID=$product_id");
                $product = $product_res->fetch_assoc();
            } else {
                $message = "Database Error: " . $conn->error;
                $msg_type = "error";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product | Emerald Admin</title>
    <style>
        /* =========================================
           CORE THEME (Emerald Glassmorphism)
           ========================================= */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        
        body {
            font-family: 'Segoe UI', system-ui, sans-serif;
            background-color: #06110e; /* Deep Forest Black */
            color: #f0fdf4;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 40px 20px;
        }

        .bg-image {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: url('https://images.unsplash.com/photo-1542838132-92c53300491e?q=80&w=2074&auto=format&fit=crop') no-repeat center center;
            background-size: cover;
            filter: brightness(0.4) grayscale(0.5);
            z-index: -1;
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
            max-width: 700px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7);
            margin-bottom: 30px;
        }

        h2 { font-size: 2.2em; margin-bottom: 10px; text-align: center; font-weight: 800; color: #fff; }
        h2 span { color: #10b981; } /* Emerald Green */
        .subtitle { text-align: center; color: #64748b; margin-bottom: 30px; font-size: 0.8em; text-transform: uppercase; letter-spacing: 3px; }

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
            color: #34d399; /* Light Emerald */
            margin-bottom: 8px;
            font-weight: 600;
            text-transform: uppercase;
        }

        input, textarea, select {
            width: 100%;
            background: rgba(20, 30, 25, 0.8);
            border: 1px solid rgba(16, 185, 129, 0.1);
            border-radius: 12px;
            padding: 14px;
            color: #ecfdf5;
            font-size: 1em;
            transition: 0.3s;
        }

        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: #10b981;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.15);
            background: rgba(20, 30, 25, 1);
        }

        textarea { height: 120px; resize: none; }

        /* =========================================
           BUTTONS & ALERTS
           ========================================= */
        .btn {
            padding: 14px 28px;
            border-radius: 12px;
            font-weight: 700;
            cursor: pointer;
            transition: 0.3s;
            border: none;
            width: 100%;
            font-size: 1em;
            margin-top: 10px;
        }

        .btn-update { background: #10b981; color: #06110e; }
        .btn-update:hover { background: #34d399; transform: translateY(-2px); box-shadow: 0 10px 20px -5px rgba(16, 185, 129, 0.4); }

        .btn-back {
            background: transparent;
            border: 1px solid #1e293b;
            color: #94a3b8;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            margin-top: 20px;
            border-radius: 50px;
            padding: 12px 40px;
            transition: 0.3s;
        }
        .btn-back:hover { border-color: #34d399; color: #fff; background: rgba(16,185,129,0.05); }

        .alert {
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 25px;
            text-align: center;
            font-weight: 600;
            border: 1px solid transparent;
        }
        .success { background: rgba(16, 185, 129, 0.1); color: #34d399; border-color: rgba(16, 185, 129, 0.2); }
        .error { background: rgba(239, 68, 68, 0.1); color: #ef4444; border-color: rgba(239, 68, 68, 0.2); }

        .fade-in { opacity: 0; animation: fadeIn 0.8s forwards; }
        @keyframes fadeIn { to { opacity: 1; } }
    </style>
</head>
<body>

<div class="bg-image"></div>
<div class="bg-overlay"></div>

<div class="glass-panel fade-in">
    <h2>Edit <span>Product</span></h2>
    <div class="subtitle">Secure Management Portal</div>

    <?php if($message != ""): ?>
        <div class="alert <?= $msg_type ?>">
            <?= $message ?>
        </div>
    <?php endif; ?>

    <?php if(!$error_state): ?>
    <form method="POST">
        <div class="form-grid">
            <div class="full-width">
                <label>Product Name</label>
                <input type="text" name="product_name" value="<?= htmlspecialchars($product['Product_Name']) ?>" required>
            </div>

            <div class="full-width">
                <label>Description</label>
                <textarea name="description" required><?= htmlspecialchars($product['Description']) ?></textarea>
            </div>

            <div>
                <label>Unit Price ($)</label>
                <input type="number" name="price" step="0.01" value="<?= $product['Current_Price'] ?>" required>
            </div>

            <div>
                <label>Inventory Stock</label>
                <input type="number" name="stock" value="<?= $product['stock'] ?>" required>
            </div>

            <div class="full-width">
                <label>Category Classification</label>
                <select name="category" required>
                    <?php while($cat = $categories_res->fetch_assoc()): ?>
                        <option value="<?= htmlspecialchars($cat['Category_Name']) ?>" <?= $product['Category_Name']==$cat['Category_Name'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['Category_Name']) ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>

        <button type="submit" name="update_product" class="btn btn-update">Update Inventory Item</button>
    </form>
    <?php endif; ?>

    <div style="text-align: center;">
        <a href="manage_products.php" class="btn-back">Cancel & Return</a>
    </div>
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