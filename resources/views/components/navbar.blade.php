<header class="topbar">
    <div class="tb-left">
        <button class="sb-toggle" onclick="openSidebar()" aria-label="Menu">
            <i class="fas fa-bars"></i>
        </button>
        <div>
            <div class="tb-page-title">@yield('page-title', 'Dashboard')</div>
            <div class="tb-page-sub">@yield('page-sub', 'Ringkasan operasional hari ini')</div>
        </div>
    </div>
    <div class="tb-right">
        <span class="tb-date">{{ \Carbon\Carbon::now()->locale('id')->isoFormat('dddd, D MMM YYYY') }}</span>

        {{-- Notification Bell --}}
        <div class="notif-wrap">
            <button class="tb-icon-btn" id="notif-btn" onclick="toggleNotif()" aria-label="Notifikasi">
                <i class="fas fa-bell"></i>
                <span class="tb-badge" id="notif-badge" style="display:none;">0</span>
            </button>

            <div class="notif-dropdown" id="notif-dropdown">
                <div class="notif-hd">
                    <span class="notif-hd-title">🔔 Notifikasi</span>
                    <button class="notif-mark-btn" onclick="markAllRead()">Tandai dibaca</button>
                </div>

                {{-- List notifikasi — diisi JS --}}
                <div class="notif-list" id="notif-list">
                    <div class="notif-empty" style="text-align:center;padding:30px 20px;color:#aaa;">
                        <i class="fas fa-bell-slash" style="font-size:1.5rem;margin-bottom:8px;display:block;"></i>
                        <span style="font-size:.8rem;">Memuat notifikasi...</span>
                    </div>
                </div>

                <div class="notif-ft">
                    <button class="notif-more-btn" id="notif-more-btn" onclick="toggleMoreNotif()">
                        Lihat Lebih Banyak <i class="fas fa-chevron-down"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</header>

<script>


/* ===============================
   PROFESSIONAL NOTIFICATION LOGIC
   Persistent read-state
   =============================== */

var NOTIF_URL = '/notifications';


var _allNotifs = [];
var _notifLoaded = false;

/* Warna dot */
var DOT_COLORS = {
    orange: '#f97316',
    blue:   '#3b82f6',
    green:  '#22c55e',
    pink:   '#ec4899',
    red:    '#ef4444',
};


/* ===============================
   FETCH NOTIFICATIONS
   =============================== */

async function fetchNotifications() {

    try {

        var res  = await fetch(NOTIF_URL, {
            headers: { 'Accept': 'application/json' }
        });

        var data = await res.json();

        _allNotifs = data.notifications ?? [];

        _notifLoaded = true;

        renderNotifList();

        updateNotifBadge();

    }
    catch(e) {

        if (!_notifLoaded) {

            document.getElementById('notif-list').innerHTML =
                '<div style="text-align:center;padding:20px;color:#aaa;font-size:.8rem;">Gagal memuat notifikasi</div>';

        }

    }

}



/* ===============================
   RENDER LIST
   =============================== */

function renderNotifList() {

    var list = document.getElementById('notif-list');

    if (!list) return;

    if (!_allNotifs || _allNotifs.length === 0) {

        list.innerHTML =
            '<div style="text-align:center;padding:30px 20px;color:#aaa;">' +
            '<i class="fas fa-bell-slash" style="font-size:1.5rem;margin-bottom:8px;display:block;"></i>' +
            '<span style="font-size:.8rem;">Tidak ada notifikasi</span>' +
            '</div>';

        return;
    }

    list.innerHTML = _allNotifs.map(function(n) {

        return `
            <div class="notif-item"
                 onclick="markReadItem(this, '${n._id ?? n.id ?? ''}')">

                <div class="notif-dot"
                     style="
                        background:#f97316;
                        width:9px;
                        height:9px;
                        border-radius:50%;
                        flex-shrink:0;
                        margin-top:3px;
                     ">
                </div>

                <div>
                    <div class="notif-msg">${n.title ?? '-'}</div>
                    <div class="notif-sub">${n.sub ?? '-'}</div>
                </div>

            </div>
        `;

    }).join('');

    applyNotifVisibility();
}

/* ===============================
   SHOW MAX 5
   =============================== */

function applyNotifVisibility() {

    var items = document.querySelectorAll('#notif-list .notif-item');

    items.forEach(function(el, idx) {

        el.style.display =
            (idx < 5 || notifExpanded)
            ? 'flex'
            : 'none';

    });

}


/* ===============================
   BADGE COUNT
   =============================== */

function updateNotifBadge() {

    var badge = document.getElementById('notif-badge');

    if (!badge) return;

    var unread = _allNotifs.length;

    badge.textContent = unread > 99 ? '99+' : unread;

    badge.style.display =
        unread > 0 ? 'flex' : 'none';
}
/* ===============================
   MARK SINGLE READ
   =============================== */

function markReadItem(el, id) {

    fetch('/notifications/read/' + id, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN':
                document.querySelector('meta[name="csrf-token"]').content
        }
    });

    el.style.opacity = '0.5';
}

/* ===============================
   MARK ALL READ
   =============================== */

markAllRead = async function() {

    await fetch('/notifications/read-all', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN':
                document.querySelector('meta[name="csrf-token"]').content
        }
    });

    fetchNotifications();

    if (typeof toast === 'function') {
        toast('Semua notifikasi ditandai dibaca', 'ok');
    }
}

/* ===============================
   ESCAPE HTML
   =============================== */

function escHtml(str) {

    return String(str ?? '')

        .replace(/&/g,'&amp;')

        .replace(/</g,'&lt;')

        .replace(/>/g,'&gt;')

        .replace(/"/g,'&quot;');

}


/* ===============================
   AUTO LOAD
   =============================== */

fetchNotifications();

setInterval(fetchNotifications, 30000);

</script>
