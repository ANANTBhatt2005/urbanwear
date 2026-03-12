<?php
/**
 * RESET PASSWORD PAGE
 */
session_start();
require_once 'api-helper.php';

// If already logged in, redirect
if (isLoggedIn()) {
    header('Location: /clothing_project/user-dashboard.php');
    exit;
}

$token = $_GET['token'] ?? '';
if (!$token) {
    header('Location: /clothing_project/login.php');
    exit;
}

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($password)) {
        $error = 'Password is required';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } else {
        // Call backend API
        $response = $API->post("/api/v1/auth/reset-password", [
            'token' => $token,
            'password' => $password
        ]);

        if ($response['success']) {
            $success = 'Your password has been reset successfully. You can now log in with your new password.';
        } else {
            $error = $response['message'] ?? 'Invalid or expired token. Please request a new reset link.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password | UrbanWear</title>
    <link rel="stylesheet" href="css/premium-auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

    <a href="login.php" class="home-link"><i class="fa-solid fa-arrow-left"></i> Back to Login</a>

    <div class="auth-container">
        <!-- Image Side -->
        <div class="auth-image-side">
            <img src="https://images.unsplash.com/photo-1441986300917-64674bd600d8?q=80&w=1920&auto=format&fit=crop" alt="Fashion Store">
            <div class="auth-overlay">
                <h2>URBANWEAR</h2>
                <p>Welcome back to the world of elegance.</p>
            </div>
        </div>

        <!-- Form Side -->
        <div class="auth-form-side">
            <div class="form-wrapper">
                <h1 class="brand-title">New Password</h1>
                <p class="brand-subtitle">Set a strong password to protect your account.</p>

                <?php if ($error): ?>
                    <div class="error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="success">
                        <?php echo htmlspecialchars($success); ?><br><br>
                        <a href="/clothing_project/login.php" class="btn-auth" style="text-align: center; text-decoration: none; display: block;">Go to Sign In</a>
                    </div>
                <?php else: ?>
                    <form method="POST">
                        <div class="form-group">
                            <label for="password">New Password</label>
                            <input type="password" id="password" name="password" required placeholder="Min 6 characters">
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" required placeholder="Repeat your new password">
                        </div>

                        <button type="submit" class="btn-auth">Update Password</button>
                    </form>
                <?php endif; ?>

                <div class="auth-links">
                    <p>Issues resetting? <a href="/clothing_project/forgot-password.php">Request again</a></p>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
