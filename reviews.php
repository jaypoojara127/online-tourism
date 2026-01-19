<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

$auth->checkUserAuth();
$user_id = $_SESSION['user_id'];

// Get user reviews
$sql = "SELECT r.*, p.package_name, p.featured_image
        FROM reviews r
        JOIN tour_packages p ON r.package_id = p.package_id
        WHERE r.user_id = '$user_id'
        ORDER BY r.review_date DESC";
$result = $db->executeQuery($sql);
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
    
    .review-card {
        background: #f8f9fa;
        border-radius: 8px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        display: flex;
        gap: 1.5rem;
    }
    
    .review-package-img {
        width: 120px;
        height: 120px;
        border-radius: 8px;
        overflow: hidden;
        flex-shrink: 0;
    }
    
    .review-package-img img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .review-content {
        flex: 1;
    }
    
    .review-header {
        display: flex;
        justify-content: space-between;
        margin-bottom: 1rem;
    }
    
    .package-title {
        font-size: 1.2rem;
        font-weight: 600;
        color: var(--dark-color);
        margin-bottom: 0.2rem;
    }
    
    .review-date {
        color: #666;
        font-size: 0.9rem;
    }
    
    .rating {
        color: #f1c40f;
        margin-bottom: 0.5rem;
    }
    
    .review-text {
        color: #555;
        line-height: 1.6;
    }
    
    .review-status {
        display: inline-block;
        padding: 0.3rem 0.8rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        margin-top: 1rem;
    }
    
    .status-approved { background: #d4edda; color: #155724; }
    .status-pending { background: #fff3cd; color: #856404; }
    .status-rejected { background: #f8d7da; color: #721c24; }
    
    .empty-reviews {
        text-align: center;
        padding: 3rem;
        color: #666;
    }
    
    .empty-reviews i {
        font-size: 4rem;
        margin-bottom: 1rem;
        color: #ddd;
    }
    
    @media (max-width: 768px) {
        .review-card {
            flex-direction: column;
        }
        
        .review-package-img {
            width: 100%;
            height: 200px;
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
                        </div>
                        
                        <?php if(isset($_SESSION['success'])): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
                        </div>
                        <?php endif; ?>
                        
                        <?php if($result->num_rows > 0): ?>
                            <?php while($review = $result->fetch_assoc()): ?>
                            <div class="review-card">
                                <div class="review-package-img">
                                    <img src="<?php echo UPLOAD_URL . $review['featured_image']; ?>" alt="<?php echo $review['package_name']; ?>">
                                </div>
                                <div class="review-content">
                                    <div class="review-header">
                                        <div>
                                            <div class="package-title"><?php echo $review['package_name']; ?></div>
                                            <div class="review-date">
                                                <i class="far fa-calendar-alt"></i> 
                                                <?php echo date('d M, Y', strtotime($review['review_date'])); ?>
                                            </div>
                                        </div>
                                        <div class="rating">
                                            <?php for($i = 1; $i <= 5; $i++): ?>
                                                <?php if($i <= $review['rating']): ?>
                                                <i class="fas fa-star"></i>
                                                <?php else: ?>
                                                <i class="far fa-star"></i>
                                                <?php endif; ?>
                                            <?php endfor; ?>
                                        </div>
                                    </div>
                                    
                                    <p class="review-text">
                                        <?php echo nl2br(htmlspecialchars($review['review_text'])); ?>
                                    </p>
                                    
                                    <span class="review-status status-<?php echo $review['status']; ?>">
                                        Status: <?php echo ucfirst($review['status']); ?>
                                    </span>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="empty-reviews">
                                <i class="fas fa-star"></i>
                                <h3>No Reviews Yet</h3>
                                <p>You haven't written any reviews yet. Complete a tour to share your experience!</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </main>
            </div>
        </div>
    </section>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>
