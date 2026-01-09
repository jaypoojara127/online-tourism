<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

$auth->checkAdminAuth();

$format = $_GET['format'] ?? 'csv';
$status = $_GET['status'] ?? '';

// Get bookings
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

if ($status) {
    $sql .= " AND b.booking_status = '$status'";
}

$sql .= " ORDER BY b.created_at DESC";
$result = $db->executeQuery($sql);

if ($format === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=bookings_' . date('Y-m-d') . '.csv');
    
    $output = fopen('php://output', 'w');
    
    // Header row
    fputcsv($output, array(
        'Booking ID', 'Customer Name', 'Email', 'Phone',
        'Package', 'Destination', 'Travel Date',
        'Travelers', 'Total Amount', 'Booking Status',
        'Payment Status', 'Transaction ID', 'Booked On'
    ));
    
    // Data rows
    while ($booking = $result->fetch_assoc()) {
        fputcsv($output, array(
            $booking['booking_id'],
            $booking['full_name'],
            $booking['email'],
            $booking['phone'],
            $booking['package_name'],
            $booking['destination_name'],
            $booking['travel_date'],
            $booking['num_travelers'],
            $booking['total_amount'],
            $booking['booking_status'],
            $booking['payment_status'] ?? 'Not Paid',
            $booking['transaction_id'] ?? '',
            $booking['created_at']
        ));
    }
    
    fclose($output);
    exit();
}

// For other formats (PDF, Excel), you would use appropriate libraries
echo "Export format $format not implemented yet.";

?>

