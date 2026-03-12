<?php
/**
 * SIGNUP PAGE - Apache frontend entry
 * Merged logic from signup-connected.php
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

// Handle signup form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = 'All fields are required';
    } elseif ($password !== $confirm_password) {
        $error = 'Passwords do not match';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email format';
    } else {
        // Call backend API to register at correct v1 endpoint
        $response = $API->post('/api/v1/auth/register', [
            'name' => $name,
            'email' => $email,
            'password' => $password
        ]);
        
        if ($response['success']) {
            // Auto-login after registration
            $token = $response['data']['token'] ?? null;
            $user = $response['data']['user'] ?? null;
            
            error_log('Signup Response: ' . json_encode($response));
            
            if ($token && $user) {
                storeAuthToken($token, $user);
                
                // Redirect immediately to appropriate dashboard based on role
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
                // Account created but auto-login failed, redirect to login
                $debug_info = 'Token: ' . ($token ? 'yes' : 'no') . ', User: ' . ($user ? 'yes' : 'no');
                error_log('Signup extraction failed: ' . $debug_info);
                $success = 'Account created successfully! Redirecting to login...';  
                header('Refresh: 2; url=/clothing_project/login.php');
            }
        } else {
            // Check for specific error messages from backend
            $error = $response['message'] ?? 'Registration failed. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up | UrbanWear</title>
    <link rel="stylesheet" href="css/premium-auth.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .password-wrapper {
            position: relative;
        }
        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: #888;
            z-index: 10;
        }
        .validation-list {
            list-style: none;
            padding: 0;
            margin: 8px 0 0 0;
            font-size: 0.8rem;
            color: #666;
        }
        .validation-item {
            display: flex;
            align-items: center;
            margin-bottom: 4px;
            transition: color 0.3s ease;
        }
        .validation-item i {
            margin-right: 8px;
            font-size: 10px;
        }
        .validation-item.valid {
            color: #2ecc71;
        }
        .validation-item.invalid {
            color: #e74c3c;
        }
    </style>
</head>
<body>

    <a href="index.php" class="home-link"><i class="fa-solid fa-arrow-left"></i> Home</a>

    <div class="auth-container">
        <!-- Image Side -->
        <div class="auth-image-side">
            <img src="https://images.unsplash.com/photo-1558769132-cb1aea458c5e?q=80&w=1974&auto=format&fit=crop" alt="Fashion Editorial">
            <div class="auth-overlay">
                <h2>JOIN THE CLUB</h2>
                <p>Experience exclusive drops and early access.</p>
            </div>
        </div>

        <!-- Form Side -->
        <div class="auth-form-side">
            <div class="form-wrapper">
                <h1 class="brand-title">Create Account</h1>
                <p class="brand-subtitle">Start your journey with UrbanWear.</p>

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
                        <label for="name">Full Name</label>
                        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required placeholder="Type your full name">
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required placeholder="Type your email">
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="password-wrapper">
                            <input type="password" id="password" name="password" required placeholder="Create a password">
                            <i class="fa-solid fa-eye password-toggle" id="togglePassword"></i>
                        </div>
                        <div id="password-hint" class="validation-list" style="margin-top:5px; font-size:0.8rem; color:#666;">
                            Password must be at least 6 characters
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Confirm Password</label>
                        <div class="password-wrapper">
                            <input type="password" id="confirm_password" name="confirm_password" required placeholder="Confirm your password">
                            <i class="fa-solid fa-eye password-toggle" id="toggleConfirm"></i>
                        </div>
                        <div id="match-message" class="validation-list" style="margin-top:5px; font-size:0.8rem;"></div>
                    </div>

                    <button type="submit" class="btn-auth" id="submitBtn">Sign Up</button>
                </form>

                <div class="auth-links">
                    <p>Already a member? <a href="/clothing_project/login.php">Sign In</a></p>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passwordInput = document.getElementById('password');
            const confirmInput = document.getElementById('confirm_password');
            const submitBtn = document.getElementById('submitBtn');
            const togglePassword = document.getElementById('togglePassword');
            const toggleConfirm = document.getElementById('toggleConfirm');
            
            function validatePassword() {
                const val = passwordInput.value;
                const hint = document.getElementById('password-hint');
                
                if (val.length >= 6) {
                    hint.innerHTML = '<span style="color:#2ecc71"><i class="fa-solid fa-check"></i> Valid length</span>';
                    return true;
                } else {
                    hint.innerHTML = 'Password must be at least 6 characters';
                    return false;
                }
            }

            function checkMatch() {
                const matchMsg = document.getElementById('match-message');
                if (confirmInput.value.length > 0) {
                    if (passwordInput.value === confirmInput.value) {
                        matchMsg.innerHTML = '<span style="color:#2ecc71"><i class="fa-solid fa-check"></i> Passwords match</span>';
                        return true;
                    } else {
                        matchMsg.innerHTML = '<span style="color:#e74c3c"><i class="fa-solid fa-times"></i> Passwords do not match</span>';
                        return false;
                    }
                }
                matchMsg.innerHTML = '';
                return false;
            }

            function toggleVisibility(input, icon) {
                const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
                input.setAttribute('type', type);
                icon.classList.toggle('fa-eye');
                icon.classList.toggle('fa-eye-slash');
            }

            passwordInput.addEventListener('input', validatePassword);
            confirmInput.addEventListener('input', checkMatch);
            
            togglePassword.addEventListener('click', () => toggleVisibility(passwordInput, togglePassword));
            toggleConfirm.addEventListener('click', () => toggleVisibility(confirmInput, toggleConfirm));

            document.querySelector('form').addEventListener('submit', function(e) {
                if (!validatePassword() || passwordInput.value !== confirmInput.value) {
                    e.preventDefault();
                    alert('Please ensure all password requirements are met and passwords match.');
                } else {
                    submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Creating Account...';
                    submitBtn.disabled = true;
                }
            });
        });
    </script>
</body>
</html>
