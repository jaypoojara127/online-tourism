<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Get filter parameters
$destination_id = $_GET['destination'] ?? '';
$min_price = $_GET['min_price'] ?? '';
$max_price = $_GET['max_price'] ?? '';
$duration = $_GET['duration'] ?? '';
$search = $_GET['search'] ?? '';

// Build filters array
$filters = array();
if (!empty($destination_id)) $filters['destination_id'] = $destination_id;
if (!empty($min_price)) $filters['min_price'] = $min_price;
if (!empty($max_price)) $filters['max_price'] = $max_price;
if (!empty($duration)) $filters['duration'] = $duration;
if (!empty($search)) $filters['search'] = $search;

// Get packages with filters
$packages = $functions->getTourPackages($filters);

// Get destinations for filter dropdown
$destinations_sql = "SELECT * FROM destinations WHERE status = 'active' ORDER BY name";
$destinations_result = $db->executeQuery($destinations_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tour Packages - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="/online-tourism/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    .packages-hero {
        background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('../assets/images/packages-bg.jpg');
        background-size: cover;
        background-position: center;
        color: white;
        padding: 6rem 0;
        text-align: center;
        margin-bottom: 3rem;
    }
    
    .search-bar {
        max-width: 600px;
        margin: 2rem auto;
    }
    
    .search-form {
        display: flex;
        background: white;
        border-radius: 50px;
        overflow: hidden;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    
    .search-form input {
        flex: 1;
        padding: 1rem 1.5rem;
        border: none;
        outline: none;
        font-size: 1rem;
    }
    
    .search-form button {
        background: var(--primary-color);
        color: white;
        border: none;
        padding: 0 2rem;
        cursor: pointer;
        transition: background 0.3s ease;
    }
    
    .search-form button:hover {
        background: #2980b9;
    }
    
    .filter-sidebar {
        background: white;
        border-radius: 8px;
        padding: 1.5rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin-bottom: 2rem;
    }
    
    .filter-group {
        margin-bottom: 1.5rem;
    }
    
    .filter-group h3 {
        margin-bottom: 1rem;
        color: var(--dark-color);
        font-size: 1.1rem;
    }
    
    .price-range {
        display: flex;
        gap: 1rem;
        align-items: center;
    }
    
    .price-range input {
        width: 100px;
        padding: 0.5rem;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
    
    .filter-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    
    .filter-tag {
        padding: 0.5rem 1rem;
        background: #f8f9fa;
        border-radius: 20px;
        font-size: 0.9rem;
        cursor: pointer;
        transition: all 0.3s ease;
    }
    
    .filter-tag:hover {
        background: var(--primary-color);
        color: white;
    }
    
    .filter-tag.active {
        background: var(--primary-color);
        color: white;
    }
    
    .packages-layout {
        display: grid;
        grid-template-columns: 250px 1fr;
        gap: 2rem;
    }
    
    .packages-grid-large {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 2rem;
    }
    
    .package-card-large {
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
    }
    
    .package-card-large:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 15px rgba(0,0,0,0.1);
    }
    
    .package-badge {
        position: absolute;
        top: 1rem;
        left: 1rem;
        background: var(--accent-color);
        color: white;
        padding: 0.3rem 0.8rem;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 600;
        z-index: 1;
    }
    
    .package-image {
        height: 200px;
        overflow: hidden;
        position: relative;
    }
    
    .package-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    
    .package-card-large:hover .package-image img {
        transform: scale(1.1);
    }
    
    .package-content {
        padding: 1.5rem;
    }
    
    .package-content h3 {
        margin-bottom: 0.5rem;
        color: var(--dark-color);
        font-size: 1.2rem;
    }
    
    .package-meta {
        display: flex;
        justify-content: space-between;
        margin-bottom: 1rem;
        font-size: 0.9rem;
        color: #666;
    }
    
    .package-meta span {
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .package-description {
        color: #666;
        margin-bottom: 1.5rem;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
    .package-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid #eee;
    }
    
    .package-price {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--primary-color);
    }
    
    .package-price small {
        font-size: 0.9rem;
        color: #666;
        font-weight: normal;
    }
    
    .pagination {
        display: flex;
        justify-content: center;
        gap: 0.5rem;
        margin-top: 3rem;
    }
    
    .pagination a {
        padding: 0.5rem 1rem;
        background: white;
        border: 1px solid #ddd;
        border-radius: 4px;
        text-decoration: none;
        color: var(--dark-color);
        transition: all 0.3s ease;
    }
    
    .pagination a:hover,
    .pagination a.active {
        background: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
    }
    
    .empty-packages {
        text-align: center;
        padding: 4rem 2rem;
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        grid-column: 1 / -1;
    }
    
    .empty-packages i {
        font-size: 4rem;
        color: #ddd;
        margin-bottom: 1rem;
    }
    
    @media (max-width: 768px) {
        .packages-layout {
            grid-template-columns: 1fr;
        }
        
        .packages-grid-large {
            grid-template-columns: 1fr;
        }
        
        .search-form {
            flex-direction: column;
            border-radius: 8px;
        }
        
        .search-form input,
        .search-form button {
            width: 100%;
            border-radius: 0;
        }
    }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <!-- Hero Section -->
    <section class="packages-hero">
        <div class="container">
            <h1>Discover Amazing Tour Packages</h1>
            <p>Handpicked experiences for unforgettable journeys</p>
            
            <!-- Search Bar -->
            <div class="search-bar">
                <form method="GET" class="search-form">
                    <input type="text" name="search" placeholder="Search packages, destinations..." value="<?php echo htmlspecialchars($search); ?>">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </form>
            </div>
        </div>
    </section>
    
    <div class="container">
        <div class="packages-layout">
            <!-- Filter Sidebar -->
            <aside class="filter-sidebar">
                <h2 style="margin-bottom: 1.5rem;">Filter Packages</h2>
                
                <!-- Destination Filter -->
                <div class="filter-group">
                    <h3>Destination</h3>
                    <select id="destination" class="filter-select" onchange="filterPackages()" style="width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 4px;">
                        <option value="">All Destinations</option>
                        <?php while($dest = $destinations_result->fetch_assoc()): ?>
                        <option value="<?php echo $dest['destination_id']; ?>" <?php echo $destination_id == $dest['destination_id'] ? 'selected' : ''; ?>>
                            <?php echo $dest['name']; ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <!-- Price Range -->
                <div class="filter-group">
                    <h3>Price Range</h3>
                    <div class="price-range">
                        <input type="number" id="min_price" placeholder="Min" value="<?php echo $min_price; ?>" onchange="filterPackages()">
                        <span>to</span>
                        <input type="number" id="max_price" placeholder="Max" value="<?php echo $max_price; ?>" onchange="filterPackages()">
                    </div>
                </div>
                
                <!-- Duration Filter -->
                <div class="filter-group">
                    <h3>Duration</h3>
                    <div class="filter-tags">
                        <span class="filter-tag <?php echo $duration == '1-3' ? 'active' : ''; ?>" data-duration="1-3" onclick="setDuration('1-3')">1-3 Days</span>
                        <span class="filter-tag <?php echo $duration == '4-7' ? 'active' : ''; ?>" data-duration="4-7" onclick="setDuration('4-7')">4-7 Days</span>
                        <span class="filter-tag <?php echo $duration == '8+' ? 'active' : ''; ?>" data-duration="8+" onclick="setDuration('8+')">8+ Days</span>
                    </div>
                </div>
                
                <!-- Clear Filters -->
                <button class="btn btn-outline btn-block" onclick="clearFilters()">
                    <i class="fas fa-times"></i> Clear Filters
                </button>
            </aside>
            
            <!-- Packages Grid -->
            <main class="packages-main">
                <div class="packages-header" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                    <h2>All Tour Packages</h2>
                    <span class="results-count"><?php echo count($packages); ?> packages found</span>
                </div>
                
                <?php if(!empty($packages)): ?>
                <div class="packages-grid-large">
                    <?php foreach($packages as $package): 
                        $available = $package['max_capacity'] - $package['current_bookings'];
                        $discount = !empty($package['discount_price']) ? 
                            round((($package['price_per_person'] - $package['discount_price']) / $package['price_per_person']) * 100) : 0;
                    ?>
                    <div class="package-card-large">
                        <?php if($discount > 0): ?>
                        <div class="package-badge"><?php echo $discount; ?>% OFF</div>
                        <?php elseif($available <= 3 && $available > 0): ?>
                        <div class="package-badge" style="background: #f39c12;">Only <?php echo $available; ?> left</div>
                        <?php elseif($available <= 0): ?>
                        <div class="package-badge" style="background: #e74c3c;">Sold Out</div>
                        <?php endif; ?>
                        
                        <div class="package-image">
                            <img src="<?php echo UPLOAD_URL . $package['featured_image']; ?>" alt="<?php echo $package['package_name']; ?>">
                        </div>
                        
                        <div class="package-content">
                            <h3><?php echo $package['package_name']; ?></h3>
                            <div class="package-meta">
                                <span><i class="fas fa-map-marker-alt"></i> <?php echo $package['destination_name']; ?></span>
                                <span><i class="fas fa-calendar-alt"></i> <?php echo $package['duration_days']; ?> Days / <?php echo $package['duration_nights']; ?> Nights</span>
                            </div>
                            
                            <div class="package-description">
                                <?php echo substr($package['overview'], 0, 150) . '...'; ?>
                            </div>
                            
                            <div class="package-footer">
                                <div class="package-price">
                                    <?php if(!empty($package['discount_price'])): ?>
                                    <span style="text-decoration: line-through; font-size: 1rem; color: #999; margin-right: 0.5rem;">
                                        ₹<?php echo number_format($package['price_per_person']); ?>
                                    </span>
                                    ₹<?php echo number_format($package['discount_price']); ?>
                                    <?php else: ?>
                                    ₹<?php echo number_format($package['price_per_person']); ?>
                                    <?php endif; ?>
                                    <small>per person</small>
                                </div>
                                
                                <a href="package-details.php?id=<?php echo $package['package_id']; ?>" class="btn btn-primary">
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Pagination -->
                <div class="pagination">
                    <a href="#" class="active">1</a>
                    <a href="#">2</a>
                    <a href="#">3</a>
                    <a href="#">4</a>
                    <a href="#">5</a>
                    <a href="#">Next →</a>
                </div>
                
                <?php else: ?>
                <div class="empty-packages">
                    <i class="fas fa-suitcase"></i>
                    <h3>No Packages Found</h3>
                    <p>Try adjusting your filters or search terms</p>
                    <button class="btn btn-primary" onclick="clearFilters()" style="margin-top: 1rem;">Clear All Filters</button>
                </div>
                <?php endif; ?>
            </main>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    
    <script>
    function filterPackages() {
        const destination = document.getElementById('destination').value;
        const minPrice = document.getElementById('min_price').value;
        const maxPrice = document.getElementById('max_price').value;
        const duration = document.querySelector('.filter-tag.active') ? document.querySelector('.filter-tag.active').dataset.duration : '';
        const search = new URLSearchParams(window.location.search).get('search') || '';
        
        const params = new URLSearchParams();
        if (destination) params.append('destination', destination);
        if (minPrice) params.append('min_price', minPrice);
        if (maxPrice) params.append('max_price', maxPrice);
        if (duration) params.append('duration', duration);
        if (search) params.append('search', search);
        
        window.location.href = `packages.php?${params.toString()}`;
    }
    
    function setDuration(duration) {
        // Remove active class from all duration tags
        document.querySelectorAll('.filter-tag').forEach(tag => {
            tag.classList.remove('active');
        });
        
        // Add active class to clicked tag
        event.target.classList.add('active');
        
        // Filter packages
        filterPackages();
    }
    
    function clearFilters() {
        window.location.href = 'packages.php';
    }
    
    // Initialize active duration tag
    document.addEventListener('DOMContentLoaded', function() {
        const duration = new URLSearchParams(window.location.search).get('duration');
        if (duration) {
            document.querySelectorAll('.filter-tag').forEach(tag => {
                tag.classList.remove('active');
                if (tag.dataset.duration === duration) {
                    tag.classList.add('active');
                }
            });
        }
    });
    </script>
</body>
</html>