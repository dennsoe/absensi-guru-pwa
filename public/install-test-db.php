<?php
/**
 * Installer - Test Database Connection
 */

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$host = $_POST['db_host'] ?? 'localhost';
$port = $_POST['db_port'] ?? '3306';
$name = $_POST['db_name'] ?? '';
$user = $_POST['db_user'] ?? '';
$pass = $_POST['db_pass'] ?? '';

if (empty($name) || empty($user)) {
    echo json_encode(['success' => false, 'message' => 'Database name dan username harus diisi']);
    exit;
}

try {
    $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    // Test query
    $stmt = $pdo->query('SELECT VERSION() as version');
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
    // Save to session for next step
    session_start();
    $_SESSION['db_config'] = [
        'host' => $host,
        'port' => $port,
        'name' => $name,
        'user' => $user,
        'pass' => $pass
    ];
    
    echo json_encode([
        'success' => true,
        'message' => 'Koneksi database berhasil! MySQL/MariaDB versi: ' . $result['version']
    ]);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Koneksi gagal: ' . $e->getMessage()
    ]);
}
