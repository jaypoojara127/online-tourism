<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

$auth->checkAdminAuth();

// Handle user actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $user_id = $db->escapeString($_GET['id']);
    $action = $db->escapeString($_GET['action']);
    
    if ($action === 'delete') {
        $sql = "DELETE FROM users WHERE user_id = '$user_id'";
        if ($db->executeQuery($sql)) {
            $_SESSION['success'] = "User deleted successfully";
        }
    } elseif ($action === 'activate' || $action === 'deactivate') {
        $status = $action === 'activate' ? 'active' : 'inactive';
        $sql = "UPDATE users SET status = '$status' WHERE user_id = '$user_id'";
        if ($db->executeQuery($sql)) {
            $_SESSION['success'] = "User $action successfully";
        }
    }
    
    header('Location: manage-users.php');
    exit();
}

// Get all users
$sql = "SELECT * FROM users ORDER BY created_at DESC";
$result = $db->executeQuery($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - <?php echo SITE_NAME; ?> Admin</title>
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
                        <h1 class="admin-title">Manage Users</h1>
                        <div class="header-actions">
                            <a href="add-user.php" class="btn btn-primary">
                                <i class="fas fa-user-plus"></i> Add User
                            </a>
                        </div>
                    </div>
                    
                    <?php if(isset($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Users Table -->
                    <div class="admin-card">
                        <div class="admin-card-header">
                            <h3>All Users</h3>
                            <span class="badge badge-primary"><?php echo $result->num_rows; ?> users</span>
                        </div>
                        <div class="admin-card-body">
                            <div class="table-responsive">
                                <table class="admin-table">
                                    <thead>
                                        <tr>
                                            <th>User ID</th>
                                            <th>Profile</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Bookings</th>
                                            <th>Joined</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($user = $result->fetch_assoc()): 
                                            // Get booking count for this user
                                            $booking_sql = "SELECT COUNT(*) as count FROM bookings WHERE user_id = '{$user['user_id']}'";
                                            $booking_result = $db->executeQuery($booking_sql);
                                            $booking_count = $booking_result->fetch_assoc()['count'];
                                        ?>
                                        <tr>
                                            <td>#<?php echo str_pad($user['user_id'], 4, '0', STR_PAD_LEFT); ?></td>
                                            <td>
                                                <?php if(!empty($user['profile_image'])): ?>
                                                <img src="<?php echo UPLOAD_URL . $user['profile_image']; ?>" 
                                                     alt="<?php echo $user['full_name']; ?>" 
                                                     class="table-image rounded-circle">
                                                <?php else: ?>
                                                <div class="avatar-placeholder">
                                                    <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                                                </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong><?php echo $user['full_name']; ?></strong><br>
                                                <small>@<?php echo $user['username']; ?></small>
                                            </td>
                                            <td><?php echo $user['email']; ?></td>
                                            <td><?php echo $user['phone'] ?: 'Not provided'; ?></td>
                                            <td>
                                                <span class="badge badge-info"><?php echo $booking_count; ?> bookings</span>
                                            </td>
                                            <td><?php echo date('d M, Y', strtotime($user['created_at'])); ?></td>
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="view-user.php?id=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-info" title="View">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="edit-user.php?id=<?php echo $user['user_id']; ?>" class="btn btn-sm btn-warning" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="?action=delete&id=<?php echo $user['user_id']; ?>" 
                                                       class="btn btn-sm btn-danger" title="Delete"
                                                       onclick="return confirm('Delete this user? All their bookings will also be deleted.')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                            
                            <?php if($result->num_rows == 0): ?>
                            <div class="empty-state">
                                <i class="fas fa-users"></i>
                                <h3>No Users Found</h3>
                                <p>No users have registered yet.</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- User Statistics -->
                    <div class="stats-grid">
                        <?php
                        // Get user statistics
                        $stats_sql = "SELECT 
                            COUNT(*) as total_users,
                            COUNT(DISTINCT user_id) as active_users,
                            SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today_registrations,
                            AVG(TIMESTAMPDIFF(DAY, created_at, NOW())) as avg_account_age
                            FROM users";
                        $stats_result = $db->executeQuery($stats_sql);
                        $stats = $stats_result->fetch_assoc();
                        ?>
                        
                        <div class="stat-card stat-card-primary">
                            <div class="stat-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $stats['total_users']; ?></h3>
                                <p>Total Users</p>
                            </div>
                        </div>
                        
                        <div class="stat-card stat-card-success">
                            <div class="stat-icon">
                                <i class="fas fa-user-check"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $stats['active_users']; ?></h3>
                                <p>Active Users</p>
                            </div>
                        </div>
                        
                        <div class="stat-card stat-card-warning">
                            <div class="stat-icon">
                                <i class="fas fa-user-plus"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo $stats['today_registrations']; ?></h3>
                                <p>Today's Registrations</p>
                            </div>
                        </div>
                        
                        <div class="stat-card stat-card-info">
                            <div class="stat-icon">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <div class="stat-info">
                                <h3><?php echo round($stats['avg_account_age']); ?> days</h3>
                                <p>Avg. Account Age</p>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="../assets/js/admin.js"></script>
    <style>
    .avatar-placeholder {
        width: 40px;
        height: 40px;
        background: var(--primary-color);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
    }
    
    .rounded-circle {
        border-radius: 50% !important;
    }
    </style>
</body>

</html>

