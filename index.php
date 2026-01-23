<?php
require_once 'includes/config.php';
require_once 'includes/functions.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?> - Home</title>
    <link rel="stylesheet" href="/online-tourism/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <?php include 'includes/header.php'; ?>
    
    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <h1>Discover Amazing Places</h1>
            <p>Explore the world with our handpicked tour packages and create unforgettable memories</p>
            <a href="pages/packages.php" class="btn btn-primary">Explore Tours</a>
        </div>
    </section>
    
    <!-- Popular Destinations -->
    <section class="section">
        <div class="container">
            <h2 class="section-title">Popular Destinations</h2>
            <div class="destinations-grid">
                <?php
                $destinations = $functions->getPopularDestinations(6);
                foreach ($destinations as $destination):
                ?>
                <div class="destination-card">
                    <img src="assets/images/destinations/<?php echo $destination['featured_image'] ?? 'default.jpg'; ?>" 
                         alt="<?php echo $destination['name']; ?>">
                    <div class="destination-info">
                        <h3><?php echo $destination['name']; ?></h3>
                        <p><?php echo $destination['city'] . ', ' . $destination['country']; ?></p>
                        <a href="pages/destinations.php?id=<?php echo $destination['destination_id']; ?>" 
                           class="btn btn-secondary">Explore</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    
    <!-- Featured Packages -->
    <section class="section bg-light">
        <div class="container">
            <h2 class="section-title">Featured Tour Packages</h2>
            <div class="packages-grid">
                <?php
                $packages = $functions->getTourPackages(array('limit' => 4));
                foreach ($packages as $package):
                    $discount = !empty($package['discount_price']) ? 
                        round((($package['price_per_person'] - $package['discount_price']) / $package['price_per_person']) * 100) : 0;
                ?>
                <div class="package-card">
                    <?php if($discount > 0): ?>
                    <div class="discount-badge"><?php echo $discount; ?>% OFF</div>
                    <?php endif; ?>
                    <img src="assets/images/packages/<?php echo $package['featured_image'] ?? 'default.jpg'; ?>" 
                         alt="<?php echo $package['package_name']; ?>">
                    <div class="package-info">
                        <h3><?php echo $package['package_name']; ?></h3>
                        <p class="destination"><i class="fas fa-map-marker-alt"></i> <?php echo $package['destination_name']; ?></p>
                        <p class="duration"><i class="fas fa-calendar-alt"></i> <?php echo $package['duration_days']; ?> Days / <?php echo $package['duration_nights']; ?> Nights</p>
                        <div class="price-section">
                            <?php if(!empty($package['discount_price'])): ?>
                            <span class="original-price">₹<?php echo number_format($package['price_per_person']); ?></span>
                            <span class="discount-price">₹<?php echo number_format($package['discount_price']); ?></span>
                            <?php else: ?>
                            <span class="price">₹<?php echo number_format($package['price_per_person']); ?></span>
                            <?php endif; ?>
                            <span class="per-person">per person</span>
                        </div>
                        <a href="pages/package-details.php?id=<?php echo $package['package_id']; ?>" 
                           class="btn btn-primary">View Details</a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="text-center">
                <a href="pages/packages.php" class="btn btn-secondary">View All Packages</a>
            </div>
        </div>
    </section>
    
    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

</body>
</html>