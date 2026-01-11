<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$auth->checkAdminAuth();

// Handle payment actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $payment_id = $db->escapeString($_GET['id']);
    $action = $db->escapeString($_GET['action']);
    
    $valid_statuses = ['pending', 'completed', 'failed', 'refunded'];
    
    if (in_array($action, $valid_statuses)) {
        $sql = "UPDATE payments SET payment_status = '$action' WHERE payment_id = '$payment_id'";
        if ($db->executeQuery($sql)) {
            $_SESSION['success'] = "Payment status updated to $action";
        }
    }
    
    header('Location: manage-payments.php');
    exit();
}

// Get all payments with filters
$filter_status = $_GET['status'] ?? '';
$filter_method = $_GET['method'] ?? '';
$filter_date = $_GET['date'] ?? '';

$sql = "SELECT py.*, b.booking_id, b.booking_date, b.num_travelers, b.total_amount as booking_amount,
               u.full_name, u.email, u.phone,
               p.package_name,
               d.name as destination_name
        FROM payments py
        JOIN bookings b ON py.booking_id = b.booking_id
        JOIN users u ON b.user_id = u.user_id
        JOIN tour_packages p ON b.package_id = p.package_id
        JOIN destinations d ON p.destination_id = d.destination_id
        WHERE 1=1";

if ($filter_status) {
    $sql .= " AND py.payment_status = '$filter_status'";
}

if ($filter_method) {
    $sql .= " AND py.payment_method = '$filter_method'";
}

if ($filter_date) {
    $sql .= " AND DATE(py.payment_date) = '$filter_date'";
}

$sql .= " ORDER BY py.payment_date DESC";
$result = $db->executeQuery($sql);

if (!$result) {
    // Query failed, show error
    echo "<p>Error loading payments data.</p>";
    $result = null;
}

// Get payment statistics
$stats_sql = "SELECT 
    COUNT(*) as total_payments,
    SUM(CASE WHEN payment_status = 'completed' THEN amount ELSE 0 END) as total_completed,
    SUM(CASE WHEN payment_status = 'pending' THEN amount ELSE 0 END) as total_pending,
    SUM(CASE WHEN payment_status = 'failed' THEN amount ELSE 0 END) as total_failed
    FROM payments";
$stats_result = $db->executeQuery($stats_sql);
$stats = $stats_result ? $stats_result->fetch_assoc() : ['total_payments' => 0, 'total_completed' => 0, 'total_pending' => 0, 'total_failed' => 0];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Payments - <?php echo SITE_NAME; ?> Admin</title>
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
                        <h1 class="admin-title">Manage Payments</h1>
                        <div class="header-actions">
                            <a href="export-bookings.php?format=csv&status=completed" class="btn btn-secondary">
                                <i class="fas fa-download"></i> Export Completed
                            </a>
                        </div>
                    </div>
                    
                    <?php if(isset($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Statistics Cards -->
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-icon">
                                <i class="fas fa-credit-card"></i>
                            </div>
                            <div class="stat-content">
                                <h3><?php echo $stats['total_payments'] ?? 0; ?></h3>
                                <p>Total Payments</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon success">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="stat-content">
                                <h3>₹<?php echo number_format($stats['total_completed'] ?? 0, 2); ?></h3>
                                <p>Completed Amount</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon warning">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="stat-content">
                                <h3>₹<?php echo number_format($stats['total_pending'] ?? 0, 2); ?></h3>
                                <p>Pending Amount</p>
                            </div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-icon danger">
                                <i class="fas fa-times-circle"></i>
                            </div>
                            <div class="stat-content">
                                <h3>₹<?php echo number_format($stats['total_failed'] ?? 0, 2); ?></h3>
                                <p>Failed Amount</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Filters -->
                    <div class="admin-card">
                        <div class="admin-card-header">
                            <h3>Filters</h3>
                        </div>
                        <div class="admin-card-body">
                            <form method="GET" class="filter-form">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="status">Payment Status</label>
                                        <select id="status" name="status" onchange="this.form.submit()">
                                            <option value="">All Status</option>
                                            <option value="pending" <?php echo $filter_status == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="completed" <?php echo $filter_status == 'completed' ? 'selected' : ''; ?>>Completed</option>
                                            <option value="failed" <?php echo $filter_status == 'failed' ? 'selected' : ''; ?>>Failed</option>
                                            <option value="refunded" <?php echo $filter_status == 'refunded' ? 'selected' : ''; ?>>Refunded</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="method">Payment Method</label>
                                        <select id="method" name="method" onchange="this.form.submit()">
                                            <option value="">All Methods</option>
                                            <option value="credit_card" <?php echo $filter_method == 'credit_card' ? 'selected' : ''; ?>>Credit Card</option>
                                            <option value="debit_card" <?php echo $filter_method == 'debit_card' ? 'selected' : ''; ?>>Debit Card</option>
                                            <option value="net_banking" <?php echo $filter_method == 'net_banking' ? 'selected' : ''; ?>>Net Banking</option>
                                            <option value="upi" <?php echo $filter_method == 'upi' ? 'selected' : ''; ?>>UPI</option>
                                            <option value="wallet" <?php echo $filter_method == 'wallet' ? 'selected' : ''; ?>>Wallet</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="date">Payment Date</label>
                                        <input type="date" id="date" name="date" value="<?php echo $filter_date; ?>" onchange="this.form.submit()">
                                    </div>
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <a href="manage-payments.php" class="btn btn-secondary">Clear Filters</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Payments Table -->
                    <div class="admin-card">
                        <div class="admin-card-header">
                            <h3>Payment Records</h3>
                        </div>
                        <div class="admin-card-body">
                            <div class="table-responsive">
                                <table class="admin-table">
                                    <thead>
                                        <tr>
                                            <th>Payment ID</th>
                                            <th>Customer</th>
                                            <th>Package</th>
                                            <th>Amount</th>
                                            <th>Method</th>
                                            <th>Status</th>
                                            <th>Transaction ID</th>
                                            <th>Payment Date</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if ($result && $result->num_rows > 0): ?>
                                            <?php while ($payment = $result->fetch_assoc()): ?>
                                                <tr>
                                                    <td><?php echo $payment['payment_id']; ?></td>
                                                    <td>
                                                        <div>
                                                            <strong><?php echo htmlspecialchars($payment['full_name']); ?></strong><br>
                                                            <small><?php echo htmlspecialchars($payment['email']); ?></small>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <div>
                                                            <strong><?php echo htmlspecialchars($payment['package_name']); ?></strong><br>
                                                            <small><?php echo htmlspecialchars($payment['destination_name']); ?></small>
                                                        </div>
                                                    </td>
                                                    <td>₹<?php echo number_format($payment['amount'], 2); ?></td>
                                                    <td><?php echo ucfirst(str_replace('_', ' ', $payment['payment_method'])); ?></td>
                                                    <td>
                                                        <span class="status-badge status-<?php echo $payment['payment_status']; ?>">
                                                            <?php echo ucfirst($payment['payment_status']); ?>
                                                        </span>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($payment['transaction_id'] ?? 'N/A'); ?></td>
                                                    <td><?php echo date('d M Y H:i', strtotime($payment['payment_date'])); ?></td>
                                                    <td>
                                                        <div class="action-buttons">
                                                            <?php if ($payment['payment_status'] === 'pending'): ?>
                                                                <a href="?action=completed&id=<?php echo $payment['payment_id']; ?>" 
                                                                   class="btn btn-sm btn-success" 
                                                                   onclick="return confirm('Mark this payment as completed?')">
                                                                    <i class="fas fa-check"></i> Complete
                                                                </a>
                                                                <a href="?action=failed&id=<?php echo $payment['payment_id']; ?>" 
                                                                   class="btn btn-sm btn-danger" 
                                                                   onclick="return confirm('Mark this payment as failed?')">
                                                                    <i class="fas fa-times"></i> Fail
                                                                </a>
                                                            <?php elseif ($payment['payment_status'] === 'completed'): ?>
                                                                <a href="?action=refunded&id=<?php echo $payment['payment_id']; ?>" 
                                                                   class="btn btn-sm btn-warning" 
                                                                   onclick="return confirm('Mark this payment as refunded?')">
                                                                    <i class="fas fa-undo"></i> Refund
                                                                </a>
                                                            <?php endif; ?>
                                                        </div>
                                                    </td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php else: ?>
                                            <tr>
                                                <td colspan="9" class="text-center">No payments found.</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
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


