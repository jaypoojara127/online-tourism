<?php
require_once 'config.php';
require_once 'functions.php';

class Auth {
    private $db;
    
    public function __construct() {
        global $db;
        $this->db = $db;
    }
    
    // Check if user is logged in
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }
    
    // Check if admin is logged in
    public function isAdminLoggedIn() {
        return isset($_SESSION['admin_id']);
    }
    
    // Admin login
    public function adminLogin($username, $password) {
        $username = $this->db->escapeString($username);
        
        $sql = "SELECT admin_id, username, password, role FROM admin WHERE username = '$username'";
        $result = $this->db->executeQuery($sql);
        
        if ($result->num_rows == 1) {
            $admin = $result->fetch_assoc();
            
            if (password_verify($password, $admin['password'])) {
                $_SESSION['admin_id'] = $admin['admin_id'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_role'] = $admin['role'];
                
                return array('success' => true, 'message' => 'Login successful');
            }
        }
        
        return array('success' => false, 'message' => 'Invalid credentials');
    }
    
    // Logout
    public function logout() {
        session_destroy();
        header('Location: ../index.php');
        exit();
    }
    
    // Admin logout
    public function adminLogout() {
        session_destroy();
        header('Location: ' . SITE_URL . 'index.php');
        exit();
    }
    
    // Check authorization
    public function checkUserAuth() {
        if (!$this->isLoggedIn()) {
            header('Location: ' . SITE_URL . 'pages/login.php');
            exit();
        }
    }
    
    public function checkAdminAuth() {
        if (!$this->isAdminLoggedIn()) {
            header('Location: ' . SITE_URL . 'admin/index.php');
            exit();
        }
    }
    
    // Get current user info
    public function getCurrentUser() {
        if ($this->isLoggedIn()) {
            $user_id = $_SESSION['user_id'];
            $sql = "SELECT * FROM users WHERE user_id = '$user_id'";
            $result = $this->db->executeQuery($sql);
            return $result->fetch_assoc();
        }
        return null;
    }
}

// Create global auth instance
$auth = new Auth();
?>