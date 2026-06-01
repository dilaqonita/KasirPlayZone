@extends('layouts.app')

@section('page-title', 'Pesan Langsung')
@section('page-sub', 'Buat booking langsung untuk pengunjung walk-in')

@push('styles')
<style>
@media print {
    body * { visibility: hidden; }
    #success-box, #success-box * { visibility: visible; }
    #success-box { position: fixed; top: 0; left: 0; width: 100%; margin: 0; }
    #success-box .btn { display: none !important; }
}

/* Slot yang sudah lewat / penuh */
.slot.penuh, .slot.past {
    opacity: 0.4 !important;
    cursor: not-allowed !important;
    pointer-events: none !important;
    background: #f5f5f5 !important;
}
</style>
@endpush

@section('content')
<div style="display:grid;grid-template-columns:1fr 340px;gap:20px;align-items:start;" class="walkin-grid">

    {{-- KIRI: FORM --}}
    <div class="card">
        <div class="card-hd">
            <span class="card-title">Form Booking Baru</span>
        </div>
        <div class="card-bd">
            <div id="walkin-alert" style="display:none;padding:10px;border-radius:5px;margin-bottom:15px;font-size:0.9rem;"></div>

            <div class="fg">
                <label>Nama Pengunjung</label>
                {{--
                    PENTING: value sengaja DIKOSONGKAN
                    Jangan isi value="{{ auth()->user()->name }}" dll
                    Nama harus diisi manual oleh kasir = nama PENGUNJUNG, bukan kasir
                --}}
                <input type="text"
                       class="input"
                       id="w-name"
                       placeholder="Masukan Nama Lengkap Pengunjung"
                       autocomplete="off"
                       oninput="calcSum()">
            </div>

            <div class="fg">
                <label>Nomor Telefon</label>
                <input type="tel" class="input" id="w-phone"
                       placeholder="Masukan Nomor Telefon Pengunjung"
                       autocomplete="off">
            </div>

            {{-- PAKET: MULTI-SELECT --}}
            <div class="fg">
                <label>Pilih Paket <span style="color:var(--red)">*</span>
                    <span style="font-size:0.8rem;color:#888;">(bisa pilih lebih dari satu)</span>
                </label>
                <div id="pkg-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(140px,1fr));gap:10px;">
                    @forelse($packages as $pkg)
                        @php
                            $pkg    = (array) $pkg;
                            $status = strtolower($pkg['status'] ?? 'aktif');
                        @endphp
                        @if($status === 'aktif' || $status === 'active')
                            <div class="pkg-card"
                                data-pkg='{{ json_encode($pkg) }}'
                                onclick='togglePkg(this, JSON.parse("{{ addslashes(json_encode($pkg)) }}"))'
                                <span class="pkg-emoji">{{ $pkg['ikon'] ?? '🎡' }}</span>
                                <div class="pkg-name">{{ $pkg['nama_package'] ?? $pkg['name'] }}</div>
                                <div class="pkg-price">Rp {{ number_format($pkg['harga'] ?? $pkg['price'] ?? 0, 0, ',', '.') }}</div>
                                <div class="pkg-qty" style="display:none;margin-top:8px;" onclick="event.stopPropagation()">
                                    <button type="button" onclick="adjPkgQty(this,-1)" style="padding:2px 8px;border:1px solid #ddd;border-radius:4px;background:#eee;">−</button>
                                    <span class="pkg-qty-val" style="margin:0 6px;">1</span>
                                    <button type="button" onclick="adjPkgQty(this,1)" style="padding:2px 8px;border:1px solid #ddd;border-radius:4px;background:#eee;">+</button>
                                </div>
                            </div>
                        @endif
                    @empty
                        <div style="grid-column:1/-1;text-align:center;color:red;padding:20px;">
                            Gagal memuat paket. Pastikan Ngrok temanmu aktif!
                        </div>
                    @endforelse
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;">
                <div class="fg" style="margin-bottom:0;">
                    <label>Tanggal Kunjungan <span style="color:var(--red)">*</span></label>
                    {{-- min = hari ini → tidak bisa pilih tanggal kemarin --}}
                    <input type="date" class="input" id="w-date"
                           value="{{ now()->format('Y-m-d') }}"
                           min="{{ now()->format('Y-m-d') }}"
                           onchange="calcSum(); updateSlotDisable()">
                </div>
                <div class="fg" style="margin-bottom:0;">
                    <label>Catatan</label>
                    <input type="text" class="input" id="w-notes" placeholder="Opsional...">
                </div>
            </div>

            <label style="margin-top:16px;display:block;">Pilih Jam <span style="color:var(--red)">*</span></label>
            <div class="slot-grid" id="slot-grid">
                @foreach($slots as $s)
                    @php
                        $s      = (array) $s;
                        $jam    = $s['waktu'] ?? '--:--';
                        $sisa   = $s['sisa_slot'] ?? 0;
                        $isFull = $s['is_full'] ?? ($sisa <= 0);
                    @endphp
                    <div class="slot {{ $isFull ? 'penuh' : '' }}"
                         data-slot-id="{{ $slot_id ?? '' }}"
                         data-time="{{ $jam }}"
                         data-full="{{ $isFull ? '1' : '0' }}"
                         onclick="{{ $isFull ? '' : 'selSlot(this)' }}"
                         style="padding:10px;border:1px solid #ddd;border-radius:8px;text-align:center;cursor:{{ $isFull ? 'not-allowed' : 'pointer' }};">
                        <div class="slot-time" style="font-weight:bold;">{{ $jam }}</div>
                        <div class="slot-avail" style="font-size:0.8rem;">{{ $sisa }} slot</div>
                    </div>
                @endforeach
            </div>

            <div class="fg" style="margin-top:16px;">
                <label>Pilih Metode Pembayaran <span style="color:var(--red)">*</span></label>
                <div class="pay-list">
                    <div class="pay-opt sel" onclick="selPay(this,'bank')">
                        <div class="pay-ico bank">🏦</div>
                        <div><div class="pay-name">Transfer Bank</div><div class="pay-sub">BCA / BNI / BRI</div></div>
                    </div>
                    <div class="pay-opt" onclick="selPay(this,'ewallet')">
                        <div class="pay-ico" style="background:#e8f5e9;">📱</div>
                        <div><div class="pay-name">Dompet Digital</div><div class="pay-sub">GoPay • OVO • DANA</div></div>
                    </div>
                    <div class="pay-opt" onclick="selPay(this,'minimarket')">
                        <div class="pay-ico" style="background:#fff3e0;">🏪</div>
                        <div><div class="pay-name">Minimarket</div><div class="pay-sub">Alfamart • Indomaret</div></div>
                    </div>
                    <div class="pay-opt" onclick="selPay(this,'cash')">
                        <div class="pay-ico cash">💵</div>
                        <div><div class="pay-name">Cash / Tunai</div><div class="pay-sub">Bayar langsung di kasir</div></div>
                    </div>
                </div>
            </div>

            <button type="button" class="btn btn-orange btn-lg btn-block"
                    style="margin-top:16px;" onclick="submitWalkin()" id="walkin-btn">
                <i class="fas fa-ticket-alt"></i> Buat Booking
            </button>
        </div>
    </div>

    {{-- KANAN: RINGKASAN --}}
    <div>
        <div class="summary-card">
            <div class="card-hd"><span class="card-title">📝 Ringkasan Pesanan</span></div>
            <div id="sum-pkg-list" style="margin-bottom:10px;">
                <div style="color:#aaa;font-size:0.9rem;">Belum ada paket dipilih</div>
            </div>
            <div class="summary-rows">
                <div class="sum-row"><span class="l">Customer</span><span class="v" id="sum-name">–</span></div>
                <div class="sum-row"><span class="l">Tanggal</span><span class="v" id="sum-date">–</span></div>
                <div class="sum-row"><span class="l">Jam</span><span class="v" id="sum-jam">–</span></div>
                <div class="sum-row"><span class="l">Pembayaran</span><span class="v" id="sum-pay">Transfer Bank</span></div>
            </div>
            <div class="sum-total"><span>TOTAL</span><span class="v" id="sum-total">Rp 0</span></div>
        </div>

        <div id="success-box" style="display:none;margin-top:14px;">
            <div class="summary-card">
                <div class="success-box" style="text-align:center;padding:20px;">
                    <div style="font-size:3rem;">🎉</div>
                    <div style="font-weight:bold;font-size:1.2rem;">Booking Berhasil!</div>
                    <div style="background:#f9f9f9;padding:10px;margin-top:10px;border-radius:8px;">
                        <div style="font-size:0.7rem;color:#888;">KODE TIKET</div>
                        <div id="ticket-code-val" style="font-weight:bold;font-size:1rem;letter-spacing:1px;word-break:break-all;">–</div>
                    </div>
                    <div id="qr-container" style="margin:14px auto;display:flex;flex-wrap:wrap;gap:6px;justify-content:center;"></div>
                    <div id="sc-total" style="color:var(--orange);font-weight:bold;"></div>
                    <div style="display:flex;gap:8px;justify-content:center;margin-top:16px;">
                        <button class="btn btn-ghost btn-sm" onclick="printTicket()"><i class="fas fa-print"></i> Print</button>
                        <button class="btn btn-orange btn-sm" onclick="resetWalkin()"><i class="fas fa-plus"></i> Baru</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
var state = {
    selectedPkgs: [],
    selectedSlot: null,
    payMethod: 'bank',
};

/* =========================================================
   DISABLE SLOT JAM YANG SUDAH LEWAT
   Hanya berlaku jika tanggal yang dipilih = hari ini
   ========================================================= */
function updateSlotDisable() {
    var dateVal = document.getElementById('w-date').value;
    // Dapatkan tanggal hari ini dalam format Y-m-d lokal
    var now     = new Date();
    var todayStr = now.getFullYear() + '-' +
                   String(now.getMonth() + 1).padStart(2,'0') + '-' +
                   String(now.getDate()).padStart(2,'0');
    var isToday = (dateVal === todayStr);

    document.querySelectorAll('#slot-grid .slot').forEach(function(el) {
        var isFull  = el.getAttribute('data-full') === '1';
        var timeStr = el.getAttribute('data-time') || '';
        var parts   = timeStr.split(':');
        var slotH   = parseInt(parts[0] || 0, 10);
        var slotM   = parseInt(parts[1] || 0, 10);

        // Jam sudah lewat = jam slot < jam sekarang (jika hari ini)
        var isPast = isToday && (
            slotH < now.getHours() ||
            (slotH === now.getHours() && slotM <= now.getMinutes())
        );

        if (isPast || isFull) {
            el.classList.add('penuh');
            el.style.opacity        = '0.4';
            el.style.cursor         = 'not-allowed';
            el.style.pointerEvents  = 'none';
            // Jika slot ini sedang dipilih, reset
            if (state.selectedSlot && state.selectedSlot.time === timeStr) {
                state.selectedSlot = null;
                document.getElementById('sum-jam').textContent = '–';
            }
        } else {
            // Kembalikan jika bukan full dari server
            if (!isFull) {
                el.classList.remove('penuh');
                el.style.opacity       = '1';
                el.style.cursor        = 'pointer';
                el.style.pointerEvents = 'auto';
                el.onclick = function() { selSlot(this); };
            }
        }
    });
}

// Jalankan saat load
updateSlotDisable();
// Update tiap menit supaya slot yang baru lewat otomatis ter-disable
setInterval(updateSlotDisable, 60000);

/* =========================================================
   PACKAGE MULTI-SELECT
   ========================================================= */
function togglePkg(el, data) {
    var key = data.id || data.nama_package;
    var idx = state.selectedPkgs.findIndex(function(p) {
        return (p.pkg.id || p.pkg.nama_package) === key;
    });

    if (idx === -1) {
        state.selectedPkgs.push({ pkg: data, qty: 1 });
        el.classList.add('sel');
        el.querySelector('.pkg-qty').style.display = 'block';
    } else {
        state.selectedPkgs.splice(idx, 1);
        el.classList.remove('sel');
        el.querySelector('.pkg-qty').style.display = 'none';
        el.querySelector('.pkg-qty-val').textContent = '1';
    }
    calcSum();
}

function adjPkgQty(btn, delta) {
    var card    = btn.closest('.pkg-card');
    var qtyEl   = card.querySelector('.pkg-qty-val');
    var pkgData = JSON.parse(card.dataset.pkg);
    var key     = pkgData.id || pkgData.nama_package;
    var entry   = state.selectedPkgs.find(function(p) {
        return (p.pkg.id || p.pkg.nama_package) === key;
    });
    if (!entry) return;
    entry.qty = Math.max(1, entry.qty + delta);
    qtyEl.textContent = entry.qty;
    calcSum();
}

/* =========================================================
   SLOT SELECTION
   ========================================================= */
function selSlot(el) {
    state.selectedSlot = { id: el.dataset.slotId, time: el.dataset.time };
    document.querySelectorAll('.slot').forEach(function(s) { s.classList.remove('sel'); });
    el.classList.add('sel');
    document.getElementById('sum-jam').textContent = el.dataset.time;
}

/* =========================================================
   PAYMENT METHOD
   ========================================================= */
function selPay(el, method) {
    state.payMethod = method;
    document.querySelectorAll('.pay-opt').forEach(function(p) { p.classList.remove('sel'); });
    el.classList.add('sel');
    var names = { bank:'Transfer Bank', ewallet:'Dompet Digital', minimarket:'Minimarket', cash:'Cash / Tunai' };
    document.getElementById('sum-pay').textContent = names[method] || method;
}

/* =========================================================
   CALC SUMMARY
   ========================================================= */
function calcSum() {
    var total  = 0;
    var listEl = document.getElementById('sum-pkg-list');
    listEl.innerHTML = '';

    if (state.selectedPkgs.length === 0) {
        listEl.innerHTML = '<div style="color:#aaa;font-size:0.9rem;">Belum ada paket dipilih</div>';
    } else {
        state.selectedPkgs.forEach(function(item) {
            var pkg = item.pkg; var qty = item.qty;
            var harga = pkg.harga || pkg.price || 0;
            var sub   = harga * qty;
            total += sub;
            var row = document.createElement('div');
            row.className = 'summary-pkg';
            row.style.cssText = 'display:flex;align-items:center;gap:10px;margin-bottom:8px;';
            row.innerHTML = '<span style="font-size:1.5rem;">' + (pkg.ikon || '🎫') + '</span>' +
                '<div style="flex:1;">' +
                '<div style="font-weight:600;font-size:0.9rem;">' + (pkg.nama_package || pkg.name) + '</div>' +
                '<div style="font-size:0.8rem;color:#888;">x' + qty + ' = Rp ' + sub.toLocaleString('id-ID') + '</div>' +
                '</div>';
            listEl.appendChild(row);
        });
    }

    document.getElementById('sum-total').textContent = 'Rp ' + total.toLocaleString('id-ID');
    // Nama = dari input form (bukan auth user)
    document.getElementById('sum-name').textContent = document.getElementById('w-name').value || '–';
    document.getElementById('sum-date').textContent = document.getElementById('w-date').value || '–';
}

/* =========================================================
   ALERT
   ========================================================= */
function showAlert(msg, type) {
    var box = document.getElementById('walkin-alert');
    box.style.display    = 'block';
    box.textContent      = msg;
    box.style.background = type === 'error' ? '#fee2e2' : '#dcfce7';
    box.style.color      = type === 'error' ? '#991b1b' : '#166534';
    setTimeout(function() { box.style.display = 'none'; }, 5000);
}

/* =========================================================
   SUBMIT
   ========================================================= */
async function submitWalkin() {
    // Ambil nama dari INPUT FORM — bukan dari auth
    var name = document.getElementById('w-name').value.trim();

    if (!name) {
        showAlert('Nama pengunjung harus diisi!', 'error');
        document.getElementById('w-name').focus();
        return;
    }
    if (state.selectedPkgs.length === 0) {
        showAlert('Pilih minimal 1 paket!', 'error');
        return;
    }
    if (!state.selectedSlot) {
        showAlert('Pilih jam kunjungan!', 'error');
        return;
    }

    var btn = document.getElementById('walkin-btn');
    btn.disabled   = true;
    btn.innerHTML  = '<i class="fas fa-spinner fa-spin"></i> Memproses...';

    var totalHarga = 0;
    var details    = state.selectedPkgs.map(function(item) {
        var harga    = item.pkg.harga || item.pkg.price || 0;
        var subtotal = harga * item.qty;
        totalHarga  += subtotal;
        return {
            nama_paket:    item.pkg.nama_package || item.pkg.name,
            harga:         harga,
            jumlah:        item.qty,
            subtotal:      subtotal,
            jam_kunjungan: state.selectedSlot.time,
        };
    });

    var payload = {
        slot_id:           state.selectedSlot.id,
        nama_customer:     name,           // ← NAMA DARI INPUT FORM
        telepon:           document.getElementById('w-phone').value.trim(),
        visit_date:        document.getElementById('w-date').value,
        metode_pembayaran: state.payMethod,
        total_harga:       totalHarga,
        details:           details,
    };

    try {
        var res    = await fetch('/walk-in', {
            method:  'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: JSON.stringify(payload),
        });
        var result = await res.json();

        if (result.success) {
            var qrContainer = document.getElementById('qr-container');
            qrContainer.innerHTML = '';
            (result.qr_urls || []).forEach(function(url) {
                var img = document.createElement('img');
                img.src = url;
                img.style.cssText = 'width:120px;height:120px;border:1px solid #eee;border-radius:6px;';
                qrContainer.appendChild(img);
            });
            document.getElementById('ticket-code-val').textContent = (result.ticket_codes || []).join(', ');
            document.getElementById('sc-total').textContent = 'Total: Rp ' + totalHarga.toLocaleString('id-ID');
            document.getElementById('success-box').style.display = 'block';
            document.getElementById('success-box').scrollIntoView({ behavior: 'smooth' });
            showAlert('Booking Berhasil!', 'success');
        } else {
            showAlert(result.message || 'Terjadi kesalahan', 'error');
        }
    } catch(e) {
        console.error(e);
        showAlert('Koneksi Error: ' + e.message, 'error');
    } finally {
        btn.disabled  = false;
        btn.innerHTML = '<i class="fas fa-ticket-alt"></i> Buat Booking';
    }
}

/* =========================================================
   RESET FORM
   ========================================================= */
function resetWalkin() {
    state = { selectedPkgs: [], selectedSlot: null, payMethod: 'bank' };
    document.querySelectorAll('.pkg-card').forEach(function(c) {
        c.classList.remove('sel');
        c.querySelector('.pkg-qty').style.display = 'none';
        c.querySelector('.pkg-qty-val').textContent = '1';
    });
    document.querySelectorAll('.slot').forEach(function(s) { s.classList.remove('sel'); });
    document.querySelectorAll('.pay-opt').forEach(function(p) { p.classList.remove('sel'); });
    var firstPay = document.querySelector('.pay-opt');
    if (firstPay) firstPay.classList.add('sel');
    document.getElementById('w-name').value  = '';
    document.getElementById('w-phone').value = '';
    document.getElementById('w-notes').value = '';
    document.getElementById('success-box').style.display = 'none';
    calcSum();
    updateSlotDisable();
}

/* =========================================================
   PRINT TIKET
   ========================================================= */
function printTicket() {
    var isi    = document.getElementById('success-box').innerHTML;
    var iframe = document.getElementById('print-frame');
    if (!iframe) {
        iframe = document.createElement('iframe');
        iframe.id = 'print-frame';
        iframe.style.cssText = 'position:fixed;top:-9999px;left:-9999px;width:0;height:0;border:0;';
        document.body.appendChild(iframe);
    }
    iframe.contentDocument.open();
    iframe.contentDocument.write(
        '<html><head><title>Tiket Booking</title>' +
        '<style>body{font-family:sans-serif;text-align:center;padding:20px;}' +
        'img{width:150px;height:150px;margin:6px;}.btn{display:none!important;}</style>' +
        '</head><body>' + isi + '</body></html>'
    );
    iframe.contentDocument.close();
    setTimeout(function() {
        iframe.contentWindow.focus();
        iframe.contentWindow.print();
    }, 500);
}
</script>
@endpush