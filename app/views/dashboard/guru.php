<div class="container-fluid p-3 p-md-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-0">
                <i class="bi bi-speedometer2 me-2"></i>
                Dashboard Guru
            </h1>
            <p class="text-muted mb-0">Selamat datang, <?= $_SESSION['nama'] ?? $_SESSION['username'] ?></p>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card card-stat bg-primary text-white">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?= $rekap['hadir'] ?? 0 ?></div>
                        <div class="stat-label">Hadir</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card card-stat bg-warning text-white">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="bi bi-clock"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?= $rekap['terlambat'] ?? 0 ?></div>
                        <div class="stat-label">Terlambat</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card card-stat bg-danger text-white">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="bi bi-x-circle"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?= $rekap['alpha'] ?? 0 ?></div>
                        <div class="stat-label">Alpha</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="card card-stat bg-success text-white">
                <div class="card-body">
                    <div class="stat-icon">
                        <i class="bi bi-percent"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value"><?= $persen_kehadiran ?>%</div>
                        <div class="stat-label">Kehadiran</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Current/Next Schedule -->
    <div class="row mb-4">
        <?php if ($current_jadwal): ?>
        <div class="col-12 col-md-6 mb-3">
            <div class="card border-primary">
                <div class="card-header bg-primary text-white">
                    <i class="bi bi-play-circle me-2"></i>
                    Jadwal Sedang Berlangsung
                </div>
                <div class="card-body">
                    <h5 class="card-title mb-3"><?= $current_jadwal['nama_mapel'] ?></h5>
                    <p class="mb-2">
                        <i class="bi bi-door-open me-2"></i>
                        <strong>Kelas:</strong> <?= $current_jadwal['nama_kelas'] ?>
                    </p>
                    <p class="mb-2">
                        <i class="bi bi-clock me-2"></i>
                        <strong>Waktu:</strong> <?= date('H:i', strtotime($current_jadwal['jam_mulai'])) ?> -
                        <?= date('H:i', strtotime($current_jadwal['jam_selesai'])) ?>
                    </p>
                    <a href="<?= BASE_URL ?>/absensi/masuk" class="btn btn-primary w-100 mt-2">
                        <i class="bi bi-box-arrow-in-right me-2"></i>
                        Absen Masuk
                    </a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($next_jadwal): ?>
        <div class="col-12 col-md-6 mb-3">
            <div class="card border-info">
                <div class="card-header bg-info text-white">
                    <i class="bi bi-skip-forward-circle me-2"></i>
                    Jadwal Selanjutnya
                </div>
                <div class="card-body">
                    <h5 class="card-title mb-3"><?= $next_jadwal['nama_mapel'] ?></h5>
                    <p class="mb-2">
                        <i class="bi bi-door-open me-2"></i>
                        <strong>Kelas:</strong> <?= $next_jadwal['nama_kelas'] ?>
                    </p>
                    <p class="mb-2">
                        <i class="bi bi-clock me-2"></i>
                        <strong>Waktu:</strong> <?= date('H:i', strtotime($next_jadwal['jam_mulai'])) ?> -
                        <?= date('H:i', strtotime($next_jadwal['jam_selesai'])) ?>
                    </p>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Jadwal Hari Ini -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="bi bi-calendar-week me-2"></i>
            Jadwal Hari Ini
        </div>
        <div class="card-body">
            <?php if (!empty($jadwal_hari_ini)): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Waktu</th>
                            <th>Mata Pelajaran</th>
                            <th>Kelas</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($jadwal_hari_ini as $jadwal): ?>
                        <tr>
                            <td>
                                <small class="text-muted">
                                    <?= date('H:i', strtotime($jadwal['jam_mulai'])) ?> -
                                    <?= date('H:i', strtotime($jadwal['jam_selesai'])) ?>
                                </small>
                            </td>
                            <td><?= $jadwal['nama_mapel'] ?></td>
                            <td><?= $jadwal['nama_kelas'] ?></td>
                            <td>
                                <span class="badge bg-secondary">Belum Dimulai</span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-calendar-x display-1 text-muted"></i>
                <p class="text-muted mt-3">Tidak ada jadwal untuk hari ini</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Riwayat Absensi Terakhir -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>
                <i class="bi bi-clock-history me-2"></i>
                Riwayat Absensi Terakhir
            </span>
            <a href="<?= BASE_URL ?>/absensi/riwayat" class="btn btn-sm btn-outline-primary">
                Lihat Semua
            </a>
        </div>
        <div class="card-body">
            <?php if (!empty($riwayat)): ?>
            <div class="list-group list-group-flush">
                <?php foreach ($riwayat as $item): ?>
                <div class="list-group-item px-0">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <h6 class="mb-1"><?= $item['nama_mapel'] ?> - <?= $item['nama_kelas'] ?></h6>
                            <p class="mb-1 small text-muted">
                                <i class="bi bi-calendar me-1"></i>
                                <?= date('d/m/Y', strtotime($item['tanggal'])) ?>
                                <i class="bi bi-clock ms-2 me-1"></i>
                                <?= $item['jam_masuk'] ?> - <?= $item['jam_keluar'] ?? 'Belum Keluar' ?>
                            </p>
                        </div>
                        <span
                            class="badge bg-<?= $item['status_kehadiran'] === 'hadir' ? 'success' : ($item['status_kehadiran'] === 'terlambat' ? 'warning' : 'danger') ?>">
                            <?= ucfirst($item['status_kehadiran']) ?>
                        </span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div class="text-center py-4">
                <i class="bi bi-inbox display-4 text-muted"></i>
                <p class="text-muted mt-2">Belum ada riwayat absensi</p>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
    .card-stat {
        border: none;
        border-radius: 15px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }

    .card-stat .card-body {
        padding: 20px;
        display: flex;
        align-items: center;
    }

    .stat-icon {
        font-size: 40px;
        opacity: 0.8;
        margin-right: 15px;
    }

    .stat-content {
        flex: 1;
    }

    .stat-value {
        font-size: 28px;
        font-weight: 700;
        line-height: 1;
    }

    .stat-label {
        font-size: 12px;
        opacity: 0.9;
        margin-top: 5px;
    }

    <blade media|%20(max-width%3A%20768px)%20%7B>.stat-icon {
        font-size: 30px;
        margin-right: 10px;
    }

    .stat-value {
        font-size: 24px;
    }
    }
</style>