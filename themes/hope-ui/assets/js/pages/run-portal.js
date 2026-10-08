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
        bindShirtSizeForm();
        bindEmergencyForm();
        maybePromptShirtSize(config);
        maybePromptEmergency(config);
        initCountdown();
    });

    var EMERGENCY_PROMPT_KEY = 'run_emergency_prompt_pending';

    function initCountdown() {
        var countdownEl = document.getElementById('run-portal-countdown');
        if (!countdownEl) { return; }

        var targetTimestamp = parseInt(countdownEl.getAttribute('data-target') || '0', 10);
        if (!targetTimestamp || isNaN(targetTimestamp)) { return; }

        var daysEl = document.getElementById('cd-days');
        var hoursEl = document.getElementById('cd-hours');
        var minsEl = document.getElementById('cd-mins');
        var secsEl = document.getElementById('cd-secs');

        function updateClock() {
            var now = Math.floor(Date.now() / 1000);
            var remaining = targetTimestamp - now;

            if (remaining <= 0) {
                if (daysEl) daysEl.textContent = '00';
                if (hoursEl) hoursEl.textContent = '00';
                if (minsEl) minsEl.textContent = '00';
                if (secsEl) secsEl.textContent = '00';

                var livePill = document.querySelector('.countdown-live-pill');
                if (livePill) {
                    livePill.innerHTML = '<i class="bi bi-clock-history me-1"></i> ĐÃ HẾT THỜI GIAN';
                    livePill.style.background = 'rgba(100, 116, 139, 0.35)';
                    livePill.style.borderColor = 'rgba(148, 163, 184, 0.4)';
                    livePill.style.color = '#cbd5e1';
                }
                return;
            }

            var days = Math.floor(remaining / 86400);
            var hours = Math.floor((remaining % 86400) / 3600);
            var mins = Math.floor((remaining % 3600) / 60);
            var secs = remaining % 60;

            if (daysEl) daysEl.textContent = days < 10 ? '0' + days : days;
            if (hoursEl) hoursEl.textContent = hours < 10 ? '0' + hours : hours;
            if (minsEl) minsEl.textContent = mins < 10 ? '0' + mins : mins;
            if (secsEl) secsEl.textContent = secs < 10 ? '0' + secs : secs;
        }

        updateClock();
        setInterval(updateClock, 1000);
    }

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
                // Chỉ nội dung chạy mới có bước khai thông tin khẩn cấp.
                if (field === 'run_event_id') {
                    try { localStorage.setItem(EMERGENCY_PROMPT_KEY, '1'); } catch (e) { /* bỏ qua */ }
                }
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

    // Size áo Fun Run là BẮT BUỘC: tự bật popup (không cho bỏ qua) khi người này đã
    // đăng ký chạy nhưng chưa chọn size. Backdrop tĩnh đã do PHP đặt sẵn.
    function maybePromptShirtSize(config) {
        if (config.getAttribute('data-needs-shirt-size') !== '1') { return; }
        var modalEl = document.getElementById('modalShirtSize');
        if (!modalEl || typeof bootstrap === 'undefined') { return; }
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }

    // Lưu size áo Fun Run qua AJAX (bắt buộc).
    function bindShirtSizeForm() {
        var form = document.getElementById('form-shirt-size');
        if (!form) { return; }

        form.addEventListener('submit', function (e) {
            e.preventDefault();

            var select = document.getElementById('shirt_size');
            if (select && !select.value) {
                if (typeof Toast !== 'undefined') { Toast.warning('Vui lòng chọn size áo.'); }
                return;
            }

            var btn = document.getElementById('btn-submit-shirt-size');
            var original = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i>Đang lưu...';

            fetch(form.action, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: new FormData(form)
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.success) {
                    var modalEl = document.getElementById('modalShirtSize');
                    var modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) { modal.hide(); }
                    if (typeof Toast !== 'undefined') { Toast.success(data.message || 'Đã lưu size áo Fun Run.'); }
                    setTimeout(function () { window.location.reload(); }, 1000);
                } else {
                    btn.disabled = false;
                    btn.innerHTML = original;
                    if (typeof Toast !== 'undefined') { Toast.error(data.message || 'Không lưu được size áo.'); }
                }
            })
            .catch(function () {
                btn.disabled = false;
                btn.innerHTML = original;
                if (typeof Toast !== 'undefined') { Toast.error('Lỗi kết nối máy chủ. Vui lòng thử lại.'); }
            });
        });
    }

    // Tự bật popup khai thông tin khẩn cấp ngay sau khi đăng ký chạy thành công
    // (trang vừa reload). Chỉ bật khi người này CHƯA khai thông tin nào.
    function maybePromptEmergency(config) {
        var pending = false;
        try { pending = localStorage.getItem(EMERGENCY_PROMPT_KEY) === '1'; } catch (e) { /* bỏ qua */ }
        if (!pending) { return; }

        // Ưu tiên bước chọn size áo (bắt buộc) trước — giữ cờ để bật emergency sau khi
        // người dùng chọn xong size và trang reload lại.
        if (config.getAttribute('data-needs-shirt-size') === '1') { return; }

        try { localStorage.removeItem(EMERGENCY_PROMPT_KEY); } catch (e) { /* bỏ qua */ }

        if (config.getAttribute('data-has-run') !== '1') { return; }
        if (config.getAttribute('data-has-emergency') === '1') { return; }

        var modalEl = document.getElementById('modalEmergency');
        if (!modalEl || typeof bootstrap === 'undefined') { return; }
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }

    // Lưu thông tin khẩn cấp qua AJAX (tất cả trường tùy chọn).
    function bindEmergencyForm() {
        var form = document.getElementById('form-emergency');
        if (!form) { return; }

        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var btn = document.getElementById('btn-submit-emergency');
            var original = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i>Đang lưu...';

            fetch(form.action, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: new FormData(form)
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.success) {
                    var modalEl = document.getElementById('modalEmergency');
                    var modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) { modal.hide(); }
                    if (typeof Toast !== 'undefined') { Toast.success(data.message || 'Đã lưu thông tin khẩn cấp.'); }
                    setTimeout(function () { window.location.reload(); }, 1000);
                } else {
                    btn.disabled = false;
                    btn.innerHTML = original;
                    if (typeof Toast !== 'undefined') { Toast.error(data.message || 'Không lưu được thông tin.'); }
                }
            })
            .catch(function () {
                btn.disabled = false;
                btn.innerHTML = original;
                if (typeof Toast !== 'undefined') { Toast.error('Lỗi kết nối máy chủ. Vui lòng thử lại.'); }
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

    function notifyRejectedOnce(config, type) {
        if (config.getAttribute('data-' + type + '-cancel-rejected') !== '1') { return; }
        var reviewedAt = config.getAttribute('data-' + type + '-reject-reviewed-at') || '0';
        var key = type + '_cancel_rejected_seen_' + reviewedAt;
        try {
            if (localStorage.getItem(key)) { return; }
            localStorage.setItem(key, '1');
        } catch (e) { /* bỏ qua nếu localStorage bị chặn */ }

        var label = type === 'tour' ? 'đợt tham quan' : 'nội dung chạy';
        if (typeof Toast !== 'undefined') {
            Toast.warning('Yêu cầu hủy ' + label + ' của bạn đã bị từ chối. Đăng ký vẫn còn hiệu lực.', 8000);
        }
    }
})();
