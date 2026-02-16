<?php
// Redirect legacy path to canonical Apache page
header('Location: /clothing_project/signup.php', true, 301);
exit;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>URBANWEAR - Sign Up</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .signup-container {
            max-width: 450px;
            margin: 50px auto;
            padding: 30px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background: #f9f9f9;
        }
        .signup-container h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #333;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #555;
        }
        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            box-sizing: border-box;
        }
        .form-group input:focus {
            outline: none;
            border-color: #28a745;
            box-shadow: 0 0 5px rgba(40, 167, 69, 0.5);
        }
        .btn {
            width: 100%;
            padding: 12px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #218838;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 12px;
            border-radius: 4px;
            margin-bottom: 20px;
            border: 1px solid #c3e6cb;
        }
        .login-link {
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
        }
        .login-link a {
            color: #28a745;
            text-decoration: none;
        }
        .login-link a:hover {
            text-decoration: underline;
        }
        .password-requirements {
            background: #f0f0f0;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 12px;
            color: #666;
        }
        .password-requirements ul {
            margin: 0;
            padding-left: 20px;
        }
        .password-requirements li {
            margin: 5px 0;
        }
        .connection-status {
            text-align: center;
            padding: 10px;
            background: #e7f3ff;
            border-radius: 4px;
            margin-bottom: 20px;
            font-size: 12px;
            color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="signup-container">
        <h2>✨ Create URBANWEAR Account</h2>
        
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
                <label for="name">Full Name</label>
                <input 
                    type="text" 
                    id="name" 
                    name="name" 
                    placeholder="Enter your full name"
                    value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
                    required
                >
            </div>
            
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
                    placeholder="Enter a strong password"
                    required
                >
            </div>
            
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input 
                    type="password" 
                    id="confirm_password" 
                    name="confirm_password" 
                    placeholder="Re-enter your password"
                    required
                >
            </div>
            
            <div class="password-requirements">
                <strong>Password Requirements:</strong>
                <ul>
                    <li>Minimum 6 characters</li>
                    <li>Mix of letters, numbers, and symbols recommended</li>
                </ul>
            </div>
            
            <button type="submit" class="btn">Create Account</button>
        </form>
        
        <div class="login-link">
            Already have an account? <a href="/clothing_project/login.php">Login here</a>
        </div>
    </div>
</body>
</html>
