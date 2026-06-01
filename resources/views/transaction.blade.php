@extends('layouts.app')

@section('title', 'Transaction')
@section('page_title', 'Transaksi')
@section('page_subtitle', 'Cek seluruh riwayat transaksi disini')

@section('content')

<style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');

    * {
        font-family: 'Plus Jakarta Sans', sans-serif;
        box-sizing: border-box;
    }

    /* ── PAGE WRAPPER ── */
    .tx-wrap {
        padding: 0;
        background: transparent;
    }

    /* ── TOOLBAR ── */
    .toolbar {
        display: flex;
        gap: 12px;
        align-items: center;
        margin-bottom: 20px;
        flex-wrap: wrap;
    }

    .toolbar form {
        width: 100%;
        display: flex;
        gap: 12px;
        align-items: center;
        flex-wrap: wrap;
    }

    /* Search box */
    .srch {
        flex: 1;
        min-width: 260px;
        height: 48px;
        background: #fff;
        border-radius: 14px;
        border: 1.5px solid #e8e2da;
        display: flex;
        align-items: center;
        padding: 0 16px;
        gap: 10px;
        box-shadow: 0 2px 8px rgba(0,0,0,.04);
        transition: border-color .2s, box-shadow .2s;
    }

    .srch:focus-within {
        border-color: #e07f40;
        box-shadow: 0 0 0 3px rgba(224,127,64,.12);
    }

    .srch svg {
        width: 16px;
        height: 16px;
        flex-shrink: 0;
        color: #b0a898;
    }

    .srch input {
        border: none;
        outline: none;
        width: 100%;
        background: transparent;
        font-size: 14px;
        color: #444;
        font-family: inherit;
        font-weight: 500;
    }

    .srch input::placeholder {
        color: #b0a898;
    }

    /* Filter selects & date */
    .fsel {
        height: 48px;
        min-width: 170px;
        padding: 0 36px 0 16px;
        border-radius: 14px;
        border: 1.5px solid #e8e2da;
        background: #fff url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23b0a898' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E") no-repeat calc(100% - 12px) center;
        appearance: none;
        -webkit-appearance: none;
        font-size: 14px;
        font-weight: 600;
        color: #555;
        font-family: inherit;
        outline: none;
        cursor: pointer;
        box-shadow: 0 2px 8px rgba(0,0,0,.04);
        transition: border-color .2s, box-shadow .2s;
    }

    .fsel:focus {
        border-color: #e07f40;
        box-shadow: 0 0 0 3px rgba(224,127,64,.12);
    }

    input[type="date"].fsel {
        background-image: none;
        color: #555;
        min-width: 160px;
        padding-right: 12px;
    }

    /* Styling icon kalender native biar lebih rapi */
    input[type="date"]::-webkit-calendar-picker-indicator {
        opacity: 0.5;
        cursor: pointer;
        filter: invert(60%) sepia(10%) saturate(300%) hue-rotate(10deg);
    }

    input[type="date"]::-webkit-calendar-picker-indicator:hover {
        opacity: 0.9;
    }

    /* ── CARD ── */
    .card {
        background: #fff;
        border-radius: 20px;
        padding: 28px;
        box-shadow: 0 2px 16px rgba(0,0,0,.06);
        overflow: hidden;
    }

    /* Card header */
    .ch {
        display: flex;
        align-items: center;
        gap: 14px;
        margin-bottom: 24px;
    }

    .ic-box {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        background: #f5efe5;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        flex-shrink: 0;
    }

    .ct {
        font-size: 17px;
        font-weight: 800;
        color: #333;
        line-height: 1.2;
    }

    .cs {
        color: #9e9487;
        margin-top: 3px;
        font-size: 13px;
        font-weight: 500;
    }

    /* ── TABLE ── */
    .tw {
        overflow-x: auto;
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    thead tr {
        background: #faf4ea;
    }

    th {
        padding: 13px 14px;
        text-align: left;
        color: #9e8f72;
        font-size: 13px;
        font-weight: 700;
        letter-spacing: .3px;
        white-space: nowrap;
    }

    th:first-child { border-radius: 10px 0 0 10px; }
    th:last-child  { border-radius: 0 10px 10px 0; }

    td {
        padding: 16px 14px;
        border-bottom: 1px solid #f3ede5;
        vertical-align: middle;
        color: #444;
        font-weight: 600;
        font-size: 14px;
    }

    tbody tr:last-child td {
        border-bottom: none;
    }

    tbody tr:hover {
        background: #fdfaf7;
    }

    /* User cell */
    .urow {
        display: flex;
        align-items: center;
        gap: 11px;
    }

    .uava {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        color: #fff;
        font-size: 14px;
        flex-shrink: 0;
    }

    /* Rotating avatar colors */
    .uava-0 { background: #7dc8c2; }
    .uava-1 { background: #e89a72; }
    .uava-2 { background: #8bb5e8; }
    .uava-3 { background: #b8a4e8; }
    .uava-4 { background: #e8c472; }
    .uava-5 { background: #7dc8a0; }

    .unm {
        font-size: 14px;
        font-weight: 700;
        color: #333;
        line-height: 1.3;
    }

    .uml {
        font-size: 12px;
        color: #aaa;
        margin-top: 1px;
        font-weight: 500;
    }

    /* Status badge */
    .badge {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 5px 16px;
        border-radius: 999px;
        font-size: 13px;
        font-weight: 700;
        background: #d9f0ec;
        color: #2f7a72;
        white-space: nowrap;
    }

    .badge.refund,
    .badge.refunded {
        background: #fde8e8;
        color: #b84444;
    }

    .badge.pending {
        background: #fef3d8;
        color: #b07c1a;
    }

    /* Action button */
    .adbtn {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        border: 1.5px solid #ede7df;
        background: #faf6f2;
        cursor: pointer;
        font-size: 16px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: background .15s, border-color .15s, transform .1s;
        color: #888;
    }

    .adbtn:hover {
        background: #f0e8df;
        border-color: #d9c9b8;
        transform: scale(1.08);
    }

    /* ── PAGINATION ── */
    .pg-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-top: 24px;
        gap: 16px;
        flex-wrap: wrap;
    }

    .pg-info {
        font-size: 13px;
        color: #9e9487;
        font-weight: 600;
    }

    .pg-wrap {
        display: flex;
        gap: 6px;
        align-items: center;
    }

    .pg-btn {
        min-width: 36px;
        height: 36px;
        border-radius: 10px;
        border: 1.5px solid #ede7df;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        text-decoration: none;
        color: #666;
        font-weight: 700;
        font-size: 13px;
        background: #fff;
        transition: background .15s, border-color .15s, color .15s;
        padding: 0 10px;
        cursor: pointer;
    }

    .pg-btn:hover:not(.pg-active):not(.pg-disabled) {
        background: #faf0e6;
        border-color: #e0c4a0;
        color: #c06a28;
    }

    .pg-active {
        background: #e88040 !important;
        border-color: #e88040 !important;
        color: #fff !important;
        box-shadow: 0 3px 8px rgba(232,128,64,.35);
    }

    .pg-disabled {
        opacity: .45;
        cursor: default;
        pointer-events: none;
    }

    .pg-ellipsis {
        color: #b0a898;
        font-weight: 700;
        font-size: 13px;
        padding: 0 4px;
    }

    /* ── MODALS ── */
    .mo {
        position: fixed;
        inset: 0;
        background: rgba(30,20,10,.4);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 9999;
        padding: 20px;
        backdrop-filter: blur(3px);
    }

    .modal {
        width: 100%;
        max-width: 520px;
        background: #fff;
        border-radius: 22px;
        padding: 28px;
        box-shadow: 0 24px 60px rgba(0,0,0,.18);
        animation: modalIn .25s ease;
    }

    @keyframes modalIn {
        from { opacity: 0; transform: translateY(14px) scale(.97); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
    }

    .mh {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
    }

    .mt {
        font-size: 18px;
        font-weight: 800;
        color: #333;
    }

    .mc {
        border: none;
        background: #f3ede7;
        width: 36px;
        height: 36px;
        border-radius: 10px;
        cursor: pointer;
        font-size: 15px;
        color: #888;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: background .15s;
    }

    .mc:hover {
        background: #e8ddd3;
        color: #555;
    }

    .btn {
        height: 46px;
        border-radius: 13px;
        border: none;
        padding: 0 20px;
        cursor: pointer;
        font-weight: 700;
        font-size: 14px;
        font-family: inherit;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: opacity .15s, transform .1s;
    }

    .btn:hover { opacity: .88; }
    .btn:active { transform: scale(.98); }

    .btn-dg {
        background: #e88040;
        color: #fff;
        box-shadow: 0 4px 14px rgba(232,128,64,.35);
    }

    .btn-ou {
        background: #f3ede7;
        color: #666;
    }

    /* Empty state */
    .empty-row td {
        text-align: center;
        padding: 56px 20px;
        color: #b0a898;
        font-weight: 600;
        font-size: 14px;
    }

    .empty-icon {
        font-size: 32px;
        display: block;
        margin-bottom: 10px;
    }
</style>

<div class="tx-wrap">

    {{-- ── TOOLBAR ── --}}
    <div class="toolbar">
        <form method="GET" action="{{ route('transaction') }}">

            {{-- Search --}}
            <div class="srch">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                     stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                </svg>
                <input
                    name="search"
                    placeholder="Cari transaksi, nama, id"
                    value="{{ request('search') }}"
                    oninput="autoSearch(this)">
            </div>

            {{-- Date --}}
            <input type="date"
                   name="date"
                   class="fsel"
                   value="{{ request('date') }}"
                   onchange="this.form.submit()">

            <select name="status" class="fsel" onchange="this.form.submit()">
                <option value="all" @selected(request('status','all') === 'all')>
                    Semua Status
                </option>

                <option value="aktif" @selected(request('status') === 'aktif')>
                    Aktif
                </option>

                <option value="digunakan" @selected(request('status') === 'digunakan')>
                    Digunakan
                </option>

                <option value="refunded" @selected(request('status') === 'refunded')>
                    Refunded
                </option>
            </select>

            {{-- Package --}}
            <select name="package" class="fsel" onchange="this.form.submit()">
                <option value="all" @selected(request('package','all') === 'all')>Semua Paket</option>
                @foreach($packages as $pkg)
                    <option value="{{ $pkg['nama_package'] }}"
                            @selected(request('package') === $pkg['nama_package'])>
                        {{ $pkg['nama_package'] }}
                    </option>
                @endforeach
            </select>

        </form>
    </div>

    {{-- ── CARD ── --}}
    <div class="card">

        {{-- Card header --}}
        <div class="ch">
            <div class="ic-box">💳</div>
            <div>
                <div class="ct">Semua Transaksi</div>
                <div class="cs">{{ $total }} Transaksi ditemukan</div>
            </div>
        </div>

        {{-- Table --}}
        <div class="tw">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Pengguna</th>
                        <th>Paket</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $i => $tx)
                        <tr>
                            <td style="color:#888;font-size:13px;">{{ $tx->transaction_id }}</td>

                            <td>
                                <div class="urow">
                                    <div class="uava uava-{{ $i % 6 }}">
                                        {{ strtoupper(substr($tx->user_name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="unm">{{ $tx->user_name }}</div>
                                        <div class="uml">{{ $tx->user_email }}</div>
                                    </div>
                                </div>
                            </td>

                            <td>{{ $tx->package_name }}</td>

                            <td style="font-weight:700;">
                                Rp.{{ number_format($tx->total, 0, ',', '.') }}
                            </td>

<td>
@php
    $status = strtolower($tx->status_ticket ?? '');
@endphp

<span style="
    padding:10px 18px;
    border-radius:999px;
    font-weight:700;
    font-size:13px;

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

                            <td style="color:#666;">
                                {{ \Carbon\Carbon::parse($tx->date)->isoFormat('D MMM YYYY') }}
                            </td>

                            <td>
<button
    class="adbtn btn-detail"
    data-id="{{ $tx->id }}"
    data-kode-qr="{{ $tx->transaction_id }}"
    data-ava="{{ strtoupper(substr($tx->user_name, 0, 1)) }}"
    data-name="{{ $tx->user_name }}"
    data-email="{{ $tx->user_email }}"
    data-paket="{{ $tx->package_name }}"
    data-tgl="{{ \Carbon\Carbon::parse($tx->date)->isoFormat('D MMM YYYY') }}"
    data-tanggal="{{ $tx->tanggal_reservasi }}"
    data-jam="{{ $tx->jam }}"
    data-metode="{{ $tx->metode }}"
    data-telepon="{{ $tx->telepon }}"
    data-total="Rp.{{ number_format($tx->total, 0, ',', '.') }}"
    title="Lihat detail"
>
    👁
</button>
                            </td>
                        </tr>
                    @empty
                        <tr class="empty-row">
                            <td colspan="7">
                                <span class="empty-icon">📭</span>
                                Tidak ada transaksi ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="pg-footer">

            <div class="pg-info">
                Menampilkan {{ $transactions->firstItem() }}–{{ $transactions->lastItem() }}
                dari {{ $total }} transaksi
            </div>

            @if($transactions->lastPage() > 1)
                <div class="pg-wrap">

                    {{-- Prev --}}
                    @if($transactions->onFirstPage())
                        <span class="pg-btn pg-disabled">‹</span>
                    @else
                        <a class="pg-btn" href="{{ $transactions->previousPageUrl() }}">‹</a>
                    @endif

                    {{-- Pages (smart ellipsis) --}}
                    @php
                        $cur  = $transactions->currentPage();
                        $last = $transactions->lastPage();
                        $pages = collect();
                        for ($p = 1; $p <= $last; $p++) {
                            if ($p === 1 || $p === $last || abs($p - $cur) <= 1) {
                                $pages->push($p);
                            }
                        }
                        $pages = $pages->unique()->sort()->values();
                    @endphp

                    @foreach($pages as $idx => $page)
                        @if($idx > 0 && $page - $pages[$idx-1] > 1)
                            <span class="pg-ellipsis">…</span>
                        @endif
                        <a class="pg-btn {{ $cur == $page ? 'pg-active' : '' }}"
                           href="{{ $transactions->url($page) }}">{{ $page }}</a>
                    @endforeach

                    {{-- Next --}}
                    @if($transactions->hasMorePages())
                        <a class="pg-btn" href="{{ $transactions->nextPageUrl() }}">›</a>
                    @else
                        <span class="pg-btn pg-disabled">›</span>
                    @endif

                </div>
            @endif

        </div>

    </div>
</div>

{{-- ── MODAL: Detail Transaksi ── --}}
<div class="mo" id="txDetailModal">
    <div class="modal">
        <div class="mh">
            <div class="mt">💳 Detail Transaksi</div>
            <button class="mc" onclick="closeModal('txDetailModal')">✕</button>
        </div>

        <div style="background:#FBF8F5;border-radius:11px;padding:13px;margin-bottom:13px">
            <div style="font-size:10px;font-weight:700;color:#9e9487;text-transform:uppercase;letter-spacing:1px">
                ID Transaksi
            </div>
            <div style="font-size:15px;font-weight:900;color:#e88040" id="tdId"></div>
        </div>

        <div class="urow" style="border:none;padding:0;margin-bottom:11px">
            <div class="uava" style="background:#f5efe5;color:#e88040;width:42px;height:42px;font-size:17px" id="tdAva"></div>
            <div>
                <div class="unm" style="font-size:14px" id="tdName"></div>
                <div class="uml" id="tdEmail"></div>
            </div>
        </div>

        <table style="width:100%">
            <tr>
                <td style="padding:6px 0;font-size:11.5px;font-weight:900">Paket</td>
                <td style="font-weight:800;text-align:right" id="tdPaket"></td>
            </tr>

            <tr>
                <td style="padding:6px 0;font-size:11.5px;font-weight:900">Tanggal Pesan</td>
                <td style="font-weight:800;text-align:right" id="tdTgl"></td>
            </tr>

            <tr>
                <td style="padding:6px 0;font-size:11.5px;font-weight:900">Tanggal Reservasi</td>
                <td style="font-weight:800;text-align:right" id="tdTanggal"></td>
            </tr>

            <tr>
                <td style="padding:6px 0;font-size:11.5px;font-weight:900">Jam Kunjungan</td>
                <td style="font-weight:800;text-align:right" id="tdJam"></td>
            </tr>

            <tr>
                <td style="padding:6px 0;font-size:11.5px;font-weight:900">Metode Pembayaran</td>
                <td style="font-weight:800;text-align:right" id="tdMetode"></td>
            </tr>

            <tr>
                <td style="padding:6px 0;font-size:11.5px;font-weight:900">Telepon</td>
                <td style="font-weight:800;text-align:right" id="tdTelepon"></td>
            </tr>

            <tr style="border-top:2px solid #f3ede5">
                <td style="padding:9px 0;font-size:13px;font-weight:900">Total</td>
                <td style="font-size:16px;font-weight:900;text-align:right;color:#e88040" id="tdTotal"></td>
            </tr>
        </table>

        <div style="display:flex;gap:8px;margin-top:10px">
            <button class="btn btn-ou" style="flex:1;justify-content:center" onclick="alert('Cetak tiket')">
                Cetak
            </button>

            <button
                id="refundBtn"
                class="btn btn-dg"
                style="flex:1;justify-content:center"
                onclick="
                    setRefundId(window.currentTransactionId, window.currentKodeQr)
                    openModal('refundConfirmModal')
                "
            >
                ↩ Refund
            </button>
        </div>
    </div>
</div>

{{-- ── MODAL: Konfirmasi Refund ── --}}
<div class="mo" id="refundConfirmModal">

    <div class="modal" style="max-width:400px;">

        <div class="mt" style="margin-bottom:20px;">
            Refund transaksi ini?
        </div>

        <button
            class="btn btn-dg"
            style="width:100%;margin-bottom:10px;"
            onclick="confirmRefund()"
        >
            Ya, refund sekarang
        </button>

        <button
            class="btn btn-ou"
            style="width:100%;"
            onclick="closeModal('refundConfirmModal')"
        >
            Batal
        </button>

    </div>

</div>

@endsection

@push('scripts')
<script>

    let selectedRefundTransactionId = null;
    let selectedRefundKodeQr = null;

    function setRefundId(transactionId, kodeQr) {

        selectedRefundTransactionId = transactionId;
        selectedRefundKodeQr = kodeQr;

        console.log('Transaction ID:', transactionId);
        console.log('Kode QR:', kodeQr);
    }

    async function confirmRefund() {

        if (!selectedRefundTransactionId || !selectedRefundKodeQr) {

            alert('ID transaksi atau kode QR tidak ditemukan');
            return;
        }

        try {

            const response = await fetch(
                `/transaction/refund/${selectedRefundTransactionId}`,
                {
                    method: 'PUT',

                    headers: {

                        'X-CSRF-TOKEN':
                            document.querySelector(
                                'meta[name="csrf-token"]'
                            ).content,

                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    },

                    body: JSON.stringify({
                        kode_qr: selectedRefundKodeQr
                    })
                }
            );

            const data = await response.json();

            console.log(data);

            if (data.success) {

                closeModal('refundConfirmModal');

                location.reload();

            } else {

                alert(data.message || 'Refund gagal');

            }

        } catch (err) {

            console.error(err);

            alert('Terjadi kesalahan');

        }
    }

function openTxDetail(id, kodeQr, av, nm, em, pk, tgl, tanggal, jam, metode, telepon, total) {
    window.currentTransactionId = id;
    window.currentKodeQr = kodeQr;

    document.getElementById('tdId').textContent = id;
    document.getElementById('tdAva').textContent = av;
    document.getElementById('tdName').textContent = nm;
    document.getElementById('tdEmail').textContent = em;
    document.getElementById('tdPaket').textContent = pk;
    document.getElementById('tdTgl').textContent = tgl;
    document.getElementById('tdTanggal').textContent = tanggal;
    document.getElementById('tdJam').textContent = jam;
    document.getElementById('tdMetode').textContent = metode;
    document.getElementById('tdTelepon').textContent = telepon;
    document.getElementById('tdTotal').textContent = total;

    openModal('txDetailModal');
}

document.querySelectorAll('.btn-detail').forEach(function(btn) {
    btn.addEventListener('click', function() {
        openTxDetail(
            this.dataset.id,
            this.dataset.kodeQr,
            this.dataset.ava,
            this.dataset.name,
            this.dataset.email,
            this.dataset.paket,
            this.dataset.tgl,
            this.dataset.tanggal,
            this.dataset.jam,
            this.dataset.metode,
            this.dataset.telepon,
            this.dataset.total
        );
    });
});
    function autoSearch(input) {

        clearTimeout(window.searchTimer);

        window.searchTimer = setTimeout(function () {

            input.form.submit();

        }, 500);
    }

    function openModal(id) {

        document.getElementById(id).style.display = 'flex';
    }

    function closeModal(id) {

        document.getElementById(id).style.display = 'none';
    }

    document.querySelectorAll('.mo').forEach(function(overlay) {

        overlay.addEventListener('click', function(e) {

            if (e.target === overlay) {

                overlay.style.display = 'none';

            }

        });

    });

</script>
@endpush