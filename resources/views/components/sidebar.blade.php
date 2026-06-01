<aside class="sidebar" id="sidebar">

<div class="brand" style="display: flex; flex-direction: column; align-items: center; justify-content: center; margin-bottom: 25px; width: 100%;">
    <div class="brand-logo" style="display: flex; justify-content: center; margin-bottom: 8px;">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" style="width: 60px; height: 60px;">
            <defs>
                <filter id="glow" x="-20%" y="-20%" width="140%" height="140%">
                    <feDropShadow dx="0" dy="3" stdDeviation="3" flood-color="#000000" flood-opacity="0.15"/>
                </filter>
            </defs>
            
            <g filter="url(#glow)">
                <path d="M20 75 C 20 70, 80 70, 80 75 C 80 82, 20 82, 20 75 Z" fill="#FDF3E7" opacity="0.3"/>
                <circle cx="27" cy="74" r="3" fill="#FFE082"/>
                <circle cx="73" cy="75" r="3" fill="#81D4FA"/>
                <circle cx="50" cy="77" r="3.5" fill="#FF8A80"/>

                <rect x="25" y="45" width="12" height="30" rx="4" fill="#FFFFFF"/>
                <rect x="63" y="45" width="12" height="30" rx="4" fill="#FFFFFF"/>
                
                <path d="M22 45 C 22 35, 40 35, 40 45 Z" fill="#FFE082"/>
                <path d="M60 45 C 60 35, 78 35, 78 45 Z" fill="#81D4FA"/>
                
                <rect x="37" y="55" width="26" height="20" rx="2" fill="#FFFFFF" opacity="0.9"/>
                <path d="M44 75 C 44 63, 56 63, 56 75 Z" fill="#E87A34"/>

                <path d="M50 35 L 50 55" stroke="#FFFFFF" stroke-width="4" stroke-linecap="round"/>
                <path d="M48 38 C 65 38, 75 50, 72 65 C 70 72, 58 75, 52 70" fill="none" stroke="#FDF3E7" stroke-width="7" stroke-linecap="round"/>
                <path d="M48 38 C 65 38, 75 50, 72 65" fill="none" stroke="#FFE082" stroke-width="2" stroke-linecap="round"/>

                <path d="M31 32 L 37 35 L 31 38 Z" fill="#FF8A80"/>
                <line x1="31" y1="32" x2="31" y2="35" stroke="#FFFFFF" stroke-width="1.5"/>
                
                <path d="M75 25 L 76.5 28.5 L 80 30 L 76.5 31.5 L 75 35 L 73.5 31.5 L 70 30 L 73.5 28.5 Z" fill="#FFE082"/>
                <path d="M20 28 L 21 30 L 23 30.5 L 21 31 L 20 33 L 19 31 L 17 30.5 L 19 30 Z" fill="#FFFFFF"/>
            </g>
        </svg>
    </div>

    <div class="brand-text" style="text-align: center;">
        <div class="brand-name" style="color: #ffffff; font-size: 1.2rem; font-weight: 500; letter-spacing: 0.5px;">PlayZone</div>
    </div>
</div>


    <nav class="sb-nav">

        <a href="{{ route('dashboard') }}"
           class="sb-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">

            <i class="fas fa-chart-bar"></i>

            <span>Dashboard</span>

        </a>

        <a href="{{ route('scanner.index') }}"
           class="sb-item {{ request()->is('scanner*') ? 'active' : '' }}">

            <i class="fas fa-qrcode"></i>

            <span>QR Scanner</span>

        </a>

        <a href="{{ route('walkin.index') }}"
           class="sb-item {{ request()->is('walk-in*') ? 'active' : '' }}">

            <i class="fas fa-clipboard-list"></i>

            <span>Pesan Langsung</span>

        </a>

        <a href="{{ route('transaction') }}"
           class="sb-item {{ request()->is('transaction*') ? 'active' : '' }}">

            <i class="fas fa-credit-card"></i>

            <span>Transaction</span>

        </a>

    </nav>

    <div class="sb-live-box">

        <span class="sb-live-dot"></span>

        <span class="sb-live-label">LIVE</span>

        <span class="sb-live-clock" id="live-clock">--:--:--</span>

    </div>

    <div class="sb-footer">

        <div class="sb-avatar">
            {{ strtoupper(substr(auth()->user()->name ?? 'K', 0, 1)) }}
        </div>

        <div class="sb-user-info">

            <div class="sb-uname">
                {{ auth()->user()->name ?? 'Kasir' }}
            </div>

        </div>

        <a href="#"
           onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
           class="sb-logout"
           title="Logout">

            <i class="fas fa-sign-out-alt"></i>

        </a>

        <form id="logout-form"
              action="/logout"
              method="POST"
              style="display:none;">

            @csrf

        </form>

    </div>

</aside>

<script>

    setInterval(() => {

        const now = new Date();

        const clock =
            document.getElementById('live-clock');

        if (clock) {

            clock.textContent =
                now.toLocaleTimeString(
                    'id-ID',
                    {
                        hour12: false
                    }
                );
        }

    }, 1000);

</script>