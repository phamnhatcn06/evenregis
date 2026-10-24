/**
 * Màn quét chấm ăn (station). Đầu đọc QR hoạt động như bàn phím: "gõ" nội dung
 * QR (URL ...?token=...) rồi Enter vào ô ẩn luôn được focus.
 *
 * Hỗ trợ OFFLINE: mất kết nối -> lưu lượt quét vào IndexedDB, báo "đã lưu
 * offline"; có mạng lại -> tự đồng bộ lên server (idempotent theo bữa+người).
 */
(function () {
    'use strict';

    var root = document.getElementById('ms-root');
    if (!root) return;

    var cfg = {
        scanUrl: root.getAttribute('data-scan-url'),
        activeMealsUrl: root.getAttribute('data-active-meals-url'),
        syncUrl: root.getAttribute('data-sync-url'),
        statsUrl: root.getAttribute('data-stats-url'),
        stationId: root.getAttribute('data-station-id')
    };

    var el = {
        input: document.getElementById('ms-scan-input'),
        card: document.getElementById('ms-card'),
        icon: document.getElementById('ms-icon'),
        status: document.getElementById('ms-status'),
        person: document.getElementById('ms-person'),
        org: document.getElementById('ms-org'),
        sub: document.getElementById('ms-sub'),
        meal: document.getElementById('ms-meal'),
        connDot: document.getElementById('ms-conn-dot'),
        connText: document.getElementById('ms-conn-text'),
        count: document.getElementById('ms-count'),
        queueHint: document.getElementById('ms-queue-hint'),
        syncBtn: document.getElementById('ms-sync-btn')
    };

    var state = {
        online: true,
        currentMeal: null,       // {id, name, ...}
        serverOffset: 0,         // server_unix - clientUnix (giây)
        lastToken: '',
        lastScanAt: 0,
        resetTimer: null
    };

    // ---------------------------------------------------------------
    // IndexedDB hàng đợi offline
    // ---------------------------------------------------------------
    var DB_NAME = 'mealScanDB', STORE = 'queue', db = null;

    function openDb() {
        return new Promise(function (resolve) {
            try {
                var req = indexedDB.open(DB_NAME, 1);
                req.onupgradeneeded = function (e) {
                    var d = e.target.result;
                    if (!d.objectStoreNames.contains(STORE)) {
                        d.createObjectStore(STORE, { keyPath: 'client_id' });
                    }
                };
                req.onsuccess = function (e) { db = e.target.result; resolve(db); };
                req.onerror = function () { resolve(null); };
            } catch (err) { resolve(null); }
        });
    }

    function queueAdd(item) {
        return new Promise(function (resolve) {
            if (!db) return resolve(false);
            try {
                var tx = db.transaction(STORE, 'readwrite');
                tx.objectStore(STORE).put(item);
                tx.oncomplete = function () { resolve(true); };
                tx.onerror = function () { resolve(false); };
            } catch (e) { resolve(false); }
        });
    }

    function queueAll() {
        return new Promise(function (resolve) {
            if (!db) return resolve([]);
            try {
                var tx = db.transaction(STORE, 'readonly');
                var req = tx.objectStore(STORE).getAll();
                req.onsuccess = function () { resolve(req.result || []); };
                req.onerror = function () { resolve([]); };
            } catch (e) { resolve([]); }
        });
    }

    function queueDelete(ids) {
        return new Promise(function (resolve) {
            if (!db || !ids.length) return resolve();
            try {
                var tx = db.transaction(STORE, 'readwrite');
                var store = tx.objectStore(STORE);
                ids.forEach(function (id) { store.delete(id); });
                tx.oncomplete = function () { resolve(); };
                tx.onerror = function () { resolve(); };
            } catch (e) { resolve(); }
        });
    }

    function queueCount() {
        return queueAll().then(function (items) { return items.length; });
    }

    // ---------------------------------------------------------------
    // Âm thanh phản hồi
    // ---------------------------------------------------------------
    var audioCtx = null;
    function beep(type) {
        try {
            if (!audioCtx) audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            var seq = type === 'success' ? [[880, 0.12]]
                : type === 'already' ? [[600, 0.1], [600, 0.1]]
                : type === 'offline' ? [[500, 0.15]]
                : [[240, 0.35]]; // error
            var t = audioCtx.currentTime;
            seq.forEach(function (s) {
                var o = audioCtx.createOscillator(), g = audioCtx.createGain();
                o.frequency.value = s[0]; o.connect(g); g.connect(audioCtx.destination);
                g.gain.setValueAtTime(0.25, t);
                o.start(t); o.stop(t + s[1]); t += s[1] + 0.05;
            });
        } catch (e) { /* bỏ qua */ }
    }

    // ---------------------------------------------------------------
    // Hiển thị kết quả
    // ---------------------------------------------------------------
    function nowUnix() { return Math.floor(Date.now() / 1000) + state.serverOffset; }

    function setCard(stateCls, icon, status, person, org, sub) {
        el.card.className = 'ms-card ' + (stateCls || '');
        el.icon.innerHTML = '<i class="bi ' + icon + '"></i>';
        el.status.textContent = status || '';
        el.person.textContent = person || '';
        el.org.textContent = org || '';
        el.sub.textContent = sub || '';
        if (state.resetTimer) clearTimeout(state.resetTimer);
        state.resetTimer = setTimeout(resetCard, 3500);
    }

    function resetCard() {
        setCardIdle();
    }
    function setCardIdle() {
        el.card.className = 'ms-card';
        el.icon.innerHTML = '<i class="bi bi-upc-scan"></i>';
        el.status.textContent = 'Sẵn sàng quét';
        el.person.textContent = '';
        el.org.textContent = '';
        el.sub.textContent = 'Đưa thẻ đại hội vào đầu đọc QR';
    }

    function renderResult(data) {
        var d = data && data.data ? data.data : {};
        var st = d.status || 'invalid';
        var person = d.person || {};
        if (st === 'success') {
            beep('success');
            setCard('state-success', 'bi-check-circle-fill', 'Chấm ăn thành công',
                person.full_name, person.org, d.meal_name || '');
            bumpCount(1);
        } else if (st === 'already') {
            beep('already');
            setCard('state-already', 'bi-exclamation-circle-fill', 'Đã ghi nhận trước đây',
                person.full_name, person.org,
                d.checked_at ? ('Đã chấm lúc ' + formatTime(d.checked_at)) : '');
        } else if (st === 'no_meal') {
            beep('error');
            setCard('state-error', 'bi-clock-fill', 'Hiện không có bữa ăn nào đang mở',
                person.full_name || '', person.org || '', '');
        } else if (st === 'not_eligible') {
            beep('error');
            setCard('state-already', 'bi-person-x-fill', 'Không có trong danh sách tham dự',
                person.full_name || '', '', '');
        } else {
            beep('error');
            setCard('state-error', 'bi-x-circle-fill', 'Mã QR không hợp lệ',
                '', '', (data && data.message) ? data.message : '');
        }
    }

    function formatTime(dt) {
        var d = new Date((dt || '').replace(' ', 'T'));
        if (isNaN(d.getTime())) return dt;
        var p = function (n) { return (n < 10 ? '0' : '') + n; };
        return p(d.getHours()) + ':' + p(d.getMinutes());
    }

    function bumpCount(n) {
        var c = parseInt(el.count.textContent, 10) || 0;
        el.count.textContent = c + n;
    }

    // ---------------------------------------------------------------
    // Kết nối + bữa đang mở
    // ---------------------------------------------------------------
    function setOnline(on) {
        state.online = on;
        el.connDot.className = 'ms-dot ' + (on ? 'on' : 'off');
        el.connText.textContent = on ? 'Trực tuyến' : 'Mất kết nối';
    }

    function fetchJson(url, opts, timeoutMs) {
        opts = opts || {};
        var ctrl = new AbortController();
        opts.signal = ctrl.signal;
        var timer = setTimeout(function () { ctrl.abort(); }, timeoutMs || 4000);
        return fetch(url, opts)
            .then(function (r) { clearTimeout(timer); return r.json(); });
    }

    function refreshMeals() {
        return fetchJson(cfg.activeMealsUrl, { headers: { 'Accept': 'application/json' } }, 4000)
            .then(function (res) {
                setOnline(true);
                var d = res && res.data ? res.data : {};
                if (typeof d.server_unix === 'number') {
                    state.serverOffset = d.server_unix - Math.floor(Date.now() / 1000);
                }
                state.currentMeal = d.current_meal || null;
                if (state.currentMeal) {
                    el.meal.textContent = state.currentMeal.name;
                    el.meal.className = 'ms-meal';
                    refreshStats(state.currentMeal.id);
                } else {
                    el.meal.textContent = 'Chưa có bữa đang mở';
                    el.meal.className = 'ms-meal ms-meal-none';
                }
                flushQueue();
                return true;
            })
            .catch(function () { setOnline(false); return false; });
    }

    function refreshStats(mealId) {
        if (!mealId) return;
        fetchJson(cfg.statsUrl + '?meal_id=' + mealId, { headers: { 'Accept': 'application/json' } }, 4000)
            .then(function (res) {
                var d = res && res.data ? res.data : {};
                if (typeof d.total === 'number') el.count.textContent = d.total;
            })
            .catch(function () { /* giữ số đếm cục bộ */ });
    }

    // ---------------------------------------------------------------
    // Quét
    // ---------------------------------------------------------------
    function handleScan(raw) {
        var qr = (raw || '').trim();
        if (qr === '') return;

        // Chống quét trùng liên tiếp cùng 1 thẻ trong 1.5s.
        var now = Date.now();
        if (qr === state.lastToken && (now - state.lastScanAt) < 1500) return;
        state.lastToken = qr; state.lastScanAt = now;

        var scannedAt = nowUnix();
        var mealId = state.currentMeal ? state.currentMeal.id : null;

        if (!state.online) {
            return enqueueOffline(qr, mealId, scannedAt);
        }

        var body = new URLSearchParams();
        body.set('qr_value', qr);
        body.set('scanned_at', scannedAt);
        if (mealId) body.set('meal_id', mealId);

        fetchJson(cfg.scanUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'Accept': 'application/json' },
            body: body.toString()
        }, 5000)
            .then(function (res) {
                setOnline(true);
                if (res && res.code === 401) {
                    setCard('state-error', 'bi-lock-fill', 'Phiên trạm hết hạn', '', '', 'Vui lòng đăng nhập lại.');
                    return;
                }
                renderResult(res);
            })
            .catch(function () {
                // Mất kết nối giữa chừng -> lưu offline, không chặn luồng.
                setOnline(false);
                enqueueOffline(qr, mealId, scannedAt);
            });
    }

    function enqueueOffline(qr, mealId, scannedAt) {
        var item = {
            client_id: 'c' + scannedAt + '_' + Math.random().toString(36).slice(2, 8),
            qr_value: qr,
            meal_id: mealId,
            scanned_at: scannedAt
        };
        queueAdd(item).then(function () {
            beep('offline');
            setCard('state-offline', 'bi-cloud-arrow-up-fill', 'Đã lưu offline',
                '', '', 'Sẽ tự đồng bộ khi có mạng.');
            updateQueueHint();
        });
    }

    function updateQueueHint() {
        queueCount().then(function (n) {
            if (n > 0) {
                el.queueHint.textContent = n + ' bản chờ đồng bộ';
                el.syncBtn.style.display = '';
                el.connText.textContent = state.online ? 'Trực tuyến' : ('Mất kết nối — ' + n + ' bản chờ');
            } else {
                el.queueHint.textContent = '';
                el.syncBtn.style.display = 'none';
            }
        });
    }

    var flushing = false;
    function flushQueue() {
        if (flushing || !state.online) return;
        queueAll().then(function (items) {
            if (!items.length) { updateQueueHint(); return; }
            flushing = true;
            var payload = items.map(function (it) {
                return { client_id: it.client_id, qr_value: it.qr_value, meal_id: it.meal_id, scanned_at: it.scanned_at };
            });
            var body = new URLSearchParams();
            body.set('items', JSON.stringify(payload));
            fetchJson(cfg.syncUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'Accept': 'application/json' },
                body: body.toString()
            }, 15000)
                .then(function (res) {
                    var d = res && res.data ? res.data : {};
                    var results = d.results || [];
                    var doneIds = results.filter(function (r) {
                        return r.status === 'success' || r.status === 'already';
                    }).map(function (r) { return r.client_id; });
                    return queueDelete(doneIds);
                })
                .then(function () {
                    flushing = false;
                    updateQueueHint();
                    if (state.currentMeal) refreshStats(state.currentMeal.id);
                })
                .catch(function () { flushing = false; setOnline(false); });
        });
    }

    // ---------------------------------------------------------------
    // Giữ focus + lắng nghe đầu đọc
    // ---------------------------------------------------------------
    function focusInput() { try { el.input.focus(); } catch (e) {} }

    el.input.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            var val = el.input.value;
            el.input.value = '';
            handleScan(val);
        }
    });
    document.addEventListener('click', focusInput);
    document.addEventListener('keydown', function () { if (document.activeElement !== el.input) focusInput(); });
    window.addEventListener('focus', focusInput);

    el.syncBtn.addEventListener('click', function () { refreshMeals(); });
    window.addEventListener('online', function () { setOnline(true); refreshMeals(); });
    window.addEventListener('offline', function () { setOnline(false); updateQueueHint(); });

    // ---------------------------------------------------------------
    // Khởi động
    // ---------------------------------------------------------------
    openDb().then(function () {
        setCardIdle();
        focusInput();
        refreshMeals();
        updateQueueHint();
        setInterval(refreshMeals, 20000); // heartbeat + cập nhật bữa/đếm
    });
})();
