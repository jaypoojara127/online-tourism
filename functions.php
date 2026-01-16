<?php
require_once 'db.php';

class TourismFunctions {
    private $db;
    
    public function __construct() {
        global $db;
        $this->db = $db;
    }
    
    // User Registration
    public function registerUser($username, $email, $password, $full_name, $phone = '', $address = '') {
        $username = $this->db->escapeString($username);
        $email = $this->db->escapeString($email);
        $full_name = $this->db->escapeString($full_name);
        $phone = $this->db->escapeString($phone);
        $address = $this->db->escapeString($address);
        
        // Check if user exists
        $check_sql = "SELECT user_id FROM users WHERE email = '$email' OR username = '$username'";
        $check_result = $this->db->executeQuery($check_sql);
        
        if ($check_result->num_rows > 0) {
            return array('success' => false, 'message' => 'Username or Email already exists');
        }
        
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert user
        $sql = "INSERT INTO users (username, email, password, full_name, phone, address) 
                VALUES ('$username', '$email', '$hashed_password', '$full_name', '$phone', '$address')";
        
        $result = $this->db->executeQuery($sql);
        
        if ($result) {
            return array('success' => true, 'message' => 'Registration successful');
        } else {
            return array('success' => false, 'message' => 'Registration failed');
        }
    }
    
    // User Login
    public function loginUser($username, $password) {
        $username = $this->db->escapeString($username);
        
        $sql = "SELECT user_id, username, email, password, full_name FROM users WHERE username = '$username' OR email = '$username'";
        $result = $this->db->executeQuery($sql);
        
        if ($result->num_rows == 1) {
            $user = $result->fetch_assoc();
            
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['user_role'] = 'user';
                
                return array('success' => true, 'message' => 'Login successful');
            }
        }
        
        return array('success' => false, 'message' => 'Invalid credentials');
    }
    
    // Get Popular Destinations
    public function getPopularDestinations($limit = 6) {
        $sql = "SELECT d.*, COUNT(b.booking_id) as bookings_count 
                FROM destinations d
                LEFT JOIN tour_packages p ON d.destination_id = p.destination_id
                LEFT JOIN bookings b ON p.package_id = b.package_id
                WHERE d.status = 'active'
                GROUP BY d.destination_id
                ORDER BY bookings_count DESC, d.created_at DESC
                LIMIT $limit";
        
        $result = $this->db->executeQuery($sql);
        $destinations = array();
        
        while ($row = $result->fetch_assoc()) {
            $destinations[] = $row;
        }
        
        return $destinations;
    }
    
    // Get Tour Packages with filters
    public function getTourPackages($filters = array()) {
        $sql = "SELECT p.*, d.name as destination_name, d.city, d.country
                FROM tour_packages p
                LEFT JOIN destinations d ON p.destination_id = d.destination_id
                WHERE p.status = 'active'";
        
        // Apply filters
        if (!empty($filters['destination_id'])) {
            $destination_id = $this->db->escapeString($filters['destination_id']);
            $sql .= " AND p.destination_id = '$destination_id'";
        }
        
        if (!empty($filters['min_price'])) {
            $min_price = $this->db->escapeString($filters['min_price']);
            $sql .= " AND p.price_per_person >= $min_price";
        }
        
        if (!empty($filters['max_price'])) {
            $max_price = $this->db->escapeString($filters['max_price']);
            $sql .= " AND p.price_per_person <= $max_price";
        }
        
        if (!empty($filters['duration'])) {
            $duration = $this->db->escapeString($filters['duration']);
            if ($duration == '8+') {
                $sql .= " AND p.duration_days >= 8";
            } elseif (strpos($duration, '-') !== false) {
                $parts = explode('-', $duration);
                if (count($parts) == 2) {
                    $min_days = (int)$parts[0];
                    $max_days = (int)$parts[1];
                    $sql .= " AND p.duration_days BETWEEN $min_days AND $max_days";
                }
            } else {
                // Fallback for exact number if simple number passed
                $sql .= " AND p.duration_days = '$duration'";
            }
        }
        
        if (!empty($filters['search'])) {
            $search = $this->db->escapeString($filters['search']);
            $sql .= " AND (p.package_name LIKE '%$search%' OR d.name LIKE '%$search%' OR d.city LIKE '%$search%')";
        }
        
        $sql .= " ORDER BY p.created_at DESC";
        
        if (!empty($filters['limit'])) {
            $limit = $this->db->escapeString($filters['limit']);
            $sql .= " LIMIT $limit";
        }
        
        $result = $this->db->executeQuery($sql);
        $packages = array();
        
        while ($row = $result->fetch_assoc()) {
            $packages[] = $row;
        }
        
        return $packages;
    }
    
    // Get Package Details
    public function getPackageDetails($package_id) {
        $package_id = $this->db->escapeString($package_id);
        
        $sql = "SELECT p.*, d.name as destination_name, d.city, d.country, d.description as destination_desc
                FROM tour_packages p
                LEFT JOIN destinations d ON p.destination_id = d.destination_id
                WHERE p.package_id = '$package_id'";
        
        $result = $this->db->executeQuery($sql);
        
        if ($result->num_rows == 1) {
            $package = $result->fetch_assoc();
            
            // Get itinerary
            $package['itinerary'] = $this->getPackageItinerary($package_id);
            
            // Get inclusions
            $package['inclusions'] = $this->getPackageInclusions($package_id);
            
            // Get gallery
            $package['gallery'] = $this->getPackageGallery($package_id);
            
            // Get reviews
            $package['reviews'] = $this->getPackageReviews($package_id);
            
            return $package;
        }
        
        return false;
    }
    
    // Get Package Itinerary
    public function getPackageItinerary($package_id) {
        $package_id = $this->db->escapeString($package_id);
        $sql = "SELECT * FROM itinerary WHERE package_id = '$package_id' ORDER BY day_number ASC";
        $result = $this->db->executeQuery($sql);
        $itinerary = array();
        while($row = $result->fetch_assoc()) {
            $itinerary[] = $row;
        }
        return $itinerary;
    }

    // Get Package Inclusions
    public function getPackageInclusions($package_id) {
        $package_id = $this->db->escapeString($package_id);
        $sql = "SELECT * FROM inclusions WHERE package_id = '$package_id'";
        $result = $this->db->executeQuery($sql);
        $inclusions = array();
        while($row = $result->fetch_assoc()) {
            $inclusions[] = $row;
        }
        return $inclusions;
    }

    // Get Package Gallery
    public function getPackageGallery($package_id) {
        $package_id = $this->db->escapeString($package_id);
        $sql = "SELECT * FROM package_gallery WHERE package_id = '$package_id'";
        $result = $this->db->executeQuery($sql);
        $gallery = array();
        while($row = $result->fetch_assoc()) {
            $gallery[] = $row;
        }
        return $gallery;
    }

    // Get Package Reviews
    public function getPackageReviews($package_id) {
        $package_id = $this->db->escapeString($package_id);
        $sql = "SELECT r.*, u.full_name, u.profile_image 
                FROM reviews r 
                JOIN users u ON r.user_id = u.user_id 
                WHERE r.package_id = '$package_id' AND r.status = 'approved' 
                ORDER BY r.review_date DESC";
        $result = $this->db->executeQuery($sql);
        $reviews = array();
        while($row = $result->fetch_assoc()) {
            $reviews[] = $row;
        }
        return $reviews;
    }

    // Get Destination Details
    public function getDestinationDetails($destination_id) {
        $destination_id = $this->db->escapeString($destination_id);
        
        $sql = "SELECT * FROM destinations WHERE destination_id = '$destination_id'";
        $result = $this->db->executeQuery($sql);
        
        if ($result->num_rows == 1) {
            return $result->fetch_assoc();
        }
        
        return false;
    }
    
    // Create Booking
    public function createBooking($user_id, $package_id, $travel_date, $num_travelers, $special_requests = '') {
        $user_id = $this->db->escapeString($user_id);
        $package_id = $this->db->escapeString($package_id);
        $travel_date = $this->db->escapeString($travel_date);
        $num_travelers = $this->db->escapeString($num_travelers);
        $special_requests = $this->db->escapeString($special_requests);
        
        // Get package price
        $package_sql = "SELECT price_per_person, discount_price FROM tour_packages WHERE package_id = '$package_id'";
        $package_result = $this->db->executeQuery($package_sql);
        $package = $package_result->fetch_assoc();
        
        // Calculate total amount
        $price = !empty($package['discount_price']) ? $package['discount_price'] : $package['price_per_person'];
        $total_amount = $price * $num_travelers;
        
        // Insert booking
        $booking_sql = "INSERT INTO bookings (user_id, package_id, booking_date, travel_date, num_travelers, total_amount, special_requests) 
                       VALUES ('$user_id', '$package_id', CURDATE(), '$travel_date', '$num_travelers', '$total_amount', '$special_requests')";
        
        $result = $this->db->executeQuery($booking_sql);
        
        if ($result) {
            $booking_id = $this->db->getLastInsertId();
            return array('success' => true, 'booking_id' => $booking_id, 'total_amount' => $total_amount);
        }
        
        return array('success' => false, 'message' => 'Booking failed');
    }
}

// Create global instance
$functions = new TourismFunctions();
?>