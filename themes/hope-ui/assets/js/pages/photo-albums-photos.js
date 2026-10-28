/* Quản trị Thư viện ảnh - quản lý ảnh: đặt ảnh bìa, xoá ảnh, xem người đã đánh dấu. */
(function () {
    'use strict';

    var grid = document.getElementById('admin-photo-grid');
    if (!grid) {
        return;
    }

    var setCoverTpl = grid.getAttribute('data-setcover-url');
    var deleteTpl = grid.getAttribute('data-delete-url');
    var csrfName = grid.getAttribute('data-csrf-name');
    var csrf = grid.getAttribute('data-csrf');

    function post(url) {
        var body = new URLSearchParams();
        body.set(csrfName, csrf);
        return fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'Accept': 'application/json' },
            body: body.toString()
        }).then(function (r) { return r.json(); });
    }

    grid.addEventListener('click', function (e) {
        var item = e.target.closest('.apg-item');
        if (!item) {
            return;
        }
        var id = item.getAttribute('data-id');

        if (e.target.closest('.apg-setcover')) {
            post(setCoverTpl.replace('__ID__', id)).then(function (data) {
                if (data && data.success) {
                    Toast.success('Đã đặt làm ảnh bìa.');
                    setTimeout(function () { location.reload(); }, 500);
                } else {
                    Toast.error('Không đặt được ảnh bìa.');
                }
            }).catch(function () { Toast.error('Lỗi kết nối server.'); });
            return;
        }

        if (e.target.closest('.apg-who')) {
            var names = item.getAttribute('data-names') || '';
            Swal.fire({
                title: 'Người đã đánh dấu',
                html: names ? names.split(', ').map(function (n) { return '<div>' + n + '</div>'; }).join('') : 'Chưa có ai.',
                icon: 'info',
                confirmButtonText: 'Đóng'
            });
            return;
        }

        if (e.target.closest('.apg-del')) {
            Swal.fire({
                title: 'Xác nhận xoá ảnh',
                text: 'Ảnh sẽ bị ẩn khỏi thư viện. Tiếp tục?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Xoá',
                cancelButtonText: 'Hủy'
            }).then(function (result) {
                if (!result.isConfirmed) {
                    return;
                }
                post(deleteTpl.replace('__ID__', id)).then(function (data) {
                    if (data && data.success) {
                        item.remove();
                        Toast.success(data.message || 'Đã xoá ảnh.');
                    } else {
                        Toast.error((data && data.message) || 'Xoá thất bại.');
                    }
                }).catch(function () { Toast.error('Lỗi kết nối server.'); });
            });
        }
    });
})();
