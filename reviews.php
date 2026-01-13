<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$auth->checkUserAuth();
$user_id = $_SESSION['user_id'];

// Get user reviews
$sql = "SELECT r.*, p.package_name, p.featured_image,
               d.name as destination_name
        FROM reviews r
        JOIN tour_packages p ON r.package_id = p.package_id
        JOIN destinations d ON p.destination_id = d.destination_id
        WHERE r.user_id = '$user_id'
        ORDER BY r.review_date DESC";
$result = $db->executeQuery($sql);

// Get bookings that can be reviewed
$reviewable_sql = "SELECT b.*, p.package_name, p.featured_image,
                          d.name as destination_name
                   FROM bookings b
                   JOIN tour_packages p ON b.package_id = p.package_id
                   JOIN destinations d ON p.destination_id = d.destination_id
                   WHERE b.user_id = '$user_id' 
                   AND b.booking_status = 'completed'
                   AND b.package_id NOT IN (
                       SELECT package_id FROM reviews WHERE user_id = '$user_id'
                   )";
$reviewable_result = $db->executeQuery($reviewable_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Reviews - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    .reviews-container {
        background: white;
        border-radius: 10px;
        padding: 2rem;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    
    .reviews-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        padding-bottom: 1rem;
        border-bottom: 2px solid var(--primary-color);
    }
    
    .reviews-tabs {
        display: flex;
        gap: 0;
        border-bottom: 1px solid #ddd;
        margin-bottom: 2rem;
    }
    
    .tab-btn {
        padding: 1rem 2rem;
        background: none;
        border: none;
        font-size: 1rem;
        cursor: pointer;
        position: relative;
        color: #666;
    }
    
    .tab-btn:hover {
        color: var(--primary-color);
    }
    
    .tab-btn.active {
        color: var(--primary-color);
        font-weight: 600;
    }
    
    .tab-btn.active::after {
        content: '';
        position: absolute;
        bottom: -1px;
        left: 0;
        right: 0;
        height: 3px;
        background: var(--primary-color);
    }
    
    .tab-content {
        display: none;
    }
    
    .tab-content.active {
        display: block;
    }
    
    .review-card {
        background: white;
        border-radius: 8px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        border-left: 4px solid var(--primary-color);
    }
    
    .review-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 1rem;
    }
    
    .review-package {
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    
    .review-image {
        width: 80px;
        height: 80px;
        border-radius: 8px;
        overflow: hidden;
    }
    
    .review-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .rating-stars {
        color: #f39c12;
        font-size: 1.2rem;
    }
    
    .review-date {
        color: #666;
        font-size: 0.9rem;
    }
    
    .review-content {
        color: #333;
        line-height: 1.6;
        margin-bottom: 1rem;
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
    
    .empty-state {
        text-align: center;
        padding: 3rem;
        color: #666;
    }
    
    .empty-state i {
        font-size: 4rem;
        margin-bottom: 1rem;
        color: #ddd;
    }
    
    .reviewable-list {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 1.5rem;
    }
    
    .reviewable-card {
        background: white;
        border-radius: 8px;
        padding: 1.5rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        border: 2px dashed #ddd;
        transition: all 0.3s ease;
    }
    
    .reviewable-card:hover {
        border-color: var(--primary-color);
        transform: translateY(-5px);
    }
    
    .reviewable-header {
        display: flex;
        gap: 1rem;
        margin-bottom: 1rem;
    }
    
    .reviewable-image {
        width: 80px;
        height: 80px;
        border-radius: 8px;
        overflow: hidden;
        flex-shrink: 0;
    }
    
    .reviewable-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .review-form {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 1.5rem;
        margin-top: 1rem;
        display: none;
    }
    
    .review-form.active {
        display: block;
    }
    
    .star-rating {
        display: flex;
        gap: 0.5rem;
        margin-bottom: 1rem;
    }
    
    .star {
        font-size: 2rem;
        color: #ddd;
        cursor: pointer;
        transition: color 0.2s;
    }
    
    .star:hover,
    .star.hover,
    .star.selected {
        color: #f39c12;
    }
    
    .form-group {
        margin-bottom: 1rem;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
    }
    
    .form-group textarea {
        width: 100%;
        padding: 0.8rem;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-family: 'Poppins', sans-serif;
        min-height: 120px;
        resize: vertical;
    }
    
    @media (max-width: 768px) {
        .reviews-tabs {
            flex-direction: column;
        }
        
        .tab-btn {
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        .reviewable-list {
            grid-template-columns: 1fr;
        }
        
        .review-header {
            flex-direction: column;
            gap: 1rem;
        }
    }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <section class="dashboard">
        <div class="container">
            <div class="dashboard-grid">
                <?php include 'includes/sidebar.php'; ?>
                
                <main class="dashboard-content">
                    <div class="reviews-container">
                        <div class="reviews-header">
                            <h1>My Reviews</h1>
                            <div class="review-stats">
                                <?php
                                $stats_sql = "SELECT 
                                    COUNT(*) as total,
                                    AVG(rating) as avg_rating,
                                    SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as published
                                    FROM reviews WHERE user_id = '$user_id'";
                                $stats_result = $db->executeQuery($stats_sql);
                                $stats = $stats_result->fetch_assoc();
                                ?>
                                <span class="badge badge-primary"><?php echo $stats['total']; ?> reviews</span>
                                <span class="badge badge-success">Avg: <?php echo number_format($stats['avg_rating'], 1); ?>/5</span>
                            </div>
                        </div>
                        
                        <!-- Tabs -->
                        <div class="reviews-tabs">
                            <button class="tab-btn active" onclick="openTab('published')">
                                <i class="fas fa-star"></i> My Reviews
                            </button>
                            <button class="tab-btn" onclick="openTab('pending')">
                                <i class="fas fa-clock"></i> Pending Reviews
                            </button>
                            <button class="tab-btn" onclick="openTab('write')">
                                <i class="fas fa-pen"></i> Write Review
                            </button>
                        </div>
                        
                        <!-- Published Reviews Tab -->
                        <div id="published-tab" class="tab-content active">
                            <?php if($result->num_rows > 0): ?>
                                <?php while($review = $result->fetch_assoc()): ?>
                                <div class="review-card">
                                    <div class="review-header">
                                        <div class="review-package">
                                            <div class="review-image">
                                                <img src="<?php echo UPLOAD_URL . $review['featured_image']; ?>" 
                                                     alt="<?php echo $review['package_name']; ?>">
                                            </div>
                                            <div>
                                                <h3 style="margin: 0 0 0.5rem 0;"><?php echo $review['package_name']; ?></h3>
                                                <p style="margin: 0; color: #666;">
                                                    <?php echo $review['destination_name']; ?>
                                                </p>
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
                                            <div class="review-date">
                                                <?php echo date('F d, Y', strtotime($review['review_date'])); ?>
                                            </div>
                                            <span class="review-status status-<?php echo $review['status']; ?>">
                                                <?php echo ucfirst($review['status']); ?>
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="review-content">
                                        <?php echo nl2br(htmlspecialchars($review['review_text'])); ?>
                                    </div>
                                    
                                    <?php if($review['status'] == 'rejected'): ?>
                                    <div class="alert alert-error" style="margin-top: 1rem;">
                                        <i class="fas fa-exclamation-circle"></i>
                                        This review was rejected by our moderation team.
                                    </div>
                                    <?php endif; ?>
                                    
                                    <div class="review-actions" style="display: flex; gap: 0.5rem; margin-top: 1rem;">
                                        <?php if($review['status'] == 'pending'): ?>
                                        <button class="btn btn-sm btn-outline" onclick="editReview(<?php echo $review['review_id']; ?>)">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <?php endif; ?>
                                        
                                        <button class="btn btn-sm btn-outline" onclick="shareReview(<?php echo $review['review_id']; ?>)">
                                            <i class="fas fa-share"></i> Share
                                        </button>
                                        
                                        <?php if($review['status'] == 'pending'): ?>
                                        <button class="btn btn-sm btn-danger" 
                                                onclick="deleteReview(<?php echo $review['review_id']; ?>)">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                            <div class="empty-state">
                                <i class="far fa-star"></i>
                                <h3>No Reviews Yet</h3>
                                <p>You haven't written any reviews yet. Share your travel experiences!</p>
                                <button class="btn btn-primary" onclick="openTab('write')">
                                    <i class="fas fa-pen"></i> Write Your First Review
                                </button>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Write Review Tab -->
                        <div id="write-tab" class="tab-content">
                            <h2>Write a Review</h2>
                            <p style="margin-bottom: 2rem; color: #666;">
                                Share your experience to help other travelers. You can review packages you've completed.
                            </p>
                            
                            <?php if($reviewable_result->num_rows > 0): ?>
                            <div class="reviewable-list">
                                <?php while($booking = $reviewable_result->fetch_assoc()): ?>
                                <div class="reviewable-card" id="booking-<?php echo $booking['booking_id']; ?>">
                                    <div class="reviewable-header">
                                        <div class="reviewable-image">
                                            <img src="<?php echo UPLOAD_URL . $booking['featured_image']; ?>" 
                                                 alt="<?php echo $booking['package_name']; ?>">
                                        </div>
                                        <div>
                                            <h3 style="margin: 0 0 0.5rem 0;"><?php echo $booking['package_name']; ?></h3>
                                            <p style="margin: 0; color: #666;">
                                                <?php echo $booking['destination_name']; ?>
                                            </p>
                                            <p style="margin: 0.5rem 0 0 0; font-size: 0.9rem;">
                                                Traveled on: <?php echo date('F d, Y', strtotime($booking['travel_date'])); ?>
                                            </p>
                                        </div>
                                    </div>
                                    
                                    <button class="btn btn-primary btn-block" 
                                            onclick="openReviewForm(<?php echo $booking['booking_id']; ?>, <?php echo $booking['package_id']; ?>)">
                                        <i class="fas fa-pen"></i> Write Review
                                    </button>
                                    
                                    <!-- Review Form (Hidden by default) -->
                                    <div class="review-form" id="review-form-<?php echo $booking['booking_id']; ?>">
                                        <form method="POST" action="submit-review.php">
                                            <input type="hidden" name="booking_id" value="<?php echo $booking['booking_id']; ?>">
                                            <input type="hidden" name="package_id" value="<?php echo $booking['package_id']; ?>">
                                            
                                            <div class="form-group">
                                                <label>Your Rating:</label>
                                                <div class="star-rating" id="rating-<?php echo $booking['booking_id']; ?>">
                                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                                    <span class="star" data-value="<?php echo $i; ?>" 
                                                          onclick="setRating(<?php echo $booking['booking_id']; ?>, <?php echo $i; ?>)">
                                                        ★
                                                    </span>
                                                    <?php endfor; ?>
                                                </div>
                                                <input type="hidden" name="rating" id="rating-input-<?php echo $booking['booking_id']; ?>" value="5">
                                            </div>
                                            
                                            <div class="form-group">
                                                <label for="review-text-<?php echo $booking['booking_id']; ?>">Your Review:</label>
                                                <textarea id="review-text-<?php echo $booking['booking_id']; ?>" 
                                                          name="review_text" 
                                                          placeholder="Share your experience... What did you like? What could be improved?"
                                                          required></textarea>
                                            </div>
                                            
                                            <div style="display: flex; gap: 1rem;">
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fas fa-paper-plane"></i> Submit Review
                                                </button>
                                                <button type="button" class="btn btn-secondary" 
                                                        onclick="closeReviewForm(<?php echo $booking['booking_id']; ?>)">
                                                    Cancel
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            </div>
                            <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-check-circle"></i>
                                <h3>No Packages to Review</h3>
                                <p>You have reviewed all your completed trips. Book more tours to share your experiences!</p>
                                <a href="../pages/packages.php" class="btn btn-primary">
                                    <i class="fas fa-suitcase"></i> Book New Tour
                                </a>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Pending Reviews Tab -->
                        <div id="pending-tab" class="tab-content">
                            <?php
                            $pending_sql = "SELECT r.*, p.package_name, p.featured_image,
                                                   d.name as destination_name
                                            FROM reviews r
                                            JOIN tour_packages p ON r.package_id = p.package_id
                                            JOIN destinations d ON p.destination_id = d.destination_id
                                            WHERE r.user_id = '$user_id' AND r.status = 'pending'
                                            ORDER BY r.review_date DESC";
                            $pending_result = $db->executeQuery($pending_sql);
                            ?>
                            
                            <?php if($pending_result->num_rows > 0): ?>
                                <?php while($review = $pending_result->fetch_assoc()): ?>
                                <div class="review-card">
                                    <div class="review-header">
                                        <div class="review-package">
                                            <div class="review-image">
                                                <img src="<?php echo UPLOAD_URL . $review['featured_image']; ?>" 
                                                     alt="<?php echo $review['package_name']; ?>">
                                            </div>
                                            <div>
                                                <h3 style="margin: 0 0 0.5rem 0;"><?php echo $review['package_name']; ?></h3>
                                                <p style="margin: 0; color: #666;">
                                                    <?php echo $review['destination_name']; ?>
                                                </p>
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
                                            <div class="review-date">
                                                Submitted: <?php echo date('F d, Y', strtotime($review['review_date'])); ?>
                                            </div>
                                            <span class="review-status status-pending">
                                                Under Review
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <div class="review-content">
                                        <?php echo nl2br(htmlspecialchars($review['review_text'])); ?>
                                    </div>
                                    
                                    <div class="alert alert-info" style="margin-top: 1rem;">
                                        <i class="fas fa-info-circle"></i>
                                        Your review is being moderated. It will be published once approved.
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                            <div class="empty-state">
                                <i class="fas fa-clock"></i>
                                <h3>No Pending Reviews</h3>
                                <p>All your reviews have been processed.</p>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </main>
            </div>
        </div>
    </section>
    
    <?php include '../includes/footer.php'; ?>
    
    <script>
    // Tab functionality
    function openTab(tabName) {
        // Hide all tab contents
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.classList.remove('active');
        });
        
        // Remove active class from all tab buttons
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        
        // Show selected tab content
        document.getElementById(tabName + '-tab').classList.add('active');
        
        // Add active class to clicked button
        event.currentTarget.classList.add('active');
    }
    
    // Review form functionality
    function openReviewForm(bookingId, packageId) {
        // Close any other open forms
        document.querySelectorAll('.review-form').forEach(form => {
            form.classList.remove('active');
        });
        
        // Open this form
        const form = document.getElementById('review-form-' + bookingId);
        form.classList.add('active');
        
        // Scroll to form
        form.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
    
    function closeReviewForm(bookingId) {
        const form = document.getElementById('review-form-' + bookingId);
        form.classList.remove('active');
    }
    
    // Star rating functionality
    function setRating(bookingId, rating) {
        const stars = document.querySelectorAll('#rating-' + bookingId + ' .star');
        const ratingInput = document.getElementById('rating-input-' + bookingId);
        
        ratingInput.value = rating;
        
        // Update star display
        stars.forEach((star, index) => {
            if (index < rating) {
                star.classList.add('selected');
            } else {
                star.classList.remove('selected');
            }
        });
    }
    
    // Hover effect for stars
    document.addEventListener('mouseover', function(e) {
        if (e.target.classList.contains('star')) {
            const bookingId = e.target.closest('.star-rating').id.split('-')[1];
            const rating = parseInt(e.target.dataset.value);
            const stars = document.querySelectorAll('#rating-' + bookingId + ' .star');
            
            stars.forEach((star, index) => {
                if (index < rating) {
                    star.classList.add('hover');
                } else {
                    star.classList.remove('hover');
                }
            });
        }
    });
    
    document.addEventListener('mouseout', function(e) {
        if (e.target.classList.contains('star')) {
            const bookingId = e.target.closest('.star-rating').id.split('-')[1];
            const stars = document.querySelectorAll('#rating-' + bookingId + ' .star');
            
            stars.forEach(star => {
                star.classList.remove('hover');
            });
        }
    });
    
    // Review actions
    function editReview(reviewId) {
        // In a real app, this would load the review into an edit form
        alert('Edit functionality would load the review for editing');
    }
    
    function shareReview(reviewId) {
        const reviewText = document.querySelector('#review-' + reviewId + ' .review-content').textContent;
        const shareText = 'Check out my review on ' + window.location.href;
        
        if (navigator.share) {
            navigator.share({
                title: 'My Travel Review',
                text: reviewText.substring(0, 100) + '...',
                url: window.location.href,
            });
        } else {
            navigator.clipboard.writeText(shareText).then(() => {
                alert('Review link copied to clipboard!');
            });
        }
    }
    
    function deleteReview(reviewId) {
        if (confirm('Are you sure you want to delete this review?')) {
            // In a real app, this would make an AJAX request
            window.location.href = 'delete-review.php?id=' + reviewId;
        }
    }
    
    // Initialize star ratings
    document.addEventListener('DOMContentLoaded', function() {
        // Set default rating of 5 for all forms
        document.querySelectorAll('.review-form').forEach(form => {
            const bookingId = form.id.split('-')[2];
            setRating(bookingId, 5);
        });
    });
    </script>
</body>
</html>