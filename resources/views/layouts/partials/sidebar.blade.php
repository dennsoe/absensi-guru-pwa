{{-- Sidebar Component - Role-based Navigation --}}
<aside class="sidebar">
    {{-- Sidebar Header --}}
    <div class="sidebar-header">
        <img src="{{ asset('assets/images/logonekas.png') }}" alt="Logo NEKAS" class="sidebar-logo">
        <div class="sidebar-brand-name">SIAG NEKAS</div>
        <div class="sidebar-brand-tagline">SMK Negeri Kasomalang</div>
    </div>

    {{-- User Info --}}
    <div class="sidebar-user">
        <div class="sidebar-user-avatar">
            <x-user-avatar :user="auth()->user()" size="lg" />
        </div>
        <div class="sidebar-user-info">
            <div class="sidebar-user-name">{{ auth()->user()->nama }}</div>
            <span class="sidebar-user-role">
                @switch(auth()->user()->role)
                    @case('admin')
                        Administrator
                    @break

                    @case('guru')
                        Guru
                    @break

                    @case('ketua_kelas')
                        Ketua Kelas
                    @break

                    @case('guru_piket')
                        Guru Piket
                    @break

                    @case('kepala_sekolah')
                        Kepala Sekolah
                    @break

                    @case('kurikulum')
                        Kurikulum
                    @break
                @endswitch
            </span>
        </div>
    </div>

    {{-- Navigation Menu --}}
    <nav class="sidebar-menu">
        {{-- Admin Menu --}}
        @if (auth()->user()->role === 'admin')
            <div class="sidebar-menu-section">
                <div class="sidebar-menu-section-title">Menu Utama</div>
                <div class="sidebar-menu-item">
                    <a href="{{ route('admin.dashboard') }}"
                        class="sidebar-menu-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2 sidebar-menu-icon"></i>
                        <span class="sidebar-menu-text">Dashboard</span>
                    </a>
                </div>
                <div class="sidebar-menu-item">
                    <a href="{{ route('admin.users') }}"
                        class="sidebar-menu-link {{ request()->routeIs('admin.users') || request()->routeIs('admin.users.*') ? 'active' : '' }}">
                        <i class="bi bi-people sidebar-menu-icon"></i>
                        <span class="sidebar-menu-text">Data Guru</span>
                    </a>
                </div>
                <div class="sidebar-menu-item">
                    <a href="{{ route('admin.kelas') }}"
                        class="sidebar-menu-link {{ request()->routeIs('admin.kelas') || request()->routeIs('admin.kelas.*') ? 'active' : '' }}">
                        <i class="bi bi-building sidebar-menu-icon"></i>
                        <span class="sidebar-menu-text">Data Kelas</span>
                    </a>
                </div>
                <div class="sidebar-menu-item">
                    <a href="{{ route('admin.jadwal.index') }}"
                        class="sidebar-menu-link {{ request()->routeIs('admin.jadwal.*') ? 'active' : '' }}">
                        <i class="bi bi-calendar-week sidebar-menu-icon"></i>
                        <span class="sidebar-menu-text">Jadwal Mengajar</span>
                    </a>
                </div>
            </div>

            <div class="sidebar-menu-section">
                <div class="sidebar-menu-section-title">Absensi & Laporan</div>
                <div class="sidebar-menu-item">
                    <a href="{{ route('admin.absensi.index') }}"
                        class="sidebar-menu-link {{ request()->routeIs('admin.absensi.*') ? 'active' : '' }}">
                        <i class="bi bi-check2-square sidebar-menu-icon"></i>
                        <span class="sidebar-menu-text">Rekap Absensi</span>
                    </a>
                </div>
                <div class="sidebar-menu-item">
                    <a href="{{ route('admin.laporan.index') }}"
                        class="sidebar-menu-link {{ request()->routeIs('admin.laporan.*') ? 'active' : '' }}">
                        <i class="bi bi-file-earmark-text sidebar-menu-icon"></i>
                        <span class="sidebar-menu-text">Laporan</span>
                    </a>
                </div>
            </div>

            <div class="sidebar-menu-section">
                <div class="sidebar-menu-section-title">Pengaturan</div>
                <div class="sidebar-menu-item">
                    <a href="{{ route('admin.settings') }}"
                        class="sidebar-menu-link {{ request()->routeIs('admin.settings') ? 'active' : '' }}">
                        <i class="bi bi-gear sidebar-menu-icon"></i>
                        <span class="sidebar-menu-text">Pengaturan</span>
                    </a>
                </div>
            </div>
        @endif

        {{-- Guru Menu --}}
        @if (auth()->user()->role === 'guru')
            <div class="sidebar-menu-section">
                <div class="sidebar-menu-section-title">Menu Utama</div>
                <div class="sidebar-menu-item">
                    <a href="{{ route('guru.dashboard') }}"
                        class="sidebar-menu-link {{ request()->routeIs('guru.dashboard') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2 sidebar-menu-icon"></i>
                        <span class="sidebar-menu-text">Dashboard</span>
                    </a>
                </div>
                <div class="sidebar-menu-item">
                    <a href="{{ route('guru.jadwal.index') }}"
                        class="sidebar-menu-link {{ request()->routeIs('guru.jadwal.*') ? 'active' : '' }}">
                        <i class="bi bi-calendar-week sidebar-menu-icon"></i>
                        <span class="sidebar-menu-text">Jadwal Mengajar</span>
                    </a>
                </div>
            </div>

            <div class="sidebar-menu-section">
                <div class="sidebar-menu-section-title">Absensi</div>
                <div class="sidebar-menu-item">
                    <a href="{{ route('guru.absensi.scan-qr') }}"
                        class="sidebar-menu-link {{ request()->routeIs('guru.absensi.scan-qr') ? 'active' : '' }}">
                        <i class="bi bi-qr-code-scan sidebar-menu-icon"></i>
                        <span class="sidebar-menu-text">Scan QR Kelas</span>
                    </a>
                </div>
                <div class="sidebar-menu-item">
                    <a href="{{ route('guru.absensi.selfie') }}"
                        class="sidebar-menu-link {{ request()->routeIs('guru.absensi.selfie') ? 'active' : '' }}">
                        <i class="bi bi-camera sidebar-menu-icon"></i>
                        <span class="sidebar-menu-text">Absen Selfie</span>
                    </a>
                </div>
                <div class="sidebar-menu-item">
                    <a href="{{ route('guru.absensi.riwayat') }}"
                        class="sidebar-menu-link {{ request()->routeIs('guru.absensi.riwayat') ? 'active' : '' }}">
                        <i class="bi bi-clock-history sidebar-menu-icon"></i>
                        <span class="sidebar-menu-text">Riwayat Absensi</span>
                    </a>
                </div>
            </div>

            <div class="sidebar-menu-section">
                <div class="sidebar-menu-section-title">Izin & Cuti</div>
                <div class="sidebar-menu-item">
                    <a href="{{ route('guru.izin.create') }}"
                        class="sidebar-menu-link {{ request()->routeIs('guru.izin.create') ? 'active' : '' }}">
                        <i class="bi bi-file-earmark-plus sidebar-menu-icon"></i>
                        <span class="sidebar-menu-text">Ajukan Izin</span>
                    </a>
                </div>
                <div class="sidebar-menu-item">
                    <a href="{{ route('guru.izin.index') }}"
                        class="sidebar-menu-link {{ request()->routeIs('guru.izin.index') ? 'active' : '' }}">
                        <i class="bi bi-list-check sidebar-menu-icon"></i>
                        <span class="sidebar-menu-text">Status Izin</span>
                    </a>
                </div>
            </div>
        @endif

        {{-- Kepala Sekolah Menu --}}
        @if (auth()->user()->role === 'kepala_sekolah')
            <div class="sidebar-menu-section">
                <div class="sidebar-menu-section-title">Menu Utama</div>
                <div class="sidebar-menu-item">
                    <a href="{{ route('kepala-sekolah.dashboard') }}"
                        class="sidebar-menu-link {{ request()->routeIs('kepala-sekolah.dashboard') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2 sidebar-menu-icon"></i>
                        <span class="sidebar-menu-text">Dashboard</span>
                    </a>
                </div>
                <div class="sidebar-menu-item">
                    <a href="{{ route('kepala-sekolah.monitoring') }}"
                        class="sidebar-menu-link {{ request()->routeIs('kepala-sekolah.monitoring') ? 'active' : '' }}">
                        <i class="bi bi-eye sidebar-menu-icon"></i>
                        <span class="sidebar-menu-text">Monitoring</span>
                    </a>
                </div>
                <div class="sidebar-menu-item">
                    <a href="{{ route('kepala-sekolah.approval') }}"
                        class="sidebar-menu-link {{ request()->routeIs('kepala-sekolah.approval') ? 'active' : '' }}">
                        <i class="bi bi-check-circle sidebar-menu-icon"></i>
                        <span class="sidebar-menu-text">Approval</span>
                        @php
                            $pendingCount = \App\Models\IzinCuti::where('status', 'pending')->count();
                        @endphp
                        @if ($pendingCount > 0)
                            <span class="sidebar-menu-badge">{{ $pendingCount }}</span>
                        @endif
                    </a>
                </div>
                <div class="sidebar-menu-item">
                    <a href="{{ route('kepala-sekolah.laporan') }}"
                        class="sidebar-menu-link {{ request()->routeIs('kepala-sekolah.laporan') ? 'active' : '' }}">
                        <i class="bi bi-file-earmark-text sidebar-menu-icon"></i>
                        <span class="sidebar-menu-text">Laporan</span>
                    </a>
                </div>
            </div>
        @endif

        {{-- Kurikulum Menu --}}
        @if (auth()->user()->role === 'kurikulum')
            <div class="sidebar-menu-section">
                <div class="sidebar-menu-section-title">Menu Utama</div>
                <div class="sidebar-menu-item">
                    <a href="{{ route('kurikulum.dashboard') }}"
                        class="sidebar-menu-link {{ request()->routeIs('kurikulum.dashboard') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2 sidebar-menu-icon"></i>
                        <span class="sidebar-menu-text">Dashboard</span>
                    </a>
                </div>
                <div class="sidebar-menu-item">
                    <a href="{{ route('kurikulum.jadwal.index') }}"
                        class="sidebar-menu-link {{ request()->routeIs('kurikulum.jadwal.*') ? 'active' : '' }}">
                        <i class="bi bi-calendar-week sidebar-menu-icon"></i>
                        <span class="sidebar-menu-text">Kelola Jadwal</span>
                    </a>
                </div>
                <div class="sidebar-menu-item">
                    <a href="{{ route('kurikulum.guru-pengganti') }}"
                        class="sidebar-menu-link {{ request()->routeIs('kurikulum.guru-pengganti') ? 'active' : '' }}">
                        <i class="bi bi-person-plus sidebar-menu-icon"></i>
                        <span class="sidebar-menu-text">Guru Pengganti</span>
                    </a>
                </div>
            </div>
        @endif

        {{-- Guru Piket Menu --}}
        @if (auth()->user()->role === 'guru_piket')
            <div class="sidebar-menu-section">
                <div class="sidebar-menu-section-title">Menu Utama</div>
                <div class="sidebar-menu-item">
                    <a href="{{ route('guru-piket.dashboard') }}"
                        class="sidebar-menu-link {{ request()->routeIs('guru-piket.dashboard') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2 sidebar-menu-icon"></i>
                        <span class="sidebar-menu-text">Dashboard</span>
                    </a>
                </div>
                <div class="sidebar-menu-item">
                    <a href="{{ route('guru-piket.monitoring') }}"
                        class="sidebar-menu-link {{ request()->routeIs('guru-piket.monitoring') ? 'active' : '' }}">
                        <i class="bi bi-eye sidebar-menu-icon"></i>
                        <span class="sidebar-menu-text">Monitoring</span>
                    </a>
                </div>
                <div class="sidebar-menu-item">
                    <a href="{{ route('guru-piket.absensi-manual') }}"
                        class="sidebar-menu-link {{ request()->routeIs('guru-piket.absensi-manual') ? 'active' : '' }}">
                        <i class="bi bi-pencil-square sidebar-menu-icon"></i>
                        <span class="sidebar-menu-text">Absensi Manual</span>
                    </a>
                </div>
            </div>
        @endif

        {{-- Ketua Kelas Menu --}}
        @if (auth()->user()->role === 'ketua_kelas')
            <div class="sidebar-menu-section">
                <div class="sidebar-menu-section-title">Menu Utama</div>
                <div class="sidebar-menu-item">
                    <a href="{{ route('ketua-kelas.dashboard') }}"
                        class="sidebar-menu-link {{ request()->routeIs('ketua-kelas.dashboard') ? 'active' : '' }}">
                        <i class="bi bi-speedometer2 sidebar-menu-icon"></i>
                        <span class="sidebar-menu-text">Dashboard</span>
                    </a>
                </div>
                <div class="sidebar-menu-item">
                    <a href="{{ route('ketua-kelas.generate-qr') }}"
                        class="sidebar-menu-link {{ request()->routeIs('ketua-kelas.generate-qr') ? 'active' : '' }}">
                        <i class="bi bi-qr-code sidebar-menu-icon"></i>
                        <span class="sidebar-menu-text">QR Code Kelas</span>
                    </a>
                </div>
                <div class="sidebar-menu-item">
                    <a href="{{ route('ketua-kelas.riwayat') }}"
                        class="sidebar-menu-link {{ request()->routeIs('ketua-kelas.riwayat') ? 'active' : '' }}">
                        <i class="bi bi-clock-history sidebar-menu-icon"></i>
                        <span class="sidebar-menu-text">Riwayat</span>
                    </a>
                </div>
            </div>
        @endif
    </nav>

    {{-- Sidebar Footer --}}
    <div class="sidebar-footer">
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="sidebar-logout-btn">
                <i class="bi bi-box-arrow-right"></i>
                <span>Logout</span>
            </button>
        </form>
    </div>
</aside>

{{-- Sidebar Overlay for Mobile --}}
<div class="sidebar-overlay" onclick="document.body.classList.remove('sidebar-open')"></div>
