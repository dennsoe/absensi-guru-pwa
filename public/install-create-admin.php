<?php
/**
 * Installer - Create Admin User
 */

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

session_start();

if (!isset($_SESSION['db_config'])) {
    echo json_encode(['success' => false, 'message' => 'Session expired. Silakan ulangi instalasi.']);
    exit;
}

$username = $_POST['admin_username'] ?? '';
$password = $_POST['admin_password'] ?? '';

if (empty($username) || empty($password)) {
    echo json_encode(['success' => false, 'message' => 'Username dan password harus diisi']);
    exit;
}

if (strlen($password) < 8) {
    echo json_encode(['success' => false, 'message' => 'Password minimal 8 karakter']);
    exit;
}

$config = $_SESSION['db_config'];

try {
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['name']};charset=utf8mb4";
    $pdo = new PDO($dsn, $config['user'], $config['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    // Check if admin already exists
    $stmt = $pdo->prepare("SELECT user_id FROM users WHERE username = :username");
    $stmt->execute(['username' => $username]);
    
    if ($stmt->fetch()) {
        // Update existing admin
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE username = :username");
        $stmt->execute([
            'password' => $hashedPassword,
            'username' => $username
        ]);
        
        $message = 'Admin berhasil diperbarui!';
    } else {
        // Create new admin
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role, status) VALUES (:username, :password, 'admin', 'aktif')");
        $stmt->execute([
            'username' => $username,
            'password' => $hashedPassword
        ]);
        
        $message = 'Admin berhasil dibuat!';
    }
    
    // Create lock file to prevent re-installation
    file_put_contents(__DIR__ . '/../.installed', date('Y-m-d H:i:s'));
    
    // Clear session
    unset($_SESSION['db_config']);
    
    echo json_encode([
        'success' => true,
        'message' => $message
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Gagal membuat admin: ' . $e->getMessage()
    ]);
}
