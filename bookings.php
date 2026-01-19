<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$auth->checkUserAuth();
$user_id = $_SESSION['user_id'];

// Get all user bookings
$sql = "SELECT b.*, p.package_name, p.featured_image, 
               d.name as destination_name,
               py.payment_status, py.transaction_id
        FROM bookings b
        JOIN tour_packages p ON b.package_id = p.package_id
        JOIN destinations d ON p.destination_id = d.destination_id
        LEFT JOIN payments py ON b.booking_id = py.booking_id
        WHERE b.user_id = '$user_id'
        ORDER BY b.created_at DESC";
$result = $db->executeQuery($sql);

// Handle cancellation request
if (isset($_GET['cancel']) && is_numeric($_GET['cancel'])) {
    $booking_id = $db->escapeString($_GET['cancel']);
    
    // Check if booking belongs to user
    $check_sql = "SELECT booking_id FROM bookings WHERE booking_id = '$booking_id' AND user_id = '$user_id'";
    $check_result = $db->executeQuery($check_sql);
    
    if ($check_result->num_rows == 1) {
        // Update booking status
        $update_sql = "UPDATE bookings SET booking_status = 'cancelled' WHERE booking_id = '$booking_id'";
        if ($db->executeQuery($update_sql)) {
            $_SESSION['success'] = 'Booking cancelled successfully';
            header('Location: bookings.php');
            exit();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    .bookings-container {
        background: white;
        border-radius: 10px;
        padding: 2rem;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    
    .bookings-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid var(--primary-color);
    }
    
    .bookings-filter {
        display: flex;
        gap: 1rem;
        margin-bottom: 2rem;
        flex-wrap: wrap;
    }
    
    .filter-btn {
        padding: 0.5rem 1.5rem;
        background: #f8f9fa;
        border: 1px solid #ddd;
        border-radius: 20px;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .filter-btn:hover,
    .filter-btn.active {
        background: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
    }
    
    .bookings-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 1rem;
    }
    
    .bookings-table th {
        background: #f8f9fa;
        padding: 1rem;
        text-align: left;
        font-weight: 600;
        color: var(--dark-color);
        border-bottom: 2px solid #dee2e6;
    }
    
    .bookings-table td {
        padding: 1rem;
        border-bottom: 1px solid #eee;
        vertical-align: middle;
    }
    
    .bookings-table tr:hover {
        background: #f8f9fa;
    }
    
    .booking-image {
        width: 80px;
        height: 80px;
        border-radius: 8px;
        overflow: hidden;
    }
    
    .booking-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .booking-actions {
        display: flex;
        gap: 0.5rem;
        flex-wrap: wrap;
    }
    
    .action-btn {
        padding: 0.3rem 0.8rem;
        border-radius: 4px;
        font-size: 0.85rem;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
    }
    
    .empty-bookings {
        text-align: center;
        padding: 3rem;
        color: #666;
    }
    
    .empty-bookings i {
        font-size: 4rem;
        margin-bottom: 1rem;
        color: #ddd;
    }
    
    .booking-status-badge {
        padding: 0.3rem 0.8rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        display: inline-block;
    }
    
    .status-pending { background: #fff3cd; color: #856404; }
    .status-confirmed { background: #d4edda; color: #155724; }
    .status-cancelled { background: #f8d7da; color: #721c24; }
    .status-completed { background: #cce5ff; color: #004085; }
    
    .payment-status-badge {
        padding: 0.3rem 0.8rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        display: inline-block;
    }
    
    .payment-pending { background: #fff3cd; color: #856404; }
    .payment-completed { background: #d4edda; color: #155724; }
    .payment-failed { background: #f8d7da; color: #721c24; }
    .payment-refunded { background: #cce5ff; color: #004085; }
    
    .amount-cell {
        font-weight: 600;
        color: var(--primary-color);
    }
    
    @media (max-width: 768px) {
        .bookings-table {
            display: block;
            overflow-x: auto;
        }
        
        .booking-actions {
            flex-direction: column;
        }
        
        .action-btn {
            justify-content: center;
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
                    <div class="bookings-container">
                        <div class="bookings-header">
                            <h1>My Bookings</h1>
                            <a href="../pages/packages.php" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Book New Tour
                            </a>
                        </div>
                        
                        <?php if(isset($_SESSION['success'])): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if(isset($_SESSION['error'])): ?>
                        <div class="alert alert-error">
                            <i class="fas fa-exclamation-circle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                        </div>
                        <?php endif; ?>
                        
                        <!-- Filter Buttons -->
                        <div class="bookings-filter">
                            <span class="filter-btn active" onclick="filterBookings('all')">All Bookings</span>
                            <span class="filter-btn" onclick="filterBookings('pending')">Pending</span>
                            <span class="filter-btn" onclick="filterBookings('confirmed')">Confirmed</span>
                            <span class="filter-btn" onclick="filterBookings('completed')">Completed</span>
                            <span class="filter-btn" onclick="filterBookings('cancelled')">Cancelled</span>
                        </div>
                        
                        <?php if($result->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="bookings-table" id="bookingsTable">
                                <thead>
                                    <tr>
                                        <th>Package</th>
                                        <th>Travel Date</th>
                                        <th>Travelers</th>
                                        <th>Amount</th>
                                        <th>Booking Status</th>
                                        <th>Payment Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($booking = $result->fetch_assoc()): ?>
                                    <tr data-status="<?php echo $booking['booking_status']; ?>">
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 1rem;">
                                                <div class="booking-image">
                                                    <img src="<?php echo UPLOAD_URL . $booking['featured_image']; ?>" alt="<?php echo $booking['package_name']; ?>">
                                                </div>
                                                <div>
                                                    <strong><?php echo $booking['package_name']; ?></strong><br>
                                                    <small><?php echo $booking['destination_name']; ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td><?php echo date('d M, Y', strtotime($booking['travel_date'])); ?></td>
                                        <td><?php echo $booking['num_travelers']; ?> person(s)</td>
                                        <td class="amount-cell">₹<?php echo number_format($booking['total_amount'], 2); ?></td>
                                        <td>
                                            <span class="booking-status-badge status-<?php echo $booking['booking_status']; ?>">
                                                <?php echo ucfirst($booking['booking_status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if($booking['payment_status']): ?>
                                            <span class="payment-status-badge payment-<?php echo $booking['payment_status']; ?>">
                                                <?php echo ucfirst($booking['payment_status']); ?>
                                            </span>
                                            <?php else: ?>
                                            <span class="payment-status-badge payment-pending">Not Paid</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="booking-actions">
                                                <a href="view-booking.php?id=<?php echo $booking['booking_id']; ?>" class="action-btn btn-info">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                                
                                                <?php if($booking['booking_status'] == 'pending' && (!$booking['payment_status'] || $booking['payment_status'] == 'pending')): ?>
                                                <a href="../pages/payment.php?booking_id=<?php echo $booking['booking_id']; ?>" class="action-btn btn-success">
                                                    <i class="fas fa-credit-card"></i> Pay
                                                </a>
                                                <?php endif; ?>
                                                
                                                <?php if($booking['booking_status'] == 'pending' || $booking['booking_status'] == 'confirmed'): ?>
                                                <a href="bookings.php?cancel=<?php echo $booking['booking_id']; ?>" 
                                                   class="action-btn btn-danger"
                                                   onclick="return confirm('Are you sure you want to cancel this booking?')">
                                                    <i class="fas fa-times"></i> Cancel
                                                </a>
                                                <?php endif; ?>
                                                
                                                <?php if($booking['booking_status'] == 'completed' && (!$booking['payment_status'] || $booking['payment_status'] == 'pending')): ?>
                                                <a href="../pages/review.php?booking_id=<?php echo $booking['booking_id']; ?>" class="action-btn btn-warning">
                                                    <i class="fas fa-star"></i> Review
                                                </a>
                                                <?php endif; ?>
                                                
                                                <?php if($booking['payment_status'] == 'completed'): ?>
                                                <a href="download-receipt.php?id=<?php echo $booking['booking_id']; ?>" class="action-btn btn-secondary">
                                                    <i class="fas fa-download"></i> Receipt
                                                </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Booking Statistics -->
                        <div class="booking-stats" style="margin-top: 2rem; padding-top: 2rem; border-top: 1px solid #eee;">
                            <h3>Booking Summary</h3>
                            <div style="display: flex; gap: 2rem; margin-top: 1rem; flex-wrap: wrap;">
                                <?php
                                // Calculate statistics
                                $stats_sql = "SELECT 
                                    COUNT(*) as total,
                                    SUM(CASE WHEN booking_status = 'pending' THEN 1 ELSE 0 END) as pending,
                                    SUM(CASE WHEN booking_status = 'confirmed' THEN 1 ELSE 0 END) as confirmed,
                                    SUM(CASE WHEN booking_status = 'completed' THEN 1 ELSE 0 END) as completed,
                                    SUM(CASE WHEN booking_status = 'cancelled' THEN 1 ELSE 0 END) as cancelled,
                                    SUM(total_amount) as total_amount
                                    FROM bookings WHERE user_id = '$user_id'";
                                $stats_result = $db->executeQuery($stats_sql);
                                $stats = $stats_result->fetch_assoc();
                                ?>
                                
                                <div class="stat-box">
                                    <h4><?php echo $stats['total']; ?></h4>
                                    <p>Total Bookings</p>
                                </div>
                                
                                <div class="stat-box">
                                    <h4>₹<?php echo number_format($stats['total_amount'], 2); ?></h4>
                                    <p>Total Spent</p>
                                </div>
                                
                                <div class="stat-box">
                                    <h4><?php echo $stats['completed']; ?></h4>
                                    <p>Completed Tours</p>
                                </div>
                                
                                <div class="stat-box">
                                    <h4><?php echo $stats['cancelled']; ?></h4>
                                    <p>Cancelled</p>
                                </div>
                            </div>
                        </div>
                        
                        <?php else: ?>
                        <div class="empty-bookings">
                            <i class="fas fa-suitcase"></i>
                            <h3>No Bookings Yet</h3>
                            <p>You haven't made any bookings yet. Start exploring our amazing tour packages!</p>
                            <a href="../pages/packages.php" class="btn btn-primary" style="margin-top: 1rem;">
                                <i class="fas fa-search"></i> Browse Packages
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                </main>
            </div>
        </div>
    </section>
    
    <?php include '../includes/footer.php'; ?>
    
    <script>
    // Filter bookings
    function filterBookings(status) {
        const rows = document.querySelectorAll('#bookingsTable tbody tr');
        const filterBtns = document.querySelectorAll('.filter-btn');
        
        // Update active filter button
        filterBtns.forEach(btn => btn.classList.remove('active'));
        event.target.classList.add('active');
        
        // Filter rows
        rows.forEach(row => {
            if (status === 'all' || row.dataset.status === status) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }
    
    // Print booking list
    function printBookings() {
        window.print();
    }
    
    // Export bookings to CSV
    function exportBookings() {
        const table = document.getElementById('bookingsTable');
        const rows = table.querySelectorAll('tr');
        let csv = [];
        
        rows.forEach(row => {
            const rowData = [];
            const cells = row.querySelectorAll('th, td');
            
            cells.forEach(cell => {
                // Skip action cells
                if (!cell.querySelector('.booking-actions')) {
                    rowData.push(`"${cell.textContent.trim().replace(/"/g, '""')}"`);
                }
            });
            
            csv.push(rowData.join(','));
        });
        
        const csvContent = csv.join('\n');
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        
        link.href = URL.createObjectURL(blob);
        link.download = 'my-bookings.csv';
        link.style.display = 'none';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
    </script>
</body>
</html>