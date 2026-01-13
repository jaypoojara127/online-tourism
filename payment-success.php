<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Get payment response
$status = $_POST["status"] ?? '';
$firstname = $_POST["firstname"] ?? '';
$amount = $_POST["amount"] ?? '';
$txnid = $_POST["txnid"] ?? '';
$posted_hash = $_POST["hash"] ?? '';
$key = $_POST["key"] ?? '';
$productinfo = $_POST["productinfo"] ?? '';
$email = $_POST["email"] ?? '';

$SALT = PAYU_MERCHANT_SALT;

// Validate payment
if(isset($_POST["additionalCharges"])) {
    $additionalCharges = $_POST["additionalCharges"];
    $retHashSeq = $additionalCharges.'|'.$SALT.'|'.$status.'|||||||||||'.$email.'|'.$firstname.'|'.$productinfo.'|'.$amount.'|'.$txnid.'|'.$key;
} else {
    $retHashSeq = $SALT.'|'.$status.'|||||||||||'.$email.'|'.$firstname.'|'.$productinfo.'|'.$amount.'|'.$txnid.'|'.$key;
}

$hash = hash("sha512", $retHashSeq);

if ($hash != $posted_hash) {
    $payment_status = "failed";
    $message = "Invalid payment response. Please contact support.";
} else {
    // Payment is valid
    if ($status == "success") {
        $payment_status = "completed";
        $message = "Payment successful! Your booking is confirmed.";
        
        // Update booking and payment status in database
        // Note: In production, you would need to map txnid to booking_id
        // For now, we'll redirect to success page
        
    } else {
        $payment_status = "failed";
        $message = "Payment failed. Please try again.";
    }
}

// Store payment response in session
$_SESSION['payment_response'] = [
    'status' => $payment_status,
    'message' => $message,
    'transaction_id' => $txnid,
    'amount' => $amount,
    'name' => $firstname
];

// Redirect to appropriate page
if ($payment_status == "completed") {
    header('Location: ../pages/payment-success.php');
} else {
    header('Location: ../pages/payment-failed.php');
}
exit();
?>