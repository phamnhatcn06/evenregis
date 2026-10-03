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
        bindSyncModal();
        bindEditRowModal(config);
        bindCellReset(config);
    });

    /**
     * Modal sửa thủ công toàn bộ trường của một người.
     *
     * Chỉ gửi lên những trường HO THỰC SỰ đổi, để không vô tình đánh dấu "đã sửa tay" cho
     * các trường chỉ đi ngang qua form mà giá trị không thay đổi.
     */
    function bindEditRowModal(config) {
        var form = document.getElementById('form_edit_row');
        if (!form) {
            return;
        }

        var modalElement = document.getElementById('modal_edit_row');
        var saveButton = document.getElementById('btn_edit_row_save');
        var resetAllButton = document.getElementById('btn_reset_all');
        var fieldLabels = parseJson(config.getAttribute('data-field-labels')) || {};
        var current = null;

        document.querySelectorAll('.js-edit-row').forEach(function (button) {
            button.addEventListener('click', function () {
                current = parseJson(button.getAttribute('data-row'));
                if (!current) {
                    return;
                }
                fillEditForm(current, fieldLabels);
                new bootstrap.Modal(modalElement).show();
            });
        });

        form.addEventListener('submit', function (event) {
            event.preventDefault();

            var changed = collectChangedFields(current);
            if (Object.keys(changed).length === 0) {
                if (typeof Toast !== 'undefined') {
                    Toast.info('Bạn chưa thay đổi trường nào.');
                }
                return;
            }

            var body = new FormData();
            body.append('id', current.id);
            Object.keys(changed).forEach(function (field) {
                body.append('fields[' + field + ']', changed[field]);
            });

            postWithButton(form.action, body, saveButton, function (data) {
                var modal = bootstrap.Modal.getInstance(modalElement);
                if (modal) {
                    modal.hide();
                }
                if (typeof Toast !== 'undefined') {
                    Toast.success(data.message);
                }
                window.setTimeout(function () { window.location.reload(); }, 600);
            });
        });

        // Nút ↺ trong từng ô của modal
        form.querySelectorAll('.js-reset-field').forEach(function (button) {
            button.addEventListener('click', function () {
                var field = button.getAttribute('data-field');
                confirmReset([field], current, fieldLabels, config, resetAllButton);
            });
        });

        if (resetAllButton) {
            resetAllButton.addEventListener('click', function () {
                var fields = (current && current.overridden_fields) || [];
                if (fields.length === 0) {
                    return;
                }
                confirmReset(fields, current, fieldLabels, config, resetAllButton);
            });
        }
    }

    /** Nạp dữ liệu dòng vào form, kèm dấu "đã sửa tay" và giá trị gốc từng trường. */
    function fillEditForm(row, fieldLabels) {
        setText('edit_row_name', row.full_name || '');
        var idInput = document.getElementById('edit_row_id');
        if (idInput) {
            idInput.value = row.id;
        }

        var overridden = row.overridden_fields || [];
        var snapshot = row.source_snapshot || {};

        document.querySelectorAll('#form_edit_row .js-edit-field').forEach(function (input) {
            var field = input.getAttribute('data-field');
            input.value = row[field] === null || row[field] === undefined ? '' : row[field];

            var isOverridden = overridden.indexOf(field) !== -1;
            var hasOrigin = Object.prototype.hasOwnProperty.call(snapshot, field);

            toggle('badge_' + field, isOverridden);
            toggleClass('origin_' + field, 'd-none', !(isOverridden && hasOrigin));

            if (isOverridden && hasOrigin) {
                var origin = snapshot[field];
                setText('origin_' + field, 'Gốc: ' + (origin === null || origin === '' ? '(để trống)' : origin));
            }

            var resetButton = document.querySelector('.js-reset-field[data-field="' + field + '"]');
            if (resetButton) {
                // Dòng HO tự thêm không có nguồn nên không có gì để khôi phục.
                resetButton.classList.toggle('d-none', !(isOverridden && hasOrigin));
            }
        });

        toggleClass('btn_reset_all', 'd-none', overridden.length === 0);

        var meta = [];
        if (row.lucky_number) {
            meta.push('Mã lucky: ' + row.lucky_number + ' (không sửa được)');
        }
        if (row.updated_by) {
            meta.push('Sửa gần nhất bởi: ' + row.updated_by);
        }
        if (overridden.length > 0) {
            meta.push('Đang có ' + overridden.length + ' trường sửa tay: '
                + overridden.map(function (f) { return fieldLabels[f] || f; }).join(', '));
        }
        setText('edit_row_meta', meta.join(' · '));
    }

    /** Chỉ lấy trường có giá trị khác với dữ liệu đang hiển thị. */
    function collectChangedFields(row) {
        var changed = {};
        if (!row) {
            return changed;
        }

        document.querySelectorAll('#form_edit_row .js-edit-field').forEach(function (input) {
            var field = input.getAttribute('data-field');
            var before = row[field] === null || row[field] === undefined ? '' : String(row[field]);
            var after = input.value === null ? '' : String(input.value);

            if (before.trim() !== after.trim()) {
                changed[field] = after;
            }
        });

        return changed;
    }

    /** Nút ↺ ngay trên ô của bảng danh sách. */
    function bindCellReset(config) {
        var fieldLabels = parseJson(config.getAttribute('data-field-labels')) || {};

        document.querySelectorAll('.js-cell-reset').forEach(function (button) {
            button.addEventListener('click', function (event) {
                event.stopPropagation();
                var field = button.getAttribute('data-field');
                var rosterId = button.getAttribute('data-roster-id');
                confirmReset([field], { id: rosterId }, fieldLabels, config, null);
            });
        });
    }

    /** Khôi phục về gốc là thao tác mất dữ liệu đã sửa nên luôn hỏi lại bằng SweetAlert. */
    function confirmReset(fields, row, fieldLabels, config, resetAllButton) {
        if (!row || !row.id || !fields || fields.length === 0) {
            return;
        }

        var names = fields.map(function (f) { return fieldLabels[f] || f; }).join(', ');
        var url = config.getAttribute('data-reset-field-url');

        var send = function () {
            var body = new FormData();
            body.append('id', row.id);
            fields.forEach(function (field) {
                body.append('fields[]', field);
            });

            postWithButton(url, body, resetAllButton, function (data) {
                if (typeof Toast !== 'undefined') {
                    Toast.success(data.message);
                }
                window.setTimeout(function () { window.location.reload(); }, 600);
            });
        };

        if (typeof Swal === 'undefined') {
            send();
            return;
        }

        Swal.fire({
            title: 'Khôi phục về giá trị gốc?',
            html: 'Giá trị bạn đã sửa tay ở <strong>' + names
                + '</strong> sẽ bị thay bằng dữ liệu gốc từ lần đồng bộ cuối, và các lần đồng bộ sau'
                + ' sẽ lại cập nhật trường này.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Khôi phục',
            cancelButtonText: 'Hủy'
        }).then(function (result) {
            if (result.isConfirmed) {
                send();
            }
        });
    }

    /** POST kèm loading state trên nút, theo quy tắc submit trong modal. */
    function postWithButton(url, body, button, onSuccess) {
        var originalHtml = button ? button.innerHTML : null;
        if (button) {
            button.disabled = true;
            button.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i>Đang xử lý...';
        }

        fetch(url, {
            method: 'POST',
            body: body,
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        })
            .then(function (response) {
                return response.json().then(function (data) {
                    return { ok: response.ok, data: data };
                });
            })
            .then(function (result) {
                if (button) {
                    button.disabled = false;
                    button.innerHTML = originalHtml;
                }

                if (!result.ok || !result.data.success) {
                    // 409 = sửa xong bị trùng khoá với người khác; giữ nguyên giá trị cũ,
                    // không reload để HO đọc được thông báo và sửa lại.
                    if (typeof Toast !== 'undefined') {
                        Toast.error(result.data.message || 'Không thể cập nhật.');
                    }
                    return;
                }

                onSuccess(result.data);
            })
            .catch(function () {
                if (button) {
                    button.disabled = false;
                    button.innerHTML = originalHtml;
                }
                if (typeof Toast !== 'undefined') {
                    Toast.error('Lỗi kết nối server.');
                }
            });
    }

    function parseJson(raw) {
        if (!raw) {
            return null;
        }
        try {
            return JSON.parse(raw);
        } catch (e) {
            return null;
        }
    }

    function toggleClass(id, className, on) {
        var element = document.getElementById(id);
        if (element) {
            element.classList.toggle(className, on);
        }
    }

    /**
     * Modal đồng bộ: Xem trước (dry-run) -> Ghi thật.
     *
     * Chỉ bật nút "Ghi thật" sau khi HO đã xem trước, để không ai ghi mù.
     */
    function bindSyncModal() {
        var form = document.getElementById('form_sync');
        var previewButton = document.getElementById('btn_sync_preview');
        var applyButton = document.getElementById('btn_sync_apply');

        if (!form || !previewButton || !applyButton) {
            return;
        }

        var previewUrl = form.getAttribute('data-preview-url');
        var propertySelect = document.getElementById('sync_property_id');

        // Đổi phạm vi thì kết quả xem trước cũ không còn đúng nữa.
        if (propertySelect) {
            propertySelect.addEventListener('change', function () {
                resetPreview(applyButton);
            });
        }

        previewButton.addEventListener('click', function () {
            submitSync(previewUrl, previewButton, function (data) {
                renderPreview(data.report);
                applyButton.disabled = false;
                applyButton.removeAttribute('title');
                if (typeof Toast !== 'undefined') {
                    Toast.info(data.message);
                }
            });
        });

        applyButton.addEventListener('click', function () {
            submitSync(form.action, applyButton, function (data) {
                var modalElement = document.getElementById('modal_sync');
                var modal = bootstrap.Modal.getInstance(modalElement);
                if (modal) {
                    modal.hide();
                }
                if (typeof Toast !== 'undefined') {
                    Toast.success(data.message);
                }
                // Danh sách vừa đổi nên phải nạp lại trang để số liệu và bảng khớp dữ liệu mới.
                window.setTimeout(function () { window.location.reload(); }, 800);
            });
        });

        function submitSync(url, button, onSuccess) {
            var originalHtml = button.innerHTML;
            button.disabled = true;
            button.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i>Đang xử lý...';
            previewButton.disabled = true;
            applyButton.disabled = true;

            fetch(url, {
                method: 'POST',
                body: new FormData(form),
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            })
                .then(function (response) {
                    return response.json().then(function (data) {
                        return { ok: response.ok, status: response.status, data: data };
                    });
                })
                .then(function (result) {
                    button.innerHTML = originalHtml;
                    previewButton.disabled = false;

                    if (!result.ok || !result.data.success) {
                        button.disabled = false;
                        // 409 = đang có tiến trình đồng bộ khác chạy cho cùng sự kiện.
                        var message = result.data.message || 'Không thể đồng bộ danh sách.';
                        if (typeof Toast !== 'undefined') {
                            Toast.error(message);
                        }
                        return;
                    }

                    button.disabled = false;
                    onSuccess(result.data);
                })
                .catch(function () {
                    button.innerHTML = originalHtml;
                    button.disabled = false;
                    previewButton.disabled = false;
                    if (typeof Toast !== 'undefined') {
                        Toast.error('Lỗi kết nối server.');
                    }
                });
        }
    }

    function resetPreview(applyButton) {
        applyButton.disabled = true;
        applyButton.setAttribute('title', 'Hãy xem trước kết quả trước khi ghi thật');
        toggle('sync_preview_result', false);
        toggle('sync_preview_empty', true);
    }

    /** Đổ số liệu + chi tiết của lần xem trước vào modal. */
    function renderPreview(report) {
        var summary = (report && report.summary) || {};

        setText('sync_sum_inserted', summary.inserted || 0);
        setText('sync_sum_restored', summary.restored || 0);
        setText('sync_sum_updated', summary.updated || 0);
        setText('sync_sum_unchanged', summary.unchanged || 0);
        setText('sync_sum_skipped', summary.skipped_override || 0);
        setText('sync_sum_deleted', summary.soft_deleted || 0);
        setText('sync_sum_conflicts', summary.conflicts || 0);

        fillList('sync_detail_skipped', 'sync_list_skipped', report.skipped_fields, function (item) {
            return item.full_name + ': ' + (item.fields || []).join(', ');
        });

        fillList('sync_detail_restored', 'sync_list_restored', report.restored_rows, function (item) {
            return item.full_name + (item.lucky_number ? ' — mã ' + item.lucky_number : '');
        });

        fillList('sync_detail_deleted', 'sync_list_deleted', report.soft_deleted_rows, function (item) {
            return item.full_name + (item.lucky_number ? ' — giữ mã ' + item.lucky_number : '');
        });

        fillList('sync_detail_conflicts', 'sync_list_conflicts', report.conflicts, function (item) {
            return '[' + conflictLabel(item.type) + '] ' + item.full_name
                + (item.dedup_key ? ' (' + item.dedup_key + ')' : '');
        });

        fillList('sync_detail_inserted', 'sync_list_inserted', report.inserted_rows, function (item) {
            return item.full_name + (item.property_name ? ' — ' + item.property_name : '');
        });

        toggle('sync_preview_empty', false);
        toggle('sync_preview_result', true);
    }

    function conflictLabel(type) {
        var labels = {
            duplicate_lucky: 'Trùng mã lucky',
            possible_wrong_merge: 'Có thể gộp sai người',
            duplicate_person: 'Một người bị tách hai dòng'
        };
        return labels[type] || type;
    }

    /** Danh sách chi tiết có thể rất dài — chỉ hiện 20 dòng đầu, phần còn lại ghi số lượng. */
    function fillList(wrapperId, listId, items, formatter) {
        var wrapper = document.getElementById(wrapperId);
        var list = document.getElementById(listId);
        if (!wrapper || !list) {
            return;
        }

        list.innerHTML = '';

        if (!items || items.length === 0) {
            wrapper.classList.add('d-none');
            return;
        }

        var MAX_SHOWN = 20;
        items.slice(0, MAX_SHOWN).forEach(function (item) {
            var li = document.createElement('li');
            li.textContent = formatter(item);
            list.appendChild(li);
        });

        if (items.length > MAX_SHOWN) {
            var more = document.createElement('li');
            more.className = 'text-muted';
            more.textContent = '... và ' + (items.length - MAX_SHOWN) + ' người nữa.';
            list.appendChild(more);
        }

        wrapper.classList.remove('d-none');
    }

    function setText(id, value) {
        var element = document.getElementById(id);
        if (element) {
            element.textContent = value;
        }
    }

    function toggle(id, visible) {
        var element = document.getElementById(id);
        if (element) {
            element.classList.toggle('d-none', !visible);
        }
    }

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
