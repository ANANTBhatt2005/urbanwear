<?php
/**
 * LOGIN PAGE - Apache frontend entry
 * Merged logic from login-connected.php
 */

session_start();
require_once 'api-helper.php';

// If already logged in, redirect
if (isLoggedIn()) {
    if (isAdmin()) {
        header('Location: /clothing_project/admin-dashboard.php');
    } else {
        header('Location: /clothing_project/user-dashboard.php');
    }
    exit;
}

$error = '';
$success = '';

// Health check for backend
$health = $API->healthCheck();
$backend_connected = $health['success'] ?? false;
$backend_message = $health['message'] ?? ($health['data']['message'] ?? null);

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Email and password are required';
    } else {
        // Call backend API at v1 endpoint
        $response = $API->post('/api/v1/auth/login', [
            'email' => $email,
            'password' => $password
        ]);

        if ($response['success']) {
            $token = $response['data']['token'] ?? null;
            $user = $response['data']['user'] ?? null;

            error_log('Login Response: ' . json_encode($response));

            if ($user && !isset($user['id']) && isset($user['_id'])) {
                $user['id'] = $user['_id'];
            }

            if ($token && $user) {
                storeAuthToken($token, $user);

                // Role-based redirect (admin -> admin-dashboard, else user-dashboard)
                $role = strtolower($user['role'] ?? 'user');
                if ($role === 'admin') {
                    $_SESSION['admin_logged_in'] = true;
                    header('Location: /clothing_project/admin-dashboard.php');
                    exit;
                } else {
                    header('Location: /clothing_project/user-dashboard.php');
                    exit;
                }
            } else {
                $debug_info = 'Token: ' . ($token ? 'yes' : 'no') . ', User: ' . ($user ? 'yes' : 'no');
                error_log('Login extraction failed: ' . $debug_info . ' Raw: ' . json_encode($response));
                $error = 'Invalid response from server: missing token or user data. ' . $debug_info;
            }
        } else {
            error_log('Login failed - success=false: ' . ($response['message'] ?? json_encode($response)));
            $error = $response['message'] ?? 'Login failed. Please check your email and password.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | UrbanWear</title>
    <link rel="stylesheet" href="css/premium-auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

    <a href="index.php" class="home-link"><i class="fa-solid fa-arrow-left"></i> Home</a>

    <div class="auth-container">
        <!-- Image Side -->
        <div class="auth-image-side">
            <img src="https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?q=80&w=1920&auto=format&fit=crop" alt="Fashion Model">
            <div class="auth-overlay">
                <h2>URBANWEAR</h2>
                <p>Welcome back to the new standard.</p>
            </div>
        </div>

        <!-- Form Side -->
        <div class="auth-form-side">
            <div class="form-wrapper">
                <h1 class="brand-title">Sign In</h1>
                <p class="brand-subtitle">Access your personal wardrobe.</p>

                <?php if ($error): ?>
                    <div class="error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <?php if ($success): ?>
                    <div class="success"><?php echo htmlspecialchars($success); ?></div>
                <?php endif; ?>

                <div class="connection-status">
                     <?php if ($backend_connected): ?>
                        <small>● System Operational</small>
                    <?php else: ?>
                        <small style="color:red">● System Unreachable</small>
                    <?php endif; ?>
                </div>

                <form method="POST">
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required placeholder="Type your email">
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" id="password" name="password" required placeholder="Type your password">
                    </div>

                    <button type="submit" class="btn-auth">Sign In</button>
                </form>

                <div class="auth-links">
                    <p>New here? <a href="/clothing_project/signup.php">Create an Account</a></p>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
