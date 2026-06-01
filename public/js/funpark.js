/* ═══════════════════════════════════════════════
   funpark.js  –  PlayZone Kasir JS
═══════════════════════════════════════════════ */

/* ─── LIVE CLOCK ─── */
function updateClock() {
    const n = new Date();
    const s =
        n.getHours().toString().padStart(2, "0") +
        ":" +
        n.getMinutes().toString().padStart(2, "0") +
        ":" +
        n.getSeconds().toString().padStart(2, "0");
    const el = document.getElementById("live-clock");
    if (el) el.textContent = s;
    const el2 = document.getElementById("update-time");
    if (el2) el2.textContent = s.slice(0, 5);
}
if (
    document.getElementById("live-clock") ||
    document.getElementById("update-time")
) {
    setInterval(updateClock, 1000);

    updateClock();
}

/* ─── MOBILE SIDEBAR ─── */
function openSidebar() {
    document.getElementById("sidebar")?.classList.add("open");
    document.getElementById("sb-overlay")?.classList.add("show");
    document.body.style.overflow = "hidden";
}
function closeSidebar() {
    document.getElementById("sidebar")?.classList.remove("open");
    document.getElementById("sb-overlay")?.classList.remove("show");
    document.body.style.overflow = "";
}
window.addEventListener("resize", () => {
    if (window.innerWidth > 768) closeSidebar();
});

/* ─── MODAL ─── */
function openM(id) {
    document.getElementById(id)?.classList.add("show");
}
function closeM(id) {
    document.getElementById(id)?.classList.remove("show");
}
document.addEventListener("click", function (e) {
    if (e.target.classList.contains("modal-overlay"))
        e.target.classList.remove("show");
});

/* ─── NOTIFICATION ─── */
let notifOpen = false;
let notifExpanded = false;
var camOn = false;
var scanCooldown = false;

function toggleNotif() {
    notifOpen = !notifOpen;
    document
        .getElementById("notif-dropdown")
        ?.classList.toggle("show", notifOpen);
}
function toggleMoreNotif() {
    notifExpanded = !notifExpanded;
    const list = document.getElementById("notif-list");
    const btn = document.getElementById("notif-more-btn");
    const items = list?.querySelectorAll(".notif-item");
    if (!items) return;
    if (notifExpanded) {
        items.forEach((i) => (i.style.display = "flex"));
        if (btn)
            btn.innerHTML = 'Sembunyikan <i class="fas fa-chevron-up"></i>';
        if (list) list.style.maxHeight = "460px";
    } else {
        items.forEach(
            (i, idx) => (i.style.display = idx < 5 ? "flex" : "none"),
        );
        if (btn)
            btn.innerHTML =
                'Lihat Lebih Banyak <i class="fas fa-chevron-down"></i>';
        if (list) list.style.maxHeight = "280px";
    }
}

document.addEventListener("click", function (e) {
    const wrap = document.getElementById("notif-btn")?.closest(".notif-wrap");
    const dd = document.getElementById("notif-dropdown");
    if (!wrap || !dd) return;
    if (!wrap.contains(e.target)) {
        dd.classList.remove("show");
        notifOpen = false;
    }
});

/* ─── TOAST ─── */
function toast(msg, type = "") {
    const c = document.getElementById("toast-wrap");
    if (!c) return;
    const el = document.createElement("div");
    el.className =
        "toast " + (type === "ok" ? "ok" : type === "err" ? "err" : "info");
    const ico = {
        ok: "fa-check-circle",
        err: "fa-times-circle",
        info: "fa-info-circle",
    };
    el.innerHTML = `<i class="fas ${ico[type] || ico.info}" style="font-size:.9rem;flex-shrink:0;"></i><span>${msg}</span>`;
    c.appendChild(el);
    setTimeout(() => {
        el.style.animation = "tOut .3s ease forwards";
        setTimeout(() => el.remove(), 300);
    }, 3500);
}

/* ─── KAPASITAS — simpan ke MongoDB via AJAX ─── */
/* ─── SIMPAN KAPASITAS (FINAL STABIL) ─── */

/* ════════════════════════════════════════════════
   QR SCANNER
════════════════════════════════════════════════ */

/* ════════════════════════════════════════════════
   checkout
════════════════════════════════════════════════ */
function openCheckout(name, pkg, checkin, txId) {
    document.getElementById("co-name").textContent = name;
    document.getElementById("co-paket").textContent = pkg;
    document.getElementById("co-checkin").textContent = checkin;

    var oldBtn = document.getElementById("co-confirm-btn");
    var newBtn = oldBtn.cloneNode(true);

    oldBtn.parentNode.replaceChild(newBtn, oldBtn);

    newBtn.disabled = false;
    newBtn.textContent = "Checkout";

    newBtn.addEventListener("click", async function () {
        newBtn.disabled = true;
        newBtn.textContent = "Memproses...";

        try {
            const res = await fetch("/scanner/checkout", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": document.querySelector(
                        'meta[name="csrf-token"]',
                    ).content,
                },
                body: JSON.stringify({
                    transaction_id: txId,
                }),
            });

            const data = await res.json();

            if (data.success) {
                closeM("checkout-modal");
                toast("Checkout berhasil", "ok");

                setTimeout(() => {
                    location.reload();
                }, 800);
            } else {
                toast(data.message || "Checkout gagal", "err");
                newBtn.disabled = false;
                newBtn.textContent = "Checkout";
            }
        } catch (e) {
            toast("Gagal menghubungi server", "err");
            newBtn.disabled = false;
            newBtn.textContent = "Checkout";
        }
    });

    openM("checkout-modal");
}
/* ═══════════════════════════════
   WALK-IN CLEAN VERSION
═══════════════════════════════ */
