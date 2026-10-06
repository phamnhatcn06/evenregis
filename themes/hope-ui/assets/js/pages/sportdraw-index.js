document.addEventListener('DOMContentLoaded', function () {
    var cfg = document.getElementById('sport-draw-config');
    if (!cfg) {
        return;
    }

    var configUrlTpl = cfg.getAttribute('data-config-url');
    var groupsUrlTpl = cfg.getAttribute('data-draw-groups-url');
    var bracketUrlTpl = cfg.getAttribute('data-draw-bracket-url');
    var lockUrlTpl = cfg.getAttribute('data-lock-url');
    var standingsUrlTpl = cfg.getAttribute('data-standings-url');
    var genkoUrlTpl = cfg.getAttribute('data-generate-knockout-url');

    var GROUP_FORMATS = ['round_robin', 'round_robin_knockout'];
    var BRACKET_FORMATS = ['single_elimination', 'round_robin_knockout'];

    function buildUrl(tpl, id) {
        return tpl.replace('__ID__', id);
    }

    function postJson(url, onDone) {
        var fd = new FormData();
        fetch(url, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (res) { return res.json(); })
            .then(onDone)
            .catch(function () {
                Toast.error('Lỗi kết nối máy chủ');
                onDone({ success: false, message: 'Lỗi kết nối' });
            });
    }

    /* ===== Modal cấu hình ===== */
    var modalEl = document.getElementById('modal-config');
    var formatSelect = document.getElementById('cfg-format');
    var groupFields = document.querySelector('.cfg-group-fields');
    var bracketFields = document.querySelector('.cfg-bracket-fields');

    function toggleFormatFields() {
        var val = formatSelect.value;
        groupFields.classList.toggle('d-none', GROUP_FORMATS.indexOf(val) === -1);
        bracketFields.classList.toggle('d-none', BRACKET_FORMATS.indexOf(val) === -1);
    }

    if (formatSelect) {
        formatSelect.addEventListener('change', toggleFormatFields);
    }

    document.querySelectorAll('.btn-config').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var data = JSON.parse(this.getAttribute('data-config'));
            document.getElementById('cfg-id').value = data.id;
            document.getElementById('cfg-name').value = data.name || '';
            formatSelect.value = data.format || '';
            document.getElementById('cfg-num-groups').value = data.num_groups || '';
            document.getElementById('cfg-advance').value = data.advance_per_group || '';
            document.getElementById('cfg-bracket-size').value = data.bracket_size || '';
            toggleFormatFields();
            bootstrap.Modal.getOrCreateInstance(modalEl).show();
        });
    });

    var formConfig = document.getElementById('form-config');
    if (formConfig) {
        formConfig.addEventListener('submit', function (e) {
            e.preventDefault();
            var btn = document.getElementById('btn-config-submit');
            var original = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i>Đang lưu...';

            var id = document.getElementById('cfg-id').value;
            var fd = new FormData(formConfig);
            fetch(buildUrl(configUrlTpl, id), {
                method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(function (res) { return res.json(); })
                .then(function (d) {
                    if (d.success) {
                        bootstrap.Modal.getInstance(modalEl).hide();
                        Toast.success(d.message || 'Đã lưu');
                        setTimeout(function () { location.reload(); }, 800);
                    } else {
                        btn.disabled = false;
                        btn.innerHTML = original;
                        Toast.error(d.message || 'Có lỗi xảy ra');
                    }
                })
                .catch(function () {
                    btn.disabled = false;
                    btn.innerHTML = original;
                    Toast.error('Lỗi kết nối máy chủ');
                });
        });
    }

    /* ===== Bốc thăm ===== */
    document.querySelectorAll('.btn-draw').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = this.getAttribute('data-id');
            var type = this.getAttribute('data-type');
            var isGroups = (type === 'groups');
            var url = buildUrl(isGroups ? groupsUrlTpl : bracketUrlTpl, id);
            var title = isGroups ? 'Bốc thăm chia bảng?' : 'Bốc thăm sơ đồ loại trực tiếp?';

            Swal.fire({
                title: title,
                text: 'Kết quả bốc thăm cũ (nếu có) sẽ bị thay thế.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                confirmButtonText: 'Bốc thăm',
                cancelButtonText: 'Hủy'
            }).then(function (result) {
                if (!result.isConfirmed) {
                    return;
                }
                postJson(url, function (d) {
                    if (d.success) {
                        Toast.success(d.message || 'Bốc thăm thành công');
                        setTimeout(function () { location.reload(); }, 800);
                    } else {
                        Toast.error(d.message || 'Bốc thăm thất bại');
                    }
                });
            });
        });
    });

    /* ===== Tính bảng xếp hạng ===== */
    document.querySelectorAll('.btn-standings').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = this.getAttribute('data-id');
            postJson(buildUrl(standingsUrlTpl, id), function (d) {
                if (d.success) {
                    Toast.success(d.message || 'Đã tính bảng xếp hạng');
                    setTimeout(function () { location.reload(); }, 800);
                } else {
                    Toast.error(d.message || 'Không thể tính BXH');
                }
            });
        });
    });

    /* ===== Sinh nhánh từ bảng ===== */
    document.querySelectorAll('.btn-genko').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = this.getAttribute('data-id');
            Swal.fire({
                title: 'Sinh sơ đồ loại trực tiếp từ vòng bảng?',
                text: 'Lấy các đội đi tiếp theo BXH hiện tại. Sơ đồ KO cũ sẽ bị thay thế.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                confirmButtonText: 'Sinh nhánh',
                cancelButtonText: 'Hủy'
            }).then(function (result) {
                if (!result.isConfirmed) {
                    return;
                }
                postJson(buildUrl(genkoUrlTpl, id), function (d) {
                    if (d.success) {
                        Toast.success(d.message || 'Đã sinh sơ đồ');
                        setTimeout(function () { location.reload(); }, 800);
                    } else {
                        Toast.error(d.message || 'Không thể sinh sơ đồ');
                    }
                });
            });
        });
    });

    /* ===== Khoá kết quả ===== */
    document.querySelectorAll('.btn-lock').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = this.getAttribute('data-id');
            Swal.fire({
                title: 'Khoá kết quả bốc thăm?',
                text: 'Sau khi khoá sẽ không thể bốc lại.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Khoá',
                cancelButtonText: 'Hủy'
            }).then(function (result) {
                if (!result.isConfirmed) {
                    return;
                }
                postJson(buildUrl(lockUrlTpl, id), function (d) {
                    if (d.success) {
                        Toast.success(d.message || 'Đã khoá');
                        setTimeout(function () { location.reload(); }, 800);
                    } else {
                        Toast.error(d.message || 'Không thể khoá');
                    }
                });
            });
        });
    });
});
