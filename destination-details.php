<?php
require_once '../includes/config.php';
require_once '../includes/functions.php';

$destination_id = $_GET['id'] ?? 0;
$destination = $functions->getDestinationDetails($destination_id);

if (!$destination) {
    header('Location: destinations.php');
    exit();
}

// Get packages for this destination
$packages = $functions->getTourPackages(['destination_id' => $destination_id]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $destination['name']; ?> - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="/online-tourism/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .destination-hero {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), url('<?php echo UPLOAD_URL . $destination['featured_image']; ?>');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 8rem 0;
            text-align: center;
            margin-bottom: 4rem;
        }
        
        .destination-info {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            margin-bottom: 3rem;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #eee;
        }
        
        .info-item h4 {
            color: var(--primary-color);
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .gallery-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }
        
        .gallery-item {
            height: 200px;
            border-radius: 8px;
            overflow: hidden;
            cursor: pointer;
            transition: transform 0.3s ease;
        }
        
        .gallery-item:hover {
            transform: scale(1.05);
        }
        
        .gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .related-packages {
            margin-top: 4rem;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <!-- Hero Section -->
    <section class="destination-hero">
        <div class="container">
            <h1 style="font-size: 3.5rem; margin-bottom: 1rem;"><?php echo $destination['name']; ?></h1>
            <p style="font-size: 1.2rem; max-width: 800px; margin: 0 auto;">
                <i class="fas fa-map-marker-alt"></i> <?php echo $destination['city'] . ', ' . $destination['country']; ?>
            </p>
        </div>
    </section>
    
    <div class="container">
        <!-- Main Info -->
        <div class="destination-info">
            <h2 style="margin-bottom: 1.5rem;">About <?php echo $destination['name']; ?></h2>
            <div style="font-size: 1.1rem; line-height: 1.8; color: #555;">
                <?php echo nl2br($destination['description']); ?>
            </div>
            
            <div class="info-grid">
                <div class="info-item">
                    <h4><i class="fas fa-sun"></i> Best Time to Visit</h4>
                    <p><?php echo $destination['best_time_to_visit']; ?></p>
                </div>
                
                <div class="info-item">
                    <h4><i class="fas fa-camera"></i> Top Attractions</h4>
                    <ul style="list-style: none; padding: 0;">
                        <?php 
                        $attractions = explode("\n", $destination['attractions']);
                        foreach($attractions as $attraction): 
                            if(trim($attraction)):
                        ?>
                        <li style="margin-bottom: 0.3rem;"><i class="fas fa-check" style="color: var(--success-color); font-size: 0.8rem; margin-right: 0.5rem;"></i> <?php echo trim($attraction); ?></li>
                        <?php endif; endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
        
        <!-- Packages -->
        <?php if(!empty($packages)): ?>
        <div class="related-packages">
            <h2 style="margin-bottom: 2rem;">Available Packages for <?php echo $destination['name']; ?></h2>
            <div class="packages-grid-large">
                <?php foreach($packages as $package): 
                    $available = $package['max_capacity'] - $package['current_bookings'];
                    $discount = !empty($package['discount_price']) ? 
                        round((($package['price_per_person'] - $package['discount_price']) / $package['price_per_person']) * 100) : 0;
                ?>
                <div class="package-card-large">
                    <?php if($discount > 0): ?>
                    <div class="package-badge"><?php echo $discount; ?>% OFF</div>
                    <?php endif; ?>
                    
                    <div class="package-image">
                        <img src="<?php echo UPLOAD_URL . $package['featured_image']; ?>" alt="<?php echo $package['package_name']; ?>">
                    </div>
                    
                    <div class="package-content">
                        <h3><?php echo $package['package_name']; ?></h3>
                        <div class="package-meta">
                            <span><i class="fas fa-clock"></i> <?php echo $package['duration_days']; ?> Days</span>
                            <span><i class="fas fa-moon"></i> <?php echo $package['duration_nights']; ?> Nights</span>
                        </div>
                        
                        <div class="package-description">
                            <?php echo substr($package['overview'], 0, 100) . '...'; ?>
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
                            </div>
                            
                            <a href="package-details.php?id=<?php echo $package['package_id']; ?>" class="btn btn-primary">
                                View Details
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>
