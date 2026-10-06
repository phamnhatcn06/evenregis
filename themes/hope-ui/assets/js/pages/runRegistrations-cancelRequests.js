/**
 * Màn duyệt yêu cầu hủy đăng ký chạy (admin).
 * Duyệt / Từ chối đều xác nhận bằng SweetAlert rồi submit form POST tương ứng.
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        bind('.btn-approve-cancel', {
            title: 'Duyệt hủy đăng ký?',
            text: 'Suất đăng ký sẽ được hoàn lại cho người khác. Hành động này không thể hoàn tác.',
            confirmText: 'Duyệt hủy',
            confirmColor: '#198754'
        });
        bind('.btn-reject-cancel', {
            title: 'Từ chối yêu cầu hủy?',
            text: 'Đăng ký của người này sẽ được giữ nguyên.',
            confirmText: 'Từ chối',
            confirmColor: '#6c757d'
        });
    });

    function bind(selector, opts) {
        document.querySelectorAll(selector).forEach(function (btn) {
            btn.addEventListener('click', function () {
                var form = document.getElementById(btn.getAttribute('data-form'));
                if (!form) { return; }
                Swal.fire({
                    title: opts.title,
                    text: opts.text,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: opts.confirmColor,
                    cancelButtonColor: '#d33',
                    confirmButtonText: opts.confirmText,
                    cancelButtonText: 'Đóng'
                }).then(function (result) {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    }
})();
