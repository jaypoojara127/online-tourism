<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
    <header class="header">
        <div class="container">
            <nav class="navbar">
                <a href="<?php echo SITE_URL; ?>" class="logo">
                    <span>Explore</span>World
                </a>
                
                <ul class="nav-links">
                    <li><a href="<?php echo SITE_URL; ?>" class="<?php echo $current_page == 'index.php' ? 'active' : ''; ?>">Home</a></li>
                    <li><a href="<?php echo SITE_URL; ?>pages/destinations.php" class="<?php echo $current_page == 'destinations.php' ? 'active' : ''; ?>">Destinations</a></li>
                    <li><a href="<?php echo SITE_URL; ?>pages/packages.php" class="<?php echo $current_page == 'packages.php' ? 'active' : ''; ?>">Tour Packages</a></li>
                    <li><a href="<?php echo SITE_URL; ?>pages/contact.php" class="<?php echo $current_page == 'contact.php' ? 'active' : ''; ?>">Contact</a></li>
                    
                    <?php if (isset($_SESSION['user_id'])): ?>
                    <li><a href="<?php echo SITE_URL; ?>user/dashboard.php" class="<?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">Dashboard</a></li>
                    <?php endif; ?>
                    
                    <?php if (isset($_SESSION['admin_id'])): ?>
                    <li><a href="<?php echo SITE_URL; ?>admin/dashboard.php" class="<?php echo $current_page == 'dashboard.php' ? 'active' : ''; ?>">Admin Panel</a></li>
                    <?php endif; ?>
                </ul>
                
                <div class="user-menu">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <span class="welcome-text">Hi, <?php echo $_SESSION['full_name']; ?></span>
                        <a href="<?php echo SITE_URL; ?>user/dashboard.php" class="btn btn-outline">
                            <i class="fas fa-user-circle"></i> Dashboard
                        </a>
                        <a href="<?php echo SITE_URL; ?>pages/logout.php" class="btn btn-outline">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    <?php elseif (isset($_SESSION['admin_id'])): ?>
                        <span class="welcome-text">Admin: <?php echo $_SESSION['admin_username']; ?></span>
                        <a href="<?php echo SITE_URL; ?>admin/dashboard.php" class="btn btn-outline">
                            <i class="fas fa-cog"></i> Admin
                        </a>
                        <a href="<?php echo SITE_URL; ?>admin/logout.php" class="btn btn-outline">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    <?php else: ?>
                        <a href="<?php echo SITE_URL; ?>pages/login.php" class="btn btn-outline">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                        <a href="<?php echo SITE_URL; ?>pages/register.php" class="btn btn-primary">
                            <i class="fas fa-user-plus"></i> Register
                        </a>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </header>