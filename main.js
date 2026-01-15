/**
 * Online Tourism Services - Main JavaScript File
 * Contains all front-end functionality
 */

// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components
    initNavigation();
    initMobileMenu();
    initSearch();
    initImageGalleries();
    initForms();
    initAnimations();
    initBookingCalculator();
    initDatePickers();
    initNotifications();
    
    // Set current year in footer
    document.getElementById('current-year')?.textContent = new Date().getFullYear();
});

/**
 * Navigation initialization
 */
function initNavigation() {
    // Add active class to current page in navigation
    const currentPage = window.location.pathname.split('/').pop();
    const navLinks = document.querySelectorAll('.nav-links a');
    
    navLinks.forEach(link => {
        const linkPage = link.getAttribute('href').split('/').pop();
        if (linkPage === currentPage || 
            (currentPage === '' && linkPage === 'index.php')) {
            link.classList.add('active');
        }
    });
    
    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            if (targetId !== '#') {
                const targetElement = document.querySelector(targetId);
                if (targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 80,
                        behavior: 'smooth'
                    });
                }
            }
        });
    });
}

/**
 * Mobile menu toggle
 */
function initMobileMenu() {
    const menuToggle = document.querySelector('.menu-toggle');
    const navLinks = document.querySelector('.nav-links');
    
    if (menuToggle) {
        menuToggle.addEventListener('click', function() {
            navLinks.classList.toggle('active');
            this.classList.toggle('active');
        });
        
        // Close menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!menuToggle.contains(e.target) && !navLinks.contains(e.target)) {
                navLinks.classList.remove('active');
                menuToggle.classList.remove('active');
            }
        });
        
        // Close menu on window resize
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                navLinks.classList.remove('active');
                menuToggle.classList.remove('active');
            }
        });
    }
}

/**
 * Search functionality
 */
function initSearch() {
    const searchBtn = document.querySelector('.search-btn');
    const searchBox = document.querySelector('.search-box');
    
    if (searchBtn && searchBox) {
        searchBtn.addEventListener('click', function() {
            searchBox.classList.toggle('active');
            if (searchBox.classList.contains('active')) {
                searchBox.querySelector('input').focus();
            }
        });
        
        // Close search when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchBtn.contains(e.target) && !searchBox.contains(e.target)) {
                searchBox.classList.remove('active');
            }
        });
    }
    
    // Live search functionality
    const searchInput = document.querySelector('.search-box input');
    if (searchInput) {
        let searchTimeout;
        
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            
            if (this.value.length >= 2) {
                searchTimeout = setTimeout(() => {
                    performLiveSearch(this.value);
                }, 500);
            }
        });
    }
}

/**
 * Perform live search
 */
async function performLiveSearch(query) {
    try {
        const response = await fetch(`api/search.php?q=${encodeURIComponent(query)}`);
        const results = await response.json();
        
        displaySearchResults(results);
    } catch (error) {
        console.error('Search error:', error);
    }
}

/**
 * Display search results
 */
function displaySearchResults(results) {
    const resultsContainer = document.querySelector('.search-results');
    if (!resultsContainer) return;
    
    resultsContainer.innerHTML = '';
    
    if (results.length === 0) {
        resultsContainer.innerHTML = '<div class="no-results">No results found</div>';
        return;
    }
    
    results.forEach(result => {
        const resultItem = document.createElement('a');
        resultItem.href = result.url;
        resultItem.className = 'search-result-item';
        resultItem.innerHTML = `
            <div class="search-result-content">
                <h4>${result.title}</h4>
                <p>${result.description}</p>
                <span class="result-type">${result.type}</span>
            </div>
        `;
        resultsContainer.appendChild(resultItem);
    });
}

/**
 * Initialize image galleries
 */
function initImageGalleries() {
    // Package gallery lightbox
    const galleryImages = document.querySelectorAll('.gallery-item img');
    galleryImages.forEach(img => {
        img.addEventListener('click', function() {
            openLightbox(this.src, this.alt);
        });
    });
    
    // Destination gallery
    const destinationImages = document.querySelectorAll('.destination-gallery img');
    destinationImages.forEach(img => {
        img.addEventListener('click', function() {
            openLightbox(this.src, this.alt);
        });
    });
}

/**
 * Open lightbox for images
 */
function openLightbox(src, alt) {
    const lightbox = document.createElement('div');
    lightbox.className = 'lightbox';
    lightbox.innerHTML = `
        <div class="lightbox-content">
            <span class="close-lightbox">&times;</span>
            <img src="${src}" alt="${alt}">
            <div class="lightbox-caption">${alt}</div>
        </div>
    `;
    
    document.body.appendChild(lightbox);
    
    // Close lightbox
    lightbox.querySelector('.close-lightbox').addEventListener('click', () => {
        document.body.removeChild(lightbox);
    });
    
    lightbox.addEventListener('click', (e) => {
        if (e.target === lightbox) {
            document.body.removeChild(lightbox);
        }
    });
    
    // Close with ESC key
    document.addEventListener('keydown', function closeOnEsc(e) {
        if (e.key === 'Escape') {
            document.body.removeChild(lightbox);
            document.removeEventListener('keydown', closeOnEsc);
        }
    });
}

/**
 * Form initialization and validation
 */
function initForms() {
    // Contact form validation
    const contactForm = document.getElementById('contact-form');
    if (contactForm) {
        contactForm.addEventListener('submit', function(e) {
            if (!validateContactForm()) {
                e.preventDefault();
            }
        });
    }
    
    // Booking form validation
    const bookingForm = document.getElementById('booking-form');
    if (bookingForm) {
        bookingForm.addEventListener('submit', function(e) {
            if (!validateBookingForm()) {
                e.preventDefault();
            }
        });
    }
    
    // Registration form validation
    const registerForm = document.getElementById('register-form');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            if (!validateRegisterForm()) {
                e.preventDefault();
            }
        });
    }
    
    // Login form validation
    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            if (!validateLoginForm()) {
                e.preventDefault();
            }
        });
    }
}

/**
 * Validate contact form
 */
function validateContactForm() {
    const name = document.getElementById('name').value.trim();
    const email = document.getElementById('email').value.trim();
    const message = document.getElementById('message').value.trim();
    
    clearFormErrors();
    
    let isValid = true;
    
    if (!name) {
        showError('name', 'Name is required');
        isValid = false;
    }
    
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!email) {
        showError('email', 'Email is required');
        isValid = false;
    } else if (!emailRegex.test(email)) {
        showError('email', 'Please enter a valid email address');
        isValid = false;
    }
    
    if (!message) {
        showError('message', 'Message is required');
        isValid = false;
    } else if (message.length < 10) {
        showError('message', 'Message must be at least 10 characters');
        isValid = false;
    }
    
    return isValid;
}

/**
 * Validate booking form
 */
function validateBookingForm() {
    const travelDate = document.getElementById('travel_date').value;
    const numTravelers = document.getElementById('num_travelers').value;
    const today = new Date().toISOString().split('T')[0];
    
    clearFormErrors();
    
    let isValid = true;
    
    if (!travelDate) {
        showError('travel_date', 'Please select a travel date');
        isValid = false;
    } else if (travelDate < today) {
        showError('travel_date', 'Travel date cannot be in the past');
        isValid = false;
    }
    
    if (!numTravelers || numTravelers < 1) {
        showError('num_travelers', 'Please enter number of travelers');
        isValid = false;
    } else if (numTravelers > 10) {
        showError('num_travelers', 'Maximum 10 travelers per booking');
        isValid = false;
    }
    
    return isValid;
}

/**
 * Validate registration form
 */
function validateRegisterForm() {
    const fullName = document.getElementById('full_name').value.trim();
    const username = document.getElementById('username').value.trim();
    const email = document.getElementById('email').value.trim();
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    clearFormErrors();
    
    let isValid = true;
    
    if (!fullName) {
        showError('full_name', 'Full name is required');
        isValid = false;
    }
    
    if (!username) {
        showError('username', 'Username is required');
        isValid = false;
    } else if (username.length < 3) {
        showError('username', 'Username must be at least 3 characters');
        isValid = false;
    }
    
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!email) {
        showError('email', 'Email is required');
        isValid = false;
    } else if (!emailRegex.test(email)) {
        showError('email', 'Please enter a valid email address');
        isValid = false;
    }
    
    if (!password) {
        showError('password', 'Password is required');
        isValid = false;
    } else if (password.length < 6) {
        showError('password', 'Password must be at least 6 characters');
        isValid = false;
    }
    
    if (!confirmPassword) {
        showError('confirm_password', 'Please confirm your password');
        isValid = false;
    } else if (password !== confirmPassword) {
        showError('confirm_password', 'Passwords do not match');
        isValid = false;
    }
    
    return isValid;
}

/**
 * Validate login form
 */
function validateLoginForm() {
    const username = document.getElementById('username').value.trim();
    const password = document.getElementById('password').value;
    
    clearFormErrors();
    
    let isValid = true;
    
    if (!username) {
        showError('username', 'Username or email is required');
        isValid = false;
    }
    
    if (!password) {
        showError('password', 'Password is required');
        isValid = false;
    }
    
    return isValid;
}

/**
 * Show error message for form field
 */
function showError(fieldId, message) {
    const field = document.getElementById(fieldId);
    const formGroup = field.closest('.form-group');
    
    const errorElement = document.createElement('div');
    errorElement.className = 'error-message';
    errorElement.textContent = message;
    
    field.classList.add('error');
    formGroup.appendChild(errorElement);
}

/**
 * Clear all form errors
 */
function clearFormErrors() {
    document.querySelectorAll('.error-message').forEach(error => error.remove());
    document.querySelectorAll('.error').forEach(field => field.classList.remove('error'));
}

/**
 * Initialize animations
 */
function initAnimations() {
    // Intersection Observer for scroll animations
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver(function(entries) {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-in');
            }
        });
    }, observerOptions);
    
    // Observe elements for animation
    document.querySelectorAll('.animate-on-scroll').forEach(el => {
        observer.observe(el);
    });
    
    // Parallax effect for hero sections
    window.addEventListener('scroll', function() {
        const scrolled = window.pageYOffset;
        const parallaxElements = document.querySelectorAll('.parallax');
        
        parallaxElements.forEach(element => {
            const speed = element.dataset.speed || 0.5;
            element.style.transform = `translateY(${scrolled * speed}px)`;
        });
    });
}

/**
 * Initialize booking calculator
 */
function initBookingCalculator() {
    const pricePerPerson = document.querySelector('.price-per-person')?.dataset.price;
    const numTravelersInput = document.getElementById('num_travelers');
    const totalAmountDisplay = document.getElementById('total-amount');
    
    if (pricePerPerson && numTravelersInput && totalAmountDisplay) {
        function updateTotal() {
            const travelers = parseInt(numTravelersInput.value) || 1;
            const total = parseFloat(pricePerPerson) * travelers;
            totalAmountDisplay.textContent = '₹' + total.toLocaleString('en-IN', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }
        
        numTravelersInput.addEventListener('input', updateTotal);
        updateTotal(); // Initial calculation
    }
}

/**
 * Initialize date pickers
 */
function initDatePickers() {
    const dateInputs = document.querySelectorAll('input[type="date"]');
    
    dateInputs.forEach(input => {
        // Set min date to tomorrow
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        const minDate = tomorrow.toISOString().split('T')[0];
        input.setAttribute('min', minDate);
        
        // Set max date to 1 year from now
        const nextYear = new Date();
        nextYear.setFullYear(nextYear.getFullYear() + 1);
        const maxDate = nextYear.toISOString().split('T')[0];
        input.setAttribute('max', maxDate);
    });
}

/**
 * Initialize notifications
 */
function initNotifications() {
    // Check for session messages
    const sessionMessages = document.querySelectorAll('.session-message');
    
    sessionMessages.forEach(message => {
        // Auto-hide after 5 seconds
        setTimeout(() => {
            message.style.opacity = '0';
            setTimeout(() => {
                if (message.parentNode) {
                    message.parentNode.removeChild(message);
                }
            }, 300);
        }, 5000);
        
        // Add close button
        const closeBtn = document.createElement('button');
        closeBtn.className = 'close-notification';
        closeBtn.innerHTML = '&times;';
        closeBtn.addEventListener('click', () => {
            message.style.opacity = '0';
            setTimeout(() => {
                if (message.parentNode) {
                    message.parentNode.removeChild(message);
                }
            }, 300);
        });
        
        message.appendChild(closeBtn);
    });
}

/**
 * Show notification toast
 */
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification toast notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${getNotificationIcon(type)}"></i>
            <span>${message}</span>
        </div>
        <button class="close-notification">&times;</button>
    `;
    
    document.body.appendChild(notification);
    
    // Add close functionality
    notification.querySelector('.close-notification').addEventListener('click', () => {
        notification.remove();
    });
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.remove();
        }
    }, 5000);
}

/**
 * Get notification icon based on type
 */
function getNotificationIcon(type) {
    switch(type) {
        case 'success': return 'check-circle';
        case 'error': return 'exclamation-circle';
        case 'warning': return 'exclamation-triangle';
        default: return 'info-circle';
    }
}

/**
 * Toggle password visibility
 */
function togglePasswordVisibility(inputId) {
    const input = document.getElementById(inputId);
    const toggleBtn = document.querySelector(`[data-toggle="${inputId}"]`);
    
    if (input.type === 'password') {
        input.type = 'text';
        toggleBtn.innerHTML = '<i class="fas fa-eye-slash"></i>';
    } else {
        input.type = 'password';
        toggleBtn.innerHTML = '<i class="fas fa-eye"></i>';
    }
}

/**
 * Format currency
 */
function formatCurrency(amount) {
    return '₹' + amount.toLocaleString('en-IN', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

/**
 * Load more items (AJAX pagination)
 */
async function loadMoreItems(containerId, page, url) {
    const container = document.getElementById(containerId);
    const loadMoreBtn = document.querySelector('.load-more-btn');
    
    if (loadMoreBtn) {
        loadMoreBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
        loadMoreBtn.disabled = true;
    }
    
    try {
        const response = await fetch(`${url}&page=${page}`);
        const data = await response.json();
        
        if (data.items && data.items.length > 0) {
            // Append new items
            data.items.forEach(item => {
                const itemElement = createItemElement(item);
                container.appendChild(itemElement);
            });
            
            // Update load more button
            if (loadMoreBtn) {
                if (data.hasMore) {
                    loadMoreBtn.dataset.page = page + 1;
                    loadMoreBtn.innerHTML = 'Load More';
                    loadMoreBtn.disabled = false;
                } else {
                    loadMoreBtn.style.display = 'none';
                }
            }
        }
    } catch (error) {
        console.error('Error loading more items:', error);
        if (loadMoreBtn) {
            loadMoreBtn.innerHTML = 'Error loading. Try again.';
            setTimeout(() => {
                loadMoreBtn.innerHTML = 'Load More';
                loadMoreBtn.disabled = false;
            }, 3000);
        }
    }
}

/**
 * Create item element for AJAX loading
 */
function createItemElement(item) {
    const div = document.createElement('div');
    div.className = 'item';
    // Customize based on your item structure
    return div;
}

/**
 * Add to wishlist
 */
async function addToWishlist(packageId) {
    try {
        const response = await fetch('api/wishlist.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                package_id: packageId,
                action: 'add'
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showNotification('Added to wishlist!', 'success');
            updateWishlistCount();
        } else {
            showNotification(result.message, 'error');
        }
    } catch (error) {
        showNotification('Error adding to wishlist', 'error');
    }
}

/**
 * Update wishlist count
 */
function updateWishlistCount() {
    const countElements = document.querySelectorAll('.wishlist-count');
    
    countElements.forEach(element => {
        const currentCount = parseInt(element.textContent) || 0;
        element.textContent = currentCount + 1;
    });
}

/**
 * Share on social media
 */
function shareOnSocial(platform, url, text) {
    const shareUrls = {
        facebook: `https://www.facebook.com/sharer/sharer.php?u=${encodeURIComponent(url)}`,
        twitter: `https://twitter.com/intent/tweet?url=${encodeURIComponent(url)}&text=${encodeURIComponent(text)}`,
        whatsapp: `https://api.whatsapp.com/send?text=${encodeURIComponent(text + ' ' + url)}`,
        linkedin: `https://www.linkedin.com/shareArticle?mini=true&url=${encodeURIComponent(url)}&title=${encodeURIComponent(text)}`
    };
    
    if (shareUrls[platform]) {
        window.open(shareUrls[platform], '_blank', 'width=600,height=400');
    }
}

/**
 * Copy to clipboard
 */
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(() => {
        showNotification('Copied to clipboard!', 'success');
    }).catch(err => {
        console.error('Failed to copy: ', err);
        showNotification('Failed to copy', 'error');
    });
}

/**
 * Initialize tooltips
 */
function initTooltips() {
    const tooltipElements = document.querySelectorAll('[data-tooltip]');
    
    tooltipElements.forEach(element => {
        element.addEventListener('mouseenter', showTooltip);
        element.addEventListener('mouseleave', hideTooltip);
    });
}

function showTooltip(e) {
    const tooltip = document.createElement('div');
    tooltip.className = 'tooltip';
    tooltip.textContent = this.getAttribute('data-tooltip');
    document.body.appendChild(tooltip);
    
    const rect = this.getBoundingClientRect();
    tooltip.style.position = 'fixed';
    tooltip.style.left = rect.left + 'px';
    tooltip.style.top = (rect.top - tooltip.offsetHeight - 10) + 'px';
    tooltip.style.zIndex = '10000';
    
    this._tooltip = tooltip;
}

function hideTooltip() {
    if (this._tooltip) {
        document.body.removeChild(this._tooltip);
        delete this._tooltip;
    }
}

/**
 * Debounce function for performance
 */
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

/**
 * Throttle function for performance
 */
function throttle(func, limit) {
    let inThrottle;
    return function() {
        const args = arguments;
        const context = this;
        if (!inThrottle) {
            func.apply(context, args);
            inThrottle = true;
            setTimeout(() => inThrottle = false, limit);
        }
    };
}

// Export functions for use in other scripts
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        showNotification,
        formatCurrency,
        copyToClipboard,
        validateContactForm,
        validateBookingForm,
        validateRegisterForm,
        validateLoginForm
    };
}