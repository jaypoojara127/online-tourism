<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

$auth->checkUserAuth();

$booking_id = $_GET['id'] ?? 0;
if (!$booking_id) {
    header('Location: ../user/dashboard.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get booking details
$sql = "SELECT b.*, p.package_name, p.duration_days, p.duration_nights, 
               p.price_per_person, p.discount_price, p.featured_image,
               d.name as destination_name, d.city, d.country
        FROM bookings b
        JOIN tour_packages p ON b.package_id = p.package_id
        JOIN destinations d ON p.destination_id = d.destination_id
        WHERE b.booking_id = '$booking_id' AND b.user_id = '$user_id'";
$result = $db->executeQuery($sql);

if ($result->num_rows == 0) {
    header('Location: ../user/dashboard.php');
    exit();
}

$booking = $result->fetch_assoc();
$price = !empty($booking['discount_price']) ? $booking['discount_price'] : $booking['price_per_person'];
$total_amount = $price * $booking['num_travelers'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmation - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="/online-tourism/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    .booking-confirmation {
        max-width: 800px;
        margin: 2rem auto;
        background: white;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    
    .confirmation-header {
        background: linear-gradient(135deg, #2ecc71, #27ae60);
        color: white;
        padding: 2rem;
        text-align: center;
    }
    
    .confirmation-icon {
        font-size: 4rem;
        margin-bottom: 1rem;
    }
    
    .confirmation-header h1 {
        margin-bottom: 0.5rem;
    }
    
    .confirmation-body {
        padding: 2rem;
    }
    
    .booking-summary {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }
    
    .summary-item {
        display: flex;
        flex-direction: column;
    }
    
    .summary-label {
        font-size: 0.9rem;
        color: #666;
        margin-bottom: 0.3rem;
    }
    
    .summary-value {
        font-weight: 600;
        color: var(--dark-color);
    }
    
    .traveler-details {
        margin-bottom: 2rem;
    }
    
    .details-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 1rem;
    }
    
    .details-table th,
    .details-table td {
        padding: 0.8rem;
        text-align: left;
        border-bottom: 1px solid #eee;
    }
    
    .details-table th {
        background: #f8f9fa;
        font-weight: 600;
    }
    
    .payment-options {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .payment-methods {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1rem;
        margin: 1rem 0;
    }
    
    .payment-method {
        background: white;
        border: 2px solid #ddd;
        border-radius: 8px;
        padding: 1rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .payment-method:hover,
    .payment-method.selected {
        border-color: var(--primary-color);
        background: rgba(52, 152, 219, 0.05);
    }
    
    .payment-method i {
        font-size: 2rem;
        margin-bottom: 0.5rem;
        color: var(--primary-color);
    }
    
    .confirmation-actions {
        display: flex;
        gap: 1rem;
        justify-content: center;
        margin-top: 2rem;
    }
    
    .booking-id {
        background: #2c3e50;
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
        letter-spacing: 1px;
        display: inline-block;
        margin-top: 1rem;
    }
    
    .next-steps {
        background: #fff8e1;
        border-left: 4px solid #ffc107;
        padding: 1rem;
        border-radius: 4px;
        margin: 2rem 0;
    }
    
    .next-steps h3 {
        color: #ff9800;
        margin-bottom: 0.5rem;
    }
    
    .next-steps ul {
        margin-left: 1.5rem;
    }
    
    .next-steps li {
        margin-bottom: 0.5rem;
    }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="booking-confirmation">
            <div class="confirmation-header">
                <div class="confirmation-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h1>Booking Confirmed!</h1>
                <p>Your tour package has been successfully booked</p>
                <div class="booking-id">Booking ID: #<?php echo str_pad($booking['booking_id'], 6, '0', STR_PAD_LEFT); ?></div>
            </div>
            
            <div class="confirmation-body">
                <!-- Next Steps -->
                <div class="next-steps">
                    <h3><i class="fas fa-info-circle"></i> Next Steps</h3>
                    <ul>
                        <li>Complete your payment to secure your booking</li>
                        <li>You will receive a confirmation email shortly</li>
                        <li>Our travel consultant will contact you within 24 hours</li>
                        <li>Keep your booking ID ready for future reference</li>
                    </ul>
                </div>
                
                <!-- Booking Summary -->
                <div class="booking-summary">
                    <h2 style="margin-bottom: 1rem;">Booking Summary</h2>
                    <div class="summary-grid">
                        <div class="summary-item">
                            <span class="summary-label">Package Name</span>
                            <span class="summary-value"><?php echo $booking['package_name']; ?></span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Destination</span>
                            <span class="summary-value"><?php echo $booking['destination_name']; ?></span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Travel Date</span>
                            <span class="summary-value"><?php echo date('F d, Y', strtotime($booking['travel_date'])); ?></span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Duration</span>
                            <span class="summary-value"><?php echo $booking['duration_days']; ?> Days / <?php echo $booking['duration_nights']; ?> Nights</span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Travelers</span>
                            <span class="summary-value"><?php echo $booking['num_travelers']; ?> Person(s)</span>
                        </div>
                        <div class="summary-item">
                            <span class="summary-label">Total Amount</span>
                            <span class="summary-value" style="color: var(--primary-color); font-size: 1.2rem;">
                                ₹<?php echo number_format($total_amount, 2); ?>
                            </span>
                        </div>
                    </div>
                </div>
                
                <!-- Traveler Details -->
                <div class="traveler-details">
                    <h2 style="margin-bottom: 1rem;">Traveler Details</h2>
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> 
                        Primary traveler details will be collected by our travel consultant.
                    </div>
                </div>
                
                <!-- Special Requests -->
                <?php if(!empty($booking['special_requests'])): ?>
                <div class="special-requests">
                    <h2 style="margin-bottom: 1rem;">Special Requests</h2>
                    <div style="background: #f8f9fa; padding: 1rem; border-radius: 4px;">
                        <?php echo nl2br(htmlspecialchars($booking['special_requests'])); ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Payment Options -->
                <div class="payment-options">
                    <h2 style="margin-bottom: 1rem;">Complete Your Payment</h2>
                    <p style="margin-bottom: 1rem;">Choose your preferred payment method:</p>
                    
                    <div class="payment-methods">
                        <div class="payment-method selected" onclick="selectPayment('online')">
                            <i class="fas fa-credit-card"></i>
                            <h4>Online Payment</h4>
                            <p>Pay now using card, UPI or net banking</p>
                        </div>
                        
                        <div class="payment-method" onclick="selectPayment('bank')">
                            <i class="fas fa-university"></i>
                            <h4>Bank Transfer</h4>
                            <p>Transfer to our bank account</p>
                        </div>
                        
                        <div class="payment-method" onclick="selectPayment('wallet')">
                            <i class="fas fa-wallet"></i>
                            <h4>Wallet</h4>
                            <p>Pay using digital wallet</p>
                        </div>
                    </div>
                    
                    <div id="payment-details" style="margin-top: 1.5rem;">
                        <!-- Online Payment Details -->
                        <div id="online-payment" class="payment-detail">
                            <h4>Secure Online Payment</h4>
                            <p>Your payment will be processed through our secure payment gateway.</p>
                            <div style="background: white; padding: 1rem; border-radius: 4px; margin-top: 1rem;">
                                <p><strong>Amount to Pay:</strong> ₹<?php echo number_format($total_amount, 2); ?></p>
                                <p><strong>Payment Gateway:</strong> PayU (Secure & Encrypted)</p>
                            </div>
                        </div>
                        
                        <!-- Bank Transfer Details (hidden by default) -->
                        <div id="bank-payment" class="payment-detail" style="display: none;">
                            <h4>Bank Transfer Details</h4>
                            <div style="background: white; padding: 1rem; border-radius: 4px; margin-top: 1rem;">
                                <p><strong>Bank Name:</strong> Tourism Bank</p>
                                <p><strong>Account Name:</strong> <?php echo SITE_NAME; ?> Tours</p>
                                <p><strong>Account Number:</strong> 123456789012</p>
                                <p><strong>IFSC Code:</strong> TOUR0001234</p>
                                <p><strong>Branch:</strong> Tourism City</p>
                                <p><strong>Amount:</strong> ₹<?php echo number_format($total_amount, 2); ?></p>
                                <p class="text-danger"><i class="fas fa-exclamation-triangle"></i> Send screenshot of transfer to info@tourism.com</p>
                            </div>
                        </div>
                        
                        <!-- Wallet Details (hidden by default) -->
                        <div id="wallet-payment" class="payment-detail" style="display: none;">
                            <h4>Digital Wallet Payment</h4>
                            <p>Scan the QR code below to pay:</p>
                            <div style="background: white; padding: 1rem; border-radius: 4px; margin-top: 1rem; text-align: center;">
                                <div style="background: #f8f9fa; padding: 1rem; border-radius: 4px; display: inline-block;">
                                    <!-- QR Code Placeholder -->
                                    <div style="width: 150px; height: 150px; background: #eee; display: flex; align-items: center; justify-content: center; margin: 0 auto;">
                                        <i class="fas fa-qrcode" style="font-size: 3rem; color: #999;"></i>
                                    </div>
                                </div>
                                <p style="margin-top: 1rem;"><strong>Amount:</strong> ₹<?php echo number_format($total_amount, 2); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Confirmation Actions -->
                <div class="confirmation-actions">
                    <a href="payment.php?booking_id=<?php echo $booking['booking_id']; ?>" class="btn btn-primary btn-lg">
                        <i class="fas fa-credit-card"></i> Proceed to Payment
                    </a>
                    <a href="../user/dashboard.php" class="btn btn-secondary btn-lg">
                        <i class="fas fa-user-circle"></i> Go to Dashboard
                    </a>
                    <a href="../pages/packages.php" class="btn btn-outline btn-lg">
                        <i class="fas fa-suitcase"></i> Browse More Tours
                    </a>
                </div>
                
                <!-- Important Information -->
                <div class="alert alert-info" style="margin-top: 2rem;">
                    <h4><i class="fas fa-info-circle"></i> Important Information</h4>
                    <ul style="margin-top: 0.5rem;">
                        <li>Your booking is confirmed but not secured until payment is completed</li>
                        <li>Cancellation policy: Full refund if cancelled 30 days before travel</li>
                        <li>Contact our support team for any queries: support@tourism.com</li>
                        <li>Keep your booking ID handy for all communications</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    
    <script>
    function selectPayment(method) {
        // Remove selected class from all methods
        document.querySelectorAll('.payment-method').forEach(item => {
            item.classList.remove('selected');
        });
        
        // Add selected class to clicked method
        event.target.closest('.payment-method').classList.add('selected');
        
        // Hide all payment details
        document.querySelectorAll('.payment-detail').forEach(detail => {
            detail.style.display = 'none';
        });
        
        // Show selected payment details
        document.getElementById(method + '-payment').style.display = 'block';
        
        // Update payment button URL
        const payButton = document.querySelector('.btn-primary');
        if (method === 'online') {
            payButton.href = 'payment.php?booking_id=<?php echo $booking['booking_id']; ?>';
            payButton.innerHTML = '<i class="fas fa-credit-card"></i> Proceed to Payment';
        } else if (method === 'bank') {
            payButton.href = '#';
            payButton.innerHTML = '<i class="fas fa-university"></i> Download Bank Details';
            payButton.onclick = function() { alert('Bank details will be sent to your email'); };
        } else if (method === 'wallet') {
            payButton.href = '#';
            payButton.innerHTML = '<i class="fas fa-wallet"></i> Show QR Code';
            payButton.onclick = function() { alert('Please scan the QR code to pay'); };
        }
    }
    
    // Print booking confirmation
    function printBooking() {
        window.print();
    }
    
    // Share booking
    function shareBooking() {
        if (navigator.share) {
            navigator.share({
                title: 'My Tour Booking - <?php echo SITE_NAME; ?>',
                text: 'I just booked <?php echo $booking['package_name']; ?> on <?php echo SITE_NAME; ?>!',
                url: window.location.href,
            });
        } else {
            alert('Share this URL: ' + window.location.href);
        }
    }
    </script>
</body>
</html>