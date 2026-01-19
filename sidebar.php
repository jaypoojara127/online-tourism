<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="dashboard-sidebar">
    <div class="user-profile">
        <?php
        $user_sql = "SELECT * FROM users WHERE user_id = '$user_id'";
        $user_result = $db->executeQuery($user_sql);
        $user = $user_result->fetch_assoc();
        ?>
        <div class="profile-image">
            <?php if(!empty($user['profile_image'])): ?>
            <img src="<?php echo UPLOAD_URL . $user['profile_image']; ?>" alt="<?php echo $user['full_name']; ?>">
            <?php else: ?>
            <div class="profile-initials">
                <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
            </div>
            <?php endif; ?>
        </div>
        <div class="profile-info">
            <h3><?php echo $user['full_name']; ?></h3>
            <p><?php echo $user['email']; ?></p>
        </div>
    </div>
    
    <nav class="dashboard-nav">
        <ul>
            <li><a href="dashboard.php" class="<?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a></li>
            <li><a href="bookings.php" class="<?php echo $current_page == 'bookings.php' ? 'active' : ''; ?>">
                <i class="fas fa-shopping-cart"></i> My Bookings
            </a></li>
            <li><a href="profile.php" class="<?php echo $current_page == 'profile.php' ? 'active' : ''; ?>">
                <i class="fas fa-user"></i> Profile Settings
            </a></li>
            <li><a href="payment-history.php" class="<?php echo $current_page == 'payment-history.php' ? 'active' : ''; ?>">
                <i class="fas fa-credit-card"></i> Payment History
            </a></li>
            <li><a href="reviews.php" class="<?php echo $current_page == 'reviews.php' ? 'active' : ''; ?>">
                <i class="fas fa-star"></i> My Reviews
            </a></li>
            <li><a href="../pages/packages.php">
                <i class="fas fa-plus"></i> Book New Tour
            </a></li>
            <li><a href="../pages/logout.php">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a></li>
        </ul>
    </nav>
</aside>