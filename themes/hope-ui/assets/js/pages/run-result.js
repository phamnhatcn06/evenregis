/**
 * Cổng đăng ký chạy — màn kết quả.
 *  - Gửi yêu cầu xin hủy (modal có loading state).
 *  - Thông báo khi yêu cầu hủy bị từ chối (hiển thị 1 lần theo reviewed_at).
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        notifyRejectedOnce();
        bindCancelForm();
    });

    function notifyRejectedOnce() {
        var cfg = document.getElementById('run-result-config');
        if (!cfg || cfg.getAttribute('data-was-rejected') !== '1') {
            return;
        }
        var reviewedAt = cfg.getAttribute('data-reviewed-at') || '0';
        var key = 'run_cancel_rejected_seen_' + reviewedAt;
        try {
            if (localStorage.getItem(key)) {
                return;
            }
            localStorage.setItem(key, '1');
        } catch (e) { /* bỏ qua nếu localStorage bị chặn */ }

        var reason = cfg.getAttribute('data-reject-reason') || '';
        if (typeof Toast !== 'undefined') {
            Toast.warning('Yêu cầu hủy của bạn đã bị từ chối. Đăng ký vẫn còn hiệu lực.' + (reason ? ' (Lý do đã gửi: ' + reason + ')' : ''), 8000);
        }
    }

    function bindCancelForm() {
        var form = document.getElementById('form-cancel-run');
        if (!form) {
            return;
        }

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            var btn = document.getElementById('btn-submit-cancel-run');
            var original = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i>Đang gửi...';

            fetch(form.action, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: new FormData(form)
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.success) {
                    var modalEl = document.getElementById('modalCancelRun');
                    var modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) { modal.hide(); }
                    if (typeof Toast !== 'undefined') {
                        Toast.success(data.message || 'Đã gửi yêu cầu hủy.');
                    }
                    setTimeout(function () { window.location.reload(); }, 1200);
                } else {
                    btn.disabled = false;
                    btn.innerHTML = original;
                    if (typeof Toast !== 'undefined') {
                        Toast.error(data.message || 'Không gửi được yêu cầu hủy.');
                    }
                }
            })
            .catch(function () {
                btn.disabled = false;
                btn.innerHTML = original;
                if (typeof Toast !== 'undefined') {
                    Toast.error('Lỗi kết nối máy chủ. Vui lòng thử lại.');
                }
            });
        });
    }
})();
