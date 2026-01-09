<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

if ($auth->isAdminLoggedIn()) {
    header('Location: ' . SITE_URL . 'admin/dashboard.php');
    exit();
}

$error = '';
$success = '';

