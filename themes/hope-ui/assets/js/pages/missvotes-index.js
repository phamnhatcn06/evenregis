/**
 * Màn kết quả bình chọn Miss — xác nhận reset toàn bộ phiếu.
 * Thao tác không hoàn tác được nên bắt người dùng gõ "RESET" để xác nhận.
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var btn = document.getElementById('btn_reset_votes');
        var form = document.getElementById('form_reset_votes');
        if (!btn || !form) {
            return;
        }

        btn.addEventListener('click', function () {
            var total = btn.getAttribute('data-total') || '0';

            Swal.fire({
                title: 'Reset bình chọn?',
                html: 'Toàn bộ <strong>' + total + '</strong> phiếu bình chọn của cuộc thi sẽ bị xoá ' +
                    'và mọi tài khoản được trả lại đủ lượt bình chọn.<br>' +
                    '<span class="text-danger">Thao tác này không thể hoàn tác.</span><br><br>' +
                    'Gõ <strong>RESET</strong> để xác nhận:',
                icon: 'warning',
                input: 'text',
                inputPlaceholder: 'RESET',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Reset',
                cancelButtonText: 'Hủy',
                preConfirm: function (value) {
                    if ((value || '').trim().toUpperCase() !== 'RESET') {
                        Swal.showValidationMessage('Vui lòng gõ đúng chữ RESET');
                        return false;
                    }
                    return true;
                }
            }).then(function (result) {
                if (result.isConfirmed) {
                    btn.disabled = true;
                    btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i>Đang reset...';
                    form.submit();
                }
            });
        });
    });
})();
