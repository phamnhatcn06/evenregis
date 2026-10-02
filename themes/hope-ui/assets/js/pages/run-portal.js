/**
 * Cổng đăng ký chạy — xử lý nút "Đăng ký" (FCFS).
 * Xác nhận bằng SweetAlert, gọi AJAX register, khóa nút khi đang xử lý.
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var config = document.getElementById('run-config');
        if (!config) {
            return;
        }
        var registerUrl = config.getAttribute('data-register-url');

        var buttons = document.querySelectorAll('.btn-run-register');
        buttons.forEach(function (btn) {
            btn.addEventListener('click', function () {
                var id = btn.getAttribute('data-id');
                var name = btn.getAttribute('data-name');

                Swal.fire({
                    title: 'Xác nhận đăng ký',
                    html: 'Bạn đăng ký nội dung <strong>' + name + '</strong>?<br><small class="text-muted">Mỗi người chỉ được đăng ký 1 nội dung và không thể đổi.</small>',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#0d6efd',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Đăng ký',
                    cancelButtonText: 'Hủy'
                }).then(function (result) {
                    if (result.isConfirmed) {
                        submitRegister(btn, registerUrl, id);
                    }
                });
            });
        });
    });

    function submitRegister(btn, url, runEventId) {
        var original = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Đang xử lý...';
        // Khóa toàn bộ nút để tránh chọn nhiều nội dung cùng lúc
        document.querySelectorAll('.btn-run-register').forEach(function (b) { b.disabled = true; });

        var body = 'run_event_id=' + encodeURIComponent(runEventId);

        fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
            body: body
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (data.success) {
                Swal.fire({
                    title: 'Thành công!',
                    html: 'Số BIB của bạn: <strong>' + (data.bib || '') + '</strong>',
                    icon: 'success',
                    confirmButtonText: 'Xem phiếu'
                }).then(function () {
                    window.location.reload();
                });
            } else {
                if (typeof Toast !== 'undefined') {
                    Toast.error(data.message || 'Không thể đăng ký.');
                }
                // Cập nhật lại số suất còn lại / trạng thái
                setTimeout(function () { window.location.reload(); }, 1500);
            }
        })
        .catch(function () {
            if (typeof Toast !== 'undefined') {
                Toast.error('Lỗi kết nối máy chủ. Vui lòng thử lại.');
            }
            btn.disabled = false;
            btn.innerHTML = original;
            document.querySelectorAll('.btn-run-register').forEach(function (b) { b.disabled = false; });
        });
    }
})();
