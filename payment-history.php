<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$auth->checkUserAuth();
$user_id = $_SESSION['user_id'];

// Get payment history
$sql = "SELECT p.*, b.package_id, b.travel_date, b.num_travelers,
               pk.package_name, pk.featured_image,
               d.name as destination_name
        FROM payments p
        JOIN bookings b ON p.booking_id = b.booking_id
        JOIN tour_packages pk ON b.package_id = pk.package_id
        JOIN destinations d ON pk.destination_id = d.destination_id
        WHERE b.user_id = '$user_id'
        ORDER BY p.payment_date DESC";
$result = $db->executeQuery($sql);

// Get payment statistics
$stats_sql = "SELECT 
    COUNT(*) as total_payments,
    SUM(CASE WHEN payment_status = 'completed' THEN amount ELSE 0 END) as total_paid,
    SUM(CASE WHEN payment_status = 'refunded' THEN amount ELSE 0 END) as total_refunded,
    SUM(CASE WHEN payment_status = 'pending' THEN 1 ELSE 0 END) as pending_payments
    FROM payments p
    JOIN bookings b ON p.booking_id = b.booking_id
    WHERE b.user_id = '$user_id'";
$stats_result = $db->executeQuery($stats_sql);
$stats = $stats_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment History - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    .payment-history-container {
        background: white;
        border-radius: 10px;
        padding: 2rem;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    
    .payment-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid var(--primary-color);
    }
    
    .payment-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1.5rem;
        margin-bottom: 2rem;
    }
    
    .stat-card {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 1.5rem;
        text-align: center;
        border-left: 4px solid var(--primary-color);
    }
    
    .stat-card.success {
        border-left-color: #2ecc71;
    }
    
    .stat-card.warning {
        border-left-color: #f39c12;
    }
    
    .stat-card.danger {
        border-left-color: #e74c3c;
    }
    
    .stat-number {
        font-size: 2rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
    }
    
    .stat-label {
        color: #666;
        font-size: 0.9rem;
    }
    
    .payment-filters {
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
    
    .payments-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 1rem;
    }
    
    .payments-table th {
        background: #f8f9fa;
        padding: 1rem;
        text-align: left;
        font-weight: 600;
        color: var(--dark-color);
        border-bottom: 2px solid #dee2e6;
    }
    
    .payments-table td {
        padding: 1rem;
        border-bottom: 1px solid #eee;
        vertical-align: middle;
    }
    
    .payments-table tr:hover {
        background: #f8f9fa;
    }
    
    .payment-image {
        width: 80px;
        height: 80px;
        border-radius: 8px;
        overflow: hidden;
    }
    
    .payment-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .payment-status {
        padding: 0.3rem 0.8rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
        display: inline-block;
    }
    
    .status-completed { background: #d4edda; color: #155724; }
    .status-pending { background: #fff3cd; color: #856404; }
    .status-failed { background: #f8d7da; color: #721c24; }
    .status-refunded { background: #cce5ff; color: #004085; }
    
    .payment-method {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .payment-method i {
        font-size: 1.2rem;
        color: var(--primary-color);
    }
    
    .amount-cell {
        font-weight: 600;
        color: var(--primary-color);
    }
    
    .empty-payments {
        text-align: center;
        padding: 3rem;
        color: #666;
    }
    
    .empty-payments i {
        font-size: 4rem;
        margin-bottom: 1rem;
        color: #ddd;
    }
    
    .receipt-btn {
        padding: 0.3rem 0.8rem;
        background: var(--primary-color);
        color: white;
        border-radius: 4px;
        text-decoration: none;
        font-size: 0.85rem;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
    }
    
    .receipt-btn:hover {
        background: #2980b9;
        color: white;
    }
    
    .export-options {
        display: flex;
        gap: 0.5rem;
        margin-top: 2rem;
    }
    
    @media (max-width: 768px) {
        .payments-table {
            display: block;
            overflow-x: auto;
        }
        
        .payment-filters {
            flex-direction: column;
        }
        
        .export-options {
            flex-direction: column;
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
                    <div class="payment-history-container">
                        <div class="payment-header">
                            <h1>Payment History</h1>
                            <div class="header-actions">
                                <button class="btn btn-primary" onclick="exportPayments('pdf')">
                                    <i class="fas fa-download"></i> Export PDF
                                </button>
                            </div>
                        </div>
                        
                        <!-- Payment Statistics -->
                        <div class="payment-stats">
                            <div class="stat-card success">
                                <div class="stat-number">₹<?php echo number_format((float)($stats['total_paid'] ?? 0), 2); ?></div>
                                <div class="stat-label">Total Paid</div>
                            </div>
                            
                            <div class="stat-card">
                                <div class="stat-number"><?php echo $stats['total_payments']; ?></div>
                                <div class="stat-label">Total Payments</div>
                            </div>
                            
                            <div class="stat-card warning">
                                <div class="stat-number"><?php echo $stats['pending_payments']; ?></div>
                                <div class="stat-label">Pending</div>
                            </div>
                            
                            <div class="stat-card danger">
                                <div class="stat-number">₹<?php echo number_format((float)($stats['total_refunded'] ?? 0), 2); ?></div>
                                <div class="stat-label">Total Refunded</div>
                            </div>
                        </div>
                        
                        <!-- Payment Filters -->
                        <div class="payment-filters">
                            <span class="filter-btn active" onclick="filterPayments('all')">All Payments</span>
                            <span class="filter-btn" onclick="filterPayments('completed')">Completed</span>
                            <span class="filter-btn" onclick="filterPayments('pending')">Pending</span>
                            <span class="filter-btn" onclick="filterPayments('failed')">Failed</span>
                            <span class="filter-btn" onclick="filterPayments('refunded')">Refunded</span>
                        </div>
                        
                        <!-- Payments Table -->
                        <?php if($result->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="payments-table" id="paymentsTable">
                                <thead>
                                    <tr>
                                        <th>Package</th>
                                        <th>Transaction ID</th>
                                        <th>Date</th>
                                        <th>Method</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($payment = $result->fetch_assoc()): ?>
                                    <tr data-status="<?php echo $payment['payment_status']; ?>">
                                        <td>
                                            <div style="display: flex; align-items: center; gap: 1rem;">
                                                <div class="payment-image">
                                                    <img src="<?php echo UPLOAD_URL . $payment['featured_image']; ?>" 
                                                         alt="<?php echo $payment['package_name']; ?>">
                                                </div>
                                                <div>
                                                    <strong><?php echo $payment['package_name']; ?></strong><br>
                                                    <small><?php echo $payment['destination_name']; ?></small><br>
                                                    <small>Travelers: <?php echo $payment['num_travelers']; ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <code><?php echo $payment['transaction_id']; ?></code>
                                        </td>
                                        <td><?php echo date('d M, Y', strtotime($payment['payment_date'])); ?></td>
                                        <td>
                                            <div class="payment-method">
                                                <?php 
                                                $method_icons = [
                                                    'credit_card' => 'fa-credit-card',
                                                    'debit_card' => 'fa-credit-card',
                                                    'net_banking' => 'fa-university',
                                                    'upi' => 'fa-mobile-alt',
                                                    'wallet' => 'fa-wallet'
                                                ];
                                                $icon = $method_icons[$payment['payment_method']] ?? 'fa-money-bill-wave';
                                                ?>
                                                <i class="fas <?php echo $icon; ?>"></i>
                                                <span><?php echo ucwords(str_replace('_', ' ', $payment['payment_method'])); ?></span>
                                            </div>
                                        </td>
                                        <td class="amount-cell">
                                            ₹<?php echo number_format($payment['amount'], 2); ?>
                                        </td>
                                        <td>
                                            <span class="payment-status status-<?php echo $payment['payment_status']; ?>">
                                                <?php echo ucfirst($payment['payment_status']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons" style="display: flex; gap: 0.5rem;">
                                                <?php if($payment['payment_status'] == 'completed'): ?>
                                                <a href="download-receipt.php?id=<?php echo $payment['payment_id']; ?>" 
                                                   class="receipt-btn" title="Download Receipt">
                                                    <i class="fas fa-receipt"></i> Receipt
                                                </a>
                                                <?php endif; ?>
                                                
                                                <?php if($payment['payment_status'] == 'pending'): ?>
                                                <a href="../pages/payment.php?booking_id=<?php echo $payment['booking_id']; ?>" 
                                                   class="btn btn-sm btn-success" title="Complete Payment">
                                                    <i class="fas fa-credit-card"></i> Pay
                                                </a>
                                                <?php endif; ?>
                                                
                                                <button class="btn btn-sm btn-outline" 
                                                        onclick="viewPaymentDetails(<?php echo $payment['payment_id']; ?>)"
                                                        title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Export Options -->
                        <div class="export-options">
                            <button class="btn btn-outline" onclick="exportPayments('csv')">
                                <i class="fas fa-file-csv"></i> Export as CSV
                            </button>
                            <button class="btn btn-outline" onclick="exportPayments('excel')">
                                <i class="fas fa-file-excel"></i> Export as Excel
                            </button>
                            <button class="btn btn-outline" onclick="printPayments()">
                                <i class="fas fa-print"></i> Print
                            </button>
                        </div>
                        
                        <?php else: ?>
                        <div class="empty-payments">
                            <i class="fas fa-credit-card"></i>
                            <h3>No Payment History</h3>
                            <p>You haven't made any payments yet. Book a tour to get started!</p>
                            <a href="../pages/packages.php" class="btn btn-primary" style="margin-top: 1rem;">
                                <i class="fas fa-search"></i> Browse Packages
                            </a>
                        </div>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Payment Insights -->
                    <?php if($result->num_rows > 0): ?>
                    <div class="dashboard-section" style="margin-top: 2rem;">
                        <h3>Payment Insights</h3>
                        <div class="insights-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; margin-top: 1rem;">
                            <div class="insight-card" style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                <h4 style="margin-bottom: 1rem;">Payment Methods</h4>
                                <div id="paymentMethodsChart" style="height: 200px;">
                                    <!-- Chart would be rendered here -->
                                    <div style="text-align: center; padding: 2rem; color: #666;">
                                        <i class="fas fa-chart-pie" style="font-size: 3rem; margin-bottom: 1rem; opacity: 0.3;"></i>
                                        <p>Payment method distribution chart</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="insight-card" style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                                <h4 style="margin-bottom: 1rem;">Recent Activity</h4>
                                <div class="activity-list">
                                    <?php
                                    $recent_sql = "SELECT p.*, pk.package_name 
                                                  FROM payments p
                                                  JOIN bookings b ON p.booking_id = b.booking_id
                                                  JOIN tour_packages pk ON b.package_id = pk.package_id
                                                  WHERE b.user_id = '$user_id'
                                                  ORDER BY p.payment_date DESC LIMIT 3";
                                    $recent_result = $db->executeQuery($recent_sql);
                                    while($recent = $recent_result->fetch_assoc()):
                                    ?>
                                    <div class="activity-item" style="display: flex; justify-content: space-between; padding: 0.8rem 0; border-bottom: 1px solid #eee;">
                                        <div>
                                            <strong><?php echo $recent['package_name']; ?></strong><br>
                                            <small><?php echo date('d M, Y', strtotime($recent['payment_date'])); ?></small>
                                        </div>
                                        <div style="text-align: right;">
                                            <span class="payment-status status-<?php echo $recent['payment_status']; ?>" style="font-size: 0.8rem;">
                                                <?php echo ucfirst($recent['payment_status']); ?>
                                            </span><br>
                                            <strong>₹<?php echo number_format($recent['amount'], 2); ?></strong>
                                        </div>
                                    </div>
                                    <?php endwhile; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </main>
            </div>
        </div>
    </section>
    
    <?php include '../includes/footer.php'; ?>
    
    <script>
    // Filter payments
    function filterPayments(status) {
        const rows = document.querySelectorAll('#paymentsTable tbody tr');
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
    
    // Export payments
    function exportPayments(format) {
        let url = 'export-payments.php?format=' + format;
        
        // Add filters if active
        const activeFilter = document.querySelector('.filter-btn.active');
        if (activeFilter && activeFilter.textContent !== 'All Payments') {
            const status = activeFilter.textContent.toLowerCase();
            url += '&status=' + status;
        }
        
        window.location.href = url;
    }
    
    // Print payments
    function printPayments() {
        const printContent = document.querySelector('.payment-history-container').innerHTML;
        const originalContent = document.body.innerHTML;
        
        document.body.innerHTML = `
            <!DOCTYPE html>
            <html>
            <head>
                <title>Payment History - <?php echo SITE_NAME; ?></title>
                <style>
                body { font-family: Arial, sans-serif; padding: 20px; }
                table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                th { background-color: #f2f2f2; }
                .header { text-align: center; margin-bottom: 30px; }
                .footer { margin-top: 30px; text-align: center; font-size: 12px; color: #666; }
                </style>
            </head>
            <body>
                <div class="header">
                    <h1>Payment History</h1>
                    <p><?php echo SITE_NAME; ?> | Printed on: ${new Date().toLocaleDateString()}</p>
                </div>
                ${printContent}
                <div class="footer">
                    <p>This is a computer-generated receipt. No signature required.</p>
                </div>
            </body>
            </html>
        `;
        
        window.print();
        document.body.innerHTML = originalContent;
        location.reload();
    }
    
    // View payment details
    function viewPaymentDetails(paymentId) {
        // In a real app, this would open a modal with payment details
        window.location.href = 'payment-details.php?id=' + paymentId;
    }
    
    // Download receipt
    function downloadReceipt(paymentId) {
        window.location.href = 'generate-receipt.php?id=' + paymentId;
    }
    
    // Search payments
    function searchPayments() {
        const searchInput = document.getElementById('searchPayments');
        const filter = searchInput.value.toLowerCase();
        const rows = document.querySelectorAll('#paymentsTable tbody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        });
    }
    
    // Sort payments
    function sortPayments(column) {
        const table = document.getElementById('paymentsTable');
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));
        
        rows.sort((a, b) => {
            let aValue = a.children[column].textContent;
            let bValue = b.children[column].textContent;
            
            // Handle amounts
            if (column === 4) { // Amount column
                aValue = parseFloat(aValue.replace(/[^0-9.]/g, ''));
                bValue = parseFloat(bValue.replace(/[^0-9.]/g, ''));
                return aValue - bValue;
            }
            
            // Handle dates
            if (column === 2) { // Date column
                aValue = new Date(aValue);
                bValue = new Date(bValue);
                return aValue - bValue;
            }
            
            // Default string comparison
            return aValue.localeCompare(bValue);
        });
        
        // Reorder rows
        rows.forEach(row => tbody.appendChild(row));
    }
    
    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        // Add search box if needed
        const header = document.querySelector('.payment-header');
        const searchBox = document.createElement('div');
        searchBox.className = 'search-box';
        searchBox.innerHTML = `
            <input type="text" id="searchPayments" placeholder="Search payments..." 
                   onkeyup="searchPayments()" style="padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px;">
        `;
        header.appendChild(searchBox);
    });
    </script>
</body>
</html>