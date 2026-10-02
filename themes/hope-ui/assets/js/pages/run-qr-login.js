/**
 * Quét QR thẻ tham dự trên trang đăng nhập cổng chạy.
 * Dùng html5-qrcode mở camera, đọc qr_token rồi submit form ẩn về server.
 * Yêu cầu HTTPS (hoặc localhost) để trình duyệt cho phép camera.
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var btnScan = document.getElementById('btn-scan-qr');
        var btnStop = document.getElementById('btn-stop-qr');
        var wrap = document.getElementById('qr-scan-wrap');
        var form = document.getElementById('qr-login-form');
        var valueInput = document.getElementById('qr_value');

        if (!btnScan || !wrap || !form || typeof Html5Qrcode === 'undefined') {
            return;
        }

        var scanner = null;
        var submitted = false;

        function stop() {
            if (scanner) {
                scanner.stop().then(function () {
                    scanner.clear();
                    scanner = null;
                }).catch(function () { scanner = null; });
            }
            wrap.style.display = 'none';
            btnScan.style.display = 'block';
        }

        function onScanSuccess(decodedText) {
            if (submitted) {
                return;
            }
            submitted = true;
            valueInput.value = decodedText;
            if (scanner) {
                scanner.stop().then(function () { form.submit(); }).catch(function () { form.submit(); });
            } else {
                form.submit();
            }
        }

        btnScan.addEventListener('click', function () {
            wrap.style.display = 'block';
            btnScan.style.display = 'none';
            scanner = new Html5Qrcode('qr-reader');
            scanner.start(
                { facingMode: 'environment' },
                { fps: 10, qrbox: { width: 220, height: 220 } },
                onScanSuccess,
                function () { /* bỏ qua frame không đọc được */ }
            ).catch(function (err) {
                wrap.style.display = 'none';
                btnScan.style.display = 'block';
                if (typeof Toast !== 'undefined') {
                    Toast.error('Không mở được camera. Hãy kiểm tra quyền camera và dùng kết nối HTTPS.');
                }
            });
        });

        if (btnStop) {
            btnStop.addEventListener('click', stop);
        }
    });
})();
