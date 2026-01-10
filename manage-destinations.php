<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$auth->checkAdminAuth();

$action = $_GET['action'] ?? 'list';
$destination_id = $_GET['id'] ?? 0;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $db->escapeString($_POST['name']);
    $country = $db->escapeString($_POST['country']);
    $city = $db->escapeString($_POST['city']);
    $description = $db->escapeString($_POST['description']);
    $best_time = $db->escapeString($_POST['best_time_to_visit']);
    $attractions = $db->escapeString($_POST['attractions']);
    $status = $db->escapeString($_POST['status']);
    
    // Handle image upload
    $featured_image = '';
    if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] == 0) {
        $upload_dir = UPLOAD_PATH . 'destinations/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_name = time() . '_' . basename($_FILES['featured_image']['name']);
        $target_file = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $target_file)) {
            $featured_image = 'destinations/' . $file_name;
        }
    }
    
    if ($_POST['action'] === 'add') {
        $sql = "INSERT INTO destinations (name, country, city, description, best_time_to_visit, attractions, featured_image, status) 
                VALUES ('$name', '$country', '$city', '$description', '$best_time', '$attractions', '$featured_image', '$status')";
        $message = 'Destination added successfully';
    } elseif ($_POST['action'] === 'edit') {
        $id = $db->escapeString($_POST['destination_id']);
        
        if (!empty($featured_image)) {
            $sql = "UPDATE destinations SET 
                    name = '$name',
                    country = '$country',
                    city = '$city',
                    description = '$description',
                    best_time_to_visit = '$best_time',
                    attractions = '$attractions',
                    featured_image = '$featured_image',
                    status = '$status'
                    WHERE destination_id = '$id'";
        } else {
            $sql = "UPDATE destinations SET 
                    name = '$name',
                    country = '$country',
                    city = '$city',
                    description = '$description',
                    best_time_to_visit = '$best_time',
                    attractions = '$attractions',
                    status = '$status'
                    WHERE destination_id = '$id'";
        }
        $message = 'Destination updated successfully';
    }
    
    $result = $db->executeQuery($sql);
    if ($result) {
        $_SESSION['success'] = $message;
        header('Location: manage-destinations.php');
        exit();
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = $db->escapeString($_GET['delete']);
    $sql = "DELETE FROM destinations WHERE destination_id = '$id'";
    $result = $db->executeQuery($sql);
    
    if ($result) {
        $_SESSION['success'] = 'Destination deleted successfully';
        header('Location: manage-destinations.php');
        exit();
    }
}

// Get destination for editing
$destination = null;
if ($action === 'edit' && $destination_id > 0) {
    $sql = "SELECT * FROM destinations WHERE destination_id = '$destination_id'";
    $result = $db->executeQuery($sql);
    if ($result->num_rows == 1) {
        $destination = $result->fetch_assoc();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Destinations - <?php echo SITE_NAME; ?> Admin</title>
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
                        <h1 class="admin-title">
                            <?php echo $action === 'add' ? 'Add New Destination' : ($action === 'edit' ? 'Edit Destination' : 'Manage Destinations'); ?>
                        </h1>
                        <?php if($action === 'list'): ?>
                        <a href="?action=add" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Destination
                        </a>
                        <?php endif; ?>
                    </div>
                    
                    <?php if(isset($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if(isset($_SESSION['error'])): ?>
                    <div class="alert alert-error">
                        <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
                    </div>
                    <?php endif; ?>
                    
                    <?php if($action === 'add' || $action === 'edit'): ?>
                    <!-- Add/Edit Form -->
                    <div class="admin-card">
                        <div class="admin-card-body">
                            <form method="POST" action="" enctype="multipart/form-data" class="admin-form">
                                <input type="hidden" name="action" value="<?php echo $action; ?>">
                                <?php if($action === 'edit'): ?>
                                <input type="hidden" name="destination_id" value="<?php echo $destination_id; ?>">
                                <?php endif; ?>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="name">Destination Name *</label>
                                        <input type="text" id="name" name="name" required 
                                               value="<?php echo $destination['name'] ?? ''; ?>">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="country">Country *</label>
                                        <input type="text" id="country" name="country" required 
                                               value="<?php echo $destination['country'] ?? ''; ?>">
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="city">City *</label>
                                        <input type="text" id="city" name="city" required 
                                               value="<?php echo $destination['city'] ?? ''; ?>">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="status">Status *</label>
                                        <select id="status" name="status" required>
                                            <option value="active" <?php echo ($destination['status'] ?? '') == 'active' ? 'selected' : ''; ?>>Active</option>
                                            <option value="inactive" <?php echo ($destination['status'] ?? '') == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="description">Description *</label>
                                    <textarea id="description" name="description" rows="5" required><?php echo $destination['description'] ?? ''; ?></textarea>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="best_time_to_visit">Best Time to Visit</label>
                                        <input type="text" id="best_time_to_visit" name="best_time_to_visit" 
                                               value="<?php echo $destination['best_time_to_visit'] ?? ''; ?>">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="featured_image">Featured Image</label>
                                        <input type="file" id="featured_image" name="featured_image" accept="image/*">
                                        <?php if($action === 'edit' && !empty($destination['featured_image'])): ?>
                                        <div class="current-image">
                                            <img src="<?php echo UPLOAD_URL . $destination['featured_image']; ?>" 
                                                 alt="Current Image" style="max-width: 200px; margin-top: 10px;">
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="attractions">Attractions (one per line)</label>
                                    <textarea id="attractions" name="attractions" rows="3"><?php echo $destination['attractions'] ?? ''; ?></textarea>
                                </div>
                                
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Save Destination
                                    </button>
                                    <a href="manage-destinations.php" class="btn btn-secondary">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <?php else: ?>
                    <!-- Destinations List -->
                    <div class="admin-card">
                        <div class="admin-card-header">
                            <h3>All Destinations</h3>
                            <div class="search-box">
                                <input type="text" id="searchDestinations" placeholder="Search destinations...">
                            </div>
                        </div>
                        <div class="admin-card-body">
                            <div class="table-responsive">
                                <table class="admin-table" id="destinationsTable">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Image</th>
                                            <th>Name</th>
                                            <th>Location</th>
                                            <th>Status</th>
                                            <th>Created</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $sql = "SELECT * FROM destinations ORDER BY created_at DESC";
                                        $result = $db->executeQuery($sql);
                                        while($row = $result->fetch_assoc()):
                                        ?>
                                        <tr>
                                            <td>#<?php echo $row['destination_id']; ?></td>
                                            <td>
                                                <?php if(!empty($row['featured_image'])): ?>
                                                <img src="<?php echo UPLOAD_URL . $row['featured_image']; ?>" 
                                                     alt="<?php echo $row['name']; ?>" class="table-image">
                                                <?php else: ?>
                                                <div class="no-image">No Image</div>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo $row['name']; ?></td>
                                            <td><?php echo $row['city'] . ', ' . $row['country']; ?></td>
                                            <td>
                                                <span class="status-badge status-<?php echo $row['status']; ?>">
                                                    <?php echo ucfirst($row['status']); ?>
                                                </span>
                                            </td>
                                            <td><?php echo date('d M, Y', strtotime($row['created_at'])); ?></td>
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="?action=edit&id=<?php echo $row['destination_id']; ?>" 
                                                       class="btn btn-sm btn-info" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="?delete=<?php echo $row['destination_id']; ?>" 
                                                       class="btn btn-sm btn-danger" title="Delete"
                                                       onclick="return confirm('Are you sure you want to delete this destination?')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                    <a href="../pages/destinations.php?id=<?php echo $row['destination_id']; ?>" 
                                                       class="btn btn-sm btn-secondary" title="View" target="_blank">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>
    
    
