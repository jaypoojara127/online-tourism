<?php
require_once '../includes/config.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="/online-tourism/assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    .error-container {
        text-align: center;
        padding: 6rem 2rem;
    }
    
    .error-code {
        font-size: 8rem;
        font-weight: 800;
        color: var(--primary-color);
        line-height: 1;
        margin-bottom: 1rem;
    }
    
    .error-message {
        font-size: 2rem;
        margin-bottom: 1rem;
        color: var(--dark-color);
    }
    
    .error-description {
        font-size: 1.2rem;
        color: #666;
        max-width: 600px;
        margin: 0 auto 2rem;
    }
    
    .error-actions {
        display: flex;
        gap: 1rem;
        justify-content: center;
        flex-wrap: wrap;
    }
    
    .search-box {
        max-width: 500px;
        margin: 2rem auto;
    }
    
    @media (max-width: 768px) {
        .error-code {
            font-size: 5rem;
        }
        
        .error-message {
            font-size: 1.5rem;
        }
    }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <div class="container">
        <div class="error-container">
            <div class="error-code">404</div>
            <h1 class="error-message">Page Not Found</h1>
            <p class="error-description">
                The page you are looking for might have been removed, had its name changed, 
                or is temporarily unavailable.
            </p>
            
            <!-- Search Box -->
            <div class="search-box">
                <form action="packages.php" method="GET" class="search-form" style="display: flex; background: #f8f9fa; border-radius: 50px; overflow: hidden;">
                    <input type="text" name="search" placeholder="Search for packages, destinations..." style="flex: 1; padding: 1rem 1.5rem; border: none; background: transparent;">
                    <button type="submit" style="background: var(--primary-color); color: white; border: none; padding: 0 1.5rem; cursor: pointer;">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
            
            <div class="error-actions">
                <a href="../index.php" class="btn btn-primary">
                    <i class="fas fa-home"></i> Go to Homepage
                </a>
                <a href="javascript:history.back()" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Go Back
                </a>
                <a href="contact.php" class="btn btn-outline">
                    <i class="fas fa-headset"></i> Contact Support
                </a>
            </div>
            
            <!-- Popular Links -->
            <div class="popular-links" style="margin-top: 3rem;">
                <h3>Popular Pages</h3>
                <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap; margin-top: 1rem;">
                    <a href="packages.php" class="btn btn-sm btn-outline">Tour Packages</a>
                    <a href="destinations.php" class="btn btn-sm btn-outline">Destinations</a>
                    <a href="contact.php" class="btn btn-sm btn-outline">Contact Us</a>
                    <a href="../user/dashboard.php" class="btn btn-sm btn-outline">Dashboard</a>
                </div>
            </div>
        </div>
    </div>
    
    <?php include '../includes/footer.php'; ?>
</body>
</html>