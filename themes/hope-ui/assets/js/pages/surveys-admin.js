/**
 * Trang danh sách Khảo sát ý kiến (admin/surveys/admin).
 * Bật/tắt nhanh trạng thái hiển thị của từng khảo sát qua AJAX (switch).
 */
(function () {
    'use strict';

    function bindToggles() {
        var switches = document.querySelectorAll('.survey-toggle');
        switches.forEach(function (input) {
            if (input.dataset.bound === '1') {
                return;
            }
            input.dataset.bound = '1';

            input.addEventListener('change', function () {
                var url = input.getAttribute('data-url');
                var desired = input.checked;
                input.disabled = true;

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                    .then(function (res) { return res.json(); })
                    .then(function (data) {
                        input.disabled = false;
                        if (data && data.success) {
                            if (typeof data.is_active !== 'undefined' && data.is_active !== null) {
                                input.checked = (parseInt(data.is_active, 10) === 1);
                            }
                            if (window.Toast) {
                                Toast.success(data.message || 'Cập nhật trạng thái thành công.');
                            }
                        } else {
                            input.checked = !desired; // hoàn tác
                            if (window.Toast) {
                                Toast.error((data && data.message) || 'Không thể cập nhật trạng thái.');
                            }
                        }
                    })
                    .catch(function () {
                        input.disabled = false;
                        input.checked = !desired; // hoàn tác
                        if (window.Toast) {
                            Toast.error('Lỗi kết nối server.');
                        }
                    });
            });
        });
    }

    document.addEventListener('DOMContentLoaded', bindToggles);
    // DataTables vẽ lại khi phân trang/tìm kiếm -> gắn lại sự kiện cho các switch mới.
    document.addEventListener('click', function () {
        setTimeout(bindToggles, 300);
    });
})();
