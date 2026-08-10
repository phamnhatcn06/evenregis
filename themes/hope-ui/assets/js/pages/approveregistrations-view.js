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
                container.innerHTML = renderSummary(data.summary || {});
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
        loadSummary(attendeeId, 'withdraw_summary_container', 'withdraw_attendee_name', 'withdraw_badge_warning', null);
        showModal('withdrawAttendeeModal');
    };

    window.openReplaceAttendeeModal = function (attendeeId) {
        document.getElementById('replace_attendee_id').value = attendeeId;
        document.getElementById('replace_reason').value = '';
        document.getElementById('replace_badge_warning').classList.add('d-none');
        loadSummary(attendeeId, 'replace_summary_container', 'replace_attendee_name', 'replace_badge_warning', 'replace_attendee_position');
        showModal('replaceAttendeeModal');
    };

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

    // Thay thế (Slice 4): tạm chặn submit cho tới khi hoàn thiện.
    var replaceForm = document.getElementById('replaceAttendeeForm');
    if (replaceForm) {
        replaceForm.addEventListener('submit', function (e) { e.preventDefault(); });
    }
})();
