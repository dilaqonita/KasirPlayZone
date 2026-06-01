{{-- Modal: Edit Kapasitas Harian --}}
<div class="modal-overlay" id="cap-modal">
    <div class="modal">
        <div class="modal-hd">
            <span class="modal-title">🎯 Edit Kapasitas Harian</span>
            <button class="modal-x" onclick="closeM('cap-modal')">✕</button>
        </div>
        <div class="modal-bd">
            <p class="modal-desc">Tentukan maksimum jumlah pengunjung yang bisa masuk per jam</p>
            <label class="fg-label">Kapasitas Harian <span class="req">*</span></label>
            <div class="cap-input-wrap">
                <input type="number" class="cap-input" id="cap-input" min="1"
                       value="{{ $dailyCapacity ?? 100 }}">
            </div>
            <div class="cap-unit-text">orang / jam</div>
        </div>
        <div class="modal-ft">
            <button class="btn btn-ghost" onclick="closeM('cap-modal')">Batal</button>
            <button class="btn btn-orange" onclick="saveKapasitas()">
                <i class="fas fa-save"></i> Simpan
            </button>
        </div>
    </div>
</div>

{{-- Modal: Konfirmasi Check-Out --}}
<div class="modal-overlay" id="checkout-modal">
    <div class="modal">
        <div class="modal-hd">
            <span class="modal-title">🟫 Konfirmasi Check-Out</span>
            <button class="modal-x" onclick="closeM('checkout-modal')">✕</button>
        </div>
        <div class="modal-bd">
            <div class="co-info-row">
                <span class="co-info-lbl">Nama</span>
                <span class="co-info-val" id="co-nama">–</span>
            </div>
            <div class="co-info-row">
                <span class="co-info-lbl">Paket</span>
                <span class="co-info-val" id="co-paket">–</span>
            </div>
            <div class="co-info-row">
                <span class="co-info-lbl">Check-in</span>
                <span class="co-info-val" id="co-checkin">–</span>
            </div>
            <div class="co-notice">
                <i class="fas fa-info-circle"></i>
                Durasi dihitung otomatis dari waktu check-in hingga sekarang.
            </div>
        </div>
        <div class="modal-ft">
            <button class="btn btn-ghost" onclick="closeM('checkout-modal')">Batal</button>
            <button class="btn btn-orange" id="co-confirm-btn">
                <i class="fas fa-sign-out-alt"></i> Checkout Sekarang
            </button>
        </div>
    </div>
</div>

{{-- Modal: Konfirmasi Refund / Batalkan Transaksi --}}
<div class="modal-overlay" id="refund-modal">
    <div class="modal">
        <div class="modal-hd">
            <span class="modal-title">❌ Batalkan Transaksi</span>
            <button class="modal-x" onclick="closeM('refund-modal')">✕</button>
        </div>
        <div class="modal-bd">
            <div class="co-info-row">
                <span class="co-info-lbl">Nama</span>
                <span class="co-info-val" id="refund-nama">–</span>
            </div>
            <div class="co-info-row">
                <span class="co-info-lbl">Kode Tiket</span>
                <span class="co-info-val" id="refund-tiket">–</span>
            </div>
            <div class="co-notice" style="background:#fee2e2;border-color:#fecaca;">
                <i class="fas fa-exclamation-triangle" style="color:#dc2626;"></i>
                <span style="color:#dc2626;">Transaksi yang dibatalkan tidak bisa dikembalikan. Kuota slot akan dikembalikan otomatis.</span>
            </div>
        </div>
        <div class="modal-ft">
            <button class="btn btn-ghost" onclick="closeM('refund-modal')">Kembali</button>
            <button class="btn btn-sm" id="refund-confirm-btn"
                    style="background:#dc2626;color:#fff;padding:8px 16px;border-radius:8px;border:none;cursor:pointer;font-family:inherit;font-weight:700;"
                    onclick="confirmRefund()">
                <i class="fas fa-times-circle"></i> Ya, Batalkan
            </button>
        </div>
    </div>
</div>

@stack('modals')