<?php

/**
 * Login Controller
 */
class LoginController extends Controller {
    
    /**
     * Show Login Page
     */
    public function index() {
        // Redirect if already logged in
        if (isset($_SESSION['user_id'])) {
            $this->redirect('/dashboard');
        }
        
        $this->view('auth/login', ['no_layout' => true]);
    }
    
    /**
     * Process Login
     */
    public function authenticate() {
        if (!$this->isPost()) {
            $this->redirect('/login');
        }
        
        $username = $this->post('username');
        $password = $this->post('password');
        $remember = $this->post('remember');
        
        // Validate
        if (empty($username) || empty($password)) {
            $_SESSION['error'] = 'Username dan password harus diisi';
            $this->redirect('/login');
        }
        
        // Verify login
        $userModel = $this->model('User');
        $user = $userModel->verifyLogin($username, $password);
        
        if (!$user) {
            $_SESSION['error'] = 'Username atau password salah';
            $this->redirect('/login');
        }
        
        // Set session
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'];
        
        // Get guru data if role is guru
        if ($user['role'] === 'guru') {
            $guruModel = $this->model('Guru');
            $guru = $guruModel->findByUserId($user['user_id']);
            
            if ($guru) {
                $_SESSION['guru_id'] = $guru['guru_id'];
                $_SESSION['nama'] = $guru['nama'];
            }
        }
        
        // Remember me
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            setcookie('remember_token', $token, time() + (86400 * 30), '/'); // 30 days
            
            // Save token to database (implement if needed)
        }
        
        // Log activity
        $this->logActivity($user['user_id'], 'login', 'users', $user['user_id']);
        
        // Redirect to dashboard
        $_SESSION['success'] = 'Login berhasil! Selamat datang, ' . $user['username'];
        $this->redirect('/dashboard');
    }
    
    /**
     * Logout
     */
    public function logout() {
        // Log activity before destroying session
        if (isset($_SESSION['user_id'])) {
            $this->logActivity($_SESSION['user_id'], 'logout', 'users', $_SESSION['user_id']);
        }
        
        // Destroy session
        session_destroy();
        
        // Clear remember cookie
        if (isset($_COOKIE['remember_token'])) {
            setcookie('remember_token', '', time() - 3600, '/');
        }
        
        // Redirect to login
        $this->redirect('/login');
    }
    
    /**
     * Log Activity
     */
    private function logActivity($userId, $aksi, $tabel, $recordId) {
        try {
            $db = Database::getInstance();
            $db->insert('log_aktivitas', [
                'user_id' => $userId,
                'aksi' => $aksi,
                'tabel' => $tabel,
                'record_id' => $recordId,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
            ]);
        } catch (Exception $e) {
            // Silent fail for logging
            error_log("Failed to log activity: " . $e->getMessage());
        }
    }
}