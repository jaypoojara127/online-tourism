<?php
require_once '../includes/config.php';

// Store failure in session
$_SESSION['payment_response'] = [
    'status' => 'failed',
    'message' => 'Payment was cancelled or failed. Please try again.',
    'transaction_id' => $_POST['txnid'] ?? '',
    'amount' => $_POST['amount'] ?? '',
    'name' => $_POST['firstname'] ?? ''
];

// Redirect to failure page
header('Location: ../pages/payment-failed.php');
exit();
?>