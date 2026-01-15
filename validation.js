// Form Validation Functions
function validateRegisterForm() {
    const fullName = document.getElementById('full_name').value;
    const username = document.getElementById('username').value;
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const confirmPassword = document.getElementById('confirm_password').value;
    
    // Reset previous errors
    clearErrors();
    
    let isValid = true;
    
    // Full Name validation
    if (fullName.trim() === '') {
        showError('full_name', 'Full name is required');
        isValid = false;
    }
    
    // Username validation
    if (username.trim() === '') {
        showError('username', 'Username is required');
        isValid = false;
    } else if (username.length < 3) {
        showError('username', 'Username must be at least 3 characters');
        isValid = false;
    }
    
    // Email validation
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (email.trim() === '') {
        showError('email', 'Email is required');
        isValid = false;
    } else if (!emailRegex.test(email)) {
        showError('email', 'Please enter a valid email address');
        isValid = false;
    }
    
    // Password validation
    if (password === '') {
        showError('password', 'Password is required');
        isValid = false;
    } else if (password.length < 6) {
        showError('password', 'Password must be at least 6 characters');
        isValid = false;
    }
    
    // Confirm password validation
    if (confirmPassword === '') {
        showError('confirm_password', 'Please confirm your password');
        isValid = false;
    } else if (password !== confirmPassword) {
        showError('confirm_password', 'Passwords do not match');
        isValid = false;
    }
    
    return isValid;
}

function validateLoginForm() {
    const username = document.getElementById('username').value;
    const password = document.getElementById('password').value;
    
    clearErrors();
    
    let isValid = true;
    
    if (username.trim() === '') {
        showError('username', 'Username or Email is required');
        isValid = false;
    }
    
    if (password === '') {
        showError('password', 'Password is required');
        isValid = false;
    }
    
    return isValid;
}

function validateBookingForm() {
    const travelDate = document.getElementById('travel_date').value;
    const numTravelers = document.getElementById('num_travelers').value;
    
    clearErrors();
    
    let isValid = true;
    const today = new Date().toISOString().split('T')[0];
    
    if (travelDate === '') {
        showError('travel_date', 'Travel date is required');
        isValid = false;
    } else if (travelDate < today) {
        showError('travel_date', 'Travel date cannot be in the past');
        isValid = false;
    }
    
    if (numTravelers === '' || numTravelers < 1) {
        showError('num_travelers', 'Number of travelers is required');
        isValid = false;
    } else if (numTravelers > 10) {
        showError('num_travelers', 'Maximum 10 travelers per booking');
        isValid = false;
    }
    
    return isValid;
}

// Utility functions
function showError(fieldId, message) {
    const field = document.getElementById(fieldId);
    const formGroup = field.closest('.form-group');
    
    // Create error element
    const errorElement = document.createElement('div');
    errorElement.className = 'error-message';
    errorElement.style.color = '#e74c3c';
    errorElement.style.fontSize = '0.85rem';
    errorElement.style.marginTop = '0.3rem';
    errorElement.textContent = message;
    
    // Add error class to input
    field.style.borderColor = '#e74c3c';
    
    // Add error message
    formGroup.appendChild(errorElement);
}

function clearErrors() {
    // Remove all error messages
    const errorMessages = document.querySelectorAll('.error-message');
    errorMessages.forEach(error => error.remove());
    
    // Reset border colors
    const inputs = document.querySelectorAll('input, textarea, select');
    inputs.forEach(input => {
        input.style.borderColor = '#ddd';
    });
}

// Search and Filter Functionality
function filterPackages() {
    const destination = document.getElementById('filter-destination').value;
    const minPrice = document.getElementById('filter-min-price').value;
    const maxPrice = document.getElementById('filter-max-price').value;
    const duration = document.getElementById('filter-duration').value;
    
    // Build query string
    const params = new URLSearchParams();
    if (destination) params.append('destination', destination);
    if (minPrice) params.append('min_price', minPrice);
    if (maxPrice) params.append('max_price', maxPrice);
    if (duration) params.append('duration', duration);
    
    // Redirect to filtered page
    window.location.href = `packages.php?${params.toString()}`;
}

// Rating System
function initializeRatingSystem() {
    const stars = document.querySelectorAll('.star-rating .star');
    
    stars.forEach(star => {
        star.addEventListener('click', function() {
            const rating = this.dataset.value;
            document.getElementById('rating').value = rating;
            
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
            const currentRating = document.getElementById('rating').value;
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
}

// Payment Form Validation
function validatePaymentForm() {
    const cardNumber = document.getElementById('card_number').value;
    const expiry = document.getElementById('expiry').value;
    const cvv = document.getElementById('cvv').value;
    
    clearErrors();
    
    let isValid = true;
    
    // Card number validation
    const cardRegex = /^[0-9]{16}$/;
    if (!cardRegex.test(cardNumber.replace(/\s/g, ''))) {
        showError('card_number', 'Please enter a valid 16-digit card number');
        isValid = false;
    }
    
    // Expiry date validation
    const expiryRegex = /^(0[1-9]|1[0-2])\/([0-9]{2})$/;
    if (!expiryRegex.test(expiry)) {
        showError('expiry', 'Please enter expiry date in MM/YY format');
        isValid = false;
    }
    
    // CVV validation
    const cvvRegex = /^[0-9]{3,4}$/;
    if (!cvvRegex.test(cvv)) {
        showError('cvv', 'Please enter a valid CVV (3 or 4 digits)');
        isValid = false;
    }
    
    return isValid;
}

// Auto-save form data
function autoSaveForm(formId) {
    const form = document.getElementById(formId);
    const inputs = form.querySelectorAll('input, textarea, select');
    
    inputs.forEach(input => {
        // Load saved data
        const savedValue = localStorage.getItem(`${formId}_${input.name}`);
        if (savedValue) {
            input.value = savedValue;
        }
        
        // Save on input change
        input.addEventListener('input', function() {
            localStorage.setItem(`${formId}_${this.name}`, this.value);
        });
    });
}

// Clear saved form data
function clearSavedForm(formId) {
    const form = document.getElementById(formId);
    const inputs = form.querySelectorAll('input, textarea, select');
    
    inputs.forEach(input => {
        localStorage.removeItem(`${formId}_${input.name}`);
    });
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize rating system if exists
    if (document.querySelector('.star-rating')) {
        initializeRatingSystem();
    }
    
    // Initialize auto-save for booking form
    if (document.getElementById('bookingForm')) {
        autoSaveForm('bookingForm');
    }
});