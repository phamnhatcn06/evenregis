document.addEventListener('DOMContentLoaded', function() {
    var qualifyUrl = document.getElementById('qualify_url').value;
    var checkAll = document.getElementById('check_all');
    var btnSelectTop = document.getElementById('btn_select_top');
    var topCount = document.getElementById('top_count');
    var btnQualify = document.getElementById('btn_qualify');
    var selectedCount = document.getElementById('selected_count');

    function updateCount() {
        var checked = document.querySelectorAll('.contestant-check:checked').length;
        if (selectedCount) {
            selectedCount.textContent = checked;
        }
    }

    document.querySelectorAll('.contestant-check').forEach(function(cb) {
        cb.addEventListener('change', updateCount);
    });

    if (checkAll) {
        checkAll.addEventListener('change', function() {
            var checked = this.checked;
            document.querySelectorAll('.contestant-check').forEach(function(cb) {
                cb.checked = checked;
            });
            updateCount();
        });
    }

    if (btnSelectTop) {
        btnSelectTop.addEventListener('click', function() {
            var top = parseInt(topCount.value) || 10;
            var checkboxes = document.querySelectorAll('.contestant-check');
            checkboxes.forEach(function(cb, index) {
                cb.checked = index < top;
            });
            updateCount();
        });
    }

    if (btnQualify) {
        btnQualify.addEventListener('click', function() {
            var checkedBoxes = document.querySelectorAll('.contestant-check:checked');
            if (checkedBoxes.length === 0) {
                Toast.warning('Vui lòng chọn ít nhất 1 thí sinh.');
                return;
            }

            var ids = [];
            checkedBoxes.forEach(function(cb) {
                ids.push(cb.value);
            });

            var nextRoundId = document.getElementById('next_round_id').value;
            var originalHtml = this.innerHTML;

            this.disabled = true;
            this.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i>Đang xử lý...';

            var formData = new FormData();
            ids.forEach(function(id) {
                formData.append('contestant_ids[]', id);
            });
            if (nextRoundId) {
                formData.append('next_round_id', nextRoundId);
            }

            fetch(qualifyUrl, {
                method: 'POST',
                body: formData
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                btnQualify.disabled = false;
                btnQualify.innerHTML = originalHtml;

                if (data.success) {
                    Toast.success(data.message);
                    location.reload();
                } else {
                    Toast.error(data.message);
                }
            })
            .catch(function() {
                btnQualify.disabled = false;
                btnQualify.innerHTML = originalHtml;
                Toast.error('Lỗi kết nối server');
            });
        });
    }

    updateCount();
});
