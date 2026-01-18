<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

$auth->checkUserAuth();

// Get payment response from session
$payment_response = $_SESSION['payment_response'] ?? null;
if (!$payment_response) {
    header('Location: ../user/dashboard.php');
    exit();
}

// Clear payment response from session
unset($_SESSION['payment_response']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="/online-tourism/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    .payment-success {
        max-width: 600px;
        margin: 4rem auto;
        background: white;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        overflow: hidden;
        text-align: center;
    }
    
    .success-header {
        background: linear-gradient(135deg, #2ecc71, #27ae60);
        color: white;
        padding: 3rem 2rem;
    }
    
    .success-icon {
        font-size: 5rem;
        margin-bottom: 1rem;
    }
    
    .success-body {
        padding: 2rem;
    }
    
    .payment-details {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 1.5rem;
        margin: 1.5rem 0;
        text-align: left;
    }
    
    .detail-row {
        display: flex;
        justify-content: space-between;
        margin-bottom: 0.8rem;
        padding-bottom: 0.8rem;
        border-bottom: 1px solid #e9ecef;
    }
    
    .detail-row:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }
    
    .next-actions {
        display: flex;
        gap: 1rem;
        justify-content: center;
        margin-top: 2rem;
        flex-wrap: wrap;
    }
    
    .whats-next {
        background: #fff8e1;
        border-left: 4px solid #ffc107;
        padding: 1.5rem;
        border-radius: 8px;
        margin-top: 2rem;
        text-align: left;
    }
    
    .whats-next h3 {
        color: #ff9800;
        margin-bottom: 1rem;
    }
    
    @media (max-width: 768px) {
        .next-actions {
            flex-direction: column;
        }
        
        .next-actions .btn {
            width: 100%;
        }
    }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="payment-success">
            <div class="success-header">
                <div class="success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h1>Payment Successful!</h1>
                <p>Thank you for your payment. Your booking is now confirmed.</p>
            </div>
            
            <div class="success-body">
                <h2 style="margin-bottom: 1rem;">Payment Details</h2>
                
                <div class="payment-details">
                    <div class="detail-row">
                        <span><strong>Transaction ID:</strong></span>
                        <span><?php echo htmlspecialchars($payment_response['transaction_id']); ?></span>
                    </div>
                    
                    <div class="detail-row">
                        <span><strong>Amount Paid:</strong></span>
                        <span style="color: var(--primary-color); font-weight: 600;">
                            ₹<?php echo number_format($payment_response['amount'], 2); ?>
                        </span>
                    </div>
                    
                    <div class="detail-row">
                        <span><strong>Payment Status:</strong></span>
                        <span style="color: #27ae60; font-weight: 600;">
                            <i class="fas fa-check-circle"></i> <?php echo ucfirst($payment_response['status']); ?>
                        </span>
                    </div>
                    
                    <div class="detail-row">
                        <span><strong>Paid By:</strong></span>
                        <span><?php echo htmlspecialchars($payment_response['name']); ?></span>
                    </div>
                    
                    <div class="detail-row">
                        <span><strong>Payment Date:</strong></span>
                        <span><?php echo date('F d, Y h:i A'); ?></span>
                    </div>
                </div>
                
                <div class="whats-next">
                    <h3><i class="fas fa-info-circle"></i> What happens next?</h3>
                    <ul>
                        <li>A confirmation email has been sent to your registered email address</li>
                        <li>Our travel consultant will contact you within 24 hours</li>
                        <li>You will receive all travel documents 7 days before departure</li>
                        <li>Keep your booking ID handy for all communications</li>
                    </ul>
                </div>
                
                <div class="next-actions">
                    <a href="../user/dashboard.php" class="btn btn-primary">
                        <i class="fas fa-tachometer-alt"></i> Go to Dashboard
                    </a>
                    <a href="../user/bookings.php" class="btn btn-secondary">
                        <i class="fas fa-shopping-cart"></i> View My Bookings
                    </a>
                    <a href="#" onclick="window.print()" class="btn btn-outline">
                        <i class="fas fa-print"></i> Print Receipt
                    </a>
                    <a href="../pages/packages.php" class="btn btn-outline">
                        <i class="fas fa-suitcase"></i> Book Another Tour
                    </a>
                </div>
                
                <div class="alert alert-info" style="margin-top: 2rem;">
                    <h4><i class="fas fa-headset"></i> Need Help?</h4>
                    <p>Our customer support team is here to help you. Contact us:</p>
                    <p style="margin-top: 0.5rem;">
                        <strong>Email:</strong> support@tourism.com | 
                        <strong>Phone:</strong> +91 9876543210
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    
    <script>
    // Auto-redirect to dashboard after 30 seconds
    setTimeout(function() {
        window.location.href = '../user/dashboard.php';
    }, 30000);
    
    // Share success
    function shareSuccess() {
        if (navigator.share) {
            navigator.share({
                title: 'Payment Successful - <?php echo SITE_NAME; ?>',
                text: 'I just completed my payment successfully on <?php echo SITE_NAME; ?>!',
                url: window.location.href,
            });
        } else {
            alert('Payment completed successfully! Transaction ID: <?php echo $payment_response['transaction_id']; ?>');
        }
    }
    </script>
</body>
</html>