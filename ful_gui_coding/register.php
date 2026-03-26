<?php
// الكود PHP باقي زي ما هو (مش متأثر)
include "db_connect.php"; // اتصال الداتابيز

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $username = trim($_POST["username"]);
    $ssn = trim($_POST["ssn"]);
    $first = trim($_POST["first_name"]);
    $last = trim($_POST["last_name"]);
    $sex = $_POST["sex"];
    $email = trim($_POST["email"]);
    $type = strtolower(trim($_POST["user_type"])); // lowercase لتجنب مشاكل المقارنة
    $password_plain = $_POST["password"];
    $password_hashed = password_hash($password_plain, PASSWORD_DEFAULT);
    $register_date = date("Y-m-d");

    // التحقق من الـ username
    $stmt = $conn->prepare("SELECT * FROM users WHERE User_Name=?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        echo "<script>alert('Username already exists!');</script>";
    } else {
        // التحقق من الـ SSN
        $stmt_ssn = $conn->prepare("SELECT * FROM users WHERE SSN=?");
        $stmt_ssn->bind_param("s", $ssn);
        $stmt_ssn->execute();
        $result_ssn = $stmt_ssn->get_result();

        if ($result_ssn->num_rows > 0) {
            echo "<script>alert('SSN already exists! Please use a different one.');</script>";    
            exit(); // مهم جدًا عشان ما يكملش الإدخال
        } else {
            // لو admin: تحقق من كلمة السر
            if ($type === "admin") {
                if (!isset($_POST['admin_pass']) || $_POST['admin_pass'] !== 'ahmedsarhan') {
                    echo "<script>alert('Wrong admin password!');</script>";
                    exit();
                }
            }
        }
        // إدخال المستخدم في جدول users
        $stmt = $conn->prepare("INSERT INTO users (User_Name, SSN, First_Name, Last_Name, Sex, Register_Date, password, password_plain, Email) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssss", $username, $ssn, $first, $last, $sex, $register_date, $password_hashed, $password_plain, $email);
        
        if ($stmt->execute()) {
            $new_id = $conn->insert_id;

            if ($type === "customer") {
                $stmt2 = $conn->prepare("INSERT INTO customers (Customer_ID, points) VALUES (?, 0)");
                $stmt2->bind_param("i", $new_id);
                $stmt2->execute();
            } elseif ($type === "admin") {
                $stmt2 = $conn->prepare("INSERT INTO administrators (Admin_ID) VALUES (?)");
                $stmt2->bind_param("i", $new_id);
                $stmt2->execute();
            }

            echo "<script>alert('Account Created Successfully!'); window.location.href='login.php';</script>";
        } else {
            echo "<script>alert('Error: " . $conn->error . "');</script>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Join the Marketplace | Register</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        /* Global Styles & Reset */
        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Plus Jakarta Sans', sans-serif; }

        body {
            background-color: #020617;
            color: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow-x: hidden;
            padding: 40px 20px;
        }

        /* 3D Background Expressing Market */
        .bg-image {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: url('https://images.unsplash.com/photo-1542838132-92c53300491e?q=80&w=2074&auto=format&fit=crop') no-repeat center center;
            background-size: cover;
            filter: brightness(0.8); /* قللنا الـ blur وحسّنّا الـ brightness */
            z-index: -2;
            transform: scale(1.1);
        }

        .bg-overlay {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: radial-gradient(circle at center, rgba(2, 6, 23, 0.4) 0%, rgba(2, 6, 23, 0.9) 100%);
            z-index: -1;
        }

        /* Floating 3D elements */
        .float-icon {
            position: absolute;
            z-index: 0;
            opacity: 0.6;
            filter: drop-shadow(0 20px 20px rgba(0,0,0,0.8));
            animation: float 6s ease-in-out infinite;
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) rotate(0deg); }
            50% { transform: translateY(-40px) rotate(15deg); }
        }

        /* Registration Card */
        .glass-card {
            position: relative;
            z-index: 10;
            width: 100%;
            max-width: 850px; /* Wider for 2-column layout */
            background: rgba(15, 23, 42, 0.6);
            backdrop-filter: blur(25px);
            -webkit-backdrop-filter: blur(25px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 32px;
            padding: 40px;
            box-shadow: 0 40px 100px rgba(0,0,0,0.6);
        }

        .header { text-align: center; margin-bottom: 35px; }
        .header h2 { font-size: 32px; font-weight: 800; color: #fff; letter-spacing: -0.5px; }
        .header p { color: #94a3b8; margin-top: 8px; font-size: 15px; }

        /* Form Grid Layout */
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        @media (max-width: 768px) {
            .form-grid { grid-template-columns: 1fr; }
            .glass-card { padding: 30px 20px; }
        }

        .input-group { margin-bottom: 15px; text-align: left; }
        .input-group label { display: block; font-size: 13px; font-weight: 600; color: #10b981; margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px; }
        
        input, select {
            width: 100%;
            padding: 14px;
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: #fff;
            font-size: 15px;
            transition: all 0.3s ease;
        }

        input:focus, select:focus {
            outline: none;
            border-color: #10b981;
            background: rgba(255, 255, 255, 0.1);
            box-shadow: 0 0 15px rgba(16, 185, 129, 0.2);
        }

        /* Custom Dropdown Styling */
        select option { background: #0f172a; color: #fff; }

        /* Dynamic Sections */
        .dynamic-section {
            grid-column: 1 / -1; /* Spans full width */
            background: rgba(16, 185, 129, 0.05);
            border: 1px dashed rgba(16, 185, 129, 0.3);
            border-radius: 16px;
            padding: 20px;
            margin-top: 10px;
            animation: slideDown 0.4s ease-out;
        }

        @keyframes slideDown {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .dynamic-section h4 { color: #34d399; margin-bottom: 5px; font-size: 16px; }
        .dynamic-section p { font-size: 13px; color: #94a3b8; }

        /* Submit Button */
        .btn-container { grid-column: 1 / -1; margin-top: 20px; }
        
        .submit-btn {
            width: 100%;
            padding: 18px;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #020617;
            border: none;
            border-radius: 14px;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .submit-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 30px rgba(16, 185, 129, 0.4);
            filter: brightness(1.1);
        }

        .footer-text { grid-column: 1 / -1; text-align: center; margin-top: 25px; font-size: 14px; color: #64748b; }
        .footer-text a { color: #10b981; text-decoration: none; font-weight: 600; }
    </style>

    <script>
        function changeForm() {
            let type = document.getElementById("user_type").value;
            let custField = document.getElementById("customer_fields");
            let adminField = document.getElementById("admin_fields");

            custField.style.display = (type === "customer") ? "block" : "none";
            adminField.style.display = (type === "admin") ? "block" : "none";
        }

        function validateForm() {
            let username = document.querySelector('input[name="username"]').value.trim();
            let password = document.querySelector('input[name="password"]').value;
            let email = document.querySelector('input[name="email"]').value;

            if (username.length < 3) {
                alert('Username must be at least 3 characters!');
                return false;
            }
            if (password.length < 6) {
                alert('Password must be at least 6 characters!');
                return false;
            }
            if (!email.includes('@')) {
                alert('Please enter a valid email!');
                return false;
            }
            return true;
        }
    </script>
</head>
<body>

    <div class="bg-image"></div>
    <div class="bg-overlay"></div>

    <div class="float-icon" style="top: 10%; left: 5%; font-size: 60px;">📦</div>
    <div class="float-icon" style="bottom: 10%; right: 5%; font-size: 80px; animation-delay: 2s;">🛍️</div>

    <div class="glass-card">
        <div class="header">
            <h2>Create Account</h2>
            <p>Join our premium marketplace community today</p>
        </div>

        <form action="" method="POST" autocomplete="off" onsubmit="return validateForm()">
            <div class="form-grid">
                
                <div class="input-group">
                    <label>Username</label>
                    <input type="text" name="username" placeholder="johndoe123" required>
                </div>

                <div class="input-group">
                    <label>SSN (National ID)</label>
                    <input type="number" name="ssn" placeholder="Enter SSN" required>
                </div>

                <div class="input-group">
                    <label>First Name</label>
                    <input type="text" name="first_name" placeholder="John" required>
                </div>

                <div class="input-group">
                    <label>Last Name</label>
                    <input type="text" name="last_name" placeholder="Doe" required>
                </div>

                <div class="input-group">
                    <label>Sex</label>
                    <select name="sex" required>
                        <option value="">Select Sex</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div class="input-group">
                    <label>Email Address</label>
                    <input type="email" name="email" placeholder="name@company.com" required>
                </div>

                <div class="input-group">
                    <label>Password</label>
                    <input type="password" name="password" placeholder="Min. 6 characters" required>
                </div>

                <div class="input-group">
                    <label>Account Type</label>
                    <select id="user_type" name="user_type" onchange="changeForm()" required>
                        <option value="">Select Type</option>
                        <option value="customer">Customer</option>
                        <option value="admin">Administrator</option>
                    </select>
                </div>

                <div id="customer_fields" class="dynamic-section" style="display:none;">
                    <h4>Customer Benefits</h4>
                    <p>Welcome! You'll start with 0 loyalty points. Earn points with every purchase you make.</p>
                </div>

                <div id="admin_fields" class="dynamic-section" style="display:none;">
                    <h4>Administrative Access</h4>
                    <p>Please enter the Master Security Key to proceed with admin creation:</p>
                    <input type="password" name="admin_pass" placeholder="Master Security Key" style="margin-top:10px; background: rgba(0,0,0,0.3);">
                </div>

                <div class="btn-container">
                    <button type="submit" class="submit-btn">Create Premium Account</button>
                </div>

                <div class="footer-text">
                    <p>Already have an account? <a href="login.php">Login here</a></p>
                    <p style="margin-top:15px;"><a href="index.php" style="color: #64748b;">← Return to Home</a></p>
                </div>

            </div>
        </form>
    </div>

</body>
</html>