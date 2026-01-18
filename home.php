<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Get popular destinations
$popularDestinations = $functions->getPopularDestinations(6);

// Get featured packages
$featuredPackages = $functions->getTourPackages(array('limit' => 4));

// Get special offers
$specialOffers = $functions->getTourPackages(array(
    'min_price' => 0,
    'max_price' => 15000,
    'limit' => 3
));

// Get testimonials
$testimonials_sql = "SELECT r.*, u.full_name, u.profile_image, 
                            p.package_name, p.featured_image
                     FROM reviews r
                     JOIN users u ON r.user_id = u.user_id
                     JOIN tour_packages p ON r.package_id = p.package_id
                     WHERE r.status = 'approved'
                     ORDER BY r.review_date DESC LIMIT 4";
$testimonials_result = $db->executeQuery($testimonials_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Explore Amazing Destinations</title>
    <link rel="stylesheet" href="/online-tourism/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
    /* Hero Section */
    .hero {
        position: relative;
        height: 80vh;
        background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), 
                    url('../assets/images/hero-bg.jpg');
        background-size: cover;
        background-position: center;
        background-attachment: fixed;
        color: white;
        display: flex;
        align-items: center;
        text-align: center;
        overflow: hidden;
    }
    
    .hero-content {
        max-width: 800px;
        margin: 0 auto;
        padding: 2rem;
        position: relative;
        z-index: 2;
    }
    
    .hero h1 {
        font-size: 3.5rem;
        margin-bottom: 1rem;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        animation: fadeInDown 1s ease;
    }
    
    .hero p {
        font-size: 1.2rem;
        margin-bottom: 2rem;
        opacity: 0.9;
        animation: fadeInUp 1s ease 0.3s both;
    }
    
    .hero-buttons {
        display: flex;
        gap: 1rem;
        justify-content: center;
        flex-wrap: wrap;
        animation: fadeInUp 1s ease 0.6s both;
    }
    
    /* Search Box */
    .search-container {
        background: white;
        border-radius: 10px;
        padding: 2rem;
        box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        max-width: 800px;
        margin: -50px auto 50px;
        position: relative;
        z-index: 10;
    }
    
    .search-title {
        text-align: center;
        margin-bottom: 1.5rem;
        color: var(--dark-color);
    }
    
    .search-form {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 1rem;
    }
    
    .search-group {
        display: flex;
        flex-direction: column;
    }
    
    .search-group label {
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: var(--dark-color);
    }
    
    .search-group input,
    .search-group select {
        padding: 0.8rem;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-family: 'Poppins', sans-serif;
    }
    
    .search-btn {
        align-self: flex-end;
        padding: 0.8rem 2rem;
        background: var(--primary-color);
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        transition: background 0.3s ease;
    }
    
    .search-btn:hover {
        background: #2980b9;
    }
    
    /* Features Section */
    .features {
        padding: 5rem 0;
        background: #f8f9fa;
    }
    
    .features-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 2rem;
        margin-top: 3rem;
    }
    
    .feature-card {
        background: white;
        padding: 2rem;
        border-radius: 10px;
        text-align: center;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
    }
    
    .feature-card:hover {
        transform: translateY(-10px);
    }
    
    .feature-icon {
        width: 70px;
        height: 70px;
        background: var(--primary-color);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
        font-size: 1.8rem;
    }
    
    /* Special Offers */
    .special-offers {
        padding: 5rem 0;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }
    
    .special-offers .section-title {
        color: white;
    }
    
    .offer-card {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border-radius: 10px;
        overflow: hidden;
        transition: transform 0.3s ease;
    }
    
    .offer-card:hover {
        transform: translateY(-10px);
    }
    
    .offer-badge {
        position: absolute;
        top: 1rem;
        right: 1rem;
        background: var(--accent-color);
        color: white;
        padding: 0.5rem 1rem;
        border-radius: 20px;
        font-weight: 600;
        z-index: 1;
    }
    
    /* Testimonials */
    .testimonials {
        padding: 5rem 0;
        background: #f8f9fa;
    }
    
    .testimonial-slider {
        position: relative;
        max-width: 1000px;
        margin: 3rem auto 0;
        overflow: hidden;
    }
    
    .testimonial-track {
        display: flex;
        transition: transform 0.5s ease;
    }
    
    .testimonial-card {
        background: white;
        border-radius: 10px;
        padding: 2rem;
        margin: 0 1rem;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        min-width: calc(50% - 2rem);
        flex-shrink: 0;
    }
    
    .testimonial-header {
        display: flex;
        align-items: center;
        margin-bottom: 1.5rem;
    }
    
    .testimonial-avatar {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        overflow: hidden;
        margin-right: 1rem;
    }
    
    .testimonial-avatar img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .testimonial-rating {
        color: #f39c12;
        margin-bottom: 0.5rem;
    }
    
    .slider-nav {
        display: flex;
        justify-content: center;
        gap: 1rem;
        margin-top: 2rem;
    }
    
    .slider-dot {
        width: 12px;
        height: 12px;
        background: #ddd;
        border-radius: 50%;
        cursor: pointer;
        transition: background 0.3s ease;
    }
    
    .slider-dot.active {
        background: var(--primary-color);
    }
    
    /* Stats Section */
    .stats {
        padding: 4rem 0;
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
        color: white;
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 2rem;
        text-align: center;
    }
    
    .stat-item {
        padding: 2rem;
    }
    
    .stat-number {
        font-size: 3rem;
        font-weight: 700;
        margin-bottom: 0.5rem;
        color: var(--primary-color);
    }
    
    /* Newsletter */
    .newsletter {
        padding: 5rem 0;
        background: linear-gradient(rgba(0,0,0,0.7), rgba(0,0,0,0.7)), 
                    url('../assets/images/newsletter-bg.jpg');
        background-size: cover;
        background-position: center;
        color: white;
        text-align: center;
    }
    
    .newsletter-form {
        max-width: 500px;
        margin: 2rem auto 0;
        display: flex;
        gap: 1rem;
    }
    
    .newsletter-input {
        flex: 1;
        padding: 1rem;
        border: none;
        border-radius: 4px;
        font-family: 'Poppins', sans-serif;
    }
    
    /* Animations */
    @keyframes fadeInDown {
        from {
            opacity: 0;
            transform: translateY(-30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    @media (max-width: 768px) {
        .hero h1 {
            font-size: 2.5rem;
        }
        
        .search-form {
            grid-template-columns: 1fr;
        }
        
        .testimonial-card {
            min-width: calc(100% - 2rem);
        }
        
        .newsletter-form {
            flex-direction: column;
        }
        
        .hero-buttons {
            flex-direction: column;
            align-items: center;
        }
    }
    </style>
</head>
<body>
    <!-- Header -->
    <?php include '../includes/header.php'; ?>
    
    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1>Discover Your Next Adventure</h1>
                <p>Experience the world's most breathtaking destinations with our expertly curated tour packages. From serene beaches to majestic mountains, we've got your perfect getaway.</p>
                <div class="hero-buttons">
                    <a href="packages.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-suitcase"></i> Explore Tours
                    </a>
                    <a href="destinations.php" class="btn btn-secondary btn-lg">
                        <i class="fas fa-map-marked-alt"></i> View Destinations
                    </a>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Search Box -->
    <div class="container">
        <div class="search-container">
            <h2 class="search-title">Find Your Perfect Tour</h2>
            <form method="GET" action="packages.php" class="search-form">
                <div class="search-group">
                    <label for="destination"><i class="fas fa-map-marker-alt"></i> Destination</label>
                    <select id="destination" name="destination">
                        <option value="">Any Destination</option>
                        <?php
                        $destinations = $functions->getPopularDestinations(10);
                        foreach($destinations as $dest):
                        ?>
                        <option value="<?php echo $dest['destination_id']; ?>">
                            <?php echo $dest['name']; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="search-group">
                    <label for="duration"><i class="fas fa-calendar-alt"></i> Duration</label>
                    <select id="duration" name="duration">
                        <option value="">Any Duration</option>
                        <option value="1-3">1-3 Days</option>
                        <option value="4-7">4-7 Days</option>
                        <option value="8+">8+ Days</option>
                    </select>
                </div>
                
                <div class="search-group">
                    <label for="price"><i class="fas fa-rupee-sign"></i> Price Range</label>
                    <select id="price" name="price">
                        <option value="">Any Price</option>
                        <option value="0-10000">Under ₹10,000</option>
                        <option value="10000-20000">₹10,000 - ₹20,000</option>
                        <option value="20000-50000">₹20,000 - ₹50,000</option>
                        <option value="50000+">Over ₹50,000</option>
                    </select>
                </div>
                
                <button type="submit" class="search-btn">
                    <i class="fas fa-search"></i> Search Tours
                </button>
            </form>
        </div>
    </div>
    
    <!-- Popular Destinations -->
    <section class="section">
        <div class="container">
            <h2 class="section-title">Popular Destinations</h2>
            <p class="section-subtitle" style="text-align: center; color: #666; margin-bottom: 3rem;">
                Discover our most loved travel destinations
            </p>
            
            <div class="destinations-grid">
                <?php foreach($popularDestinations as $destination): ?>
                <div class="destination-card">
                    <img src="<?php echo UPLOAD_URL . $destination['featured_image']; ?>" 
                         alt="<?php echo $destination['name']; ?>">
                    <div class="destination-info">
                        <h3><?php echo $destination['name']; ?></h3>
                        <p><?php echo $destination['city'] . ', ' . $destination['country']; ?></p>
                        <a href="destinations.php?id=<?php echo $destination['destination_id']; ?>" 
                           class="btn btn-secondary">Explore</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center" style="margin-top: 3rem;">
                <a href="destinations.php" class="btn btn-primary">
                    <i class="fas fa-compass"></i> View All Destinations
                </a>
            </div>
        </div>
    </section>
    
    <!-- Features -->
    <section class="features">
        <div class="container">
            <h2 class="section-title" style="text-align: center;">Why Choose Us</h2>
            <p class="section-subtitle" style="text-align: center; color: #666; margin-bottom: 3rem;">
                We provide exceptional travel experiences with unmatched service
            </p>
            
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3>Safe & Secure</h3>
                    <p>Your safety is our priority. All tours include comprehensive safety measures and insurance.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-rupee-sign"></i>
                    </div>
                    <h3>Best Price Guarantee</h3>
                    <p>We offer the best prices for premium experiences. Found cheaper? We'll match it!</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h3>24/7 Support</h3>
                    <p>Round-the-clock customer support to assist you throughout your journey.</p>
                </div>
                
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-award"></i>
                    </div>
                    <h3>Expert Guides</h3>
                    <p>Travel with certified guides who know the destinations inside out.</p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Featured Packages -->
    <section class="section bg-light">
        <div class="container">
            <h2 class="section-title">Featured Tour Packages</h2>
            <p class="section-subtitle" style="text-align: center; color: #666; margin-bottom: 3rem;">
                Handpicked experiences for unforgettable memories
            </p>
            
            <div class="packages-grid">
                <?php foreach($featuredPackages as $package): 
                    $discount = !empty($package['discount_price']) ? 
                        round((($package['price_per_person'] - $package['discount_price']) / $package['price_per_person']) * 100) : 0;
                ?>
                <div class="package-card">
                    <?php if($discount > 0): ?>
                    <div class="discount-badge"><?php echo $discount; ?>% OFF</div>
                    <?php endif; ?>
                    
                    <img src="<?php echo UPLOAD_URL . $package['featured_image']; ?>" 
                         alt="<?php echo $package['package_name']; ?>">
                    
                    <div class="package-info">
                        <h3><?php echo $package['package_name']; ?></h3>
                        <p class="destination">
                            <i class="fas fa-map-marker-alt"></i> <?php echo $package['destination_name']; ?>
                        </p>
                        <p class="duration">
                            <i class="fas fa-calendar-alt"></i> 
                            <?php echo $package['duration_days']; ?> Days / <?php echo $package['duration_nights']; ?> Nights
                        </p>
                        
                        <div class="price-section">
                            <?php if(!empty($package['discount_price'])): ?>
                            <span class="original-price">₹<?php echo number_format($package['price_per_person']); ?></span>
                            <span class="discount-price">₹<?php echo number_format($package['discount_price']); ?></span>
                            <?php else: ?>
                            <span class="price">₹<?php echo number_format($package['price_per_person']); ?></span>
                            <?php endif; ?>
                            <span class="per-person">per person</span>
                        </div>
                        
                        <a href="package-details.php?id=<?php echo $package['package_id']; ?>" 
                           class="btn btn-primary">View Details</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <div class="text-center" style="margin-top: 3rem;">
                <a href="packages.php" class="btn btn-secondary">
                    <i class="fas fa-suitcase"></i> View All Packages
                </a>
            </div>
        </div>
    </section>
    
    <!-- Special Offers -->
    <section class="special-offers">
        <div class="container">
            <h2 class="section-title">Special Offers</h2>
            <p class="section-subtitle" style="text-align: center; opacity: 0.9; margin-bottom: 3rem;">
                Limited time deals for smart travelers
            </p>
            
            <div class="packages-grid">
                <?php foreach($specialOffers as $package): 
                    $discount = !empty($package['discount_price']) ? 
                        round((($package['price_per_person'] - $package['discount_price']) / $package['price_per_person']) * 100) : 0;
                ?>
                <div class="offer-card">
                    <div class="offer-badge">SAVE <?php echo $discount; ?>%</div>
                    
                    <img src="<?php echo UPLOAD_URL . $package['featured_image']; ?>" 
                         alt="<?php echo $package['package_name']; ?>"
                         style="width: 100%; height: 200px; object-fit: cover;">
                    
                    <div style="padding: 1.5rem;">
                        <h3 style="margin-bottom: 0.5rem; color: white;"><?php echo $package['package_name']; ?></h3>
                        <p style="color: rgba(255,255,255,0.8); margin-bottom: 1rem;">
                            <i class="fas fa-map-marker-alt"></i> <?php echo $package['destination_name']; ?>
                        </p>
                        
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <div>
                                <?php if(!empty($package['discount_price'])): ?>
                                <span style="text-decoration: line-through; opacity: 0.7; margin-right: 0.5rem;">
                                    ₹<?php echo number_format($package['price_per_person']); ?>
                                </span>
                                <span style="font-size: 1.5rem; font-weight: 700; color: white;">
                                    ₹<?php echo number_format($package['discount_price']); ?>
                                </span>
                                <?php else: ?>
                                <span style="font-size: 1.5rem; font-weight: 700; color: white;">
                                    ₹<?php echo number_format($package['price_per_person']); ?>
                                </span>
                                <?php endif; ?>
                            </div>
                            
                            <a href="package-details.php?id=<?php echo $package['package_id']; ?>" 
                               class="btn btn-primary">
                                Book Now
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    
    <!-- Testimonials -->
    <section class="testimonials">
        <div class="container">
            <h2 class="section-title" style="text-align: center;">What Our Travelers Say</h2>
            <p class="section-subtitle" style="text-align: center; color: #666; margin-bottom: 3rem;">
                Real stories from our happy customers
            </p>
            
            <div class="testimonial-slider">
                <div class="testimonial-track" id="testimonialTrack">
                    <?php while($testimonial = $testimonials_result->fetch_assoc()): ?>
                    <div class="testimonial-card">
                        <div class="testimonial-header">
                            <div class="testimonial-avatar">
                                <?php if(!empty($testimonial['profile_image'])): ?>
                                <img src="<?php echo UPLOAD_URL . $testimonial['profile_image']; ?>" 
                                     alt="<?php echo $testimonial['full_name']; ?>">
                                <?php else: ?>
                                <div style="width: 100%; height: 100%; background: var(--primary-color); 
                                            color: white; display: flex; align-items: center; justify-content: center; 
                                            font-weight: bold;">
                                    <?php echo strtoupper(substr($testimonial['full_name'], 0, 1)); ?>
                                </div>
                                <?php endif; ?>
                            </div>
                            
                            <div>
                                <h4 style="margin: 0 0 0.3rem 0;"><?php echo $testimonial['full_name']; ?></h4>
                                <div class="testimonial-rating">
                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                    <?php if($i <= $testimonial['rating']): ?>
                                    <i class="fas fa-star"></i>
                                    <?php else: ?>
                                    <i class="far fa-star"></i>
                                    <?php endif; ?>
                                    <?php endfor; ?>
                                </div>
                                <small style="color: #666;"><?php echo $testimonial['package_name']; ?></small>
                            </div>
                        </div>
                        
                        <p style="color: #333; line-height: 1.6; font-style: italic;">
                            "<?php echo substr($testimonial['review_text'], 0, 150); ?>..."
                        </p>
                        
                        <div style="color: #666; font-size: 0.9rem; margin-top: 1rem;">
                            <?php echo date('F Y', strtotime($testimonial['review_date'])); ?>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
                
                <div class="slider-nav" id="testimonialNav">
                    <?php 
                    $testimonial_count = $testimonials_result->num_rows;
                    for($i = 0; $i < ceil($testimonial_count / 2); $i++):
                    ?>
                    <span class="slider-dot <?php echo $i === 0 ? 'active' : ''; ?>" 
                          data-slide="<?php echo $i; ?>"></span>
                    <?php endfor; ?>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Stats -->
    <section class="stats">
        <div class="container">
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-number" data-count="5000">0</div>
                    <p>Happy Travelers</p>
                </div>
                
                <div class="stat-item">
                    <div class="stat-number" data-count="150">0</div>
                    <p>Tour Packages</p>
                </div>
                
                <div class="stat-item">
                    <div class="stat-number" data-count="50">0</div>
                    <p>Destinations</p>
                </div>
                
                <div class="stat-item">
                    <div class="stat-number" data-count="98">0</div>
                    <p>% Satisfaction</p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Newsletter -->
    <section class="newsletter">
        <div class="container">
            <h2 style="margin-bottom: 1rem;">Subscribe to Our Newsletter</h2>
            <p style="opacity: 0.9; max-width: 600px; margin: 0 auto;">
                Get the latest travel deals, destination guides, and exclusive offers straight to your inbox.
            </p>
            
            <form class="newsletter-form" id="newsletterForm">
                <input type="email" class="newsletter-input" placeholder="Enter your email address" required>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-paper-plane"></i> Subscribe
                </button>
            </form>
            
            <p style="margin-top: 1rem; font-size: 0.9rem; opacity: 0.7;">
                We respect your privacy. Unsubscribe at any time.
            </p>
        </div>
    </section>
    
    <!-- Footer -->
    <?php include '../includes/footer.php'; ?>
    
    <script src="../assets/js/main.js"></script>
    <script>
    // Testimonial Slider
    const track = document.getElementById('testimonialTrack');
    const dots = document.querySelectorAll('.slider-dot');
    let currentSlide = 0;
    const slideWidth = document.querySelector('.testimonial-card').offsetWidth + 32; // including margin
    
    function updateSlider() {
        track.style.transform = `translateX(-${currentSlide * slideWidth}px)`;
        
        dots.forEach((dot, index) => {
            dot.classList.toggle('active', index === currentSlide);
        });
    }
    
    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            currentSlide = index;
            updateSlider();
        });
    });
    
    // Auto slide
    setInterval(() => {
        currentSlide = (currentSlide + 1) % Math.ceil(track.children.length / 2);
        updateSlider();
    }, 5000);
    
    // Animate stats
    function animateStats() {
        const statNumbers = document.querySelectorAll('.stat-number');
        
        statNumbers.forEach(stat => {
            const target = parseInt(stat.dataset.count);
            const duration = 2000;
            const step = target / (duration / 16); // 60fps
            
            let current = 0;
            const timer = setInterval(() => {
                current += step;
                if (current >= target) {
                    current = target;
                    clearInterval(timer);
                }
                stat.textContent = Math.floor(current);
            }, 16);
        });
    }
    
    // Intersection Observer for stats animation
    const statsSection = document.querySelector('.stats');
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateStats();
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });
    
    if (statsSection) {
        observer.observe(statsSection);
    }
    
    // Newsletter form
    document.getElementById('newsletterForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const email = this.querySelector('input').value;
        
        // Simulate subscription
        this.innerHTML = '<div class="alert alert-success" style="margin: 0;">Thank you for subscribing!</div>';
        
        // In production, this would be an AJAX call
        console.log('Subscribed email:', email);
    });
    
    // Initialize animations
    document.addEventListener('DOMContentLoaded', function() {
        // Add animation classes
        const animatedElements = document.querySelectorAll('.feature-card, .destination-card, .package-card');
        animatedElements.forEach((el, index) => {
            el.style.animationDelay = `${index * 0.1}s`;
            el.classList.add('animate-on-scroll');
        });
        
        // Initialize scroll animations
        initAnimations();
    });
    </script>
</body>
</html>