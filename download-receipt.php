<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

$auth->checkUserAuth();
$user_id = $_SESSION['user_id'];

$payment_id = $_GET['id'] ?? 0;
if (!$payment_id) {
    header('Location: payment-history.php');
    exit();
}

// Get payment details
$sql = "SELECT p.*, b.booking_id, b.travel_date, b.num_travelers,
               u.full_name, u.email, u.phone,
               pk.package_name, pk.price_per_person, pk.discount_price,
               d.name as destination_name
        FROM payments p
        JOIN bookings b ON p.booking_id = b.booking_id
        JOIN users u ON b.user_id = u.user_id
        JOIN tour_packages pk ON b.package_id = pk.package_id
        JOIN destinations d ON pk.destination_id = d.destination_id
        WHERE p.payment_id = '$payment_id' AND b.user_id = '$user_id'";
$result = $db->executeQuery($sql);

if ($result->num_rows == 0) {
    header('Location: payment-history.php');
    exit();
}

$payment = $result->fetch_assoc();

// Generate PDF receipt
require_once '../includes/fpdf/fpdf.php';

$pdf = new FPDF();
$pdf->AddPage();

// Header
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, SITE_NAME, 0, 1, 'C');
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, 'Payment Receipt', 0, 1, 'C');
$pdf->Ln(10);

// Receipt Details
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Receipt Details', 0, 1);
$pdf->SetFont('Arial', '', 10);

$pdf->Cell(50, 8, 'Receipt Number:', 0, 0);
$pdf->Cell(0, 8, 'RCPT-' . str_pad($payment['payment_id'], 6, '0', STR_PAD_LEFT), 0, 1);

$pdf->Cell(50, 8, 'Transaction ID:', 0, 0);
$pdf->Cell(0, 8, $payment['transaction_id'], 0, 1);

$pdf->Cell(50, 8, 'Date:', 0, 0);
$pdf->Cell(0, 8, date('d M, Y', strtotime($payment['payment_date'])), 0, 1);

$pdf->Ln(5);

// Customer Details
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Customer Details', 0, 1);
$pdf->SetFont('Arial', '', 10);

$pdf->Cell(50, 8, 'Name:', 0, 0);
$pdf->Cell(0, 8, $payment['full_name'], 0, 1);

$pdf->Cell(50, 8, 'Email:', 0, 0);
$pdf->Cell(0, 8, $payment['email'], 0, 1);

$pdf->Cell(50, 8, 'Phone:', 0, 0);
$pdf->Cell(0, 8, $payment['phone'], 0, 1);

$pdf->Ln(5);

// Booking Details
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Booking Details', 0, 1);
$pdf->SetFont('Arial', '', 10);

$pdf->Cell(50, 8, 'Package:', 0, 0);
$pdf->Cell(0, 8, $payment['package_name'], 0, 1);

$pdf->Cell(50, 8, 'Destination:', 0, 0);
$pdf->Cell(0, 8, $payment['destination_name'], 0, 1);

$pdf->Cell(50, 8, 'Travel Date:', 0, 0);
$pdf->Cell(0, 8, date('d M, Y', strtotime($payment['travel_date'])), 0, 1);

$pdf->Cell(50, 8, 'Travelers:', 0, 0);
$pdf->Cell(0, 8, $payment['num_travelers'] . ' person(s)', 0, 1);

$pdf->Ln(5);

// Payment Details
$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(0, 10, 'Payment Details', 0, 1);
$pdf->SetFont('Arial', '', 10);

$pdf->Cell(50, 8, 'Amount Paid:', 0, 0);
$pdf->Cell(0, 8, '₹' . number_format($payment['amount'], 2), 0, 1);

$pdf->Cell(50, 8, 'Payment Method:', 0, 0);
$pdf->Cell(0, 8, ucwords(str_replace('_', ' ', $payment['payment_method'])), 0, 1);

$pdf->Cell(50, 8, 'Payment Status:', 0, 0);
$pdf->Cell(0, 8, ucfirst($payment['payment_status']), 0, 1);

$pdf->Ln(10);

// Footer
$pdf->SetFont('Arial', 'I', 10);
$pdf->Cell(0, 10, 'Thank you for choosing ' . SITE_NAME . '!', 0, 1, 'C');
$pdf->Cell(0, 10, 'For any queries, contact: support@tourism.com', 0, 1, 'C');

// Output PDF
$pdf->Output('I', 'Receipt-' . $payment['payment_id'] . '.pdf');
exit();
?>