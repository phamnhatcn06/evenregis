/**
 * Sport Registration - BR-REG-05: Giới hạn số môn thể thao
 * Section 15: Yêu cầu đăng ký mở rộng
 */
(function() {
    'use strict';

    var config = {
        maxSports: 3,
        attendeeId: null,
        eventId: null,
        apiUrl: '',
        apiKey: ''
    };

    function init() {
        var container = document.getElementById('sport-registration-container');
        if (!container) return;

        config.maxSports = parseInt(container.dataset.maxSports) || 3;
        config.attendeeId = container.dataset.attendeeId;
        config.eventId = container.dataset.eventId;
        config.apiUrl = container.dataset.apiUrl;
        config.apiKey = container.dataset.apiKey;

        loadCurrentSportCount();
        initSportCheckboxes();
    }

    function loadCurrentSportCount() {
        var countEl = document.getElementById('current-sport-count');
        var remainingEl = document.getElementById('remaining-sports');

        if (!config.attendeeId) return;

        fetch(config.apiUrl + '/sport-count?attendee_id=' + config.attendeeId, {
            headers: {
                'Authorization': 'Bearer ' + config.apiKey,
                'Accept': 'application/json'
            }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            var current = data.current || 0;
            var remaining = config.maxSports - current;

            if (countEl) countEl.textContent = current + '/' + config.maxSports;
            if (remainingEl) remainingEl.textContent = remaining;

            updateCheckboxStates(current, remaining);
        })
        .catch(function(err) {
            console.error('Lỗi tải số môn thể thao:', err);
        });
    }

    function initSportCheckboxes() {
        var checkboxes = document.querySelectorAll('.sport-checkbox');

        checkboxes.forEach(function(checkbox) {
            checkbox.addEventListener('change', function() {
                if (this.checked) {
                    validateSportSelection(this);
                } else {
                    loadCurrentSportCount();
                }
            });
        });
    }

    function validateSportSelection(checkbox) {
        var sportId = checkbox.dataset.sportId;
        var rootSportId = checkbox.dataset.rootSportId;

        fetch(config.apiUrl + '/validate-sport', {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + config.apiKey,
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                attendee_id: config.attendeeId,
                sport_id: sportId,
                event_id: config.eventId
            })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (!data.valid) {
                checkbox.checked = false;
                showWarning(data.message || 'Không thể đăng ký môn này');
            } else {
                if (data.isNewRoot) {
                    loadCurrentSportCount();
                }
            }
        })
        .catch(function(err) {
            checkbox.checked = false;
            showWarning('Lỗi kiểm tra giới hạn môn thể thao');
        });
    }

    function updateCheckboxStates(current, remaining) {
        var checkboxes = document.querySelectorAll('.sport-checkbox:not(:checked)');
        var registeredRoots = getRegisteredRootSports();

        checkboxes.forEach(function(checkbox) {
            var rootSportId = checkbox.dataset.rootSportId;
            var isNewRoot = registeredRoots.indexOf(rootSportId) === -1;

            if (isNewRoot && remaining <= 0) {
                checkbox.disabled = true;
                checkbox.closest('.sport-item').classList.add('disabled');
            } else {
                checkbox.disabled = false;
                checkbox.closest('.sport-item').classList.remove('disabled');
            }
        });

        var warningEl = document.getElementById('sport-limit-warning');
        if (warningEl) {
            if (remaining <= 0) {
                warningEl.style.display = 'block';
                warningEl.textContent = 'Đã đạt giới hạn ' + config.maxSports + ' môn thể thao';
            } else if (remaining === 1) {
                warningEl.style.display = 'block';
                warningEl.textContent = 'Còn có thể đăng ký 1 môn nữa';
                warningEl.className = 'alert alert-warning';
            } else {
                warningEl.style.display = 'none';
            }
        }
    }

    function getRegisteredRootSports() {
        var checked = document.querySelectorAll('.sport-checkbox:checked');
        var roots = [];
        checked.forEach(function(cb) {
            var root = cb.dataset.rootSportId;
            if (roots.indexOf(root) === -1) {
                roots.push(root);
            }
        });
        return roots;
    }

    function showWarning(message) {
        if (typeof Toast !== 'undefined') {
            Toast.warning(message);
        } else if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'warning',
                title: 'Cảnh báo',
                text: message
            });
        } else {
            alert(message);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
