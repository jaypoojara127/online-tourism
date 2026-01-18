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
    <title>Payment Failed - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="/online-tourism/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    .payment-failed {
        max-width: 600px;
        margin: 4rem auto;
        background: white;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        overflow: hidden;
        text-align: center;
    }
    
    .failed-header {
        background: linear-gradient(135deg, #e74c3c, #c0392b);
        color: white;
        padding: 3rem 2rem;
    }
    
    .failed-icon {
        font-size: 5rem;
        margin-bottom: 1rem;
    }
    
    .failed-body {
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
    
    .troubleshooting {
        background: #fff8e1;
        border-left: 4px solid #ffc107;
        padding: 1.5rem;
        border-radius: 8px;
        margin-top: 2rem;
        text-align: left;
    }
    
    .troubleshooting h3 {
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
        <div class="payment-failed">
            <div class="failed-header">
                <div class="failed-icon">
                    <i class="fas fa-times-circle"></i>
                </div>
                <h1>Payment Failed</h1>
                <p><?php echo htmlspecialchars($payment_response['message']); ?></p>
            </div>
            
            <div class="failed-body">
                <h2 style="margin-bottom: 1rem;">Payment Details</h2>
                
                <div class="payment-details">
                    <?php if($payment_response['transaction_id']): ?>
                    <div class="detail-row">
                        <span><strong>Transaction ID:</strong></span>
                        <span><?php echo htmlspecialchars($payment_response['transaction_id']); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if($payment_response['amount']): ?>
                    <div class="detail-row">
                        <span><strong>Amount:</strong></span>
                        <span>₹<?php echo number_format($payment_response['amount'], 2); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <div class="detail-row">
                        <span><strong>Payment Status:</strong></span>
                        <span style="color: #e74c3c; font-weight: 600;">
                            <i class="fas fa-times-circle"></i> <?php echo ucfirst($payment_response['status']); ?>
                        </span>
                    </div>
                    
                    <div class="detail-row">
                        <span><strong>Date:</strong></span>
                        <span><?php echo date('F d, Y h:i A'); ?></span>
                    </div>
                </div>
                
                <div class="troubleshooting">
                    <h3><i class="fas fa-wrench"></i> Troubleshooting Tips</h3>
                    <ul>
                        <li>Check if your card has sufficient funds</li>
                        <li>Verify your card details are correct</li>
                        <li>Ensure your internet connection is stable</li>
                        <li>Try using a different payment method</li>
                        <li>Contact your bank if the issue persists</li>
                    </ul>
                </div>
                
                <div class="next-actions">
                    <a href="payment.php?booking_id=<?php echo $_GET['booking_id'] ?? ''; ?>" class="btn btn-primary">
                        <i class="fas fa-redo"></i> Try Again
                    </a>
                    <a href="../user/dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-tachometer-alt"></i> Go to Dashboard
                    </a>
                    <a href="../pages/contact.php" class="btn btn-outline">
                        <i class="fas fa-headset"></i> Contact Support
                    </a>
                </div>
                
                <div class="alert alert-info" style="margin-top: 2rem;">
                    <h4><i class="fas fa-headset"></i> Need Immediate Assistance?</h4>
                    <p>Our customer support team is available 24/7 to help you:</p>
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
    // Auto-redirect to payment page after 10 seconds
    setTimeout(function() {
        <?php if(isset($_GET['booking_id'])): ?>
        window.location.href = 'payment.php?booking_id=<?php echo $_GET['booking_id']; ?>';
        <?php else: ?>
        window.location.href = '../user/dashboard.php';
        <?php endif; ?>
    }, 10000);
    </script>
</body>
</html>