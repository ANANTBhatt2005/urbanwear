<?php
// BASE URL helper
// Determines base path relative to web server document root. Returns '' for root, or '/subdir' if served from a subdirectory.
$baseDir = dirname($_SERVER['SCRIPT_NAME']);
if ($baseDir === '/' || $baseDir === '\\') {
    $baseDir = '';
}
define('BASE_URL', $baseDir);

/**
 * url() - Build a safe URL based on BASE_URL
 * Example: url('login-connected.php') -> '/login-connected.php' or '/clothing_project/login-connected.php'
 */
function url($path = '') {
    $path = ltrim($path, '/');
    if ($path === '') {
        return BASE_URL === '' ? '/' : BASE_URL . '/';
    }
    return (BASE_URL === '') ? '/' . $path : BASE_URL . '/' . $path;
}
