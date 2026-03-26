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

// إضافة وكيل جديد (Customer → Agent)
if (isset($_POST['add_agent'])) {
    $user_id = intval($_POST['user_id']);
    $office  = $_POST['office'];

    $check = $conn->query("SELECT * FROM agents WHERE Agent_ID = $user_id");

    if ($check->num_rows == 0) {
        // حذف كل ما يتعلق بالكاستمر لضمان سلامة البيانات عند تحويل الرتبة
        $orders = $conn->query("SELECT Order_ID FROM orders WHERE Customer_ID = $user_id");

        while ($row = $orders->fetch_assoc()) {
            $oid = $row['Order_ID'];
            $conn->query("DELETE FROM order_items WHERE Order_ID = $oid");
            $conn->query("DELETE FROM orderstatushistory WHERE Order_ID = $oid");
            
            $pay_res = $conn->query("SELECT Payment_ID FROM payments WHERE Order_ID = $oid");
            while($p = $pay_res->fetch_assoc()) {
                $pid = $p['Payment_ID'];
                $conn->query("DELETE FROM card WHERE Payment_ID = $pid");
                $conn->query("DELETE FROM cash WHERE Payment_ID = $pid");
            }
            $conn->query("DELETE FROM payments WHERE Order_ID = $oid");
            $conn->query("DELETE FROM orders WHERE Order_ID = $oid");
        }

        $conn->query("DELETE FROM customers WHERE Customer_ID = $user_id");

        $stmt = $conn->prepare("INSERT INTO agents (Agent_ID, Office) VALUES (?, ?)");
        $stmt->bind_param("is", $user_id, $office);
        
        if ($stmt->execute()) {
            $message = "Agent added successfully! Customer data has been migrated.";
            $msg_type = "success";
        } else {
            $message = "Error adding agent: " . $conn->error;
            $msg_type = "error";
        }
    } else {
        $message = "This user is already an agent.";
        $msg_type = "error";
    }
}

// حذف وكيل وتحويله لزبون
if (isset($_POST['delete_agent'])) {
    $agent_id = intval($_POST['agent_id']);
    $conn->query("DELETE FROM orderstatushistory WHERE Agent_ID = $agent_id");

    $stmt = $conn->prepare("DELETE FROM agents WHERE Agent_ID=?");
    $stmt->bind_param("i", $agent_id);
    
    if($stmt->execute()){
        $conn->query("INSERT INTO customers (Customer_ID, points) VALUES ($agent_id, 0) ON DUPLICATE KEY UPDATE points = 0");
        $message = "Agent reverted to Customer status.";
        $msg_type = "success";
    }
}

// تعديل وكيل
if (isset($_POST['update_agent'])) {
    $agent_id = intval($_POST['agent_id']);
    $office = $_POST['office'];
    $stmt = $conn->prepare("UPDATE agents SET Office=? WHERE Agent_ID=?");
    $stmt->bind_param("si", $office, $agent_id);
    $stmt->execute();
    $message = "Agent office updated.";
    $msg_type = "success";
}

// جلب البيانات للعرض
$agents_res = $conn->query("
    SELECT a.Agent_ID, a.Office, u.First_Name, u.Last_Name
    FROM agents a
    JOIN users u ON a.Agent_ID = u.User_ID
");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Agents | Control Panel</title>
    <style>
        /* =========================================
           CORE THEME (Dark Glassmorphism)
           ========================================= */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #0f172a;
            color: #f8fafc;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 40px 20px;
            overflow-x: hidden;
        }

        .bg-image {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: url('https://images.unsplash.com/photo-1557804506-669a67965ba0?q=80&w=1974&auto=format&fit=crop') no-repeat center center;
            background-size: cover;
            filter: brightness(0.4);
            z-index: -2;
        }

        .bg-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: radial-gradient(circle at center, rgba(15, 23, 42, 0.4), #0f172a 90%);
            z-index: -1;
        }

        .glass-panel {
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 30px;
            width: 100%;
            max-width: 900px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.5);
            margin-bottom: 30px;
        }

        h2 { font-size: 2.2em; margin-bottom: 20px; color: #fff; text-align: center; }
        h2 span { color: #f59e0b; }
        h3 { color: #10b981; margin-bottom: 15px; font-size: 1.3em; border-left: 4px solid #10b981; padding-left: 10px; }

        /* =========================================
           FORMS & INPUTS
           ========================================= */
        label { display: block; font-size: 0.8em; color: #94a3b8; margin-bottom: 5px; text-transform: uppercase; }

        input, select {
            width: 100%;
            padding: 12px;
            background: rgba(30, 41, 59, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 10px;
            color: #fff;
            margin-bottom: 15px;
            transition: 0.3s;
        }

        input:focus { border-color: #f59e0b; outline: none; background: rgba(30, 41, 59, 1); }

        /* =========================================
           TABLE STYLING
           ========================================= */
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th { text-align: left; padding: 12px; color: #f59e0b; border-bottom: 2px solid rgba(245, 158, 11, 0.3); font-size: 0.9em; }
        td { padding: 12px; border-bottom: 1px solid rgba(255, 255, 255, 0.05); }

        /* =========================================
           BUTTONS
           ========================================= */
        .btn {
            padding: 10px 18px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: 0.3s;
        }
        .btn-add { background: #f59e0b; color: #0f172a; width: 100%; margin-top: 10px; }
        .btn-add:hover { background: #d97706; transform: translateY(-2px); }

        .btn-update { background: rgba(16, 185, 129, 0.2); color: #10b981; border: 1px solid #10b981; }
        .btn-update:hover { background: #10b981; color: #0f172a; }

        .btn-delete { background: rgba(239, 68, 68, 0.2); color: #ef4444; border: 1px solid #ef4444; }
        .btn-delete:hover { background: #ef4444; color: #fff; }

        .btn-back {
            text-decoration: none;
            color: #94a3b8;
            border: 1px solid #334155;
            padding: 12px 30px;
            border-radius: 30px;
            transition: 0.3s;
        }
        .btn-back:hover { background: #fff; color: #0f172a; }

        /* =========================================
           ALERTS
           ========================================= */
        .message {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
            width: 100%;
            max-width: 900px;
        }
        .success { background: rgba(16, 185, 129, 0.2); color: #10b981; border: 1px solid #10b981; }
        .error { background: rgba(239, 68, 68, 0.2); color: #ef4444; border: 1px solid #ef4444; }

        .fade-in { opacity: 0; animation: fadeIn 0.8s forwards; }
        @keyframes fadeIn { to { opacity: 1; } }
    </style>
</head>
<body>

<div class="bg-image"></div>
<div class="bg-overlay"></div>

<div class="glass-panel fade-in">
    <h2>Manage <span>Agents</span></h2>

    <?php if($message != ""): ?>
        <div class="message <?= $msg_type ?>"><?= $message ?></div>
    <?php endif; ?>

    <h3>Promote User to Agent</h3>
    <form method="POST">
        <label>Search Customer (Name or ID)</label>
        <input list="users_list" name="user_id" placeholder="Start typing name..." required>
        <datalist id="users_list">
            <?php 
            $cust_list = $conn->query("
                SELECT u.User_ID, u.First_Name, u.Last_Name 
                FROM users u 
                JOIN customers c ON u.User_ID = c.Customer_ID
            ");
            while($user = $cust_list->fetch_assoc()): ?>
                <option value="<?= $user['User_ID'] ?>"><?= $user['First_Name'] ?> <?= $user['Last_Name'] ?> (ID: <?= $user['User_ID'] ?>)</option>
            <?php endwhile; ?>
        </datalist>

        <label>Assign Office Number</label>
        <input type="text" name="office" placeholder="e.g. Office 204, Floor 2" required>

        <button type="submit" name="add_agent" class="btn btn-add">Promote to Agent</button>
    </form>
</div>

<div class="glass-panel fade-in" style="animation-delay: 0.2s;">
    <h3>Active Agents</h3>
    <div style="overflow-x: auto;">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Full Name</th>
                    <th>Office</th>
                    <th style="text-align: right;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($agent = $agents_res->fetch_assoc()): ?>
                <tr>
                    <form method="POST">
                        <td>#<?= $agent['Agent_ID'] ?></td>
                        <td><?= htmlspecialchars($agent['First_Name'] . " " . $agent['Last_Name']) ?></td>
                        <td>
                            <input type="text" name="office" value="<?= htmlspecialchars($agent['Office']) ?>" 
                                   style="margin-bottom:0; padding: 5px 10px; width: 150px;">
                        </td>
                        <td style="text-align: right;">
                            <input type="hidden" name="agent_id" value="<?= $agent['Agent_ID'] ?>">
                            <button type="submit" name="update_agent" class="btn btn-update">Save</button>
                            <button type="submit" name="delete_agent" class="btn btn-delete" 
                                    onclick="return confirm('Demote this agent back to Customer? All agent-specific records will be removed.')">Remove</button>
                        </td>
                    </form>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<a href="admin_home.php" class="btn-back">Back to Dashboard</a>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.body.style.opacity = '0';
        setTimeout(() => {
            document.body.style.transition = 'opacity 0.6s';
            document.body.style.opacity = '1';
        }, 50);
    });
</script>

</body>
</html>