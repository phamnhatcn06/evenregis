/**
 * Cổng bình chọn Miss Mường Thanh 2026 - Vòng chung kết.
 * Đăng nhập Google hoặc tài khoản nội bộ (SSO token) rồi gửi bình chọn lên server.
 * Backend áp dụng hạn mức theo tài khoản.
 */
(function () {
    'use strict';

    // 1. Đếm ngược tới giờ mở cổng (hiển thị khi cổng chưa mở)
    initCountdown();

    var voterBox = document.getElementById('voter-box');
    if (!voterBox) {
        return;
    }

    var voteUrl = voterBox.getAttribute('data-vote-url');
    var statusUrl = voterBox.getAttribute('data-status-url');
    var clientId = voterBox.getAttribute('data-client-id');
    var maxVotes = parseInt(voterBox.getAttribute('data-max-votes') || '5', 10);
    var tokenAuth = voterBox.getAttribute('data-token-auth') === '1';
    var credential = null; // Google ID token sau khi đăng nhập

    // Khởi tạo các tính năng tương tác giao diện
    initSearchAndFilter();
    initPhotoModal();
    initStickyBar();
    renderHeartTokens(maxVotes);

    function initCountdown() {
        var el = document.getElementById('vote-countdown');
        if (!el) { return; }
        var start = new Date(el.getAttribute('data-start')).getTime();
        var serverNow = new Date(el.getAttribute('data-now')).getTime();
        if (isNaN(start) || isNaN(serverNow)) { return; }
        var offset = serverNow - Date.now(); // chênh lệch server - client

        var daysEl = document.getElementById('countdown-days');
        var hoursEl = document.getElementById('countdown-hours');
        var minsEl = document.getElementById('countdown-mins');
        var secsEl = document.getElementById('countdown-secs');

        var tick = function () {
            var remaining = start - (Date.now() + offset);
            if (remaining <= 0) {
                if (daysEl) { daysEl.textContent = '00'; }
                if (hoursEl) { hoursEl.textContent = '00'; }
                if (minsEl) { minsEl.textContent = '00'; }
                if (secsEl) { secsEl.textContent = '00'; }
                window.location.reload();
                return;
            }
            var s = Math.floor(remaining / 1000);
            var d = Math.floor(s / 86400); s -= d * 86400;
            var h = Math.floor(s / 3600); s -= h * 3600;
            var m = Math.floor(s / 60); s -= m * 60;
            var pad = function (n) { return (n < 10 ? '0' : '') + n; };

            if (daysEl && hoursEl && minsEl && secsEl) {
                daysEl.textContent = pad(d);
                hoursEl.textContent = pad(h);
                minsEl.textContent = pad(m);
                secsEl.textContent = pad(s);
            } else {
                var text = (d > 0 ? d + ' ngày ' : '') + pad(h) + ':' + pad(m) + ':' + pad(s);
                el.textContent = 'Cổng mở sau: ' + text;
            }
        };
        tick();
        setInterval(tick, 1000);
    }

    // Google gọi hàm này sau khi khách đăng nhập thành công
    window.onGoogleCredential = function (response) {
        credential = response.credential;
        loadVoterStatus();
    };

    function loadVoterStatus() {
        var body = new FormData();
        if (credential) { body.append('credential', credential); }

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
                if (!tokenAuth && typeof Toast !== 'undefined') {
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
        if (nameEl && name) { nameEl.textContent = name; }
        var avatarEl = document.getElementById('voter-avatar-letter');
        if (avatarEl && name) {
            avatarEl.textContent = name.trim().charAt(0).toUpperCase();
        }
        updateRemaining(remaining);
    }

    function renderHeartTokens(remaining) {
        var container = document.getElementById('quota-hearts-container');
        if (!container) { return; }
        var rem = typeof remaining !== 'undefined' ? parseInt(remaining, 10) : maxVotes;
        var html = '';
        for (var i = 0; i < maxVotes; i++) {
            if (i < rem) {
                html += '<i class="fa fa-heart bc-heart-token"></i>';
            } else {
                html += '<i class="fa fa-heart-o bc-heart-token empty"></i>';
            }
        }
        container.innerHTML = html;
    }

    function markVotedButtons(votedIds) {
        votedIds.forEach(function (cid) {
            setVoted(cid);
        });
    }

    function setVoted(contestantId) {
        var selector = typeof contestantId === 'object' && contestantId.getAttribute
            ? contestantId
            : document.querySelectorAll('.btn-vote[data-contestant="' + contestantId + '"]');

        var buttons = selector.length ? selector : [selector];
        buttons.forEach(function (btn) {
            if (!btn) { return; }
            btn.classList.remove('btn-outline-primary');
            btn.classList.add('btn-success');
            btn.innerHTML = '<i class="fa fa-check me-1"></i>Đã bình chọn';
            btn.disabled = true;
        });
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
                locale: 'vi',
                shape: 'pill'
            });
        }
        return true;
    }

    if (tokenAuth) {
        // Đã đăng nhập bằng tài khoản nội bộ: nạp ngay trạng thái
        loadVoterStatus();
    } else {
        // Thư viện Google tải bất đồng bộ -> thử lại vài lần
        var tries = 0;
        var timer = setInterval(function () {
            if (initGoogle() || ++tries > 20) {
                clearInterval(timer);
            }
        }, 150);
    }

    function vote(button) {
        var contestantId = button.getAttribute('data-contestant');

        if (!tokenAuth && !credential) {
            if (typeof Toast !== 'undefined') {
                Toast.warning('Vui lòng đăng nhập trước khi bình chọn.');
            }
            return;
        }

        var original = button.innerHTML;
        button.disabled = true;
        button.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i>Đang gửi...';

        var body = new FormData();
        body.append('contestant_id', contestantId);
        if (credential) { body.append('credential', credential); }

        fetch(voteUrl, { method: 'POST', body: body })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                updateCount(contestantId, data.vote_count);
                updateRemaining(data.remaining);

                if (data.success) {
                    setVoted(contestantId);
                    if (data.remaining <= 0) { disableAllVoteButtons(); }
                    if (typeof Toast !== 'undefined') { Toast.success(data.message || 'Bình chọn thành công!'); }
                } else {
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
        document.querySelectorAll('.vote-count[data-contestant="' + contestantId + '"]').forEach(function (el) {
            el.textContent = count;
        });

        // Cập nhật thuộc tính data-votes của card để hỗ trợ sort
        document.querySelectorAll('[data-cid="' + contestantId + '"]').forEach(function (card) {
            card.setAttribute('data-votes', count);
        });

        var modalVotes = document.getElementById('modal-votes');
        var modalBtn = document.getElementById('modal-vote-btn');
        if (modalVotes && modalBtn && modalBtn.getAttribute('data-contestant') === String(contestantId)) {
            modalVotes.textContent = count;
        }
    }

    function updateRemaining(remaining) {
        if (typeof remaining === 'undefined' || remaining === null) { return; }
        var el = document.getElementById('remaining-votes');
        if (el) { el.textContent = remaining; }
        var stickyRem = document.getElementById('sticky-remaining');
        if (stickyRem) { stickyRem.textContent = remaining; }
        renderHeartTokens(remaining);
    }

    function disableAllVoteButtons() {
        document.querySelectorAll('.btn-vote').forEach(function (b) {
            if (!b.disabled) {
                b.disabled = true;
            }
        });
    }

    // 2. Tìm kiếm và sắp xếp thí sinh
    function initSearchAndFilter() {
        var searchInput = document.getElementById('contestant-search');
        var clearBtn = document.getElementById('contestant-search-clear');
        var countEl = document.getElementById('contestant-count');
        var emptyEl = document.getElementById('empty-search');
        var sortBtns = document.querySelectorAll('.bc-sort-btn');
        var grid = document.getElementById('finalist-grid');
        if (!grid) { return; }

        var items = Array.prototype.slice.call(grid.children);

        function filterItems() {
            var query = (searchInput ? searchInput.value : '').toLowerCase().trim();
            if (clearBtn) {
                clearBtn.style.display = query.length > 0 ? 'block' : 'none';
            }

            var visibleCount = 0;
            items.forEach(function (col) {
                var name = (col.getAttribute('data-name') || '').toLowerCase();
                var sbd = (col.getAttribute('data-sbd') || '').toLowerCase();
                var unit = (col.getAttribute('data-unit') || '').toLowerCase();

                var match = !query || name.indexOf(query) !== -1 || sbd.indexOf(query) !== -1 || unit.indexOf(query) !== -1;
                if (match) {
                    col.style.display = '';
                    visibleCount++;
                } else {
                    col.style.display = 'none';
                }
            });

            if (countEl) { countEl.textContent = visibleCount; }
            if (emptyEl) { emptyEl.style.display = visibleCount === 0 ? 'block' : 'none'; }
        }

        if (searchInput) {
            searchInput.addEventListener('input', filterItems);
        }
        if (clearBtn) {
            clearBtn.addEventListener('click', function () {
                searchInput.value = '';
                filterItems();
                searchInput.focus();
            });
        }

        sortBtns.forEach(function (btn) {
            btn.addEventListener('click', function () {
                sortBtns.forEach(function (b) { b.classList.remove('active'); });
                btn.classList.add('active');
                var sortType = btn.getAttribute('data-sort');

                items.sort(function (a, b) {
                    if (sortType === 'votes') {
                        var vA = parseInt(a.getAttribute('data-votes') || '0', 10);
                        var vB = parseInt(b.getAttribute('data-votes') || '0', 10);
                        return vB - vA;
                    } else if (sortType === 'name') {
                        var nA = (a.getAttribute('data-name') || '').toLowerCase();
                        var nB = (b.getAttribute('data-name') || '').toLowerCase();
                        return nA.localeCompare(nB, 'vi');
                    } else { // SBD
                        var sA = (a.getAttribute('data-sbd') || '');
                        var sB = (b.getAttribute('data-sbd') || '');
                        return sA.localeCompare(sB, undefined, { numeric: true });
                    }
                });

                items.forEach(function (col) {
                    grid.appendChild(col);
                });
            });
        });
    }

    // 3. Xem ảnh lớn và chi tiết thí sinh qua Modal
    function initPhotoModal() {
        var modalEl = document.getElementById('bcPhotoModal');
        if (!modalEl || typeof bootstrap === 'undefined') { return; }
        var bsModal = new bootstrap.Modal(modalEl);

        document.addEventListener('click', function (e) {
            var wrapper = e.target.closest ? e.target.closest('.bc-card-photo-wrapper') : null;
            if (wrapper) {
                var card = wrapper.closest('.contestant-card');
                if (!card) { return; }

                var photo = wrapper.querySelector('.bc-card-photo');
                var name = card.getAttribute('data-name');
                var unit = card.getAttribute('data-unit');
                var votes = card.getAttribute('data-votes');
                var cid = card.getAttribute('data-cid');

                var modalImg = document.getElementById('modal-photo');
                var modalName = document.getElementById('modal-name');
                var modalUnit = document.getElementById('modal-unit');
                var modalVotes = document.getElementById('modal-votes');
                var modalVoteBtn = document.getElementById('modal-vote-btn');

                if (modalImg && photo) { modalImg.src = photo.src; }
                if (modalName) { modalName.textContent = name || ''; }
                if (modalSbd) {
                    modalSbd.innerHTML = sbd ? '<i class="fa fa-star me-1"></i>' + sbd : '';
                    modalSbd.style.display = sbd ? 'inline-flex' : 'none';
                }
                if (modalUnit) { modalUnit.textContent = unit || ''; }
                if (modalVotes) { modalVotes.textContent = votes || '0'; }

                if (modalVoteBtn) {
                    modalVoteBtn.setAttribute('data-contestant', cid);
                    var cardBtn = card.querySelector('.btn-vote');
                    if (cardBtn) {
                        modalVoteBtn.disabled = cardBtn.disabled;
                        modalVoteBtn.className = cardBtn.className;
                        modalVoteBtn.innerHTML = cardBtn.innerHTML;
                    }
                }

                bsModal.show();
            }
        });
    }

    // 4. Thanh trạng thái rút gọn ghim trên màn hình điện thoại
    function initStickyBar() {
        var stickyBar = document.getElementById('bc-sticky-bar');
        var targetBox = document.getElementById('voter-box');
        if (!stickyBar || !targetBox) { return; }

        window.addEventListener('scroll', function () {
            var rect = targetBox.getBoundingClientRect();
            if (rect.bottom < 0) {
                stickyBar.classList.add('is-visible');
            } else {
                stickyBar.classList.remove('is-visible');
            }
        }, { passive: true });

        var scrollBtn = document.getElementById('bc-scroll-top');
        if (scrollBtn) {
            scrollBtn.addEventListener('click', function () {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        }
    }

    // 5. Sự kiện click nút Bình chọn
    document.addEventListener('click', function (e) {
        var btn = e.target.closest ? e.target.closest('.btn-vote') : null;
        if (btn && !btn.disabled) {
            vote(btn);
        }
    });
})();
