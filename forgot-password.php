<?php
/**
 * FORGOT PASSWORD PAGE
 */
session_start();
require_once 'api-helper.php';

// If already logged in, redirect
if (isLoggedIn()) {
    header('Location: /clothing_project/user-dashboard.php');
    exit;
}

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    
    if (empty($email)) {
        $error = 'Email is required';
    } else {
        // Call backend API
        $response = $API->post('/api/v1/auth/forgot-password', [
            'email' => $email
        ]);

        if ($response['success']) {
            $success = 'If an account exists with this email, you will receive a reset link shortly.';
        } else {
            $error = $response['message'] ?? 'Something went wrong. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password | UrbanWear</title>
    <link rel="stylesheet" href="css/premium-auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

    <a href="login.php" class="home-link"><i class="fa-solid fa-arrow-left"></i> Back to Login</a>

    <div class="auth-container">
        <!-- Image Side -->
        <div class="auth-image-side">
            <img src="https://images.unsplash.com/photo-1490481651871-ab68de25d43d?q=80&w=1920&auto=format&fit=crop" alt="Fashion Wardrobe">
            <div class="auth-overlay">
                <h2>URBANWEAR</h2>
                <p>Secure your access to premium style.</p>
            </div>
        </div>

        <!-- Form Side -->
        <div class="auth-form-side">
            <div class="form-wrapper">
                <h1 class="brand-title">Reset Password</h1>
                <p class="brand-subtitle">Enter your email and we'll send you a link to reset your password.</p>

                <?php if ($error): ?>
                    <div class="error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="success"><?php echo htmlspecialchars($success); ?></div>
                <?php else: ?>
                    <form method="POST">
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required placeholder="Type your email">
                        </div>

                        <button type="submit" class="btn-auth">Send Reset Link</button>
                    </form>
                <?php endif; ?>

                <div class="auth-links">
                    <p>Remembered your password? <a href="/clothing_project/login.php">Sign In</a></p>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
