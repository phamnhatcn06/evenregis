/**
 * schedule-my.js — Đếm ngược realtime cho thẻ "Sự kiện tiếp theo".
 *
 * Đồng bộ theo thời gian máy chủ (data-server-time) để tránh lệch giờ thiết bị:
 * tính độ lệch giữa đồng hồ máy chủ và trình duyệt tại thời điểm tải trang, rồi
 * mỗi giây suy ra "giờ máy chủ hiện tại" = Date.now() + offset.
 */
(function () {
    'use strict';

    function pad(num) {
        return (num < 10 ? '0' : '') + num;
    }

    document.addEventListener('DOMContentLoaded', function () {
        var card = document.getElementById('nextUpCard');
        if (!card) {
            return;
        }

        var serverTime = parseInt(card.getAttribute('data-server-time'), 10);
        var targetTime = parseInt(card.getAttribute('data-target-time'), 10);
        var countdown = document.getElementById('nextUpCountdown');

        if (!countdown || !targetTime || targetTime <= 0 || !serverTime) {
            return;
        }

        // Độ lệch (giây) giữa giờ máy chủ và giờ trình duyệt lúc tải trang.
        var offsetSeconds = serverTime - Math.floor(Date.now() / 1000);

        var elD = countdown.querySelector('[data-cd="d"]');
        var elH = countdown.querySelector('[data-cd="h"]');
        var elM = countdown.querySelector('[data-cd="m"]');
        var elS = countdown.querySelector('[data-cd="s"]');

        var timer = null;

        function render() {
            var nowServer = Math.floor(Date.now() / 1000) + offsetSeconds;
            var diff = targetTime - nowServer;

            if (diff <= 0) {
                // Đã đến giờ: dừng đếm, chuyển trạng thái "Đang diễn ra".
                if (timer) {
                    clearInterval(timer);
                }
                countdown.classList.add('countdown-live');
                countdown.innerHTML = '<span class="cd-live"><span class="pulse-dot-green"></span> Đang diễn ra</span>';
                return;
            }

            var days = Math.floor(diff / 86400);
            var hours = Math.floor((diff % 86400) / 3600);
            var minutes = Math.floor((diff % 3600) / 60);
            var seconds = diff % 60;

            if (elD) { elD.textContent = pad(days); }
            if (elH) { elH.textContent = pad(hours); }
            if (elM) { elM.textContent = pad(minutes); }
            if (elS) { elS.textContent = pad(seconds); }
        }

        render();
        timer = setInterval(render, 1000);
    });
})();
