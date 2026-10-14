/**
 * Cổng bình chọn Miss - Vòng chung kết.
 * Đăng nhập Google (Google Identity Services) rồi gửi credential kèm mỗi lượt
 * bình chọn lên server để xác thực. Backend áp dụng hạn mức theo tài khoản Google.
 */
(function () {
    'use strict';

    var voterBox = document.getElementById('voter-box');
    if (!voterBox) {
        return;
    }

    var voteUrl = voterBox.getAttribute('data-vote-url');
    var statusUrl = voterBox.getAttribute('data-status-url');
    var clientId = voterBox.getAttribute('data-client-id');
    var credential = null; // Google ID token sau khi đăng nhập

    // Google gọi hàm này sau khi khách đăng nhập thành công
    window.onGoogleCredential = function (response) {
        credential = response.credential;
        loadVoterStatus();
    };

    function loadVoterStatus() {
        var body = new FormData();
        body.append('credential', credential);

        fetch(statusUrl, { method: 'POST', body: body })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (!data.success) {
                    if (typeof Toast !== 'undefined') { Toast.error(data.message || 'Xác thực thất bại.'); }
                    return;
                }
                showSignedIn(data.name, data.remaining);
                markVotedButtons(data.voted_ids || []);
                if (data.remaining <= 0) {
                    disableAllVoteButtons();
                }
                if (typeof Toast !== 'undefined') {
                    Toast.success('Đăng nhập thành công! Hãy bình chọn cho thí sinh bạn yêu thích.');
                }
            })
            .catch(function () {
                if (typeof Toast !== 'undefined') { Toast.error('Lỗi kết nối máy chủ.'); }
            });
    }

    function showSignedIn(name, remaining) {
        var signinArea = document.getElementById('signin-area');
        if (signinArea) { signinArea.classList.add('d-none'); }
        var info = document.getElementById('signed-in-info');
        if (info) { info.classList.remove('d-none'); }
        var nameEl = document.getElementById('voter-name');
        if (nameEl) { nameEl.textContent = name || ''; }
        var remEl = document.getElementById('remaining-votes');
        if (remEl && typeof remaining !== 'undefined') { remEl.textContent = remaining; }
    }

    function markVotedButtons(votedIds) {
        votedIds.forEach(function (cid) {
            var btn = document.querySelector('.btn-vote[data-contestant="' + cid + '"]');
            if (btn) { setVoted(btn); }
        });
    }

    function setVoted(button) {
        button.classList.remove('btn-outline-primary');
        button.classList.add('btn-success');
        button.innerHTML = '<i class="fa fa-check me-1"></i>Đã bình chọn';
        button.disabled = true;
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
                updateCount(contestantId, data.vote_count);
                updateRemaining(data.remaining);

                if (data.success) {
                    setVoted(button);
                    if (data.remaining <= 0) { disableAllVoteButtons(); }
                    if (typeof Toast !== 'undefined') { Toast.success(data.message || 'Bình chọn thành công!'); }
                } else {
                    // Đồng bộ lại trạng thái nếu đã chọn rồi / hết lượt
                    markVotedButtons(data.voted_ids || []);
                    if (!button.disabled) {
                        button.disabled = false;
                        button.innerHTML = original;
                    }
                    if (typeof Toast !== 'undefined') { Toast.error(data.message || 'Có lỗi xảy ra.'); }
                }
            })
            .catch(function () {
                button.disabled = false;
                button.innerHTML = original;
                if (typeof Toast !== 'undefined') { Toast.error('Lỗi kết nối máy chủ.'); }
            });
    }

    function updateCount(contestantId, count) {
        if (typeof count === 'undefined' || count === null) { return; }
        var el = document.querySelector('.vote-count[data-contestant="' + contestantId + '"]');
        if (el) { el.textContent = count; }
    }

    function updateRemaining(remaining) {
        if (typeof remaining === 'undefined' || remaining === null) { return; }
        var el = document.getElementById('remaining-votes');
        if (el) { el.textContent = remaining; }
    }

    function disableAllVoteButtons() {
        document.querySelectorAll('.btn-vote').forEach(function (b) {
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
