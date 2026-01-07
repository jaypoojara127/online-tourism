<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$auth->checkAdminAuth();

// Get dashboard statistics
$sql_users = "SELECT COUNT(*) as total FROM users";
$sql_bookings = "SELECT COUNT(*) as total FROM bookings";
$sql_packages = "SELECT COUNT(*) as total FROM tour_packages";
$sql_revenue = "SELECT SUM(total_amount) as total FROM bookings WHERE booking_status = 'confirmed'";

$result_users = $db->executeQuery($sql_users);
$result_bookings = $db->executeQuery($sql_bookings);
$result_packages = $db->executeQuery($sql_packages);
$result_revenue = $db->executeQuery($sql_revenue);

$total_users = $result_users->fetch_assoc()['total'];
$total_bookings = $result_bookings->fetch_assoc()['total'];
$total_packages = $result_packages->fetch_assoc()['total'];
$total_revenue = $result_revenue->fetch_assoc()['total'] ?? 0;

// Get recent bookings
$sql_recent = "SELECT b.*, u.full_name, p.package_name 
               FROM bookings b
               JOIN users u ON b.user_id = u.user_id
               JOIN tour_packages p ON b.package_id = p.package_id
               ORDER BY b.created_at DESC LIMIT 10";
$recent_bookings = $db->executeQuery($sql_recent);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="/online-tourism/assets/css/style.css">
    <link rel="stylesheet" href="/online-tourism/assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="admin-wrapper">
        <!-- Admin Sidebar -->
        <?php include 'includes/sidebar.php'; ?>
        
        <!-- Main Content -->
        <div class="admin-content">
            <!-- Admin Header -->
            <?php include 'includes/header.php'; ?>
            
            <main class="admin-main">
                <div class="container-fluid">
                    <h1 class="admin-title">Dashboard Overview</h1>
                    
                    <!-- Stats Cards -->
                    <div class="stats-grid">
                        <div class="stat-card stat-card-primary">
                            <div class="stat-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $total_users; ?></h3>
                                <p>Total Users</p>
                            </div>
                        </div>
                        
                        <div class="stat-card stat-card-success">
                            <div class="stat-icon">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $total_bookings; ?></h3>
                                <p>Total Bookings</p>
                            </div>
                        </div>
                        
                        <div class="stat-card stat-card-warning">
                            <div class="stat-icon">
                                <i class="fas fa-suitcase-rolling"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $total_packages; ?></h3>
                                <p>Tour Packages</p>
                            </div>
                        </div>
                        
                        <div class="stat-card stat-card-info">
                            <div class="stat-icon">
                                <i class="fas fa-rupee-sign"></i>
                            </div>
                            <div class="stat-info">
                                <h3>₹<?php echo number_format($total_revenue, 2); ?></h3>
                                <p>Total Revenue</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Recent Bookings -->
                    <div class="admin-card">
                        <div class="admin-card-header">
                            <h3>Recent Bookings</h3>
                            <a href="manage-bookings.php" class="btn btn-sm btn-primary">View All</a>
                        </div>
                        <div class="admin-card-body">
                            <div class="table-responsive">
                                <table class="admin-table">
                                    <thead>
                                        <tr>
                                            <th>Booking ID</th>
                                            <th>Customer</th>
                                            <th>Package</th>
                                            <th>Travel Date</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($booking = $recent_bookings->fetch_assoc()): ?>
                                        <tr>
                                            <td>#<?php echo $booking['booking_id']; ?></td>
                                            <td><?php echo $booking['full_name']; ?></td>
                                            <td><?php echo $booking['package_name']; ?></td>
                                            <td><?php echo date('d M, Y', strtotime($booking['travel_date'])); ?></td>
                                            <td>₹<?php echo number_format($booking['total_amount'], 2); ?></td>
                                            <td>
                                                <span class="status-badge status-<?php echo $booking['booking_status']; ?>">
                                                    <?php echo ucfirst($booking['booking_status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <a href="view-booking.php?id=<?php echo $booking['booking_id']; ?>" class="btn btn-sm btn-info">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="admin-card">
                        <div class="admin-card-header">
                            <h3>Quick Actions</h3>
                        </div>
                        <div class="admin-card-body">
                            <div class="quick-actions">
                                <a href="manage-destinations.php?action=add" class="quick-action">
                                    <i class="fas fa-plus"></i>
                                    <span>Add Destination</span>
                                </a>
                                <a href="manage-packages.php?action=add" class="quick-action">
                                    <i class="fas fa-plus"></i>
                                    <span>Add Package</span>
                                </a>
                                <a href="manage-users.php" class="quick-action">
                                    <i class="fas fa-users"></i>
                                    <span>Manage Users</span>
                                </a>
                                <a href="manage-reviews.php" class="quick-action">
                                    <i class="fas fa-star"></i>
                                    <span>Manage Reviews</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="../assets/js/admin.js"></script>
</body>

</html>

