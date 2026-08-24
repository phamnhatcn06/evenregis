/**
 * Thay thế / Huỷ tư cách người tham dự — màn hình phê duyệt đăng ký.
 *
 * Thay thế (đa nội dung): thêm nhiều người thay trong 1 thao tác, gán từng nội dung
 * của người bị thay cho người thay tương ứng (hoặc đánh dấu huỷ). Khoá nút Lưu tới khi
 * mọi nội dung đã có quyết định.
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
    var cancelContentUrl = config.getAttribute('data-cancel-content-url');
    var checkStaffUrl = config.getAttribute('data-check-staff-url');
    var attendeeProfileUrl = config.getAttribute('data-attendee-profile-url');

    function escapeHtml(str) {
        return String(str == null ? '' : str)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

    function teamMetaBadges(t) {
        var meta = [];
        if (t.jersey_number) { meta.push('Số áo ' + escapeHtml(t.jersey_number)); }
        if (t.position) { meta.push(escapeHtml(t.position)); }
        if (parseInt(t.is_captain, 10)) { meta.push('<span class="badge bg-warning text-dark">Đội trưởng</span>'); }
        if (parseInt(t.is_alliance, 10)) { meta.push('<span class="badge bg-info text-dark">Liên quân</span>'); }
        return meta;
    }

    // Preview file (dùng chung, gọi từ onchange inline của các card người thay).
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
                preview.innerHTML = '<img src="' + e.target.result + '" style="max-height:52px;border-radius:4px;">';
            };
            reader.readAsDataURL(file);
        }
    };

    function showModal(id) {
        var el = document.getElementById(id);
        if (el && window.bootstrap) {
            bootstrap.Modal.getOrCreateInstance(el).show();
        }
    }

    // =====================================================================
    // HUỶ TƯ CÁCH (giữ nguyên hành vi cũ)
    // =====================================================================

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

    // Cuộc thi + Miss + vai trò (chỉ đọc) — dùng cho modal huỷ tư cách.
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

        var contests = summary.beauty_contests || [];
        html += '<div class="mb-3"><h6 class="mb-1"><i class="fa fa-star me-1 text-danger"></i>Thi Miss</h6>';
        if (contests.length) {
            html += '<ul class="list-group list-group-flush">';
            contests.forEach(function (c) {
                html += '<li class="list-group-item px-0 py-1">'
                    + escapeHtml(c.contest_name || '')
                    + (c.candidate_number ? ' <span class="badge bg-secondary">SBD ' + escapeHtml(c.candidate_number) + '</span>' : '')
                    + ' <span class="badge bg-danger">Sẽ huỷ</span>'
                    + '</li>';
            });
            html += '</ul>';
        } else {
            html += '<p class="text-muted small mb-0">Không đăng ký thi Miss.</p>';
        }
        html += '</div>';

        var talents = summary.talent_entries || [];
        html += '<div class="mb-3"><h6 class="mb-1"><i class="fa fa-music me-1 text-info"></i>Văn nghệ</h6>';
        if (talents.length) {
            html += '<ul class="list-group list-group-flush">';
            talents.forEach(function (te) {
                html += '<li class="list-group-item px-0 py-1">'
                    + escapeHtml(te.entry_title || '')
                    + (te.category_name ? ' <span class="badge bg-secondary">' + escapeHtml(te.category_name) + '</span>' : '')
                    + ' <span class="badge bg-danger">Sẽ huỷ</span>'
                    + '</li>';
            });
            html += '</ul>';
        } else {
            html += '<p class="text-muted small mb-0">Không đăng ký tiết mục văn nghệ.</p>';
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

    function renderWithdrawContent(summary) {
        return renderTeamsInteractive(summary.sport_teams || []) + renderCompsRoles(summary);
    }

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
                container.innerHTML = (renderFn || renderWithdrawContent)(data.summary || {});
            })
            .catch(function () {
                container.innerHTML = '<p class="text-danger">Lỗi kết nối máy chủ.</p>';
            });
    }

    window.openWithdrawAttendeeModal = function (attendeeId) {
        document.getElementById('withdraw_attendee_id').value = attendeeId;
        document.getElementById('withdraw_reason').value = '';
        document.getElementById('withdraw_badge_warning').classList.add('d-none');
        loadSummary(attendeeId, 'withdraw_summary_container', 'withdraw_attendee_name', 'withdraw_badge_warning', null, renderWithdrawContent);
        showModal('withdrawAttendeeModal');
    };

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
                if (result.isConfirmed) { submitWithdraw(); }
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

    // =====================================================================
    // HUỶ NỘI DUNG THI ĐẤU (thể thao) — giữ tư cách VĐV
    // =====================================================================

    function renderCancelTeams(teams) {
        var html = '<div class="mb-2"><h6 class="mb-2"><i class="fa fa-futbol-o me-1 text-primary"></i>Đội thi đấu đã đăng ký</h6>';
        if (!teams.length) {
            return html + '<p class="text-muted small mb-0">Người này không tham gia đội thể thao nào.</p></div>';
        }
        teams.forEach(function (t) {
            var meta = teamMetaBadges(t);
            var tid = t.sport_team_id;
            html += '<div class="border rounded p-2 mb-2">'
                + '<div class="form-check">'
                + '<input class="form-check-input cancel-team-select" type="checkbox" name="team_ids[]" value="' + tid + '" id="cancel_content_team_' + tid + '">'
                + '<label class="form-check-label" for="cancel_content_team_' + tid + '">'
                + '<strong>' + escapeHtml(t.sport_name || t.team_name || '') + '</strong> '
                + '<small class="text-muted">' + escapeHtml(t.team_name || '') + '</small>'
                + '<br><small>' + meta.join(' · ') + ' · ' + (t.member_count || 0) + ' thành viên</small>'
                + '</label></div>';

            html += '<div class="ms-4 mt-1 cancel-team-options d-none" data-team="' + tid + '">'
                + '<div class="form-check">'
                + '<input class="form-check-input" type="checkbox" name="cancel_whole_team[' + tid + ']" value="1" id="cancel_whole_' + tid + '">'
                + '<label class="form-check-label small text-danger" for="cancel_whole_' + tid + '">Huỷ cả đội (gỡ toàn bộ thành viên &amp; xoá đội)</label>'
                + '</div>';

            if (parseInt(t.is_captain, 10)) {
                var others = t.other_members || [];
                html += '<div class="mt-1">'
                    + '<label class="form-label small mb-1 text-warning"><i class="fa fa-exclamation-triangle"></i> Người này là đội trưởng — chọn đội trưởng mới:</label>'
                    + '<select class="form-select form-select-sm" name="new_captain[' + tid + ']">'
                    + '<option value="">-- Để trống (đội tạm không có đội trưởng) --</option>';
                others.forEach(function (m) {
                    html += '<option value="' + m.member_id + '">' + escapeHtml(m.attendee_name || ('#' + m.attendee_id)) + '</option>';
                });
                html += '</select></div>';
            }
            html += '</div></div>';
        });
        return html + '</div>';
    }

    // Chỉ hiện tuỳ chọn (huỷ cả đội / đội trưởng mới) khi đội được tick huỷ.
    document.addEventListener('change', function (e) {
        if (!e.target.classList || !e.target.classList.contains('cancel-team-select')) { return; }
        var options = e.target.closest('.border').querySelector('.cancel-team-options');
        if (options) { options.classList.toggle('d-none', !e.target.checked); }
    });

    window.openCancelContentModal = function (attendeeId) {
        document.getElementById('cancel_content_attendee_id').value = attendeeId;
        document.getElementById('cancel_content_reason').value = '';
        document.getElementById('cancel_content_badge_warning').classList.add('d-none');
        loadSummary(attendeeId, 'cancel_content_container', 'cancel_content_attendee_name', 'cancel_content_badge_warning', null, function (summary) {
            return renderCancelTeams(summary.sport_teams || []);
        });
        showModal('cancelContentModal');
    };

    var cancelContentForm = document.getElementById('cancelContentForm');
    if (cancelContentForm) {
        cancelContentForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var selected = cancelContentForm.querySelectorAll('.cancel-team-select:checked');
            if (!selected.length) {
                Toast.error('Vui lòng chọn ít nhất một đội cần huỷ.');
                return;
            }
            var reason = document.getElementById('cancel_content_reason').value.trim();
            if (!reason) {
                Toast.error('Vui lòng nhập lý do huỷ nội dung.');
                return;
            }
            var name = document.getElementById('cancel_content_attendee_name').textContent || 'người này';
            Swal.fire({
                title: 'Xác nhận huỷ nội dung',
                html: 'Huỷ <strong>' + selected.length + '</strong> nội dung thi đấu của <strong>' + escapeHtml(name) + '</strong>?'
                    + '<br><small class="text-muted">Nếu hết nội dung, VĐV sẽ tự động bị huỷ tư cách.</small>',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f0ad4e',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Huỷ nội dung',
                cancelButtonText: 'Đóng'
            }).then(function (result) {
                if (result.isConfirmed) { submitCancelContent(); }
            });
        });
    }

    function submitCancelContent() {
        var btn = document.getElementById('btn_submit_cancel_content');
        var originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i>Đang xử lý...';

        var formData = new FormData(document.getElementById('cancelContentForm'));
        fetch(cancelContentUrl, { method: 'POST', body: formData })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.success) {
                    var modalEl = document.getElementById('cancelContentModal');
                    var modal = bootstrap.Modal.getInstance(modalEl);
                    if (modal) { modal.hide(); }
                    Toast.success(data.message || 'Đã huỷ nội dung.');
                    setTimeout(function () { location.reload(); }, 1400);
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

    // =====================================================================
    // THAY THẾ ĐA NỘI DUNG
    // =====================================================================

    function readJson(id) {
        var el = document.getElementById(id);
        if (!el) { return null; }
        try { return JSON.parse(el.textContent || 'null'); } catch (e) { return null; }
    }
    var STAFF_LIST = readJson('replace_staff_list') || [];
    var OTHER_ATTENDEES = readJson('replace_other_attendees_list') || [];
    var ROLES_MAP = readJson('replace_roles_list') || {};

    var currentSummary = null;   // summary người bị thay đang mở
    var subSeq = 0;              // id duy nhất cho mỗi card (không lặp lại trong 1 lần mở)

    var FILE_FIELDS = [
        { k: 'portrait',   path: 'portrait_path',   label: 'Ảnh chân dung', req: true },
        { k: 'cccd_front', path: 'cccd_front_path', label: 'CCCD trước',    req: false },
        { k: 'cccd_back',  path: 'cccd_back_path',  label: 'CCCD sau',      req: false },
        { k: 'contract',   path: 'contract_path',   label: 'Hợp đồng',      req: false }
    ];

    function staffOptionsHtml() {
        var html = '<option value="">-- Chọn nhân sự SMILE --</option>';
        STAFF_LIST.forEach(function (st) {
            html += '<option value="' + escapeHtml(st.id) + '"'
                + ' data-name="' + escapeHtml(st.name) + '"'
                + ' data-code="' + escapeHtml(st.code) + '"'
                + ' data-position="' + escapeHtml(st.position) + '">'
                + escapeHtml(st.name + (st.code ? ' (' + st.code + ')' : '')) + '</option>';
        });
        return html;
    }

    function otherAttendeeOptionsHtml() {
        // Không cho chọn chính người đang bị thay.
        var oldId = (document.getElementById('replace_attendee_id') || {}).value || '';
        var html = '<option value="">-- Chọn người đã có trong danh sách --</option>';
        OTHER_ATTENDEES.forEach(function (a) {
            if (String(a.id) === String(oldId)) { return; }
            var label = a.name || ('#' + a.id);
            if (a.position) { label += ' · ' + a.position; }
            if (a.id_card) { label += ' · CCCD ' + a.id_card; }
            if (parseInt(a.in_current, 10)) { label += ' · (đăng ký hiện tại)'; }
            html += '<option value="' + escapeHtml(a.id) + '"'
                + ' data-name="' + escapeHtml(a.name || '') + '"'
                + ' data-position="' + escapeHtml(a.position || '') + '"'
                + ' data-idcard="' + escapeHtml(a.id_card || '') + '"'
                + ' data-incurrent="' + (parseInt(a.in_current, 10) ? '1' : '0') + '">'
                + escapeHtml(label) + '</option>';
        });
        return html;
    }

    function rolesOptionsHtml() {
        var html = '';
        Object.keys(ROLES_MAP).forEach(function (rid) {
            html += '<option value="' + escapeHtml(rid) + '">' + escapeHtml(ROLES_MAP[rid]) + '</option>';
        });
        return html;
    }

    // Danh sách card đang hoạt động, theo thứ tự DOM.
    function activeCards() {
        return Array.prototype.slice.call(document.querySelectorAll('#replace_subs_container .replace-sub-card'));
    }

    // Nhãn hiển thị của 1 card: "#k Tên".
    function cardName(card) {
        var staff = card.querySelector('.rsub-staff');
        if (staff && staff.value) {
            var opt = staff.options[staff.selectedIndex];
            var n = opt ? (opt.getAttribute('data-name') || opt.textContent) : '';
            if (n) { return n.trim(); }
        }
        var nameInput = card.querySelector('.rsub-name');
        return nameInput && nameInput.value ? nameInput.value.trim() : '';
    }

    function renumberCards() {
        activeCards().forEach(function (card, i) {
            var numEl = card.querySelector('.rsub-num');
            if (numEl) { numEl.textContent = '#' + (i + 1); }
        });
    }

    function buildSubCardHtml(idx) {
        var filesHtml = '';
        FILE_FIELDS.forEach(function (f) {
            filesHtml += '<div class="col-6 mb-2">'
                + '<label class="form-label small fw-bold mb-1">' + f.label + (f.req ? ' <span class="text-danger">*</span>' : '') + '</label>'
                + '<div id="rsub_prev_' + f.k + '_' + idx + '" class="border rounded text-center p-1 mb-1" style="min-height:44px;"></div>'
                + '<input type="file" class="form-control form-control-sm" name="sub_' + f.k + '_file_' + idx + '"'
                + ' accept="image/*' + (f.k === 'contract' ? ',.pdf' : '') + '"'
                + ' onchange="replacePreviewFile(this, \'rsub_prev_' + f.k + '_' + idx + '\')">'
                + '<input type="hidden" class="rsub-url" data-path="' + f.path + '" id="rsub_url_' + f.k + '_' + idx + '">'
                + '</div>';
        });

        return '<div class="border rounded p-2 mb-2 replace-sub-card" data-sub="' + idx + '">'
            + '<div class="d-flex justify-content-between align-items-center mb-2">'
            + '<strong class="small text-primary">Người thay <span class="rsub-num">#1</span></strong>'
            + '<button type="button" class="btn btn-sm btn-outline-danger py-0 px-1 rsub-remove" title="Xoá người thay này"><i class="fa fa-times"></i></button>'
            + '</div>'
            + '<div class="mb-2">'
            + '<select class="form-select form-select-sm rsub-staff" id="rsub_staff_' + idx + '">' + staffOptionsHtml() + '</select>'
            + '</div>'
            + (OTHER_ATTENDEES.length
                ? '<div class="mb-2">'
                    + '<select class="form-select form-select-sm rsub-other" id="rsub_other_' + idx + '">' + otherAttendeeOptionsHtml() + '</select>'
                    + '<small class="text-muted">Chọn người tham dự có sẵn của đơn vị (đăng ký hiện tại hoặc đăng ký khác). Người ở đăng ký hiện tại sẽ được dùng lại, không tạo bản ghi mới.</small>'
                    + '</div>'
                : '')
            + '<div class="row g-2 mb-2">'
            + '<div class="col-12"><input type="text" class="form-control form-control-sm rsub-name" placeholder="Hoặc nhập họ tên (thủ công)"></div>'
            + '<div class="col-6"><input type="text" class="form-control form-control-sm rsub-pos" placeholder="Chức danh"></div>'
            + '<div class="col-6"><input type="text" class="form-control form-control-sm rsub-idcard" placeholder="Số CCCD/CMND"></div>'
            + '</div>'
            + '<div class="mb-2">'
            + '<label class="form-label small mb-1">Vai trò</label>'
            + '<select class="form-select form-select-sm rsub-roles" multiple size="3">' + rolesOptionsHtml() + '</select>'
            + '<small class="text-muted">Giữ Ctrl để chọn nhiều.</small>'
            + '</div>'
            + '<div class="row g-2">' + filesHtml + '</div>'
            + '<div class="alert alert-success py-1 px-2 mt-1 small d-none rsub-alert"></div>'
            + '<input type="hidden" class="rsub-existing-id">'
            + '</div>';
    }

    function clearCardProfile(card) {
        card.querySelectorAll('.rsub-url').forEach(function (h) { h.value = ''; });
        FILE_FIELDS.forEach(function (f) {
            var idx = card.getAttribute('data-sub');
            var prev = document.getElementById('rsub_prev_' + f.k + '_' + idx);
            if (prev) { prev.innerHTML = ''; }
        });
        var eid = card.querySelector('.rsub-existing-id');
        if (eid) { eid.value = ''; }
        var alertBox = card.querySelector('.rsub-alert');
        if (alertBox) { alertBox.classList.add('d-none'); }
    }

    // Tra hồ sơ attendee đã có (theo staff/CCCD) để dùng lại ảnh + tự điền.
    function lookupExisting(card) {
        var staffSel = card.querySelector('.rsub-staff');
        var staffId = staffSel ? staffSel.value : '';
        var staffCode = '';
        if (staffSel && staffSel.value) {
            var selOpt = staffSel.options[staffSel.selectedIndex];
            staffCode = selOpt ? (selOpt.getAttribute('data-code') || '') : '';
        }
        var idCard = (card.querySelector('.rsub-idcard') || {}).value || '';
        var alertBox = card.querySelector('.rsub-alert');
        var eid = card.querySelector('.rsub-existing-id');

        if (!staffId && staffCode === '' && (!idCard || !idCard.trim())) {
            if (alertBox) { alertBox.classList.add('d-none'); }
            if (eid) { eid.value = ''; }
            return;
        }
        var regInput = document.querySelector('#replaceAttendeeForm input[name="registration_id"]');
        var regId = regInput ? regInput.value : '';
        // Ưu tiên khớp theo staff_code (đáng tin hơn staff_id trên bản ghi cũ).
        var url = checkStaffUrl + '?staff_id=' + encodeURIComponent(staffId || '')
            + '&staff_code=' + encodeURIComponent(staffCode || '')
            + '&id_card=' + encodeURIComponent(idCard || '')
            + '&registration_id=' + encodeURIComponent(regId || '');

        fetch(url, { headers: { 'Accept': 'application/json' } })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (!(data.success && data.has_attendee && data.attendee)) {
                    if (alertBox) { alertBox.classList.add('d-none'); }
                    if (eid) { eid.value = ''; }
                    return;
                }
                var att = data.attendee;
                if (eid) { eid.value = att.id || ''; }
                var idx = card.getAttribute('data-sub');
                var prefilled = [], filesLoaded = [];

                var nameInput = card.querySelector('.rsub-name');
                if (att.full_name && nameInput && !nameInput.value) { nameInput.value = att.full_name; prefilled.push('họ tên'); }
                var posInput = card.querySelector('.rsub-pos');
                if (att.position && posInput && !posInput.value) { posInput.value = att.position; prefilled.push('chức danh'); }
                var idInput = card.querySelector('.rsub-idcard');
                if (att.id_card && idInput && !idInput.value) { idInput.value = att.id_card; prefilled.push('CCCD'); }

                FILE_FIELDS.forEach(function (f) {
                    var fileUrl = att[f.path] || (f.k === 'portrait' ? att.photo_path : '') || '';
                    var hidden = document.getElementById('rsub_url_' + f.k + '_' + idx);
                    var prev = document.getElementById('rsub_prev_' + f.k + '_' + idx);
                    if (fileUrl) {
                        if (hidden) { hidden.value = fileUrl; }
                        if (prev) {
                            var isPdf = String(fileUrl).toLowerCase().endsWith('.pdf');
                            prev.innerHTML = isPdf
                                ? '<i class="fa fa-file-pdf-o fa-lg text-danger"></i><br><span class="badge bg-success">Hồ sơ cũ</span>'
                                : '<img src="' + escapeHtml(fileUrl) + '" style="max-height:44px;border-radius:4px;"><br><span class="badge bg-success">Hồ sơ cũ</span>';
                        }
                        filesLoaded.push(f.label);
                    } else if (hidden) {
                        hidden.value = '';
                    }
                });

                // Vai trò: tự chọn theo hồ sơ cũ nếu người dùng chưa chọn.
                var roleSel = card.querySelector('.rsub-roles');
                if (roleSel && att.role_id) {
                    var hasSel = Array.prototype.some.call(roleSel.options, function (o) { return o.selected; });
                    if (!hasSel) {
                        var ids = String(att.role_id).split(',').map(function (x) { return x.trim(); });
                        Array.prototype.forEach.call(roleSel.options, function (o) {
                            if (ids.indexOf(o.value) >= 0) { o.selected = true; }
                        });
                        prefilled.push('vai trò');
                    }
                }

                if (alertBox) {
                    if (data.in_registration) {
                        alertBox.className = 'alert alert-warning py-1 px-2 mt-1 small rsub-alert';
                        alertBox.innerHTML = '<i class="fa fa-info-circle me-1"></i><strong>Người này đã có trong đăng ký.</strong> '
                            + 'Sẽ dùng lại bản ghi hiện có và chỉ gán thêm nội dung (không tạo bản ghi mới; ảnh/vai trò nhập ở đây sẽ bỏ qua).';
                    } else {
                        alertBox.className = 'alert alert-success py-1 px-2 mt-1 small rsub-alert';
                        var msg = '<i class="fa fa-check-circle me-1"></i><strong>Đã tìm thấy hồ sơ cũ.</strong>';
                        if (prefilled.length) { msg += ' Điền: ' + prefilled.join(', ') + '.'; }
                        if (filesLoaded.length) { msg += ' Ảnh dùng lại: ' + filesLoaded.join(', ') + '.'; }
                        alertBox.innerHTML = msg;
                    }
                    alertBox.classList.remove('d-none');
                }
                refreshAssignOptions();
            })
            .catch(function () {
                if (alertBox) { alertBox.classList.add('d-none'); }
            });
    }

    // Đổi nhân sự SMILE: xoá dữ liệu tự điền cũ để lần tra mới điền lại đúng người.
    function onStaffChange(card) {
        clearCardProfile(card);
        ['.rsub-name', '.rsub-pos', '.rsub-idcard'].forEach(function (sel) {
            var el = card.querySelector(sel);
            if (el) { el.value = ''; }
        });
        var roleSel = card.querySelector('.rsub-roles');
        if (roleSel) { Array.prototype.forEach.call(roleSel.options, function (o) { o.selected = false; }); }
        // Bỏ chọn nguồn "đăng ký khác" (hai nguồn loại trừ nhau).
        var otherSel = card.querySelector('.rsub-other');
        if (otherSel && otherSel.value) {
            if (window.jQuery && jQuery.fn.select2) { jQuery(otherSel).val('').trigger('change.select2'); }
            else { otherSel.value = ''; }
        }
        refreshAssignOptions();
        lookupExisting(card);
    }

    // Điền hồ sơ (ảnh chân dung, CCCD, HĐLĐ, vai trò) vào card từ 1 attendee đã có.
    function applyProfileToCard(card, att) {
        var idx = card.getAttribute('data-sub');
        var eid = card.querySelector('.rsub-existing-id');
        if (eid) { eid.value = att.id || ''; }

        FILE_FIELDS.forEach(function (f) {
            var fileUrl = att[f.path] || (f.k === 'portrait' ? att.photo_path : '') || '';
            var hidden = document.getElementById('rsub_url_' + f.k + '_' + idx);
            var prev = document.getElementById('rsub_prev_' + f.k + '_' + idx);
            if (fileUrl) {
                if (hidden) { hidden.value = fileUrl; }
                if (prev) {
                    var isPdf = String(fileUrl).toLowerCase().endsWith('.pdf');
                    prev.innerHTML = isPdf
                        ? '<i class="fa fa-file-pdf-o fa-lg text-danger"></i><br><span class="badge bg-success">Hồ sơ cũ</span>'
                        : '<img src="' + escapeHtml(fileUrl) + '" style="max-height:44px;border-radius:4px;"><br><span class="badge bg-success">Hồ sơ cũ</span>';
                }
            } else if (hidden) {
                hidden.value = '';
            }
        });

        var roleSel = card.querySelector('.rsub-roles');
        if (roleSel && att.role_id) {
            var ids = String(att.role_id).split(',').map(function (x) { return x.trim(); });
            Array.prototype.forEach.call(roleSel.options, function (o) {
                o.selected = ids.indexOf(o.value) >= 0;
            });
        }
    }

    // Chọn người thay từ đăng ký khác: điền họ tên/chức danh/CCCD ngay, rồi tải hồ sơ (ảnh, CCCD, HĐLĐ).
    function onOtherChange(card) {
        var sel = card.querySelector('.rsub-other');
        var val = sel ? sel.value : '';

        // Reset dữ liệu tự điền cũ.
        clearCardProfile(card);
        ['.rsub-name', '.rsub-pos', '.rsub-idcard'].forEach(function (s) {
            var el = card.querySelector(s); if (el) { el.value = ''; }
        });
        var roleSel = card.querySelector('.rsub-roles');
        if (roleSel) { Array.prototype.forEach.call(roleSel.options, function (o) { o.selected = false; }); }

        if (!val) { refreshAssignOptions(); return; }

        // Bỏ chọn nhân sự SMILE (hai nguồn loại trừ nhau).
        var staffSel = card.querySelector('.rsub-staff');
        if (staffSel && staffSel.value) {
            if (window.jQuery && jQuery.fn.select2) { jQuery(staffSel).val('').trigger('change.select2'); }
            else { staffSel.value = ''; }
        }

        var opt = sel.options[sel.selectedIndex];
        var nameInput = card.querySelector('.rsub-name');
        if (nameInput) { nameInput.value = opt.getAttribute('data-name') || ''; }
        var posInput = card.querySelector('.rsub-pos');
        if (posInput) { posInput.value = opt.getAttribute('data-position') || ''; }
        var idInput = card.querySelector('.rsub-idcard');
        if (idInput) { idInput.value = opt.getAttribute('data-idcard') || ''; }

        var alertBox = card.querySelector('.rsub-alert');
        refreshAssignOptions();

        if (!attendeeProfileUrl) { return; }
        fetch(attendeeProfileUrl + '?attendee_id=' + encodeURIComponent(val), { headers: { 'Accept': 'application/json' } })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (!(data.success && data.attendee)) { return; }
                applyProfileToCard(card, data.attendee);
                if (alertBox) {
                    var inCurrent = opt.getAttribute('data-incurrent') === '1';
                    if (inCurrent) {
                        alertBox.className = 'alert alert-warning py-1 px-2 mt-1 small rsub-alert';
                        alertBox.innerHTML = '<i class="fa fa-info-circle me-1"></i><strong>Người này đã có trong đăng ký hiện tại.</strong> '
                            + 'Sẽ dùng lại bản ghi hiện có và chỉ gán thêm nội dung (không tạo bản ghi mới).';
                    } else {
                        alertBox.className = 'alert alert-success py-1 px-2 mt-1 small rsub-alert';
                        alertBox.innerHTML = '<i class="fa fa-check-circle me-1"></i><strong>Đã lấy hồ sơ từ đăng ký khác.</strong> Ảnh chân dung, CCCD, HĐLĐ được dùng lại.';
                    }
                    alertBox.classList.remove('d-none');
                }
                refreshAssignOptions();
            })
            .catch(function () {});
    }

    function wireCard(card) {
        var staff = card.querySelector('.rsub-staff');
        if (staff) {
            var initSelect2 = window.jQuery && jQuery.fn.select2;
            if (initSelect2) {
                jQuery(staff).select2({
                    dropdownParent: jQuery('#replaceAttendeeModal'),
                    width: '100%',
                    placeholder: '-- Chọn nhân sự SMILE --',
                    allowClear: true
                }).on('change', function () { onStaffChange(card); });
            } else {
                staff.addEventListener('change', function () { onStaffChange(card); });
            }
        }
        var other = card.querySelector('.rsub-other');
        if (other) {
            if (window.jQuery && jQuery.fn.select2) {
                jQuery(other).select2({
                    dropdownParent: jQuery('#replaceAttendeeModal'),
                    width: '100%',
                    placeholder: '-- Chọn từ đăng ký khác --',
                    allowClear: true
                }).on('change', function () { onOtherChange(card); });
            } else {
                other.addEventListener('change', function () { onOtherChange(card); });
            }
        }
        var nameInput = card.querySelector('.rsub-name');
        if (nameInput) { nameInput.addEventListener('input', refreshAssignOptions); }
        var idInput = card.querySelector('.rsub-idcard');
        if (idInput) { idInput.addEventListener('blur', function () { lookupExisting(card); }); }
        var removeBtn = card.querySelector('.rsub-remove');
        if (removeBtn) { removeBtn.addEventListener('click', function () { removeCard(card); }); }
    }

    function addCard() {
        var idx = subSeq++;
        var container = document.getElementById('replace_subs_container');
        var wrap = document.createElement('div');
        wrap.innerHTML = buildSubCardHtml(idx);
        var card = wrap.firstChild;
        container.appendChild(card);
        wireCard(card);
        renumberCards();
        refreshAssignOptions();
    }

    function removeCard(card) {
        if (activeCards().length <= 1) {
            Toast.warning('Phải có ít nhất một người thay.');
            return;
        }
        var staff = card.querySelector('.rsub-staff');
        if (staff && window.jQuery && jQuery.fn.select2) {
            try { jQuery(staff).select2('destroy'); } catch (e) {}
        }
        var other = card.querySelector('.rsub-other');
        if (other && window.jQuery && jQuery.fn.select2) {
            try { jQuery(other).select2('destroy'); } catch (e) {}
        }
        card.parentNode.removeChild(card);
        renumberCards();
        refreshAssignOptions();
    }

    // ---- Bảng gán nội dung (cột trái) ----

    function renderAssignments(summary) {
        var teams = summary.sport_teams || [];
        var comps = summary.competitions || [];
        var contests = summary.beauty_contests || [];
        var roles = summary.roles || [];
        var html = '';

        html += '<div class="mb-2"><h6 class="mb-1"><i class="fa fa-futbol-o me-1 text-primary"></i>Đội thể thao</h6>';
        if (teams.length) {
            teams.forEach(function (t) {
                var tid = t.sport_team_id;
                var meta = teamMetaBadges(t);
                html += '<div class="border rounded p-2 mb-1">'
                    + '<div><strong>' + escapeHtml(t.sport_name || t.team_name || '') + '</strong> '
                    + '<small class="text-muted">' + escapeHtml(t.team_name || '') + '</small>'
                    + (meta.length ? '<br><small>' + meta.join(' · ') + '</small>' : '') + '</div>'
                    + '<div class="d-flex align-items-center gap-2 mt-1">'
                    + '<select class="form-select form-select-sm assign-select" data-kind="team" data-id="' + tid + '"></select>'
                    + '<div class="form-check mb-0 flex-shrink-0 team-whole-wrap d-none">'
                    + '<input class="form-check-input assign-cancel-whole" type="checkbox" data-id="' + tid + '" id="cw_' + tid + '">'
                    + '<label class="form-check-label small text-danger" for="cw_' + tid + '">Huỷ cả đội</label>'
                    + '</div></div></div>';
            });
        } else {
            html += '<p class="text-muted small mb-0">Không tham gia đội nào.</p>';
        }
        html += '</div>';

        html += '<div class="mb-2"><h6 class="mb-1"><i class="fa fa-trophy me-1 text-warning"></i>Thi nghiệp vụ</h6>';
        if (comps.length) {
            comps.forEach(function (c) {
                html += '<div class="border rounded p-2 mb-1">'
                    + '<div>' + escapeHtml(c.competition_name || '')
                    + (c.candidate_number ? ' <span class="badge bg-secondary">SBD ' + escapeHtml(c.candidate_number) + '</span>' : '') + '</div>'
                    + '<select class="form-select form-select-sm assign-select mt-1" data-kind="comp" data-id="' + c.competition_id + '"></select>'
                    + '</div>';
            });
        } else {
            html += '<p class="text-muted small mb-0">Không đăng ký cuộc thi nào.</p>';
        }
        html += '</div>';

        html += '<div class="mb-2"><h6 class="mb-1"><i class="fa fa-star me-1 text-danger"></i>Thi Miss</h6>';
        if (contests.length) {
            contests.forEach(function (c) {
                html += '<div class="small mb-1">' + escapeHtml(c.contest_name || '')
                    + ' <span class="badge bg-danger">Sẽ huỷ</span></div>';
            });
        } else {
            html += '<p class="text-muted small mb-0">Không đăng ký thi Miss.</p>';
        }
        html += '</div>';

        var talents = summary.talent_entries || [];
        html += '<div class="mb-2"><h6 class="mb-1"><i class="fa fa-music me-1 text-info"></i>Văn nghệ</h6>';
        if (talents.length) {
            talents.forEach(function (te) {
                html += '<div class="border rounded p-2 mb-1">'
                    + '<div>' + escapeHtml(te.entry_title || '')
                    + (te.category_name ? ' <span class="badge bg-secondary">' + escapeHtml(te.category_name) + '</span>' : '') + '</div>'
                    + '<select class="form-select form-select-sm assign-select mt-1" data-kind="talent" data-id="' + te.entry_id + '"></select>'
                    + '</div>';
            });
        } else {
            html += '<p class="text-muted small mb-0">Không đăng ký tiết mục văn nghệ.</p>';
        }
        html += '</div>';

        html += '<div><h6 class="mb-1"><i class="fa fa-id-badge me-1 text-success"></i>Vai trò của người bị thay</h6>';
        if (roles.length) {
            html += roles.map(function (r) {
                return '<span class="badge bg-secondary me-1 mb-1">' + escapeHtml(r.role_name || '') + '</span>';
            }).join('');
            html += '<div class="small text-muted mt-1">Vai trò được nhập riêng cho từng người thay ở cột bên phải.</div>';
        } else {
            html += '<p class="text-muted small mb-0">Không có vai trò.</p>';
        }
        html += '</div>';

        var container = document.getElementById('replace_assign_container');
        container.innerHTML = html;

        container.querySelectorAll('.assign-select').forEach(function (sel) {
            sel.addEventListener('change', function () {
                if (sel.getAttribute('data-kind') === 'team') {
                    var wrap = sel.parentNode.querySelector('.team-whole-wrap');
                    if (wrap) { wrap.classList.toggle('d-none', sel.value !== 'cancel'); }
                }
                validateCoverage();
            });
        });
    }

    // Cập nhật option cho tất cả dropdown gán theo danh sách người thay hiện có.
    function refreshAssignOptions() {
        var cards = activeCards();
        document.querySelectorAll('#replace_assign_container .assign-select').forEach(function (sel) {
            var prev = sel.value;
            var html = '<option value="">-- Chọn --</option>';
            cards.forEach(function (card, i) {
                var idx = card.getAttribute('data-sub');
                var nm = cardName(card);
                html += '<option value="s' + idx + '">Người thay #' + (i + 1) + (nm ? ' · ' + escapeHtml(nm) : '') + '</option>';
            });
            html += '<option value="cancel">Huỷ nội dung này</option>';
            sel.innerHTML = html;
            // Giữ lựa chọn cũ nếu vẫn hợp lệ
            var stillValid = prev === 'cancel' || prev === '' ||
                cards.some(function (card) { return ('s' + card.getAttribute('data-sub')) === prev; });
            sel.value = stillValid ? prev : '';
            if (sel.getAttribute('data-kind') === 'team') {
                var wrap = sel.parentNode.querySelector('.team-whole-wrap');
                if (wrap) { wrap.classList.toggle('d-none', sel.value !== 'cancel'); }
            }
        });
        validateCoverage();
    }

    function validateCoverage() {
        var selects = document.querySelectorAll('#replace_assign_container .assign-select');
        var undecided = 0;
        selects.forEach(function (sel) { if (!sel.value) { undecided++; } });

        var btn = document.getElementById('btn_submit_replace');
        var hint = document.getElementById('replace_cover_hint');
        if (undecided > 0) {
            if (btn) { btn.disabled = true; }
            if (hint) {
                hint.textContent = 'Còn ' + undecided + ' nội dung chưa quyết định';
                hint.classList.remove('d-none');
            }
        } else {
            if (btn) { btn.disabled = false; }
            if (hint) { hint.classList.add('d-none'); }
        }
    }

    function loadReplaceSummary(attendeeId) {
        var container = document.getElementById('replace_assign_container');
        container.innerHTML = '<div class="text-center text-muted py-3"><i class="fa fa-spinner fa-spin me-1"></i>Đang tải...</div>';
        document.getElementById('replace_attendee_name').textContent = '-';
        document.getElementById('replace_attendee_position').textContent = '-';
        document.getElementById('replace_badge_warning').classList.add('d-none');

        fetch(summaryUrl + '?attendee_id=' + encodeURIComponent(attendeeId), {
            headers: { 'Accept': 'application/json' }
        })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (!data.success) {
                    container.innerHTML = '<p class="text-danger">' + escapeHtml(data.error || 'Không tải được dữ liệu.') + '</p>';
                    return;
                }
                currentSummary = data.summary || {};
                var att = data.attendee || {};
                document.getElementById('replace_attendee_name').textContent = att.full_name || '';
                var parts = [];
                if (att.division_name) { parts.push(att.division_name); }
                if (att.position_name) { parts.push(att.position_name); }
                document.getElementById('replace_attendee_position').textContent = parts.join(' - ') || '-';
                if (parseInt(att.badge_printed, 10)) {
                    document.getElementById('replace_badge_warning').classList.remove('d-none');
                }
                renderAssignments(currentSummary);
                refreshAssignOptions();
            })
            .catch(function () {
                container.innerHTML = '<p class="text-danger">Lỗi kết nối máy chủ.</p>';
            });
    }

    window.openReplaceAttendeeModal = function (attendeeId) {
        document.getElementById('replace_attendee_id').value = attendeeId;
        document.getElementById('replace_reason').value = '';
        document.getElementById('btn_submit_replace').disabled = true;
        document.getElementById('replace_cover_hint').classList.add('d-none');

        // Reset danh sách người thay về đúng 1 card mặc định.
        var subsContainer = document.getElementById('replace_subs_container');
        activeCards().forEach(function (card) {
            var staff = card.querySelector('.rsub-staff');
            if (staff && window.jQuery && jQuery.fn.select2) { try { jQuery(staff).select2('destroy'); } catch (e) {} }
            var other = card.querySelector('.rsub-other');
            if (other && window.jQuery && jQuery.fn.select2) { try { jQuery(other).select2('destroy'); } catch (e) {} }
        });
        subsContainer.innerHTML = '';
        subSeq = 0;
        addCard();

        loadReplaceSummary(attendeeId);
        showModal('replaceAttendeeModal');
    };

    var addSubBtn = document.getElementById('replace_add_sub');
    if (addSubBtn) { addSubBtn.addEventListener('click', function () { addCard(); }); }

    // ---- Submit ----

    function collectAndValidate() {
        var reason = document.getElementById('replace_reason').value.trim();
        if (!reason) { Toast.error('Vui lòng nhập lý do thay thế.'); return null; }

        var cards = activeCards();
        var idxToJ = {};       // data-sub (idx) -> chỉ số liên tục j
        cards.forEach(function (card, j) { idxToJ[card.getAttribute('data-sub')] = j; });

        // Xác định các card được gán ít nhất 1 nội dung.
        var selects = document.querySelectorAll('#replace_assign_container .assign-select');
        var referenced = {};
        var undecided = 0;
        selects.forEach(function (sel) {
            if (!sel.value) { undecided++; return; }
            var m = /^s(\d+)$/.exec(sel.value);
            if (m) { referenced[m[1]] = true; }
        });
        if (undecided > 0) { Toast.error('Còn ' + undecided + ' nội dung chưa quyết định người thay.'); return null; }

        var refIdx = Object.keys(referenced);
        if (!refIdx.length) {
            Toast.error('Chưa gán nội dung nào cho người thay. Nếu chỉ muốn gỡ, dùng chức năng Huỷ tư cách.');
            return null;
        }

        var fd = new FormData();
        fd.append('attendee_id', document.getElementById('replace_attendee_id').value);
        fd.append('registration_id', document.querySelector('#replaceAttendeeForm input[name="registration_id"]').value);
        fd.append('event_id', document.querySelector('#replaceAttendeeForm input[name="event_id"]').value);
        fd.append('property_id', document.querySelector('#replaceAttendeeForm input[name="property_id"]').value);
        fd.append('reason', reason);

        // Chỉ gửi các card được gán nội dung.
        var ok = true;
        refIdx.forEach(function (idx) {
            var card = document.querySelector('.replace-sub-card[data-sub="' + idx + '"]');
            if (!card) { return; }
            var j = idxToJ[idx];
            var staffId = (card.querySelector('.rsub-staff') || {}).value || '';
            var fullName = (card.querySelector('.rsub-name') || {}).value || '';
            fullName = fullName.trim();
            var num = j + 1;
            if (!staffId && !fullName) {
                Toast.error('Người thay #' + num + ': chọn nhân sự SMILE hoặc nhập họ tên.');
                ok = false; return;
            }
            var portraitFile = card.querySelector('input[name="sub_portrait_file_' + idx + '"]');
            var hasPortraitFile = portraitFile && portraitFile.files && portraitFile.files[0];
            var portraitUrl = (document.getElementById('rsub_url_portrait_' + idx) || {}).value || '';
            if (!staffId && !hasPortraitFile && !portraitUrl) {
                Toast.error('Người thay #' + num + ': cần ảnh chân dung.');
                ok = false; return;
            }

            fd.append('sub[' + j + '][staff_id]', staffId);
            fd.append('sub[' + j + '][full_name]', fullName);
            fd.append('sub[' + j + '][position]', (card.querySelector('.rsub-pos') || {}).value || '');
            fd.append('sub[' + j + '][id_card]', (card.querySelector('.rsub-idcard') || {}).value || '');
            fd.append('sub[' + j + '][existing_attendee_id]', (card.querySelector('.rsub-existing-id') || {}).value || '');

            var roleSel = card.querySelector('.rsub-roles');
            var roleVals = [];
            if (roleSel) {
                Array.prototype.forEach.call(roleSel.selectedOptions, function (o) { roleVals.push(o.value); });
            }
            fd.append('sub[' + j + '][role_id]', roleVals.join(', '));

            // File + URL hồ sơ cũ theo path chuẩn.
            FILE_FIELDS.forEach(function (f) {
                var input = card.querySelector('input[name="sub_' + f.k + '_file_' + idx + '"]');
                if (input && input.files && input.files[0]) {
                    fd.append('sub_' + f.k + '_file_' + j, input.files[0]);
                }
                var url = (document.getElementById('rsub_url_' + f.k + '_' + idx) || {}).value || '';
                if (url) { fd.append('sub[' + j + '][existing_' + f.k + '_url]', url); }
            });
        });
        if (!ok) { return null; }

        // Gán nội dung: chuyển s{idx} -> s{j}.
        selects.forEach(function (sel) {
            var kind = sel.getAttribute('data-kind');
            var id = sel.getAttribute('data-id');
            var val = sel.value;
            var out;
            if (val === 'cancel') {
                out = 'cancel';
            } else {
                var m = /^s(\d+)$/.exec(val);
                out = (m && idxToJ[m[1]] !== undefined) ? ('s' + idxToJ[m[1]]) : 'cancel';
            }
            if (kind === 'team') {
                fd.append('assign_team[' + id + ']', out);
                var cw = document.querySelector('.assign-cancel-whole[data-id="' + id + '"]');
                if (out === 'cancel' && cw && cw.checked) { fd.append('cancel_whole_team[' + id + ']', '1'); }
            } else if (kind === 'comp') {
                fd.append('assign_comp[' + id + ']', out);
            } else if (kind === 'talent') {
                fd.append('assign_talent[' + id + ']', out);
            }
        });

        return fd;
    }

    var replaceForm = document.getElementById('replaceAttendeeForm');
    if (replaceForm) {
        replaceForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var fd = collectAndValidate();
            if (!fd) { return; }
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
                if (result.isConfirmed) { submitReplace(fd); }
            });
        });
    }

    function submitReplace(formData) {
        var btn = document.getElementById('btn_submit_replace');
        var originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i>Đang xử lý...';

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
