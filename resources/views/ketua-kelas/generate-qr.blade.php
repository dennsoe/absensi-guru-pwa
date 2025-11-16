@extends('layouts.app')

@section('title', 'QR Code Kelas')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <!-- Header -->
                <div class="text-center mb-4">
                    <h2 class="fw-bold text-primary">QR Code Absensi Kelas</h2>
                    <p class="text-muted">Tunjukkan QR Code ini kepada Guru untuk diabsen</p>
                </div>

                <!-- Alert -->
                <div id="alertContainer"></div>

                <!-- QR Code Card -->
                <div class="card shadow-sm">
                    <div class="card-body text-center p-5">
                        <!-- Info Kelas -->
                        <div class="alert alert-primary mb-4">
                            <h5 class="fw-bold mb-2">
                                <i class="bi bi-people-fill"></i> Kelas: <span
                                    id="kelasInfo">{{ $kelas->nama_kelas ?? '-' }}</span>
                            </h5>
                            <p class="mb-0">Ketua Kelas: <strong>{{ $ketua_kelas_nama ?? auth()->user()->nama }}</strong>
                            </p>
                        </div>

                        <!-- QR Code Container -->
                        <div id="qrcode" class="mb-4 d-flex justify-content-center">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Generating QR Code...</span>
                            </div>
                        </div>

                        <!-- Timer -->
                        <div class="mb-3">
                            <h5 class="text-muted">QR Code berlaku selama:</h5>
                            <h1 class="fw-bold text-primary" id="timer">5:00</h1>
                            <p class="text-muted small">QR Code akan otomatis diperbarui setiap 5 menit</p>
                        </div>

                        <!-- Info -->
                        <div class="alert alert-light border">
                            <h6 class="fw-bold mb-2">
                                <i class="bi bi-info-circle"></i> Cara Menggunakan:
                            </h6>
                            <ol class="text-start mb-0 small">
                                <li>QR Code akan otomatis muncul di layar</li>
                                <li>Tunjukkan QR Code ini ke Guru yang akan absensi</li>
                                <li>Guru akan men-scan QR Code menggunakan HP mereka</li>
                                <li>QR Code akan otomatis diperbarui setiap 5 menit</li>
                                <li>Pastikan layar HP cukup terang untuk dipindai</li>
                            </ol>
                        </div>

                        <!-- Refresh Button -->
                        <button type="button" class="btn btn-primary" id="refreshBtn">
                            <i class="bi bi-arrow-clockwise"></i> Perbarui QR Code
                        </button>
                    </div>
                </div>

                <!-- Statistik Scan Hari Ini -->
                <div class="card shadow-sm mt-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-graph-up"></i> Statistik Hari Ini</h5>
                    </div>
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-4">
                                <div class="border rounded p-3">
                                    <h3 class="text-primary mb-1" id="totalScan">0</h3>
                                    <small class="text-muted">Total Scan</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="border rounded p-3">
                                    <h3 class="text-success mb-1" id="guruHadir">0</h3>
                                    <small class="text-muted">Guru Hadir</small>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="border rounded p-3">
                                    <h3 class="text-warning mb-1" id="guruTerlambat">0</h3>
                                    <small class="text-muted">Terlambat</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Riwayat Scan Hari Ini -->
                <div class="card shadow-sm mt-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-clock-history"></i> Riwayat Scan Hari Ini</h5>
                    </div>
                    <div class="card-body">
                        <div id="riwayatScan">
                            <div class="text-center text-muted py-3">
                                <span class="spinner-border spinner-border-sm" role="status"></span>
                                <span class="ms-2">Memuat riwayat...</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
@endpush

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/qrcodejs@1.0.0/qrcode.min.js"></script>
    <script>
        let qrCodeInstance = null;
        let timerInterval = null;
        let countdown = 300; // 5 minutes in seconds

        // QR Data Structure
        const kelasId = {{ $kelas_id ?? 'null' }};
        const ketuaKelasNama = "{{ $ketua_kelas_nama ?? auth()->user()->nama }}";

        // Generate QR Code
        function generateQRCode() {
            try {
                // Generate QR Data untuk scan oleh guru
                const qrData = {
                    kelas_id: kelasId,
                    ketua_kelas: ketuaKelasNama,
                    timestamp: Date.now(),
                    expires: Date.now() + (5 * 60 * 1000) // 5 minutes
                };

                const qrString = btoa(JSON.stringify(qrData));

                // Clear previous QR Code
                document.getElementById('qrcode').innerHTML = '';

                // Generate new QR Code
                qrCodeInstance = new QRCode(document.getElementById('qrcode'), {
                    text: qrString,
                    width: 300,
                    height: 300,
                    colorDark: '#2563eb',
                    colorLight: '#ffffff',
                    correctLevel: QRCode.CorrectLevel.H
                });

                // Start Timer
                countdown = 300;
                startTimer();

            } catch (error) {
                console.error('Error generating QR Code:', error);
                showAlert('Gagal menghasilkan QR Code: ' + error.message, 'danger');
            }
        }

        // Start Timer
        function startTimer() {
            if (timerInterval) {
                clearInterval(timerInterval);
            }

            timerInterval = setInterval(() => {
                countdown--;

                const minutes = Math.floor(countdown / 60);
                const seconds = countdown % 60;
                document.getElementById('timer').textContent =
                    `${minutes}:${seconds.toString().padStart(2, '0')}`;

                if (countdown <= 0) {
                    clearInterval(timerInterval);
                    generateQRCode(); // Auto refresh
                }
            }, 1000);
        }

        // Show Alert
        function showAlert(message, type = 'info') {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
            document.getElementById('alertContainer').appendChild(alertDiv);

            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }

        // Load Statistik
        async function loadStatistik() {
            try {
                const response = await fetch('{{ route('ketua-kelas.statistik-scan') }}');
                const data = await response.json();

                document.getElementById('totalScan').textContent = data.total || 0;
                document.getElementById('guruHadir').textContent = data.hadir || 0;
                document.getElementById('guruTerlambat').textContent = data.terlambat || 0;
            } catch (error) {
                console.error('Error loading statistik:', error);
            }
        }

        // Load Riwayat Scan
        async function loadRiwayat() {
            try {
                const response = await fetch('{{ route('ketua-kelas.riwayat-scan') }}');
                const data = await response.json();

                const container = document.getElementById('riwayatScan');

                if (data.length === 0) {
                    container.innerHTML =
                    '<p class="text-center text-muted py-3">Belum ada guru yang scan hari ini</p>';
                    return;
                }

                let html = '<div class="list-group">';
                data.forEach(item => {
                    const statusClass = item.status_kehadiran === 'hadir' ? 'success' :
                        item.status_kehadiran === 'terlambat' ? 'warning' : 'danger';
                    const statusText = item.status_kehadiran === 'hadir' ? 'Hadir' :
                        item.status_kehadiran === 'terlambat' ? 'Terlambat' : 'Alfa';

                    html += `
                <div class="list-group-item">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">${item.guru.nama}</h6>
                            <small class="text-muted">
                                <i class="bi bi-clock"></i> ${item.jam_absen}
                                <span class="ms-2">${item.jadwal.mata_pelajaran}</span>
                            </small>
                        </div>
                        <span class="badge bg-${statusClass}">${statusText}</span>
                    </div>
                </div>
            `;
                });
                html += '</div>';

                container.innerHTML = html;
            } catch (error) {
                console.error('Error loading riwayat:', error);
                document.getElementById('riwayatScan').innerHTML =
                    '<p class="text-center text-danger py-3">Gagal memuat riwayat</p>';
            }
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            generateQRCode();
            loadStatistik();
            loadRiwayat();

            // Auto refresh riwayat every 30 seconds
            setInterval(() => {
                loadStatistik();
                loadRiwayat();
            }, 30000);

            // Refresh Button
            document.getElementById('refreshBtn').addEventListener('click', function() {
                generateQRCode();
                loadStatistik();
                loadRiwayat();
            });
        });

        // Cleanup on page unload
        window.addEventListener('beforeunload', function() {
            if (timerInterval) {
                clearInterval(timerInterval);
            }
        });
    </script>
@endpush
