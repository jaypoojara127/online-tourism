<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$auth->checkAdminAuth();

$action = $_GET['action'] ?? 'list';
$itinerary_id = $_GET['id'] ?? 0;
$package_id = $_GET['package_id'] ?? 0;

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $package_id_post = $db->escapeString($_POST['package_id']);
    $day_number = $db->escapeString($_POST['day_number']);
    $title = $db->escapeString($_POST['title']);
    $description = $db->escapeString($_POST['description']);
    $accommodation = $db->escapeString($_POST['accommodation']);
    $meals = $db->escapeString($_POST['meals']);
    $activities = $db->escapeString($_POST['activities']);
    
    if ($_POST['form_action'] === 'add') {
        $sql = "INSERT INTO itinerary (package_id, day_number, title, description, accommodation, meals, activities) 
                VALUES ('$package_id_post', '$day_number', '$title', '$description', '$accommodation', '$meals', '$activities')";
        $message = 'Itinerary added successfully';
    } elseif ($_POST['form_action'] === 'edit') {
        $id = $db->escapeString($_POST['itinerary_id']);
        $sql = "UPDATE itinerary SET 
                package_id = '$package_id_post',
                day_number = '$day_number',
                title = '$title',
                description = '$description',
                accommodation = '$accommodation',
                meals = '$meals',
                activities = '$activities'
                WHERE itinerary_id = '$id'";
        $message = 'Itinerary updated successfully';
    }
    
    if ($db->executeQuery($sql)) {
        $_SESSION['success'] = $message;
    } else {
        $_SESSION['error'] = 'Error saving itinerary';
    }
    header('Location: manage-itineraries.php');
    exit();
}

// Handle delete
if ($action === 'delete' && $itinerary_id) {
    $sql = "DELETE FROM itinerary WHERE itinerary_id = '$itinerary_id'";
    if ($db->executeQuery($sql)) {
        $_SESSION['success'] = 'Itinerary deleted successfully';
    }
    header('Location: manage-itineraries.php');
    exit();
}

// Get all packages with their itineraries
$sql = "SELECT p.package_id, p.package_name, d.name as destination_name,
               COUNT(i.itinerary_id) as itinerary_count
        FROM tour_packages p
        LEFT JOIN destinations d ON p.destination_id = d.destination_id
        LEFT JOIN itinerary i ON p.package_id = i.package_id
        GROUP BY p.package_id
        ORDER BY p.package_name";
$packages_result = $db->executeQuery($sql);

$packages = [];
while ($package = $packages_result->fetch_assoc()) {
    $package_id = $package['package_id'];
    
    // Get itineraries for this package
    $sql = "SELECT * FROM itinerary WHERE package_id = '$package_id' ORDER BY day_number";
    $itineraries_result = $db->executeQuery($sql);
    
    $itineraries = [];
    while ($itinerary = $itineraries_result->fetch_assoc()) {
        $itineraries[] = $itinerary;
    }
    
    $package['itineraries'] = $itineraries;
    $packages[] = $package;
}

// Get package details for forms
$selected_package = null;
$selected_itinerary = null;

if ($action === 'add' && $package_id) {
    $sql = "SELECT p.*, d.name as destination_name FROM tour_packages p 
            LEFT JOIN destinations d ON p.destination_id = d.destination_id 
            WHERE p.package_id = '$package_id'";
    $result = $db->executeQuery($sql);
    $selected_package = $result->fetch_assoc();
} elseif ($action === 'edit' && $itinerary_id) {
    $sql = "SELECT i.*, p.package_name, d.name as destination_name 
            FROM itinerary i 
            JOIN tour_packages p ON i.package_id = p.package_id 
            LEFT JOIN destinations d ON p.destination_id = d.destination_id 
            WHERE i.itinerary_id = '$itinerary_id'";
    $result = $db->executeQuery($sql);
    $selected_itinerary = $result->fetch_assoc();
    $selected_package = $selected_itinerary; // For form display
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Itineraries - Admin</title>
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
                        <h1>Manage Itineraries</h1>
                        <?php if ($action === 'list'): ?>
                            <a href="manage-packages.php" class="btn btn-secondary">Manage Packages</a>
                        <?php else: ?>
                            <a href="manage-itineraries.php" class="btn btn-secondary">Back to List</a>
                        <?php endif; ?>
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

                    <?php if ($action === 'list'): ?>
                        <div class="card">
                            <div class="card-header">
                                <h3>All Packages & Itineraries</h3>
                            </div>
                            <div class="card-body">
                                <?php if (empty($packages)): ?>
                                    <p>No packages found.</p>
                                <?php else: ?>
                                    <?php foreach ($packages as $package): ?>
                                        <div class="package-itinerary mb-4">
                                            <div class="d-flex justify-between align-center mb-3">
                                                <h4><?php echo htmlspecialchars($package['package_name'] ?? ''); ?> 
                                                    <small>(<?php echo htmlspecialchars($package['destination_name'] ?? ''); ?>)</small>
                                                </h4>
                                                <a href="?action=add&package_id=<?php echo $package['package_id']; ?>" class="btn btn-primary btn-sm">
                                                    <i class="fas fa-plus"></i> Add Itinerary
                                                </a>
                                            </div>
                                            
                                            <?php if (empty($package['itineraries'])): ?>
                                                <p class="text-muted">No itineraries added yet.</p>
                                            <?php else: ?>
                                                <div class="table-responsive">
                                                    <table class="table">
                                                        <thead>
                                                            <tr>
                                                                <th>Day</th>
                                                                <th>Title</th>
                                                                <th>Description</th>
                                                                <th>Accommodation</th>
                                                                <th>Meals</th>
                                                                <th>Actions</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php foreach ($package['itineraries'] as $itinerary): ?>
                                                                <tr>
                                                                    <td><?php echo $itinerary['day_number']; ?></td>
                                                                    <td><?php echo htmlspecialchars($itinerary['title'] ?? ''); ?></td>
                                                                    <td><?php echo htmlspecialchars(substr($itinerary['description'] ?? '', 0, 50)) . (strlen($itinerary['description'] ?? '') > 50 ? '...' : ''); ?></td>
                                                                    <td><?php echo htmlspecialchars($itinerary['accommodation'] ?? ''); ?></td>
                                                                    <td><?php echo htmlspecialchars($itinerary['meals'] ?? ''); ?></td>
                                                                    <td>
                                                                        <a href="?action=edit&id=<?php echo $itinerary['itinerary_id']; ?>" class="btn btn-sm btn-warning">
                                                                            <i class="fas fa-edit"></i> Edit
                                                                        </a>
                                                                        <a href="?action=delete&id=<?php echo $itinerary['itinerary_id']; ?>" 
                                                                           class="btn btn-sm btn-danger" 
                                                                           onclick="return confirm('Are you sure you want to delete this itinerary?')">
                                                                            <i class="fas fa-trash"></i> Delete
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <hr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php elseif ($action === 'add' || $action === 'edit'): ?>
                        <div class="card">
                            <div class="card-header">
                                <h3><?php echo $action === 'add' ? 'Add' : 'Edit'; ?> Itinerary</h3>
                            </div>
                            <div class="card-body">
                                <form method="POST" action="">
                                    <input type="hidden" name="form_action" value="<?php echo $action; ?>">
                                    <?php if ($action === 'edit'): ?>
                                        <input type="hidden" name="itinerary_id" value="<?php echo $selected_itinerary['itinerary_id']; ?>">
                                    <?php endif; ?>

                                    <div class="form-group">
                                        <label for="package_id">Package</label>
                                        <select id="package_id" name="package_id" required <?php echo $action === 'edit' ? 'disabled' : ''; ?>>
                                            <?php
                                            $packages_sql = "SELECT p.package_id, p.package_name, d.name as destination_name 
                                                           FROM tour_packages p 
                                                           LEFT JOIN destinations d ON p.destination_id = d.destination_id 
                                                           ORDER BY p.package_name";
                                            $packages_result = $db->executeQuery($packages_sql);
                                            while ($pkg = $packages_result->fetch_assoc()):
                                            ?>
                                                <option value="<?php echo $pkg['package_id']; ?>" 
                                                        <?php echo ($selected_package && $selected_package['package_id'] == $pkg['package_id']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars(($pkg['package_name'] ?? '') . ' (' . ($pkg['destination_name'] ?? '') . ')'); ?>
                                                </option>
                                            <?php endwhile; ?>
                                        </select>
                                        <?php if ($action === 'edit'): ?>
                                            <input type="hidden" name="package_id" value="<?php echo $selected_itinerary['package_id']; ?>">
                                        <?php endif; ?>
                                    </div>

                                    <div class="form-group">
                                        <label for="day_number">Day Number</label>
                                        <input type="number" id="day_number" name="day_number" 
                                               value="<?php echo $selected_itinerary['day_number'] ?? ''; ?>" required min="1">
                                    </div>

                                    <div class="form-group">
                                        <label for="title">Title</label>
                                        <input type="text" id="title" name="title" 
                                               value="<?php echo htmlspecialchars($selected_itinerary['title'] ?? ''); ?>" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="description">Description</label>
                                        <textarea id="description" name="description" rows="4" required><?php echo htmlspecialchars($selected_itinerary['description'] ?? ''); ?></textarea>
                                    </div>

                                    <div class="form-group">
                                        <label for="accommodation">Accommodation</label>
                                        <input type="text" id="accommodation" name="accommodation" 
                                               value="<?php echo htmlspecialchars($selected_itinerary['accommodation'] ?? ''); ?>">
                                    </div>

                                    <div class="form-group">
                                        <label for="meals">Meals</label>
                                        <input type="text" id="meals" name="meals" 
                                               value="<?php echo htmlspecialchars($selected_itinerary['meals'] ?? ''); ?>" 
                                               placeholder="e.g., Breakfast, Lunch, Dinner">
                                    </div>

                                    <div class="form-group">
                                        <label for="activities">Activities</label>
                                        <textarea id="activities" name="activities" rows="3"><?php echo htmlspecialchars($selected_itinerary['activities'] ?? ''); ?></textarea>
                                    </div>

                           <div class="form-group">
                                        <button type="submit" class="btn btn-primary">
                                            <?php echo $action === 'add' ? 'Add' : 'Update'; ?> Itinerary
                                        </button>
                                        <a href="manage-itineraries.php" class="btn btn-secondary">Cancel</a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </main>
        </div>
    </div>
    <script src="../assets/js/admin.js"></script>
</body>
</html>         


