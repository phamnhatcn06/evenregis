document.addEventListener('DOMContentLoaded', function () {
    var root = document.getElementById('swim-times-root');
    if (!root) {
        return;
    }
    var saveUrl = root.getAttribute('data-save-url');
    var saveBtn = document.getElementById('btn-save-swim-times');
    if (!saveBtn) {
        return;
    }

    // Đổi chuỗi "ss.cc" hoặc "m:ss.cc" sang mili-giây; '' -> null
    function parseTimeToMs(text) {
        var s = (text || '').trim();
        if (s === '') {
            return null;
        }
        var min = 0;
        var rest = s;
        if (s.indexOf(':') !== -1) {
            var parts = s.split(':');
            min = parseInt(parts[0], 10) || 0;
            rest = parts[1];
        }
        var sec = parseFloat(rest);
        if (isNaN(sec)) {
            return NaN;
        }
        return Math.round((min * 60 + sec) * 1000);
    }

    saveBtn.addEventListener('click', function () {
        var inputs = document.querySelectorAll('.swim-time-input');
        var times = {};
        var invalid = false;
        inputs.forEach(function (inp) {
            var laneId = inp.getAttribute('data-lane-id');
            var ms = parseTimeToMs(inp.value);
            if (ms !== null && isNaN(ms)) {
                invalid = true;
                inp.classList.add('is-invalid');
            } else {
                inp.classList.remove('is-invalid');
                if (ms !== null) {
                    times[laneId] = ms;
                }
            }
        });

        if (invalid) {
            Toast.error('Có ô thành tích không hợp lệ. Nhập dạng ss.cc hoặc m:ss.cc.');
            return;
        }

        var original = saveBtn.innerHTML;
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i>Đang lưu...';

        var fd = new FormData();
        Object.keys(times).forEach(function (laneId) {
            fd.append('times[' + laneId + ']', times[laneId]);
        });

        fetch(saveUrl, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function (res) { return res.json(); })
            .then(function (d) {
                if (d.success) {
                    Toast.success(d.message || 'Đã lưu thành tích');
                    setTimeout(function () { location.reload(); }, 900);
                } else {
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = original;
                    Toast.error(d.message || 'Không thể lưu');
                }
            })
            .catch(function () {
                saveBtn.disabled = false;
                saveBtn.innerHTML = original;
                Toast.error('Lỗi kết nối máy chủ');
            });
    });
});
