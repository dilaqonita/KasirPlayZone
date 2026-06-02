<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="walkin-store-url" content="{{ route('walkin.store') }}">
<title>PlayZone – Sistem Kasir</title>
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
{{-- Baris funpark.css sudah dihapus dari sini --}}
</head>
<body>

{{-- Mobile sidebar overlay --}}
<div class="sb-overlay" id="sb-overlay" onclick="closeSidebar()"></div>

@include('components.sidebar')

<div class="main">
    @include('components.navbar')

    <div class="content">
        @yield('content')
    </div>
</div>

<!-- Toast container -->
<div id="toast-wrap"></div>

@include('components.modals')

@stack('scripts')
{{-- Baris funpark.js sudah dihapus dari sini --}}
</body>
</html>