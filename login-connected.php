<?php
// Redirect legacy path to canonical Apache page
header('Location: /clothing_project/login.php', true, 301);
exit;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>URBANWEAR - Login</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            min-height: 100vh;
            background: #ffeb3b;
            overflow-x: hidden;
        }

        /* Login Page Styles */
        .main-container {
            display: flex;
            min-height: 100vh;
        }

        .image-side {
            flex: 1.2;
            background: url('img/model.jpg') center/cover no-repeat; /* Apni image ka path daal de */
            position: relative;
            display: none;
        }

        .image-side::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(to rightrgba(255, 59, 59, 0), transparent 70%);
        }

        @media (min-width: 992px) {
            .image-side {
                display: block;
            }
        }

        .login-side {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            background: #ffffff;
        }

        .login-box {
            width: 100%;
            max-width: 420px;
            text-align: center;
        }

        .logo {
            font-size: 52px;
            font-weight: 900;
            letter-spacing: 5px;
            color: #000;
            margin-bottom: 12px;
            text-transform: uppercase;
        }

        .tagline {
            font-size: 22px;
            font-weight: 500;
            color: #222;
            margin-bottom: 50px;
        }

        .highlight {
            color: #ffeb3b;
            font-weight: 900;
            padding: 4px 12px;
            border-radius: 8px;
        }

        input {
            width: 100%;
            padding: 18px 24px;
            margin: 18px 0;
            border: 2px solid #ddd;
            border-radius: 14px;
            font-size: 17px;
            background: #f9f9f9;
            transition: all 0.3s ease;
        }

        input:focus {
            outline: none;
            border-color: #000;
            background: #fff;
            box-shadow: 0 0 0 5px rgba(0,0,0,0.12);
        }

        button {
            width: 100%;
            padding: 18px;
            margin: 25px 0 20px;
            background: #000;
            color: #fff;
            border: none;
            border-radius: 14px;
            font-size: 18px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.4s ease;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }

        button:hover {
            background: #222;
            transform: translateY(-3px);
            box-shadow: 0 12px 30px rgba(0,0,0,0.25);
        }

        .error {
            color: #e63946;
            font-size: 15px;
            margin: 15px 0;
            background: rgba(230,57,70,0.1);
            padding: 12px;
            border-radius: 12px;
        }

        .extra {
            font-size: 15px;
            color: #444;
            margin-top: 30px;
        }

        .extra a {
            color: #000;
            font-weight: 700;
            text-decoration: none;
        }

        .extra a:hover {
            color: #e63946;
            text-decoration: underline;
        }

        /* Dashboard Styles */
        .dashboard-container {
            padding: 60px 80px;
            max-width: 1400px;
            margin: 0 auto;
        }

        .welcome {
            font-size: 42px;
            font-weight: 900;
            margin-bottom: 10px;
        }

        .welcome span {
            color: #e63946;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 30px;
            margin: 50px 0;
        }

        .stat-card {
            background: #fff;
            border-radius: 20px;
            padding: 35px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.12);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-10px);
        }

        .stat-card i {
            font-size: 48px;
            color: #e63946;
            margin-bottom: 20px;
        }

        .stat-card h3 {
            font-size: 32px;
            margin: 10px 0;
        }

        .stat-card p {
            color: #555;
            font-size: 16px;
        }

        .section {
            background: #fff;
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 50px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.12);
        }

        .section h2 {
            font-size: 28px;
            margin-bottom: 25px;
            color: #000;
            border-bottom: 3px solid #ffeb3b;
            padding-bottom: 15px;
        }

        .logout-btn {
            background: #e63946;
            color: white;
            border: none;
            padding: 15px 40px;
            border-radius: 12px;
            font-size: 18px;
            font-weight: 700;
            cursor: pointer;
            margin-top: 40px;
            transition: all 0.3s;
        }

        .logout-btn:hover {
            background: #c72d3a;
            transform: translateY(-3px);
        }

        @media (max-width: 992px) {
            .main-container, .dashboard-container {
                padding: 30px 20px;
            }
            .welcome {
                font-size: 32px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2>🔐 URBANWEAR Login</h2>
        
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        
        <div class="connection-status">
            <?php if ($backend_connected): ?>
                ✅ Connected to Node.js Backend (<?php echo htmlspecialchars($API->getBaseUrl()); ?>)
            <?php else: ?>
                <span style="color:#721c24;">❌ Connection error: Failed to connect to backend (<?php echo htmlspecialchars($API->getBaseUrl()); ?>)</span>
            <?php endif; ?>
        </div>
        
        <form method="POST">
            <div class="form-group">
                <label for="email">Email Address</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    placeholder="Enter your email"
                    value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                    required
                >
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    placeholder="Enter your password"
                    required
                >
            </div>
            
            <button type="submit" class="btn">Login</button>
        </form>
        
        <div class="signup-link">
            Don't have an account? <a href="/clothing_project/signup.php">Sign up here</a>
        </div>
    </div>
</body>
</html>