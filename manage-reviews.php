<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

$auth->checkAdminAuth();

// Handle review actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    $review_id = $db->escapeString($_GET['id']);
    $action = $db->escapeString($_GET['action']);
    
    if ($action === 'approve') {
        $sql = "UPDATE reviews SET status = 'approved' WHERE review_id = '$review_id'";
        $_SESSION['success'] = "Review approved successfully";
    } elseif ($action === 'reject') {
        $sql = "UPDATE reviews SET status = 'rejected' WHERE review_id = '$review_id'";
        $_SESSION['success'] = "Review rejected successfully";
    } elseif ($action === 'delete') {
        $sql = "DELETE FROM reviews WHERE review_id = '$review_id'";
        $_SESSION['success'] = "Review deleted successfully";
    }
    
    if (isset($sql)) {
        $db->executeQuery($sql);
        header('Location: manage-reviews.php');
        exit();
    }
}

// Get reviews with filters
$filter_status = $_GET['status'] ?? '';
$filter_rating = $_GET['rating'] ?? '';
$filter_package = $_GET['package'] ?? '';

$sql = "SELECT r.*, u.full_name, u.email, 
               p.package_name, p.package_id,
               b.booking_id, b.travel_date
        FROM reviews r
        JOIN users u ON r.user_id = u.user_id
        JOIN tour_packages p ON r.package_id = p.package_id
        LEFT JOIN bookings b ON r.booking_id = b.booking_id
        WHERE 1=1";

if ($filter_status) {
    $sql .= " AND r.status = '$filter_status'";
}

if ($filter_rating) {
    $sql .= " AND r.rating = '$filter_rating'";
}

if ($filter_package) {
    $sql .= " AND r.package_id = '$filter_package'";
}

$sql .= " ORDER BY r.review_date DESC";
$result = $db->executeQuery($sql);

// Get packages for filter
$packages_sql = "SELECT package_id, package_name FROM tour_packages ORDER BY package_name";
$packages_result = $db->executeQuery($packages_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Reviews - <?php echo SITE_NAME; ?> Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    .rating-stars {
        color: #f39c12;
        font-size: 1.2rem;
    }
    
    .review-content {
        max-height: 100px;
        overflow: hidden;
        position: relative;
    }
    
    .review-content.expanded {
        max-height: none;
    }
    
    .read-more {
        color: var(--primary-color);
        cursor: pointer;
        font-size: 0.9rem;
        margin-top: 0.5rem;
        display: inline-block;
    }
    
    .review-status {
        display: inline-block;
        padding: 0.3rem 0.8rem;
        border-radius: 20px;
        font-size: 0.85rem;
        font-weight: 600;
    }
    
    .status-pending { background: #fff3cd; color: #856404; }
    .status-approved { background: #d4edda; color: #155724; }
    .status-rejected { background: #f8d7da; color: #721c24; }
    
    .stat-card-small {
        background: white;
        border-radius: 8px;
        padding: 1rem;
        text-align: center;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .stat-card-small h3 {
        font-size: 1.8rem;
        margin-bottom: 0.5rem;
    }
    
    .review-summary {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 1rem;
        margin-bottom: 2rem;
    }
    
    .review-card {
        background: white;
        border-radius: 8px;
        padding: 1.5rem;
        margin-bottom: 1rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .review-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #eee;
    }
    
    .reviewer-info {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    
    .reviewer-avatar {
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
    
    @media (max-width: 768px) {
        .review-header {
            flex-direction: column;
            gap: 1rem;
        }
        
        .review-actions {
            align-self: flex-end;
        }
    }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <?php include 'includes/sidebar.php'; ?>
        
        <div class="admin-content">
            <?php include 'includes/header.php'; ?>
            
            <main class="admin-main">
                <div class="container-fluid">
                    <div class="admin-header">
                        <h1 class="admin-title">Manage Reviews</h1>
                    </div>
                    
                    <?php if(isset($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Review Statistics -->
                    <div class="review-summary">
                        <?php
                        // Get review statistics
                        $stats_sql = "SELECT 
                            COUNT(*) as total_reviews,
                            AVG(rating) as avg_rating,
                            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_reviews,
                            SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_reviews,
                            SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_reviews
                            FROM reviews";
                        $stats_result = $db->executeQuery($stats_sql);
                        $stats = $stats_result->fetch_assoc();
                        ?>
                        
                        <div class="stat-card-small">
                            <h3><?php echo $stats['total_reviews']; ?></h3>
                            <p>Total Reviews</p>
                        </div>
                        
                        <div class="stat-card-small">
                            <h3><?php echo number_format((float)($stats['avg_rating'] ?? 0), 1); ?>/5</h3>
                            <p>Average Rating</p>
                        </div>
                        
                        <div class="stat-card-small">
                            <h3><?php echo $stats['pending_reviews']; ?></h3>
                            <p>Pending</p>
                        </div>
                        
                        <div class="stat-card-small">
                            <h3><?php echo $stats['approved_reviews']; ?></h3>
                            <p>Approved</p>
                        </div>
                    </div>
                    
                    <!-- Filters -->
                    <div class="admin-card">
                        <div class="admin-card-body">
                            <form method="GET" class="filter-form">
                                <div class="form-row">
                                    <div class="form-group">
                                        <label for="status">Status</label>
                                        <select id="status" name="status" onchange="this.form.submit()">
                                            <option value="">All Status</option>
                                            <option value="pending" <?php echo $filter_status == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                            <option value="approved" <?php echo $filter_status == 'approved' ? 'selected' : ''; ?>>Approved</option>
                                            <option value="rejected" <?php echo $filter_status == 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="rating">Rating</label>
                                        <select id="rating" name="rating" onchange="this.form.submit()">
                                            <option value="">All Ratings</option>
                                            <option value="5" <?php echo $filter_rating == '5' ? 'selected' : ''; ?>>5 Stars</option>
                                            <option value="4" <?php echo $filter_rating == '4' ? 'selected' : ''; ?>>4 Stars</option>
                                            <option value="3" <?php echo $filter_rating == '3' ? 'selected' : ''; ?>>3 Stars</option>
                                            <option value="2" <?php echo $filter_rating == '2' ? 'selected' : ''; ?>>2 Stars</option>
                                            <option value="1" <?php echo $filter_rating == '1' ? 'selected' : ''; ?>>1 Star</option>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label for="package">Package</label>
                                        <select id="package" name="package" onchange="this.form.submit()">
                                            <option value="">All Packages</option>
                                            <?php while($package = $packages_result->fetch_assoc()): ?>
                                            <option value="<?php echo $package['package_id']; ?>" 
                                                <?php echo $filter_package == $package['package_id'] ? 'selected' : ''; ?>>
                                                <?php echo $package['package_name']; ?>
                                            </option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>
                                    
                                    <div class="form-group">
                                        <label>&nbsp;</label>
                                        <a href="manage-reviews.php" class="btn btn-outline">Clear Filters</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                    
                    <!-- Reviews List -->
                    <div class="admin-card">
                        <div class="admin-card-header">
                            <h3>Customer Reviews</h3>
                            <span class="badge badge-primary"><?php echo $result->num_rows; ?> reviews</span>
                        </div>
                        <div class="admin-card-body">
                            <?php if($result->num_rows > 0): ?>
                            <div class="reviews-list">
                                <?php while($review = $result->fetch_assoc()): ?>
                                <div class="review-card">
                                    <div class="review-header">
                                        <div class="reviewer-info">
                                            <div class="reviewer-avatar">
                                                <?php echo strtoupper(substr($review['full_name'], 0, 1)); ?>
                                            </div>
                                            <div>
                                                <h4 style="margin: 0;"><?php echo $review['full_name']; ?></h4>
                                                <small><?php echo $review['email']; ?></small><br>
                                                <small>Booked: <?php echo date('d M, Y', strtotime($review['travel_date'])); ?></small>
                                            </div>
                                        </div>
                                        
                                        <div style="text-align: right;">
                                            <div class="rating-stars">
                                                <?php for($i = 1; $i <= 5; $i++): ?>
                                                <?php if($i <= $review['rating']): ?>
                                                <i class="fas fa-star"></i>
                                                <?php else: ?>
                                                <i class="far fa-star"></i>
                                                <?php endif; ?>
                                                <?php endfor; ?>
                                            </div>
                                            <span class="review-status status-<?php echo $review['status']; ?>">
                                                <?php echo ucfirst($review['status']); ?>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <strong>Package:</strong> 
                                        <a href="../pages/package-details.php?id=<?php echo $review['package_id']; ?>" target="_blank">
                                            <?php echo $review['package_name']; ?>
                                        </a>
                                    </div>
                                    
                                    <div class="review-content" id="review-<?php echo $review['review_id']; ?>">
                                        <?php echo nl2br(htmlspecialchars($review['review_text'])); ?>
                                    </div>
                                    
                                    <?php if(strlen($review['review_text']) > 200): ?>
                                    <span class="read-more" onclick="toggleReview(<?php echo $review['review_id']; ?>)">
                                        Read more
                                    </span>
                                    <?php endif; ?>
                                    
                                    <div class="review-meta" style="margin-top: 1rem; color: #666; font-size: 0.9rem;">
                                        Reviewed on: <?php echo date('F d, Y', strtotime($review['review_date'])); ?>
                                    </div>
                                    
                                    <div class="review-actions" style="margin-top: 1rem; display: flex; gap: 0.5rem;">
                                        <?php if($review['status'] == 'pending'): ?>
                                        <a href="?action=approve&id=<?php echo $review['review_id']; ?>" 
                                           class="btn btn-sm btn-success">
                                            <i class="fas fa-check"></i> Approve
                                        </a>
                                        <a href="?action=reject&id=<?php echo $review['review_id']; ?>" 
                                           class="btn btn-sm btn-danger"
                                           onclick="return confirm('Reject this review?')">
                                            <i class="fas fa-times"></i> Reject
                                        </a>
                                        <?php endif; ?>
                                        
                                        <a href="?action=delete&id=<?php echo $review['review_id']; ?>" 
                                           class="btn btn-sm btn-outline"
                                           onclick="return confirm('Delete this review?')">
                                            <i class="fas fa-trash"></i> Delete
                                        </a>
                                        
                                        <button class="btn btn-sm btn-outline" 
                                                onclick="copyReview(<?php echo $review['review_id']; ?>)">
                                            <i class="fas fa-copy"></i> Copy
                                        </button>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            </div>
                            <?php else: ?>
                            <div class="empty-state" style="text-align: center; padding: 3rem;">
                                <i class="fas fa-star" style="font-size: 4rem; color: #ddd; margin-bottom: 1rem;"></i>
                                <h3>No Reviews Found</h3>
                                <p>No reviews match your filters.</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <script src="../assets/js/admin.js"></script>
    <script>
    // Toggle review content
    function toggleReview(reviewId) {
        const content = document.getElementById('review-' + reviewId);
        const toggleBtn = content.nextElementSibling;
        
        content.classList.toggle('expanded');
        
        if (content.classList.contains('expanded')) {
            toggleBtn.textContent = 'Read less';
        } else {
            toggleBtn.textContent = 'Read more';
        }
    }
    
    // Copy review text
    function copyReview(reviewId) {
        const content = document.getElementById('review-' + reviewId);
        const text = content.textContent;
        
        navigator.clipboard.writeText(text).then(() => {
            alert('Review copied to clipboard!');
        });
    }
    
    // Filter by rating
    function filterByRating(rating) {
        window.location.href = 'manage-reviews.php?rating=' + rating;
    }
    
    // Quick status update
    function updateStatus(reviewId, status) {
        if (confirm(`Are you sure you want to mark this review as ${status}?`)) {
            window.location.href = `manage-reviews.php?action=${status}&id=${reviewId}`;
        }
    }
    
    // Initialize expanded reviews if needed
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-expand short reviews
        document.querySelectorAll('.review-content').forEach(content => {
            if (content.textContent.length <= 300) {
                content.classList.add('expanded');
                const toggleBtn = content.nextElementSibling;
                if (toggleBtn && toggleBtn.classList.contains('read-more')) {
                    toggleBtn.style.display = 'none';
                }
            }
        });
    });
    </script>
</body>

</html>

