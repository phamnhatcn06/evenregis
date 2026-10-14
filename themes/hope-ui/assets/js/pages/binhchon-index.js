/**
 * Cổng bình chọn Miss - Vòng chung kết.
 * Đăng nhập Google (Google Identity Services) rồi gửi credential kèm mỗi lượt
 * bình chọn lên server để xác thực.
 */
(function () {
    'use strict';

    var voterBox = document.getElementById('voter-box');
    if (!voterBox) {
        return;
    }

    var voteUrl = voterBox.getAttribute('data-vote-url');
    var clientId = voterBox.getAttribute('data-client-id');
    var credential = null; // Google ID token sau khi đăng nhập

    // Google gọi hàm này sau khi khách đăng nhập thành công
    window.onGoogleCredential = function (response) {
        credential = response.credential;
        showSignedIn(response);
    };

    function showSignedIn(response) {
        var signin = document.getElementById('g_id_signin');
        if (signin) {
            signin.classList.add('d-none');
        }
        var info = document.getElementById('signed-in-info');
        if (info) {
            info.classList.remove('d-none');
        }
        // Giải mã tên từ JWT payload để hiển thị (không dùng để xác thực)
        try {
            var payload = JSON.parse(atob(response.credential.split('.')[1]));
            var nameEl = document.getElementById('voter-name');
            if (nameEl) {
                nameEl.textContent = payload.name || payload.email || '';
            }
        } catch (e) {}
        if (typeof Toast !== 'undefined') {
            Toast.success('Đăng nhập thành công! Hãy bình chọn cho thí sinh bạn yêu thích.');
        }
    }

    function initGoogle() {
        if (typeof google === 'undefined' || !google.accounts || !google.accounts.id) {
            return false;
        }
        if (!clientId || clientId.indexOf('YOUR_GOOGLE_CLIENT_ID') !== -1) {
            return true; // chưa cấu hình -> không init
        }
        google.accounts.id.initialize({
            client_id: clientId,
            callback: window.onGoogleCredential
        });
        var target = document.getElementById('g_id_signin');
        if (target) {
            google.accounts.id.renderButton(target, {
                theme: 'outline',
                size: 'large',
                text: 'signin_with',
                locale: 'vi'
            });
        }
        return true;
    }

    // Thư viện Google tải bất đồng bộ -> thử lại vài lần
    var tries = 0;
    var timer = setInterval(function () {
        if (initGoogle() || ++tries > 20) {
            clearInterval(timer);
        }
    }, 150);

    function vote(button) {
        var contestantId = button.getAttribute('data-contestant');

        if (!credential) {
            if (typeof Toast !== 'undefined') {
                Toast.warning('Vui lòng đăng nhập bằng Google trước khi bình chọn.');
            }
            return;
        }

        var original = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i>Đang gửi...';

        var body = new FormData();
        body.append('contestant_id', contestantId);
        body.append('credential', credential);

        fetch(voteUrl, { method: 'POST', body: body })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.success) {
                    button.classList.remove('btn-outline-primary');
                    button.classList.add('btn-success');
                    button.innerHTML = '<i class="fa fa-check me-1"></i>Đã bình chọn';
                    button.disabled = true;

                    var countEl = document.querySelector('.vote-count[data-contestant="' + contestantId + '"]');
                    if (countEl && typeof data.vote_count !== 'undefined') {
                        countEl.textContent = data.vote_count;
                    }
                    var remEl = document.getElementById('remaining-votes');
                    if (remEl && typeof data.remaining !== 'undefined') {
                        remEl.textContent = data.remaining;
                        if (data.remaining <= 0) {
                            disableAllVoteButtons();
                        }
                    }
                    if (typeof Toast !== 'undefined') {
                        Toast.success(data.message || 'Bình chọn thành công!');
                    }
                } else {
                    button.disabled = false;
                    button.innerHTML = original;
                    if (typeof Toast !== 'undefined') {
                        Toast.error(data.message || 'Có lỗi xảy ra.');
                    }
                }
            })
            .catch(function () {
                button.disabled = false;
                button.innerHTML = original;
                if (typeof Toast !== 'undefined') {
                    Toast.error('Lỗi kết nối máy chủ.');
                }
            });
    }

    function disableAllVoteButtons() {
        var btns = document.querySelectorAll('.btn-vote');
        btns.forEach(function (b) {
            if (!b.disabled) {
                b.disabled = true;
            }
        });
    }

    document.addEventListener('click', function (e) {
        var btn = e.target.closest ? e.target.closest('.btn-vote') : null;
        if (btn && !btn.disabled) {
            vote(btn);
        }
    });
})();
