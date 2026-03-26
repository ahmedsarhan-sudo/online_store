<?php
session_start();
include "db_connect.php";

// --- Authentication & Admin Check ---
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: index.php");
    exit();
}

$admin_id = $_SESSION['user_id'];

// Fetch Admin Name
$user = $conn->query("SELECT First_Name, Last_Name FROM users WHERE User_ID=$admin_id")->fetch_assoc();
$first = $user['First_Name'];
$last = $user['Last_Name'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrator Dashboard | Our Market</title>
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
            padding: 40px 20px;
            position: relative;
            background-color: #0f172a;
        }

        /* =========================================
           2. 3D BACKGROUND (THE GREEN THEME)
           ========================================= */
        /* .bg-image {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: url('https://images.unsplash.com/photo-1655522060985-6769176edff7?q=80&w=1074&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D') no-repeat center center;
            background-size: cover;
            filter: brightness(0.6);
            z-index: -2;
            transform: scale(1.05);
        } */

        .bg-image {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: url('https://images.unsplash.com/photo-1655522060985-6769176edff7?q=80&w=1074&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D') no-repeat center center;
            background-size: cover;
            filter: brightness(0.8);
            opacity: 0.6;
            transform: scale(1.05);
            z-index: -1;
        }

        .bg-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: radial-gradient(circle at center, rgba(15, 23, 42, 0.4), #0f172a 95%);
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
            max-width: 500px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6);
            z-index: 10;
            text-align: center;
        }

        h1 { 
            color: #f8fafc; 
            font-size: 2.2em; 
            margin-bottom: 5px; 
            font-weight: 700;
        }
        
        h1 span { color: #10b981; }

        p.role-badge {
            display: inline-block;
            background: rgba(16, 185, 129, 0.2);
            color: #10b981;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 35px;
            border: 1px solid rgba(16, 185, 129, 0.3);
        }

        /* =========================================
           4. ADMIN BUTTONS (GREEN FRAME STYLE)
           ========================================= */
        .btn-stack {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .admin-btn {
            width: 100%;
            padding: 16px;
            background: rgba(255, 255, 255, 0.03);
            color: #e2e8f0;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 14px;
            font-size: 1.05em;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.3s ease;
            display: block;
            text-align: center;
        }

        .admin-btn:hover {
            background: rgba(16, 185, 129, 0.1);
            border-color: #10b981;
            color: #10b981;
            transform: translateX(10px);
        }

        /* Special Logout/Exit Button */
        .btn-exit {
            margin-top: 30px;
            padding: 12px;
            background: transparent;
            color: #94a3b8;
            border: 1px dashed rgba(148, 163, 184, 0.3);
            border-radius: 12px;
            font-size: 0.9em;
            text-decoration: none;
            display: block;
            transition: 0.3s;
        }

        .btn-exit:hover {
            color: #ef4444;
            border-color: #ef4444;
            background: rgba(239, 68, 68, 0.05);
        }

        /* Smooth reveal animation */
        .reveal {
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.8s ease-out;
        }

        .reveal.active {
            opacity: 1;
            transform: translateY(0);
        }
    </style>
</head>
<body>

<div class="bg-image"></div>
<div class="bg-overlay"></div>

<div class="glass-panel reveal" id="dashboard">
    <h1>Hello, <span><?= $first ?></span></h1>
    <p class="role-badge">System Administrator</p>

    <div class="btn-stack">
        <a href="view_all_customers.php" class="admin-btn">👥 View All Customers</a>
        <a href="view_all_orders.php" class="admin-btn">📦 View All Orders</a>
        <a href="manage_categories.php" class="admin-btn">📂 Manage Categories</a>
        <a href="manage_products.php" class="admin-btn">🛒 Manage Products</a>
        <a href="manage_agents.php" class="admin-btn">👔 Manage Agents</a>
        <a href="update_info.php" class="admin-btn">⚙️ My Profile Settings</a>
    </div>

    <a href="index.php" class="btn-exit">Log out of Admin Panel</a>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const dashboard = document.getElementById('dashboard');
        setTimeout(() => {
            dashboard.classList.add('active');
        }, 150);
    });
</script>

</body>
</html>