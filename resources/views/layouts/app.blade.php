<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<meta name="walkin-store-url" content="{{ route('walkin.store') }}">
<title>PlayZone – Sistem Kasir</title>

<!-- Fonts & Icons -->
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<!-- CSS LAYOUT UTAMA KASIR PLAYZONE (Penyelamat Menu Atas) -->
<style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body { font-family: 'Nunito', sans-serif; background-color: #FDFBF9; color: #1e293b; min-height: 100vh; overflow-x: hidden; }

    /* LAYOUT UTAMA */
    .main { margin-left: 260px; min-height: 100vh; display: flex; flex-direction: column; transition: all 0.3s ease; }
    .content { padding: 24px; flex-grow: 1; background-color: #FDFBF9 !important; }

    /* TOPBAR / NAVBAR */
    .topbar { display: flex; justify-content: space-between; align-items: center; padding: 16px 24px; background: #ffffff; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.02); border-bottom: 1px solid #f1f5f9; }
    .tb-left { display: flex; align-items: center; gap: 16px; }
    .sb-toggle { background: none; border: none; font-size: 20px; color: #64748b; cursor: pointer; display: none; }
    .tb-page-title { font-size: 20px; font-weight: 800; color: #0f172a; line-height: 1.2; }
    .tb-page-sub { font-size: 13px; color: #94a3b8; font-weight: 600; margin-top: 2px; }
    .tb-right { display: flex; align-items: center; gap: 20px; }
    .tb-date { font-size: 14px; font-weight: 700; color: #64748b; background: #f8fafc; padding: 8px 14px; border-radius: 10px; border: 1px solid #e2e8f0; }

    /* NOTIFIKASI DROPDOWN */
    .notif-wrap { position: relative; }
    .tb-icon-btn { background: #f8fafc; border: 1px solid #e2e8f0; width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; color: #64748b; font-size: 16px; cursor: pointer; position: relative; transition: all 0.2s; }
    .tb-icon-btn:hover { background: #f1f5f9; color: #0f172a; }
    .tb-badge { position: absolute; top: -6px; right: -6px; background: #ef4444; color: white; font-size: 11px; font-weight: 800; min-width: 18px; height: 18px; border-radius: 10px; display: flex; align-items: center; justify-content: center; padding: 0 4px; border: 2px solid #fff; }
    .notif-dropdown { display: none; position: absolute; right: 0; top: 50px; width: 320px; background: #ffffff; border-radius: 16px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); border: 1px solid #e2e8f0; z-index: 1000; overflow: hidden; }
    .notif-dropdown.show { display: block; }
    .notif-hd { display: flex; justify-content: space-between; align-items: center; padding: 14px 16px; border-bottom: 1px solid #f1f5f9; background: #f8fafc; }
    .notif-hd-title { font-weight: 800; font-size: 14px; color: #0f172a; }
    .notif-mark-btn { background: none; border: none; color: #ef4444; font-size: 12px; font-weight: 700; cursor: pointer; }
    .notif-list { max-height: 280px; overflow-y: auto; }
    .notif-item { display: flex; gap: 12px; padding: 12px 16px; border-bottom: 1px solid #f8fafc; cursor: pointer; transition: background 0.2s; text-decoration: none; }
    .notif-item:hover { background: #f8fafc; }
    .notif-msg { font-size: 13px; font-weight: 700; color: #1e293b; line-height: 1.4; }
    .notif-sub { font-size: 11px; color: #64748b; margin-top: 3px; }
    .notif-ft { padding: 10px; text-align: center; border-top: 1px solid #f1f5f9; background: #f8fafc; }
    .notif-more-btn { background: none; border: none; color: #64748b; font-size: 12px; font-weight: 700; cursor: pointer; width: 100%; }

    /* SIDEBAR MOBILE OVERLAY */
    .sb-overlay { display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.3); backdrop-filter: blur(4px); z-index: 9998; }

    /* RESPONSIVE RESPONSES */
    @media (max-width: 992px) {
        .main { margin-left: 0; }
        .sb-toggle { display: block; }
        .sb-overlay.show { display: block; }
    }
</style>
</head>
<body>

<div class="sb-overlay" id="sb-overlay" onclick="closeSidebar()"></div>

@include('components.sidebar')

<div class="main">
    @include('components.navbar')

    <div class="content">
        @yield('content')
    </div>
</div>

<div id="toast-wrap"></div>

@include('components.modals')

@stack('scripts')

<!-- Script Dropdown Notifikasi Otomatis -->
<script>
function toggleNotif() {
    var dropdown = document.getElementById('notif-dropdown');
    if(dropdown) dropdown.classList.toggle('show');
}
window.onclick = function(event) {
    if (!event.target.matches('#notif-btn') && !event.target.matches('#notif-btn *')) {
        var dropdown = document.getElementById('notif-dropdown');
        if (dropdown && dropdown.classList.contains('show')) {
            dropdown.classList.remove('show');
        }
    }
}
</script>
</body>
</html>