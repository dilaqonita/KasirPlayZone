@extends('layouts.app')

@section('page-title', 'QR Scanner')
@section('page-sub', 'Scan tiket untuk check-in / check-out pengunjung')

@section('content')

<style>
.scanner-wrap {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    align-items: start;
}
.cam-container {
    width: 100%;
    aspect-ratio: 4/3;
    background: #111;
    border-radius: 16px;
    position: relative;
    overflow: hidden;
}
#cam-placeholder {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 12px;
    color: rgba(255,255,255,.5);
    z-index: 2;
}
#cam-placeholder i { font-size: 2.5rem; }
#cam-placeholder p { font-size: .82rem; font-weight: 600; text-align: center; line-height: 1.5; }
#qr-reader {
    width: 100% !important;
    height: 100% !important;
    position: absolute;
    inset: 0;
    display: none;
    border-radius: 16px;
    overflow: hidden;
}
#qr-reader video {
    width: 100% !important;
    height: 100% !important;
    object-fit: cover !important;
}
#qr-reader img { display: none !important; }
#cam-error {
    display: none;
    margin-top: 10px;
    padding: 10px 14px;
    background: #fee2e2;
    border-radius: 10px;
    font-size: .8rem;
    font-weight: 700;
    color: #dc2626;
    align-items: center;
    gap: 8px;
}
/* Durasi log — elemen yang akan diupdate JS tiap detik */
.log-dur { font-size:.68rem; color:var(--text3); }
</style>

<div class="scanner-wrap">

    {{-- LEFT: Kamera --}}
    <div>
        <div class="card">
            <div class="card-hd">
                <span class="card-title">Scan QR Tiket</span>
            </div>
            <div class="card-bd">
                <div class="cam-container">
                    <div id="cam-placeholder">
                        <i class="fas fa-camera"></i>
                        <p>Klik "Mulai Scan"<br>untuk mengaktifkan kamera</p>
                    </div>
                    <div id="qr-reader"></div>
                </div>

                <div id="cam-error" style="display:none;">
                    <i class="fas fa-exclamation-circle"></i>
                    <span id="cam-error-msg"></span>
                </div>

                <button class="btn btn-orange btn-block btn-lg"
                        style="margin-top:14px;"
                        onclick="toggleCamera()"
                        id="cam-btn">
                    <i class="fas fa-camera"></i> Mulai Scan
                </button>

                <div style="text-align:center;margin:14px 0;font-size:.72rem;color:var(--text3);font-weight:700;text-transform:uppercase;letter-spacing:.06em;">
                    ATAU MASUKKAN MANUAL
                </div>

                <div style="display:flex;gap:8px;">
                    <input type="text" class="input" id="scan-input"
                           placeholder="Masukkan kode tiket" style="flex:1;"
                           onkeydown="if(event.key==='Enter') doManualValidate()">
                    <button class="btn btn-orange" onclick="doManualValidate()">Cek</button>
                </div>
            </div>
        </div>
    </div>

    {{-- RIGHT: Hasil + Log --}}
    <div style="display:flex;flex-direction:column;gap:16px;">
        <div class="card">
            <div class="card-hd"><span class="card-title">Hasil Validasi</span></div>
            <div class="card-bd" id="result-panel">
                <div style="text-align:center;padding:30px 20px;color:var(--text3);">
                    <div style="font-size:2.5rem;margin-bottom:10px;">🔍</div>
                    <div style="font-size:.85rem;font-weight:800;color:var(--text2);margin-bottom:4px;">Scan atau masukkan kode tiket</div>
                    <div style="font-size:.75rem;font-weight:600;">Hasil validasi akan muncul di sini</div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-hd"><span class="card-title">Log Aktivitas Hari Ini</span></div>
            <div id="log-list">
                @forelse($activityLogs ?? [] as $log)
                    @if(!empty($log->customer_name) && strtolower($log->customer_name) !== 'guest')
                    @php
                        // Kirim sebagai unix timestamp ms → JS hitung realtime
                        $logTs = \Carbon\Carbon::parse($log->created_at)
                                    ->setTimezone('Asia/Jakarta')
                                    ->timestamp * 1000;
                    @endphp
                    <div class="log-item">
                        <div class="vis-avatar {{ ['a','k','s','g','r','d'][$loop->index % 6] }}"
                             style="width:30px;height:30px;font-size:.75rem;flex-shrink:0;">
                            {{ strtoupper(substr($log->customer_name ?? '?', 0, 1)) }}
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div style="font-size:.8rem;font-weight:800;color:var(--text);">
                                {{ $log->customer_name ?? '-' }}
                            </div>
                            <div style="font-size:.68rem;color:var(--text3);">
                                {{ $log->package_name ?? $log->paket ?? '-' }}
                            </div>
                        </div>
                        <div style="text-align:right;margin-right:8px;">
                            {{-- Jam tetap dari PHP (tidak berubah) --}}
                            <div style="font-size:.78rem;font-weight:800;">
                                {{ \Carbon\Carbon::parse($log->created_at)->setTimezone('Asia/Jakarta')->format('H:i') }}
                            </div>
                            {{-- Durasi: data-ts → dihitung JS tiap detik, bukan PHP --}}
                            <div class="log-dur" data-ts="{{ $logTs }}">–</div>
                        </div>
                        <span class="log-action {{ $log->type === 'checkin' ? 'in' : 'out' }}">
                            {{ $log->type === 'checkin' ? 'Check-In' : 'Checkout' }}
                        </span>
                    </div>
                    @endif
                @empty
                <div style="text-align:center;padding:30px;color:var(--text3);">
                    <i class="fas fa-list-alt" style="font-size:1.8rem;margin-bottom:8px;display:block;opacity:.3;"></i>
                    <span style="font-size:.78rem;font-weight:700;">Aktivitas akan muncul di sini</span>
                </div>
                @endforelse
            </div>
        </div>
    </div>

</div>

@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"></script>
<script>
const VALIDATE_URL = "{{ route('scanner.validate') }}";
const CHECKIN_URL  = "{{ route('scanner.checkin') }}";
const CHECKOUT_URL = "{{ route('scanner.checkout') }}";
const CSRF_TOKEN   = "{{ csrf_token() }}";

// Tidak perlu deklarasi ulang camOn & scanCooldown
// karena funpark.js sudah deklarasi: var camOn = false; var scanCooldown = false;

/* ═══════════════════════════════════════════════
   REALTIME DURASI LOG AKTIVITAS
   Pakai data-ts (unix ms) → bebas timezone, realtime
   ═══════════════════════════════════════════════ */
function updateLogDurations() {
    var now = Date.now();
    document.querySelectorAll('.log-dur[data-ts]').forEach(function(el) {
        var ts   = parseInt(el.getAttribute('data-ts'), 10);
        if (!ts || isNaN(ts)) return;
        var diff = Math.floor((now - ts) / 1000);
        if (diff < 0) { el.textContent = 'baru saja'; return; }
        var h = Math.floor(diff / 3600);
        var m = Math.floor((diff % 3600) / 60);
        var s = diff % 60;
        if (h > 0)      el.textContent = h + 'j ' + m + 'm lalu';
        else if (m > 0) el.textContent = m + 'm ' + s + 's lalu';
        else            el.textContent = s + 's lalu';
    });
}
// Jalankan sekali langsung supaya tidak tampil "–" saat load
updateLogDurations();
// Update tiap detik → realtime, tidak berubah saat pindah halaman
setInterval(updateLogDurations, 1000);

/* ═══════════════════════════════════════════════
   KAMERA
   ═══════════════════════════════════════════════ */
var html5QrCode = null;

async function toggleCamera() {
    camOn ? await stopCamera() : await startCamera();
}

async function startCamera() {
    const btn         = document.getElementById("cam-btn");
    const placeholder = document.getElementById("cam-placeholder");
    const reader      = document.getElementById("qr-reader");
    const errBox      = document.getElementById("cam-error");

    if (errBox) errBox.style.display = "none";
    if (btn) { btn.innerHTML = '<i class="fas fa-spinner spin"></i> Memulai...'; btn.disabled = true; }

    try {
        html5QrCode = new Html5Qrcode("qr-reader");
        const devices = await Html5Qrcode.getCameras();
        if (!devices || devices.length === 0) {
            showCamError("Tidak ada kamera yang ditemukan di perangkat ini.");
            resetCamBtn(); return;
        }

        const camera = devices.find(d => /back|rear|environment/i.test(d.label)) || devices[0];

        await html5QrCode.start(
            camera.id,
            { fps: 30, qrbox: { width: 300, height: 300 }, showTorchButtonIfSupported: true },
            function(decodedText) {
                if (scanCooldown) return;
                scanCooldown = true;
                const inp = document.getElementById("scan-input");
                if (inp) inp.value = decodedText;
                playBeep();
                toast("📷 QR terbaca: " + decodedText, "info");
                serverValidate(decodedText);
                setTimeout(() => { scanCooldown = false; }, 3000);
            },
            function() {}
        );

        if (placeholder) placeholder.style.display = "none";
        if (reader)      reader.style.display      = "block";
        if (errBox)      errBox.style.display      = "none";
        camOn = true;

        if (btn) {
            btn.innerHTML        = '<i class="fas fa-stop"></i> Stop Kamera';
            btn.style.background = "#EF4444";
            btn.disabled         = false;
        }
        toast("📷 Kamera aktif – arahkan ke QR tiket", "info");

    } catch(err) {
        if (camOn) return;
        let msg = "Tidak dapat mengakses kamera.";
        if (err.toString().includes("NotAllowed") || err.toString().includes("Permission"))
            msg = "Izin kamera ditolak. Klik ikon kunci di address bar lalu izinkan kamera.";
        if (err.toString().includes("NotFound"))
            msg = "Kamera tidak ditemukan di perangkat ini.";
        showCamError(msg);
        resetCamBtn();
    }
}

async function stopCamera() {
    try { if (html5QrCode) await html5QrCode.stop(); } catch(e) {}
    camOn = false;
    const placeholder = document.getElementById("cam-placeholder");
    const reader      = document.getElementById("qr-reader");
    const btn         = document.getElementById("cam-btn");
    if (placeholder) placeholder.style.display = "flex";
    if (reader)      reader.style.display      = "none";
    if (btn) {
        btn.innerHTML        = '<i class="fas fa-camera"></i> Mulai Scan';
        btn.style.background = "";
        btn.disabled         = false;
    }
}

function showCamError(msg) {
    const errBox = document.getElementById("cam-error");
    const errMsg = document.getElementById("cam-error-msg");
    if (errMsg) errMsg.textContent = msg;
    if (errBox) errBox.style.display = "flex";
}

function resetCamBtn() {
    const btn = document.getElementById("cam-btn");
    if (btn) { btn.innerHTML = '<i class="fas fa-camera"></i> Mulai Scan'; btn.disabled = false; }
}

function playBeep() {
    try {
        const ctx  = new (window.AudioContext || window.webkitAudioContext)();
        const osc  = ctx.createOscillator();
        const gain = ctx.createGain();
        osc.connect(gain); gain.connect(ctx.destination);
        osc.frequency.value = 880;
        gain.gain.setValueAtTime(0.3, ctx.currentTime);
        gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.3);
        osc.start(ctx.currentTime); osc.stop(ctx.currentTime + 0.3);
    } catch(e) {}
}

function doManualValidate() {
    const val = document.getElementById("scan-input").value.trim();
    if (!val) return;
    serverValidate(val);
}

async function serverValidate(code) {
    showResultLoading();
    try {
        const res  = await fetch(VALIDATE_URL, {
            method:  "POST",
            headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": CSRF_TOKEN },
            body:    JSON.stringify({ code }),
        });
        const data = await res.json();
        showResult(data);
    } catch(e) {
        showResult({ type: "invalid", title: "❌ Gagal", sub: "Tidak dapat menghubungi server", cls: "invalid", actions: ["reset"] });
    }
}

function showResultLoading() {
    const panel = document.getElementById("result-panel");
    if (panel) panel.innerHTML = `
        <div style="text-align:center;padding:40px 20px;color:var(--text3);">
            <i class="fas fa-spinner fa-spin" style="font-size:2rem;margin-bottom:12px;display:block;"></i>
            <div style="font-size:.85rem;font-weight:700;">Memvalidasi tiket...</div>
        </div>`;
}

function showResult(d) {
    const panel = document.getElementById("result-panel");
    if (!panel) return;

    const rowsHtml = (d.rows || []).map(r => {
        const label = Array.isArray(r) ? r[0] : (r.label || '');
        const value = Array.isArray(r) ? r[1] : (r.value || '');
        return `<tr>
            <td style="color:var(--text3);font-size:.78rem;padding:6px 0;width:40%">${label}</td>
            <td style="font-size:.82rem;font-weight:700;padding:6px 0;">${value}</td>
        </tr>`;
    }).join("");

    const actionBtns = (d.actions || []).map(a => {
        if (a === "checkin")
            return `<button class="btn btn-green btn-sm"
                onclick="doCheckin('${d.transaction_id || ''}','${d.customer || ''}','${d.paket || ''}')">
                <i class="fas fa-sign-in-alt"></i> Check-in Sekarang</button>`;
        if (a === "checkout")
            return `<button class="btn btn-orange btn-sm" onclick="doCheckout('${d.transaction_id || ''}')">
                <i class="fas fa-sign-out-alt"></i> Check-Out</button>`;
        if (a === "reset")
            return `<button class="btn btn-ghost btn-sm" onclick="resetResultPanel()">
                <i class="fas fa-redo"></i> Scan Lagi</button>`;
        return "";
    }).join("");

    const colors = {
        valid:   { bg: "#e6f7f2", border: "#3bb88a", text: "#0f5132" },
        invalid: { bg: "#fee2e2", border: "#e85454", text: "#dc2626" },
        warn:    { bg: "#fef9e7", border: "#f59e0b", text: "#92400e" },
        info:    { bg: "#eaf3fb", border: "#4a90d9", text: "#1e40af" },
        success: { bg: "#e6f7f2", border: "#3bb88a", text: "#0f5132" },
    };
    const c = colors[d.cls || d.type] || colors.info;

    const qrSection = d.ticket_code
        ? `<div style="text-align:center;margin:12px 0 4px;">
                <div style="font-size:.7rem;font-weight:700;color:var(--text3);margin-bottom:6px;text-transform:uppercase;letter-spacing:.05em;">QR Tiket</div>
                <div style="display:inline-block;background:#fff;border-radius:10px;padding:6px;border:1px solid #f0f0f0;">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${encodeURIComponent(d.ticket_code)}"
                         style="width:110px;height:110px;display:block;border-radius:6px;">
                </div>
                <div style="font-size:.72rem;font-weight:800;color:var(--text2);margin-top:6px;letter-spacing:.08em;">${d.ticket_code}</div>
           </div>`
        : '';

    panel.innerHTML = `
        <div style="border:1.5px solid ${c.border};border-radius:12px;overflow:hidden;">
            <div style="background:${c.bg};padding:14px 16px;border-bottom:1px solid ${c.border};">
                <div style="font-size:1rem;font-weight:800;color:${c.text};">${d.title || ''}</div>
                <div style="font-size:.78rem;color:${c.text};opacity:.8;margin-top:2px;">${d.sub || ''}</div>
            </div>
            <div style="padding:14px 16px;">
                ${qrSection}
                <table style="width:100%;border-collapse:collapse;">${rowsHtml}</table>
                <div style="display:flex;gap:8px;flex-wrap:wrap;margin-top:14px;">${actionBtns}</div>
            </div>
        </div>`;
}

async function doCheckin(id, customer, paket) {
    if (!id) { alert("ID tidak ditemukan"); return; }
    try {
        const formData = new FormData();
        formData.append("transaction_id", id);
        formData.append("customer", customer);
        formData.append("paket", paket);
        const res  = await fetch(CHECKIN_URL, {
            method:  "POST",
            headers: { "X-CSRF-TOKEN": CSRF_TOKEN },
            body:    formData,
        });
        const data = await res.json();
        if (data.success) {
            toast("✅ Check-in berhasil!", "ok");
            addLog("in", customer);
            resetResultPanel();
        } else {
            alert(data.message || "Check-in gagal");
        }
    } catch(e) {
        alert("Gagal menghubungi server");
        console.error(e);
    }
}

async function doCheckout(id) {
    if (!id || id === "0") { toast("ID transaksi tidak ditemukan", "err"); return; }
    try {
        const res  = await fetch(CHECKOUT_URL, {
            method:  "POST",
            headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": CSRF_TOKEN },
            body:    JSON.stringify({ transaction_id: String(id) }),
        });
        const data = await res.json();
        if (data.success) {
            toast("✅ Checkout berhasil!", "ok");
            addLog("out", data.customer_name || "Pengunjung");
            resetResultPanel();
        } else {
            toast(data.message || "Gagal check-out", "err");
        }
    } catch(e) {
        toast("Gagal menghubungi server", "err");
    }
}

function resetResultPanel() {
    const p = document.getElementById("result-panel");
    if (p) p.innerHTML = `
        <div style="text-align:center;padding:40px 20px;color:var(--text3);">
            <div style="font-size:3rem;margin-bottom:12px;">🔍</div>
            <div style="font-size:.85rem;font-weight:800;color:var(--text2);margin-bottom:4px;">Scan atau masukkan kode tiket</div>
            <div style="font-size:.75rem;font-weight:600;">Hasil validasi akan muncul di sini</div>
        </div>`;
    const i = document.getElementById("scan-input");
    if (i) i.value = "";
}

function addLog(type, name) {
    if (!name || name.toLowerCase() === 'guest') return;

    const el = document.getElementById("log-list");
    if (!el) return;
    const empty = el.querySelector('[style*="text-align:center"]');
    if (empty) empty.remove();

    const nowMs = Date.now();
    const t     = new Date().toTimeString().slice(0, 5);
    const colors = ["a","k","s","g","r","d"];
    const cls    = colors[Math.floor(Math.random() * colors.length)];
    const actionCls   = type === "in" ? "in" : "out";
    const actionLabel = type === "in" ? "Check-In" : "Checkout";

    // data-ts diisi timestamp sekarang → langsung ikut dihitung updateLogDurations()
    el.insertAdjacentHTML("afterbegin", `
        <div class="log-item">
            <div class="vis-avatar ${cls}" style="width:30px;height:30px;font-size:.75rem;flex-shrink:0;">
                ${name.charAt(0).toUpperCase()}
            </div>
            <div style="flex:1;min-width:0;">
                <div style="font-size:.8rem;font-weight:800;color:var(--text);">${name}</div>
                <div style="font-size:.68rem;color:var(--text3);">–</div>
            </div>
            <div style="text-align:right;margin-right:8px;">
                <div style="font-size:.78rem;font-weight:800;color:var(--text);">${t}</div>
                <div class="log-dur" data-ts="${nowMs}">baru saja</div>
            </div>
            <span class="log-action ${actionCls}">${actionLabel}</span>
        </div>`);
}

window.addEventListener("beforeunload", stopCamera);
</script>
@endpush