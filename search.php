<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

$auth->checkAdminAuth();

// Placeholder for search functionality
$query = $_GET['q'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - Admin</title>
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
                    <h1>Search Results for "<?php echo htmlspecialchars($query); ?>"</h1>
                    <p>Search functionality coming soon...</p>
                    <a href="dashboard.php" class="btn btn-primary">Back to Dashboard</a>
                </div>
            </main>
        </div>
    </div>
    <script src="../assets/js/admin.js"></script>
</body>
</html>
