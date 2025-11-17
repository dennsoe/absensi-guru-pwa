@extends('layouts.app')

@section('title', 'Scan QR Code Kelas')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <!-- Header -->
                <div class="text-center mb-4">
                    <h2 class="fw-bold text-primary">Scan QR Code Kelas</h2>
                    <p class="text-muted">Scan QR Code yang ditampilkan Ketua Kelas untuk absensi</p>
                </div>

                <!-- Alert -->
                <div id="alertContainer"></div>

                <div class="row">
                    <!-- Scanner Section -->
                    <div class="col-md-8">
                        <div class="card shadow-sm">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="bi bi-camera"></i> Scanner QR Code</h5>
                            </div>
                            <div class="card-body">
                                <!-- Scanner Container -->
                                <div id="reader" class="mb-3"></div>

                                <!-- Scanner Controls -->
                                <div class="d-flex justify-content-center gap-2">
                                    <button type="button" class="btn btn-success" id="startBtn">
                                        <i class="bi bi-play-fill"></i> Mulai Scan
                                    </button>
                                    <button type="button" class="btn btn-danger d-none" id="stopBtn">
                                        <i class="bi bi-stop-fill"></i> Stop Scan
                                    </button>
                                </div>

                                <!-- Instructions -->
                                <div class="alert alert-info mt-3">
                                    <h6 class="fw-bold mb-2">
                                        <i class="bi bi-info-circle"></i> Petunjuk:
                                    </h6>
                                    <ul class="mb-0 small">
                                        <li>Klik tombol "Mulai Scan" untuk mengaktifkan kamera</li>
                                        <li>Arahkan kamera ke QR Code yang ditampilkan Ketua Kelas</li>
                                        <li>QR Code akan otomatis terdeteksi dan diproses</li>
                                        <li>Pastikan GPS Anda aktif dan Anda berada di area sekolah</li>
                                        <li>Pastikan pencahayaan cukup terang</li>
                                        <li>Jaga jarak 20-30 cm dari QR Code</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Info Section -->
                    <div class="col-md-4">
                        <!-- GPS Status -->
                        <div class="card shadow-sm mb-3">
                            <div class="card-header bg-white">
                                <h6 class="mb-0"><i class="bi bi-geo-alt-fill"></i> Status GPS</h6>
                            </div>
                            <div class="card-body">
                                <div id="gpsStatus">
                                    <div class="text-center py-2">
                                        <span class="spinner-border spinner-border-sm text-primary" role="status"></span>
                                        <p class="text-muted mb-0 mt-2 small">Mendeteksi lokasi...</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Camera Status -->
                        <div class="card shadow-sm mb-3">
                            <div class="card-header bg-white">
                                <h6 class="mb-0"><i class="bi bi-camera-video"></i> Status Kamera</h6>
                            </div>
                            <div class="card-body">
                                <div id="cameraStatus" class="text-center py-2">
                                    <i class="bi bi-camera-video-off text-muted" style="font-size: 2rem;"></i>
                                    <p class="text-muted mb-0 mt-2">Kamera belum aktif</p>
                                </div>
                            </div>
                        </div>

                        <!-- Scan Result -->
                        <div class="card shadow-sm">
                            <div class="card-header bg-white">
                                <h6 class="mb-0"><i class="bi bi-check2-circle"></i> Hasil Scan</h6>
                            </div>
                            <div class="card-body">
                                <div id="scanResult" class="text-center py-3">
                                    <i class="bi bi-qr-code text-muted" style="font-size: 3rem;"></i>
                                    <p class="text-muted mb-0 mt-2">Belum ada hasil scan</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Riwayat Absensi Hari Ini -->
                <div class="card shadow-sm mt-4">
                    <div class="card-header bg-white">
                        <h5 class="mb-0"><i class="bi bi-clock-history"></i> Riwayat Absensi Hari Ini</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>Waktu</th>
                                        <th>Kelas</th>
                                        <th>Mata Pelajaran</th>
                                        <th>Status</th>
                                        <th>Keterangan</th>
                                    </tr>
                                </thead>
                                <tbody id="riwayatTable">
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">
                                            <span class="spinner-border spinner-border-sm" role="status"></span>
                                            <span class="ms-2">Memuat riwayat...</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <style>
        #reader {
            border: 2px dashed #dee2e6;
            border-radius: 0.5rem;
            min-height: 400px;
        }

        #reader video {
            border-radius: 0.5rem;
        }
    </style>
@endpush

@push('scripts')
    <script src="{{ asset('assets/vendor/html5-qrcode.min.js') }}"></script>
    <script>
        let html5QrcodeScanner = null;
        let isScanning = false;
        let userLocation = null;

        // Configuration
        const SEKOLAH_LAT = -7.797068; // Ganti dengan koordinat sekolah
        const SEKOLAH_LNG = 110.370529; // Ganti dengan koordinat sekolah
        const RADIUS_METER = 200;

        // Get User Location
        function getUserLocation() {
            return new Promise((resolve, reject) => {
                if (!navigator.geolocation) {
                    reject(new Error('GPS tidak didukung oleh browser Anda'));
                    return;
                }

                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        resolve({
                            latitude: position.coords.latitude,
                            longitude: position.coords.longitude
                        });
                    },
                    (error) => {
                        reject(error);
                    }, {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 0
                    }
                );
            });
        }

        // Calculate Distance (Haversine Formula)
        function calculateDistance(lat1, lon1, lat2, lon2) {
            const R = 6371e3; // Earth radius in meters
            const φ1 = lat1 * Math.PI / 180;
            const φ2 = lat2 * Math.PI / 180;
            const Δφ = (lat2 - lat1) * Math.PI / 180;
            const Δλ = (lon2 - lon1) * Math.PI / 180;

            const a = Math.sin(Δφ / 2) * Math.sin(Δφ / 2) +
                Math.cos(φ1) * Math.cos(φ2) *
                Math.sin(Δλ / 2) * Math.sin(Δλ / 2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

            return R * c; // Distance in meters
        }

        // Initialize GPS
        async function initGPS() {
            try {
                userLocation = await getUserLocation();

                const distance = calculateDistance(
                    userLocation.latitude,
                    userLocation.longitude,
                    SEKOLAH_LAT,
                    SEKOLAH_LNG
                );

                const statusDiv = document.getElementById('gpsStatus');

                if (distance > RADIUS_METER) {
                    statusDiv.innerHTML = `
                <div class="text-danger">
                    <i class="bi bi-x-circle-fill" style="font-size: 2rem;"></i>
                    <p class="mb-1 mt-2"><strong>Lokasi Terlalu Jauh</strong></p>
                    <p class="small mb-0">Jarak: ${Math.round(distance)}m dari sekolah</p>
                    <p class="small mb-0">Maksimal: ${RADIUS_METER}m</p>
                </div>
            `;
                    showAlert('Anda berada terlalu jauh dari sekolah. Harap mendekat ke area sekolah.', 'danger');
                    document.getElementById('startBtn').disabled = true;
                } else {
                    statusDiv.innerHTML = `
                <div class="text-success">
                    <i class="bi bi-check-circle-fill" style="font-size: 2rem;"></i>
                    <p class="mb-1 mt-2"><strong>Lokasi Valid</strong></p>
                    <div class="small text-start">
                        <p class="mb-0"><strong>Latitude:</strong> ${userLocation.latitude.toFixed(6)}</p>
                        <p class="mb-0"><strong>Longitude:</strong> ${userLocation.longitude.toFixed(6)}</p>
                        <p class="mb-0 text-success"><strong>Jarak:</strong> ${Math.round(distance)}m ✓</p>
                    </div>
                </div>
            `;
                }
            } catch (error) {
                console.error('GPS Error:', error);
                const statusDiv = document.getElementById('gpsStatus');
                statusDiv.innerHTML = `
            <div class="text-danger">
                <i class="bi bi-x-circle-fill" style="font-size: 2rem;"></i>
                <p class="mb-1 mt-2"><strong>GPS Tidak Aktif</strong></p>
                <p class="small mb-0">${error.message}</p>
            </div>
        `;
                showAlert('Gagal mendapatkan lokasi GPS. Pastikan GPS aktif dan izin lokasi diberikan.', 'danger');
                document.getElementById('startBtn').disabled = true;
            }
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

            // Auto dismiss after 5 seconds
            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }

        // Update Camera Status
        function updateCameraStatus(active) {
            const statusDiv = document.getElementById('cameraStatus');
            if (active) {
                statusDiv.innerHTML = `
            <i class="bi bi-camera-video-fill text-success" style="font-size: 2rem;"></i>
            <p class="text-success mb-0 mt-2"><strong>Kamera Aktif</strong></p>
            <small class="text-muted">Siap memindai QR Code</small>
        `;
            } else {
                statusDiv.innerHTML = `
            <i class="bi bi-camera-video-off text-muted" style="font-size: 2rem;"></i>
            <p class="text-muted mb-0 mt-2">Kamera belum aktif</p>
        `;
            }
        }

        // Update Scan Result Display
        function updateScanResult(success, data = null) {
            const resultDiv = document.getElementById('scanResult');

            if (success && data) {
                resultDiv.innerHTML = `
            <div class="text-success">
                <i class="bi bi-check-circle-fill" style="font-size: 3rem;"></i>
                <h5 class="mt-2 mb-1">Absensi Berhasil!</h5>
                <p class="mb-1"><strong>Kelas ${data.kelas}</strong></p>
                <small class="text-muted">${data.mata_pelajaran}</small>
                <p class="mt-2 mb-0">
                    <span class="badge bg-${data.status === 'hadir' ? 'success' : 'warning'}">${data.status_text}</span>
                </p>
            </div>
        `;
            } else {
                resultDiv.innerHTML = `
            <div class="text-danger">
                <i class="bi bi-x-circle-fill" style="font-size: 3rem;"></i>
                <h5 class="mt-2 mb-1">Scan Gagal!</h5>
                <p class="text-muted mb-0">${data || 'QR Code tidak valid'}</p>
            </div>
        `;
            }

            // Reset result after 3 seconds
            setTimeout(() => {
                resultDiv.innerHTML = `
            <i class="bi bi-qr-code text-muted" style="font-size: 3rem;"></i>
            <p class="text-muted mb-0 mt-2">Belum ada hasil scan</p>
        `;
            }, 3000);
        }

        // Process QR Code
        async function processQRCode(decodedText) {
            try {
                // Validate GPS first
                if (!userLocation) {
                    showAlert('GPS belum aktif. Mohon tunggu...', 'warning');
                    return;
                }

                // Parse QR Data (dari ketua kelas)
                const qrData = JSON.parse(atob(decodedText));

                // Validate structure
                if (!qrData.kelas_id || !qrData.timestamp) {
                    showAlert('Format QR Code tidak valid.', 'danger');
                    updateScanResult(false, 'Format QR tidak valid');
                    return;
                }

                // Validate expiry
                if (Date.now() > qrData.expires) {
                    showAlert('QR Code sudah kadaluarsa. Minta ketua kelas untuk refresh QR Code.', 'warning');
                    updateScanResult(false, 'QR Code kadaluarsa');
                    return;
                }

                // Send to server (guru yang scan, data dari QR kelas)
                const response = await fetch('{{ route('guru.absensi.proses-qr') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        qr_data: decodedText,
                        latitude: userLocation.latitude,
                        longitude: userLocation.longitude,
                        scan_time: new Date().toISOString()
                    })
                });

                const result = await response.json();

                if (result.success) {
                    showAlert(result.message, 'success');
                    updateScanResult(true, result.data);
                    loadRiwayat(); // Refresh history
                } else {
                    showAlert(result.message, 'danger');
                    updateScanResult(false, result.message);
                }

            } catch (error) {
                console.error('Error processing QR Code:', error);
                showAlert('Gagal memproses QR Code: ' + error.message, 'danger');
                updateScanResult(false, 'Error memproses QR');
            }
        }

        // Start Scanner
        function startScanner() {
            if (isScanning) return;

            html5QrcodeScanner = new Html5Qrcode("reader");

            html5QrcodeScanner.start({
                    facingMode: "environment"
                }, // Use back camera
                {
                    fps: 10,
                    qrbox: {
                        width: 250,
                        height: 250
                    }
                },
                (decodedText, decodedResult) => {
                    // QR Code detected
                    processQRCode(decodedText);
                },
                (errorMessage) => {
                    // Scanning error (ignore)
                }
            ).then(() => {
                isScanning = true;
                updateCameraStatus(true);
                document.getElementById('startBtn').classList.add('d-none');
                document.getElementById('stopBtn').classList.remove('d-none');
            }).catch(err => {
                console.error('Error starting scanner:', err);
                showAlert('Gagal mengaktifkan kamera: ' + err, 'danger');
            });
        }

        // Stop Scanner
        function stopScanner() {
            if (!isScanning || !html5QrcodeScanner) return;

            html5QrcodeScanner.stop().then(() => {
                isScanning = false;
                updateCameraStatus(false);
                document.getElementById('startBtn').classList.remove('d-none');
                document.getElementById('stopBtn').classList.add('d-none');
            }).catch(err => {
                console.error('Error stopping scanner:', err);
            });
        }

        // Load Riwayat
        async function loadRiwayat() {
            try {
                const response = await fetch('{{ route('guru.absensi.riwayat') }}');
                const data = await response.json();

                const tbody = document.getElementById('riwayatTable');

                if (data.length === 0) {
                    tbody.innerHTML =
                        '<tr><td colspan="5" class="text-center text-muted">Belum ada riwayat absensi hari ini</td></tr>';
                    return;
                }

                let html = '';
                data.forEach(item => {
                    const statusClass = item.status_kehadiran === 'hadir' ? 'success' :
                        item.status_kehadiran === 'terlambat' ? 'warning' : 'danger';
                    const statusText = item.status_kehadiran === 'hadir' ? 'Hadir' :
                        item.status_kehadiran === 'terlambat' ? 'Terlambat' : 'Alfa';

                    html += `
                <tr>
                    <td>${item.jam_absen}</td>
                    <td>${item.kelas || '-'}</td>
                    <td>${item.jadwal.mata_pelajaran}</td>
                    <td><span class="badge bg-${statusClass}">${statusText}</span></td>
                    <td class="small text-muted">${item.keterangan || '-'}</td>
                </tr>
            `;
                });

                tbody.innerHTML = html;
            } catch (error) {
                console.error('Error loading riwayat:', error);
                document.getElementById('riwayatTable').innerHTML =
                    '<tr><td colspan="5" class="text-center text-danger">Gagal memuat riwayat</td></tr>';
            }
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            initGPS();
            loadRiwayat();

            // Start/Stop buttons
            document.getElementById('startBtn').addEventListener('click', startScanner);
            document.getElementById('stopBtn').addEventListener('click', stopScanner);
        });

        // Cleanup on page unload
        window.addEventListener('beforeunload', function() {
            if (isScanning) {
                stopScanner();
            }
        });
    </script>
@endpush
