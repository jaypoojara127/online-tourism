<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $db->escapeString($_POST['name'] ?? '');
    $email = $db->escapeString($_POST['email'] ?? '');
    $phone = $db->escapeString($_POST['phone'] ?? '');
    $subject = $db->escapeString($_POST['subject'] ?? '');
    $message = $db->escapeString($_POST['message'] ?? '');
    
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        $error = 'Please fill all required fields';
    } else {
        // Save contact message to database
        $sql = "INSERT INTO contact_messages (name, email, phone, subject, message) 
                VALUES ('$name', '$email', '$phone', '$subject', '$message')";
        
        if ($db->executeQuery($sql)) {
            $success = 'Thank you for contacting us! We will get back to you soon.';
            
            // Clear form
            $_POST = array();
        } else {
            $error = 'Something went wrong. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="/online-tourism/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    .contact-hero {
        background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('../assets/images/contact-bg.jpg');
        background-size: cover;
        background-position: center;
        color: white;
        padding: 6rem 0;
        text-align: center;
        margin-bottom: 3rem;
    }
    
    .contact-container {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 3rem;
        margin: 2rem 0;
    }
    
    .contact-info {
        background: white;
        border-radius: 10px;
        padding: 2rem;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    
    .contact-form {
        background: white;
        border-radius: 10px;
        padding: 2rem;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    
    .info-item {
        display: flex;
        align-items: flex-start;
        margin-bottom: 2rem;
        padding-bottom: 2rem;
        border-bottom: 1px solid #eee;
    }
    
    .info-item:last-child {
        border-bottom: none;
        margin-bottom: 0;
        padding-bottom: 0;
    }
    
    .info-icon {
        width: 50px;
        height: 50px;
        background: var(--primary-color);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        margin-right: 1rem;
        flex-shrink: 0;
    }
    
    .info-content h3 {
        margin-bottom: 0.5rem;
        color: var(--dark-color);
    }
    
    .form-group {
        margin-bottom: 1.5rem;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
    }
    
    .form-group input,
    .form-group textarea,
    .form-group select {
        width: 100%;
        padding: 0.8rem;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-family: 'Poppins', sans-serif;
    }
    
    .form-group textarea {
        min-height: 150px;
        resize: vertical;
    }
    
    .required {
        color: #e74c3c;
    }
    
    .office-hours {
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 1px solid #eee;
    }
    
    .hours-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 1rem;
    }
    
    .hours-table td {
        padding: 0.5rem;
        border-bottom: 1px solid #eee;
    }
    
    .hours-table tr:last-child td {
        border-bottom: none;
    }
    
    .map-container {
        margin-top: 3rem;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    
    .map-placeholder {
        height: 300px;
        background: #f8f9fa;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #666;
    }
    
    .faq-section {
        margin: 4rem 0;
    }
    
    .faq-item {
        background: white;
        border-radius: 8px;
        padding: 1.5rem;
        margin-bottom: 1rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .faq-item:hover {
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    
    .faq-question {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .faq-question h3 {
        margin: 0;
        font-size: 1.1rem;
    }
    
    .faq-answer {
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid #eee;
        display: none;
    }
    
    .faq-item.active .faq-answer {
        display: block;
    }
    
    .faq-item.active .faq-icon {
        transform: rotate(45deg);
    }
    
    .faq-icon {
        transition: transform 0.3s ease;
    }
    
    @media (max-width: 768px) {
        .contact-container {
            grid-template-columns: 1fr;
        }
    }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <!-- Hero Section -->
    <section class="contact-hero">
        <div class="container">
            <h1>Get in Touch</h1>
            <p>We're here to help you plan your perfect journey</p>
        </div>
    </section>
    
    <div class="container">
        <!-- Contact Container -->
        <div class="contact-container">
            <!-- Contact Information -->
            <div class="contact-info">
                <h2 style="margin-bottom: 2rem;">Contact Information</h2>
                
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="info-content">
                        <h3>Our Office</h3>
                        <p>123 Tourism Street<br>Travel City, TC 123456<br>India</p>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-phone"></i>
                    </div>
                    <div class="info-content">
                        <h3>Phone Number</h3>
                        <p>+91 9876543210<br>+91 9876543211</p>
                        <p style="margin-top: 0.5rem;"><small>Mon-Sat: 9 AM - 6 PM</small></p>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="info-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <div class="info-content">
                        <h3>Email Address</h3>
                        <p>info@tourism.com<br>support@tourism.com</p>
                        <p style="margin-top: 0.5rem;"><small>Response within 24 hours</small></p>
                    </div>
                </div>
                
                <!-- Office Hours -->
                <div class="office-hours">
                    <h3>Office Hours</h3>
                    <table class="hours-table">
                        <tr>
                            <td>Monday - Friday</td>
                            <td>9:00 AM - 6:00 PM</td>
                        </tr>
                        <tr>
                            <td>Saturday</td>
                            <td>9:00 AM - 4:00 PM</td>
                        </tr>
                        <tr>
                            <td>Sunday</td>
                            <td>Closed</td>
                        </tr>
                        <tr>
                            <td>Holidays</td>
                            <td>10:00 AM - 2:00 PM</td>
                        </tr>
                    </table>
                </div>
            </div>
            
            <!-- Contact Form -->
            <div class="contact-form">
                <h2 style="margin-bottom: 2rem;">Send us a Message</h2>
                
                <?php if($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                </div>
                <?php endif; ?>
                
                <?php if($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
                <?php endif; ?>
                
                <form method="POST" action="" onsubmit="return validateContactForm()">
                    <div class="form-group">
                        <label for="name">Full Name <span class="required">*</span></label>
                        <input type="text" id="name" name="name" required 
                               value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-row" style="display: flex; gap: 1rem; margin-bottom: 1.5rem;">
                        <div class="form-group" style="flex: 1;">
                            <label for="email">Email Address <span class="required">*</span></label>
                            <input type="email" id="email" name="email" required 
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                        </div>
                        
                        <div class="form-group" style="flex: 1;">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" 
                                   value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="subject">Subject <span class="required">*</span></label>
                        <select id="subject" name="subject" required>
                            <option value="">Select a subject</option>
                            <option value="General Inquiry" <?php echo ($_POST['subject'] ?? '') == 'General Inquiry' ? 'selected' : ''; ?>>General Inquiry</option>
                            <option value="Booking Assistance" <?php echo ($_POST['subject'] ?? '') == 'Booking Assistance' ? 'selected' : ''; ?>>Booking Assistance</option>
                            <option value="Payment Issue" <?php echo ($_POST['subject'] ?? '') == 'Payment Issue' ? 'selected' : ''; ?>>Payment Issue</option>
                            <option value="Cancellation Request" <?php echo ($_POST['subject'] ?? '') == 'Cancellation Request' ? 'selected' : ''; ?>>Cancellation Request</option>
                            <option value="Feedback" <?php echo ($_POST['subject'] ?? '') == 'Feedback' ? 'selected' : ''; ?>>Feedback</option>
                            <option value="Other" <?php echo ($_POST['subject'] ?? '') == 'Other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Message <span class="required">*</span></label>
                        <textarea id="message" name="message" required><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">
                        <i class="fas fa-paper-plane"></i> Send Message
                    </button>
                </form>
                
                <!-- Emergency Contact -->
                <div class="alert alert-info" style="margin-top: 2rem;">
                    <h4><i class="fas fa-phone-alt"></i> Emergency Contact</h4>
                    <p>For urgent assistance during your trip:</p>
                    <p style="margin-top: 0.5rem;"><strong>Emergency Hotline:</strong> +91 9876543219</p>
                    <p><strong>24/7 Support:</strong> Available for booked customers</p>
                </div>
            </div>
        </div>
        
        <!-- Map -->
        <div class="map-container">
            <div class="map-placeholder">
                <div style="text-align: center;">
                    <i class="fas fa-map-marked-alt" style="font-size: 3rem; margin-bottom: 1rem; color: #999;"></i>
                    <h3>Our Location</h3>
                    <p>123 Tourism Street, Travel City</p>
                    <a href="https://maps.google.com" target="_blank" class="btn btn-outline" style="margin-top: 1rem;">
                        <i class="fas fa-directions"></i> Get Directions
                    </a>
                </div>
            </div>
        </div>
        
        <!-- FAQ Section -->
        <div class="faq-section">
            <h2 style="text-align: center; margin-bottom: 2rem;">Frequently Asked Questions</h2>
            
            <div class="faq-item" onclick="toggleFAQ(this)">
                <div class="faq-question">
                    <h3>How do I book a tour package?</h3>
                    <i class="fas fa-plus faq-icon"></i>
                </div>
                <div class="faq-answer">
                    <p>You can book a tour package by browsing our packages, selecting your preferred option, choosing your travel dates, and completing the booking form. You'll need to create an account or login to proceed with payment.</p>
                </div>
            </div>
            
            <div class="faq-item" onclick="toggleFAQ(this)">
                <div class="faq-question">
                    <h3>What payment methods do you accept?</h3>
                    <i class="fas fa-plus faq-icon"></i>
                </div>
                <div class="faq-answer">
                    <p>We accept all major credit/debit cards, net banking, UPI payments, and digital wallets. All payments are processed through secure payment gateways.</p>
                </div>
            </div>
            
            <div class="faq-item" onclick="toggleFAQ(this)">
                <div class="faq-question">
                    <h3>What is your cancellation policy?</h3>
                    <i class="fas fa-plus faq-icon"></i>
                </div>
                <div class="faq-answer">
                    <p>We offer full refund if cancelled 30 days before travel. 50% refund if cancelled 15-30 days before travel. No refund for cancellations within 15 days of travel date.</p>
                </div>
            </div>
            
            <div class="faq-item" onclick="toggleFAQ(this)">
                <div class="faq-question">
                    <h3>Do you provide travel insurance?</h3>
                    <i class="fas fa-plus faq-icon"></i>
                </div>
                <div class="faq-answer">
                    <p>Yes, we offer optional travel insurance with all our packages. You can add it during the booking process or contact our support team to add it later.</p>
                </div>
            </div>
            
            <div class="faq-item" onclick="toggleFAQ(this)">
                <div class="faq-question">
                    <h3>Can I customize a tour package?</h3>
                    <i class="fas fa-plus faq-icon"></i>
                </div>
                <div class="faq-answer">
                    <p>Yes, we offer customized tour packages. Contact our travel consultants with your requirements, and we'll create a personalized itinerary for you.</p>
                </div>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    
    <script>
    // FAQ Toggle
    function toggleFAQ(item) {
        item.classList.toggle('active');
    }
    
    // Form Validation
    function validateContactForm() {
        const name = document.getElementById('name').value;
        const email = document.getElementById('email').value;
        const subject = document.getElementById('subject').value;
        const message = document.getElementById('message').value;
        
        // Reset previous errors
        clearErrors();
        
        let isValid = true;
        
        if (!name.trim()) {
            showError('name', 'Name is required');
            isValid = false;
        }
        
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!email.trim()) {
            showError('email', 'Email is required');
            isValid = false;
        } else if (!emailRegex.test(email)) {
            showError('email', 'Please enter a valid email address');
            isValid = false;
        }
        
        if (!subject) {
            showError('subject', 'Please select a subject');
            isValid = false;
        }
        
        if (!message.trim()) {
            showError('message', 'Message is required');
            isValid = false;
        } else if (message.trim().length < 10) {
            showError('message', 'Message should be at least 10 characters');
            isValid = false;
        }
        
        return isValid;
    }
    
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
        
        const inputs = document.querySelectorAll('input, textarea, select');
        inputs.forEach(input => {
            input.style.borderColor = '#ddd';
        });
    }
    
    // Initialize FAQ items
    document.addEventListener('DOMContentLoaded', function() {
        // You can pre-open the first FAQ if needed
        // document.querySelector('.faq-item').classList.add('active');
    });
    </script>
</body>
</html>