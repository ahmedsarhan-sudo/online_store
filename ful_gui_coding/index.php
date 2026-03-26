<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Our Market</title>
    <style>
        /* =========================================
           1. CORE RESET & TYPOGRAPHY
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
            position: relative;
            overflow-y: auto;
        }

        /* =========================================
           2. 3D BACKGROUND
           ========================================= */
        .bg-image {
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            /* Kept your new background image but styled it to match the theme */
            background: url('https://images.unsplash.com/photo-1578916171728-46686eac8d58?q=80&w=1920&auto=format&fit=crop') no-repeat center center;
            background-size: cover;
            filter: brightness(0.6);
            z-index: -2;
            transform: scale(1.05);
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
            background: rgba(15, 23, 42, 0.65);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 60px 40px;
            width: 100%;
            max-width: 800px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.6);
            z-index: 10;
            text-align: center;
        }

        h1 { 
            color: #f8fafc; 
            font-size: 3.5em; 
            margin-bottom: 20px; 
            font-weight: 800; 
            letter-spacing: -1px;
        }
        
        h1 span {
            color: #10b981; /* Emerald Green Text */
        }

        p.subtitle { 
            color: #94a3b8; 
            margin-bottom: 40px; 
            font-size: 1.2em;
            line-height: 1.6;
        }

        /* =========================================
           4. BUTTONS (GREEN FRAME STYLE)
           ========================================= */
        .button-container {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-block;
            padding: 18px 35px;
            border-radius: 50px; /* Fully rounded like your Tailwind version */
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        /* Emerald Primary Button */
        .btn-green {
            background: rgba(16, 185, 129, 0.05);
            color: #10b981;
            border: 1px solid rgba(16, 185, 129, 0.4);
        }

        .btn-green:hover {
            background: #10b981;
            color: #0f172a;
            transform: translateY(-4px);
            box-shadow: 0 10px 20px -5px rgba(16, 185, 129, 0.4);
        }

        /* Secondary Outline Button */
        .btn-secondary {
            background: rgba(255, 255, 255, 0.05);
            color: #e2e8f0;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .btn-secondary:hover {
            background: #e2e8f0;
            color: #0f172a;
            border-color: #e2e8f0;
            transform: translateY(-4px);
            box-shadow: 0 10px 20px -5px rgba(255, 255, 255, 0.2);
        }
    </style>
</head>
<body>

<div class="bg-image"></div>
<div class="bg-overlay"></div>

<div class="glass-panel">
    <h1>Welcome to <span>Our Market</span></h1>
    <p class="subtitle">Premium quality, reliable service. Log in to continue your shopping experience.</p>
    
    <div class="button-container">
        <a href="login.php" id="login-link" class="btn btn-green">Login to Account</a>
        <a href="register.php" id="register-link" class="btn btn-secondary">Create an Account</a>
    </div>
</div>

<script>
    // Smooth Fade-in effect matching the internal pages
    document.addEventListener('DOMContentLoaded', function() {
        document.body.style.opacity = '0';
        setTimeout(() => {
            document.body.style.transition = 'opacity 0.8s ease-in-out';
            document.body.style.opacity = '1';
        }, 100);
    });
    
    // Button interaction logic
    document.getElementById('login-link').addEventListener('click', function(event) {
        // Remove event.preventDefault() so the link actually navigates to login.php
        alert('Redirecting to Login page... Enjoy your shopping!');
    });
    
    document.getElementById('register-link').addEventListener('click', function(event) {
        alert('Redirecting to Register page... Welcome aboard!');
    });
</script>

</body>
</html>