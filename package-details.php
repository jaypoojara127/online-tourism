<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

$package_id = $_GET['id'] ?? 0;
if (!$package_id) {
    header('Location: packages.php');
    exit();
}

$package = $functions->getPackageDetails($package_id);
if (!$package) {
    header('Location: packages.php');
    exit();
}

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_review'])) {
    if (!$auth->isLoggedIn()) {
        $_SESSION['error'] = 'Please login to submit a review';
        header('Location: login.php');
        exit();
    }
    
    $user_id = $_SESSION['user_id'];
    $rating = $db->escapeString($_POST['rating']);
    $review_text = $db->escapeString($_POST['review_text']);
    
    // Check if user has booked this package
    $booking_check = "SELECT booking_id FROM bookings 
                      WHERE user_id = '$user_id' 
                      AND package_id = '$package_id'
                      AND booking_status = 'completed'";
    $booking_result = $db->executeQuery($booking_check);
    
    if ($booking_result->num_rows > 0) {
        $booking = $booking_result->fetch_assoc();
        $booking_id = $booking['booking_id'];
        
        $sql = "INSERT INTO reviews (user_id, package_id, booking_id, rating, review_text) 
                VALUES ('$user_id', '$package_id', '$booking_id', '$rating', '$review_text')";
        
        if ($db->executeQuery($sql)) {
            $_SESSION['success'] = 'Thank you for your review!';
            header('Location: package-details.php?id=' . $package_id);
            exit();
        }
    } else {
        $_SESSION['error'] = 'You need to complete a booking before reviewing this package';
    }
}

// Handle booking
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_now'])) {
    if (!$auth->isLoggedIn()) {
        $_SESSION['redirect'] = 'package-details.php?id=' . $package_id;
        header('Location: login.php');
        exit();
    }
    
    $user_id = $_SESSION['user_id'];
    $travel_date = $db->escapeString($_POST['travel_date']);
    $num_travelers = $db->escapeString($_POST['num_travelers']);
    $special_requests = $db->escapeString($_POST['special_requests']);
    
    // Check availability
    $available = $package['max_capacity'] - $package['current_bookings'];
    if ($num_travelers > $available) {
        $_SESSION['error'] = "Only $available spots available for this date";
    } else {
        $result = $functions->createBooking($user_id, $package_id, $travel_date, $num_travelers, $special_requests);
        
        if ($result['success']) {
            header('Location: booking.php?id=' . $result['booking_id']);
            exit();
        } else {
            $_SESSION['error'] = $result['message'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $package['package_name']; ?> - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="/online-tourism/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    .package-header {
        position: relative;
        height: 400px;
        overflow: hidden;
        border-radius: 10px;
        margin-bottom: 2rem;
    }
    
    .package-header img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .package-header-overlay {
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        background: linear-gradient(transparent, rgba(0,0,0,0.8));
        color: white;
        padding: 2rem;
    }
    
    .package-price {
        background: var(--primary-color);
        color: white;
        padding: 0.5rem 1.5rem;
        border-radius: 30px;
        font-size: 1.5rem;
        font-weight: 600;
        display: inline-block;
    }
    
    .package-highlights {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
        margin: 2rem 0;
    }
    
    .highlight-item {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 1rem;
        background: #f8f9fa;
        border-radius: 8px;
    }
    
    .highlight-icon {
        width: 40px;
        height: 40px;
        background: var(--primary-color);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .itinerary-day {
        background: white;
        border-radius: 8px;
        padding: 1.5rem;
        margin-bottom: 1rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .itinerary-day-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        padding-bottom: 1rem;
        border-bottom: 1px solid #eee;
    }
    
    .gallery-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 1rem;
        margin: 2rem 0;
    }
    
    .gallery-item {
        position: relative;
        border-radius: 8px;
        overflow: hidden;
        cursor: pointer;
    }
    
    .gallery-item img {
        width: 100%;
        height: 150px;
        object-fit: cover;
        transition: transform 0.3s ease;
    }
    
    .gallery-item:hover img {
        transform: scale(1.05);
    }
    
    .review-item {
        background: white;
        border-radius: 8px;
        padding: 1.5rem;
        margin-bottom: 1rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .review-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
    }
    
    .review-rating {
        color: #f39c12;
    }
    
    .star-rating {
        display: inline-flex;
        gap: 0.2rem;
        margin-left: 1rem;
    }
    
    .star-rating .star {
        cursor: pointer;
        color: #ddd;
        font-size: 1.5rem;
        transition: color 0.2s;
    }
    
    .star-rating .star.active,
    .star-rating .star:hover,
    .star-rating .star.hover {
        color: #f39c12;
    }
    
    .booking-sidebar {
        background: white;
        border-radius: 8px;
        padding: 1.5rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        position: sticky;
        top: 100px;
    }
    
    .availability-badge {
        display: inline-block;
        padding: 0.3rem 0.8rem;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: 600;
        margin-left: 1rem;
    }
    
    .availability-available {
        background: rgba(46, 204, 113, 0.1);
        color: #27ae60;
    }
    
    .availability-low {
        background: rgba(241, 196, 15, 0.1);
        color: #f39c12;
    }
    
    .availability-soldout {
        background: rgba(231, 76, 60, 0.1);
        color: #c0392b;
    }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <!-- Package Header -->
    <section class="package-header">
        <img src="<?php echo UPLOAD_URL . $package['featured_image']; ?>" alt="<?php echo $package['package_name']; ?>">
        <div class="package-header-overlay">
            <h1><?php echo $package['package_name']; ?></h1>
            <p><i class="fas fa-map-marker-alt"></i> <?php echo $package['destination_name']; ?> | 
               <i class="fas fa-calendar-alt"></i> <?php echo $package['duration_days']; ?> Days / <?php echo $package['duration_nights']; ?> Nights</p>
            <div class="package-price">
                <?php if(!empty($package['discount_price'])): ?>
                <span style="text-decoration: line-through; font-size: 1rem;">₹<?php echo number_format($package['price_per_person']); ?></span>
                ₹<?php echo number_format($package['discount_price']); ?>
                <?php else: ?>
                ₹<?php echo number_format($package['price_per_person']); ?>
                <?php endif; ?>
                <small style="font-size: 0.9rem;"> per person</small>
            </div>
        </div>
    </section>
    
    <div class="container">
        <div class="content-layout">
            <!-- Main Content -->
            <div class="main-content">
                <?php if(isset($_SESSION['success'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></div>
                <?php endif; ?>
                
                <?php if(isset($_SESSION['error'])): ?>
                <div class="alert alert-error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></div>
                <?php endif; ?>
                
                <!-- Package Overview -->
                <div class="section-card">
                    <h2>Package Overview</h2>
                    <p><?php echo $package['overview']; ?></p>
                </div>
                
                <!-- Highlights -->
                <?php if(!empty($package['highlights'])): ?>
                <div class="section-card">
                    <h2>Key Highlights</h2>
                    <div class="package-highlights">
                        <?php
                        $highlights = explode("\n", $package['highlights']);
                        foreach($highlights as $highlight):
                            if(trim($highlight)):
                        ?>
                        <div class="highlight-item">
                            <div class="highlight-icon">
                                <i class="fas fa-check"></i>
                            </div>
                            <span><?php echo trim($highlight); ?></span>
                        </div>
                        <?php endif; endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Itinerary -->
                <?php if(!empty($package['itinerary'])): ?>
                <div class="section-card">
                    <h2>Day-wise Itinerary</h2>
                    <?php foreach($package['itinerary'] as $day): ?>
                    <div class="itinerary-day">
                        <div class="itinerary-day-header">
                            <h3>Day <?php echo $day['day_number']; ?>: <?php echo $day['title']; ?></h3>
                            <?php if(!empty($day['accommodation'])): ?>
                            <span class="accommodation"><i class="fas fa-hotel"></i> <?php echo $day['accommodation']; ?></span>
                            <?php endif; ?>
                        </div>
                        <p><?php echo $day['description']; ?></p>
                        
                        <?php if(!empty($day['meals'])): ?>
                        <p><strong>Meals:</strong> <?php echo $day['meals']; ?></p>
                        <?php endif; ?>
                        
                        <?php if(!empty($day['activities'])): ?>
                        <p><strong>Activities:</strong> <?php echo $day['activities']; ?></p>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <!-- Inclusions & Exclusions -->
                <div class="section-card">
                    <h2>What's Included</h2>
                    <div class="inclusions-grid">
                        <?php if(!empty($package['inclusions'])): ?>
                        <div class="inclusion-section">
                            <h3>Inclusions</h3>
                            <ul>
                                <?php foreach($package['inclusions'] as $inclusion): ?>
                                <li>
                                    <i class="fas fa-check text-success"></i>
                                    <?php echo $inclusion['description']; ?>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        <?php endif; ?>
                        
                        <div class="exclusion-section">
                            <h3>Exclusions</h3>
                            <ul>
                                <li><i class="fas fa-times text-danger"></i> Airfare / Train tickets</li>
                                <li><i class="fas fa-times text-danger"></i> Travel insurance</li>
                                <li><i class="fas fa-times text-danger"></i> Personal expenses</li>
                                <li><i class="fas fa-times text-danger"></i> Optional activities</li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <!-- Gallery -->
                <?php if(!empty($package['gallery'])): ?>
                <div class="section-card">
                    <h2>Photo Gallery</h2>
                    <div class="gallery-grid">
                        <?php foreach($package['gallery'] as $image): ?>
                        <div class="gallery-item">
                            <img src="<?php echo UPLOAD_URL . $image['image_url']; ?>" 
                                 alt="<?php echo $image['caption']; ?>"
                                 data-caption="<?php echo $image['caption']; ?>">
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Reviews -->
                <div class="section-card">
                    <h2>Customer Reviews</h2>
                    
                    <!-- Review Form (for users who have completed booking) -->
                    <?php 
                    $can_review = false;
                    if ($auth->isLoggedIn()) {
                        $user_id = $_SESSION['user_id'];
                        $check_sql = "SELECT booking_id FROM bookings 
                                     WHERE user_id = '$user_id' 
                                     AND package_id = '$package_id'
                                     AND booking_status = 'completed'";
                        $check_result = $db->executeQuery($check_sql);
                        $can_review = $check_result->num_rows > 0;
                        
                        // Check if already reviewed
                        $review_check = "SELECT review_id FROM reviews 
                                        WHERE user_id = '$user_id' 
                                        AND package_id = '$package_id'";
                        $review_result = $db->executeQuery($review_check);
                        $has_reviewed = $review_result->num_rows > 0;
                    }
                    ?>
                    
                    <?php if($can_review && !$has_reviewed): ?>
                    <div class="review-form">
                        <h3>Write a Review</h3>
                        <form method="POST">
                            <div class="form-group">
                                <label>Your Rating:</label>
                                <div class="star-rating">
                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                    <span class="star" data-value="<?php echo $i; ?>">★</span>
                                    <?php endfor; ?>
                                </div>
                                <input type="hidden" id="rating" name="rating" value="5" required>
                            </div>
                            <div class="form-group">
                                <label for="review_text">Your Review:</label>
                                <textarea id="review_text" name="review_text" rows="4" required></textarea>
                            </div>
                            <button type="submit" name="submit_review" class="btn btn-primary">Submit Review</button>
                        </form>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Reviews List -->
                    <div class="reviews-list">
                        <?php if(!empty($package['reviews'])): ?>
                            <?php foreach($package['reviews'] as $review): ?>
                            <div class="review-item">
                                <div class="review-header">
                                    <div>
                                        <h4><?php echo $review['full_name']; ?></h4>
                                        <div class="review-rating">
                                            <?php for($i = 1; $i <= 5; $i++): ?>
                                            <?php if($i <= $review['rating']): ?>
                                            <i class="fas fa-star"></i>
                                            <?php else: ?>
                                            <i class="far fa-star"></i>
                                            <?php endif; ?>
                                            <?php endfor; ?>
                                            <span style="margin-left: 0.5rem; color: #666;"><?php echo date('d M, Y', strtotime($review['review_date'])); ?></span>
                                        </div>
                                    </div>
                                </div>
                                <p><?php echo $review['review_text']; ?></p>
                            </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>No reviews yet. Be the first to review this package!</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Booking Sidebar -->
            <aside class="booking-sidebar">
                <h2>Book This Package</h2>
                
                <?php
                $available = $package['max_capacity'] - $package['current_bookings'];
                $availability_class = $available > 5 ? 'availability-available' : 
                                    ($available > 0 ? 'availability-low' : 'availability-soldout');
                $availability_text = $available > 0 ? "$available spots available" : 'Sold Out';
                ?>
                
                <div class="availability">
                    <p><strong>Availability:</strong> <span class="availability-badge <?php echo $availability_class; ?>"><?php echo $availability_text; ?></span></p>
                </div>
                
                <?php if($available > 0): ?>
                <form method="POST" class="booking-form" onsubmit="return validateBookingForm()">
                    <div class="form-group">
                        <label for="travel_date"><i class="fas fa-calendar-alt"></i> Travel Date *</label>
                        <input type="date" id="travel_date" name="travel_date" required 
                               min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="num_travelers"><i class="fas fa-users"></i> Number of Travelers *</label>
                        <input type="number" id="num_travelers" name="num_travelers" 
                               min="1" max="<?php echo $available; ?>" value="1" required>
                        <small class="text-muted">Maximum <?php echo $available; ?> travelers available</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="special_requests"><i class="fas fa-comment-alt"></i> Special Requests</label>
                        <textarea id="special_requests" name="special_requests" rows="3" 
                                  placeholder="Any special requirements or requests..."></textarea>
                    </div>
                    
                    <div class="price-summary">
                        <h3>Price Summary</h3>
                        <div class="price-details">
                            <div class="price-row">
                                <span>Price per person</span>
                                <span>₹<?php echo number_format(!empty($package['discount_price']) ? $package['discount_price'] : $package['price_per_person']); ?></span>
                            </div>
                            <div class="price-row">
                                <span>Number of travelers</span>
                                <span id="traveler-count">1</span>
                            </div>
                            <div class="price-row total">
                                <strong>Total Amount</strong>
                                <strong id="total-amount">₹<?php echo number_format(!empty($package['discount_price']) ? $package['discount_price'] : $package['price_per_person']); ?></strong>
                            </div>
                        </div>
                    </div>
                    
                    <?php if($auth->isLoggedIn()): ?>
                    <button type="submit" name="book_now" class="btn btn-primary btn-block">Book Now</button>
                    <?php else: ?>
                    <a href="login.php?redirect=package-details.php?id=<?php echo $package_id; ?>" class="btn btn-primary btn-block">Login to Book</a>
                    <?php endif; ?>
                </form>
                <?php else: ?>
                <div class="alert alert-error">
                    <p>This package is currently sold out. Please check back later or explore other packages.</p>
                </div>
                <?php endif; ?>
                
                <div class="need-help">
                    <h3>Need Help?</h3>
                    <p><i class="fas fa-phone"></i> Call: +91 9876543210</p>
                    <p><i class="fas fa-envelope"></i> Email: info@tourism.com</p>
                    <p><i class="fas fa-clock"></i> Mon-Sat: 9 AM - 6 PM</p>
                </div>
            </aside>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    
    <script>
    // Dynamic price calculation
    const pricePerPerson = <?php echo !empty($package['discount_price']) ? $package['discount_price'] : $package['price_per_person']; ?>;
    const numTravelersInput = document.getElementById('num_travelers');
    const travelerCount = document.getElementById('traveler-count');
    const totalAmount = document.getElementById('total-amount');
    
    function updatePrice() {
        const travelers = parseInt(numTravelersInput.value) || 1;
        const total = pricePerPerson * travelers;
        
        travelerCount.textContent = travelers;
        totalAmount.textContent = '₹' + total.toLocaleString('en-IN');
    }
    
    numTravelersInput.addEventListener('input', updatePrice);
    updatePrice(); // Initial calculation
    
    // Star rating
    const stars = document.querySelectorAll('.star-rating .star');
    const ratingInput = document.getElementById('rating');
    
    stars.forEach(star => {
        star.addEventListener('click', function() {
            const rating = this.dataset.value;
            ratingInput.value = rating;
            
            // Update star display
            stars.forEach(s => {
                if (s.dataset.value <= rating) {
                    s.classList.add('active');
                } else {
                    s.classList.remove('active');
                }
            });
        });
        
        star.addEventListener('mouseover', function() {
            const hoverRating = this.dataset.value;
            stars.forEach(s => {
                if (s.dataset.value <= hoverRating) {
                    s.classList.add('hover');
                } else {
                    s.classList.remove('hover');
                }
            });
        });
        
        star.addEventListener('mouseout', function() {
            const currentRating = ratingInput.value;
            stars.forEach(s => {
                s.classList.remove('hover');
                if (s.dataset.value <= currentRating) {
                    s.classList.add('active');
                } else {
                    s.classList.remove('active');
                }
            });
        });
    });
    
    // Form validation
    function validateBookingForm() {
        const travelDate = document.getElementById('travel_date').value;
        const numTravelers = document.getElementById('num_travelers').value;
        const today = new Date().toISOString().split('T')[0];
        
        if (!travelDate) {
            alert('Please select a travel date');
            return false;
        }
        
        if (travelDate < today) {
            alert('Travel date cannot be in the past');
            return false;
        }
        
        if (!numTravelers || numTravelers < 1) {
            alert('Please enter number of travelers');
            return false;
        }
        
        if (numTravelers > <?php echo $available; ?>) {
            alert('Maximum <?php echo $available; ?> travelers allowed');
            return false;
        }
        
        return true;
    }
    
    // Image gallery lightbox
    const galleryItems = document.querySelectorAll('.gallery-item img');
    galleryItems.forEach(img => {
        img.addEventListener('click', function() {
            const lightbox = document.createElement('div');
            lightbox.style.position = 'fixed';
            lightbox.style.top = '0';
            lightbox.style.left = '0';
            lightbox.style.width = '100%';
            lightbox.style.height = '100%';
            lightbox.style.backgroundColor = 'rgba(0,0,0,0.9)';
            lightbox.style.display = 'flex';
            lightbox.style.alignItems = 'center';
            lightbox.style.justifyContent = 'center';
            lightbox.style.zIndex = '10000';
            lightbox.style.cursor = 'pointer';
            
            const lightboxImg = document.createElement('img');
            lightboxImg.src = this.src;
            lightboxImg.style.maxWidth = '90%';
            lightboxImg.style.maxHeight = '90%';
            lightboxImg.style.objectFit = 'contain';
            lightboxImg.style.borderRadius = '8px';
            
            if (this.dataset.caption) {
                const caption = document.createElement('div');
                caption.textContent = this.dataset.caption;
                caption.style.position = 'absolute';
                caption.style.bottom = '20px';
                caption.style.color = 'white';
                caption.style.textAlign = 'center';
                caption.style.width = '100%';
                caption.style.padding = '1rem';
                caption.style.backgroundColor = 'rgba(0,0,0,0.5)';
                lightbox.appendChild(caption);
            }
            
            lightbox.appendChild(lightboxImg);
            document.body.appendChild(lightbox);
            
            // Close on click
            lightbox.addEventListener('click', function() {
                document.body.removeChild(lightbox);
            });
            
            // Close on escape key
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    document.body.removeChild(lightbox);
                }
            });
        });
    });
    </script>
</body>
</html>