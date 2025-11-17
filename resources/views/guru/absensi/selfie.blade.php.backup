@extends('layouts.app')

@section('title', 'Absensi Selfie')

@section('content')
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <!-- Header -->
                <div class="text-center mb-4">
                    <h2 class="fw-bold text-primary">Absensi dengan Selfie</h2>
                    <p class="text-muted">Ambil foto selfie Anda untuk absensi</p>
                </div>

                <!-- Alert -->
                <div id="alertContainer"></div>

                <div class="row">
                    <!-- Camera Section -->
                    <div class="col-md-8">
                        <div class="card shadow-sm">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="bi bi-camera-fill"></i> Kamera Selfie</h5>
                            </div>
                            <div class="card-body">
                                <!-- Camera Preview -->
                                <div id="cameraContainer" class="text-center mb-3">
                                    <video id="video" autoplay playsinline class="w-100 rounded d-none"></video>
                                    <canvas id="canvas" class="w-100 rounded d-none"></canvas>
                                    <div id="placeholder" class="camera-placeholder">
                                        <i class="bi bi-camera text-muted" style="font-size: 5rem;"></i>
                                        <p class="text-muted mt-3">Kamera belum aktif</p>
                                    </div>
                                </div>

                                <!-- Camera Controls -->
                                <div class="d-flex justify-content-center gap-2">
                                    <button type="button" class="btn btn-success" id="startCameraBtn">
                                        <i class="bi bi-camera-video"></i> Aktifkan Kamera
                                    </button>
                                    <button type="button" class="btn btn-primary d-none" id="captureBtn">
                                        <i class="bi bi-camera"></i> Ambil Foto
                                    </button>
                                    <button type="button" class="btn btn-warning d-none" id="retakeBtn">
                                        <i class="bi bi-arrow-counterclockwise"></i> Ulangi
                                    </button>
                                    <button type="button" class="btn btn-success d-none" id="submitBtn" disabled>
                                        <i class="bi bi-check-circle"></i> Submit Absensi
                                    </button>
                                </div>

                                <!-- Instructions -->
                                <div class="alert alert-info mt-3">
                                    <h6 class="fw-bold mb-2">
                                        <i class="bi bi-info-circle"></i> Petunjuk:
                                    </h6>
                                    <ol class="mb-0 small">
                                        <li>Klik "Aktifkan Kamera" untuk membuka kamera</li>
                                        <li>Pastikan wajah Anda terlihat jelas</li>
                                        <li>Pastikan pencahayaan cukup</li>
                                        <li>Klik "Ambil Foto" untuk mengambil selfie</li>
                                        <li>Periksa hasil foto, klik "Ulangi" jika kurang bagus</li>
                                        <li>Klik "Submit Absensi" jika sudah OK</li>
                                    </ol>
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

                        <!-- Jadwal Hari Ini -->
                        <div class="card shadow-sm mb-3">
                            <div class="card-header bg-white">
                                <h6 class="mb-0"><i class="bi bi-calendar-check"></i> Jadwal Hari Ini</h6>
                            </div>
                            <div class="card-body">
                                <div id="jadwalList">
                                    <div class="text-center py-2">
                                        <span class="spinner-border spinner-border-sm" role="status"></span>
                                        <p class="text-muted mb-0 mt-2 small">Memuat jadwal...</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Info Foto -->
                        <div class="card shadow-sm">
                            <div class="card-header bg-white">
                                <h6 class="mb-0"><i class="bi bi-image"></i> Info Foto</h6>
                            </div>
                            <div class="card-body">
                                <div id="photoInfo">
                                    <p class="small text-muted mb-0">Belum ada foto yang diambil</p>
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
                        <div id="riwayatAbsensi">
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
    <style>
        .camera-placeholder {
            background-color: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 0.5rem;
            padding: 3rem;
            min-height: 400px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        #video,
        #canvas {
            max-height: 500px;
            object-fit: cover;
        }
    </style>
@endpush

@push('scripts')
    <script>
        let stream = null;
        let capturedImageData = null;
        let userLocation = null;
        let selectedJadwalId = null;

        // GPS Configuration
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
                    document.getElementById('startCameraBtn').disabled = true;
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
                document.getElementById('startCameraBtn').disabled = true;
            }
        }

        // Start Camera
        async function startCamera() {
            try {
                stream = await navigator.mediaDevices.getUserMedia({
                    video: {
                        facingMode: 'user',
                        width: {
                            ideal: 1280
                        },
                        height: {
                            ideal: 720
                        }
                    }
                });

                const video = document.getElementById('video');
                video.srcObject = stream;

                document.getElementById('placeholder').classList.add('d-none');
                video.classList.remove('d-none');

                document.getElementById('startCameraBtn').classList.add('d-none');
                document.getElementById('captureBtn').classList.remove('d-none');

            } catch (error) {
                console.error('Camera Error:', error);
                showAlert('Gagal mengaktifkan kamera: ' + error.message, 'danger');
            }
        }

        // Capture Photo
        function capturePhoto() {
            const video = document.getElementById('video');
            const canvas = document.getElementById('canvas');
            const context = canvas.getContext('2d');

            // Set canvas size (compress to 800x600)
            canvas.width = 800;
            canvas.height = 600;

            // Draw video frame to canvas
            context.drawImage(video, 0, 0, canvas.width, canvas.height);

            // Get compressed image data (JPEG 75% quality)
            capturedImageData = canvas.toDataURL('image/jpeg', 0.75);

            // Stop camera
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
            }

            // Show captured image
            video.classList.add('d-none');
            canvas.classList.remove('d-none');

            // Update buttons
            document.getElementById('captureBtn').classList.add('d-none');
            document.getElementById('retakeBtn').classList.remove('d-none');
            document.getElementById('submitBtn').classList.remove('d-none');
            document.getElementById('submitBtn').disabled = selectedJadwalId === null;

            // Show photo info
            const dataSize = Math.round(capturedImageData.length / 1024);
            document.getElementById('photoInfo').innerHTML = `
        <p class="small mb-1"><strong>Resolusi:</strong> 800 x 600 px</p>
        <p class="small mb-1"><strong>Format:</strong> JPEG</p>
        <p class="small mb-1"><strong>Ukuran:</strong> ~${dataSize} KB</p>
        <p class="small mb-0 text-success"><i class="bi bi-check-circle"></i> Foto siap disubmit</p>
    `;
        }

        // Retake Photo
        function retakePhoto() {
            const video = document.getElementById('video');
            const canvas = document.getElementById('canvas');

            canvas.classList.add('d-none');
            capturedImageData = null;

            document.getElementById('retakeBtn').classList.add('d-none');
            document.getElementById('submitBtn').classList.add('d-none');
            document.getElementById('startCameraBtn').classList.remove('d-none');

            document.getElementById('photoInfo').innerHTML = `
        <p class="small text-muted mb-0">Belum ada foto yang diambil</p>
    `;
        }

        // Submit Absensi
        async function submitAbsensi() {
            if (!capturedImageData || !userLocation || !selectedJadwalId) {
                showAlert('Data belum lengkap. Pastikan foto sudah diambil, GPS aktif, dan jadwal dipilih.', 'danger');
                return;
            }

            const submitBtn = document.getElementById('submitBtn');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Mengirim...';

            try {
                const response = await fetch('{{ route('guru.absensi.proses-selfie') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        jadwal_id: selectedJadwalId,
                        foto_selfie: capturedImageData,
                        latitude: userLocation.latitude,
                        longitude: userLocation.longitude
                    })
                });

                const result = await response.json();

                if (result.success) {
                    showAlert(result.message, 'success');

                    // Reset form
                    retakePhoto();
                    loadRiwayat();
                    loadJadwal();

                } else {
                    showAlert(result.message, 'danger');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = '<i class="bi bi-check-circle"></i> Submit Absensi';
                }

            } catch (error) {
                console.error('Submit Error:', error);
                showAlert('Gagal mengirim absensi: ' + error.message, 'danger');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="bi bi-check-circle"></i> Submit Absensi';
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

            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }

        // Load Jadwal
        async function loadJadwal() {
            try {
                // For now, use dummy data. Later connect to API
                const jadwalList = document.getElementById('jadwalList');

                // Simulate API call
                setTimeout(() => {
                    jadwalList.innerHTML = `
                <div class="list-group list-group-flush">
                    <button class="list-group-item list-group-item-action jadwal-item" data-jadwal-id="1">
                        <small class="text-muted d-block">07:00 - 08:30</small>
                        <strong>Matematika</strong>
                    </button>
                    <button class="list-group-item list-group-item-action jadwal-item" data-jadwal-id="2">
                        <small class="text-muted d-block">08:30 - 10:00</small>
                        <strong>Fisika</strong>
                    </button>
                </div>
                <p class="small text-muted mt-2 mb-0">Pilih jadwal untuk absensi</p>
            `;

                    // Add click handlers
                    document.querySelectorAll('.jadwal-item').forEach(item => {
                        item.addEventListener('click', function() {
                            // Remove active from all
                            document.querySelectorAll('.jadwal-item').forEach(i => i.classList
                                .remove('active'));
                            // Add active to clicked
                            this.classList.add('active');
                            selectedJadwalId = this.dataset.jadwalId;

                            // Enable submit if photo is captured
                            if (capturedImageData) {
                                document.getElementById('submitBtn').disabled = false;
                            }
                        });
                    });
                }, 500);

            } catch (error) {
                console.error('Error loading jadwal:', error);
                document.getElementById('jadwalList').innerHTML =
                    '<p class="text-center text-danger small">Gagal memuat jadwal</p>';
            }
        }

        // Load Riwayat
        async function loadRiwayat() {
            try {
                const response = await fetch('{{ route('guru.absensi.riwayat') }}');
                const data = await response.json();

                const container = document.getElementById('riwayatAbsensi');

                if (data.length === 0) {
                    container.innerHTML =
                        '<p class="text-center text-muted py-3">Belum ada riwayat absensi hari ini</p>';
                    return;
                }

                let html = '<div class="list-group">';
                data.forEach(item => {
                    const statusClass = item.status_kehadiran === 'hadir' ? 'success' :
                        item.status_kehadiran === 'terlambat' ? 'warning' : 'danger';
                    const statusText = item.status_kehadiran === 'hadir' ? 'Hadir' :
                        item.status_kehadiran === 'terlambat' ? 'Terlambat' : 'Tidak Hadir';

                    html += `
                <div class="list-group-item">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">${item.jadwal.mata_pelajaran}</h6>
                            <small class="text-muted">
                                <i class="bi bi-clock"></i> ${item.jam_absen}
                                ${item.metode_absensi === 'qr' ? '<i class="bi bi-qr-code ms-2"></i>' : '<i class="bi bi-camera ms-2"></i>'}
                                ${item.metode_absensi === 'qr' ? 'QR Code' : 'Selfie'}
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
                document.getElementById('riwayatAbsensi').innerHTML =
                    '<p class="text-center text-danger py-3">Gagal memuat riwayat absensi</p>';
            }
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            initGPS();
            loadJadwal();
            loadRiwayat();

            // Button handlers
            document.getElementById('startCameraBtn').addEventListener('click', startCamera);
            document.getElementById('captureBtn').addEventListener('click', capturePhoto);
            document.getElementById('retakeBtn').addEventListener('click', retakePhoto);
            document.getElementById('submitBtn').addEventListener('click', submitAbsensi);
        });

        // Cleanup on page unload
        window.addEventListener('beforeunload', function() {
            if (stream) {
                stream.getTracks().forEach(track => track.stop());
            }
        });
    </script>
@endpush
