/**
 * Cài đặt Quay số may mắn: lưu cấu hình, điều kiện loại trừ, CRUD giải thưởng.
 */
document.addEventListener('DOMContentLoaded', function () {
    var root = document.getElementById('lucky-settings');
    if (!root) return;

    var saveSettingsUrl = root.getAttribute('data-save-settings-url');
    var savePrizeUrl = root.getAttribute('data-save-prize-url');
    var deletePrizeTpl = root.getAttribute('data-delete-prize-url');
    var resetUrl = root.getAttribute('data-reset-url');

    // ---------- Điều kiện loại trừ ----------
    var excludeRows = document.getElementById('exclude-rows');
    var excludeTpl = document.getElementById('exclude-row-template');

    document.getElementById('btn-add-exclude').addEventListener('click', function () {
        var node = excludeTpl.content.cloneNode(true);
        excludeRows.appendChild(node);
    });

    excludeRows.addEventListener('click', function (e) {
        var btn = e.target.closest('.btn-remove-exclude');
        if (btn) btn.closest('.exclude-row').remove();
    });

    function collectExcludeColumns() {
        var rows = excludeRows.querySelectorAll('.exclude-row');
        var out = [];
        rows.forEach(function (r) {
            var col = r.querySelector('.exclude-col').value;
            var val = r.querySelector('.exclude-val').value.trim();
            if (col && val !== '') out.push({ column: col, value: val });
        });
        return out;
    }

    // ---------- Lưu cấu hình chung ----------
    var formSettings = document.getElementById('form-settings');
    if (formSettings) {
        formSettings.addEventListener('submit', function (e) {
            e.preventDefault();
            var btn = document.getElementById('btn-save-settings');
            var original = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i>Đang lưu...';

            var fd = new FormData(formSettings);
            fd.append('exclude_columns', JSON.stringify(collectExcludeColumns()));

            fetch(saveSettingsUrl, { method: 'POST', body: fd })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    btn.disabled = false;
                    btn.innerHTML = original;
                    if (data.success) {
                        Toast.success(data.message || 'Đã lưu cấu hình');
                    } else {
                        Toast.error(data.message || 'Không lưu được');
                    }
                })
                .catch(function () {
                    btn.disabled = false;
                    btn.innerHTML = original;
                    Toast.error('Lỗi kết nối server');
                });
        });
    }

    // ---------- Modal giải thưởng ----------
    var modalEl = document.getElementById('modal-prize');
    var formPrize = document.getElementById('form-prize');

    function fillPrizeForm(p) {
        document.getElementById('prize-id').value = p ? p.id : '';
        document.getElementById('prize-name').value = p ? (p.prize_name || '') : '';
        document.getElementById('prize-order').value = p ? (p.prize_order || '') : '';
        document.getElementById('prize-code').value = p ? (p.code || '') : '';
        document.getElementById('prize-quantity').value = p ? (p.quantity || 1) : 1;
        document.getElementById('prize-spin-count').value = p ? (p.spin_count || 1) : 1;
        document.getElementById('prize-duration').value = p ? (p.duration || 0) : 3200;
        document.getElementById('prize-stagger').value = p ? (p.stagger_duration || 0) : 0;
        document.getElementById('prize-exclude-levels').value = p ? (p.exclude_levels || '') : '';
        document.getElementById('modal-prize-title').textContent = p ? 'Sửa giải' : 'Thêm giải';

        var sel = document.getElementById('prize-eligible-types');
        var types = (p && Array.isArray(p.eligible_types)) ? p.eligible_types.map(String) : [];
        Array.prototype.forEach.call(sel.options, function (opt) {
            opt.selected = types.indexOf(opt.value) !== -1;
        });
    }

    var btnAdd = document.getElementById('btn-add-prize');
    if (btnAdd) btnAdd.addEventListener('click', function () { fillPrizeForm(null); });

    document.querySelectorAll('.btn-edit-prize').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var tr = btn.closest('tr');
            var p = JSON.parse(tr.getAttribute('data-prize'));
            fillPrizeForm(p);
            var modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        });
    });

    if (formPrize) {
        formPrize.addEventListener('submit', function (e) {
            e.preventDefault();
            var btn = document.getElementById('btn-submit-prize');
            var original = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i>Đang lưu...';

            var fd = new FormData(formPrize);
            fetch(savePrizeUrl, { method: 'POST', body: fd })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (data.success) {
                        var modal = bootstrap.Modal.getInstance(modalEl);
                        if (modal) modal.hide();
                        Toast.success(data.message || 'Đã lưu');
                        setTimeout(function () { location.reload(); }, 600);
                    } else {
                        btn.disabled = false;
                        btn.innerHTML = original;
                        Toast.error(data.message || 'Không lưu được');
                    }
                })
                .catch(function () {
                    btn.disabled = false;
                    btn.innerHTML = original;
                    Toast.error('Lỗi kết nối server');
                });
        });
    }

    // ---------- Xoá giải ----------
    document.querySelectorAll('.btn-del-prize').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = btn.getAttribute('data-id');
            Swal.fire({
                title: 'Xoá giải này?',
                text: 'Mọi kết quả trúng của giải cũng bị xoá. Không thể hoàn tác.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Xoá',
                cancelButtonText: 'Hủy'
            }).then(function (result) {
                if (!result.isConfirmed) return;
                fetch(deletePrizeTpl.replace('__ID__', id), { method: 'DELETE' })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (data.success) {
                            Toast.success(data.message || 'Đã xoá');
                            setTimeout(function () { location.reload(); }, 600);
                        } else {
                            Toast.error(data.message || 'Không xoá được');
                        }
                    })
                    .catch(function () { Toast.error('Lỗi kết nối server'); });
            });
        });
    });

    // ---------- Reset kết quả ----------
    var btnReset = document.getElementById('btn-reset-winners');
    if (btnReset) {
        btnReset.addEventListener('click', function () {
            Swal.fire({
                title: 'Reset toàn bộ kết quả?',
                text: 'Xoá tất cả người đã trúng ở mọi giải. Không thể hoàn tác.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Reset',
                cancelButtonText: 'Hủy'
            }).then(function (result) {
                if (!result.isConfirmed) return;
                fetch(resetUrl, { method: 'POST' })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (data.success) {
                            Toast.success(data.message || 'Đã reset');
                            setTimeout(function () { location.reload(); }, 600);
                        } else {
                            Toast.error(data.message || 'Không reset được');
                        }
                    })
                    .catch(function () { Toast.error('Lỗi kết nối server'); });
            });
        });
    }
});
