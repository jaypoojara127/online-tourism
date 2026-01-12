<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

$auth->checkAdminAuth();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settings = [
        'site_name' => $_POST['site_name'] ?? '',
        'site_email' => $_POST['site_email'] ?? '',
        'site_phone' => $_POST['site_phone'] ?? '',
        'site_address' => $_POST['site_address'] ?? '',
        'currency' => $_POST['currency'] ?? 'INR',
        'payu_merchant_key' => $_POST['payu_merchant_key'] ?? '',
        'payu_merchant_salt' => $_POST['payu_merchant_salt'] ?? ''
    ];

    foreach ($settings as $key => $value) {
        $escaped_key = $db->escapeString($key);
        $escaped_value = $db->escapeString($value);
        $sql = "INSERT INTO settings (setting_key, setting_value) VALUES ('$escaped_key', '$escaped_value') 
                ON DUPLICATE KEY UPDATE setting_value = '$escaped_value', updated_at = CURRENT_TIMESTAMP";
        $db->executeQuery($sql);
    }

    $_SESSION['success'] = "Settings updated successfully";
    header('Location: settings.php');
    exit();
}

// Get current settings
$settings = [];
$sql = "SELECT setting_key, setting_value FROM settings";
$result = $db->executeQuery($sql);
while ($row = $result->fetch_assoc()) {
    $settings[$row['setting_key']] = $row['setting_value'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Admin</title>
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
                        <h1>Site Settings</h1>
                    </div>

                    <?php if (isset($_SESSION['success'])): ?>
                        <div class="alert alert-success">
                            <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                        </div>
                    <?php endif; ?>

                    <div class="card">
                        <div class="card-header">
                            <h3>General Settings</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="">
                                <div class="form-group">
                                    <label for="site_name">Site Name</label>
                                    <input type="text" id="site_name" name="site_name" value="<?php echo htmlspecialchars($settings['site_name'] ?? ''); ?>" required>
                                </div>

                                <div class="form-group">
                                    <label for="site_email">Site Email</label>
                                    <input type="email" id="site_email" name="site_email" value="<?php echo htmlspecialchars($settings['site_email'] ?? ''); ?>" required>
                                </div>

                                <div class="form-group">
                                    <label for="site_phone">Site Phone</label>
                                    <input type="text" id="site_phone" name="site_phone" value="<?php echo htmlspecialchars($settings['site_phone'] ?? ''); ?>" required>
                                </div>

                                <div class="form-group">
                                    <label for="site_address">Site Address</label>
                                    <textarea id="site_address" name="site_address" rows="3"><?php echo htmlspecialchars($settings['site_address'] ?? ''); ?></textarea>
                                </div>

                                <div class="form-group">
                                    <label for="currency">Currency</label>
                                    <select id="currency" name="currency">
                                        <option value="INR" <?php echo ($settings['currency'] ?? 'INR') === 'INR' ? 'selected' : ''; ?>>INR (Indian Rupee)</option>
                                        <option value="USD" <?php echo ($settings['currency'] ?? 'INR') === 'USD' ? 'selected' : ''; ?>>USD (US Dollar)</option>
                                        <option value="EUR" <?php echo ($settings['currency'] ?? 'INR') === 'EUR' ? 'selected' : ''; ?>>EUR (Euro)</option>
                                    </select>
                                </div>

                                <h3 class="mt-4 mb-3">Payment Settings (PayU)</h3>

                                <div class="form-group">
                                    <label for="payu_merchant_key">PayU Merchant Key</label>
                                    <input type="text" id="payu_merchant_key" name="payu_merchant_key" value="<?php echo htmlspecialchars($settings['payu_merchant_key'] ?? ''); ?>" required>
                                </div>

                                <div class="form-group">
                                    <label for="payu_merchant_salt">PayU Merchant Salt</label>
                                    <input type="text" id="payu_merchant_salt" name="payu_merchant_salt" value="<?php echo htmlspecialchars($settings['payu_merchant_salt'] ?? ''); ?>" required>
                                </div>

                                <div class="form-group">
                                    <button type="submit" class="btn btn-primary">Save Settings</button>
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
