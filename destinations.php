<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Get all destinations
$sql = "SELECT * FROM destinations WHERE status = 'active' ORDER BY name";
$result = $db->executeQuery($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Destinations - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="/online-tourism/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    .destinations-hero {
        background: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('../assets/images/destinations-bg.jpg');
        background-size: cover;
        background-position: center;
        color: white;
        padding: 6rem 0;
        text-align: center;
        margin-bottom: 3rem;
    }
    
    .destinations-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 2rem;
        margin: 2rem 0;
    }
    
    .destination-card-large {
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        transition: transform 0.3s ease;
    }
    
    .destination-card-large:hover {
        transform: translateY(-10px);
    }
    
    .destination-image {
        height: 250px;
        overflow: hidden;
    }
    
    .destination-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }
    
    .destination-card-large:hover .destination-image img {
        transform: scale(1.1);
    }
    
    .destination-content {
        padding: 1.5rem;
    }
    
    .destination-content h3 {
        margin-bottom: 0.5rem;
        color: var(--dark-color);
    }
    
    .destination-location {
        color: var(--primary-color);
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .destination-description {
        color: #666;
        margin-bottom: 1rem;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    
    .destination-attractions {
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid #eee;
    }
    
    .destination-attractions h4 {
        margin-bottom: 0.5rem;
        font-size: 0.9rem;
        color: #666;
    }
    
    .attractions-list {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }
    
    .attraction-tag {
        background: #f8f9fa;
        padding: 0.3rem 0.8rem;
        border-radius: 20px;
        font-size: 0.8rem;
        color: #666;
    }
    
    .filter-section {
        background: white;
        padding: 1.5rem;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        margin-bottom: 2rem;
    }
    
    .filter-form {
        display: flex;
        gap: 1rem;
        flex-wrap: wrap;
    }
    
    .filter-group {
        flex: 1;
        min-width: 200px;
    }
    
    .filter-group label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
    }
    
    .filter-group select {
        width: 100%;
        padding: 0.8rem;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
    
    .empty-state {
        text-align: center;
        padding: 4rem 2rem;
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .empty-state i {
        font-size: 4rem;
        color: #ddd;
        margin-bottom: 1rem;
    }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <!-- Hero Section -->
    <section class="destinations-hero">
        <div class="container">
            <h1>Explore Amazing Destinations</h1>
            <p>Discover the world's most beautiful places with our curated destinations</p>
        </div>
    </section>
    
    <div class="container">
        <!-- Filter Section -->
        <div class="filter-section">
            <h2 style="margin-bottom: 1rem;">Find Your Perfect Destination</h2>
            <form method="GET" class="filter-form">
                <div class="filter-group">
                    <label for="country">Country</label>
                    <select id="country" name="country">
                        <option value="">All Countries</option>
                        <?php
                        $countries_sql = "SELECT DISTINCT country FROM destinations WHERE status = 'active' ORDER BY country";
                        $countries_result = $db->executeQuery($countries_sql);
                        while($country = $countries_result->fetch_assoc()):
                        ?>
                        <option value="<?php echo $country['country']; ?>"><?php echo $country['country']; ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label for="best_time">Best Time to Visit</label>
                    <select id="best_time" name="best_time">
                        <option value="">Any Time</option>
                        <option value="Summer">Summer</option>
                        <option value="Winter">Winter</option>
                        <option value="Spring">Spring</option>
                        <option value="Autumn">Autumn</option>
                        <option value="All Year">All Year</option>
                    </select>
                </div>
                
                <div class="filter-group">
                    <label>&nbsp;</label>
                    <button type="submit" class="btn btn-primary" style="padding: 0.8rem 2rem;">Filter</button>
                </div>
            </form>
        </div>
        
        <!-- Destinations Grid -->
        <?php if($result->num_rows > 0): ?>
        <div class="destinations-grid">
            <?php while($destination = $result->fetch_assoc()): ?>
            <div class="destination-card-large">
                <div class="destination-image">
                    <img src="<?php echo UPLOAD_URL . $destination['featured_image']; ?>" alt="<?php echo $destination['name']; ?>">
                </div>
                <div class="destination-content">
                    <h3><?php echo $destination['name']; ?></h3>
                    <div class="destination-location">
                        <i class="fas fa-map-marker-alt"></i>
                        <span><?php echo $destination['city'] . ', ' . $destination['country']; ?></span>
                    </div>
                    <div class="destination-description">
                        <?php echo substr($destination['description'], 0, 150) . '...'; ?>
                    </div>
                    
                    <?php if(!empty($destination['best_time_to_visit'])): ?>
                    <p><strong>Best Time:</strong> <?php echo $destination['best_time_to_visit']; ?></p>
                    <?php endif; ?>
                    
                    <?php if(!empty($destination['attractions'])): ?>
                    <div class="destination-attractions">
                        <h4>Top Attractions</h4>
                        <div class="attractions-list">
                            <?php
                            $attractions = explode("\n", $destination['attractions']);
                            $count = 0;
                            foreach($attractions as $attraction):
                                if(trim($attraction) && $count < 3):
                                    $count++;
                            ?>
                            <span class="attraction-tag"><?php echo trim($attraction); ?></span>
                            <?php endif; endforeach; ?>
                            <?php if(count($attractions) > 3): ?>
                            <span class="attraction-tag">+<?php echo count($attractions) - 3; ?> more</span>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <div style="margin-top: 1.5rem;">
                        <a href="destination-details.php?id=<?php echo $destination['destination_id']; ?>" class="btn btn-primary">Explore Destination</a>
                        <a href="packages.php?destination=<?php echo $destination['destination_id']; ?>" class="btn btn-outline">View Packages</a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-map-marked-alt"></i>
            <h3>No Destinations Found</h3>
            <p>Check back later for new destinations!</p>
        </div>
        <?php endif; ?>
    </div>
    
    <?php include '../includes/footer.php'; ?>
    
    <script>
    // Filter functionality
    document.addEventListener('DOMContentLoaded', function() {
        const countrySelect = document.getElementById('country');
        const bestTimeSelect = document.getElementById('best_time');
        
        // Get URL parameters
        const urlParams = new URLSearchParams(window.location.search);
        const countryParam = urlParams.get('country');
        const bestTimeParam = urlParams.get('best_time');
        
        if (countryParam) {
            countrySelect.value = countryParam;
        }
        
        if (bestTimeParam) {
            bestTimeSelect.value = bestTimeParam;
        }
    });
    </script>
</body>
</html>