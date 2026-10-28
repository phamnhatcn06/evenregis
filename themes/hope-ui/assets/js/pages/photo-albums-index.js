/* Quản trị Thư viện ảnh - danh sách album: mở modal sửa + submit cập nhật (AJAX). */
(function () {
    'use strict';

    var form = document.getElementById('formEditAlbum');
    var modalEl = document.getElementById('modalEditAlbum');
    if (!form || !modalEl) {
        return;
    }
    var urlTemplate = form.getAttribute('data-url-template');
    var modal = new bootstrap.Modal(modalEl);

    // Mở modal, nạp dữ liệu album vào form.
    document.querySelectorAll('.btn-edit-album').forEach(function (btn) {
        btn.addEventListener('click', function () {
            form.setAttribute('action', urlTemplate.replace('__ID__', btn.getAttribute('data-id')));
            document.getElementById('ea_name').value = btn.getAttribute('data-name') || '';
            document.getElementById('ea_public').value = btn.getAttribute('data-public') || '1';
            document.getElementById('ea_sort').value = btn.getAttribute('data-sort') || '0';
            modal.show();
        });
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var btn = document.getElementById('btn_save_album');
        var original = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i>Đang lưu...';

        fetch(form.getAttribute('action'), { method: 'POST', body: new FormData(form) })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data && data.success) {
                    modal.hide();
                    Toast.success(data.message || 'Đã cập nhật.');
                    setTimeout(function () { location.reload(); }, 600);
                } else {
                    btn.disabled = false;
                    btn.innerHTML = original;
                    Toast.error((data && data.message) || 'Cập nhật thất bại.');
                }
            })
            .catch(function () {
                btn.disabled = false;
                btn.innerHTML = original;
                Toast.error('Lỗi kết nối server.');
            });
    });
})();
