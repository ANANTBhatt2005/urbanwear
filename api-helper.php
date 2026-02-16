<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/config.php';
/**
 * URBANWEAR BACKEND API CLIENT
 * 
 * Professional PHP cURL wrapper for Node.js Express backend
 * 
 * Usage:
 *   $api = new URBANWEARApi('http://localhost:5000');
 *   $result = $api->post('/api/v1/auth/login', ['email' => 'user@example.com', 'password' => 'password']);
 */

class URBANWEARApi {
    private $baseUrl;
    private $timeout = 30;
    
    public function __construct($baseUrl = 'http://localhost:5000') {
        $this->baseUrl = rtrim($baseUrl, '/');
    }
    
    /**
     * Make GET request to backend
     */
    public function get($endpoint, $token = null) {
        return $this->request('GET', $endpoint, null, $token);
    }
    
    /**
     * Make POST request to backend
     */
    public function post($endpoint, $data = [], $token = null) {
        return $this->request('POST', $endpoint, $data, $token);
    }
    
    /**
     * Make PUT request to backend
     */
    public function put($endpoint, $data = [], $token = null) {
        return $this->request('PUT', $endpoint, $data, $token);
    }
    
    /**
     * Make DELETE request to backend
     */
    public function delete($endpoint, $token = null) {
        return $this->request('DELETE', $endpoint, null, $token);
    }
    
    /**
     * Core request method using cURL
     */
    private function request($method, $endpoint, $data = null, $token = null) {
        $url = $this->baseUrl . $endpoint;
        
        $ch = curl_init($url);
        
        // Basic options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeout);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        
        // Check if data contains a CURLFile for multipart/form-data
        $isMultipart = false;
        if (is_array($data)) {
            foreach ($data as $value) {
                if ($value instanceof CURLFile) {
                    $isMultipart = true;
                    break;
                }
            }
        }

        // Headers
        $headers = [
            'Accept: application/json'
        ];
        
        if (!$isMultipart) {
            $headers[] = 'Content-Type: application/json';
        }
        
        // Add JWT token if provided
        if ($token) {
            $headers[] = "Authorization: Bearer $token";
        }
        
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        // Add request body for POST/PUT
        if ($data && in_array($method, ['POST', 'PUT'])) {
            if ($isMultipart) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            } else {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
        }
        
        // Execute request
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        // Handle cURL errors
        if ($error) {
            return [
                'success' => false,
                'message' => 'Connection error: ' . $error,
                'error' => $error,
                'statusCode' => 0
            ];
        }
        
        // Parse JSON response
        $decoded = json_decode($response, true);
        
        // Return the full decoded response from backend
        // Backend is expected to return: { success, message, data: { token, user } }
        // For backward compatibility, if token/user exist at root we map them into data.
        $data = $decoded['data'] ?? null;
        if (!$data && (isset($decoded['token']) || isset($decoded['user']))) {
            $data = [
                'token' => $decoded['token'] ?? null,
                'user' => $decoded['user'] ?? null
            ];
        }

        return [
            'success' => $decoded['success'] ?? false,
            'message' => $decoded['message'] ?? 'No response',
            'data' => $data,
            'statusCode' => $httpCode,
            'raw' => $decoded
        ];
    }
    
    /**
     * Health check - verify backend is running
     */
    public function healthCheck() {
        return $this->get('/health');
    }

    /**
     * Get configured base URL
     */
    public function getBaseUrl() {
        return $this->baseUrl;
    }
}

/**
 * Session helper functions
 */

/**
 * Store auth token in session
 */
function storeAuthToken($token, $user) {
    // Normalize user id fields for compatibility
    if ($user && !isset($user['id']) && isset($user['_id'])) {
        $user['id'] = $user['_id'];
    }

    $_SESSION['auth_token'] = $token;
    $_SESSION['user_id'] = $user['id'] ?? $user['_id'] ?? null;
    $_SESSION['user_email'] = $user['email'] ?? null;
    $_SESSION['user_name'] = $user['name'] ?? null;
    $_SESSION['user_role'] = $user['role'] ?? 'user';
    $_SESSION['logged_in'] = true;
}

/**
 * Get current auth token from session
 */
function getAuthToken() {
    return $_SESSION['auth_token'] ?? null;
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Check if current user is admin
 */
function isAdmin() {
    // Support both Node.js role and legacy local admin session
    if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
        return true;
    }
    
    $role = $_SESSION['user_role'] ?? null;
    return isLoggedIn() && (strtolower($role) === 'admin');
}

/**
 * Clear session - logout
 */
function clearAuthSession() {
    unset($_SESSION['auth_token']);
    unset($_SESSION['user_id']);
    unset($_SESSION['user_email']);
    unset($_SESSION['user_name']);
    unset($_SESSION['user_role']);
    unset($_SESSION['logged_in']);
    unset($_SESSION['admin_logged_in']);
}

/**
 * Redirect to login if not authenticated
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /clothing_project/login.php');
        exit;
    }
}

/**
 * Redirect to login if not admin
 */
function requireAdmin() {
    if (!isAdmin()) {
        error_log("Admin access denied. Session: " . json_encode($_SESSION));
        header('Location: /clothing_project/login.php');
        exit;
    }
}

/**
 * Get current user info
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'] ?? null,
        'email' => $_SESSION['user_email'] ?? null,
        'name' => $_SESSION['user_name'] ?? null,
        'role' => $_SESSION['user_role'] ?? 'user'
    ];
}

/**
 * Initialize API client globally
 */
$API = new URBANWEARApi('http://localhost:5000');
