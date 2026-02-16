<?php
/**
 * LOGOUT - Connected to Node.js Backend
 * 
 * Simple logout handler that clears the session and redirects to login
 */

session_start();
require_once 'api-helper.php';

// Clear auth session
clearAuthSession();

// Redirect to login
header('Location: /clothing_project/login.php?logged_out=true');
exit;
