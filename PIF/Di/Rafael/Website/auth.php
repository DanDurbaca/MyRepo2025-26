<?php
require_once 'config.php';

class Auth {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    // Register new user
    public function register($username, $email, $password, $firstName, $lastName) {
        // Validate input
        if (empty($username) || empty($email) || empty($password)) {
            return ["success" => false, "message" => "All fields are required"];
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ["success" => false, "message" => "Invalid email format"];
        }
        
        // Check if username or email exists
        $stmt = $this->pdo->prepare("SELECT pk_username FROM user WHERE pk_username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            return ["success" => false, "message" => "Username or email already exists"];
        }
        
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert user
        $stmt = $this->pdo->prepare("
            INSERT INTO user (pk_username, email, password, firstName, lastName, role) 
            VALUES (?, ?, ?, ?, ?, 'User')
        ");
        
        try {
            $stmt->execute([$username, $email, $hashedPassword, $firstName, $lastName]);
            return ["success" => true, "message" => "Registration successful"];
        } catch(PDOException $e) {
            return ["success" => false, "message" => "Registration failed: " . $e->getMessage()];
        }
    }
    
    // Login user
    public function login($username, $password) {
        // Get user
        $stmt = $this->pdo->prepare("
            SELECT pk_username, password, email, firstName, lastName, role 
            FROM user 
            WHERE pk_username = ? OR email = ?
        ");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();
        
        if (!$user || !password_verify($password, $user['password'])) {
            return ["success" => false, "message" => "Invalid username/email or password"];
        }
        
        // Set session
        $_SESSION['username'] = $user['pk_username'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['firstName'] = $user['firstName'];
        $_SESSION['lastName'] = $user['lastName'];
        $_SESSION['role'] = $user['role'];
        
        return ["success" => true, "message" => "Login successful", "user" => $user];
    }
    
    // Logout
    public function logout() {
        session_destroy();
        return ["success" => true, "message" => "Logged out successfully"];
    }
    
    // Update user profile
    public function updateProfile($username, $email, $firstName, $lastName, $currentPassword, $newPassword = null) {
        // Verify current password
        $stmt = $this->pdo->prepare("SELECT password FROM user WHERE pk_username = ?");
        $stmt->execute([$_SESSION['username']]);
        $user = $stmt->fetch();
        
        if (!password_verify($currentPassword, $user['password'])) {
            return ["success" => false, "message" => "Current password is incorrect"];
        }
        
        // Build update query
        $updates = [];
        $params = [];
        
        if (!empty($newPassword)) {
            $updates[] = "password = ?";
            $params[] = password_hash($newPassword, PASSWORD_DEFAULT);
        }
        
        if ($email !== $_SESSION['email']) {
            $updates[] = "email = ?";
            $params[] = $email;
        }
        
        $updates[] = "firstName = ?";
        $params[] = $firstName;
        $updates[] = "lastName = ?";
        $params[] = $lastName;
        
        $params[] = $_SESSION['username']; // WHERE condition
        
        $stmt = $this->pdo->prepare("
            UPDATE user 
            SET " . implode(", ", $updates) . " 
            WHERE pk_username = ?
        ");
        
        try {
            $stmt->execute($params);
            
            // Update session
            $_SESSION['email'] = $email;
            $_SESSION['firstName'] = $firstName;
            $_SESSION['lastName'] = $lastName;
            
            return ["success" => true, "message" => "Profile updated successfully"];
        } catch(PDOException $e) {
            return ["success" => false, "message" => "Update failed: " . $e->getMessage()];
        }
    }
}
?>