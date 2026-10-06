/**
 * Cổng đăng ký hoạt động Đại hội — gộp 2 khối Chạy bộ (Fun Run) & Đi tham quan.
 *  - Đăng ký (FCFS): xác nhận SweetAlert, AJAX, khóa nút khi xử lý.
 *  - Xin hủy: modal nhập lý do, loading state.
 *  - Thông báo hủy đã được duyệt / bị từ chối (hiển thị 1 lần qua localStorage).
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var config = document.getElementById('run-config');
        if (!config) { return; }

        notifyApprovedOnce(config, 'run');
        notifyApprovedOnce(config, 'tour');
        notifyRejectedOnce(config, 'run');
        notifyRejectedOnce(config, 'tour');

        bindRegister(config);
        bindCancelForms();
    });

    function registerUrlFor(config, type) {
        return type === 'tour'
            ? config.getAttribute('data-register-tour-url')
            : config.getAttribute('data-register-run-url');
    }

    function fieldFor(type) {
        return type === 'tour' ? 'tour_session_id' : 'run_event_id';
    }

    function bindRegister(config) {
        document.querySelectorAll('.btn-portal-register').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var type = btn.getAttribute('data-type');
                var id = btn.getAttribute('data-id');
                var name = btn.getAttribute('data-name');

                Swal.fire({
                    title: 'Xác nhận đăng ký',
                    html: 'Bạn đăng ký <strong>' + name + '</strong>?<br><small class="text-muted">Mỗi nội dung chỉ chọn 1 lựa chọn và không thể đổi.</small>',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#0d6efd',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Đăng ký',
                    cancelButtonText: 'Hủy'
                }).then(function (result) {
                    if (result.isConfirmed) {
                        submitRegister(btn, registerUrlFor(config, type), fieldFor(type), id);
                    }
                });
            });
        });
    }

    function submitRegister(btn, url, field, id) {
        var original = btn.innerHTML;
        // Khóa mọi nút cùng khối (cùng data-type) để tránh chọn nhiều lựa chọn.
        var type = btn.getAttribute('data-type');
        var group = document.querySelectorAll('.btn-portal-register[data-type="' + type + '"]');
        group.forEach(function (b) { b.disabled = true; });
        btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Đang xử lý...';

        fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
            body: field + '=' + encodeURIComponent(id)
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            if (data.success) {
                Swal.fire({
                    title: 'Thành công!',
                    html: data.bib ? ('Số BIB của bạn: <strong>' + data.bib + '</strong>') : (data.message || 'Đăng ký thành công!'),
                    icon: 'success',
                    confirmButtonText: 'OK'
                }).then(function () { window.location.reload(); });
            } else {
                if (typeof Toast !== 'undefined') { Toast.error(data.message || 'Không thể đăng ký.'); }
                setTimeout(function () { window.location.reload(); }, 1500);
            }
        })
        .catch(function () {
            if (typeof Toast !== 'undefined') { Toast.error('Lỗi kết nối máy chủ. Vui lòng thử lại.'); }
            btn.innerHTML = original;
            group.forEach(function (b) { b.disabled = false; });
        });
    }

    function bindCancelForms() {
        document.querySelectorAll('.form-cancel-portal').forEach(function (form) {
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                var btn = document.getElementById(form.getAttribute('data-btn'));
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
                        var modalEl = document.getElementById(form.getAttribute('data-modal'));
                        var modal = bootstrap.Modal.getInstance(modalEl);
                        if (modal) { modal.hide(); }
                        if (typeof Toast !== 'undefined') { Toast.success(data.message || 'Đã gửi yêu cầu hủy.'); }
                        setTimeout(function () { window.location.reload(); }, 1200);
                    } else {
                        btn.disabled = false;
                        btn.innerHTML = original;
                        if (typeof Toast !== 'undefined') { Toast.error(data.message || 'Không gửi được yêu cầu hủy.'); }
                    }
                })
                .catch(function () {
                    btn.disabled = false;
                    btn.innerHTML = original;
                    if (typeof Toast !== 'undefined') { Toast.error('Lỗi kết nối máy chủ. Vui lòng thử lại.'); }
                });
            });
        });
    }

    function notifyApprovedOnce(config, type) {
        if (config.getAttribute('data-' + type + '-cancel-approved') !== '1') { return; }
        var reviewedAt = config.getAttribute('data-' + type + '-cancel-reviewed-at') || '0';
        var key = type + '_cancel_approved_seen_' + reviewedAt;
        try {
            if (localStorage.getItem(key)) { return; }
            localStorage.setItem(key, '1');
        } catch (e) { /* bỏ qua nếu localStorage bị chặn */ }

        var name = config.getAttribute('data-' + type + '-cancel-name') || '';
        var label = type === 'tour' ? 'đợt tham quan' : 'nội dung chạy';
        if (typeof Toast !== 'undefined') {
            Toast.info('Yêu cầu hủy ' + label + (name ? ' "' + name + '"' : '') + ' đã được duyệt. Suất đã được hoàn, bạn có thể đăng ký lại.', 8000);
        }
    }
})();
