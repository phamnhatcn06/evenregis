/**
 * Ghi kết quả từng trận. Sau khi lưu, backend tự đẩy đội thắng sang vòng sau;
 * chỉ refresh riêng phần bảng qua AJAX (không tải lại cả trang) để thấy các
 * trận vòng sau được điền đội.
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var board = document.getElementById('results-board');
        if (!board) {
            return;
        }
        var saveUrl = board.getAttribute('data-save-url');
        var reloadUrl = board.getAttribute('data-reload-url');

        // Tải lại ruột bảng qua AJAX và thay thế tại chỗ.
        function refreshBoard() {
            if (!reloadUrl) {
                return;
            }
            fetch(reloadUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function (res) { return res.text(); })
                .then(function (html) { board.innerHTML = html; })
                .catch(function () { /* giữ nguyên bảng hiện tại nếu lỗi */ });
        }

        board.addEventListener('click', function (e) {
            var btn = e.target.closest ? e.target.closest('.btn-save-result') : null;
            if (!btn) {
                return;
            }

            var row = btn.closest('.match-row');
            var matchId = row.getAttribute('data-match-id');
            var isKnockout = row.getAttribute('data-knockout') === '1';
            var scoreA = row.querySelector('.score-a');
            var scoreB = row.querySelector('.score-b');
            var winnerSelect = row.querySelector('.winner-select');
            var winnerId = winnerSelect ? winnerSelect.value : '';

            if (!scoreA || !scoreB || scoreA.value === '' || scoreB.value === '') {
                Toast.warning('Vui lòng nhập đủ tỉ số hai đội.');
                return;
            }

            // Trận loại trực tiếp không được hòa: phải chọn đội thắng khi bằng điểm
            var tie = parseInt(scoreA.value, 10) === parseInt(scoreB.value, 10);
            if (isKnockout && tie && !winnerId) {
                Toast.warning('Trận loại trực tiếp không được hòa — hãy chọn đội thắng.');
                return;
            }

            var originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';

            var formData = new FormData();
            formData.append('match_id', matchId);
            formData.append('score_a', scoreA.value);
            formData.append('score_b', scoreB.value);
            if (winnerId) {
                formData.append('winner_team_id', winnerId);
            }

            fetch(saveUrl, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                body: formData
            })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                    if (data && data.success) {
                        Toast.success(data.message || 'Đã ghi kết quả.');
                        var badge = row.querySelector('.result-badge');
                        if (badge) {
                            badge.classList.remove('d-none');
                        }
                        // Vòng sau có thể vừa được điền đội -> tải lại sau giây lát
                        setTimeout(function () { window.location.reload(); }, 800);
                    } else {
                        Toast.error((data && data.message) || 'Không thể ghi kết quả.');
                    }
                })
                .catch(function () {
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                    Toast.error('Lỗi kết nối server.');
                });
        });
    });
})();
