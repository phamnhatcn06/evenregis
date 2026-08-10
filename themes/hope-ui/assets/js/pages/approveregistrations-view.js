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

    /**
     * Dựng HTML danh sách nội dung tham gia từ dữ liệu summary.
     */
    function renderSummary(summary) {
        var html = '';

        var teams = summary.sport_teams || [];
        html += '<div class="mb-3"><h6 class="mb-1"><i class="fa fa-futbol-o me-1 text-primary"></i>Đội thể thao</h6>';
        if (teams.length) {
            html += '<ul class="list-group list-group-flush">';
            teams.forEach(function (t) {
                var meta = [];
                if (t.jersey_number) { meta.push('Số áo ' + t.jersey_number); }
                if (t.position) { meta.push(t.position); }
                if (parseInt(t.is_captain, 10)) { meta.push('<span class="badge bg-warning text-dark">Đội trưởng</span>'); }
                if (parseInt(t.is_alliance, 10)) { meta.push('<span class="badge bg-info text-dark">Liên quân</span>'); }
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
        html += '</div>';

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

    function escapeHtml(str) {
        return String(str == null ? '' : str)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }

    /**
     * Tải summary và đổ vào 2 vùng (container + tên + cảnh báo thẻ).
     */
    function loadSummary(attendeeId, containerId, nameId, badgeWarnId, positionId) {
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

    // Slice 1: chỉ hiển thị (read-only). Logic submit bổ sung ở Slice 2 (huỷ) và Slice 4 (thay thế).
    ['withdrawAttendeeForm', 'replaceAttendeeForm'].forEach(function (formId) {
        var form = document.getElementById(formId);
        if (form) {
            form.addEventListener('submit', function (e) { e.preventDefault(); });
        }
    });
})();
