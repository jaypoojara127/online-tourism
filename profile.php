<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$auth->checkUserAuth();
$user_id = $_SESSION['user_id'];

// Get user data
$sql = "SELECT * FROM users WHERE user_id = '$user_id'";
$result = $db->executeQuery($sql);
$user = $result->fetch_assoc();

$success = '';
$error = '';

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $full_name = $db->escapeString($_POST['full_name']);
    $phone = $db->escapeString($_POST['phone']);
    $address = $db->escapeString($_POST['address']);
    
    // Handle profile image upload
    $profile_image = $user['profile_image'];
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $upload_dir = UPLOAD_PATH . 'profiles/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_name = time() . '_' . basename($_FILES['profile_image']['name']);
        $target_file = $upload_dir . $file_name;
        
        // Validate file
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $max_size = 2 * 1024 * 1024; // 2MB
        
        if (!in_array($_FILES['profile_image']['type'], $allowed_types)) {
            $error = 'Only JPG, PNG, and GIF images are allowed';
        } elseif ($_FILES['profile_image']['size'] > $max_size) {
            $error = 'Image size should be less than 2MB';
        } elseif (move_uploaded_file($_FILES['profile_image']['tmp_name'], $target_file)) {
            // Delete old profile image if exists
            if (!empty($profile_image) && file_exists(UPLOAD_PATH . $profile_image)) {
                unlink(UPLOAD_PATH . $profile_image);
            }
            $profile_image = 'profiles/' . $file_name;
        }
    }
    
    if (!$error) {
        $update_sql = "UPDATE users SET 
                      full_name = '$full_name',
                      phone = '$phone',
                      address = '$address',
                      profile_image = '$profile_image'
                      WHERE user_id = '$user_id'";
        
        if ($db->executeQuery($update_sql)) {
            // Update session
            $_SESSION['full_name'] = $full_name;
            $success = 'Profile updated successfully';
            
            // Refresh user data
            $result = $db->executeQuery($sql);
            $user = $result->fetch_assoc();
        } else {
            $error = 'Failed to update profile';
        }
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = 'All password fields are required';
    } elseif ($new_password !== $confirm_password) {
        $error = 'New passwords do not match';
    } elseif (strlen($new_password) < 6) {
        $error = 'Password must be at least 6 characters long';
    } else {
        // Verify current password
        if (password_verify($current_password, $user['password'])) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $password_sql = "UPDATE users SET password = '$hashed_password' WHERE user_id = '$user_id'";
            
            if ($db->executeQuery($password_sql)) {
                $success = 'Password changed successfully';
            } else {
                $error = 'Failed to change password';
            }
        } else {
            $error = 'Current password is incorrect';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    .profile-container {
        background: white;
        border-radius: 10px;
        padding: 2rem;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    
    .profile-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid var(--primary-color);
    }
    
    .profile-tabs {
        display: flex;
        gap: 0;
        border-bottom: 1px solid #ddd;
        margin-bottom: 2rem;
    }
    
    .tab-btn {
        padding: 1rem 2rem;
        background: none;
        border: none;
        font-size: 1rem;
        cursor: pointer;
        position: relative;
        color: #666;
    }
    
    .tab-btn:hover {
        color: var(--primary-color);
    }
    
    .tab-btn.active {
        color: var(--primary-color);
        font-weight: 600;
    }
    
    .tab-btn.active::after {
        content: '';
        position: absolute;
        bottom: -1px;
        left: 0;
        right: 0;
        height: 3px;
        background: var(--primary-color);
    }
    
    .tab-content {
        display: none;
    }
    
    .tab-content.active {
        display: block;
    }
    
    .profile-form {
        max-width: 600px;
    }
    
    .profile-image-section {
        text-align: center;
        margin-bottom: 2rem;
    }
    
    .profile-image-container {
        width: 150px;
        height: 150px;
        border-radius: 50%;
        overflow: hidden;
        margin: 0 auto 1rem;
        border: 5px solid #f8f9fa;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    
    .profile-image-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .profile-initials {
        width: 100%;
        height: 100%;
        background: var(--primary-color);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
        font-weight: bold;
    }
    
    .image-upload {
        position: relative;
        display: inline-block;
    }
    
    .image-upload input[type="file"] {
        position: absolute;
        left: 0;
        top: 0;
        opacity: 0;
        width: 100%;
        height: 100%;
        cursor: pointer;
    }
    
    .image-upload-label {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background: #f8f9fa;
        border-radius: 4px;
        cursor: pointer;
        transition: background 0.3s ease;
    }
    
    .image-upload-label:hover {
        background: #e9ecef;
    }
    
    .form-row {
        display: flex;
        gap: 1rem;
        margin-bottom: 1.5rem;
    }
    
    .form-group {
        flex: 1;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
    }
    
    .form-group input,
    .form-group textarea {
        width: 100%;
        padding: 0.8rem;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-family: 'Poppins', sans-serif;
    }
    
    .form-group textarea {
        min-height: 100px;
        resize: vertical;
    }
    
    .read-only-field {
        background: #f8f9fa;
        padding: 0.8rem;
        border-radius: 4px;
        border: 1px solid #ddd;
    }
    
    .account-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-top: 2rem;
    }
    
    .stat-card {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 1.5rem;
        text-align: center;
    }
    
    .stat-icon {
        font-size: 2rem;
        color: var(--primary-color);
        margin-bottom: 1rem;
    }
    
    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        color: var(--dark-color);
        margin-bottom: 0.5rem;
    }
    
    .danger-zone {
        background: #f8d7da;
        border: 1px solid #f5c6cb;
        border-radius: 8px;
        padding: 1.5rem;
        margin-top: 2rem;
    }
    
    .danger-zone h3 {
        color: #721c24;
        margin-bottom: 1rem;
    }
    
    @media (max-width: 768px) {
        .form-row {
            flex-direction: column;
            gap: 0;
        }
        
        .profile-tabs {
            flex-direction: column;
        }
        
        .tab-btn {
            text-align: left;
            border-bottom: 1px solid #eee;
        }
    }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <section class="dashboard">
        <div class="container">
            <div class="dashboard-grid">
                <?php include 'includes/sidebar.php'; ?>
                
                <main class="dashboard-content">
                    <div class="profile-container">
                        <div class="profile-header">
                            <h1>My Profile</h1>
                            <div class="member-since">
                                <small>Member since <?php echo date('F Y', strtotime($user['created_at'])); ?></small>
                            </div>
                        </div>
                        
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
                        
                        <!-- Tabs -->
                        <div class="profile-tabs">
                            <button class="tab-btn active" onclick="openTab('personal')">
                                <i class="fas fa-user"></i> Personal Info
                            </button>
                            <button class="tab-btn" onclick="openTab('password')">
                                <i class="fas fa-lock"></i> Change Password
                            </button>
                            <button class="tab-btn" onclick="openTab('preferences')">
                                <i class="fas fa-cog"></i> Preferences
                            </button>
                            <button class="tab-btn" onclick="openTab('account')">
                                <i class="fas fa-shield-alt"></i> Account Security
                            </button>
                        </div>
                        
                        <!-- Personal Info Tab -->
                        <div id="personal-tab" class="tab-content active">
                            <form method="POST" action="" enctype="multipart/form-data" class="profile-form">
                                <div class="profile-image-section">
                                    <div class="profile-image-container">
                                        <?php if(!empty($user['profile_image'])): ?>
                                        <img src="<?php echo UPLOAD_URL . $user['profile_image']; ?>" alt="<?php echo $user['full_name']; ?>">
                                        <?php else: ?>
                                        <div class="profile-initials">
                                            <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <div class="image-upload">
                                        <label class="image-upload-label">
                                            <i class="fas fa-camera"></i>
                                            Change Photo
                                            <input type="file" name="profile_image" accept="image/*">
                                        </label>
                                        <small style="display: block; margin-top: 0.5rem; color: #666;">Max 2MB, JPG/PNG/GIF</small>
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label>Username</label>
                                        <div class="read-only-field"><?php echo $user['username']; ?></div>
                                    </div>
                                    <div class="form-group">
                                        <label>Email Address</label>
                                        <div class="read-only-field"><?php echo $user['email']; ?></div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="full_name">Full Name *</label>
                                    <input type="text" id="full_name" name="full_name" value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="phone">Phone Number</label>
                                        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="address">Address</label>
                                    <textarea id="address" name="address"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                                </div>
                                
                                <button type="submit" name="update_profile" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Save Changes
                                </button>
                            </form>
                            
                            <!-- Account Statistics -->
                            <div class="account-stats">
                                <?php
                                // Get user statistics
                                $stats_sql = "SELECT 
                                    COUNT(b.booking_id) as total_bookings,
                                    SUM(b.total_amount) as total_spent,
                                    COUNT(DISTINCT b.package_id) as packages_booked,
                                    COUNT(r.review_id) as reviews_given
                                    FROM users u
                                    LEFT JOIN bookings b ON u.user_id = b.user_id
                                    LEFT JOIN reviews r ON u.user_id = r.user_id
                                    WHERE u.user_id = '$user_id'";
                                $stats_result = $db->executeQuery($stats_sql);
                                $stats = $stats_result->fetch_assoc();
                                ?>
                                
                                <div class="stat-card">
                                    <div class="stat-icon">
                                        <i class="fas fa-suitcase"></i>
                                    </div>
                                    <div class="stat-number"><?php echo $stats['total_bookings'] ?: 0; ?></div>
                                    <p>Total Bookings</p>
                                </div>
                                
                                <div class="stat-card">
                                    <div class="stat-icon">
                                        <i class="fas fa-rupee-sign"></i>
                                    </div>
                                    <div class="stat-number">₹<?php echo number_format($stats['total_spent'] ?: 0, 0); ?></div>
                                    <p>Total Spent</p>
                                </div>
                                
                                <div class="stat-card">
                                    <div class="stat-icon">
                                        <i class="fas fa-map-marked-alt"></i>
                                    </div>
                                    <div class="stat-number"><?php echo $stats['packages_booked'] ?: 0; ?></div>
                                    <p>Destinations</p>
                                </div>
                                
                                <div class="stat-card">
                                    <div class="stat-icon">
                                        <i class="fas fa-star"></i>
                                    </div>
                                    <div class="stat-number"><?php echo $stats['reviews_given'] ?: 0; ?></div>
                                    <p>Reviews Given</p>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Change Password Tab -->
                        <div id="password-tab" class="tab-content">
                            <form method="POST" action="" class="profile-form" onsubmit="return validatePasswordForm()">
                                <div class="form-group">
                                    <label for="current_password">Current Password *</label>
                                    <input type="password" id="current_password" name="current_password" required>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="new_password">New Password *</label>
                                        <input type="password" id="new_password" name="new_password" required>
                                        <small class="text-muted">Minimum 6 characters</small>
                                    </div>
                                    <div class="form-group">
                                        <label for="confirm_password">Confirm New Password *</label>
                                        <input type="password" id="confirm_password" name="confirm_password" required>
                                    </div>
                                </div>
                                
                                <div class="alert alert-info">
                                    <h4><i class="fas fa-info-circle"></i> Password Requirements</h4>
                                    <ul style="margin-top: 0.5rem;">
                                        <li>Minimum 6 characters</li>
                                        <li>Use a mix of letters, numbers, and symbols</li>
                                        <li>Avoid common words and personal information</li>
                                    </ul>
                                </div>
                                
                                <button type="submit" name="change_password" class="btn btn-primary">
                                    <i class="fas fa-key"></i> Change Password
                                </button>
                            </form>
                            
                            <!-- Security Tips -->
                            <div class="alert alert-info" style="margin-top: 2rem;">
                                <h4><i class="fas fa-shield-alt"></i> Security Tips</h4>
                                <ul style="margin-top: 0.5rem;">
                                    <li>Use a unique password for this account</li>
                                    <li>Change your password regularly</li>
                                    <li>Never share your password with anyone</li>
                                    <li>Log out from shared computers</li>
                                </ul>
                            </div>
                        </div>
                        
                        <!-- Preferences Tab -->
                        <div id="preferences-tab" class="tab-content">
                            <form method="POST" action="" class="profile-form">
                                <h3>Notification Preferences</h3>
                                
                                <div class="preference-group" style="margin-bottom: 1.5rem;">
                                    <label style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                                        <input type="checkbox" checked>
                                        <span>Booking confirmations and updates</span>
                                    </label>
                                    
                                    <label style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                                        <input type="checkbox" checked>
                                        <span>Payment receipts and invoices</span>
                                    </label>
                                    
                                    <label style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                                        <input type="checkbox" checked>
                                        <span>Travel reminders and alerts</span>
                                    </label>
                                    
                                    <label style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                                        <input type="checkbox">
                                        <span>Promotional offers and discounts</span>
                                    </label>
                                    
                                    <label style="display: flex; align-items: center; gap: 1rem;">
                                        <input type="checkbox" checked>
                                        <span>Newsletter and travel tips</span>
                                    </label>
                                </div>
                                
                                <h3>Communication Preferences</h3>
                                <div class="preference-group" style="margin-bottom: 1.5rem;">
                                    <label style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                                        <input type="radio" name="communication" value="email" checked>
                                        <span>Email only</span>
                                    </label>
                                    
                                    <label style="display: flex; align-items: center; gap: 1rem; margin-bottom: 1rem;">
                                        <input type="radio" name="communication" value="sms">
                                        <span>SMS only</span>
                                    </label>
                                    
                                    <label style="display: flex; align-items: center; gap: 1rem;">
                                        <input type="radio" name="communication" value="both">
                                        <span>Both email and SMS</span>
                                    </label>
                                </div>
                                
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Save Preferences
                                </button>
                            </form>
                        </div>
                        
                        <!-- Account Security Tab -->
                        <div id="account-tab" class="tab-content">
                            <h3>Account Activity</h3>
                            <div class="activity-log" style="margin-top: 1rem;">
                                <div class="activity-item" style="padding: 1rem; border-bottom: 1px solid #eee;">
                                    <div style="display: flex; justify-content: space-between;">
                                        <span><i class="fas fa-sign-in-alt text-success"></i> Last login</span>
                                        <small><?php echo date('d M Y, h:i A'); ?></small>
                                    </div>
                                    <small style="color: #666;">IP: 192.168.1.1 | Browser: Chrome</small>
                                </div>
                                
                                <div class="activity-item" style="padding: 1rem; border-bottom: 1px solid #eee;">
                                    <div style="display: flex; justify-content: space-between;">
                                        <span><i class="fas fa-user-edit text-primary"></i> Profile updated</span>
                                        <small><?php echo date('d M Y', strtotime($user['updated_at'])); ?></small>
                                    </div>
                                </div>
                                
                                <div class="activity-item" style="padding: 1rem;">
                                    <div style="display: flex; justify-content: space-between;">
                                        <span><i class="fas fa-user-plus text-info"></i> Account created</span>
                                        <small><?php echo date('d M Y', strtotime($user['created_at'])); ?></small>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Active Sessions -->
                            <h3 style="margin-top: 2rem;">Active Sessions</h3>
                            <div class="sessions-list" style="margin-top: 1rem;">
                                <div class="session-item" style="padding: 1rem; background: #f8f9fa; border-radius: 4px;">
                                    <div style="display: flex; justify-content: space-between; align-items: center;">
                                        <div>
                                            <strong>Current Session</strong><br>
                                            <small>Chrome on Windows | <?php echo date('d M Y, h:i A'); ?></small>
                                        </div>
                                        <span class="text-success"><i class="fas fa-circle"></i> Active</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Danger Zone -->
                            <div class="danger-zone">
                                <h3><i class="fas fa-exclamation-triangle"></i> Danger Zone</h3>
                                <p>These actions are irreversible. Please proceed with caution.</p>
                                
                                <div style="margin-top: 1rem;">
                                    <button class="btn btn-outline" onclick="exportData()">
                                        <i class="fas fa-download"></i> Export My Data
                                    </button>
                                    
                                    <button class="btn btn-danger" onclick="deleteAccount()" style="margin-left: 1rem;">
                                        <i class="fas fa-trash"></i> Delete Account
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </section>
    
    <?php include '../includes/footer.php'; ?>
    
    <script>
    // Tab functionality
    function openTab(tabName) {
        // Hide all tab contents
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.classList.remove('active');
        });
        
        // Remove active class from all tab buttons
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        
        // Show selected tab content
        document.getElementById(tabName + '-tab').classList.add('active');
        
        // Add active class to clicked button
        event.currentTarget.classList.add('active');
    }
    
    // Password validation
    function validatePasswordForm() {
        const currentPassword = document.getElementById('current_password').value;
        const newPassword = document.getElementById('new_password').value;
        const confirmPassword = document.getElementById('confirm_password').value;
        
        // Reset previous errors
        clearErrors();
        
        let isValid = true;
        
        if (!currentPassword) {
            showError('current_password', 'Current password is required');
            isValid = false;
        }
        
        if (!newPassword) {
            showError('new_password', 'New password is required');
            isValid = false;
        } else if (newPassword.length < 6) {
            showError('new_password', 'Password must be at least 6 characters');
            isValid = false;
        }
        
        if (!confirmPassword) {
            showError('confirm_password', 'Please confirm your password');
            isValid = false;
        } else if (newPassword !== confirmPassword) {
            showError('confirm_password', 'Passwords do not match');
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
        
        const inputs = document.querySelectorAll('input');
        inputs.forEach(input => {
            input.style.borderColor = '#ddd';
        });
    }
    
    // Export data
    function exportData() {
        if (confirm('This will export all your data including bookings and personal information. Continue?')) {
            alert('Data export started. You will receive an email with download link.');
            // In production, this would trigger a server-side export process
        }
    }
    
    // Delete account
    function deleteAccount() {
        if (confirm('WARNING: This will permanently delete your account and all associated data. This action cannot be undone. Are you sure?')) {
            const confirmText = prompt('Type "DELETE" to confirm account deletion:');
            if (confirmText === 'DELETE') {
                // Redirect to delete account page
                window.location.href = 'delete-account.php';
            }
        }
    }
    
    // Preview profile image
    document.querySelector('input[name="profile_image"]').addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(event) {
                const preview = document.querySelector('.profile-image-container img') || 
                               document.querySelector('.profile-initials');
                if (preview) {
                    if (preview.classList.contains('profile-initials')) {
                        preview.style.display = 'none';
                        const img = document.createElement('img');
                        img.src = event.target.result;
                        img.style.width = '100%';
                        img.style.height = '100%';
                        img.style.objectFit = 'cover';
                        preview.parentNode.appendChild(img);
                    } else {
                        preview.src = event.target.result;
                    }
                }
            };
            reader.readAsDataURL(file);
        }
    });
    </script>
</body>
</html>