<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Sistem Informasi Absensi Guru SMK Negeri Kasomalang">

    <title>@yield('title', 'SIAG NEKAS') - {{ config('app.name') }}</title>

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

    <!-- Custom CSS -->
    @vite(['resources/css/app.css'])

    @stack('styles')
</head>

<body>
    @yield('content')

    <!-- Bootstrap JS Local -->
    <script src="{{ asset('assets/vendor/bootstrap-5.3.3/js/bootstrap.bundle.min.js') }}"></script>

    @stack('scripts')
</body>

</html>
