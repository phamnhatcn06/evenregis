/**
 * Thay thế / Huỷ tư cách người tham dự — màn hình phê duyệt đăng ký.
 * Slice 1: mở modal + nạp bản kê nội dung (đội thể thao, cuộc thi, vai trò) read-only.
 */
(function () {
    'use strict';

    var config = document.getElementById('attendee-actions-config');
    if (!config) {
        return;
    }
    var summaryUrl = config.getAttribute('data-summary-url');
    var withdrawUrl = config.getAttribute('data-withdraw-url');
    var replaceUrl = config.getAttribute('data-replace-url');

    /**
     * Dựng HTML danh sách nội dung tham gia từ dữ liệu summary.
     */
    function teamMetaBadges(t) {
        var meta = [];
        if (t.jersey_number) { meta.push('Số áo ' + t.jersey_number); }
        if (t.position) { meta.push(escapeHtml(t.position)); }
        if (parseInt(t.is_captain, 10)) { meta.push('<span class="badge bg-warning text-dark">Đội trưởng</span>'); }
        if (parseInt(t.is_alliance, 10)) { meta.push('<span class="badge bg-info text-dark">Liên quân</span>'); }
        return meta;
    }

    // Đội thể thao — bản chỉ đọc (dùng cho modal thay thế).
    function renderTeamsReadonly(teams) {
        var html = '<div class="mb-3"><h6 class="mb-1"><i class="fa fa-futbol-o me-1 text-primary"></i>Đội thể thao</h6>';
        if (teams.length) {
            html += '<ul class="list-group list-group-flush">';
            teams.forEach(function (t) {
                var meta = teamMetaBadges(t);
                html += '<li class="list-group-item px-0 py-1">'
                    + '<strong>' + escapeHtml(t.sport_name || t.team_name || '') + '</strong> '
                    + '<small class="text-muted">' + escapeHtml(t.team_name || '') + '</small>'
                    + (meta.length ? '<br><small>' + meta.join(' · ') + '</small>' : '')
                    + '</li>';
            });
            html += '</ul>';
        } else {
            html += '<p class="text-muted small mb-0">Không tham gia đội nào.</p>';
        }
        return html + '</div>';
    }

    // Đội thể thao — bản tương tác (modal huỷ): checkbox huỷ cả đội + chọn captain mới.
    function renderTeamsInteractive(teams) {
        var html = '<div class="mb-3"><h6 class="mb-1"><i class="fa fa-futbol-o me-1 text-primary"></i>Đội thể thao</h6>';
        if (!teams.length) {
            return html + '<p class="text-muted small mb-0">Không tham gia đội nào.</p></div>';
        }
        teams.forEach(function (t) {
            var meta = teamMetaBadges(t);
            var tid = t.sport_team_id;
            html += '<div class="border rounded p-2 mb-2">'
                + '<div class="d-flex justify-content-between align-items-start">'
                + '<div><strong>' + escapeHtml(t.sport_name || t.team_name || '') + '</strong> '
                + '<small class="text-muted">' + escapeHtml(t.team_name || '') + '</small>'
                + '<br><small>' + meta.join(' · ') + ' · ' + (t.member_count || 0) + ' thành viên</small></div>'
                + '<div class="form-check">'
                + '<input class="form-check-input withdraw-cancel-team" type="checkbox" name="cancel_team_ids[]" value="' + tid + '" id="cancel_team_' + tid + '">'
                + '<label class="form-check-label small text-danger" for="cancel_team_' + tid + '">Huỷ cả đội</label>'
                + '</div></div>';

            // Nếu người này là đội trưởng: cho chọn captain mới (khi không huỷ đội)
            if (parseInt(t.is_captain, 10)) {
                var others = t.other_members || [];
                html += '<div class="mt-2 captain-block" data-team="' + tid + '">'
                    + '<label class="form-label small mb-1 text-warning"><i class="fa fa-exclamation-triangle"></i> Người này là đội trưởng — chọn đội trưởng mới:</label>'
                    + '<select class="form-select form-select-sm" name="new_captain[' + tid + ']">'
                    + '<option value="">-- Để trống (đội tạm không có đội trưởng) --</option>';
                others.forEach(function (m) {
                    html += '<option value="' + m.member_id + '">' + escapeHtml(m.attendee_name || ('#' + m.attendee_id)) + '</option>';
                });
                html += '</select></div>';
            }
            html += '</div>';
        });
        return html + '</div>';
    }

    // Cuộc thi + vai trò (chung cho cả 2 modal).
    function renderCompsRoles(summary) {
        var html = '';
        var comps = summary.competitions || [];
        html += '<div class="mb-3"><h6 class="mb-1"><i class="fa fa-trophy me-1 text-warning"></i>Thi nghiệp vụ</h6>';
        if (comps.length) {
            html += '<ul class="list-group list-group-flush">';
            comps.forEach(function (c) {
                html += '<li class="list-group-item px-0 py-1">'
                    + escapeHtml(c.competition_name || '')
                    + (c.candidate_number ? ' <span class="badge bg-secondary">SBD ' + escapeHtml(c.candidate_number) + '</span>' : '')
                    + '</li>';
            });
            html += '</ul>';
        } else {
            html += '<p class="text-muted small mb-0">Không đăng ký cuộc thi nào.</p>';
        }
        html += '</div>';

        var roles = summary.roles || [];
        html += '<div><h6 class="mb-1"><i class="fa fa-id-badge me-1 text-success"></i>Vai trò</h6>';
        if (roles.length) {
            html += roles.map(function (r) {
                return '<span class="badge bg-primary me-1 mb-1">' + escapeHtml(r.role_name || '') + '</span>';
            }).join('');
        } else {
            html += '<p class="text-muted small mb-0">Không có vai trò.</p>';
        }
        html += '</div>';

        return html;
    }

    // Bản chỉ đọc cho modal thay thế.
    function renderSummary(summary) {
        return renderTeamsReadonly(summary.sport_teams || []) + renderCompsRoles(summary);
    }

    // Đội thể thao cho modal thay thế: checkbox kế thừa (mặc định tích).
    function renderTeamsForReplace(teams) {
        var html = '<div class="mb-3"><h6 class="mb-1"><i class="fa fa-futbol-o me-1 text-primary"></i>Đội thể thao</h6>';
        if (!teams.length) {
            return html + '<p class="text-muted small mb-0">Không tham gia đội nào.</p></div>';
        }
        teams.forEach(function (t) {
            var meta = teamMetaBadges(t);
            var tid = t.sport_team_id;
            html += '<div class="form-check border rounded p-2 mb-1">'
                + '<input class="form-check-input" type="checkbox" name="inherit_team_ids[]" value="' + tid + '" id="inherit_team_' + tid + '" checked>'
                + '<label class="form-check-label" for="inherit_team_' + tid + '">'
                + '<strong>' + escapeHtml(t.sport_name || t.team_name || '') + '</strong> '
                + '<small class="text-muted">' + escapeHtml(t.team_name || '') + '</small>'
                + (meta.length ? '<br><small>' + meta.join(' · ') + '</small>' : '')
                + '</label></div>';
        });
        return html + '<small class="text-muted">Bỏ tích = huỷ cả đội.</small></div>';
    }

    function renderReplaceContent(summary) {
        return renderTeamsForReplace(summary.sport_teams || []) + renderCompsRoles(summary);
    }

    // Preview file cho modal thay thế (dùng cho onchange inline).
    window.replacePreviewFile = function (input, previewId) {
        var preview = document.getElementById(previewId);
        if (!preview || !input.files || !input.files[0]) { return; }
        var file = input.files[0];
        var isPdf = file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf');
        if (isPdf) {
            preview.innerHTML = '<i class="fa fa-file-pdf-o fa-2x text-danger"></i><div class="small text-muted">' + escapeHtml(file.name) + '</div>';
        } else {
            var reader = new FileReader();
            reader.onload = function (e) {
                preview.innerHTML = '<img src="' + e.target.result + '" style="max-height:60px;border-radius:4px;">';
            };
            reader.readAsDataURL(file);
        }
    };

    // Bản tương tác cho modal huỷ tư cách.
    function renderWithdrawContent(summary) {
        return renderTeamsInteractive(summary.sport_teams || []) + renderCompsRoles(summary);
    }

    function escapeHtml(str) {
        return String(str == null ? '' : str)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

    /**
     * Tải summary và đổ vào 2 vùng (container + tên + cảnh báo thẻ).
     */
    function loadSummary(attendeeId, containerId, nameId, badgeWarnId, positionId, renderFn) {
        var container = document.getElementById(containerId);
        container.innerHTML = '<div class="text-center text-muted py-3"><i class="fa fa-spinner fa-spin me-1"></i>Đang tải...</div>';

        fetch(summaryUrl + '?attendee_id=' + encodeURIComponent(attendeeId), {
            headers: { 'Accept': 'application/json' }
        })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (!data.success) {
                    container.innerHTML = '<p class="text-danger">' + escapeHtml(data.error || 'Không tải được dữ liệu.') + '</p>';
                    return;
                }
                var att = data.attendee || {};
                if (nameId) {
                    var nameEl = document.getElementById(nameId);
                    if (nameEl) { nameEl.textContent = att.full_name || ''; }
                }
                if (positionId) {
                    var posEl = document.getElementById(positionId);
                    if (posEl) {
                        var parts = [];
                        if (att.division_name) { parts.push(att.division_name); }
                        if (att.position_name) { parts.push(att.position_name); }
                        posEl.textContent = parts.join(' - ') || '-';
                    }
                }
                if (badgeWarnId && parseInt(att.badge_printed, 10)) {
                    document.getElementById(badgeWarnId).classList.remove('d-none');
                }
                container.innerHTML = (renderFn || renderSummary)(data.summary || {});
            })
            .catch(function () {
                container.innerHTML = '<p class="text-danger">Lỗi kết nối máy chủ.</p>';
            });
    }

    function showModal(id) {
        var el = document.getElementById(id);
        if (el && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(el).show();
        }
    }

    window.openWithdrawAttendeeModal = function (attendeeId) {
        document.getElementById('withdraw_attendee_id').value = attendeeId;
        document.getElementById('withdraw_reason').value = '';
        document.getElementById('withdraw_badge_warning').classList.add('d-none');
        loadSummary(attendeeId, 'withdraw_summary_container', 'withdraw_attendee_name', 'withdraw_badge_warning', null, renderWithdrawContent);
        showModal('withdrawAttendeeModal');
    };

    var checkStaffUrl = config.getAttribute('data-check-staff-url');

    // Danh sách các trường file và ID hidden input tương ứng.
    var fileUrlFields = [
        { key: 'portrait',    attr: 'portrait_path',    previewId: 'replace_portrait_preview',    hiddenId: 'replace_existing_portrait_url' },
        { key: 'cccd_front',  attr: 'cccd_front_path',  previewId: 'replace_cccd_front_preview',  hiddenId: 'replace_existing_cccd_front_url' },
        { key: 'cccd_back',   attr: 'cccd_back_path',   previewId: 'replace_cccd_back_preview',   hiddenId: 'replace_existing_cccd_back_url' },
        { key: 'contract',    attr: 'contract_path',    previewId: 'replace_contract_preview',    hiddenId: 'replace_existing_contract_url' },
    ];

    function clearExistingFilePreviews() {
        fileUrlFields.forEach(function (f) {
            var hiddenEl = document.getElementById(f.hiddenId);
            if (hiddenEl) { hiddenEl.value = ''; }
            var previewEl = document.getElementById(f.previewId);
            if (previewEl) { previewEl.innerHTML = ''; }
        });
    }

    function applyExistingFileUrl(fileInfo, url) {
        // Lưu URL vào hidden input để backend nhận
        var hiddenEl = document.getElementById(fileInfo.hiddenId);
        if (hiddenEl) { hiddenEl.value = url; }
        // Hiển thị preview
        var previewEl = document.getElementById(fileInfo.previewId);
        if (!previewEl) { return; }
        var isPdf = url.toLowerCase().endsWith('.pdf');
        if (isPdf) {
            previewEl.innerHTML = '<i class="fa fa-file-pdf-o fa-2x text-danger"></i><br><span class="badge bg-success mt-1"><i class="fa fa-check"></i> File từ hồ sơ trước</span>';
        } else {
            previewEl.innerHTML = '<img src="' + escapeHtml(url) + '" style="max-height:60px;border-radius:4px;"><br><span class="badge bg-success mt-1"><i class="fa fa-check"></i> File từ hồ sơ trước</span>';
        }
    }

    function checkExistingAttendee(staffId, idCard) {
        var alertBox = document.getElementById('replace_existing_alert');
        var existingAttIdEl = document.getElementById('replace_existing_attendee_id');

        if (!staffId && (!idCard || !idCard.trim())) {
            if (alertBox) { alertBox.classList.add('d-none'); }
            if (existingAttIdEl) { existingAttIdEl.value = ''; }
            clearExistingFilePreviews();
            return;
        }

        var url = checkStaffUrl + '?staff_id=' + encodeURIComponent(staffId || '') + '&id_card=' + encodeURIComponent(idCard || '');
        fetch(url, { headers: { 'Accept': 'application/json' } })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.success && data.has_attendee && data.attendee) {
                    var att = data.attendee;
                    if (existingAttIdEl) { existingAttIdEl.value = att.id || ''; }

                    // Điền thông tin cá nhân vào form (tab thủ công) nếu các ô còn trống
                    var prefilled = [];
                    if (att.full_name) {
                        var fullNameInput = document.getElementById('replace_full_name');
                        if (fullNameInput && !fullNameInput.value) {
                            fullNameInput.value = att.full_name;
                            prefilled.push('họ tên');
                        }
                    }
                    if (att.position) {
                        var positionInput = document.getElementById('replace_position');
                        if (positionInput && !positionInput.value) {
                            positionInput.value = att.position;
                            prefilled.push('chức danh');
                        }
                    }
                    if (att.id_card) {
                        var idCardInputEl = document.getElementById('replace_id_card');
                        if (idCardInputEl && !idCardInputEl.value) {
                            idCardInputEl.value = att.id_card;
                            prefilled.push('CCCD');
                        }
                    }

                    // Set URL file vào hidden inputs + hiển thị preview
                    var filesLoaded = [];
                    fileUrlFields.forEach(function (f) {
                        var fileUrl = att[f.attr] || '';
                        if (fileUrl) {
                            applyExistingFileUrl(f, fileUrl);
                            if (f.key === 'portrait') { filesLoaded.push('ảnh chân dung'); }
                            else if (f.key === 'cccd_front') { filesLoaded.push('CCCD trước'); }
                            else if (f.key === 'cccd_back')  { filesLoaded.push('CCCD sau'); }
                            else if (f.key === 'contract')   { filesLoaded.push('hợp đồng'); }
                        } else {
                            // Xoá URL cũ nếu attendee mới không có file này
                            var hiddenEl = document.getElementById(f.hiddenId);
                            if (hiddenEl) { hiddenEl.value = ''; }
                        }
                    });

                    // Cập nhật nội dung alert box
                    if (alertBox) {
                        var alertMsg = '<i class="fa fa-check-circle me-1"></i> <strong>Đã tìm thấy hồ sơ người tham dự trước đó!</strong>';
                        if (prefilled.length > 0) {
                            alertMsg += ' Đã tự động điền: <strong>' + prefilled.join(', ') + '</strong>.';
                        }
                        if (filesLoaded.length > 0) {
                            alertMsg += ' File dùng lại: <strong>' + filesLoaded.join(', ') + '</strong>.';
                        }
                        if (prefilled.length === 0 && filesLoaded.length === 0) {
                            alertMsg += ' Hồ sơ chưa có ảnh/tài liệu đính kèm.';
                        }
                        alertBox.innerHTML = alertMsg;
                        alertBox.classList.remove('d-none');
                    }

                } else {
                    if (alertBox) { alertBox.classList.add('d-none'); }
                    if (existingAttIdEl) { existingAttIdEl.value = ''; }
                    clearExistingFilePreviews();
                }
            })
            .catch(function () {
                if (alertBox) { alertBox.classList.add('d-none'); }
            });
    }

    window.openReplaceAttendeeModal = function (attendeeId) {
        var form = document.getElementById('replaceAttendeeForm');
        form.reset();
        document.getElementById('replace_attendee_id').value = attendeeId;
        document.getElementById('replace_staff_id').value = '';
        var alertBox = document.getElementById('replace_existing_alert');
        if (alertBox) { alertBox.classList.add('d-none'); }
        var existingAttIdEl = document.getElementById('replace_existing_attendee_id');
        if (existingAttIdEl) { existingAttIdEl.value = ''; }

        // Reset tất cả hidden URL + preview file
        clearExistingFilePreviews();

        // Reset staff info preview box
        var infoBox = document.getElementById('replace_staff_info');
        if (infoBox) { infoBox.classList.add('d-none'); }

        if (window.jQuery && jQuery.fn.select2) {
            jQuery('#replace_staff_select').val('').trigger('change.select2');
        }
        document.getElementById('replace_badge_warning').classList.add('d-none');
        loadSummary(attendeeId, 'replace_summary_container', 'replace_attendee_name', 'replace_badge_warning', 'replace_attendee_position', renderReplaceContent);
        showModal('replaceAttendeeModal');
    };

    // Đồng bộ nhân sự SMILE đã chọn vào hidden staff_id & kiểm tra attendee đã có.
    var staffSelect = document.getElementById('replace_staff_select');
    var $staffSelect = null;

    function updateStaffInfoBox(selectEl) {
        var infoBox = document.getElementById('replace_staff_info');
        var infoName = document.getElementById('replace_staff_info_name');
        var infoPos  = document.getElementById('replace_staff_info_position');
        if (!infoBox) { return; }
        var val = selectEl.value;
        if (!val) {
            infoBox.classList.add('d-none');
            if (infoName) { infoName.textContent = ''; }
            if (infoPos)  { infoPos.textContent  = ''; }
            return;
        }
        var opt = selectEl.options[selectEl.selectedIndex];
        var name = opt ? (opt.getAttribute('data-name') || '') : '';
        var pos  = opt ? (opt.getAttribute('data-position') || '') : '';
        if (infoName) { infoName.textContent = name; }
        if (infoPos)  { infoPos.textContent  = pos; }
        infoBox.classList.remove('d-none');
    }

    if (staffSelect) {
    // Xoá các trường đã tự điền từ lần chọn trước để tránh rọc nhầm dữ liệu.
    function clearAutofilledFields() {
        var fields = ['replace_full_name', 'replace_position', 'replace_id_card'];
        fields.forEach(function (id) {
            var el = document.getElementById(id);
            if (el) { el.value = ''; }
        });
        clearExistingFilePreviews();
        var existingAttIdEl = document.getElementById('replace_existing_attendee_id');
        if (existingAttIdEl) { existingAttIdEl.value = ''; }
        var alertBox = document.getElementById('replace_existing_alert');
        if (alertBox) { alertBox.classList.add('d-none'); }
    }

    // Select2 cho ô chọn nhân sự (hỗ trợ gõ tìm kiếm) — gắn dropdown vào modal.
        if (window.jQuery && jQuery.fn.select2) {
            $staffSelect = jQuery(staffSelect);
            $staffSelect.select2({
                dropdownParent: jQuery('#replaceAttendeeModal'),
                width: '100%',
                placeholder: '-- Chọn nhân sự --',
                allowClear: true,
                language: {
                    noResults: function () { return 'Không tìm thấy nhân sự'; },
                    searching: function () { return 'Đang tìm...'; }
                }
            });
            $staffSelect.on('change', function () {
                document.getElementById('replace_staff_id').value = this.value;
                updateStaffInfoBox(staffSelect);
                clearAutofilledFields(); // xóa dữ liệu cũ trước khi tra cứu mới
                checkExistingAttendee(this.value, '');
            });
        } else {
            staffSelect.addEventListener('change', function () {
                document.getElementById('replace_staff_id').value = this.value;
                updateStaffInfoBox(staffSelect);
                clearAutofilledFields(); // xóa dữ liệu cũ trước khi tra cứu mới
                checkExistingAttendee(this.value, '');
            });
        }
    }

    var idCardInput = document.getElementById('replace_id_card');
    if (idCardInput) {
        idCardInput.addEventListener('blur', function () {
            var staffId = document.getElementById('replace_staff_id').value;
            checkExistingAttendee(staffId, this.value);
        });
    }

    // Huỷ tư cách (Slice 2): xác nhận bằng SweetAlert rồi gửi POST.
    var withdrawForm = document.getElementById('withdrawAttendeeForm');
    if (withdrawForm) {
        withdrawForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var reason = document.getElementById('withdraw_reason').value.trim();
            if (!reason) {
                Toast.error('Vui lòng nhập lý do huỷ tư cách.');
                return;
            }
            var name = document.getElementById('withdraw_attendee_name').textContent || 'người này';
            Swal.fire({
                title: 'Xác nhận huỷ tư cách',
                html: 'Huỷ tư cách <strong>' + escapeHtml(name) + '</strong> và gỡ khỏi các nội dung tham gia?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Huỷ tư cách',
                cancelButtonText: 'Đóng'
            }).then(function (result) {
                if (result.isConfirmed) {
                    submitWithdraw();
                }
            });
        });
    }

    function submitWithdraw() {
        var btn = document.getElementById('btn_submit_withdraw');
        var originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i>Đang xử lý...';

        var formData = new FormData(document.getElementById('withdrawAttendeeForm'));
        fetch(withdrawUrl, { method: 'POST', body: formData })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.success) {
                    var modalEl = document.getElementById('withdrawAttendeeModal');
                    var modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) { modal.hide(); }
                    Toast.success(data.message || 'Đã huỷ tư cách.');
                    setTimeout(function () { location.reload(); }, 1200);
                } else {
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                    Toast.error(data.error || 'Có lỗi xảy ra.');
                }
            })
            .catch(function () {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
                Toast.error('Lỗi kết nối máy chủ.');
            });
    }

    // Thay thế (Slice 4): validate → xác nhận → POST.
    var replaceForm = document.getElementById('replaceAttendeeForm');
    if (replaceForm) {
        replaceForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var staffId = document.getElementById('replace_staff_id').value;
            var fullName = document.getElementById('replace_full_name').value.trim();
            var reason = document.getElementById('replace_reason').value.trim();
            var portrait = replaceForm.querySelector('input[name="portrait_file"]').files[0];
            var existingPortraitUrl = (document.getElementById('replace_existing_portrait_url') || {}).value || '';

            if (!staffId && !fullName) {
                Toast.error('Vui lòng chọn nhân sự SMILE hoặc nhập họ tên người thay.');
                return;
            }
            if (!reason) {
                Toast.error('Vui lòng nhập lý do thay thế.');
                return;
            }
            // Ảnh chân dung chỉ bắt buộc khi nhập thủ công (không có staff_id từ SMILE)
            if (!staffId && !portrait && !existingPortraitUrl) {
                Toast.error('Vui lòng chọn ảnh chân dung người thay.');
                return;
            }
            Swal.fire({
                title: 'Xác nhận thay thế',
                text: 'Thực hiện thay thế người tham dự? Người bị thay sẽ bị huỷ tư cách.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Thực hiện',
                cancelButtonText: 'Đóng'
            }).then(function (result) {
                if (result.isConfirmed) {
                    submitReplace();
                }
            });
        });
    }

    function submitReplace() {
        var btn = document.getElementById('btn_submit_replace');
        var originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i>Đang xử lý...';

        var formData = new FormData(document.getElementById('replaceAttendeeForm'));
        fetch(replaceUrl, { method: 'POST', body: formData })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.success) {
                    var modal = bootstrap.Modal.getInstance(document.getElementById('replaceAttendeeModal'));
                    if (modal) { modal.hide(); }
                    Toast.success(data.message || 'Đã thay thế người tham dự.');
                    setTimeout(function () { location.reload(); }, 1200);
                } else {
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                    Toast.error(data.error || 'Có lỗi xảy ra.');
                }
            })
            .catch(function () {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
                Toast.error('Lỗi kết nối máy chủ.');
            });
    }
})();
