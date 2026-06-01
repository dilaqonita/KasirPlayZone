@extends('layouts.app')

@section('page-title', 'Dashboard')
@section('page-sub', 'Ringkasan operasional hari ini')

@section('content')

@if($occupancyPct >= 100)
<div style="
    background: linear-gradient(135deg,#fee2e2,#fef2f2);
    border:1px solid #fecaca;
    border-left:6px solid #dc2626;
    border-radius:16px;
    padding:16px 20px;
    margin-bottom:20px;
    display:flex;
    align-items:center;
    gap:14px;
">
    <div style="
        width:50px;
        height:50px;
        border-radius:50%;
        background:#dc2626;
        color:white;
        display:flex;
        align-items:center;
        justify-content:center;
        font-size:22px;
        flex-shrink:0;
    ">
        🚨
    </div>

    <div>
        <div style="
            font-size:18px;
            font-weight:800;
            color:#991b1b;
            margin-bottom:3px;
        ">
            Kapasitas Penuh!
        </div>

        <div style="
            color:#7f1d1d;
            font-weight:500;
        ">
            Tingkat keramaian sudah
            <strong>{{ $occupancyPct }}%</strong>.
            Disarankan membatasi pemesanan baru.
        </div>
    </div>
</div>

@elseif($occupancyPct >= 70)

<div style="
    background: linear-gradient(135deg,#fff7ed,#fffbeb);
    border:1px solid #fed7aa;
    border-left:6px solid #f97316;
    border-radius:16px;
    padding:16px 20px;
    margin-bottom:20px;
    display:flex;
    align-items:center;
    gap:14px;
">
    <div style="
        width:50px;
        height:50px;
        border-radius:50%;
        background:#f97316;
        color:white;
        display:flex;
        align-items:center;
        justify-content:center;
        font-size:22px;
        flex-shrink:0;
    ">
        ⚠️
    </div>

    <div>
        <div style="
            font-size:18px;
            font-weight:800;
            color:#c2410c;
            margin-bottom:3px;
        ">
            Kapasitas Hampir Penuh!
        </div>

        <div style="
            color:#9a3412;
            font-weight:500;
        ">
            Tingkat keramaian sudah
            <strong>{{ $occupancyPct }}%</strong>.
            Segera pantau kapasitas pengunjung.
        </div>
    </div>
</div>
@endif

{{-- ── 1. CSS CUSTOM DESIGN (100% Sesuai Gambar Acuan) ── --}}
<style>
/* Background utama halaman super soft cream/oren tipis */
body, .content, main { 
    background-color: #FDFBF9 !important; 
}

/* Base layout row untuk Stat Cards */
.stat-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
    margin-bottom: 24px;
}

/* Base style untuk Stat Card */
.stat-card {
    background-color: #ffffff;
    border-radius: 16px;
    padding: 24px;
    text-align: center;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
    border: none;
    transition: transform 0.2s ease;
}

/* Urutan warna border atas Stat Cards */
.stat-card.orange { border-top: 4px solid #F5A34A; }
.stat-card.blue   { border-top: 4px solid #4A90E2; }
.stat-card.purple { border-top: 4px solid #B07DD4; }
.stat-card.green  { border-top: 4px solid #6DC8C0; }

.stat-ico-wrap { font-size: 24px; margin-bottom: 8px; }
.stat-val { font-size: 28px; font-weight: 800; color: #2c3e50; }
.stat-lbl { font-size: 13px; color: #8a99a8; font-weight: 600; }

/* Styling Komponen di Dalam Card Kapasitas Baru Biar Gak Berantakan */
.card .ch {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: 15px;
}
.card .ct {
    font-size: 16px;
    font-weight: 700;
    color: #1e293b;
    display: flex;
    align-items: center;
    gap: 6px;
}
.card .cs {
    font-size: 12px;
    color: #94a3b8;
    margin-top: 2px;
}

/* Tombol Edit Kapasitas Abu-abu Tipis Sesuai Gambar */
.btn-edit-custom {
    width: 100%;
    background-color: #f8fafc;
    color: #334155;
    border: 1px solid #e2e8f0;
    padding: 10px;
    font-size: 14px;
    font-weight: 700;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    justify-content: center;
    align-items: center;
}
.btn-edit-custom:hover {
    background-color: #f1f5f9;
}

/* CSS Modal Overlay untuk Checkout */
.modal-overlay {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.55);
    z-index: 99999;
    align-items: center;
    justify-content: center;
}
.modal-overlay.show { display: flex; }
.modal-box {
    background: #fff;
    border-radius: 16px;
    padding: 28px;
    box-shadow: 0 20px 60px rgba(0,0,0,.25);
    position: relative;
    animation: modalIn .2s ease;
}
@keyframes modalIn {
    from { transform: scale(.93); opacity: 0; }
    to   { transform: scale(1);   opacity: 1; }
}
</style>

{{-- Alert Banner --}}
@if(isset($capacityAlert) && $capacityAlert)
<div class="alert-banner">
    <span class="alert-icon">⚠️</span>
    <div>
        <strong>Kapasitas Hampir Penuh !</strong>
        Slot jam {{ $capacityAlert }}
    </div>
</div>
@endif

{{-- ── 2. STAT CARDS SECTION ── --}}
<div class="stat-row">
    <div class="stat-card orange">
        <div class="stat-ico-wrap orange-ico"><i class="fas fa-calendar-check"></i></div>
        <div class="stat-val" id="d-booking">{{ $stats['total_bookings_today'] }}</div>
        <div class="stat-lbl">Booking Hari Ini</div>
    </div>

    <div class="stat-card blue">
        <div class="stat-ico-wrap blue-ico"><i class="fas fa-users"></i></div>
        <div class="stat-val" id="d-active">{{ $stats['active_visitors'] }}</div>
        <div class="stat-lbl">Pengunjung di Dalam</div>
    </div>

    <div class="stat-card purple">
        <div class="stat-ico-wrap purple-ico"><i class="fas fa-qrcode"></i></div>
        <div class="stat-val" id="d-pending">{{ $stats['pending_verification'] ?? 0 }}</div>
        <div class="stat-lbl">Menunggu Scan</div>
    </div>

    <div class="stat-card green">
        <div class="stat-ico-wrap green-ico"><i class="fas fa-chart-bar"></i></div>
        <div class="stat-val" id="d-capacity">{{ $dailyCapacity }}</div>
        <div class="stat-lbl">Kapasitas per Hari</div>
    </div>
</div>

{{-- ── 3. ROW: VISITOR LIST + CAPACITY INLINE CARD ── --}}
<div class="grid2">
    {{-- Pengunjung Dalam Card --}}
    <div class="card">
        <div class="card-hd">
            <span class="card-title">Pengunjung Dalam</span>
            <span class="update-tag">Update <span id="update-time">{{ now()->format('H:i') }}</span></span>
        </div>
        <div id="visitor-list">
            @forelse($activeVisitors as $visitor)
            @php
                $colors        = ['a','k','s','g','r','d'];
                $colorClass    = $colors[$loop->index % count($colors)];
                $initials = strtoupper(substr($visitor->customer_name, 0, 1));
                $checkinCarbon = \Carbon\Carbon::parse($visitor->waktu_masuk ?? $visitor->check_in_at)->setTimezone('Asia/Jakarta');
            @endphp
            <div class="visitor-item" data-tx-id="{{ $visitor->transaction_id }}">
                <div class="vis-avatar {{ $colorClass }}">{{ $initials }}</div>
                <div class="vis-info">
                    <div class="vis-name">{{ $visitor->customer_name }}</div>
                    <div class="vis-pkg">
    {{ $visitor->paket ?? $visitor->package_name ?? $visitor->nama_paket ?? '-' }}
</div>
                </div>
<button class="vis-co-btn" onclick="openCheckout(
    '{{ $visitor->customer_name ?? '-' }}',
    '{{ $visitor->nama_paket ?? '-' }}',
    '{{ $checkinCarbon->format('H:i') }}',
    '{{ (string)$visitor->transaction_id }}'
)">
    Checkout
</button>
            </div>
            @empty
            <div class="visitor-empty">
                <i class="fas fa-users"></i>
                <span>Belum ada pengunjung aktif</span>
            </div>
            @endforelse
        </div>
    </div>

    {{-- Kapasitas Card Tampilan Murni Sesuai Gambar --}}
    <div class="card" style="display:flex; flex-direction:column; background:#ffffff; border-radius:16px; padding:24px; box-shadow:0 4px 12px rgba(0,0,0,0.03);">
        <div class="ch">
            <div>
                <div class="ct">🎯 Kapasitas</div>
                <div class="cs">Maks pengunjung perhari</div>
            </div>
        </div>
        
        <div style="flex:0.5;"></div>
        
        <div style="text-align:center; padding:18px 0 10px;">
            <div id="capDisplay" style="font-size:60px; font-weight:900; font-family:'Poppins',sans-serif; color:#E28743; line-height:1;">
                {{ $dailyCapacity }}
            </div>
            {{-- Hidden element untuk sinkronisasi live stats --}}
            <span id="cap-number" style="display:none;">{{ $dailyCapacity }}</span>
            <div style="font-size:15px; color:#8a99a8; font-weight:700; margin-top:6px;">Orang / Hari</div>
        </div>
      
        {{-- Mode Tampilan Biasa --}}
        <div id="capViewMode" style="width:100%;">
            <button class="btn-edit-custom" onclick="enableEditCap()">
                Edit Kapasitas
            </button>
        </div>
      
        {{-- Mode Input Edit Inline --}}
        <div id="capEditMode" style="display:none; width:100%;">
            <div class="fg" style="margin-bottom:10px; text-align:left;">
                <label class="fl" style="font-size:12px; font-weight:700; color:#64748b;">Jumlah Kapasitas</label>
                <input class="fi" type="number" id="cap-input" value="{{ $dailyCapacity }}" min="1"
                    style="font-size:16px; font-weight:800; text-align:center; width:100%; padding:8px; border:1px solid #cbd5e1; border-radius:8px; box-sizing:border-box; margin-top:4px;" onkeydown="if(event.key==='Enter') saveCapVal()" />
            </div>
            <div style="display:flex; gap:8px;">
                <button class="btn btn-orange btn-sm" style="flex:1; justify-content:center;" onclick="saveCapVal()">Simpan</button>
                <button class="btn btn-ghost btn-sm" style="flex:1; justify-content:center;" onclick="cancelEditCap()">Batal</button>
            </div>
        </div>
    </div>
</div>

{{-- ── 4. TRANSAKSI TERBARU SECTION ── --}}
<div class="card" style="margin-top:24px;">
    <div class="card-hd">
        <span class="card-title">Transaksi Terbaru</span>

        <a href="{{ route('walkin.index') }}" class="btn btn-orange btn-sm">
            <i class="fas fa-plus"></i> Walk-in
        </a>
    </div>

    <div class="tbl-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Pengguna</th>
                    <th>Paket</th>
                    <th>Total</th>
                    <th>Tanggal</th>
                    <th>Tiket</th>
                </tr>
            </thead>

            <tbody id="trx-tbody">

                @foreach($recentTransactions as $tx)
                <tr>

                    {{-- ID --}}
                    <td style="font-weight:600; color:#64748b; font-size:14px; vertical-align:middle; white-space:nowrap;">
                        {{ $tx['kode_qr'] }}
                    </td>

                    {{-- Pengguna --}}
                    <td style="vertical-align:middle;">
                        <div style="display:flex; align-items:center; gap:12px;">

                            <div style="
                                width:38px;
                                height:38px;
                                min-width:38px;
                                border-radius:999px;
                                background:#9ad0cf;
                                color:white;
                                display:flex;
                                align-items:center;
                                justify-content:center;
                                font-weight:700;
                                font-size:15px;
                            ">
                                {{ strtoupper(substr($tx['nama_customer'],0,1)) }}
                            </div>

                            <div style="
                                font-weight:700;
                                font-size:15px;
                                color:#1e293b;
                                line-height:1.2;
                                white-space:nowrap;
                            ">
                                {{ $tx['nama_customer'] }}
                            </div>

                        </div>
                    </td>

                    {{-- Paket --}}
                    <td style="font-weight:600; color:#334155; font-size:14px; vertical-align:middle; white-space:nowrap;">
                        {{ $tx['nama_paket'] }}
                    </td>

                    {{-- Total --}}
                    <td style="font-weight:700; color:#1e293b; font-size:14px; vertical-align:middle; white-space:nowrap;">
                        Rp.{{ number_format($tx['total_harga'],0,',','.') }}
                    </td>

                    {{-- Tanggal --}}
                    <td style="font-weight:500; color:#64748b; font-size:14px; vertical-align:middle; white-space:nowrap;">
                        {{ \Carbon\Carbon::parse($tx['created_at'])->format('d M Y') }}
                    </td>

                    {{-- Status Ticket --}}
                    <td style="vertical-align:middle;">

                        @php
                            $status = strtolower($tx['status_ticket'] ?? '');
                        @endphp

                        <span style="
                            padding:7px 14px;
                            border-radius:12px;
                            font-size:12px;
                            font-weight:700;
                            display:inline-flex;
                            align-items:center;
                            justify-content:center;
                            min-width:110px;

                            background:
                            {{
                                $status === 'aktif'
                                    ? '#dcfce7'
                                    : ($status === 'digunakan'
                                        ? '#fef3c7'
                                        : '#fee2e2')
                            }};

                            color:
                            {{
                                $status === 'aktif'
                                    ? '#166534'
                                    : ($status === 'digunakan'
                                        ? '#92400e'
                                        : '#991b1b')
                            }};
                        ">
                            {{ ucfirst($status) }}
                        </span>

                    </td>

                </tr>
                @endforeach

            </tbody>
        </table>
    </div>
</div>
{{-- MODAL CHECKOUT --}}
<div id="checkout-modal" class="modal-overlay">
    <div class="modal-box" style="width:360px;max-width:92vw;">
        <div style="font-weight:800;font-size:1rem;margin-bottom:16px;">🚪 Konfirmasi Checkout</div>
        <table style="width:100%;border-collapse:collapse;margin-bottom:18px;">
            <tr><td style="color:#888;font-size:.8rem;padding:6px 0;width:38%">Nama</td><td style="font-weight:700;font-size:.85rem;" id="co-name">–</td></tr>
            <tr><td style="color:#888;font-size:.8rem;padding:6px 0;">Paket</td><td style="font-weight:700;font-size:.85rem;" id="co-paket">–</td></tr>
            <tr><td style="color:#888;font-size:.8rem;padding:6px 0;">Check-in</td><td style="font-weight:700;font-size:.85rem;" id="co-checkin">–</td></tr>
        </table>
        <div style="display:flex;gap:10px;">
            <button class="btn btn-ghost btn-sm" onclick="closeM('checkout-modal')" style="flex:1;">Batal</button>
            <button class="btn btn-orange btn-sm" id="co-confirm-btn" style="flex:1;">✅ Checkout</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
var CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
var CHECKOUT_URL = '/scanner/checkout';
var LIVE_URL = "{{ route('dashboard.live-visitors') }}";
window.currentSlotId = "{{ $slotId }}";

// Update Durasi
function updateDurations() {
    var now = Date.now();
    document.querySelectorAll('.live-time').forEach(function(el) {
        var ts = parseInt(el.getAttribute('data-ts'), 10);
        if (!ts || isNaN(ts)) return;
        var diff = Math.floor((now - ts) / 1000);
        if (diff < 0) { el.textContent = '0s'; return; }
        var h = Math.floor(diff / 3600);
        var m = Math.floor((diff % 3600) / 60);
        var s = diff % 60;
        if (h > 0) el.textContent = h + 'j ' + m + 'm';
        else if (m > 0) el.textContent = m + 'm ' + s + 's';
        else el.textContent = s + 's';
    });
}
setInterval(updateDurations, 1000);

/* ── 5. FUNGSI UNTUK MERESPONS TOMBOL EDIT INLINE ── */
function enableEditCap() {
    var currentVal = document.getElementById('capDisplay').textContent.trim();
    document.getElementById('cap-input').value = currentVal;
    document.getElementById('capViewMode').style.display = 'none';
    document.getElementById('capEditMode').style.display = 'block';
}

function cancelEditCap() {
    document.getElementById('capEditMode').style.display = 'none';
    document.getElementById('capViewMode').style.display = 'block';
}

async function saveCapVal() {
    await saveKapasitas();
}

function closeM(id) {
    if(id === 'cap-modal') {
        var updatedVal = document.getElementById('cap-number').textContent;
        document.getElementById('capDisplay').textContent = updatedVal;
        cancelEditCap();
    }
}
function openM(id) {} 

// Simpan Kapasitas (UTUH PUNYA KAMU, Selector Disesuaikan Otomatis)
async function saveKapasitas() {
    var val = parseInt(document.getElementById('cap-input').value);
    var slotId = window.currentSlotId;
    if (!val || val < 1) { toast('Kapasitas tidak valid!', 'err'); return; }
    if (!slotId) { toast('Slot ID tidak ditemukan!', 'err'); return; }

    var btn = document.querySelector('#capEditMode .btn-orange'); 
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>...';
    }

    try {
        var res = await fetch('/dashboard/capacity', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF_TOKEN,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ capacity: val, slot_id: slotId }),
        });
        var data = await res.json();
        if (data.success) {
            document.getElementById('cap-number').textContent = val;
            if (document.getElementById('d-capacity')) document.getElementById('d-capacity').textContent = val;
            closeM('cap-modal');
            toast('✅ Kapasitas disimpan!', 'ok');
        } else {
            toast('Gagal: ' + (data.message || 'Error'), 'err');
        }
    } catch(e) {
        toast('Gagal menghubungi server!', 'err');
    } finally {
        if (btn) { btn.disabled = false; btn.innerHTML = 'Simpan'; }
    }
}

// Refresh Stats
async function refreshLiveStats() {
    try {
        var res = await fetch(LIVE_URL);
        var data = await res.json();
        if (data.count !== undefined) document.getElementById('d-active').textContent = data.count;
        if (data.booking_today !== undefined) document.getElementById('d-booking').textContent = data.booking_today;
        if (data.pending !== undefined) document.getElementById('d-pending').textContent = data.pending;
    } catch(e) {}
}
setInterval(refreshLiveStats, 30000);
</script>
@endpush