/**
 * Màn Tổng hợp danh sách Vòng Chung Kết (VCK) + mã lucky.
 *
 * Giai đoạn này: hiển thị flash message bằng Toast, sao chép định danh đăng nhập,
 * và dropdown phụ thuộc Đơn vị -> Bộ phận -> Phòng ban.
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var config = document.getElementById('final-attendee-roster-config');
        if (!config) {
            return;
        }

        showFlashMessages(config);
        bindCopyButtons();
        bindDependentFilters();
    });

    /** Flash message từ PHP hiển thị bằng Toast, không dùng Bootstrap Alert. */
    function showFlashMessages(config) {
        var raw = config.getAttribute('data-flash');
        if (!raw || typeof Toast === 'undefined') {
            return;
        }

        var messages;
        try {
            messages = JSON.parse(raw);
        } catch (e) {
            return;
        }

        Object.keys(messages || {}).forEach(function (type) {
            var text = messages[type];
            if (!text) {
                return;
            }
            if (type === 'error') {
                Toast.error(text);
            } else if (type === 'warning') {
                Toast.warning(text);
            } else if (type === 'info') {
                Toast.info(text);
            } else {
                Toast.success(text);
            }
        });
    }

    /** Sao chép định danh DHMT... để HO phát cho người tham dự. */
    function bindCopyButtons() {
        document.querySelectorAll('.js-copy').forEach(function (button) {
            button.addEventListener('click', function () {
                var text = button.getAttribute('data-copy');
                if (!text) {
                    return;
                }

                copyToClipboard(text).then(function () {
                    if (typeof Toast !== 'undefined') {
                        Toast.success('Đã sao chép: ' + text);
                    }
                }).catch(function () {
                    if (typeof Toast !== 'undefined') {
                        Toast.error('Không sao chép được. Vui lòng chọn và sao chép thủ công.');
                    }
                });
            });
        });
    }

    /**
     * navigator.clipboard chỉ hoạt động ở secure context (https/localhost); fallback cho http.
     */
    function copyToClipboard(text) {
        if (navigator.clipboard && window.isSecureContext) {
            return navigator.clipboard.writeText(text);
        }

        return new Promise(function (resolve, reject) {
            var textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.setAttribute('readonly', '');
            textarea.style.position = 'fixed';
            textarea.style.left = '-9999px';
            document.body.appendChild(textarea);
            textarea.select();

            try {
                document.execCommand('copy') ? resolve() : reject();
            } catch (e) {
                reject(e);
            } finally {
                document.body.removeChild(textarea);
            }
        });
    }

    /**
     * Đổi Đơn vị thì nạp lại Bộ phận / Phòng ban cho đúng phạm vi.
     *
     * Dữ liệu dropdown do controller truyền xuống theo đơn vị đang lọc, nên ở bước này chỉ cần
     * submit lại form để server trả danh sách đúng; bản AJAX sẽ làm ở bước dropdown phụ thuộc.
     */
    function bindDependentFilters() {
        var property = document.getElementById('filter-property');
        var division = document.getElementById('filter-division');
        var department = document.getElementById('filter-department');

        if (!property) {
            return;
        }

        property.addEventListener('change', function () {
            // Đổi đơn vị thì mã bộ phận/phòng ban cũ gần như chắc chắn không còn thuộc đơn vị mới,
            // giữ lại sẽ ra danh sách rỗng gây hiểu nhầm là "không có ai".
            if (division) {
                division.value = '';
            }
            if (department) {
                department.value = '';
            }
            property.form.submit();
        });
    }
})();
