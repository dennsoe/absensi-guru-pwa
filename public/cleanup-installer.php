<?php
/**
 * Cleanup Installer Files
 * Jalankan file ini setelah instalasi selesai untuk menghapus file-file installer
 */

// Check if installed
if (!file_exists(__DIR__ . '/../.installed')) {
    die('Error: Instalasi belum selesai. Silakan jalankan install.php terlebih dahulu.');
}

$filesToDelete = [
    'install.php',
    'install-check.php',
    'install-test-db.php',
    'install-import-db.php',
    'install-create-admin.php',
    'cleanup-installer.php' // file ini sendiri
];

$deleted = [];
$failed = [];

foreach ($filesToDelete as $file) {
    $filepath = __DIR__ . '/' . $file;
    
    if (file_exists($filepath)) {
        if (@unlink($filepath)) {
            $deleted[] = $file;
        } else {
            $failed[] = $file;
        }
    }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cleanup Installer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .card {
            max-width: 600px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="card-header bg-<?= empty($failed) ? 'success' : 'warning' ?> text-white">
            <h5 class="mb-0">
                <i class="bi bi-<?= empty($failed) ? 'check-circle' : 'exclamation-triangle' ?>-fill me-2"></i>
                Cleanup Installer
            </h5>
        </div>
        <div class="card-body">
            <?php if (!empty($deleted)): ?>
            <div class="alert alert-success">
                <strong><i class="bi bi-check-circle me-2"></i>Berhasil Dihapus:</strong>
                <ul class="mb-0 mt-2">
                    <?php foreach ($deleted as $file): ?>
                    <li><code><?= htmlspecialchars($file) ?></code></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
            
            <?php if (!empty($failed)): ?>
            <div class="alert alert-warning">
                <strong><i class="bi bi-exclamation-triangle me-2"></i>Gagal Dihapus:</strong>
                <ul class="mb-0 mt-2">
                    <?php foreach ($failed as $file): ?>
                    <li><code><?= htmlspecialchars($file) ?></code></li>
                    <?php endforeach; ?>
                </ul>
                <hr>
                <small>Silakan hapus file-file ini secara manual via FTP/cPanel File Manager untuk keamanan.</small>
            </div>
            <?php endif; ?>
            
            <div class="text-center mt-4">
                <a href="../" class="btn btn-primary">
                    <i class="bi bi-house-door me-2"></i>
                    Kembali ke Aplikasi
                </a>
            </div>
        </div>
    </div>
</body>
</html>
