<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if (!isset($_GET['booking_id'])) {
    header('Location: ../user/dashboard.php');
    exit();
}

$booking_id = $_GET['booking_id'];
$user_id = $_SESSION['user_id'];

// Get booking details
$sql = "SELECT b.*, p.package_name, u.email, u.phone 
        FROM bookings b
        JOIN tour_packages p ON b.package_id = p.package_id
        JOIN users u ON b.user_id = u.user_id
        WHERE b.booking_id = '$booking_id' AND b.user_id = '$user_id'";
$result = $db->executeQuery($sql);

if ($result->num_rows == 0) {
    header('Location: ../user/dashboard.php');
    exit();
}

$booking = $result->fetch_assoc();

// PayU Payment Integration
$MERCHANT_KEY = PAYU_MERCHANT_KEY;
$SALT = PAYU_MERCHANT_SALT;
$action = PAYU_BASE_URL . '/_payment';

$txnid = substr(hash('sha256', mt_rand() . microtime()), 0, 20);
$amount = number_format((float)$booking['total_amount'], 2, '.', '');
$firstname = $_SESSION['full_name'];
$email = $booking['email'];
$phone = $booking['phone'];
$productinfo = $booking['package_name'];
$surl = SITE_URL . 'api/payment-success.php';
$furl = SITE_URL . 'api/payment-failure.php';

$hash_string = $MERCHANT_KEY . '|' . $txnid . '|' . $amount . '|' . $productinfo . '|' . $firstname . '|' . $email . '|||||||||||' . $SALT;
$hash = strtolower(hash('sha512', $hash_string));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="/online-tourism/assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <section class="payment-section">
        <div class="container">
            <div class="payment-container">
                <h2>Complete Your Payment</h2>
                <div class="test-badge" style="background: #f1c40f; color: #000; padding: 5px 10px; display: inline-block; border-radius: 4px; margin-bottom: 1rem; font-size: 0.8rem; font-weight: bold;">
                    ENVIRONMENT: TEST MODE (Key: <?php echo substr($MERCHANT_KEY, 0, 2) . '****'; ?>)
                    <br>Amount Sent: <?php echo $amount; ?>
                    <br>Salt Used: <?php echo substr($SALT, 0, 4) . '...' . substr($SALT, -4); ?>
                    <!-- Debug String: <?php echo $hash_string; ?> -->
                </div>
                
                <div class="payment-summary">
                    <h3>Booking Summary</h3>
                    <div class="summary-details">
                        <p><strong>Package:</strong> <?php echo $booking['package_name']; ?></p>
                        <p><strong>Travel Date:</strong> <?php echo date('d M, Y', strtotime($booking['travel_date'])); ?></p>
                        <p><strong>Travelers:</strong> <?php echo $booking['num_travelers']; ?></p>
                        <p><strong>Total Amount:</strong> ₹<?php echo number_format($booking['total_amount'], 2); ?></p>
                    </div>
                </div>
                
                <div class="payment-methods">
                    <h3>Select Payment Method</h3>
                    <form action="<?php echo $action; ?>" method="POST" id="paymentForm">
                        <input type="hidden" name="key" value="<?php echo $MERCHANT_KEY; ?>">
                        <input type="hidden" name="txnid" value="<?php echo $txnid; ?>">
                        <input type="hidden" name="amount" value="<?php echo $amount; ?>">
                        <input type="hidden" name="productinfo" value="<?php echo $productinfo; ?>">
                        <input type="hidden" name="firstname" value="<?php echo $firstname; ?>">
                        <input type="hidden" name="email" value="<?php echo $email; ?>">
                        <input type="hidden" name="phone" value="<?php echo $phone; ?>">
                        <input type="hidden" name="surl" value="<?php echo $surl; ?>">
                        <input type="hidden" name="furl" value="<?php echo $furl; ?>">
                        <input type="hidden" name="hash" value="<?php echo $hash; ?>">
                        
                        <div class="payment-options">
                            <label class="payment-option">
                                <input type="radio" name="payment_mode" value="CC" checked>
                                <span>Credit Card</span>
                            </label>
                            <label class="payment-option">
                                <input type="radio" name="payment_mode" value="DC">
                                <span>Debit Card</span>
                            </label>
                            <label class="payment-option">
                                <input type="radio" name="payment_mode" value="NB">
                                <span>Net Banking</span>
                            </label>
                            <label class="payment-option">
                                <input type="radio" name="payment_mode" value="UPI">
                                <span>UPI</span>
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-block">Proceed to Pay ₹<?php echo number_format($amount, 2); ?></button>
                    </form>
                </div>
            </div>
        </div>
    </section>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>