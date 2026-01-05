<header class="admin-header">

    <div class="header-content">
        <button class="sidebar-toggle">
            <i class="fas fa-bars"></i>
        </button>
        
        <div class="header-search">
            <form action="search.php" method="GET">
                <input type="text" name="q" placeholder="Search...">
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>
        </div>
        
        <div class="header-actions">
            <div class="notification-dropdown">
                <button class="notification-btn">
                    <i class="fas fa-bell"></i>
                    <span class="notification-count">3</span>
                </button>
                <div class="notification-dropdown-content">
                    <div class="notification-header">
                        <h4>Notifications</h4>
                    </div>
                    <div class="notification-list">
                        <a href="#" class="notification-item">
                            <i class="fas fa-shopping-cart text-success"></i>
                            <div>
                                <p>New booking received</p>
                                <small>2 minutes ago</small>
                            </div>
                        </a>
                        <a href="#" class="notification-item">
                            <i class="fas fa-user-plus text-info"></i>
                            <div>
                                <p>New user registered</p>
                                <small>10 minutes ago</small>
                            </div>
                        </a>
                        <a href="#" class="notification-item">
                            <i class="fas fa-star text-warning"></i>
                            <div>
                                <p>New review submitted</p>
                                <small>1 hour ago</small>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
            
            <div class="user-dropdown">
                <button class="user-btn">
                    <div class="user-avatar">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <span><?php echo $_SESSION['admin_username']; ?></span>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="user-dropdown-content">
                    <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
                    <a href="settings.php"><i class="fas fa-cog"></i> Settings</a>
                    <div class="dropdown-divider"></div>
                    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
        </div>
    </div>
</header>



