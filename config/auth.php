<?php


session_start();

class Auth {
    private $conn;

    public function __construct($database) {
        $this->conn = $database;
    }

    
    public function register($email, $password, $full_name, $phone, $role = 'patient', $dob = null) {
     
        if (empty($email) || empty($password) || empty($full_name)) {
            return ['status' => false, 'message' => 'Missing required fields'];
        }

      
        $query = "SELECT id FROM users WHERE email = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            return ['status' => false, 'message' => 'Email already exists'];
        }

        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $created_at = date('Y-m-d H:i:s');

        
        $query = "INSERT INTO users (email, password, full_name, phone, role, dob, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('sssssss', $email, $hashed_password, $full_name, $phone, $role, $dob, $created_at);

        if ($stmt->execute()) {
            return ['status' => true, 'message' => 'User registered successfully', 'user_id' => $stmt->insert_id];
        }

        return ['status' => false, 'message' => 'Registration failed'];
    }

    /*
      Login user
     */
    public function login($email, $password) {
        if (empty($email) || empty($password)) {
            return ['status' => false, 'message' => 'Email and password required'];
        }

        $query = "SELECT id, email, password, full_name, role, status FROM users WHERE email = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            return ['status' => false, 'message' => 'Invalid credentials'];
        }

        $user = $result->fetch_assoc();

        if ($user['status'] !== 'active') {
            return ['status' => false, 'message' => 'Account is inactive'];
        }

        if (!password_verify($password, $user['password'])) {
            return ['status' => false, 'message' => 'Invalid credentials'];
        }

        // Session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['login_time'] = time();

        return ['status' => true, 'message' => 'Login successful', 'user' => $user];
    }

    /* 
     Check if user is logged in
     */
    public function isLoggedIn() {
        return isset($_SESSION['user_id']);
    }

    /*
     Get current user
     */
    public function getCurrentUser() {
        if (!$this->isLoggedIn()) {
            return null;
        }
        return $_SESSION;
    }

    /*
     Check user role
     */
    public function hasRole($role) {
        return isset($_SESSION['role']) && $_SESSION['role'] === $role;
    }

    /*
      Logout user
     */
    public function logout() {
        session_destroy();
        return ['status' => true, 'message' => 'Logged out successfully'];
    }

    /*
      Verify session timeout
     */
    public function checkSessionTimeout() {
        if (!isset($_SESSION['login_time'])) {
            return false;
        }

        if (time() - $_SESSION['login_time'] > SESSION_TIMEOUT) {
            $this->logout();
            return false;
        }

        $_SESSION['login_time'] = time();
        return true;
    }
}
?>