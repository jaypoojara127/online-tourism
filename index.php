<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

if ($auth->isAdminLoggedIn()) {
    header('Location: ' . SITE_URL . 'admin/dashboard.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    if (empty($username) || empty($password)) {
        $error = 'Please enter username and password';
    } else {
        $result = $auth->adminLogin($username, $password);
        
        if ($result['success']) {
            $success = $result['message'];
            header('refresh:1;url=' . SITE_URL . 'admin/dashboard.php');
        } else {
            $error = $result['message'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="/online-tourism/assets/css/style.css">
    <link rel="stylesheet" href="/online-tourism/assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="admin-login-page">
    <div class="admin-login-container">
        <div class="admin-login-box">
            <div class="login-header">
                <h1><i class="fas fa-crown"></i> <?php echo SITE_NAME; ?></h1>
                <p>Administrator Panel</p>
            </div>
            
            <?php if($error): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="" class="admin-login-form">
                <div class="form-group">
                    <label for="username"><i class="fas fa-user"></i> Username</label>
                    <input type="text" id="username" name="username" required autofocus>
                </div>
                
                <div class="form-group">
                    <label for="password"><i class="fas fa-lock"></i> Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">
                    <i class="fas fa-sign-in-alt"></i> Login as Admin
                </button>
                
                <div class="back-to-site">
                    <a href="../index.php"><i class="fas fa-arrow-left"></i> Back to Website</a>
                </div>
            </form>
        </div>
    </div>
</body>

</html>
