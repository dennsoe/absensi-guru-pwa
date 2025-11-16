<?php
/**
 * Installer - System Requirements Check
 */

header('Content-Type: application/json');

$checks = [
    'php' => version_compare(PHP_VERSION, '7.4.0', '>='),
    'pdo' => extension_loaded('pdo'),
    'pdo_mysql' => extension_loaded('pdo_mysql'),
    'gd' => extension_loaded('gd'),
    'openssl' => extension_loaded('openssl'),
    'uploads' => is_writable(__DIR__ . '/uploads') || @mkdir(__DIR__ . '/uploads', 0755, true),
    'logs' => is_writable(__DIR__ . '/../logs') || @mkdir(__DIR__ . '/../logs', 0755, true),
    'backup' => is_writable(__DIR__ . '/../backup') || @mkdir(__DIR__ . '/../backup', 0755, true),
];

// Try to create directories if not exist
if (!is_dir(__DIR__ . '/uploads')) {
    @mkdir(__DIR__ . '/uploads', 0755, true);
    $checks['uploads'] = is_writable(__DIR__ . '/uploads');
}

if (!is_dir(__DIR__ . '/../logs')) {
    @mkdir(__DIR__ . '/../logs', 0755, true);
    $checks['logs'] = is_writable(__DIR__ . '/../logs');
}

if (!is_dir(__DIR__ . '/../backup')) {
    @mkdir(__DIR__ . '/../backup', 0755, true);
    $checks['backup'] = is_writable(__DIR__ . '/../backup');
}

// Add PHP version info
$checks['php_version'] = PHP_VERSION;
$checks['all_passed'] = !in_array(false, $checks, true);

echo json_encode($checks);
