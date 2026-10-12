/**
 * Gán thủ công (kéo-thả) đội vào bảng đấu / vị trí seed nhánh.
 * Dùng HTML5 Drag and Drop API thuần (không phụ thuộc thư viện ngoài).
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var board = document.getElementById('assign-board');
        if (!board) {
            return;
        }

        var saveUrl = board.getAttribute('data-save-url');
        var mode = board.getAttribute('data-mode'); // 'groups' | 'bracket'
        var dragged = null;

        var poolZone = board.querySelector('[data-zone="pool"]');
        var poolCount = document.getElementById('pool-count');

        function updatePoolCount() {
            if (poolCount && poolZone) {
                poolCount.textContent = poolZone.querySelectorAll('.team-chip').length;
            }
        }

        // ===== Drag events (ủy quyền trên board) =====
        board.addEventListener('dragstart', function (e) {
            var chip = e.target.closest ? e.target.closest('.team-chip') : null;
            if (!chip) {
                return;
            }
            dragged = chip;
            chip.classList.add('opacity-50');
            if (e.dataTransfer) {
                e.dataTransfer.effectAllowed = 'move';
                e.dataTransfer.setData('text/plain', chip.getAttribute('data-team-id'));
            }
        });

        board.addEventListener('dragend', function () {
            if (dragged) {
                dragged.classList.remove('opacity-50');
            }
            dragged = null;
            clearHighlight();
        });

        board.addEventListener('dragover', function (e) {
            var zone = e.target.closest ? e.target.closest('.team-zone') : null;
            if (!zone) {
                return;
            }
            e.preventDefault();
            if (e.dataTransfer) {
                e.dataTransfer.dropEffect = 'move';
            }
            zone.classList.add('bg-light', 'border-primary');
        });

        board.addEventListener('dragleave', function (e) {
            var zone = e.target.closest ? e.target.closest('.team-zone') : null;
            if (zone) {
                zone.classList.remove('bg-light', 'border-primary');
            }
        });

        board.addEventListener('drop', function (e) {
            var zone = e.target.closest ? e.target.closest('.team-zone') : null;
            if (!zone || !dragged) {
                return;
            }
            e.preventDefault();
            zone.classList.remove('bg-light', 'border-primary');

            // Ô seed chỉ chứa 1 đội: nếu đã có, đẩy đội cũ về pool
            var isSeedSlot = zone.getAttribute('data-zone') === 'seed';
            if (isSeedSlot) {
                var existing = zone.querySelector('.team-chip');
                if (existing && existing !== dragged && poolZone) {
                    poolZone.appendChild(existing);
                }
            }
            zone.appendChild(dragged);
            updatePoolCount();
        });

        function clearHighlight() {
            var zones = board.querySelectorAll('.team-zone');
            for (var i = 0; i < zones.length; i++) {
                zones[i].classList.remove('bg-light', 'border-primary');
            }
        }

        // ===== Thu thập dữ liệu phân bổ =====
        function collectAssignments() {
            var out = [];
            if (mode === 'groups') {
                var groups = board.querySelectorAll('[data-zone="group"]');
                for (var g = 0; g < groups.length; g++) {
                    var label = groups[g].getAttribute('data-group');
                    var chips = groups[g].querySelectorAll('.team-chip');
                    for (var i = 0; i < chips.length; i++) {
                        out.push({
                            team_id: chips[i].getAttribute('data-team-id'),
                            group_label: label,
                            group_order: i + 1
                        });
                    }
                }
            } else if (mode === 'bracket') {
                var slots = board.querySelectorAll('[data-zone="seed"]');
                for (var s = 0; s < slots.length; s++) {
                    var chip = slots[s].querySelector('.team-chip');
                    if (chip) {
                        out.push({
                            team_id: chip.getAttribute('data-team-id'),
                            seed: parseInt(slots[s].getAttribute('data-seed'), 10)
                        });
                    }
                }
            }
            return out;
        }

        // ===== Lưu =====
        var btnSave = document.getElementById('btn-save-assignment');
        if (btnSave) {
            btnSave.addEventListener('click', function () {
                var assignments = collectAssignments();
                if (!assignments.length) {
                    Toast.warning('Chưa phân bổ đội nào.');
                    return;
                }

                var originalHtml = btnSave.innerHTML;
                btnSave.disabled = true;
                btnSave.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i>Đang lưu...';

                var formData = new FormData();
                formData.append('assignments', JSON.stringify(assignments));

                fetch(saveUrl, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: formData
                })
                    .then(function (res) { return res.json(); })
                    .then(function (data) {
                        btnSave.disabled = false;
                        btnSave.innerHTML = originalHtml;
                        if (data && data.success) {
                            Toast.success(data.message || 'Đã lưu phân bổ.');
                        } else {
                            Toast.error((data && data.message) || 'Không thể lưu phân bổ.');
                        }
                    })
                    .catch(function () {
                        btnSave.disabled = false;
                        btnSave.innerHTML = originalHtml;
                        Toast.error('Lỗi kết nối server.');
                    });
            });
        }

        updatePoolCount();
    });
})();
