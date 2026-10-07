/**
 * Popup xác nhận / bổ sung hồ sơ đại biểu (cổng cá nhân).
 * Server chỉ render popup khi chưa xác nhận trong phiên đăng nhập, nên có popup là bật ngay;
 * popup không đóng được cho tới khi lưu thành công.
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var modalEl = document.getElementById('modalProfile');
        var form = document.getElementById('form-profile');
        if (!modalEl || !form || typeof bootstrap === 'undefined') { return; }

        var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            submitProfile(form, modal);
        });
    });

    function submitProfile(form, modal) {
        var btn = document.getElementById('btn-submit-profile');
        if (!btn) { return; }
        var original = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i>Đang lưu...';

        function restore(message) {
            btn.disabled = false;
            btn.innerHTML = original;
            if (typeof Toast !== 'undefined') { Toast.error(message); }
        }

        fetch(form.action, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: new FormData(form)
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (data.success) {
                modal.hide();
                if (typeof Toast !== 'undefined') { Toast.success(data.message || 'Đã lưu thông tin.'); }
            } else {
                restore(data.message || 'Không lưu được thông tin.');
            }
        })
        .catch(function () {
            restore('Lỗi kết nối máy chủ. Vui lòng thử lại.');
        });
    }
})();
