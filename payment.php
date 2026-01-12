<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

$auth->checkUserAuth();

if (!isset($_GET['booking_id'])) {
    header('Location: ../user/dashboard.php');
    exit();
}

$booking_id = $_GET['booking_id'];
$user_id = $_SESSION['user_id'];

// Get booking details
$sql = "SELECT b.*, p.package_name, p.duration_days, p.duration_nights, 
               p.featured_image, p.price_per_person, p.discount_price,
               d.name as destination_name, d.city, d.country,
               u.email, u.phone, u.full_name
        FROM bookings b
        JOIN tour_packages p ON b.package_id = p.package_id
        JOIN destinations d ON p.destination_id = d.destination_id
        JOIN users u ON b.user_id = u.user_id
        WHERE b.booking_id = '$booking_id' AND b.user_id = '$user_id'";
$result = $db->executeQuery($sql);

if ($result->num_rows == 0) {
    header('Location: ../user/dashboard.php');
    exit();
}

$booking = $result->fetch_assoc();
$price = !empty($booking['discount_price']) ? $booking['discount_price'] : $booking['price_per_person'];
$total_amount = $price * $booking['num_travelers'];

// Check if already paid
$payment_check = "SELECT * FROM payments WHERE booking_id = '$booking_id' AND payment_status = 'completed'";
$payment_result = $db->executeQuery($payment_check);
if ($payment_result->num_rows > 0) {
    header('Location: payment-success.php?booking_id=' . $booking_id);
    exit();
}

// PayU Payment Integration
$MERCHANT_KEY = PAYU_MERCHANT_KEY;
$SALT = PAYU_MERCHANT_SALT;
$action = PAYU_BASE_URL . '/_payment';

$txnid = substr(hash('sha256', mt_rand() . microtime()), 0, 20);
$amount = $total_amount;
$firstname = $booking['full_name'];
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
    <title>Complete Payment - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    .payment-container {
        max-width: 800px;
        margin: 2rem auto;
        background: white;
        border-radius: 10px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    
    .payment-header {
        background: linear-gradient(135deg, var(--primary-color), #2980b9);
        color: white;
        padding: 2rem;
        text-align: center;
    }
    
    .payment-body {
        padding: 2rem;
    }
    
    .payment-steps {
        display: flex;
        justify-content: space-between;
        margin-bottom: 2rem;
        position: relative;
    }
    
    .payment-steps::before {
        content: '';
        position: absolute;
        top: 20px;
        left: 50px;
        right: 50px;
        height: 2px;
        background: #ddd;
        z-index: 1;
    }
    
    .step {
        text-align: center;
        position: relative;
        z-index: 2;
        flex: 1;
    }
    
    .step-icon {
        width: 40px;
        height: 40px;
        background: #f8f9fa;
        border: 2px solid #ddd;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 0.5rem;
        font-weight: bold;
        color: #666;
    }
    
    .step.active .step-icon {
        background: var(--primary-color);
        border-color: var(--primary-color);
        color: white;
    }
    
    .step.completed .step-icon {
        background: #2ecc71;
        border-color: #2ecc71;
        color: white;
    }
    
    .step-label {
        font-size: 0.9rem;
        color: #666;
    }
    
    .step.active .step-label {
        color: var(--primary-color);
        font-weight: 600;
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
    
    .payment-methods {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin: 1.5rem 0;
    }
    
    .payment-method {
        background: white;
        border: 2px solid #ddd;
        border-radius: 8px;
        padding: 1.5rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .payment-method:hover {
        border-color: var(--primary-color);
        box-shadow: 0 4px 8px rgba(52, 152, 219, 0.1);
    }
    
    .payment-method.selected {
        border-color: var(--primary-color);
        background: rgba(52, 152, 219, 0.05);
    }
    
    .payment-method i {
        font-size: 2.5rem;
        margin-bottom: 1rem;
        color: var(--primary-color);
    }
    
    .card-details {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 1.5rem;
        margin-top: 1.5rem;
        display: none;
    }
    
    .card-details.active {
        display: block;
    }
    
    .form-row {
        display: flex;
        gap: 1rem;
        margin-bottom: 1rem;
    }
    
    .form-group {
        flex: 1;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
    }
    
    .form-group input {
        width: 100%;
        padding: 0.8rem;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
    
    .amount-display {
        text-align: center;
        margin: 2rem 0;
        padding: 1.5rem;
        background: linear-gradient(135deg, #2ecc71, #27ae60);
        color: white;
        border-radius: 8px;
    }
    
    .amount-display h2 {
        margin: 0;
        font-size: 2.5rem;
    }
    
    .amount-display p {
        margin: 0.5rem 0 0 0;
        opacity: 0.9;
    }
    
    .upi-details {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 1.5rem;
        margin-top: 1.5rem;
        text-align: center;
        display: none;
    }
    
    .upi-details.active {
        display: block;
    }
    
    .upi-qr {
        width: 200px;
        height: 200px;
        background: white;
        margin: 1rem auto;
        padding: 1rem;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .upi-qr i {
        font-size: 4rem;
        color: #999;
    }
    
    .netbanking-details {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 1.5rem;
        margin-top: 1.5rem;
        display: none;
    }
    
    .netbanking-details.active {
        display: block;
    }
    
    .banks-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
        gap: 1rem;
        margin-top: 1rem;
    }
    
    .bank-option {
        background: white;
        border: 1px solid #ddd;
        border-radius: 4px;
        padding: 1rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .bank-option:hover {
        border-color: var(--primary-color);
        background: rgba(52, 152, 219, 0.05);
    }
    
    .bank-option.selected {
        border-color: var(--primary-color);
        background: rgba(52, 152, 219, 0.1);
    }
    
    .bank-logo {
        font-size: 2rem;
        color: var(--primary-color);
        margin-bottom: 0.5rem;
    }
    
    .security-info {
        background: #fff8e1;
        border-left: 4px solid #ffc107;
        padding: 1rem;
        border-radius: 4px;
        margin-top: 2rem;
    }
    
    @media (max-width: 768px) {
        .form-row {
            flex-direction: column;
            gap: 0;
        }
        
        .payment-methods {
            grid-template-columns: 1fr;
        }
        
        .banks-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="payment-container">
            <div class="payment-header">
                <h1>Complete Your Payment</h1>
                <p>Secure payment processed through PayU</p>
            </div>
            
            <div class="payment-body">
                <!-- Payment Steps -->
                <div class="payment-steps">
                    <div class="step completed">
                        <div class="step-icon">1</div>
                        <div class="step-label">Booking</div>
                    </div>
                    <div class="step active">
                        <div class="step-icon">2</div>
                        <div class="step-label">Payment</div>
                    </div>
                    <div class="step">
                        <div class="step-icon">3</div>
                        <div class="step-label">Confirmation</div>
                    </div>
                </div>
                
                <!-- Booking Summary -->
                <div class="booking-summary">
                    <h3>Booking Summary</h3>
                    <div class="summary-grid">
                        <div class="summary-item">
                            <span class="summary-label">Booking ID</span>
                            <span class="summary-value">#<?php echo str_pad($booking['booking_id'], 6, '0', STR_PAD_LEFT); ?></span>
                        </div>
                        
                        <div class="summary-item">
                            <span class="summary-label">Package</span>
                            <span class="summary-value"><?php echo $booking['package_name']; ?></span>
                        </div>
                        
                        <div class="summary-item">
                            <span class="summary-label">Destination</span>
                            <span class="summary-value"><?php echo $booking['destination_name']; ?></span>
                        </div>
                        
                        <div class="summary-item">
                            <span class="summary-label">Travel Date</span>
                            <span class="summary-value"><?php echo date('d M, Y', strtotime($booking['travel_date'])); ?></span>
                        </div>
                        
                        <div class="summary-item">
                            <span class="summary-label">Travelers</span>
                            <span class="summary-value"><?php echo $booking['num_travelers']; ?> person(s)</span>
                        </div>
                        
                        <div class="summary-item">
                            <span class="summary-label">Duration</span>
                            <span class="summary-value"><?php echo $booking['duration_days']; ?> Days / <?php echo $booking['duration_nights']; ?> Nights</span>
                        </div>
                    </div>
                </div>
                
                <!-- Amount Display -->
                <div class="amount-display">
                    <p>Total Amount to Pay</p>
                    <h2>₹<?php echo number_format($total_amount, 2); ?></h2>
                    <p>Inclusive of all taxes</p>
                </div>
                
                <!-- Payment Methods -->
                <h3 style="margin-bottom: 1rem;">Select Payment Method</h3>
                
                <div class="payment-methods">
                    <div class="payment-method selected" onclick="selectPaymentMethod('card')">
                        <i class="fas fa-credit-card"></i>
                        <h4>Credit/Debit Card</h4>
                        <p>Visa, MasterCard, RuPay, AMEX</p>
                    </div>
                    
                    <div class="payment-method" onclick="selectPaymentMethod('upi')">
                        <i class="fas fa-mobile-alt"></i>
                        <h4>UPI</h4>
                        <p>Google Pay, PhonePe, Paytm</p>
                    </div>
                    
                    <div class="payment-method" onclick="selectPaymentMethod('netbanking')">
                        <i class="fas fa-university"></i>
                        <h4>Net Banking</h4>
                        <p>All major banks</p>
                    </div>
                    
                    <div class="payment-method" onclick="selectPaymentMethod('wallet')">
                        <i class="fas fa-wallet"></i>
                        <h4>Wallets</h4>
                        <p>Paytm, MobiKwik, Amazon Pay</p>
                    </div>
                </div>
                
                <!-- Card Payment Details -->
                <div id="card-details" class="card-details active">
                    <h4>Enter Card Details</h4>
                    <form id="card-form">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="card-number">Card Number</label>
                                <input type="text" id="card-number" placeholder="1234 5678 9012 3456" maxlength="19" 
                                       oninput="formatCardNumber(this)">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="card-name">Name on Card</label>
                                <input type="text" id="card-name" placeholder="John Doe">
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="expiry-date">Expiry Date</label>
                                <input type="text" id="expiry-date" placeholder="MM/YY" maxlength="5" 
                                       oninput="formatExpiryDate(this)">
                            </div>
                            
                            <div class="form-group">
                                <label for="cvv">CVV</label>
                                <input type="password" id="cvv" placeholder="123" maxlength="4" 
                                       oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label style="display: flex; align-items: center; gap: 0.5rem;">
                                <input type="checkbox" id="save-card">
                                <span>Save card for future payments</span>
                            </label>
                        </div>
                    </form>
                </div>
                
                <!-- UPI Payment Details -->
                <div id="upi-details" class="upi-details">
                    <h4>Pay via UPI</h4>
                    <p>Scan the QR code or enter UPI ID</p>
                    
                    <div class="upi-qr">
                        <i class="fas fa-qrcode"></i>
                    </div>
                    
                    <div style="margin-top: 1rem;">
                        <input type="text" placeholder="Enter UPI ID" style="width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 4px;">
                        <button class="btn btn-primary" style="margin-top: 1rem; width: 100%;">
                            <i class="fas fa-paper-plane"></i> Verify & Pay
                        </button>
                    </div>
                </div>
                
                <!-- Net Banking Details -->
                <div id="netbanking-details" class="netbanking-details">
                    <h4>Select Your Bank</h4>
                    <div class="banks-grid">
                        <div class="bank-option" onclick="selectBank('hdfc')">
                            <div class="bank-logo">
                                <i class="fas fa-university"></i>
                            </div>
                            <span>HDFC Bank</span>
                        </div>
                        
                        <div class="bank-option" onclick="selectBank('icici')">
                            <div class="bank-logo">
                                <i class="fas fa-university"></i>
                            </div>
                            <span>ICICI Bank</span>
                        </div>
                        
                        <div class="bank-option" onclick="selectBank('sbi')">
                            <div class="bank-logo">
                                <i class="fas fa-university"></i>
                            </div>
                            <span>SBI</span>
                        </div>
                        
                        <div class="bank-option" onclick="selectBank('axis')">
                            <div class="bank-logo">
                                <i class="fas fa-university"></i>
                            </div>
                            <span>Axis Bank</span>
                        </div>
                        
                        <div class="bank-option" onclick="selectBank('kotak')">
                            <div class="bank-logo">
                                <i class="fas fa-university"></i>
                            </div>
                            <span>Kotak Bank</span>
                        </div>
                        
                        <div class="bank-option" onclick="selectBank('other')">
                            <div class="bank-logo">
                                <i class="fas fa-ellipsis-h"></i>
                            </div>
                            <span>Other Banks</span>
                        </div>
                    </div>
                </div>
                
                <!-- Wallet Payment Details -->
                <div id="wallet-details" class="upi-details" style="display: none;">
                    <h4>Select Wallet</h4>
                    <div style="display: flex; flex-direction: column; gap: 1rem; margin-top: 1rem;">
                        <button class="btn btn-outline" style="text-align: left; padding: 1rem;">
                            <i class="fab fa-google-pay" style="color: #5F6368; margin-right: 1rem;"></i>
                            Google Pay
                        </button>
                        
                        <button class="btn btn-outline" style="text-align: left; padding: 1rem;">
                            <i class="fas fa-mobile-alt" style="color: #5F3E92; margin-right: 1rem;"></i>
                            PhonePe
                        </button>
                        
                        <button class="btn btn-outline" style="text-align: left; padding: 1rem;">
                            <i class="fab fa-amazon-pay" style="color: #FF9900; margin-right: 1rem;"></i>
                            Amazon Pay
                        </button>
                        
                        <button class="btn btn-outline" style="text-align: left; padding: 1rem;">
                            <i class="fas fa-wallet" style="color: #00B9F1; margin-right: 1rem;"></i>
                            Paytm Wallet
                        </button>
                    </div>
                </div>
                
                <!-- Security Information -->
                <div class="security-info">
                    <h4><i class="fas fa-shield-alt"></i> Secure Payment</h4>
                    <p>Your payment is secured with 256-bit SSL encryption. We do not store your card details.</p>
                </div>
                
                <!-- PayU Payment Form (Hidden) -->
                <form id="payu-form" action="<?php echo $action; ?>" method="POST" style="display: none;">
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
                    <input type="hidden" name="service_provider" value="payu_paisa">
                </form>
                
                <!-- Action Buttons -->
                <div style="display: flex; justify-content: space-between; margin-top: 2rem;">
                    <a href="booking.php?id=<?php echo $booking_id; ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Booking
                    </a>
                    
                    <button class="btn btn-primary" onclick="processPayment()" id="pay-button">
                        <i class="fas fa-lock"></i> Pay Now ₹<?php echo number_format($total_amount, 2); ?>
                    </button>
                </div>
                
                <!-- Payment Security Logos -->
                <div style="display: flex; justify-content: center; gap: 2rem; margin-top: 2rem; opacity: 0.7;">
                    <i class="fas fa-shield-alt" title="SSL Secured"></i>
                    <i class="fas fa-lock" title="256-bit Encryption"></i>
                    <i class="fas fa-credit-card" title="PCI DSS Compliant"></i>
                    <i class="fas fa-user-shield" title="Fraud Protection"></i>
                </div>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    
    <script>
    // Payment method selection
    let selectedMethod = 'card';
    let selectedBank = null;
    
    function selectPaymentMethod(method) {
        selectedMethod = method;
        
        // Update UI
        document.querySelectorAll('.payment-method').forEach(el => {
            el.classList.remove('selected');
        });
        event.currentTarget.classList.add('selected');
        
        // Show relevant payment details
        document.getElementById('card-details').classList.remove('active');
        document.getElementById('upi-details').classList.remove('active');
        document.getElementById('netbanking-details').classList.remove('active');
        document.getElementById('wallet-details').style.display = 'none';
        
        switch(method) {
            case 'card':
                document.getElementById('card-details').classList.add('active');
                break;
            case 'upi':
                document.getElementById('upi-details').classList.add('active');
                break;
            case 'netbanking':
                document.getElementById('netbanking-details').classList.add('active');
                break;
            case 'wallet':
                document.getElementById('wallet-details').style.display = 'block';
                break;
        }
        
        updatePayButton();
    }
    
    function selectBank(bank) {
        selectedBank = bank;
        
        // Update UI
        document.querySelectorAll('.bank-option').forEach(el => {
            el.classList.remove('selected');
        });
        event.currentTarget.classList.add('selected');
        
        updatePayButton();
    }
    
    function updatePayButton() {
        const button = document.getElementById('pay-button');
        let text = 'Pay Now';
        
        switch(selectedMethod) {
            case 'card':
                text = 'Pay with Card';
                break;
            case 'upi':
                text = 'Pay via UPI';
                break;
            case 'netbanking':
                if (selectedBank) {
                    text = `Pay via ${selectedBank.toUpperCase()} Bank`;
                } else {
                    text = 'Select Bank to Pay';
                }
                break;
            case 'wallet':
                text = 'Pay with Wallet';
                break;
        }
        
        button.innerHTML = `<i class="fas fa-lock"></i> ${text} ₹<?php echo number_format($total_amount, 2); ?>`;
    }
    
    // Format card number
    function formatCardNumber(input) {
        let value = input.value.replace(/\D/g, '');
        let formatted = '';
        
        for (let i = 0; i < value.length; i++) {
            if (i > 0 && i % 4 === 0) {
                formatted += ' ';
            }
            formatted += value[i];
        }
        
        input.value = formatted.substring(0, 19); // Max 16 digits + 3 spaces
    }
    
    // Format expiry date
    function formatExpiryDate(input) {
        let value = input.value.replace(/\D/g, '');
        
        if (value.length >= 2) {
            value = value.substring(0, 2) + '/' + value.substring(2, 4);
        }
        
        input.value = value.substring(0, 5); // MM/YY format
    }
    
    // Validate card details
    function validateCardDetails() {
        const cardNumber = document.getElementById('card-number').value.replace(/\s/g, '');
        const cardName = document.getElementById('card-name').value.trim();
        const expiryDate = document.getElementById('expiry-date').value;
        const cvv = document.getElementById('cvv').value;
        
        // Reset errors
        clearErrors();
        
        let isValid = true;
        
        // Validate card number
        if (!cardNumber) {
            showError('card-number', 'Card number is required');
            isValid = false;
        } else if (!/^\d{16}$/.test(cardNumber)) {
            showError('card-number', 'Please enter a valid 16-digit card number');
            isValid = false;
        }
        
        // Validate card name
        if (!cardName) {
            showError('card-name', 'Name on card is required');
            isValid = false;
        }
        
        // Validate expiry date
        if (!expiryDate) {
            showError('expiry-date', 'Expiry date is required');
            isValid = false;
        } else if (!/^\d{2}\/\d{2}$/.test(expiryDate)) {
            showError('expiry-date', 'Please enter expiry date in MM/YY format');
            isValid = false;
        } else {
            const [month, year] = expiryDate.split('/');
            const now = new Date();
            const currentYear = now.getFullYear() % 100;
            const currentMonth = now.getMonth() + 1;
            
            if (parseInt(month) < 1 || parseInt(month) > 12) {
                showError('expiry-date', 'Invalid month');
                isValid = false;
            } else if (parseInt(year) < currentYear || 
                      (parseInt(year) === currentYear && parseInt(month) < currentMonth)) {
                showError('expiry-date', 'Card has expired');
                isValid = false;
            }
        }
        
        // Validate CVV
        if (!cvv) {
            showError('cvv', 'CVV is required');
            isValid = false;
        } else if (!/^\d{3,4}$/.test(cvv)) {
            showError('cvv', 'Please enter a valid CVV (3 or 4 digits)');
            isValid = false;
        }
        
        return isValid;
    }
    
    // Process payment
    function processPayment() {
        if (selectedMethod === 'card') {
            if (!validateCardDetails()) {
                return;
            }
        } else if (selectedMethod === 'netbanking' && !selectedBank) {
            alert('Please select your bank');
            return;
        }
        
        // Show processing
        const button = document.getElementById('pay-button');
        const originalText = button.innerHTML;
        button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        button.disabled = true;
        
        // In a real application, you would:
        // 1. Validate payment details
        // 2. Process payment via AJAX
        // 3. Redirect to payment gateway
        
        // For demo, we'll simulate processing
        setTimeout(() => {
            // Submit to PayU
            document.getElementById('payu-form').submit();
        }, 2000);
    }
    
    // Utility functions
    function showError(fieldId, message) {
        const field = document.getElementById(fieldId);
        const formGroup = field.closest('.form-group');
        
        const errorElement = document.createElement('div');
        errorElement.className = 'error-message';
        errorElement.style.color = '#e74c3c';
        errorElement.style.fontSize = '0.85rem';
        errorElement.style.marginTop = '0.3rem';
        errorElement.textContent = message;
        
        field.style.borderColor = '#e74c3c';
        formGroup.appendChild(errorElement);
    }
    
    function clearErrors() {
        const errorMessages = document.querySelectorAll('.error-message');
        errorMessages.forEach(error => error.remove());
        
        const inputs = document.querySelectorAll('input');
        inputs.forEach(input => {
            input.style.borderColor = '#ddd';
        });
    }
    
    // Auto-fill demo data for testing
    function fillDemoData() {
        if (confirm('Fill demo payment data for testing?')) {
            document.getElementById('card-number').value = '4111 1111 1111 1111';
            document.getElementById('card-name').value = '<?php echo $booking["full_name"]; ?>';
            document.getElementById('expiry-date').value = '12/30';
            document.getElementById('cvv').value = '123';
        }
    }
    
    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        // Add demo button for testing (remove in production)
        const demoButton = document.createElement('button');
        demoButton.className = 'btn btn-outline';
        demoButton.style.marginTop = '1rem';
        demoButton.innerHTML = '<i class="fas fa-vial"></i> Fill Demo Data';
        demoButton.onclick = fillDemoData;
        document.querySelector('.form-group:last-child').appendChild(demoButton);
        
        updatePayButton();
    });
    </script>
</body>
</html>