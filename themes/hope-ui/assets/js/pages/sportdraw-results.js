/**
 * Ghi kết quả từng trận. Sau khi lưu, backend tự đẩy đội thắng sang vòng sau;
 * tải lại trang để thấy các trận vòng sau được điền đội.
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var board = document.getElementById('results-board');
        if (!board) {
            return;
        }
        var saveUrl = board.getAttribute('data-save-url');

        board.addEventListener('click', function (e) {
            var btn = e.target.closest ? e.target.closest('.btn-save-result') : null;
            if (!btn) {
                return;
            }

            var row = btn.closest('.match-row');
            var matchId = row.getAttribute('data-match-id');
            var scoreA = row.querySelector('.score-a');
            var scoreB = row.querySelector('.score-b');

            if (!scoreA || !scoreB || scoreA.value === '' || scoreB.value === '') {
                Toast.warning('Vui lòng nhập đủ tỉ số hai đội.');
                return;
            }

            var originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';

            var formData = new FormData();
            formData.append('match_id', matchId);
            formData.append('score_a', scoreA.value);
            formData.append('score_b', scoreB.value);

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
