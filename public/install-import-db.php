<?php
/**
 * Installer - Import Database
 */

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

session_start();

if (!isset($_SESSION['db_config'])) {
    echo json_encode(['success' => false, 'message' => 'Session expired. Silakan test koneksi database lagi.']);
    exit;
}

$config = $_SESSION['db_config'];

try {
    $dsn = "mysql:host={$config['host']};port={$config['port']};dbname={$config['name']};charset=utf8mb4";
    $pdo = new PDO($dsn, $config['user'], $config['pass'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    // Read SQL files
    $sqlPart1 = file_get_contents(__DIR__ . '/../database/absensi_guru_part1.sql');
    $sqlPart2 = file_get_contents(__DIR__ . '/../database/absensi_guru_part2.sql');
    
    if (!$sqlPart1 || !$sqlPart2) {
        throw new Exception('File SQL tidak ditemukan');
    }
    
    // Begin transaction
    $pdo->beginTransaction();
    
    // Execute part 1
    $pdo->exec($sqlPart1);
    
    // Execute part 2
    $pdo->exec($sqlPart2);
    
    // Commit
    $pdo->commit();
    
    // Save config to file
    $configContent = "<?php
/**
 * Konfigurasi Database - Fleksibel untuk berbagai environment
 */

// Database Configuration dengan environment detection
if (!defined('ENVIRONMENT')) {
    define('ENVIRONMENT', 'development');
}

if (ENVIRONMENT === 'development') {
    // Development (Local)
    define('DB_HOST', '{$config['host']}');
    define('DB_NAME', '{$config['name']}');
    define('DB_USER', '{$config['user']}');
    define('DB_PASS', '{$config['pass']}');
    define('DB_CHARSET', 'utf8mb4');
} else {
    // Production - baca dari environment variables atau .env file
    define('DB_HOST', getenv('DB_HOST') ?: '{$config['host']}');
    define('DB_NAME', getenv('DB_NAME') ?: '{$config['name']}');
    define('DB_USER', getenv('DB_USER') ?: '{$config['user']}');
    define('DB_PASS', getenv('DB_PASS') ?: '{$config['pass']}');
    define('DB_CHARSET', 'utf8mb4');
}

// PDO Options
define('DB_OPTIONS', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
    PDO::MYSQL_ATTR_INIT_COMMAND => \"SET NAMES \" . DB_CHARSET
]);

/**
 * Get Database Connection
 */
function getDB() {
    static \$pdo = null;
    
    if (\$pdo === null) {
        try {
            \$dsn = \"mysql:host=\" . DB_HOST . \";dbname=\" . DB_NAME . \";charset=\" . DB_CHARSET;
            \$pdo = new PDO(\$dsn, DB_USER, DB_PASS, DB_OPTIONS);
        } catch (PDOException \$e) {
            if (DEBUG_MODE) {
                die(\"Koneksi database gagal: \" . \$e->getMessage());
            } else {
                error_log(\"Database Error: \" . \$e->getMessage());
                die(\"Terjadi kesalahan sistem. Silakan hubungi administrator.\");
            }
        }
    }
    
    return \$pdo;
}
";
    
    file_put_contents(__DIR__ . '/../config/database.php', $configContent);
    
    echo json_encode([
        'success' => true,
        'message' => 'Database berhasil diimport! 17 tabel dibuat.'
    ]);
    
} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    echo json_encode([
        'success' => false,
        'message' => 'Import gagal: ' . $e->getMessage()
    ]);
}
