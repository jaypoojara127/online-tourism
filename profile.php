<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

$auth->checkAdminAuth();

// Get current admin data
$admin_id = $_SESSION['admin_id'];
$sql = "SELECT * FROM admin WHERE admin_id = '$admin_id'";
$result = $db->executeQuery($sql);
$admin = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $db->escapeString($_POST['username']);
    $email = $db->escapeString($_POST['email']);
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    $errors = [];
    
    // Validate username
    if (empty($username)) {
        $errors[] = 'Username is required';
    } elseif ($username !== $admin['username']) {
        // Check if username is already taken
        $check_sql = "SELECT admin_id FROM admin WHERE username = '$username' AND admin_id != '$admin_id'";
        $check_result = $db->executeQuery($check_sql);
        if ($check_result->num_rows > 0) {
            $errors[] = 'Username is already taken';
        }
    }
    
    // Validate email
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    } elseif ($email !== $admin['email']) {
        // Check if email is already taken
        $check_sql = "SELECT admin_id FROM admin WHERE email = '$email' AND admin_id != '$admin_id'";
        $check_result = $db->executeQuery($check_sql);
        if ($check_result->num_rows > 0) {
            $errors[] = 'Email is already taken';
        }
    }
    
    // Validate password change
    if (!empty($new_password)) {
        if (empty($current_password)) {
            $errors[] = 'Current password is required to change password';
        } elseif (!password_verify($current_password, $admin['password'])) {
            $errors[] = 'Current password is incorrect';
        } elseif (strlen($new_password) < 6) {
            $errors[] = 'New password must be at least 6 characters';
        } elseif ($new_password !== $confirm_password) {
            $errors[] = 'New password and confirmation do not match';
        }
    }
    
    if (empty($errors)) {
        // Update admin data
        $update_fields = [];
        $update_fields[] = "username = '$username'";
        $update_fields[] = "email = '$email'";
        
        if (!empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_fields[] = "password = '$hashed_password'";
        }
        
        $update_sql = "UPDATE admin SET " . implode(', ', $update_fields) . " WHERE admin_id = '$admin_id'";
        
        if ($db->executeQuery($update_sql)) {
            $_SESSION['admin_username'] = $username;
            $_SESSION['success'] = 'Profile updated successfully';
            
            // Refresh admin data
            $result = $db->executeQuery($sql);
            $admin = $result->fetch_assoc();
        } else {
            $_SESSION['error'] = 'Error updating profile';
        }
        
        header('Location: profile.php');
        exit();
    } else {
        $_SESSION['errors'] = $errors;
        header('Location: profile.php');
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile - Admin</title>
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
                    <div class="d-flex justify-between align-center mb-4">
                        <h1>Admin Profile</h1>
                    </div>

                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success">
                            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="alert alert-error">
                            <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_SESSION['errors'])): ?>
                        <div class="alert alert-error">
                            <ul>
                                <?php foreach ($_SESSION['errors'] as $error): ?>
                                    <li><?php echo $error; ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <?php unset($_SESSION['errors']); ?>
                        </div>
                    <?php endif; ?>

                    <div class="card">
                        <div class="card-header">
                            <h3>Profile Information</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <div class="form-group">
                                    <label for="username">Username</label>
                                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($admin['username']); ?>" required>
                                </div>

                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($admin['email']); ?>" required>
                                </div>

                                <div class="form-group">
                                    <label for="role">Role</label>
                                    <input type="text" id="role" value="<?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $admin['role']))); ?>" readonly>
                                    <small class="text-muted">Role cannot be changed</small>
                                </div>

                                <div class="form-group">
                                    <label for="created_at">Member Since</label>
                                    <input type="text" id="created_at" value="<?php echo date('d M Y', strtotime($admin['created_at'])); ?>" readonly>
                                </div>

                                <h3 class="mt-4 mb-3">Change Password</h3>
                                <p class="text-muted">Leave blank if you don't want to change your password</p>

                                <div class="form-group">
                                    <label for="current_password">Current Password</label>
                                    <input type="password" id="current_password" name="current_password">
                                </div>

                                <div class="form-group">
                                    <label for="new_password">New Password</label>
                                    <input type="password" id="new_password" name="new_password">
                                    <small class="text-muted">Minimum 6 characters</small>
                                </div>

                                <div class="form-group">
                                    <label for="confirm_password">Confirm New Password</label>
                                    <input type="password" id="confirm_password" name="confirm_password">
                                </div>

                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">Update Profile</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    <script src="../assets/js/admin.js"></script>
</body>
</html>


