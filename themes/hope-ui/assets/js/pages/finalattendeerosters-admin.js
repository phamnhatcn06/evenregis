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
        initSelect2();
        bindDependentFilters(config);
        bindSyncModal();
        bindEditRowModal(config);
        bindCellReset(config);
        bindInlineEdit(config);
        bindGenLuckyModal();
        bindLuckyConflictBadge();
        bindAddPersonModal();
        bindWithdraw(config);
        bindClearConflict(config);
        bindMergeSplitModal(config);
        bindPerPageSelect();
        bindSetLuckyModal(config);
    });

    /** Huỷ tư cách — luôn hỏi lại, và nói rõ mã lucky sẽ bị khoá chứ không mất. */
    function bindWithdraw(config) {
        var url = config.getAttribute('data-delete-url');

        document.querySelectorAll('.js-withdraw').forEach(function (button) {
            button.addEventListener('click', function () {
                var rosterId = button.getAttribute('data-roster-id');
                var fullName = button.getAttribute('data-full-name');
                var lucky = button.getAttribute('data-lucky');

                var send = function () {
                    var body = new FormData();
                    body.append('id', rosterId);
                    body.append('also_deactivate_attendee', 1);

                    postWithButton(url, body, button, function (data) {
                        if (typeof Toast !== 'undefined') {
                            Toast.success(data.message);
                        }
                        window.setTimeout(function () { window.location.reload(); }, 800);
                    });
                };

                if (typeof Swal === 'undefined') {
                    send();
                    return;
                }

                Swal.fire({
                    title: 'Huỷ tư cách người này?',
                    html: '<p style="text-align:left"><strong>' + escapeHtml(fullName) + '</strong>'
                        + ' sẽ bị đưa khỏi danh sách và không đăng nhập được cổng chạy.</p>'
                        + (lucky
                            ? '<p style="text-align:left">Mã lucky <strong>' + escapeHtml(lucky)
                                + '</strong> được giữ lại và <strong>không cấp cho người khác</strong>.</p>'
                            : ''),
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Huỷ tư cách',
                    cancelButtonText: 'Không'
                }).then(function (result) {
                    if (result.isConfirmed) {
                        send();
                    }
                });
            });
        });
    }

    /** Đánh dấu xung đột đã xử lý. */
    function bindClearConflict(config) {
        var url = config.getAttribute('data-clear-conflict-url');

        document.querySelectorAll('.js-clear-conflict').forEach(function (button) {
            button.addEventListener('click', function () {
                var body = new FormData();
                body.append('id', button.getAttribute('data-roster-id'));

                postWithButton(url, body, button, function (data) {
                    if (typeof Toast !== 'undefined') {
                        Toast.success(data.message);
                    }
                    window.setTimeout(function () { window.location.reload(); }, 600);
                });
            });
        });
    }

    /** Modal gộp dòng / tách người. */
    function bindMergeSplitModal(config) {
        var modalElement = document.getElementById('modal_merge_split');
        if (!modalElement) {
            return;
        }

        var mergeForm = document.getElementById('form_merge');
        var splitForm = document.getElementById('form_split');
        var mergeButton = document.getElementById('btn_merge_submit');
        var splitButton = document.getElementById('btn_split_submit');
        var searchInput = document.getElementById('merge_search');
        var results = document.getElementById('merge_results');
        var current = null;

        document.querySelectorAll('.js-merge-split').forEach(function (button) {
            button.addEventListener('click', function () {
                current = {
                    id: button.getAttribute('data-roster-id'),
                    fullName: button.getAttribute('data-full-name'),
                    lucky: button.getAttribute('data-lucky'),
                    staffCode: button.getAttribute('data-staff-code'),
                    attendeeIds: parseJson(button.getAttribute('data-attendee-ids')) || []
                };

                fillMergeSplit(current);
                new bootstrap.Modal(modalElement).show();
            });
        });

        // Đổi tab thì đổi nút hành động, để HO không bấm nhầm thao tác ngược lại.
        modalElement.querySelectorAll('[data-bs-toggle="tab"]').forEach(function (tab) {
            tab.addEventListener('shown.bs.tab', function (event) {
                var isSplit = event.target.getAttribute('data-bs-target') === '#tab_split';
                mergeButton.classList.toggle('d-none', isSplit);
                splitButton.classList.toggle('d-none', !isSplit);
            });
        });

        if (searchInput) {
            var timer = null;
            searchInput.addEventListener('input', function () {
                window.clearTimeout(timer);
                timer = window.setTimeout(function () {
                    searchMergeCandidates(config, searchInput.value, current, results);
                }, 350);
            });
        }

        mergeButton.addEventListener('click', function () {
            var mergeId = document.getElementById('merge_merge_id').value;
            if (!mergeId) {
                if (typeof Toast !== 'undefined') {
                    Toast.error('Hãy tìm và chọn dòng cần gộp.');
                }
                return;
            }

            postWithButton(mergeForm.action, new FormData(mergeForm), mergeButton, function (data) {
                closeModal(modalElement);
                if (typeof Toast !== 'undefined') {
                    Toast.success(data.message);
                }
                window.setTimeout(function () { window.location.reload(); }, 900);
            });
        });

        splitButton.addEventListener('click', function () {
            var checked = splitForm.querySelectorAll('input[name="attendee_ids_to_split[]"]:checked');
            if (checked.length === 0) {
                if (typeof Toast !== 'undefined') {
                    Toast.error('Hãy chọn bản ghi cần tách ra.');
                }
                return;
            }

            var staffCode = document.getElementById('split_staff_code').value.trim();
            var idCard = document.getElementById('split_id_card').value.trim();
            if (!staffCode && !idCard) {
                if (typeof Toast !== 'undefined') {
                    Toast.error('Hãy điền mã nhân viên hoặc số CCCD cho người được tách.');
                }
                return;
            }

            postWithButton(splitForm.action, new FormData(splitForm), splitButton, function (data) {
                closeModal(modalElement);
                if (typeof Toast !== 'undefined') {
                    Toast.success(data.message);
                }
                window.setTimeout(function () { window.location.reload(); }, 900);
            });
        });
    }

    function fillMergeSplit(row) {
        setText('ms_row_name', row.fullName);
        document.getElementById('merge_keep_id').value = row.id;
        document.getElementById('merge_keep_label').value = row.fullName
            + (row.lucky ? ' — mã ' + row.lucky : ' — chưa có mã');
        document.getElementById('split_row_id').value = row.id;
        document.getElementById('merge_merge_id').value = '';
        document.getElementById('merge_search').value = '';
        toggle('merge_selected', false);
        toggle('merge_results', false);
        document.getElementById('split_staff_code').value = '';
        document.getElementById('split_id_card').value = '';

        var container = document.getElementById('split_attendees');
        container.innerHTML = '';

        if (!row.attendeeIds || row.attendeeIds.length < 2) {
            container.innerHTML = '<div class="text-muted small">'
                + 'Dòng này chỉ gộp từ một bản ghi nên không có gì để tách.</div>';
            return;
        }

        row.attendeeIds.forEach(function (id, index) {
            // Dựng bằng DOM thay vì innerHTML: id lấy từ API nên không ghép thẳng vào HTML.
            var wrapper = document.createElement('div');
            wrapper.className = 'form-check';

            var input = document.createElement('input');
            input.className = 'form-check-input';
            input.type = 'checkbox';
            input.name = 'attendee_ids_to_split[]';
            input.value = id;
            input.id = 'split_att_' + id;

            var label = document.createElement('label');
            label.className = 'form-check-label';
            label.setAttribute('for', input.id);
            label.textContent = 'Bản ghi #' + id + (index === 0 ? ' (bản đại diện)' : '');

            wrapper.appendChild(input);
            wrapper.appendChild(label);
            container.appendChild(wrapper);
        });
    }

    /** Tìm dòng để gộp, dùng chính danh sách tổng hợp đang có. */
    function searchMergeCandidates(config, keyword, current, results) {
        if (!keyword || keyword.trim().length < 2 || !current) {
            results.classList.add('d-none');
            return;
        }

        var url = config.getAttribute('data-list-url');
        var params = 'event_id=' + encodeURIComponent(config.getAttribute('data-event-id'))
            + '&period_id=' + encodeURIComponent(config.getAttribute('data-period-id'))
            + '&keyword=' + encodeURIComponent(keyword.trim())
            + '&per_page=25&ajax_search=1';

        fetch(url + (url.indexOf('?') === -1 ? '?' : '&') + params, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        })
            .then(function (response) { return response.json(); })
            .then(function (data) {
                var rows = (data && data.rows) || [];
                results.innerHTML = '';

                var candidates = rows.filter(function (item) {
                    return String(item.id) !== String(current.id);
                });

                if (candidates.length === 0) {
                    results.innerHTML = '<div class="list-group-item text-muted small">'
                        + 'Không tìm thấy dòng nào khác khớp từ khoá.</div>';
                    results.classList.remove('d-none');
                    return;
                }

                candidates.forEach(function (item) {
                    var button = document.createElement('button');
                    button.type = 'button';
                    button.className = 'list-group-item list-group-item-action';
                    button.innerHTML = escapeHtml(item.full_name)
                        + (item.staff_code ? ' <span class="text-muted">(' + escapeHtml(item.staff_code) + ')</span>' : '')
                        + (item.lucky_number
                            ? ' <span class="badge bg-secondary">mã ' + escapeHtml(item.lucky_number) + '</span>'
                            : ' <span class="badge bg-light text-dark">chưa có mã</span>');

                    button.addEventListener('click', function () {
                        document.getElementById('merge_merge_id').value = item.id;
                        setText('merge_selected_name', item.full_name);
                        toggle('merge_selected', true);
                        results.classList.add('d-none');
                    });

                    results.appendChild(button);
                });

                results.classList.remove('d-none');
            })
            .catch(function () {
                if (typeof Toast !== 'undefined') {
                    Toast.error('Không tìm được danh sách để gộp.');
                }
            });
    }

    function closeModal(element) {
        var modal = bootstrap.Modal.getInstance(element);
        if (modal) {
            modal.hide();
        }
    }

    /**
     * Modal thêm người thủ công.
     *
     * Sau khi lưu, hiện mã lucky và định danh DHMT trong hộp thoại chờ HO xác nhận đã ghi lại —
     * nếu reload ngay thì HO mất thông tin cần phát cho người đó.
     */
    /**
     * Nút lưu trong modal là type="button" (theo rules/modal-submit.md) nên không tự gửi form.
     * Hàm này nối nút với form, đồng thời giữ được việc bấm Enter trong ô nhập.
     */
    function wireSubmitButton(form, button) {
        button.addEventListener('click', function () {
            if (typeof form.requestSubmit === 'function') {
                form.requestSubmit();
                return;
            }
            form.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
        });
    }

    function bindAddPersonModal() {
        var form = document.getElementById('form_add_person');
        var button = document.getElementById('btn_add_person_save');

        if (!form || !button) {
            return;
        }

        wireSubmitButton(form, button);

        form.addEventListener('submit', function (event) {
            event.preventDefault();

            postWithButton(form.action, new FormData(form), button, function (data) {
                var modalElement = document.getElementById('modal_add_person');
                var modal = bootstrap.Modal.getInstance(modalElement);
                if (modal) {
                    modal.hide();
                }

                var row = data.row || {};

                if (typeof Swal !== 'undefined' && row.lucky_number) {
                    Swal.fire({
                        title: 'Đã thêm ' + row.full_name,
                        html: '<p style="text-align:left">Hãy ghi lại và phát cho người này:</p>'
                            + '<ul style="text-align:left">'
                            + '<li>Mã lucky: <strong>' + escapeHtml(row.lucky_number) + '</strong></li>'
                            + '<li>Định danh đăng nhập: <strong>'
                            + escapeHtml(row.login_identifier || ('DHMT' + row.lucky_number))
                            + '</strong></li>'
                            + (row.badge_number
                                ? '<li>Số thẻ: <strong>' + escapeHtml(row.badge_number) + '</strong></li>'
                                : '')
                            + '</ul>',
                        icon: 'success',
                        confirmButtonText: 'Tôi đã ghi lại'
                    }).then(function () {
                        form.reset();
                        window.location.reload();
                    });
                    return;
                }

                if (typeof Toast !== 'undefined') {
                    Toast.success(data.message);
                }
                form.reset();
                window.setTimeout(function () { window.location.reload(); }, 800);
            });
        });
    }

    /** Modal cấp mã lucky. Chỉ cấp cho người chưa có mã, không có đường cấp lại. */
    function bindGenLuckyModal() {
        var form = document.getElementById('form_gen_lucky');
        var button = document.getElementById('btn_gen_lucky_submit');

        if (!form || !button) {
            return;
        }

        button.addEventListener('click', function () {
            postWithButton(form.action, new FormData(form), button, function (data) {
                var modalElement = document.getElementById('modal_gen_lucky');
                var modal = bootstrap.Modal.getInstance(modalElement);
                if (modal) {
                    modal.hide();
                }

                if (typeof Toast !== 'undefined') {
                    Toast.success(data.message);
                }

                // Có người giữ nhiều mã thì phải cho HO đọc danh sách trước khi trang reload,
                // vì họ cần thông báo thu hồi định danh đã phát ra ngoài.
                var conflicts = (data.report && data.report.conflicts) || [];
                if (conflicts.length > 0 && typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Có ' + conflicts.length + ' người giữ nhiều mã',
                        html: buildConflictHtml(conflicts),
                        icon: 'warning',
                        confirmButtonText: 'Tôi đã ghi lại'
                    }).then(function () { window.location.reload(); });
                    return;
                }

                window.setTimeout(function () { window.location.reload(); }, 800);
            });
        });
    }

    function buildConflictHtml(conflicts) {
        var rows = conflicts.slice(0, 20).map(function (item) {
            var dropped = (item.dropped_lucky || []).join(', ');
            return '<li style="text-align:left">' + escapeHtml(item.full_name)
                + (item.staff_code ? ' (' + escapeHtml(item.staff_code) + ')' : '')
                + ': giữ mã <strong>' + escapeHtml(item.kept_lucky) + '</strong>'
                + (dropped ? ', bỏ mã ' + escapeHtml(dropped) : '')
                + '</li>';
        }).join('');

        var more = conflicts.length > 20
            ? '<p class="text-muted">... và ' + (conflicts.length - 20) + ' người nữa.</p>'
            : '';

        return '<p style="text-align:left">Những người này cần được <strong>thông báo thu hồi</strong>'
            + ' định danh cũ đã phát:</p><ul>' + rows + '</ul>' + more;
    }

    /** Badge "Xung đột mã lucky" bấm vào mở danh sách người bị ảnh hưởng. */
    function bindLuckyConflictBadge() {
        var badge = document.getElementById('btn_show_lucky_conflicts');
        if (!badge) {
            return;
        }

        badge.addEventListener('click', function () {
            var conflicts = parseJson(badge.getAttribute('data-conflicts')) || [];
            if (conflicts.length === 0 || typeof Swal === 'undefined') {
                return;
            }

            var rows = conflicts.slice(0, 20).map(function (item) {
                return '<li style="text-align:left">' + escapeHtml(item.full_name)
                    + (item.staff_code ? ' (' + escapeHtml(item.staff_code) + ')' : '')
                    + ': các mã ' + escapeHtml((item.lucky_numbers || []).join(', '))
                    + (item.suggest_keep ? ' — đề xuất giữ <strong>' + escapeHtml(item.suggest_keep) + '</strong>' : '')
                    + '</li>';
            }).join('');

            Swal.fire({
                title: 'Người đang giữ nhiều mã lucky',
                html: '<p style="text-align:left">Bấm <strong>Cấp mã lucky</strong> để hệ thống giữ mã'
                    + ' cấp sớm nhất và bỏ các mã còn lại.</p><ul>' + rows + '</ul>',
                icon: 'warning',
                confirmButtonText: 'Đã hiểu'
            });
        });
    }

    function escapeHtml(value) {
        var div = document.createElement('div');
        div.textContent = value === null || value === undefined ? '' : String(value);
        return div.innerHTML;
    }

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

        if (saveButton) {
            wireSubmitButton(form, saveButton);
        }

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

    /**
     * Sửa trực tiếp trường (inline edit) ngay trên bảng, ví dụ Chức danh.
     */
    function bindInlineEdit(config) {
        var canUpdate = config.getAttribute('data-can-update') === '1';
        if (!canUpdate) {
            return;
        }

        var updateUrl = config.getAttribute('data-update-field-url');
        if (!updateUrl) {
            return;
        }

        var wrappers = document.querySelectorAll('.js-inline-editable');
        if (!wrappers || wrappers.length === 0) {
            return;
        }

        wrappers.forEach(function (wrapper) {
            var view = wrapper.querySelector('.js-inline-view');
            var editor = wrapper.querySelector('.js-inline-editor');
            var input = wrapper.querySelector('.js-inline-input');
            var saveBtn = wrapper.querySelector('.js-inline-save');
            var cancelBtn = wrapper.querySelector('.js-inline-cancel');
            var textSpan = wrapper.querySelector('.js-inline-text');
            var rosterId = wrapper.getAttribute('data-roster-id');
            var field = wrapper.getAttribute('data-field');

            if (!view || !editor || !input) {
                return;
            }

            var isSaving = false;

            function openEditor() {
                // Đóng các ô khác đang mở trước
                wrappers.forEach(function (other) {
                    if (other !== wrapper) {
                        var otherEd = other.querySelector('.js-inline-editor');
                        var otherVw = other.querySelector('.js-inline-view');
                        if (otherEd && !otherEd.classList.contains('d-none')) {
                            otherEd.classList.add('d-none');
                            if (otherVw) otherVw.classList.remove('d-none');
                        }
                    }
                });

                view.classList.add('d-none');
                editor.classList.remove('d-none');
                input.value = wrapper.getAttribute('data-value') || '';
                input.focus();
                input.select();
            }

            function closeEditor() {
                if (isSaving) return;
                editor.classList.add('d-none');
                view.classList.remove('d-none');
                input.value = wrapper.getAttribute('data-value') || '';
            }

            function saveValue(onDone) {
                if (isSaving) return;
                var newVal = input.value.trim();
                var curVal = (wrapper.getAttribute('data-value') || '').trim();

                if (newVal === curVal) {
                    closeEditor();
                    if (typeof onDone === 'function') onDone();
                    return;
                }

                isSaving = true;
                input.disabled = true;
                if (saveBtn) {
                    saveBtn.disabled = true;
                    saveBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';
                }
                if (cancelBtn) cancelBtn.disabled = true;

                var body = new FormData();
                body.append('id', rosterId);
                body.append('fields[' + field + ']', newVal);

                fetch(updateUrl, {
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
                        isSaving = false;
                        input.disabled = false;
                        if (saveBtn) {
                            saveBtn.disabled = false;
                            saveBtn.innerHTML = '<i class="fa fa-check"></i>';
                        }
                        if (cancelBtn) cancelBtn.disabled = false;

                        if (!result.ok || !result.data.success) {
                            if (typeof Toast !== 'undefined') {
                                Toast.error(result.data.message || 'Không thể cập nhật chức danh.');
                            }
                            return;
                        }

                        if (typeof Toast !== 'undefined') {
                            Toast.success(result.data.message || 'Đã cập nhật chức danh.');
                        }

                        // Cập nhật giá trị hiển thị & attribute
                        wrapper.setAttribute('data-value', newVal);
                        textSpan.textContent = newVal !== '' ? newVal : '-';
                        view.classList.add('far-cell-overridden');

                        // Cập nhật dữ liệu dòng trong nút sửa modal (nếu có)
                        updateModalRowData(rosterId, field, newVal);

                        // Đảm bảo nút khôi phục gốc hiển thị nếu có giá trị gốc
                        ensureResetButton(wrapper, field, rosterId, config);

                        closeEditor();
                        if (typeof onDone === 'function') onDone();
                    })
                    .catch(function () {
                        isSaving = false;
                        input.disabled = false;
                        if (saveBtn) {
                            saveBtn.disabled = false;
                            saveBtn.innerHTML = '<i class="fa fa-check"></i>';
                        }
                        if (cancelBtn) cancelBtn.disabled = false;
                        if (typeof Toast !== 'undefined') {
                            Toast.error('Lỗi kết nối server.');
                        }
                    });
            }

            // Click vào ô (trừ nút reset) để mở editor
            view.addEventListener('click', function (e) {
                if (e.target.closest('.js-cell-reset')) {
                    return;
                }
                openEditor();
            });

            if (saveBtn) {
                saveBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    saveValue();
                });
            }

            if (cancelBtn) {
                cancelBtn.addEventListener('click', function (e) {
                    e.preventDefault();
                    closeEditor();
                });
            }

            input.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    saveValue();
                } else if (e.key === 'Escape') {
                    e.preventDefault();
                    closeEditor();
                } else if (e.key === 'Tab' && !e.shiftKey) {
                    e.preventDefault();
                    saveValue(function () {
                        focusNextInlineEditor(wrapper);
                    });
                }
            });

            // Click ra ngoài thì tự lưu nếu có sửa đổi, hoặc đóng nếu không đổi
            document.addEventListener('mousedown', function (e) {
                if (!wrapper.contains(e.target) && !editor.classList.contains('d-none')) {
                    var newVal = input.value.trim();
                    var curVal = (wrapper.getAttribute('data-value') || '').trim();
                    if (newVal !== curVal) {
                        saveValue();
                    } else {
                        closeEditor();
                    }
                }
            });
        });
    }

    function focusNextInlineEditor(currentWrapper) {
        var all = Array.prototype.slice.call(document.querySelectorAll('.js-inline-editable'));
        var idx = all.indexOf(currentWrapper);
        if (idx !== -1 && idx + 1 < all.length) {
            var next = all[idx + 1];
            var nextView = next.querySelector('.js-inline-view');
            if (nextView) {
                nextView.click();
            }
        }
    }

    function updateModalRowData(rosterId, field, newVal) {
        document.querySelectorAll('.js-edit-row').forEach(function (btn) {
            var raw = btn.getAttribute('data-row');
            if (!raw) return;
            var row = parseJson(raw);
            if (row && String(row.id) === String(rosterId)) {
                row[field] = newVal;
                if (!Array.isArray(row.overridden_fields)) {
                    row.overridden_fields = [];
                }
                if (row.overridden_fields.indexOf(field) === -1) {
                    row.overridden_fields.push(field);
                }
                btn.setAttribute('data-row', JSON.stringify(row));
            }
        });
    }

    function ensureResetButton(wrapper, field, rosterId, config) {
        var view = wrapper.querySelector('.js-inline-view');
        if (!view || view.querySelector('.js-cell-reset')) {
            return;
        }
        if (wrapper.getAttribute('data-has-origin') !== '1') {
            return;
        }
        var original = wrapper.getAttribute('data-original') || '';
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'far-cell-reset-btn js-cell-reset';
        btn.setAttribute('data-field', field);
        btn.setAttribute('data-roster-id', rosterId);
        btn.setAttribute('data-field-label', field);
        btn.setAttribute('title', 'Khôi phục về giá trị gốc: ' + original);
        btn.innerHTML = '<i class="fa fa-undo"></i>';

        btn.addEventListener('click', function (event) {
            event.stopPropagation();
            var fieldLabels = parseJson(config.getAttribute('data-field-labels')) || {};
            confirmReset([field], { id: rosterId }, fieldLabels, config, null);
        });

        view.appendChild(btn);
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
                    var originalHtml = button.innerHTML;
                    button.innerHTML = '<i class="fa fa-check text-success"></i>';
                    window.setTimeout(function () {
                        button.innerHTML = originalHtml;
                    }, 1200);
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
     * Khởi tạo Select2 cho ô lọc Đơn vị (có tìm kiếm, xoá nhanh, giao diện Hope UI).
     */
    function initSelect2() {
        if (typeof jQuery !== 'undefined' && jQuery.fn && jQuery.fn.select2) {
            var $property = jQuery('#filter-property');
            if ($property.length && !$property.hasClass('select2-hidden-accessible')) {
                $property.select2({
                    placeholder: '-- Tất cả đơn vị --',
                    allowClear: true,
                    width: '100%',
                    language: {
                        noResults: function () { return 'Không tìm thấy đơn vị'; },
                        searching: function () { return 'Đang tìm...'; }
                    }
                });
            }
        }
    }

    /**
     * Dropdown phụ thuộc: Đơn vị -> Phòng ban, nạp bằng AJAX, không reload trang.
     *
     * Gọi qua action của Yii (không gọi thẳng External API) để API key không bị nhúng vào HTML.
     */
    function bindDependentFilters(config) {
        var property = document.getElementById('filter-property');
        var department = document.getElementById('filter-department');

        if (!property) {
            return;
        }

        var url = config.getAttribute('data-filter-options-url');
        var eventId = config.getAttribute('data-event-id');
        var periodId = config.getAttribute('data-period-id');

        if (!url || !eventId) {
            return;
        }

        function onPropertyChange() {
            if (!department) return;
            reload();
        }

        property.addEventListener('change', onPropertyChange);
        if (typeof jQuery !== 'undefined') {
            jQuery(property).on('change', onPropertyChange);
        }

        function reload() {
            var params = [
                'event_id=' + encodeURIComponent(eventId),
                'period_id=' + encodeURIComponent(periodId || ''),
                'property_id=' + encodeURIComponent(property.value || '')
            ];

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

                    fillSelect(department, data.departments, '-- Tất cả phòng ban --', '');
                })
                .catch(function (error) {
                    restorePrompt(department, '-- Tất cả phòng ban --', '');
                    if (typeof Toast !== 'undefined') {
                        Toast.error('Không tải được danh sách phòng ban. ' + error.message);
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

    /** Điều chỉnh số bản ghi / trang từ dropdown */
    function bindPerPageSelect() {
        document.querySelectorAll('.js-per-page-select').forEach(function (select) {
            select.addEventListener('change', function () {
                var newSize = select.value;
                var href = window.location.href;
                if (window.URL) {
                    try {
                        var url = new URL(href);
                        url.searchParams.set('per_page', newSize);
                        url.searchParams.delete('page'); // Reset về trang 1
                        window.location.href = url.toString();
                        return;
                    } catch (e) {}
                }
                var sep = href.indexOf('?') === -1 ? '?' : '&';
                window.location.href = href + sep + 'per_page=' + encodeURIComponent(newSize);
            });
        });
    }

    /** Modal thiết lập mã lucky thủ công và toggle hoán đổi khi trùng */
    function bindSetLuckyModal(config) {
        var modalElement = document.getElementById('modal_set_lucky');
        if (!modalElement) {
            return;
        }

        var bsModal = null;
        var setLuckyUrl = config.getAttribute('data-set-lucky-url');
        var checkLuckyUrl = config.getAttribute('data-check-lucky-url');
        var eventId = config.getAttribute('data-event-id');

        var targetNameEl = document.getElementById('set_lucky_target_name');
        var targetUnitEl = document.getElementById('set_lucky_target_unit');
        var targetCurrentEl = document.getElementById('set_lucky_target_current');
        var idInput = document.getElementById('set_lucky_id');
        var currentValInput = document.getElementById('set_lucky_current_val');
        var luckyInput = document.getElementById('set_lucky_input');
        var statusBox = document.getElementById('set_lucky_status_box');
        var submitBtn = document.getElementById('btn_submit_lucky');
        var submitText = document.getElementById('btn_submit_lucky_text');

        var checkTimeout = null;
        var currentTarget = null;

        // Bắt sự kiện click nút gán / đổi mã lucky
        document.querySelectorAll('.js-set-lucky').forEach(function (button) {
            button.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();

                currentTarget = {
                    id: button.getAttribute('data-roster-id'),
                    fullName: button.getAttribute('data-full-name') || '',
                    unit: button.getAttribute('data-unit') || '',
                    lucky: button.getAttribute('data-lucky') || ''
                };

                targetNameEl.textContent = currentTarget.fullName;
                targetUnitEl.textContent = currentTarget.unit || '-';
                idInput.value = currentTarget.id;
                currentValInput.value = currentTarget.lucky;

                if (currentTarget.lucky) {
                    targetCurrentEl.innerHTML = '<span class="badge bg-success text-white fw-bold"><i class="fa fa-ticket me-1"></i>' + escapeHtml(currentTarget.lucky) + '</span>';
                    luckyInput.value = currentTarget.lucky;
                } else {
                    targetCurrentEl.innerHTML = '<span class="badge bg-secondary">Chưa cấp</span>';
                    luckyInput.value = '';
                }

                statusBox.className = 'mb-3 d-none';
                statusBox.innerHTML = '';
                submitText.textContent = 'Lưu mã lucky';

                if (!bsModal) {
                    bsModal = new bootstrap.Modal(modalElement);
                }
                bsModal.show();

                setTimeout(function () {
                    luckyInput.focus();
                    luckyInput.select();
                }, 350);
            });
        });

        // Kiểm tra real-time với debounce
        function checkLuckyDebounced() {
            if (checkTimeout) {
                clearTimeout(checkTimeout);
            }

            var val = (luckyInput.value || '').trim();
            if (!val) {
                statusBox.className = 'mb-3 d-none';
                statusBox.innerHTML = '';
                submitText.textContent = 'Lưu mã lucky';
                return;
            }

            // Chuẩn hoá sơ bộ để check (nếu số 1-4 chữ số)
            var checkVal = val;
            if (/^\d{1,4}$/.test(val) && parseInt(val, 10) > 0) {
                checkVal = ('0000' + parseInt(val, 10)).slice(-4);
            }

            var currentVal = (currentValInput.value || '').trim();
            if (checkVal === currentVal) {
                statusBox.className = 'mb-3';
                statusBox.innerHTML = '<div class="alert alert-info py-2 px-3 small mb-0"><i class="fa fa-info-circle me-1"></i> Đây là mã hiện tại của người này.</div>';
                submitText.textContent = 'Giữ nguyên mã';
                return;
            }

            statusBox.className = 'mb-3';
            statusBox.innerHTML = '<div class="text-muted small"><i class="fa fa-spinner fa-spin me-1"></i> Đang kiểm tra mã ' + escapeHtml(checkVal) + '...</div>';

            checkTimeout = setTimeout(function () {
                var url = checkLuckyUrl
                    + (checkLuckyUrl.indexOf('?') === -1 ? '?' : '&')
                    + 'event_id=' + encodeURIComponent(eventId)
                    + '&lucky_number=' + encodeURIComponent(checkVal)
                    + '&exclude_id=' + encodeURIComponent(idInput.value);

                fetch(url, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    credentials: 'same-origin'
                })
                    .then(function (res) { return res.json(); })
                    .then(function (res) {
                        if (!res.success || !res.data) {
                            statusBox.className = 'mb-3 d-none';
                            return;
                        }

                        var data = res.data;
                        if (data.exists && data.owner) {
                            var owner = data.owner;
                            var ownerDesc = escapeHtml(owner.full_name);
                            if (owner.property_name) {
                                ownerDesc += ' (' + escapeHtml(owner.property_name) + ')';
                            }
                            if (owner.is_withdrawn) {
                                ownerDesc += ' [Đã huỷ tư cách]';
                            }

                            statusBox.className = 'mb-3';
                            statusBox.innerHTML = '<div class="alert alert-warning py-2 px-3 small mb-0 border-0" style="background: rgba(245, 158, 11, 0.15); color: #92400e;">'
                                + '<div class="d-flex align-items-start gap-2">'
                                + '<i class="fa fa-exchange fa-lg mt-1 text-warning"></i>'
                                + '<div>'
                                + 'Mã <strong>' + escapeHtml(owner.lucky_number) + '</strong> đang thuộc về <strong>' + ownerDesc + '</strong>.<br>'
                                + 'Khi xác nhận, hệ thống sẽ <strong>hoán đổi mã (toggle/swap)</strong> giữa 2 người.'
                                + '</div></div></div>';
                            submitText.innerHTML = '<i class="fa fa-exchange me-1"></i> Hoán đổi mã';
                        } else {
                            statusBox.className = 'mb-3';
                            statusBox.innerHTML = '<div class="alert alert-success py-2 px-3 small mb-0 border-0" style="background: rgba(16, 185, 129, 0.12); color: #065f46;">'
                                + '<i class="fa fa-check-circle me-1"></i> Mã <strong>' + escapeHtml(checkVal) + '</strong> đang trống, có thể gán trực tiếp.'
                                + '</div>';
                            submitText.textContent = 'Gán mã lucky';
                        }
                    })
                    .catch(function () {
                        statusBox.className = 'mb-3 d-none';
                    });
            }, 250);
        }

        luckyInput.addEventListener('input', checkLuckyDebounced);

        // Submit form
        submitBtn.addEventListener('click', function () {
            var val = (luckyInput.value || '').trim();
            if (!val) {
                if (typeof Toast !== 'undefined') {
                    Toast.error('Vui lòng nhập mã lucky.');
                }
                luckyInput.focus();
                return;
            }

            var body = new FormData();
            body.append('id', idInput.value);
            body.append('lucky_number', val);

            postWithButton(setLuckyUrl, body, submitBtn, function (data) {
                if (bsModal) {
                    bsModal.hide();
                }

                if (typeof Toast !== 'undefined') {
                    Toast.success(data.message || 'Đã cập nhật mã lucky.');
                }

                // Tải lại trang sau 600ms để đồng bộ toàn bộ KPI cards, filter counts và bảng
                window.setTimeout(function () {
                    window.location.reload();
                }, 600);
            });
        });

        // Bấm Enter trong input để submit
        luckyInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                submitBtn.click();
            }
        });
    }

    function escapeHtml(text) {
        if (text === null || text === undefined) {
            return '';
        }
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
})();
