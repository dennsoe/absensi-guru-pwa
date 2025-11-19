<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Sistem Informasi Absensi Guru SMK Negeri Kasomalang">

    <title>@yield('title', 'Dashboard') - {{ config('app.name') }}</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="{{ asset('assets/images/logonekas.png') }}">

    <!-- Bootstrap CSS Local -->
    <link rel="stylesheet" href="{{ asset('assets/vendor/bootstrap-5.3.3/css/bootstrap.min.css') }}">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Google Fonts - Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet" />

    <!-- Custom CSS via Vite -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>

<body>
    <div class="app-wrapper">
        @include('layouts.partials.sidebar')

        <div class="main-content">
            @include('layouts.partials.navbar')

            <div class="content-wrapper">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle me-2"></i>
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-circle me-2"></i>
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <strong>Terdapat kesalahan:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @yield('content')
            </div>
        </div>
    </div>

    <!-- jQuery HARUS PERTAMA (required for Bootstrap components & Select2) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Bootstrap JS Local -->
    <script src="{{ asset('assets/vendor/bootstrap-5.3.3/js/bootstrap.bundle.min.js') }}"></script>

    <!-- Bootstrap Dropdown Initialize & Debug -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('=== DROPDOWN DEBUG START ===');
            console.log('Bootstrap loaded:', typeof bootstrap !== 'undefined');
            console.log('jQuery loaded:', typeof jQuery !== 'undefined');

            // Find all dropdown buttons
            var dropdownButtons = document.querySelectorAll('[data-bs-toggle="dropdown"]');
            console.log('Found dropdown buttons:', dropdownButtons.length);

            dropdownButtons.forEach(function(btn, index) {
                console.log(`Button ${index}:`, btn);
                console.log('  - Has data-bs-toggle:', btn.getAttribute('data-bs-toggle'));
                console.log('  - Type:', btn.getAttribute('type'));
                console.log('  - Computed z-index:', window.getComputedStyle(btn).zIndex);
                console.log('  - Pointer events:', window.getComputedStyle(btn).pointerEvents);

                // Add manual click listener for testing
                btn.addEventListener('click', function(e) {
                    console.log('BUTTON CLICKED!', this);
                    console.log('Event:', e);
                });

                // Initialize Bootstrap dropdown
                try {
                    new bootstrap.Dropdown(btn);
                    console.log(`  ✓ Bootstrap Dropdown initialized for button ${index}`);
                } catch (error) {
                    console.error(`  ✗ Failed to initialize dropdown ${index}:`, error);
                }
            });

            // Check for overlapping elements
            setTimeout(function() {
                dropdownButtons.forEach(function(btn, index) {
                    var rect = btn.getBoundingClientRect();
                    var elementAtPoint = document.elementFromPoint(rect.left + rect.width / 2, rect
                        .top + rect.height / 2);
                    console.log(`Element at button ${index} position:`, elementAtPoint);
                    if (elementAtPoint !== btn && !btn.contains(elementAtPoint)) {
                        console.warn(`⚠️ Button ${index} is being covered by:`, elementAtPoint);
                    }
                });
            }, 500);

            console.log('=== DROPDOWN DEBUG END ===');
        });
    </script>

    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Custom JS sudah diload via Vite di head section -->
    @stack('scripts')
</body>

</html>
