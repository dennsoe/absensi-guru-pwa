{{-- Navbar Component - Top Navigation --}}
<nav class="navbar">
    {{-- Toggle Button for Mobile --}}
    <button class="navbar-toggle" onclick="document.body.classList.toggle('sidebar-open')">
        <i class="bi bi-list"></i>
    </button>

    {{-- Date & Time Display --}}
    <div class="navbar-datetime" id="navbarDateTime">
        <div>
            <i class="bi bi-calendar3"></i>
            <span id="currentDate">Loading...</span>
        </div>
        <div>
            <i class="bi bi-clock"></i>
            <span id="currentTime">Loading...</span>
        </div>
    </div>

    {{-- Navbar Actions --}}
    <div class="navbar-actions">
        {{-- Notification Dropdown --}}
        <div class="dropdown navbar-notification">
            <button class="navbar-notification-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-bell"></i>
                @php
                    $unreadCount = auth()->user()->notifikasi()->where('is_read', false)->count();
                @endphp
                @if ($unreadCount > 0)
                    <span class="navbar-notification-badge">{{ $unreadCount }}</span>
                @endif
            </button>

            <ul class="dropdown-menu dropdown-menu-end navbar-notification-menu" style="min-width: 320px;">
                <li class="navbar-notification-header">
                    <strong>Notifikasi</strong>
                </li>

                <div class="navbar-notification-list">
                    @php
                        $notifications = auth()->user()->notifikasi()->latest()->limit(5)->get();
                    @endphp

                    @forelse($notifications as $notif)
                        <li>
                            <a class="dropdown-item navbar-notification-item {{ !$notif->is_read ? 'unread' : '' }}"
                                href="{{ route('notifikasi.show', $notif->id) }}">
                                <div class="navbar-notification-item-title">{{ $notif->judul }}</div>
                                <div class="navbar-notification-item-message">{{ $notif->pesan }}</div>
                                <div class="navbar-notification-item-time">
                                    {{ $notif->created_at->diffForHumans() }}
                                </div>
                            </a>
                        </li>
                    @empty
                        <li class="navbar-notification-empty">
                            <i class="bi bi-bell-slash"></i>
                            <p>Tidak ada notifikasi</p>
                        </li>
                    @endforelse
                </div>

                @if ($notifications->count() > 0)
                    <li class="navbar-notification-footer">
                        <a href="{{ route('notifikasi.index') }}" class="dropdown-item text-center">Lihat semua
                            notifikasi</a>
                    </li>
                @endif
            </ul>
        </div>

        {{-- User Profile Dropdown --}}
        <div class="dropdown navbar-profile">
            <button class="navbar-profile-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="navbar-profile-avatar">
                    <x-user-avatar :user="auth()->user()" size="sm" />
                </div>
                <div class="navbar-profile-info">
                    <span class="navbar-profile-name">{{ auth()->user()->nama }}</span>
                    <span class="navbar-profile-role">{{ ucfirst(str_replace('_', ' ', auth()->user()->role)) }}</span>
                </div>
                <i class="bi bi-chevron-down"></i>
            </button>

            <ul class="dropdown-menu dropdown-menu-end navbar-profile-menu">
                <li>
                    <a href="{{ route('profile.index') }}" class="dropdown-item navbar-profile-menu-item">
                        <i class="bi bi-person"></i>
                        Profil Saya
                    </a>
                </li>
                <li>
                    <a href="{{ route('profile.edit') }}" class="dropdown-item navbar-profile-menu-item">
                        <i class="bi bi-gear"></i>
                        Pengaturan
                    </a>
                </li>
                <li>
                    <hr class="dropdown-divider navbar-profile-menu-divider">
                </li>
                <li>
                    <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                        @csrf
                        <button type="submit" class="dropdown-item navbar-profile-menu-item">
                            <i class="bi bi-box-arrow-right"></i>
                            Logout
                        </button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>

<script>
    function dateTime() {
        return {
            currentDate: '',
            currentTime: '',
            updateTime() {
                const now = new Date();

                const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                const months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

                const dayName = days[now.getDay()];
                const day = now.getDate();
                const month = months[now.getMonth()];
                const year = now.getFullYear();

                this.currentDate = `${dayName}, ${day} ${month} ${year}`;

                const hours = String(now.getHours()).padStart(2, '0');
                const minutes = String(now.getMinutes()).padStart(2, '0');
                const seconds = String(now.getSeconds()).padStart(2, '0');

                this.currentTime = `${hours}:${minutes}:${seconds}`;

                setTimeout(() => this.updateTime(), 1000);
            }
        }
    }
</script>
