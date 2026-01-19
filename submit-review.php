<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

$auth->checkUserAuth();
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = $db->escapeString($_POST['booking_id']);
    $package_id = $db->escapeString($_POST['package_id']);
    $rating = $db->escapeString($_POST['rating']);
    $review_text = $db->escapeString($_POST['review_text']);
    
    // Check if user has completed this booking
    $check_sql = "SELECT booking_id FROM bookings 
                  WHERE booking_id = '$booking_id' 
                  AND user_id = '$user_id'
                  AND booking_status = 'completed'";
    $check_result = $db->executeQuery($check_sql);
    
    if ($check_result->num_rows == 1) {
        $sql = "INSERT INTO reviews (user_id, package_id, booking_id, rating, review_text) 
                VALUES ('$user_id', '$package_id', '$booking_id', '$rating', '$review_text')";
        
        if ($db->executeQuery($sql)) {
            $_SESSION['success'] = 'Thank you for your review! It will be published after moderation.';
            header('Location: reviews.php');
            exit();
        }
    }
    
    $_SESSION['error'] = 'Unable to submit review. Please try again.';
    header('Location: reviews.php');
    exit();
}

header('Location: reviews.php');
exit();
?>