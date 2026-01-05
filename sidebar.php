<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="admin-sidebar">
    <div class="sidebar-header">
        <h3><i class="fas fa-crown"></i> Admin Panel</h3>
        <p><?php echo $_SESSION['admin_username']; ?></p>
    </div>
    
    <nav class="sidebar-nav">
        <ul>
            <li>
                <a href="dashboard.php" class="<?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
            </li>
            
            <li class="nav-section">
                <span class="section-title">Content Management</span>
                <ul>
                    <li>
                        <a href="manage-destinations.php" class="<?php echo $current_page == 'manage-destinations.php' ? 'active' : ''; ?>">
                            <i class="fas fa-map-marker-alt"></i> Destinations
                        </a>
                    </li>
                    
                    <li>
                        <a href="manage-packages.php" class="<?php echo $current_page == 'manage-packages.php' ? 'active' : ''; ?>">
                            <i class="fas fa-suitcase-rolling"></i> Tour Packages
                        </a>
                    </li>
                    <li>
                        <a href="manage-itineraries.php" class="<?php echo $current_page == 'manage-itineraries.php' ? 'active' : ''; ?>">
                            <i class="fas fa-calendar-alt"></i> Itineraries
                        </a>
                    </li>
                </ul>
            </li>
            
            <li class="nav-section">
                <span class="section-title">Booking Management</span>
                <ul>
                    <li>
                        <a href="manage-bookings.php" class="<?php echo $current_page == 'manage-bookings.php' ? 'active' : ''; ?>">
                            <i class="fas fa-shopping-cart"></i> Bookings
                        </a>
                    </li>
                    <li>
                        <a href="manage-payments.php" class="<?php echo $current_page == 'manage-payments.php' ? 'active' : ''; ?>">
                            <i class="fas fa-credit-card"></i> Payments
                        </a>
                    </li>
                </ul>
            </li>
            <li class="nav-section">
                <span class="section-title">User Management</span>
                <ul>
                    <li>
                        <a href="manage-users.php" class="<?php echo $current_page == 'manage-users.php' ? 'active' : ''; ?>">
                            <i class="fas fa-users"></i> Users
                        </a>
                    </li>
                    <li>
                        <a href="manage-reviews.php" class="<?php echo $current_page == 'manage-reviews.php' ? 'active' : ''; ?>">
                            <i class="fas fa-star"></i> Reviews
                        </a>
                    </li>
                </ul>
            </li>
            
           <li class="nav-section">
                <span class="section-title">Settings</span>
                <ul>
                    <li>
                        <a href="settings.php" class="<?php echo $current_page == 'settings.php' ? 'active' : ''; ?>">
                            <i class="fas fa-cog"></i> Site Settings
                        </a>
                    </li>
                    <li>
                        <a href="profile.php" class="<?php echo $current_page == 'profile.php' ? 'active' : ''; ?>">
                            <i class="fas fa-user-cog"></i> Admin Profile
                        </a>
                    </li>
                </ul>
            </li>
            <li>
                <a href="logout.php" class="logout-link">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </li>
        </ul>
    </nav>
</aside> 




