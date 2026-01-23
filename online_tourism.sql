-- Complete Database Schema for Online Tourism Services

-- Drop existing tables if they exist
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS users, admin, destinations, tour_packages, itinerary, inclusions, bookings, payments, reviews, package_gallery, contact_messages;
SET FOREIGN_KEY_CHECKS = 1;

-- Create database
CREATE DATABASE IF NOT EXISTS online_tourism;
USE online_tourism;

-- Users table
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    profile_image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_username (username)
);

-- Admin table
CREATE TABLE admin (
    admin_id INT PRIMARY KEY AUTO_INCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    role ENUM('super_admin', 'admin') DEFAULT 'admin',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_username (username)
);

-- Destinations table
CREATE TABLE destinations (
    destination_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    country VARCHAR(100) NOT NULL,
    city VARCHAR(100) NOT NULL,
    description TEXT NOT NULL,
    best_time_to_visit VARCHAR(255),
    attractions TEXT,
    featured_image VARCHAR(255),
    gallery_images TEXT,
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_country (country),
    INDEX idx_status (status)
);

-- Tour packages table
CREATE TABLE tour_packages (
    package_id INT PRIMARY KEY AUTO_INCREMENT,
    package_name VARCHAR(200) NOT NULL,
    destination_id INT,
    duration_days INT NOT NULL,
    duration_nights INT NOT NULL,
    price_per_person DECIMAL(10,2) NOT NULL,
    discount_price DECIMAL(10,2),
    max_capacity INT NOT NULL,
    current_bookings INT DEFAULT 0,
    featured_image VARCHAR(255),
    overview TEXT,
    highlights TEXT,
    status ENUM('active', 'inactive', 'sold_out') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (destination_id) REFERENCES destinations(destination_id) ON DELETE SET NULL,
    INDEX idx_destination (destination_id),
    INDEX idx_status (status),
    INDEX idx_price (price_per_person),
    INDEX idx_created (created_at)
);

-- Itinerary table
CREATE TABLE itinerary (
    itinerary_id INT PRIMARY KEY AUTO_INCREMENT,
    package_id INT,
    day_number INT NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT NOT NULL,
    accommodation VARCHAR(255),
    meals VARCHAR(100),
    activities TEXT,
    FOREIGN KEY (package_id) REFERENCES tour_packages(package_id) ON DELETE CASCADE,
    INDEX idx_package (package_id),
    INDEX idx_day (day_number)
);

-- Inclusions table
CREATE TABLE inclusions (
    inclusion_id INT PRIMARY KEY AUTO_INCREMENT,
    package_id INT,
    inclusion_type ENUM('hotel', 'meal', 'transport', 'activity', 'guide', 'other') NOT NULL,
    description VARCHAR(255) NOT NULL,
    FOREIGN KEY (package_id) REFERENCES tour_packages(package_id) ON DELETE CASCADE,
    INDEX idx_package (package_id),
    INDEX idx_type (inclusion_type)
);

-- Bookings table
CREATE TABLE bookings (
    booking_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    package_id INT,
    booking_date DATE NOT NULL,
    travel_date DATE NOT NULL,
    num_travelers INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    special_requests TEXT,
    booking_status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (package_id) REFERENCES tour_packages(package_id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_package (package_id),
    INDEX idx_status (booking_status),
    INDEX idx_travel_date (travel_date),
    INDEX idx_created (created_at)
);

-- Payments table
CREATE TABLE payments (
    payment_id INT PRIMARY KEY AUTO_INCREMENT,
    booking_id INT,
    payment_method VARCHAR(50),
    transaction_id VARCHAR(100) UNIQUE,
    amount DECIMAL(10,2) NOT NULL,
    payment_status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    receipt_url VARCHAR(255),
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id) ON DELETE CASCADE,
    INDEX idx_booking (booking_id),
    INDEX idx_status (payment_status),
    INDEX idx_transaction (transaction_id),
    INDEX idx_date (payment_date)
);

-- Reviews table
CREATE TABLE reviews (
    review_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT,
    package_id INT,
    booking_id INT,
    rating INT CHECK (rating >= 1 AND rating <= 5),
    review_text TEXT,
    review_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (package_id) REFERENCES tour_packages(package_id) ON DELETE CASCADE,
    FOREIGN KEY (booking_id) REFERENCES bookings(booking_id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_package (package_id),
    INDEX idx_rating (rating),
    INDEX idx_status (status),
    INDEX idx_date (review_date)
);

-- Package gallery table
CREATE TABLE package_gallery (
    gallery_id INT PRIMARY KEY AUTO_INCREMENT,
    package_id INT,
    image_url VARCHAR(255) NOT NULL,
    caption VARCHAR(255),
    FOREIGN KEY (package_id) REFERENCES tour_packages(package_id) ON DELETE CASCADE,
    INDEX idx_package (package_id)
);

-- Contact messages table
CREATE TABLE contact_messages (
    message_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('unread', 'read', 'replied') DEFAULT 'unread',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    replied_at TIMESTAMP NULL,
    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_created (created_at)
);

-- Insert default admin (password: admin123)
INSERT INTO admin (username, password, email, role) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@tourism.com', 'super_admin');

-- Insert sample destinations
INSERT INTO destinations (name, country, city, description, best_time_to_visit, attractions, featured_image) VALUES
('Goa Beaches', 'India', 'Goa', 'Beautiful beaches and Portuguese architecture. Goa is a state in western India with coastlines stretching along the Arabian Sea. Its long history as a Portuguese colony prior to 1961 is evident in its preserved 17th-century churches and the area''s tropical spice plantations.', 'October to March', 'Baga Beach\nCalangute Beach\nFort Aguada\nBasilica of Bom Jesus\nDudhsagar Falls', 'destinations/goa.jpg'),
('Kashmir Valley', 'India', 'Srinagar', 'Paradise on Earth with beautiful valleys. Kashmir is often called "Paradise on Earth" due to its breathtaking landscapes. The region is known for its stunning Mughal gardens, pristine lakes, and snow-capped mountains.', 'March to October', 'Dal Lake\nMughal Gardens\nGulmarg\nPahalgam\nSonamarg', 'destinations/kashmir.jpg'),
('Kerala Backwaters', 'India', 'Alleppey', 'Serene backwaters and lush greenery. Kerala is famous for its backwaters, a network of interconnected canals, rivers, lakes, and inlets. Houseboat cruises through these backwaters are a major tourist attraction.', 'September to March', 'Alleppey Backwaters\nKumarakom\nVembanad Lake\nMarari Beach\nCochin', 'destinations/kerala.jpg'),
('Rajasthan Heritage', 'India', 'Jaipur', 'Royal palaces and desert forts. Rajasthan is known for its historical forts, palaces, art, and culture. The state offers a unique blend of royal heritage and vibrant traditions.', 'November to February', 'Amber Fort\nHawa Mahal\nCity Palace\nJaisalmer Fort\nThar Desert', 'destinations/rajasthan.jpg'),
('Himalayan Treks', 'India', 'Manali', 'Adventure in the mighty Himalayas. Manali is a popular hill station known for its adventure sports, snow-covered mountains, and Buddhist monasteries.', 'March to June, September to November', 'Solang Valley\nRohtang Pass\nHadimba Temple\nOld Manali\nManu Temple', 'destinations/manali.jpg'),
('Andaman Islands', 'India', 'Port Blair', 'Pristine islands and marine life. The Andaman and Nicobar Islands are a group of islands known for their palm-lined white-sand beaches, mangroves, and tropical rainforests.', 'October to May', 'Radhanagar Beach\nCellular Jail\nHavelock Island\nRoss Island\nMahatma Gandhi Marine National Park', 'destinations/andaman.jpg');

-- Insert sample tour packages
INSERT INTO tour_packages (package_name, destination_id, duration_days, duration_nights, price_per_person, discount_price, max_capacity, overview, highlights, featured_image) VALUES
('Goa Beach Special', 1, 5, 4, 15000.00, 12000.00, 20, 'Experience the best of Goa with this 5-day beach package. Enjoy sun, sand, and sea along with Portuguese heritage sites.', 'Beach Hopping\nPortuguese Heritage Tour\nWater Sports\nNightlife Experience\nLocal Cuisine Tasting', 'packages/goa-special.jpg'),
('Kashmir Heaven Tour', 2, 7, 6, 25000.00, 22000.00, 15, 'Discover the beauty of Kashmir with this comprehensive 7-day tour covering all major attractions.', 'Shikara Ride on Dal Lake\nMughal Gardens Visit\nGulmarg Gondola Ride\nPahalgam Valley Tour\nHouseboat Stay', 'packages/kashmir-tour.jpg'),
('Kerala Backwater Bliss', 3, 6, 5, 18000.00, 16000.00, 18, 'Relax in the serene backwaters of Kerala with this 6-day houseboat package.', 'Houseboat Cruise\nAyurvedic Spa Treatment\nKathakali Dance Show\nSpice Plantation Visit\nBeach Visit', 'packages/kerala-backwater.jpg'),
('Rajasthan Royal Experience', 4, 8, 7, 28000.00, 25000.00, 12, 'Live like royalty with this 8-day tour of Rajasthan''s most magnificent palaces and forts.', 'Amber Fort Elephant Ride\nDesert Safari\nCultural Folk Dance\nPalace Hotel Stay\nTraditional Rajasthani Dinner', 'packages/rajasthan-royal.jpg'),
('Himalayan Adventure', 5, 5, 4, 20000.00, NULL, 10, 'For adventure seekers! This 5-day package includes trekking, paragliding, and mountain biking in the Himalayas.', 'Trekking in Solang Valley\nParagliding Experience\nMountain Biking\nRiver Rafting\nCable Car Ride', 'packages/himalayan-adventure.jpg'),
('Andaman Island Hopping', 6, 7, 6, 30000.00, 27000.00, 15, 'Explore the pristine islands of Andaman with this 7-day island hopping package.', 'Scuba Diving\nSnorkeling\nIsland Hopping\nLight and Sound Show\nGlass Bottom Boat Ride', 'packages/andaman-islands.jpg');

-- Insert sample itinerary for Goa package
INSERT INTO itinerary (package_id, day_number, title, description, accommodation, meals, activities) VALUES
(1, 1, 'Arrival in Goa', 'Arrive at Goa International Airport. Transfer to your beach resort. Evening free to relax on the beach.', '3-star Beach Resort', 'Dinner', 'Airport Transfer\nBeach Relaxation'),
(1, 2, 'North Goa Exploration', 'Visit famous North Goa beaches - Calangute, Baga, and Anjuna. Explore Portuguese churches and chapels.', '3-star Beach Resort', 'Breakfast, Dinner', 'Beach Hopping\nChurch Visit\nLocal Market'),
(1, 3, 'South Goa Discovery', 'Discover the peaceful beaches of South Goa - Colva and Palolem. Visit the Basilica of Bom Jesus.', '3-star Beach Resort', 'Breakfast, Dinner', 'Beach Visit\nUNESCO Heritage Site\nSpice Plantation'),
(1, 4, 'Adventure Day', 'Enjoy water sports activities. Optional: Dolphin spotting cruise. Evening visit to flea markets.', '3-star Beach Resort', 'Breakfast, Dinner', 'Water Sports\nDolphin Cruise\nShopping'),
(1, 5, 'Departure', 'Free time for last minute shopping. Transfer to airport for departure.', NULL, 'Breakfast', 'Shopping\nAirport Transfer');

-- Insert sample inclusions
INSERT INTO inclusions (package_id, inclusion_type, description) VALUES
(1, 'hotel', '4 nights accommodation in 3-star beach resort'),
(1, 'meal', 'Daily breakfast and dinner'),
(1, 'transport', 'AC vehicle for all transfers and sightseeing'),
(1, 'activity', 'Beach hopping tour'),
(1, 'guide', 'English speaking tour guide'),
(1, 'other', 'All applicable taxes');

-- Insert sample gallery images
INSERT INTO package_gallery (package_id, image_url, caption) VALUES
(1, 'packages/gallery/goa1.jpg', 'Calangute Beach'),
(1, 'packages/gallery/goa2.jpg', 'Portuguese Church'),
(1, 'packages/gallery/goa3.jpg', 'Sunset at Baga Beach'),
(2, 'packages/gallery/kashmir1.jpg', 'Dal Lake Shikara'),
(2, 'packages/gallery/kashmir2.jpg', 'Tulip Garden'),
(3, 'packages/gallery/kerala1.jpg', 'Houseboat Cruise');

-- Insert sample users (password for all: user123)
INSERT INTO users (username, email, password, full_name, phone, address) VALUES
('john_doe', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John Doe', '9876543210', '123 Main Street, Mumbai'),
('jane_smith', 'jane@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Jane Smith', '9876543211', '456 Park Avenue, Delhi'),
('robert_wilson', 'robert@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Robert Wilson', '9876543212', '789 MG Road, Bangalore');

-- Insert sample bookings
INSERT INTO bookings (user_id, package_id, booking_date, travel_date, num_travelers, total_amount, booking_status) VALUES
(1, 1, '2024-01-15', '2024-03-20', 2, 24000.00, 'confirmed'),
(2, 2, '2024-01-20', '2024-04-15', 3, 66000.00, 'completed'),
(3, 3, '2024-01-25', '2024-05-10', 2, 32000.00, 'pending');

-- Insert sample payments
INSERT INTO payments (booking_id, payment_method, transaction_id, amount, payment_status) VALUES
(1, 'credit_card', 'TXN123456', 24000.00, 'completed'),
(2, 'net_banking', 'TXN123457', 66000.00, 'completed');

-- Insert sample reviews
INSERT INTO reviews (user_id, package_id, booking_id, rating, review_text, status) VALUES
(1, 1, 1, 5, 'Amazing experience! The beaches were beautiful and the food was delicious. Highly recommended!', 'approved'),
(2, 2, 2, 4, 'Beautiful destination, but could improve on hotel quality. Overall good experience.', 'approved');

-- Insert sample contact messages
INSERT INTO contact_messages (name, email, phone, subject, message, status) VALUES
('Alice Johnson', 'alice@example.com', '9876543213', 'Booking Assistance', 'I need help with my booking #12345', 'replied'),
('Bob Williams', 'bob@example.com', '9876543214', 'General Inquiry', 'Do you offer group discounts for 10+ people?', 'read');

-- Create views for reporting
CREATE VIEW booking_summary AS
SELECT 
    b.booking_id,
    u.full_name,
    p.package_name,
    b.travel_date,
    b.num_travelers,
    b.total_amount,
    b.booking_status,
    py.payment_status
FROM bookings b
JOIN users u ON b.user_id = u.user_id
JOIN tour_packages p ON b.package_id = p.package_id
LEFT JOIN payments py ON b.booking_id = py.booking_id;

CREATE VIEW package_popularity AS
SELECT 
    p.package_id,
    p.package_name,
    d.name as destination,
    COUNT(b.booking_id) as total_bookings,
    SUM(b.total_amount) as total_revenue,
    AVG(r.rating) as average_rating
FROM tour_packages p
LEFT JOIN destinations d ON p.destination_id = d.destination_id
LEFT JOIN bookings b ON p.package_id = b.package_id
LEFT JOIN reviews r ON p.package_id = r.package_id
GROUP BY p.package_id;

-- Create stored procedures
DELIMITER //

CREATE PROCEDURE GetMonthlyRevenue(IN year INT)
BEGIN
    SELECT 
        MONTH(payment_date) as month,
        SUM(amount) as revenue,
        COUNT(payment_id) as transactions
    FROM payments
    WHERE YEAR(payment_date) = year AND payment_status = 'completed'
    GROUP BY MONTH(payment_date)
    ORDER BY month;
END//

CREATE PROCEDURE GetUserBookings(IN user_id INT)
BEGIN
    SELECT 
        b.booking_id,
        p.package_name,
        b.travel_date,
        b.booking_status,
        b.total_amount,
        py.payment_status
    FROM bookings b
    JOIN tour_packages p ON b.package_id = p.package_id
    LEFT JOIN payments py ON b.booking_id = py.booking_id
    WHERE b.user_id = user_id
    ORDER BY b.created_at DESC;
END//

CREATE PROCEDURE UpdatePackageAvailability(IN p_package_id INT)
BEGIN
    DECLARE v_bookings INT;
    DECLARE v_capacity INT;
    
    SELECT COUNT(*), max_capacity INTO v_bookings, v_capacity
    FROM bookings b
    JOIN tour_packages p ON b.package_id = p.package_id
    WHERE b.package_id = p_package_id AND b.booking_status IN ('confirmed', 'completed');
    
    UPDATE tour_packages 
    SET current_bookings = v_bookings,
        status = CASE 
            WHEN v_bookings >= v_capacity THEN 'sold_out'
            WHEN v_bookings >= v_capacity * 0.8 THEN 'active'
            ELSE 'active'
        END
    WHERE package_id = p_package_id;
END//

DELIMITER ;

-- Create triggers
DELIMITER //

CREATE TRIGGER after_booking_insert
AFTER INSERT ON bookings
FOR EACH ROW
BEGIN
    CALL UpdatePackageAvailability(NEW.package_id);
END//

CREATE TRIGGER after_booking_update
AFTER UPDATE ON bookings
FOR EACH ROW
BEGIN
    IF OLD.booking_status != NEW.booking_status THEN
        CALL UpdatePackageAvailability(NEW.package_id);
    END IF;
END//

CREATE TRIGGER before_user_insert
BEFORE INSERT ON users
FOR EACH ROW
BEGIN
    SET NEW.username = LOWER(NEW.username);
    SET NEW.email = LOWER(NEW.email);
END//

DELIMITER ;

-- Create indexes for performance
CREATE INDEX idx_user_bookings ON bookings(user_id, booking_status);
CREATE INDEX idx_package_bookings ON bookings(package_id, travel_date);
CREATE INDEX idx_payment_dates ON payments(payment_date, payment_status);
CREATE INDEX idx_review_package ON reviews(package_id, status);
CREATE INDEX idx_package_status ON tour_packages(status, price_per_person);

-- Create event for auto-cancelling pending bookings
DELIMITER //

CREATE EVENT auto_cancel_pending_bookings
ON SCHEDULE EVERY 1 DAY
DO
BEGIN
    UPDATE bookings 
    SET booking_status = 'cancelled'
    WHERE booking_status = 'pending' 
    AND created_at < DATE_SUB(NOW(), INTERVAL 24 HOUR);
END//

DELIMITER ;