<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'online_tourism');

// Site configuration
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$script_name = str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);
define('SITE_URL', $protocol . "://" . $host . '/online-tourism/');
define('SITE_NAME', 'ExploreWorld Tourism');

// Payment Gateway Configuration (PayU)
define('PAYU_MERCHANT_KEY', 'gtKFFx');
define('PAYU_MERCHANT_SALT', '4R38IvwiV57FwVpsgOvTXBdLE4tHUXFW');
define('PAYU_BASE_URL', 'https://test.payu.in'); // Test URL

// File upload paths
define('UPLOAD_PATH', $_SERVER['DOCUMENT_ROOT'] . '/online-tourism/assets/images/uploads/');
define('UPLOAD_URL', SITE_URL . 'assets/images/uploads/');

// Start session
session_start();

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>