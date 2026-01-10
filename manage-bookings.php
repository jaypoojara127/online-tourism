<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$auth->checkAdminAuth();

// Handle booking actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $booking_id = $db->escapeString($_GET['id']);
    $action = $db->escapeString($_GET['action']);
    
    $valid_statuses = ['confirmed', 'cancelled', 'completed'];
    
    if (in_array($action, $valid_statuses)) {
        $sql = "UPDATE bookings SET booking_status = '$action' WHERE booking_id = '$booking_id'";
        if ($db->executeQuery($sql)) {
            $_SESSION['success'] = "Booking status updated to $action";
        }
    }
    
    header('Location: manage-bookings.php');
    exit();
}

// Get all bookings with filters
$filter_status = $_GET['status'] ?? '';
$filter_date = $_GET['date'] ?? '';

$sql = "SELECT b.*, u.full_name, u.email, u.phone, 
               p.package_name, p.price_per_person,
               d.name as destination_name,
               py.payment_status, py.transaction_id
        FROM bookings b
        JOIN users u ON b.user_id = u.user_id
        JOIN tour_packages p ON b.package_id = p.package_id
        JOIN destinations d ON p.destination_id = d.destination_id
        LEFT JOIN payments py ON b.booking_id = py.booking_id
        WHERE 1=1";

if ($filter_status) {
    $sql .= " AND b.booking_status = '$filter_status'";
}

if ($filter_date) {
    $sql .= " AND DATE(b.created_at) = '$filter_date'";
}

$sql .= " ORDER BY b.created_at DESC";
$result = $db->executeQuery($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings - <?php echo SITE_NAME; ?> Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="admin-content">
            <?php include 'includes/header.php'; ?>
            
            <main class="admin-main">
                <div class="container-fluid">
                    <div class="admin-header">
                        <h1 class="admin-title">Manage Bookings</h1>
                        <div class="header-actions">
                            <a href="export-bookings.php" class="btn btn-secondary">
                                <i class="fas fa-download"></i> Export
                            </a>
                        </div>
                    </div>
                    
                    <?php if(isset($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Filters -->
                    <div class="admin-card">
                        <div class="admin-card-body">
                            <form method="GET" class="filter-form">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="status">Booking Status</label>
                                        <select id="status" name="status" onchange="this.form.submit()">
                                            <option value="">All Status</option>
                                            <option value="pending" <?php echo $filter_status == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="confirmed" <?php echo $filter_status == 'confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                            <option value="completed" <?php echo $filter_status == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                            <option value="cancelled" <?php echo $filter_status == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="date">Booking Date</label>
                                        <input type="date" id="date" name="date" value="<?php echo $filter_date; ?>" onchange="this.form.submit()">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <a href="manage-bookings.php" class="btn btn-outline">Clear Filters</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Bookings Table -->
                    <div class="admin-card">
                        <div class="admin-card-header">
                            <h3>All Bookings</h3>
                            <span class="badge badge-primary"><?php echo $result->num_rows; ?> bookings</span>
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
                                            <th>Travelers</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Payment</th>
                                            <th>Booked On</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($booking = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td>#<?php echo str_pad($booking['booking_id'], 6, '0', STR_PAD_LEFT); ?></td>
                                            <td>
                                                <strong><?php echo $booking['full_name']; ?></strong><br>
                                                <small><?php echo $booking['email']; ?></small><br>
                                                <small><?php echo $booking['phone']; ?></small>
                                            </td>
                                            <td>
                                                <strong><?php echo $booking['package_name']; ?></strong><br>
                                                <small><?php echo $booking['destination_name']; ?></small>
                                            </td>
                                            <td><?php echo date('d M, Y', strtotime($booking['travel_date'])); ?></td>
                                            <td><?php echo $booking['num_travelers']; ?></td>
                                            <td>₹<?php echo number_format($booking['total_amount'], 2); ?></td>
                                            <td>
                                                <span class="status-badge status-<?php echo $booking['booking_status']; ?>">
                                                    <?php echo ucfirst($booking['booking_status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if($booking['payment_status']): ?>
                                                <span class="payment-status-badge payment-<?php echo $booking['payment_status']; ?>">
                                                    <?php echo ucfirst($booking['payment_status']); ?>
                                                </span>
                                                <?php else: ?>
                                                <span class="badge badge-warning">Not Paid</span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo date('d M, Y', strtotime($booking['created_at'])); ?></td>
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="view-booking.php?id=<?php echo $booking['booking_id']; ?>" class="btn btn-sm btn-info" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    
                                                    <?php if($booking['booking_status'] == 'pending'): ?>
                                                    <a href="?action=confirmed&id=<?php echo $booking['booking_id']; ?>" class="btn btn-sm btn-success" title="Confirm">
                                                        <i class="fas fa-check"></i>
                                                    </a>
                                                    <?php endif; ?>
                                                    
                                                    <?php if($booking['booking_status'] != 'cancelled'): ?>
                                                    <a href="?action=cancelled&id=<?php echo $booking['booking_id']; ?>" 
                                                       class="btn btn-sm btn-danger" title="Cancel"
                                                       onclick="return confirm('Cancel this booking?')">
                                                        <i class="fas fa-times"></i>
                                                    </a>
                                                    <?php endif; ?>
                                                    
                                                    <?php if($booking['booking_status'] == 'confirmed'): ?>
                                                    <a href="?action=completed&id=<?php echo $booking['booking_id']; ?>" class="btn btn-sm btn-primary" title="Mark Complete">
                                                        <i class="fas fa-flag-checkered"></i>
                                                    </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <?php if($result->num_rows == 0): ?>
                            <div class="empty-state">
                                <i class="fas fa-shopping-cart"></i>
                                <h3>No Bookings Found</h3>
                                <p>No bookings match your filters.</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="../assets/js/admin.js"></script>
</body>
</html>