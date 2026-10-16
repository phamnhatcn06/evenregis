/**
 * Điều khiển quay số từ xa: gửi lệnh spin, cập nhật tên giải, hiển thị phản hồi.
 */
document.addEventListener('DOMContentLoaded', function () {
    var cfg = window.__REMOTE || {};
    var btn = document.getElementById('btnSpin');
    var msg = document.getElementById('msg');
    var prizeName = document.getElementById('prizeName');

    function showMsg(text, ok) {
        msg.textContent = text;
        msg.className = 'msg ' + (ok ? 'ok' : 'err');
    }

    function refreshPrize() {
        fetch(cfg.prize, { cache: 'no-store' })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res && res.ok && res.data && res.data.prize_name) {
                    prizeName.textContent = res.data.prize_name
                        + ' (' + res.data.awarded + '/' + res.data.quantity + ')';
                }
            })
            .catch(function () {});
    }

    btn.addEventListener('click', function () {
        btn.disabled = true;
        showMsg('Đang gửi lệnh quay...', true);
        fetch(cfg.spin, { method: 'POST' })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res && res.ok) {
                    showMsg('Đã gửi! Màn quay số đang xử lý.', true);
                } else {
                    showMsg((res && res.msg) ? res.msg : 'Không gửi được lệnh', false);
                }
                setTimeout(function () { btn.disabled = false; refreshPrize(); }, 2000);
            })
            .catch(function () {
                showMsg('Lỗi kết nối server', false);
                btn.disabled = false;
            });
    });

    setInterval(refreshPrize, 5000);
    refreshPrize();
});
