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
        bindDependentFilters(config);
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
     * Dropdown phụ thuộc: Đơn vị -> Bộ phận -> Phòng ban, nạp bằng AJAX, không reload trang.
     *
     * Gọi qua action của Yii (không gọi thẳng External API) để API key không bị nhúng vào HTML.
     */
    function bindDependentFilters(config) {
        var property = document.getElementById('filter-property');
        var division = document.getElementById('filter-division');
        var department = document.getElementById('filter-department');

        if (!property || !division || !department) {
            return;
        }

        var url = config.getAttribute('data-filter-options-url');
        var eventId = config.getAttribute('data-event-id');
        var periodId = config.getAttribute('data-period-id');

        if (!url || !eventId) {
            return;
        }

        property.addEventListener('change', function () {
            // Đổi đơn vị thì mã bộ phận/phòng ban cũ gần như chắc chắn không còn thuộc đơn vị mới;
            // giữ lại sẽ ra danh sách rỗng, HO dễ hiểu nhầm là "đơn vị này không có ai".
            reload({ resetDivision: true, resetDepartment: true });
        });

        division.addEventListener('change', function () {
            reload({ resetDivision: false, resetDepartment: true });
        });

        function reload(options) {
            var params = [
                'event_id=' + encodeURIComponent(eventId),
                'period_id=' + encodeURIComponent(periodId || ''),
                'property_id=' + encodeURIComponent(property.value || '')
            ];

            // Nạp lại phòng ban theo bộ phận đang chọn; nếu vừa đổi đơn vị thì bỏ bộ phận cũ.
            var divisionCode = options.resetDivision ? '' : (division.value || '');
            params.push('division_code=' + encodeURIComponent(divisionCode));

            setLoading(division, options.resetDivision);
            setLoading(department, true);

            fetch(url + (url.indexOf('?') === -1 ? '?' : '&') + params.join('&'), {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            })
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('HTTP ' + response.status);
                    }
                    return response.json();
                })
                .then(function (data) {
                    if (!data || !data.success) {
                        throw new Error((data && data.message) || 'Lỗi không xác định');
                    }

                    if (options.resetDivision) {
                        fillSelect(division, data.divisions, '-- Tất cả bộ phận --', '');
                    } else {
                        restorePrompt(division, '-- Tất cả bộ phận --', divisionCode);
                    }

                    fillSelect(department, data.departments, '-- Tất cả phòng ban --', '');
                })
                .catch(function (error) {
                    restorePrompt(division, '-- Tất cả bộ phận --', divisionCode);
                    restorePrompt(department, '-- Tất cả phòng ban --', '');
                    if (typeof Toast !== 'undefined') {
                        Toast.error('Không tải được danh sách bộ phận / phòng ban. ' + error.message);
                    }
                });
        }
    }

    function setLoading(select, clearValue) {
        select.disabled = true;
        if (clearValue) {
            select.innerHTML = '<option value="">-- Đang tải... --</option>';
        }
    }

    /** Đổ lại option, giữ nguyên giá trị đang chọn nếu nó còn tồn tại trong danh sách mới. */
    function fillSelect(select, items, promptLabel, selectedValue) {
        var previous = selectedValue !== undefined ? selectedValue : select.value;

        select.innerHTML = '';
        select.appendChild(createOption('', promptLabel));

        (items || []).forEach(function (item) {
            var label = item.name + (item.count ? ' (' + item.count + ')' : '');
            select.appendChild(createOption(item.code, label));
        });

        select.value = previous;
        if (select.value !== previous) {
            select.value = '';
        }
        select.disabled = false;
    }

    function restorePrompt(select, promptLabel, keepValue) {
        if (select.options.length === 0 || select.options[0].value !== '') {
            select.innerHTML = '';
            select.appendChild(createOption('', promptLabel));
        }
        if (keepValue !== undefined) {
            select.value = keepValue;
        }
        select.disabled = false;
    }

    function createOption(value, label) {
        var option = document.createElement('option');
        option.value = value;
        option.textContent = label;
        return option;
    }
})();
