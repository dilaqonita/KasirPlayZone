<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PlayZone – Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        /* ── WARNA UTAMA ─────────────────── */
        :root {
            --or:      #E87A34;   /* Warna Oranye Utama */
            --or-pale: #FDEFE7;   /* Warna Background Halaman (Krem Lembut) */
            --card:    #E87A34;   /* Kotak Login Tetap Oranye */
            --txt:     #FFFFFF;   /* Semua Teks di Kartu Warna Putih */
            --input-bg:#F4F5F7;   /* Background input abu-abu muda lembut */
            --btn-bg:  #FDF3E7;   /* Tombol Login Putih Krem */
            --btn-txt: #E87A34;   /* Teks Tombol Login Oranye */
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        
        body {
            font-family: 'Nunito', sans-serif;
            min-height: 100vh;
            background: var(--or-pale); 
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .card {
            background: var(--card); 
            border-radius: 24px;
            padding: 40px 36px;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 24px 80px rgba(232, 122, 52, 0.3);
        }
        
        .brand {
            text-align: center;
            margin-bottom: 32px;
            display: flex;
            flex-direction: column;
            align-items: center; /* Memastikan semua elemen brand di tengah */
        }
        
        /* CSS Icon */
        .brand-logo { 
            /* Jarak disesuaikan di HTML inline agar lebih fleksibel */
            color: var(--txt);
            display: flex;
            justify-content: center;
        }
        
        .brand-name { 
            font-size: 1.7rem; 
            font-weight: 900; 
            color: var(--txt); 
            margin-top: 0; /* PERBAIKAN: Jarak atas dihapus */
            line-height: 1.2;
        }
        
        /* Tempat teks tambahan agar otomatis berwarna putih lembut */
        .brand-sub { 
            font-size: .85rem; 
            color: rgba(255, 255, 255, 0.85); 
            font-weight: 600; 
            margin-top: 4px; 
            line-height: 1.5; 
        }

        .fg { margin-bottom: 16px; }
        .fg label { display: block; font-size: .82rem; font-weight: 800; color: var(--txt); margin-bottom: 6px; }
        
        .input-wrap { position: relative; }
        .input-wrap .ico { 
            position: absolute; 
            left: 14px; 
            top: 50%; 
            transform: translateY(-50%); 
            color: #9aa0b4; 
            font-size: .85rem; 
            pointer-events: none; 
        }
        
        .input-wrap input {
            width: 100%; 
            padding: 12px 40px 12px 40px; /* Jarak teks aman dari icon */
            border: none; 
            border-radius: 12px;
            background: var(--input-bg); 
            font-size: .88rem;
            font-family: 'Nunito', sans-serif;
            color: #1a1d2e; 
            font-weight: 600; 
            outline: none;
        }
        .input-wrap input:focus { background: #fff; }
        
        .input-wrap .toggle-pwd {
            position: absolute; 
            right: 14px; 
            top: 50%; 
            transform: translateY(-50%);
            background: none; 
            border: none; 
            color: #9aa0b4; 
            cursor: pointer; 
            font-size: .85rem;
        }

        .row-check {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 20px; font-size: .8rem;
        }
        .row-check label { display: flex; align-items: center; gap: 7px; color: var(--txt); font-weight: 700; cursor: pointer; }
        .row-check input[type=checkbox] { accent-color: var(--or-pale); width: 15px; height: 15px; }

        .btn-login {
            width: 100%; padding: 14px;
            background: var(--btn-bg); 
            color: var(--btn-txt);
            border: none; 
            border-radius: 12px; 
            font-size: .95rem; 
            font-weight: 800;
            cursor: pointer; 
            font-family: 'Nunito', sans-serif;
            display: flex; 
            align-items: center; 
            justify-content: center; 
            gap: 8px;
            transition: opacity .15s;
        }
        .btn-login:hover { opacity: 0.9; }

        .error-box {
            background: rgba(255, 255, 255, 0.2); 
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 14px; padding: 12px 16px;
            font-size: .8rem; font-weight: 700; color: var(--txt);
            display: flex; align-items: center; gap: 12px;
            margin-bottom: 16px;
        }

        .footer { text-align: center; margin-top: 20px; font-size: .76rem; color: rgba(255,255,255,0.7); font-weight: 600; }

        @media (max-width: 480px) {
            .card { padding: 28px 20px; }
        }
    </style>
</head>
<body>

<div class="card">
    <div class="brand">
        <div class="brand-logo" style="display: flex; justify-content: center; margin-bottom: 5px;">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" style="width: 80px; height: 80px;">
                <defs>
                    <filter id="glow" x="-20%" y="-20%" width="140%" height="140%">
                        <feDropShadow dx="0" dy="4" stdDeviation="4" flood-color="#000000" flood-opacity="0.15"/>
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
        <div class="brand-name">PlayZone</div>
        <div class="brand-sub">
            Kasir Login <br>
            Masuk untuk mengelola sistem bisnis Playground
        </div>
    </div>

    {{-- Error dari Laravel --}}
    @if ($errors->any())
        <div class="error-box">
            <i class="fas fa-exclamation-circle"></i>
            {{ $errors->first() }}
        </div>
    @endif

    @if (session('error'))
        <div class="error-box">
            <i class="fas fa-exclamation-circle"></i>
            {{ session('error') }}
        </div>
    @endif

    <form method="POST" action="/login-api">
        @csrf

        <div class="fg">
            <label for="email">Email</label>
            <div class="input-wrap">
                <i class="fas fa-envelope ico"></i>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="email@contoh.com"
                    required
                    autocomplete="email"
                    autofocus
                >
            </div>
        </div>

        <div class="fg">
            <label for="password">Kata Sandi</label>
            <div class="input-wrap">
                <i class="fas fa-lock ico"></i>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Masukkan kata sandi"
                    required
                    autocomplete="current-password"
                >
                <button type="button" class="toggle-pwd" onclick="togglePwd()" tabindex="-1">
                    <i class="fas fa-eye-slash" id="pwd-ico"></i>
                </button>
            </div>
        </div>

        <div class="row-check">
            <label>
                <input type="checkbox" name="remember" id="remember">
                Ingat Saya
            </label>
        </div>

        <button type="submit" class="btn-login">
            <i class="fas fa-sign-in-alt"></i> Masuk
        </button>
    </form>
</div>

<script>
function togglePwd() {
    var inp = document.getElementById('password');
    var ico = document.getElementById('pwd-ico');
    if (inp.type === 'password') {
        inp.type = 'text';
        ico.className = 'fas fa-eye';
    } else {
        inp.type = 'password';
        ico.className = 'fas fa-eye-slash';
    }
}
</script>

</body>
</html>