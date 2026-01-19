<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$auth->checkUserAuth();
$user_id = $_SESSION['user_id'];

// Get user info
$user_sql = "SELECT * FROM users WHERE user_id = '$user_id'";
$user_result = $db->executeQuery($user_sql);
$user = $user_result->fetch_assoc();

// Get user bookings
$bookings_sql = "SELECT b.*, p.package_name, p.featured_image 
                 FROM bookings b
                 JOIN tour_packages p ON b.package_id = p.package_id
                 WHERE b.user_id = '$user_id'
                 ORDER BY b.created_at DESC LIMIT 5";
$bookings_result = $db->executeQuery($bookings_sql);

// Count total bookings
$count_sql = "SELECT COUNT(*) as total FROM bookings WHERE user_id = '$user_id'";
$count_result = $db->executeQuery($count_sql);
$total_bookings = $count_result->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="/online-tourism/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <section class="dashboard">
        <div class="container">
            <div class="dashboard-grid">
                <!-- Sidebar -->
                <aside class="dashboard-sidebar">
                    <div class="user-profile">
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
                            <li><a href="dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                            <li><a href="bookings.php"><i class="fas fa-shopping-cart"></i> My Bookings</a></li>
                            <li><a href="profile.php"><i class="fas fa-user"></i> Profile Settings</a></li>
                            <li><a href="payment-history.php"><i class="fas fa-credit-card"></i> Payment History</a></li>
                            <li><a href="../pages/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                        </ul>
                    </nav>
                </aside>
                
                <!-- Main Content -->
                <main class="dashboard-content">
                    <div class="dashboard-header">
                        <h1>Welcome, <?php echo $user['full_name']; ?>!</h1>
                        <p>Here's an overview of your account</p>
                    </div>
                    
                    <div class="stats-cards">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-suitcase"></i>
                            </div>
                            <div class="stat-content">
                                <h3><?php echo $total_bookings; ?></h3>
                                <p>Total Bookings</p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="stat-content">
                                <h3>2</h3>
                                <p>Upcoming Trips</p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-star"></i>
                            </div>
                            <div class="stat-content">
                                <h3>5</h3>
                                <p>Reviews Given</p>
                            </div>
                        </div>
                        
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-wallet"></i>
                            </div>
                            <div class="stat-content">
                                <h3>₹15,000</h3>
                                <p>Total Spent</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Recent Bookings -->
                    <div class="dashboard-section">
                        <div class="section-header">
                            <h2>Recent Bookings</h2>
                            <a href="bookings.php" class="btn btn-primary">View All</a>
                        </div>
                        
                        <?php if($bookings_result->num_rows > 0): ?>
                        <div class="bookings-list">
                            <?php while($booking = $bookings_result->fetch_assoc()): ?>
                            <div class="booking-card">
                                <div class="booking-image">
                                    <?php if(!empty($booking['featured_image'])): ?>
                                    <img src="<?php echo UPLOAD_URL . $booking['featured_image']; ?>" alt="<?php echo $booking['package_name']; ?>">
                                    <?php else: ?>
                                    <div class="no-image">No Image</div>
                                    <?php endif; ?>
                                </div>
                                <div class="booking-details">
                                    <h3><?php echo $booking['package_name']; ?></h3>
                                    <div class="booking-info">
                                        <p><i class="fas fa-calendar-alt"></i> Travel Date: <?php echo date('d M, Y', strtotime($booking['travel_date'])); ?></p>
                                        <p><i class="fas fa-users"></i> Travelers: <?php echo $booking['num_travelers']; ?></p>
                                        <p><i class="fas fa-rupee-sign"></i> Amount: ₹<?php echo number_format($booking['total_amount'], 2); ?></p>
                                    </div>
                                    <div class="booking-status">
                                        <span class="status-badge status-<?php echo $booking['booking_status']; ?>">
                                            <?php echo ucfirst($booking['booking_status']); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="booking-actions">
                                    <a href="view-booking.php?id=<?php echo $booking['booking_id']; ?>" class="btn btn-sm btn-primary">View Details</a>
                                    <?php if($booking['booking_status'] == 'pending'): ?>
                                    <a href="../pages/payment.php?booking_id=<?php echo $booking['booking_id']; ?>" class="btn btn-sm btn-success">Pay Now</a>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                        <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-suitcase"></i>
                            <h3>No Bookings Yet</h3>
                            <p>You haven't made any bookings yet. Start exploring our tour packages!</p>
                            <a href="../pages/packages.php" class="btn btn-primary">Explore Packages</a>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Quick Links -->
                    <div class="dashboard-section">
                        <h2>Quick Actions</h2>
                        <div class="quick-links">
                            <a href="../pages/packages.php" class="quick-link">
                                <i class="fas fa-search"></i>
                                <span>Browse Packages</span>
                            </a>
                            <a href="profile.php" class="quick-link">
                                <i class="fas fa-user-edit"></i>
                                <span>Update Profile</span>
                            </a>
                            <a href="../pages/contact.php" class="quick-link">
                                <i class="fas fa-headset"></i>
                                <span>Contact Support</span>
                            </a>
                            <a href="#" class="quick-link">
                                <i class="fas fa-question-circle"></i>
                                <span>Help Center</span>
                            </a>
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </section>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>