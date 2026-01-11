<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$auth->checkAdminAuth();

$action = $_GET['action'] ?? 'list';
$package_id = $_GET['id'] ?? 0;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $package_name = $db->escapeString($_POST['package_name']);
    $destination_id = $db->escapeString($_POST['destination_id']);
    $duration_days = $db->escapeString($_POST['duration_days']);
    $duration_nights = $db->escapeString($_POST['duration_nights']);
    $price_per_person = $db->escapeString($_POST['price_per_person']);
    $discount_price = $db->escapeString($_POST['discount_price']);
    $max_capacity = $db->escapeString($_POST['max_capacity']);
    $overview = $db->escapeString($_POST['overview']);
    $highlights = $db->escapeString($_POST['highlights']);
    $status = $db->escapeString($_POST['status']);
    
    // Handle image upload
    $featured_image = '';
    if (isset($_FILES['featured_image']) && $_FILES['featured_image']['error'] == 0) {
        $upload_dir = UPLOAD_PATH . 'packages/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        
        $file_name = time() . '_' . basename($_FILES['featured_image']['name']);
        $target_file = $upload_dir . $file_name;
        
        if (move_uploaded_file($_FILES['featured_image']['tmp_name'], $target_file)) {
            $featured_image = 'packages/' . $file_name;
        }
    }
    
    if ($_POST['action'] === 'add') {
        $sql = "INSERT INTO tour_packages (package_name, destination_id, duration_days, duration_nights, 
                price_per_person, discount_price, max_capacity, featured_image, overview, highlights, status) 
                VALUES ('$package_name', '$destination_id', '$duration_days', '$duration_nights', 
                '$price_per_person', '$discount_price', '$max_capacity', '$featured_image', 
                '$overview', '$highlights', '$status')";
        $message = 'Package added successfully';
    } elseif ($_POST['action'] === 'edit') {
        $id = $db->escapeString($_POST['package_id']);
        
        if (!empty($featured_image)) {
            $sql = "UPDATE tour_packages SET 
                    package_name = '$package_name',
                    destination_id = '$destination_id',
                    duration_days = '$duration_days',
                    duration_nights = '$duration_nights',
                    price_per_person = '$price_per_person',
                    discount_price = '$discount_price',
                    max_capacity = '$max_capacity',
                    featured_image = '$featured_image',
                    overview = '$overview',
                    highlights = '$highlights',
                    status = '$status'
                    WHERE package_id = '$id'";
        } else {
            $sql = "UPDATE tour_packages SET 
                    package_name = '$package_name',
                    destination_id = '$destination_id',
                    duration_days = '$duration_days',
                    duration_nights = '$duration_nights',
                    price_per_person = '$price_per_person',
                    discount_price = '$discount_price',
                    max_capacity = '$max_capacity',
                    overview = '$overview',
                    highlights = '$highlights',
                    status = '$status'
                    WHERE package_id = '$id'";
        }
        $message = 'Package updated successfully';
    }
    
    $result = $db->executeQuery($sql);
    if ($result) {
        $package_id = $db->getLastInsertId();
        
        // Handle gallery images
        if (isset($_FILES['gallery_images']) && !empty($_FILES['gallery_images']['name'][0])) {
            $upload_dir = UPLOAD_PATH . 'packages/gallery/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            foreach ($_FILES['gallery_images']['tmp_name'] as $key => $tmp_name) {
                if ($_FILES['gallery_images']['error'][$key] == 0) {
                    $file_name = time() . '_' . $key . '_' . basename($_FILES['gallery_images']['name'][$key]);
                    $target_file = $upload_dir . $file_name;
                    
                    if (move_uploaded_file($tmp_name, $target_file)) {
                        $image_url = 'packages/gallery/' . $file_name;
                        $caption = $db->escapeString($_POST['gallery_captions'][$key] ?? '');
                        
                        $gallery_sql = "INSERT INTO package_gallery (package_id, image_url, caption) 
                                       VALUES ('$package_id', '$image_url', '$caption')";
                        $db->executeQuery($gallery_sql);
                    }
                }
            }
        }
        
        $_SESSION['success'] = $message;
        header('Location: manage-packages.php');
        exit();
    }
}

// Handle delete
if (isset($_GET['delete'])) {
    $id = $db->escapeString($_GET['delete']);
    $sql = "DELETE FROM tour_packages WHERE package_id = '$id'";
    $result = $db->executeQuery($sql);
    
    if ($result) {
        $_SESSION['success'] = 'Package deleted successfully';
        header('Location: manage-packages.php');
        exit();
    }
}

// Get package for editing
$package = null;
if ($action === 'edit' && $package_id > 0) {
    $sql = "SELECT * FROM tour_packages WHERE package_id = '$package_id'";
    $result = $db->executeQuery($sql);
    if ($result->num_rows == 1) {
        $package = $result->fetch_assoc();
    }
}

// Get destinations for dropdown
$destinations_sql = "SELECT * FROM destinations WHERE status = 'active' ORDER BY name";
$destinations_result = $db->executeQuery($destinations_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Packages - <?php echo SITE_NAME; ?> Admin</title>
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
                            <?php echo $action === 'add' ? 'Add New Package' : ($action === 'edit' ? 'Edit Package' : 'Manage Packages'); ?>
                        </h1>
                        <?php if($action === 'list'): ?>
                        <a href="?action=add" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Package
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
                                <input type="hidden" name="package_id" value="<?php echo $package_id; ?>">
                                <?php endif; ?>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="package_name">Package Name *</label>
                                        <input type="text" id="package_name" name="package_name" required 
                                               value="<?php echo $package['package_name'] ?? ''; ?>">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="destination_id">Destination *</label>
                                        <select id="destination_id" name="destination_id" required>
                                            <option value="">Select Destination</option>
                                            <?php while($dest = $destinations_result->fetch_assoc()): ?>
                                            <option value="<?php echo $dest['destination_id']; ?>"
                                                <?php echo ($package['destination_id'] ?? '') == $dest['destination_id'] ? 'selected' : ''; ?>>
                                                <?php echo $dest['name']; ?> (<?php echo $dest['city']; ?>, <?php echo $dest['country']; ?>)
                                            </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="duration_days">Duration (Days) *</label>
                                        <input type="number" id="duration_days" name="duration_days" min="1" required 
                                               value="<?php echo $package['duration_days'] ?? '1'; ?>">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="duration_nights">Duration (Nights) *</label>
                                        <input type="number" id="duration_nights" name="duration_nights" min="0" required 
                                               value="<?php echo $package['duration_nights'] ?? '0'; ?>">
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="price_per_person">Price per Person (₹) *</label>
                                        <input type="number" id="price_per_person" name="price_per_person" min="0" step="0.01" required 
                                               value="<?php echo $package['price_per_person'] ?? '0'; ?>">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="discount_price">Discount Price (₹)</label>
                                        <input type="number" id="discount_price" name="discount_price" min="0" step="0.01" 
                                               value="<?php echo $package['discount_price'] ?? ''; ?>">
                                    </div>
                                </div>
                                
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="max_capacity">Maximum Capacity *</label>
                                        <input type="number" id="max_capacity" name="max_capacity" min="1" required 
                                               value="<?php echo $package['max_capacity'] ?? '20'; ?>">
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="status">Status *</label>
                                        <select id="status" name="status" required>
                                            <option value="active" <?php echo ($package['status'] ?? '') == 'active' ? 'selected' : ''; ?>>Active</option>
                                            <option value="inactive" <?php echo ($package['status'] ?? '') == 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                                            <option value="sold_out" <?php echo ($package['status'] ?? '') == 'sold_out' ? 'selected' : ''; ?>>Sold Out</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="overview">Package Overview *</label>
                                    <textarea id="overview" name="overview" rows="4" required><?php echo $package['overview'] ?? ''; ?></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label for="highlights">Key Highlights (one per line)</label>
                                    <textarea id="highlights" name="highlights" rows="3"><?php echo $package['highlights'] ?? ''; ?></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <label for="featured_image">Featured Image *</label>
                                    <input type="file" id="featured_image" name="featured_image" accept="image/*" <?php echo $action === 'add' ? 'required' : ''; ?>>
                                    <?php if($action === 'edit' && !empty($package['featured_image'])): ?>
                                    <div class="current-image">
                                        <img src="<?php echo UPLOAD_URL . $package['featured_image']; ?>" 
                                             alt="Current Image" style="max-width: 200px; margin-top: 10px;">
                                    </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="form-group">
                                    <label for="gallery_images">Gallery Images</label>
                                    <div id="gallery-container">
                                        <div class="gallery-item">
                                            <input type="file" name="gallery_images[]" accept="image/*">
                                            <input type="text" name="gallery_captions[]" placeholder="Image caption">
                                            <button type="button" class="btn btn-sm btn-danger remove-gallery-item">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <button type="button" id="add-gallery-item" class="btn btn-sm btn-secondary">
                                        <i class="fas fa-plus"></i> Add Another Image
                                    </button>
                                </div>
                                
                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Save Package
                                    </button>
                                    <a href="manage-packages.php" class="btn btn-secondary">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <?php else: ?>
                    <!-- Packages List -->
                    <div class="admin-card">
                        <div class="admin-card-header">
                            <h3>All Packages</h3>
                            <div class="search-box">
                                <input type="text" id="searchPackages" placeholder="Search packages...">
                            </div>
                        </div>
                        <div class="admin-card-body">
                            <div class="table-responsive">
                                <table class="admin-table" id="packagesTable">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Image</th>
                                            <th>Package Name</th>
                                            <th>Destination</th>
                                            <th>Duration</th>
                                            <th>Price</th>
                                            <th>Capacity</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $sql = "SELECT p.*, d.name as destination_name 
                                                FROM tour_packages p
                                                LEFT JOIN destinations d ON p.destination_id = d.destination_id
                                                ORDER BY p.created_at DESC";
                                        $result = $db->executeQuery($sql);
                                        while($row = $result->fetch_assoc()):
                                            $available = $row['max_capacity'] - $row['current_bookings'];
                                        ?>
                                        <tr>
                                            <td>#<?php echo $row['package_id']; ?></td>
                                            <td>
                                                <?php if(!empty($row['featured_image'])): ?>
                                                <img src="<?php echo UPLOAD_URL . $row['featured_image']; ?>" 
                                                     alt="<?php echo $row['package_name']; ?>" class="table-image">
                                                <?php else: ?>
                                                <div class="no-image">No Image</div>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo $row['package_name']; ?></td>
                                            <td><?php echo $row['destination_name']; ?></td>
                                            <td><?php echo $row['duration_days']; ?>D/<?php echo $row['duration_nights']; ?>N</td>
                                            <td>
                                                <?php if(!empty($row['discount_price'])): ?>
                                                <span class="original-price">₹<?php echo number_format($row['price_per_person']); ?></span>
                                                <br>
                                                <span class="discount-price">₹<?php echo number_format($row['discount_price']); ?></span>
                                                <?php else: ?>
                                                ₹<?php echo number_format($row['price_per_person']); ?>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="<?php echo $available <= 0 ? 'text-danger' : ''; ?>">
                                                    <?php echo $available; ?> available
                                                </span>
                                            </td>
                                            <td>
                                                <span class="status-badge status-<?php echo $row['status']; ?>">
                                                    <?php echo ucfirst($row['status']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="action-buttons">
                                                    <a href="?action=edit&id=<?php echo $row['package_id']; ?>" 
                                                       class="btn btn-sm btn-info" title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="manage-itineraries.php?package_id=<?php echo $row['package_id']; ?>" 
                                                       class="btn btn-sm btn-warning" title="Itinerary">
                                                        <i class="fas fa-calendar-alt"></i>
                                                    </a>
                                                    <a href="?delete=<?php echo $row['package_id']; ?>" 
                                                       class="btn btn-sm btn-danger" title="Delete"
                                                       onclick="return confirm('Are you sure you want to delete this package?')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                    <a href="../pages/package-details.php?id=<?php echo $row['package_id']; ?>" 
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
    
    <script src="../assets/js/admin.js"></script>
    <script>
    // Search functionality
    document.getElementById('searchPackages').addEventListener('keyup', function() {
        const filter = this.value.toLowerCase();
        const rows = document.querySelectorAll('#packagesTable tbody tr');
        
        rows.forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(filter) ? '' : 'none';
        });
    });
    
    // Gallery images functionality
    document.getElementById('add-gallery-item').addEventListener('click', function() {
        const container = document.getElementById('gallery-container');
        const newItem = document.createElement('div');
        newItem.className = 'gallery-item';
        newItem.innerHTML = `
            <input type="file" name="gallery_images[]" accept="image/*">
            <input type="text" name="gallery_captions[]" placeholder="Image caption">
            <button type="button" class="btn btn-sm btn-danger remove-gallery-item">
                <i class="fas fa-times"></i>
            </button>
        `;
        container.appendChild(newItem);
        
        // Add remove functionality
        newItem.querySelector('.remove-gallery-item').addEventListener('click', function() {
            container.removeChild(newItem);
        });
    });
    
    // Initialize remove buttons
    document.querySelectorAll('.remove-gallery-item').forEach(button => {
        button.addEventListener('click', function() {
            this.closest('.gallery-item').remove();
        });
    });
    </script>
</body>
</html>