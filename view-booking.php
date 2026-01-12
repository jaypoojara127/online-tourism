<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$auth->checkAdminAuth();

$booking_id = $_GET['id'] ?? 0;
$booking = null;

if ($booking_id) {
    $booking_id = $db->escapeString($booking_id);
    $sql = "SELECT b.*, u.full_name, u.email, u.phone, p.package_name, p.price_per_person 
            FROM bookings b
            JOIN users u ON b.user_id = u.user_id
            JOIN tour_packages p ON b.package_id = p.package_id
            WHERE b.booking_id = '$booking_id'";
    $result = $db->executeQuery($sql);
    if ($result->num_rows > 0) {
        $booking = $result->fetch_assoc();
    }
}

if (!$booking) {
    header('Location: dashboard.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Booking #<?php echo $booking_id; ?> - Admin</title>
    <link rel="stylesheet" href="/online-tourism/assets/css/style.css">
    <link rel="stylesheet" href="/online-tourism/assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'includes/sidebar.php'; ?>
        <div class="admin-content">
            <?php include 'includes/header.php'; ?>
            <main class="admin-main">
                <div class="container-fluid">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h1>Booking Details #<?php echo $booking_id; ?></h1>
                        <a href="dashboard.php" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
                    </div>
                    
                    <div class="booking-details-card" style="background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                        <div class="row">
                            <div class="col-md-6" style="margin-bottom: 2rem;">
                                <h3>Customer Information</h3>
                                <p><strong>Name:</strong> <?php echo $booking['full_name']; ?></p>
                                <p><strong>Email:</strong> <?php echo $booking['email']; ?></p>
                                <p><strong>Phone:</strong> <?php echo $booking['phone']; ?></p>
                            </div>
                            <div class="col-md-6" style="margin-bottom: 2rem;">
                                <h3>Package Information</h3>
                                <p><strong>Package:</strong> <?php echo $booking['package_name']; ?></p>
                                <p><strong>Travel Date:</strong> <?php echo date('d M, Y', strtotime($booking['travel_date'])); ?></p>
                                <p><strong>Travelers:</strong> <?php echo $booking['num_travelers']; ?></p>
                            </div>
                        </div>
                        
                        <div class="payment-info" style="border-top: 1px solid #eee; pt-3; mt-3">
                            <h3 style="margin-top: 1rem;">Payment Information</h3>
                            <p><strong>Total Amount:</strong> <span style="font-size: 1.5rem; color: var(--primary-color); font-weight: bold;">₹<?php echo number_format($booking['total_amount'], 2); ?></span></p>
                            <p><strong>Status:</strong> <span class="status-badge status-<?php echo $booking['booking_status']; ?>"><?php echo ucfirst($booking['booking_status']); ?></span></p>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="../assets/js/admin.js"></script>
</body>
</html>
