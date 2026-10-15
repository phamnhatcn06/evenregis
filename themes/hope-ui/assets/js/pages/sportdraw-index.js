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
    var playoffUrlTpl = cfg.getAttribute('data-draw-playoff-url');
    var peListUrlTpl = cfg.getAttribute('data-playoff-entrants-list-url');
    var peMoveUrlTpl = cfg.getAttribute('data-playoff-entrants-move-url');
    var promotePlayoffUrlTpl = cfg.getAttribute('data-promote-playoff-url');
    var swimDrawUrlTpl = cfg.getAttribute('data-draw-swim-url');
    var promoteSwimUrlTpl = cfg.getAttribute('data-promote-swim-url');
    var promoteGroupsUrlTpl = cfg.getAttribute('data-promote-groups-url');

    var GROUP_FORMATS = ['round_robin', 'round_robin_knockout'];
    var BRACKET_FORMATS = ['single_elimination', 'round_robin_knockout'];

    function buildUrl(tpl, id) {
        return tpl.replace('__ID__', id);
    }

    function postJson(url, onDone, params) {
        var fd = new FormData();
        if (params) {
            Object.keys(params).forEach(function (k) { fd.append(k, params[k]); });
        }
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
    var groupFields = document.querySelectorAll('.cfg-group-fields');
    var bracketFields = document.querySelector('.cfg-bracket-fields');
    var pairingFields = document.querySelector('.cfg-pairing-fields');

    function toggleFormatFields() {
        var val = formatSelect.value;
        groupFields.forEach(function (el) {
            el.classList.toggle('d-none', GROUP_FORMATS.indexOf(val) === -1);
        });
        bracketFields.classList.toggle('d-none', BRACKET_FORMATS.indexOf(val) === -1);
        pairingFields.classList.toggle('d-none', val !== 'round_robin_knockout');
    }

    function valueOr(value, fallback) {
        return (value === null || value === undefined || value === '') ? fallback : value;
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
            document.getElementById('cfg-win-points').value = valueOr(data.win_points, 3);
            document.getElementById('cfg-draw-points').value = valueOr(data.draw_points, 1);
            document.getElementById('cfg-loss-points').value = valueOr(data.loss_points, 0);
            document.getElementById('cfg-tiebreak').value = valueOr(data.tiebreak, 'head_to_head');
            document.getElementById('cfg-ko-pairing').value = valueOr(data.ko_pairing, 'cross');
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
            var url, title;
            if (type === 'groups') {
                url = buildUrl(groupsUrlTpl, id);
                title = 'Bốc thăm chia bảng?';
            } else if (type === 'playoff') {
                url = buildUrl(playoffUrlTpl, id);
                title = 'Bốc thăm play-off?';
            } else {
                url = buildUrl(bracketUrlTpl, id);
                title = 'Bốc thăm sơ đồ loại trực tiếp?';
            }

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

    /* ===== Chọn VĐV từ vòng chung kết vào play-off ===== */
    var peModalEl = document.getElementById('modal-playoff-entrants');
    if (peModalEl) {
        var peCurrentStageId = null;
        var peListEl = document.getElementById('pe-list');
        var peCheckAll = document.getElementById('pe-check-all');
        var peCountEl = document.getElementById('pe-selected-count');
        var peSubmitBtn = document.getElementById('pe-submit');
        var peStageNameEl = document.getElementById('pe-stage-name');

        function peCheckboxes() {
            return peListEl.querySelectorAll('input.pe-item');
        }

        function peUpdateCount() {
            var total = peCheckboxes().length;
            var checked = peListEl.querySelectorAll('input.pe-item:checked').length;
            peCountEl.textContent = checked + '/' + total + ' đã chọn';
            if (peCheckAll) {
                peCheckAll.checked = total > 0 && checked === total;
            }
        }

        function peRenderList(items) {
            if (!Array.isArray(items) || items.length === 0) {
                peListEl.innerHTML = '<div class="text-center text-muted py-3">Vòng chung kết chưa có VĐV nào.</div>';
                peUpdateCount();
                return;
            }
            var html = '';
            items.forEach(function (it) {
                var id = 'pe-item-' + it.team_id;
                html += '<div class="form-check py-1 border-bottom">'
                    + '<input class="form-check-input pe-item" type="checkbox" value="' + it.team_id + '" id="' + id + '">'
                    + '<label class="form-check-label" for="' + id + '">' + (it.name || ('#' + it.team_id)) + '</label>'
                    + '</div>';
            });
            peListEl.innerHTML = html;
            peCheckboxes().forEach(function (cb) {
                cb.addEventListener('change', peUpdateCount);
            });
            peUpdateCount();
        }

        document.querySelectorAll('.btn-playoff-entrants').forEach(function (btn) {
            btn.addEventListener('click', function () {
                peCurrentStageId = this.getAttribute('data-id');
                peStageNameEl.textContent = this.getAttribute('data-name') || 'play-off';
                peListEl.innerHTML = '<div class="text-center text-muted py-3"><i class="fa fa-spinner fa-spin me-1"></i>Đang tải danh sách...</div>';
                if (peCheckAll) { peCheckAll.checked = false; }
                peCountEl.textContent = '0 đã chọn';
                bootstrap.Modal.getOrCreateInstance(peModalEl).show();

                fetch(buildUrl(peListUrlTpl, peCurrentStageId), { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(function (res) { return res.json(); })
                    .then(function (d) {
                        if (d.success) {
                            peRenderList(d.data || []);
                        } else {
                            peListEl.innerHTML = '<div class="text-center text-danger py-3">' + (d.message || 'Không tải được danh sách') + '</div>';
                        }
                    })
                    .catch(function () {
                        peListEl.innerHTML = '<div class="text-center text-danger py-3">Lỗi kết nối máy chủ</div>';
                    });
            });
        });

        if (peCheckAll) {
            peCheckAll.addEventListener('change', function () {
                var checked = this.checked;
                peCheckboxes().forEach(function (cb) { cb.checked = checked; });
                peUpdateCount();
            });
        }

        if (peSubmitBtn) {
            peSubmitBtn.addEventListener('click', function () {
                var ids = [];
                peListEl.querySelectorAll('input.pe-item:checked').forEach(function (cb) { ids.push(cb.value); });
                if (ids.length === 0) {
                    Toast.warning('Vui lòng chọn ít nhất một VĐV.');
                    return;
                }
                var original = peSubmitBtn.innerHTML;
                peSubmitBtn.disabled = true;
                peSubmitBtn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i>Đang chuyển...';

                var fd = new FormData();
                ids.forEach(function (id) { fd.append('team_ids[]', id); });
                fetch(buildUrl(peMoveUrlTpl, peCurrentStageId), {
                    method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                    .then(function (res) { return res.json(); })
                    .then(function (d) {
                        if (d.success) {
                            bootstrap.Modal.getInstance(peModalEl).hide();
                            Toast.success(d.message || 'Đã chuyển VĐV vào play-off');
                            setTimeout(function () { location.reload(); }, 900);
                        } else {
                            peSubmitBtn.disabled = false;
                            peSubmitBtn.innerHTML = original;
                            Toast.error(d.message || 'Không thể chuyển VĐV');
                        }
                    })
                    .catch(function () {
                        peSubmitBtn.disabled = false;
                        peSubmitBtn.innerHTML = original;
                        Toast.error('Lỗi kết nối máy chủ');
                    });
            });
        }
    }

    /* ===== Chuyển người thắng play-off sang vòng bảng ===== */
    document.querySelectorAll('.btn-promote-playoff').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = this.getAttribute('data-id');
            var targetId = this.getAttribute('data-target-id');
            var targetName = this.getAttribute('data-target-name') || 'vòng bảng';
            Swal.fire({
                title: 'Chuyển người thắng play-off?',
                text: 'Người/đội thắng các trận play-off (đã có kết quả) sẽ được thêm vào "' + targetName + '".',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                confirmButtonText: 'Chuyển',
                cancelButtonText: 'Hủy'
            }).then(function (result) {
                if (!result.isConfirmed) {
                    return;
                }
                postJson(buildUrl(promotePlayoffUrlTpl, id), function (d) {
                    if (d.success) {
                        var added = (d.data && d.data.added) ? d.data.added.length : 0;
                        Toast.success((d.message || 'Đã chuyển') + ' (' + added + ' suất mới)');
                        setTimeout(function () { location.reload(); }, 1000);
                    } else {
                        Toast.error(d.message || 'Không thể chuyển người thắng');
                    }
                }, { target_stage_id: targetId });
            });
        });
    });

    /* ===== Chuyển đội đi tiếp của vòng loại chia bảng sang vòng chung kết ===== */
    document.querySelectorAll('.btn-promote-groups').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = this.getAttribute('data-id');
            var targetId = this.getAttribute('data-target-id');
            var targetName = this.getAttribute('data-target-name') || 'vòng chung kết';
            Swal.fire({
                title: 'Chuyển đội đi tiếp vào vòng chung kết?',
                text: 'Hệ thống tính lại BXH vòng loại rồi đưa các đội đi tiếp vào "' + targetName + '".',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                confirmButtonText: 'Chuyển',
                cancelButtonText: 'Hủy'
            }).then(function (result) {
                if (!result.isConfirmed) {
                    return;
                }
                postJson(buildUrl(promoteGroupsUrlTpl, id), function (d) {
                    if (d.success) {
                        var added = (d.data && d.data.added) ? d.data.added.length : 0;
                        Toast.success((d.message || 'Đã chuyển') + ' (' + added + ' suất mới)');
                        setTimeout(function () { location.reload(); }, 1000);
                    } else {
                        Toast.error(d.message || 'Không thể chuyển đội đi tiếp');
                    }
                }, { target_stage_id: targetId });
            });
        });
    });

    /* ===== Bơi: bốc thăm phân lượt/làn ===== */
    document.querySelectorAll('.btn-swim-draw').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = this.getAttribute('data-id');
            Swal.fire({
                title: 'Bốc thăm phân lượt/làn?',
                text: 'Chia VĐV vào các lượt (6 làn/lượt). Kết quả cũ (nếu có) sẽ bị thay thế.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                confirmButtonText: 'Bốc thăm',
                cancelButtonText: 'Hủy'
            }).then(function (result) {
                if (!result.isConfirmed) {
                    return;
                }
                postJson(buildUrl(swimDrawUrlTpl, id), function (d) {
                    if (d.success) {
                        Toast.success(d.message || 'Đã bốc lượt/làn');
                        setTimeout(function () { location.reload(); }, 800);
                    } else {
                        Toast.error(d.message || 'Bốc thăm thất bại');
                    }
                }, { lane_count: 6 });
            });
        });
    });

    /* ===== Bơi: chuyển Top 6 vào chung kết ===== */
    document.querySelectorAll('.btn-promote-swim').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var id = this.getAttribute('data-id');
            var finalId = this.getAttribute('data-final-id');
            var finalName = this.getAttribute('data-final-name') || 'chung kết';
            Swal.fire({
                title: 'Chuyển Top 6 vào chung kết?',
                text: 'Lấy 6 VĐV có thành tích tốt nhất ở vòng loại vào "' + finalName + '".',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#0d6efd',
                confirmButtonText: 'Chuyển',
                cancelButtonText: 'Hủy'
            }).then(function (result) {
                if (!result.isConfirmed) {
                    return;
                }
                postJson(buildUrl(promoteSwimUrlTpl, id), function (d) {
                    if (d.success) {
                        var added = (d.data && d.data.added) ? d.data.added.length : 0;
                        Toast.success((d.message || 'Đã chuyển') + ' (' + added + ' suất mới)');
                        setTimeout(function () { location.reload(); }, 1000);
                    } else {
                        Toast.error(d.message || 'Không thể chuyển Top');
                    }
                }, { final_stage_id: finalId, top_n: 6 });
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
