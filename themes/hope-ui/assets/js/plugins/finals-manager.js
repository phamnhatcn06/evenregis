/**
 * Finals Manager — quản lý việc chọn đội/VĐV/thí sinh/tiết mục vào vòng chung kết.
 *
 * Dùng chung cho 4 module: Thể thao, Thi nghiệp vụ, Thi sắc đẹp, Văn nghệ.
 * Đọc cấu hình từ thẻ #finals-app qua các data-* attribute:
 *   data-mode           : "sport" (2 dropdown event+sport) | "single" (1 dropdown scope)
 *   data-candidates-url : URL AJAX lấy danh sách ứng viên
 *   data-list-url       : URL AJAX lấy danh sách đã vào chung kết
 *   data-add-url        : URL AJAX thêm vào chung kết
 *   data-remove-url     : URL AJAX gỡ khỏi chung kết
 *
 * Mỗi item chuẩn hoá từ server: { id, ref, code, name, sub }
 *   - candidate.id : id dùng để thêm (đội/đăng ký/thí sinh/tiết mục)
 *   - finalist.id  : id bản ghi chung kết dùng để gỡ
 *   - finalist.ref : id gốc (để loại ứng viên đã có trong chung kết)
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var app = document.getElementById('finals-app');
        if (!app) {
            return;
        }

        var mode = app.getAttribute('data-mode') || 'single';
        var urls = {
            candidates: app.getAttribute('data-candidates-url'),
            list: app.getAttribute('data-list-url'),
            add: app.getAttribute('data-add-url'),
            remove: app.getAttribute('data-remove-url')
        };

        var eventSelect = document.getElementById('finals-event');
        var sportSelect = document.getElementById('finals-sport');
        var scopeSelect = document.getElementById('finals-scope');

        var candidatesBox = document.getElementById('finals-candidates');
        var finalistsBox = document.getElementById('finals-finalists');
        var addBtn = document.getElementById('finals-add-btn');
        var searchInput = document.getElementById('finals-search');

        function scopeParams() {
            if (mode === 'sport') {
                return {
                    event_id: eventSelect ? eventSelect.value : '',
                    sport_id: sportSelect ? sportSelect.value : ''
                };
            }
            return { id: scopeSelect ? scopeSelect.value : '' };
        }

        function scopeReady() {
            var p = scopeParams();
            if (mode === 'sport') {
                return p.event_id && p.sport_id;
            }
            return !!p.id;
        }

        function buildQuery(params) {
            return Object.keys(params)
                .filter(function (k) { return params[k] !== '' && params[k] != null; })
                .map(function (k) { return encodeURIComponent(k) + '=' + encodeURIComponent(params[k]); })
                .join('&');
        }

        function placeholder(box, text) {
            box.innerHTML = '<div class="text-center text-muted py-4"><i class="fa fa-info-circle me-1"></i>' + text + '</div>';
        }

        function escapeHtml(str) {
            if (str == null) return '';
            return String(str)
                .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;').replace(/'/g, '&#039;');
        }

        function itemHtml(item, opts) {
            var checkbox = opts.checkbox
                ? '<input type="checkbox" class="form-check-input mt-0 me-2 finals-check" value="' + escapeHtml(item.id) + '">'
                : '';
            var action = opts.removable
                ? '<button type="button" class="btn btn-sm btn-outline-danger finals-remove-btn" data-id="' + escapeHtml(item.id) + '"><i class="fa fa-times"></i></button>'
                : '';
            var code = item.code ? '<span class="badge bg-primary me-2">' + escapeHtml(item.code) + '</span>' : '';
            var sub = item.sub ? '<div class="small text-muted">' + escapeHtml(item.sub) + '</div>' : '';
            return '<label class="list-group-item d-flex align-items-center gap-2">' +
                checkbox +
                '<div class="flex-grow-1">' + code + escapeHtml(item.name) + sub + '</div>' +
                action +
                '</label>';
        }

        function renderCandidates(items) {
            if (!items.length) {
                placeholder(candidatesBox, 'Không còn ứng viên nào đủ điều kiện.');
                updateAddBtn();
                return;
            }
            candidatesBox.innerHTML = '<div class="list-group list-group-flush">' +
                items.map(function (it) { return itemHtml(it, { checkbox: true }); }).join('') +
                '</div>';
            candidatesBox.querySelectorAll('.finals-check').forEach(function (chk) {
                chk.addEventListener('change', updateAddBtn);
            });
            updateAddBtn();
        }

        function renderFinalists(items) {
            var countEl = document.getElementById('finals-count');
            if (countEl) {
                countEl.textContent = items.length;
            }
            if (!items.length) {
                placeholder(finalistsBox, 'Chưa có ai vào chung kết.');
                return;
            }
            finalistsBox.innerHTML = '<div class="list-group list-group-flush">' +
                items.map(function (it) { return itemHtml(it, { removable: true }); }).join('') +
                '</div>';
            finalistsBox.querySelectorAll('.finals-remove-btn').forEach(function (btn) {
                btn.addEventListener('click', function () { removeFinalist(btn.getAttribute('data-id'), btn); });
            });
        }

        function updateAddBtn() {
            if (!addBtn) return;
            var checked = candidatesBox.querySelectorAll('.finals-check:checked').length;
            addBtn.disabled = checked === 0;
            addBtn.innerHTML = '<i class="fa fa-arrow-right me-1"></i>Thêm vào chung kết' + (checked ? ' (' + checked + ')' : '');
        }

        function fetchJson(url, options) {
            return fetch(url, options || {}).then(function (r) { return r.json(); });
        }

        function reload() {
            if (!scopeReady()) {
                placeholder(candidatesBox, 'Hãy chọn ' + (mode === 'sport' ? 'sự kiện và môn thi' : 'hạng mục') + ' để bắt đầu.');
                placeholder(finalistsBox, 'Chưa có dữ liệu.');
                updateAddBtn();
                return;
            }
            var qs = buildQuery(scopeParams());
            placeholder(candidatesBox, 'Đang tải...');
            placeholder(finalistsBox, 'Đang tải...');

            fetchJson(urls.candidates + '?' + qs)
                .then(function (data) { renderCandidates((data && data.success && data.data) ? data.data : []); })
                .catch(function () { placeholder(candidatesBox, 'Lỗi tải dữ liệu.'); });

            fetchJson(urls.list + '?' + qs)
                .then(function (data) { renderFinalists((data && data.success && data.data) ? data.data : []); })
                .catch(function () { placeholder(finalistsBox, 'Lỗi tải dữ liệu.'); });
        }

        function addSelected() {
            var ids = Array.prototype.map.call(
                candidatesBox.querySelectorAll('.finals-check:checked'),
                function (c) { return c.value; }
            );
            if (!ids.length) return;

            var original = addBtn.innerHTML;
            addBtn.disabled = true;
            addBtn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i>Đang xử lý...';

            var body = new FormData();
            var p = scopeParams();
            Object.keys(p).forEach(function (k) { body.append(k, p[k]); });
            ids.forEach(function (id) { body.append('ids[]', id); });

            fetchJson(urls.add, { method: 'POST', body: body })
                .then(function (data) {
                    if (data && data.success) {
                        if (window.Toast) Toast.success(data.message || 'Đã thêm vào chung kết');
                        reload();
                    } else {
                        addBtn.disabled = false;
                        addBtn.innerHTML = original;
                        if (window.Toast) Toast.error((data && data.message) || 'Có lỗi xảy ra');
                    }
                })
                .catch(function () {
                    addBtn.disabled = false;
                    addBtn.innerHTML = original;
                    if (window.Toast) Toast.error('Lỗi kết nối server');
                });
        }

        function removeFinalist(id, btn) {
            function doRemove() {
                if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i>'; }
                var body = new FormData();
                body.append('id', id);
                fetchJson(urls.remove, { method: 'POST', body: body })
                    .then(function (data) {
                        if (data && data.success) {
                            if (window.Toast) Toast.success(data.message || 'Đã gỡ khỏi chung kết');
                            reload();
                        } else {
                            if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fa fa-times"></i>'; }
                            if (window.Toast) Toast.error((data && data.message) || 'Có lỗi xảy ra');
                        }
                    })
                    .catch(function () {
                        if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fa fa-times"></i>'; }
                        if (window.Toast) Toast.error('Lỗi kết nối server');
                    });
            }

            if (window.Swal) {
                Swal.fire({
                    title: 'Xác nhận gỡ',
                    text: 'Gỡ mục này khỏi vòng chung kết?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Gỡ',
                    cancelButtonText: 'Hủy'
                }).then(function (result) { if (result.isConfirmed) doRemove(); });
            } else {
                doRemove();
            }
        }

        function filterCandidates() {
            var term = (searchInput.value || '').toLowerCase();
            candidatesBox.querySelectorAll('.list-group-item').forEach(function (row) {
                row.style.display = row.textContent.toLowerCase().indexOf(term) !== -1 ? '' : 'none';
            });
        }

        if (eventSelect) eventSelect.addEventListener('change', reload);
        if (sportSelect) sportSelect.addEventListener('change', reload);
        if (scopeSelect) scopeSelect.addEventListener('change', reload);
        if (addBtn) addBtn.addEventListener('click', addSelected);
        if (searchInput) searchInput.addEventListener('input', filterCandidates);

        reload();
    });
})();
