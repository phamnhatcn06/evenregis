/**
 * Sửa nhanh giới tính cho người tham dự chưa xác định giới tính.
 */
document.addEventListener('DOMContentLoaded', function () {
    var openBtn = document.getElementById('btnOpenGenderFix');
    if (!openBtn) return;

    var listUrl = openBtn.getAttribute('data-list-url');
    var updateUrl = openBtn.getAttribute('data-update-url');
    var eventId = openBtn.getAttribute('data-event-id');
    var modalEl = document.getElementById('genderFixModal');
    var bodyEl = document.getElementById('genderFixBody');
    var countEl = document.getElementById('genderFixCount');

    function escapeHtml(s) {
        return String(s == null ? '' : s)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function rowHtml(p, idx) {
        var ids = Array.isArray(p.ids) ? p.ids.join(',') : (p.id || '');
        return '' +
            '<tr data-ids="' + ids + '">' +
            '<td class="text-center">' + idx + '</td>' +
            '<td>' + escapeHtml(p.full_name) +
            (p.staff_code ? ' <span class="badge bg-secondary">' + escapeHtml(p.staff_code) + '</span>' : '') +
            '</td>' +
            '<td>' + escapeHtml(p.property_name) + '</td>' +
            '<td class="text-center gender-status" colspan="2" style="width:200px;">' +
            '<div class="btn-group btn-group-sm" role="group">' +
            '<button type="button" class="btn btn-outline-primary gender-pick" data-gender="1">Nam</button>' +
            '<button type="button" class="btn btn-outline-danger gender-pick" data-gender="0">Nữ</button>' +
            '</div>' +
            '</td>' +
            '</tr>';
    }

    function loadList() {
        bodyEl.innerHTML = '<tr><td colspan="5" class="text-center text-muted py-3">Đang tải...</td></tr>';
        fetch(listUrl + '?event_id=' + encodeURIComponent(eventId), { headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                var data = (res && res.success && res.data) ? res.data : [];
                if (countEl) countEl.textContent = data.length;
                if (!data.length) {
                    bodyEl.innerHTML = '<tr><td colspan="5" class="text-center text-success py-3">' +
                        '<i class="fa fa-check-circle me-1"></i>Tất cả đã có giới tính.</td></tr>';
                    return;
                }
                bodyEl.innerHTML = data.map(function (p, i) { return rowHtml(p, i + 1); }).join('');
            })
            .catch(function () {
                bodyEl.innerHTML = '<tr><td colspan="5" class="text-center text-danger py-3">Lỗi tải dữ liệu.</td></tr>';
            });
    }

    function saveRow(tr) {
        var ids = tr.getAttribute('data-ids');
        var sel = tr.querySelector('.gender-select');
        var btn = tr.querySelector('.gender-save');
        var gender = sel.value;
        if (gender !== '0' && gender !== '1') {
            if (window.Toast) Toast.warning('Chọn giới tính trước khi lưu.');
            return;
        }
        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';

        var fd = new FormData();
        fd.append('ids', ids);
        fd.append('gender', gender);
        fetch(updateUrl, { method: 'POST', body: fd, headers: { 'Accept': 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res && res.success) {
                    if (window.Toast) Toast.success(res.message || 'Đã cập nhật.');
                    tr.style.transition = 'opacity .3s';
                    tr.style.opacity = '0.4';
                    sel.disabled = true;
                    btn.classList.remove('btn-success');
                    btn.classList.add('btn-secondary');
                    btn.innerHTML = '<i class="fa fa-check"></i>';
                    if (countEl) {
                        var n = parseInt(countEl.textContent, 10) || 0;
                        if (n > 0) countEl.textContent = n - 1;
                    }
                } else {
                    if (window.Toast) Toast.error((res && res.message) || 'Lỗi cập nhật.');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa fa-save"></i>';
                }
            })
            .catch(function () {
                if (window.Toast) Toast.error('Lỗi kết nối.');
                btn.disabled = false;
                btn.innerHTML = '<i class="fa fa-save"></i>';
            });
    }

    bodyEl.addEventListener('click', function (e) {
        var btn = e.target.closest('.gender-save');
        if (btn) {
            var tr = btn.closest('tr');
            if (tr) saveRow(tr);
        }
    });

    openBtn.addEventListener('click', function () {
        if (modalEl && window.bootstrap) {
            var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        }
        loadList();
    });
});
