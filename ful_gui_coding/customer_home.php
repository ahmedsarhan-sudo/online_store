<?php
session_start();
include "db_connect.php";

// التأكد أن المستخدم عامل login و customer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'customer') {
    header("Location: index.php");
    exit();
}

$customer_id = $_SESSION['user_id'];

// جلب اسم العميل من جدول users
$user = $conn->query("SELECT First_Name, Last_Name FROM users WHERE User_ID=$customer_id")->fetch_assoc();
$first = $user['First_Name'];
$last = $user['Last_Name'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Dashboard</title>
    <style>
        /* =========================================
           1. CORE TYPOGRAPHY & RESET
           ========================================= */
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #f8fafc; /* Light Slate text */
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            overflow-y: auto;
            position: relative;
        }

        /* =========================================
           2. 3D DEPTH PERCEPTION (LAYERS)
           ========================================= */
        
        /* Layer -2: The Background Image (The "World") */
        .bg-image {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: url('https://images.unsplash.com/photo-1542838132-92c53300491e?q=80&w=2074&auto=format&fit=crop') no-repeat center center;
            background-size: cover;
            filter: brightness(0.8); /* قللنا الـ blur وحسّنّا الـ brightness */
            z-index: -2;
            transform: scale(1.1);
        }

        /* Layer -1: The Radial Gradient (The "Shadow/Mood") */
        .bg-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            /* Dark Slate radial gradient to create focus in the center */
            background: radial-gradient(circle at center, rgba(15, 23, 42, 0.5), #0f172a 90%);
            z-index: -1;
        }

        /* Layer 10: The Glass Card (The "Interactive Interface") */
        .glass-panel {
            background: rgba(15, 23, 42, 0.6); /* Translucent Deep Slate */
            backdrop-filter: blur(20px); /* The CSS Magic */
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1); /* Subtle edge highlight */
            border-radius: 24px;
            padding: 50px 40px;
            width: 100%;
            max-width: 800px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            text-align: center;
            z-index: 10;
        }

        /* =========================================
           3. EMERALD & SLATE COLOR THEORY
           ========================================= */
        h1 {
            color: #10b981; /* Emerald Green */
            font-size: 2.5em;
            margin-bottom: 10px;
            font-weight: 600;
            letter-spacing: 1px;
        }

        p.subtitle {
            color: #94a3b8; /* Muted Slate */
            margin-bottom: 40px;
            font-size: 1.1em;
        }

        /* =========================================
           4. VISUAL HIERARCHY (THE GRID)
           ========================================= */
        .button-grid {
            display: grid;
            /* 2-Column layout that drops to 1 column on small screens */
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            width: 100%;
        }

        form {
            width: 100%;
        }

        button {
            width: 100%;
            padding: 18px 20px;
            background: rgba(16, 185, 129, 0.05); /* Very subtle green tint */
            color: #10b981; /* Emerald Green Text */
            border: 1px solid rgba(16, 185, 129, 0.3); /* Emerald Border */
            border-radius: 16px;
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        /* Hover Effects */
        button:hover {
            background: #10b981; /* Solid Emerald Background */
            color: #0f172a; /* Dark Slate Text for high contrast */
            border-color: #10b981;
            transform: translateY(-4px);
            box-shadow: 0 10px 20px -5px rgba(16, 185, 129, 0.4); /* Emerald Glow */
        }

        /* Specific styling for the logout/go back button to separate it visually */
        .btn-secondary {
            background: rgba(255, 255, 255, 0.05);
            color: #e2e8f0;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }
        .btn-secondary:hover {
            background: #e2e8f0;
            color: #0f172a;
            border-color: #e2e8f0;
            box-shadow: 0 10px 20px -5px rgba(255, 255, 255, 0.2);
        }

    </style>
</head>
<body>

<div class="bg-image"></div>
<div class="bg-overlay"></div>

<div class="glass-panel">
    <h1>Welcome, <?= htmlspecialchars($first) ?> <?= htmlspecialchars($last) ?>!</h1>
    <p class="subtitle">What would you like to do today?</p>

    <div class="button-grid">
        <form action="show_orders.php" method="GET">
            <button type="submit">📦 Show My Orders</button>
        </form>

        <form action="customer_order.php" method="GET">
            <button type="submit">🛒 Add Products to Order</button>
        </form>

        <form action="update_info.php" method="GET">
            <button type="submit">⚙️ My Info</button>
        </form>

        <form action="index.php" method="GET">
            <button type="submit" class="btn-secondary">⬅️ Go to Registration Page</button>
        </form>
    </div>
</div>

<script>
    // تأثير fade-in للصفحة
    document.addEventListener('DOMContentLoaded', function() {
        document.body.style.opacity = '0';
        setTimeout(() => {
            document.body.style.transition = 'opacity 1s ease-in-out';
            document.body.style.opacity = '1';
        }, 100);
    });
</script>

</body>
</html>