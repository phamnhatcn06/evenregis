/**
 * Màn Import người trúng giải (Lucky Draw).
 * Luồng: chọn sự kiện -> upload Excel -> Xem trước (parse) -> Lưu (cộng dồn).
 * Xoá 1 dòng / xoá toàn bộ có xác nhận SweetAlert.
 */
(function () {
    'use strict';

    var root = document.getElementById('lucky-import');
    if (!root) {
        return;
    }

    var parseUrl = root.getAttribute('data-parse-url');
    var saveUrl = root.getAttribute('data-save-url');
    var deleteUrlTpl = root.getAttribute('data-delete-url');
    var clearUrl = root.getAttribute('data-clear-url');

    var eventSelect = document.getElementById('import-event');
    var fileInput = document.getElementById('import-file');
    var btnParse = document.getElementById('btn-parse');
    var btnSave = document.getElementById('btn-save');
    var btnClear = document.getElementById('btn-clear');

    var previewCard = document.getElementById('preview-card');
    var previewBody = document.getElementById('preview-body');
    var previewErrors = document.getElementById('preview-errors');
    var previewCount = document.getElementById('preview-count');

    var parsedRows = [];

    // Đổi sự kiện -> tải lại trang với event_id để hiển thị danh sách tương ứng.
    if (eventSelect) {
        eventSelect.addEventListener('change', function () {
            var url = new URL(window.location.href);
            url.searchParams.set('event_id', this.value);
            window.location.href = url.toString();
        });
    }

    function esc(s) {
        var d = document.createElement('div');
        d.textContent = s == null ? '' : String(s);
        return d.innerHTML;
    }

    // ---- Xem trước: đọc file trên server ----
    if (btnParse) {
        btnParse.addEventListener('click', function () {
            if (!eventSelect || !eventSelect.value) {
                Toast.warning('Vui lòng chọn sự kiện.');
                return;
            }
            if (!fileInput || !fileInput.files.length) {
                Toast.warning('Vui lòng chọn file Excel.');
                return;
            }

            var original = btnParse.innerHTML;
            btnParse.disabled = true;
            btnParse.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i>Đang đọc...';

            var fd = new FormData();
            fd.append('file', fileInput.files[0]);

            fetch(parseUrl, { method: 'POST', body: fd })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    btnParse.disabled = false;
                    btnParse.innerHTML = original;
                    if (!data.success) {
                        Toast.error(data.message || 'Không đọc được file.');
                        return;
                    }
                    parsedRows = data.rows || [];
                    renderPreview(parsedRows, data.errors || []);
                })
                .catch(function () {
                    btnParse.disabled = false;
                    btnParse.innerHTML = original;
                    Toast.error('Lỗi kết nối server.');
                });
        });
    }

    function renderPreview(rows, errors) {
        previewBody.innerHTML = '';
        rows.forEach(function (row, idx) {
            var tr = document.createElement('tr');
            tr.innerHTML =
                '<td>' + (idx + 1) + '</td>' +
                '<td>' + esc(row.full_name) + '</td>' +
                '<td>' + esc(row.lucky_code) + '</td>' +
                '<td>' + esc(row.unit_label) + '</td>' +
                '<td>' + esc(row.prize_tier) + '</td>' +
                '<td>' + esc(row.prize_name) + '</td>';
            previewBody.appendChild(tr);
        });

        previewCount.textContent = rows.length + ' dòng hợp lệ';

        if (errors.length) {
            previewErrors.innerHTML = '<b>Có ' + errors.length + ' dòng bị bỏ qua:</b><ul class="mb-0 mt-1">' +
                errors.map(function (e) { return '<li>' + esc(e) + '</li>'; }).join('') + '</ul>';
            previewErrors.classList.remove('d-none');
        } else {
            previewErrors.classList.add('d-none');
        }

        previewCard.classList.remove('d-none');
        btnSave.disabled = rows.length === 0;
    }

    // ---- Lưu ----
    if (btnSave) {
        btnSave.addEventListener('click', function () {
            if (!parsedRows.length) {
                Toast.warning('Không có dữ liệu để lưu.');
                return;
            }
            var original = btnSave.innerHTML;
            btnSave.disabled = true;
            btnSave.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i>Đang lưu...';

            fetch(saveUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ event_id: parseInt(eventSelect.value, 10), rows: parsedRows })
            })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (!data.success) {
                        btnSave.disabled = false;
                        btnSave.innerHTML = original;
                        Toast.error(data.message || 'Lưu thất bại.');
                        return;
                    }
                    if (data.warnings && data.warnings.length) {
                        Toast.warning('Có ' + data.warnings.length + ' mã trùng (vẫn được thêm). Xem lại danh sách.');
                    }
                    Toast.success(data.message || 'Import thành công.');
                    setTimeout(function () { window.location.reload(); }, 1200);
                })
                .catch(function () {
                    btnSave.disabled = false;
                    btnSave.innerHTML = original;
                    Toast.error('Lỗi kết nối server.');
                });
        });
    }

    // ---- Xoá 1 dòng ----
    root.querySelectorAll('.btn-del-winner').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = this.getAttribute('data-id');
            Swal.fire({
                title: 'Xác nhận xoá',
                text: 'Xoá người trúng giải này khỏi danh sách?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Xoá',
                cancelButtonText: 'Hủy'
            }).then(function (result) {
                if (!result.isConfirmed) {
                    return;
                }
                fetch(deleteUrlTpl.replace('__ID__', id), { method: 'POST' })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (data.success) {
                            Toast.success(data.message || 'Đã xoá.');
                            setTimeout(function () { window.location.reload(); }, 800);
                        } else {
                            Toast.error(data.message || 'Không xoá được.');
                        }
                    })
                    .catch(function () { Toast.error('Lỗi kết nối server.'); });
            });
        });
    });

    // ---- Xoá toàn bộ ----
    if (btnClear) {
        btnClear.addEventListener('click', function () {
            Swal.fire({
                title: 'Xoá toàn bộ danh sách?',
                text: 'Toàn bộ người trúng giải của sự kiện này sẽ bị xoá. Hành động này không thể hoàn tác.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Xoá hết',
                cancelButtonText: 'Hủy'
            }).then(function (result) {
                if (!result.isConfirmed) {
                    return;
                }
                fetch(clearUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ event_id: parseInt(eventSelect.value, 10) })
                })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (data.success) {
                            Toast.success(data.message || 'Đã xoá.');
                            setTimeout(function () { window.location.reload(); }, 800);
                        } else {
                            Toast.error(data.message || 'Không xoá được.');
                        }
                    })
                    .catch(function () { Toast.error('Lỗi kết nối server.'); });
            });
        });
    }
})();
