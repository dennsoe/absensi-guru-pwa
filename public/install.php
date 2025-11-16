<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Installer - Sistem Absensi Guru v3.5</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .installer-container {
            max-width: 800px;
            margin: 0 auto;
        }

        .card {
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
        }

        .step {
            display: none;
        }

        .step.active {
            display: block;
        }

        .progress {
            height: 10px;
            margin-bottom: 30px;
        }

        .check-item {
            padding: 15px;
            border: 1px solid #dee2e6;
            border-radius: 10px;
            margin-bottom: 10px;
        }

        .check-success {
            background-color: #d4edda;
            border-color: #c3e6cb;
        }

        .check-error {
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
    </style>
</head>

<body>
    <div class="installer-container">
        <!-- Header -->
        <div class="text-center text-white mb-4">
            <h1 class="display-4 fw-bold">
                <i class="bi bi-gear-fill me-2"></i>
                Installer Sistem Absensi Guru
            </h1>
            <p class="lead">Versi 3.5 - Progressive Web App Edition</p>
        </div>

        <!-- Progress Bar -->
        <div class="progress">
            <div class="progress-bar" id="progressBar" role="progressbar" style="width: 0%"></div>
        </div>

        <!-- Step 1: Requirement Check -->
        <div class="card step active" id="step1">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="bi bi-check-circle me-2"></i>
                    Step 1: Pemeriksaan Sistem
                </h5>
            </div>
            <div class="card-body">
                <p class="text-muted">Memeriksa persyaratan sistem...</p>

                <div id="requirementChecks">
                    <div class="check-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-gear me-2"></i>PHP Version (≥ 7.4)</span>
                            <span id="check-php"></span>
                        </div>
                    </div>

                    <div class="check-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-database me-2"></i>PDO Extension</span>
                            <span id="check-pdo"></span>
                        </div>
                    </div>

                    <div class="check-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-database-fill me-2"></i>PDO MySQL Driver</span>
                            <span id="check-pdo-mysql"></span>
                        </div>
                    </div>

                    <div class="check-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-folder me-2"></i>Writable: /public/uploads/</span>
                            <span id="check-uploads"></span>
                        </div>
                    </div>

                    <div class="check-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-folder me-2"></i>Writable: /logs/</span>
                            <span id="check-logs"></span>
                        </div>
                    </div>

                    <div class="check-item">
                        <div class="d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-folder me-2"></i>Writable: /backup/</span>
                            <span id="check-backup"></span>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button class="btn btn-primary" onclick="nextStep(2)">
                        Lanjut ke Database Setup
                        <i class="bi bi-arrow-right ms-2"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Step 2: Database Setup -->
        <div class="card step" id="step2">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="bi bi-database me-2"></i>
                    Step 2: Konfigurasi Database
                </h5>
            </div>
            <div class="card-body">
                <form id="dbForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Database Host</label>
                            <input type="text" class="form-control" name="db_host" value="localhost" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Database Port</label>
                            <input type="text" class="form-control" name="db_port" value="3306" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Database Name</label>
                        <input type="text" class="form-control" name="db_name" value="absensi_guru" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Database Username</label>
                        <input type="text" class="form-control" name="db_user" value="root" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Database Password</label>
                        <input type="password" class="form-control" name="db_pass">
                        <small class="text-muted">Kosongkan jika tidak ada password</small>
                    </div>

                    <div class="alert alert-info">
                        <i class="bi bi-info-circle me-2"></i>
                        Pastikan database sudah dibuat terlebih dahulu!
                    </div>

                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-secondary" onclick="prevStep(1)">
                            <i class="bi bi-arrow-left me-2"></i>
                            Kembali
                        </button>
                        <button type="button" class="btn btn-primary" onclick="testDatabase()">
                            <i class="bi bi-plug me-2"></i>
                            Test Koneksi
                        </button>
                        <button type="button" class="btn btn-success" onclick="nextStep(3)">
                            Lanjut
                            <i class="bi bi-arrow-right ms-2"></i>
                        </button>
                    </div>
                </form>

                <div id="dbTestResult" class="mt-3"></div>
            </div>
        </div>

        <!-- Step 3: Import Database -->
        <div class="card step" id="step3">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="bi bi-box-arrow-in-down me-2"></i>
                    Step 3: Import Database
                </h5>
            </div>
            <div class="card-body">
                <p>Import file SQL ke database...</p>

                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    Proses ini akan membuat tabel dan data awal. Pastikan database kosong atau backup data lama Anda!
                </div>

                <div id="importProgress" style="display: none;">
                    <div class="spinner-border text-primary me-2" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                    <span>Mengimport database...</span>
                </div>

                <div id="importResult"></div>

                <div class="mt-4 d-flex gap-2">
                    <button class="btn btn-secondary" onclick="prevStep(2)">
                        <i class="bi bi-arrow-left me-2"></i>
                        Kembali
                    </button>
                    <button class="btn btn-primary" onclick="importDatabase()">
                        <i class="bi bi-download me-2"></i>
                        Import Database
                    </button>
                </div>
            </div>
        </div>

        <!-- Step 4: Admin Setup -->
        <div class="card step" id="step4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="bi bi-person-fill me-2"></i>
                    Step 4: Setup Admin
                </h5>
            </div>
            <div class="card-body">
                <form id="adminForm">
                    <div class="mb-3">
                        <label class="form-label">Username Admin</label>
                        <input type="text" class="form-control" name="admin_username" value="admin" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password Admin</label>
                        <input type="password" class="form-control" name="admin_password" required>
                        <small class="text-muted">Minimal 8 karakter</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Konfirmasi Password</label>
                        <input type="password" class="form-control" name="admin_password_confirm" required>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-secondary" onclick="prevStep(3)">
                            <i class="bi bi-arrow-left me-2"></i>
                            Kembali
                        </button>
                        <button type="button" class="btn btn-success" onclick="createAdmin()">
                            <i class="bi bi-check-circle me-2"></i>
                            Buat Admin & Selesai
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Step 5: Finish -->
        <div class="card step" id="step5">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    Instalasi Selesai!
                </h5>
            </div>
            <div class="card-body text-center py-5">
                <i class="bi bi-check-circle-fill text-success" style="font-size: 80px;"></i>
                <h3 class="mt-4">Instalasi Berhasil!</h3>
                <p class="text-muted">Sistem Absensi Guru siap digunakan</p>

                <div class="alert alert-warning mt-4">
                    <strong>Penting:</strong> Hapus file <code>install.php</code> dari server untuk keamanan!
                </div>

                <div class="mt-4">
                    <a href="<?= rtrim(str_replace('/public', '', dirname($_SERVER['SCRIPT_NAME'])), '/') ?>/login" class="btn btn-primary btn-lg">
                        <i class="bi bi-box-arrow-in-right me-2"></i>
                        Login ke Sistem
                    </a>
                </div>
                
                <div class="mt-3">
                    <small class="text-muted">
                        <strong>Catatan Keamanan:</strong> Jangan lupa hapus file-file installer:<br>
                        <code>install.php, install-check.php, install-test-db.php, install-import-db.php, install-create-admin.php</code>
                    </small>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Check system requirements on load
        window.onload = function () {
            checkRequirements();
        };

        function checkRequirements() {
            // This would be handled by PHP in real implementation
            fetch('install-check.php')
                .then(response => response.json())
                .then(data => {
                    updateCheck('php', data.php);
                    updateCheck('pdo', data.pdo);
                    updateCheck('pdo-mysql', data.pdo_mysql);
                    updateCheck('uploads', data.uploads);
                    updateCheck('logs', data.logs);
                    updateCheck('backup', data.backup);
                });
        }

        function updateCheck(id, passed) {
            const elem = document.getElementById('check-' + id);
            const parent = elem.closest('.check-item');

            if (passed) {
                elem.innerHTML = '<i class="bi bi-check-circle-fill text-success"></i>';
                parent.classList.add('check-success');
            } else {
                elem.innerHTML = '<i class="bi bi-x-circle-fill text-danger"></i>';
                parent.classList.add('check-error');
            }
        }

        function nextStep(step) {
            // Hide all steps
            document.querySelectorAll('.step').forEach(s => s.classList.remove('active'));

            // Show target step
            document.getElementById('step' + step).classList.add('active');

            // Update progress
            const progress = (step / 5) * 100;
            document.getElementById('progressBar').style.width = progress + '%';
        }

        function prevStep(step) {
            nextStep(step);
        }

        function testDatabase() {
            const formData = new FormData(document.getElementById('dbForm'));

            fetch('install-test-db.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    const resultDiv = document.getElementById('dbTestResult');

                    if (data.success) {
                        resultDiv.innerHTML =
                            '<div class="alert alert-success"><i class="bi bi-check-circle me-2"></i>' + data
                            .message + '</div>';
                    } else {
                        resultDiv.innerHTML =
                            '<div class="alert alert-danger"><i class="bi bi-x-circle me-2"></i>' + data.message +
                            '</div>';
                    }
                });
        }

        function importDatabase() {
            document.getElementById('importProgress').style.display = 'block';

            const formData = new FormData(document.getElementById('dbForm'));

            fetch('install-import-db.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    document.getElementById('importProgress').style.display = 'none';
                    const resultDiv = document.getElementById('importResult');

                    if (data.success) {
                        resultDiv.innerHTML =
                            '<div class="alert alert-success"><i class="bi bi-check-circle me-2"></i>' + data
                            .message + '</div>';
                        setTimeout(() => nextStep(4), 1500);
                    } else {
                        resultDiv.innerHTML =
                            '<div class="alert alert-danger"><i class="bi bi-x-circle me-2"></i>' + data.message +
                            '</div>';
                    }
                });
        }

        function createAdmin() {
            const formData = new FormData(document.getElementById('adminForm'));

            // Validate password
            if (formData.get('admin_password') !== formData.get('admin_password_confirm')) {
                alert('Password tidak cocok!');
                return;
            }

            if (formData.get('admin_password').length < 8) {
                alert('Password minimal 8 karakter!');
                return;
            }

            fetch('install-create-admin.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        nextStep(5);
                    } else {
                        alert('Error: ' + data.message);
                    }
                });
        }
    </script>
</body>

</html>