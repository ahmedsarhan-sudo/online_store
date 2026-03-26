<?php
session_start();
include "db_connect.php";

// --- Authentication Check ---
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_type'])) {
    header("Location: index.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];
$message = "";

// --- Fetch User Data ---
$user = $conn->query("SELECT * FROM users WHERE User_ID=$user_id")->fetch_assoc();

if ($user_type == "customer") {
    $extra = $conn->query("SELECT * FROM customers WHERE Customer_ID=$user_id")->fetch_assoc();
} elseif ($user_type == "agent") {
    $extra = $conn->query("SELECT * FROM agents WHERE Agent_ID=$user_id")->fetch_assoc();
} elseif ($user_type == "admin") {
    $extra = $conn->query("SELECT * FROM administrators WHERE Admin_ID=$user_id")->fetch_assoc();
}

// --- Update Handling ---
if (isset($_POST["update"])) {
    $u_name   = $_POST["username"];
    $ssn      = $_POST["ssn"];
    $first    = $_POST["first_name"];
    $last     = $_POST["last_name"];
    $sex      = $_POST["sex"];
    $email    = $_POST["email"];
    $password_plain = $_POST["password"];
    $password = password_hash($password_plain, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("UPDATE users SET User_Name=?, SSN=?, First_Name=?, Last_Name=?, Sex=?, Email=?, password_plain=?, password=? WHERE User_ID=?");
    $stmt->bind_param("ssssssssi", $u_name, $ssn, $first, $last, $sex, $email, $password_plain, $password, $user_id);
    
    if($stmt->execute()) {
        if ($user_type == "agent" && isset($_POST["office"])) {
            $office = $_POST["office"];
            $stmt2 = $conn->prepare("UPDATE agents SET Office=? WHERE Agent_ID=?");
            $stmt2->bind_param("ii", $office, $user_id);
            $stmt2->execute();
        }
        $message = "Successfully Updated!";
        header("Refresh:1");
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Info | Customer System</title>
    <style>
        /* =========================================
           1. CORE TYPOGRAPHY & RESET (MATCHED)
           ========================================= */
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            overflow-y: auto;
            position: relative;
        }

        /* =========================================
           2. 3D DEPTH PERCEPTION (MATCHED)
           ========================================= */
        .bg-image {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: url('https://images.pexels.com/photos/1759484/pexels-photo-1759484.jpeg') no-repeat center center;
            background-size: cover;
            filter: brightness(0.4);
            z-index: -1;
            transform: scale(1.1);
        }

        .bg-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: radial-gradient(circle at center, rgba(15, 23, 42, 0.5), #0f172a 90%);
            z-index: -1;
        }

        .glass-panel {
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 40px;
            width: 100%;
            max-width: 600px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            z-index: 10;
        }

        /* =========================================
           3. EMERALD & SLATE THEME
           ========================================= */
        h1 {
            color: #10b981;
            font-size: 2em;
            margin-bottom: 10px;
            font-weight: 600;
            text-align: center;
        }

        p.subtitle {
            color: #94a3b8;
            margin-bottom: 30px;
            font-size: 1.1em;
            text-align: center;
        }

        /* =========================================
           4. FORM & INPUT STYLING
           ========================================= */
        .form-group {
            margin-bottom: 20px;
            text-align: left;
        }

        label {
            display: block;
            color: #94a3b8;
            font-size: 0.9em;
            margin-bottom: 8px;
            margin-left: 5px;
        }

        input, select {
            width: 100%;
            padding: 14px;
            background: rgba(15, 23, 42, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: #fff;
            font-size: 1em;
            transition: all 0.3s ease;
        }

        input:focus {
            outline: none;
            border-color: #10b981;
            background: rgba(15, 23, 42, 0.7);
        }

        .row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        /* =========================================
           5. BUTTONS (MATCHED TO DASHBOARD)
           ========================================= */
        .btn-primary {
            width: 100%;
            padding: 18px;
            background: rgba(16, 185, 129, 0.05);
            color: #10b981;
            border: 1px solid rgba(16, 185, 129, 0.3);
            border-radius: 16px;
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .btn-primary:hover {
            background: #10b981;
            color: #0f172a;
            transform: translateY(-4px);
            box-shadow: 0 10px 20px -5px rgba(16, 185, 129, 0.4);
        }

        .btn-secondary {
            display: block;
            width: 100%;
            text-align: center;
            text-decoration: none;
            padding: 14px;
            background: rgba(255, 255, 255, 0.05);
            color: #e2e8f0;
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 16px;
            font-size: 1em;
            margin-top: 15px;
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            background: #e2e8f0;
            color: #0f172a;
            box-shadow: 0 10px 20px -5px rgba(255, 255, 255, 0.2);
        }

        .alert {
            background: rgba(16, 185, 129, 0.2);
            color: #10b981;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 600;
        }
    </style>
</head>
<body>

<div class="bg-image"></div>
<div class="bg-overlay"></div>

<div class="glass-panel">
    <h1>Account Settings</h1>
    <p class="subtitle">Modify your personal profile details</p>

    <?php if ($message): ?>
        <div class="alert"><?= $message ?></div>
    <?php endif; ?>

    <form method="POST" onsubmit="return validateForm()">
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" value="<?= htmlspecialchars($user['User_Name']) ?>" required>
        </div>

        <div class="row">
            <div class="form-group">
                <label>First Name</label>
                <input type="text" name="first_name" value="<?= htmlspecialchars($user['First_Name']) ?>" required>
            </div>
            <div class="form-group">
                <label>Last Name</label>
                <input type="text" name="last_name" value="<?= htmlspecialchars($user['Last_Name']) ?>" required>
            </div>
        </div>

        <div class="form-group">
            <label>Email Address</label>
            <input type="email" name="email" value="<?= htmlspecialchars($user['Email']) ?>" required>
        </div>

        <div class="row">
            <div class="form-group">
                <label>SSN</label>
                <input type="text" name="ssn" value="<?= htmlspecialchars($user['SSN']) ?>" required>
            </div>
            <div class="form-group">
                <label>Sex</label>
                <select name="sex">
                    <option value="Male" <?= ($user['Sex']=="Male")?"selected":"" ?>>Male</option>
                    <option value="Female" <?= ($user['Sex']=="Female")?"selected":"" ?>>Female</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label>Current Password</label>
            <input type="text" name="password" value="<?= htmlspecialchars($user['password_plain']) ?>" required>
        </div>

        <?php if ($user_type == "agent"): ?>
            <div class="form-group">
                <label>Office ID</label>
                <input type="number" name="office" value="<?= $extra['Office'] ?>">
            </div>
        <?php elseif ($user_type == "customer"): ?>
            <div class="form-group" style="background: rgba(16,185,129,0.05); padding: 15px; border-radius: 12px; border: 1px dashed rgba(16,185,129,0.3);">
                <p style="color: #10b981; font-size: 0.9em;">Loyalty Points: <strong><?= $extra['points'] ?></strong></p>
            </div>
        <?php endif; ?>

        <button type="submit" name="update" class="btn-primary">💾 Save My Changes</button>
    </form>

    <a href="customer_home.php" class="btn-secondary">⬅️ Return to Dashboard</a>
</div>

<script>
    function validateForm() {
        let pass = document.querySelector('input[name="password"]').value;
        if (pass.length < 6) {
            alert('Password must be at least 6 characters!');
            return false;
        }
        return true;
    }

    document.addEventListener('DOMContentLoaded', function() {
        document.body.style.opacity = '0';
        setTimeout(() => {
            document.body.style.transition = 'opacity 0.8s ease-in-out';
            document.body.style.opacity = '1';
        }, 50);
    });
</script>

</body>
</html>