<?php
session_start();
include "db_connect.php";

// --- Authentication & Admin Check ---
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'admin') {
    header("Location: index.php");
    exit();
}

if (!isset($_GET['user_id'])) {
    header("Location: view_all_customers.php");
    exit();
}

$customer_id = intval($_GET['user_id']);
$message = "";
$error = "";

// --- Handle Update Logic ---
if (isset($_POST['update'])) {
    $first = $_POST['first_name'];
    $last = $_POST['last_name'];
    $email = $_POST['email'];
    $sex = $_POST['sex'];
    $points = intval($_POST['points']);
    $new_pass_plain = trim($_POST['password_plain']);
    $new_pass_confirm = trim($_POST['password_confirm']);

    // Update main user data
    $stmt = $conn->prepare("UPDATE users SET First_Name=?, Last_Name=?, Email=?, Sex=? WHERE User_ID=?");
    $stmt->bind_param("ssssi", $first, $last, $email, $sex, $customer_id);
    $stmt->execute();

    // Update customer-specific points
    $stmt2 = $conn->prepare("UPDATE customers SET points=? WHERE Customer_ID=?");
    $stmt2->bind_param("ii", $points, $customer_id);
    $stmt2->execute();

    if ($new_pass_plain !== "") {
        if ($new_pass_plain === $new_pass_confirm) {
            $hashed = password_hash($new_pass_plain, PASSWORD_DEFAULT);
            $stmt3 = $conn->prepare("UPDATE users SET Password=?, Password_plain=? WHERE User_ID=?");
            $stmt3->bind_param("ssi", $hashed, $new_pass_plain, $customer_id);
            $stmt3->execute();
            $message = "Account information and security updated successfully!";
        } else {
            $error = "Password confirmation does not match!";
        }
    } else {
        $message = "Customer information updated successfully!";
    }
}

// Fetch current data for the form
$user = $conn->query("SELECT u.User_ID, u.First_Name, u.Last_Name, u.Email, u.Sex, c.points
                      FROM users u
                      JOIN customers c ON u.User_ID = c.Customer_ID
                      WHERE u.User_ID=$customer_id")->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Account | Admin Access</title>
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
            flex-direction: column;
            align-items: center;
            padding: 40px 20px;
            background-color: #0f172a;
        }

        .bg-image {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: url('https://images.unsplash.com/photo-1601600576337-c1d8a0d1373c?q=80&w=687&auto=format&fit=crop&ixlib=rb-4.1.0&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D') no-repeat center center;
            background-size: cover;
            filter: brightness(0.7);
            z-index: -2;
            transform: scale(1.05);
        }

        .bg-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: radial-gradient(circle at center, rgba(15, 23, 42, 0.5), #0f172a 95%);
            z-index: -1;
        }

        /* =========================================
           2. THE GLASS FORM
           ========================================= */
        .glass-panel {
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 40px;
            width: 100%;
            max-width: 600px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6);
            margin-bottom: 30px;
        }

        h2 { font-size: 2em; margin-bottom: 10px; text-align: center; }
        h2 span { color: #10b981; }
        
        h3 { 
            color: #10b981; 
            font-size: 1.1em; 
            margin: 30px 0 15px; 
            text-transform: uppercase; 
            letter-spacing: 1px;
            border-bottom: 1px solid rgba(16, 185, 129, 0.2);
            padding-bottom: 10px;
        }

        .alert {
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-weight: 600;
            text-align: center;
            border: 1px solid;
        }
        .success { background: rgba(16, 185, 129, 0.1); color: #10b981; border-color: rgba(16, 185, 129, 0.3); }
        .error { background: rgba(239, 68, 68, 0.1); color: #ef4444; border-color: rgba(239, 68, 68, 0.3); }

        /* =========================================
           3. INPUTS & BUTTONS
           ========================================= */
        .input-group { margin-bottom: 20px; text-align: left; }
        
        label {
            display: block;
            margin-bottom: 8px;
            color: #94a3b8;
            font-size: 0.9em;
            font-weight: 600;
        }

        input, select {
            width: 100%;
            padding: 14px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: #fff;
            font-size: 1em;
            transition: 0.3s;
        }

        input:focus, select:focus {
            outline: none;
            border-color: #10b981;
            background: rgba(16, 185, 129, 0.05);
            box-shadow: 0 0 15px rgba(16, 185, 129, 0.2);
        }

        .btn-submit {
            width: 100%;
            padding: 16px;
            background: #10b981;
            color: #0f172a;
            border: none;
            border-radius: 12px;
            font-size: 1.1em;
            font-weight: 700;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 10px;
        }

        .btn-submit:hover {
            background: #34d399;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -5px rgba(16, 185, 129, 0.4);
        }

        .btn-back {
            background: transparent;
            color: #94a3b8;
            border: 1px solid rgba(148, 163, 184, 0.3);
            padding: 12px 30px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: 0.3s;
        }

        .btn-back:hover { color: #fff; border-color: #fff; }

        .fade-in { opacity: 0; animation: fadeIn 0.8s forwards; }
        @keyframes fadeIn { to { opacity: 1; } }
    </style>
</head>
<body>

<div class="bg-image"></div>
<div class="bg-overlay"></div>

<div class="glass-panel fade-in">
    <h2>Edit Customer <span>#<?= $user['User_ID'] ?></span></h2>
    
    <?php if ($message): ?>
        <div class="alert success"><?= $message ?></div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert error"><?= $error ?></div>
    <?php endif; ?>

    <form method="POST">
        <div style="display: flex; gap: 15px;">
            <div class="input-group" style="flex: 1;">
                <label>First Name</label>
                <input type="text" name="first_name" value="<?= $user['First_Name'] ?>" required>
            </div>
            <div class="input-group" style="flex: 1;">
                <label>Last Name</label>
                <input type="text" name="last_name" value="<?= $user['Last_Name'] ?>" required>
            </div>
        </div>

        <div class="input-group">
            <label>Email Address</label>
            <input type="email" name="email" value="<?= $user['Email'] ?>" required>
        </div>

        <div style="display: flex; gap: 15px;">
            <div class="input-group" style="flex: 1;">
                <label>Gender</label>
                <select name="sex">
                    <option value="Male" <?= $user['Sex'] == 'Male' ? 'selected' : '' ?>>Male</option>
                    <option value="Female" <?= $user['Sex'] == 'Female' ? 'selected' : '' ?>>Female</option>
                    <option value="Other" <?= $user['Sex'] == 'Other' ? 'selected' : '' ?>>Other</option>
                </select>
            </div>
            <div class="input-group" style="flex: 1;">
                <label>Reward Points</label>
                <input type="number" name="points" value="<?= $user['points'] ?>" min="0">
            </div>
        </div>

        <h3>Security & Credentials</h3>

        <div class="input-group">
            <label>New Password (Leave blank to keep current)</label>
            <input type="password" name="password_plain" placeholder="••••••••">
        </div>

        <div class="input-group">
            <label>Confirm New Password</label>
            <input type="password" name="password_confirm" placeholder="••••••••">
        </div>

        <button type="submit" name="update" class="btn-submit">Save Changes</button>
    </form>
</div>

<a href="view_all_customers.php" class="btn-back">Back to Customers List</a>

</body>
</html>