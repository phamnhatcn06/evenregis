document.addEventListener('DOMContentLoaded', function() {
    var searchInput = document.getElementById('search_contestant');
    var checkAll = document.getElementById('check_all_contestants');
    var btnAssign = document.getElementById('btn_assign_contestant');
    var assignUrl = document.getElementById('assign_contestant_url');

    if (!assignUrl) return;

    function updateCount() {
        var count = document.querySelectorAll('.contestant-check:checked').length;
        document.getElementById('selected_contestant_count').textContent = count;
    }

    if (searchInput) {
        searchInput.addEventListener('input', function() {
            var filter = this.value.toLowerCase();
            var rows = document.querySelectorAll('.contestant-row');
            rows.forEach(function(row) {
                var search = row.getAttribute('data-search') || '';
                row.style.display = search.indexOf(filter) > -1 ? '' : 'none';
            });
        });
    }

    if (checkAll) {
        checkAll.addEventListener('change', function() {
            var checks = document.querySelectorAll('.contestant-check');
            var isChecked = this.checked;
            checks.forEach(function(cb) {
                var row = cb.closest('tr');
                if (row && row.style.display !== 'none') {
                    cb.checked = isChecked;
                }
            });
            updateCount();
        });
    }

    document.querySelectorAll('.contestant-check').forEach(function(cb) {
        cb.addEventListener('change', updateCount);
    });

    if (btnAssign) {
        btnAssign.addEventListener('click', function() {
            var checks = document.querySelectorAll('.contestant-check:checked');
            var ids = [];
            checks.forEach(function(cb) {
                ids.push(cb.value);
            });

            if (ids.length === 0) {
                Toast.warning('Vui lòng chọn ít nhất 1 thí sinh.');
                return;
            }

            var btn = this;
            var originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i>Đang xử lý...';

            var formData = new FormData();
            ids.forEach(function(id) {
                formData.append('registration_ids[]', id);
            });

            fetch(assignUrl.value, {
                method: 'POST',
                body: formData
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
                console.log('Assign response:', data);

                if (data.success) {
                    var modal = bootstrap.Modal.getInstance(document.getElementById('modal_assign_contestant'));
                    if (modal) modal.hide();
                    Toast.success(data.message || 'Thêm thí sinh thành công');
                    location.reload();
                } else {
                    Toast.error(data.message || 'Có lỗi xảy ra');
                    console.error('Assign error:', data.debug);
                }
            })
            .catch(function() {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
                Toast.error('Lỗi kết nối server');
            });
        });
    }

    var modal = document.getElementById('modal_assign_contestant');
    if (modal) {
        modal.addEventListener('show.bs.modal', function() {
            if (searchInput) searchInput.value = '';
            if (checkAll) checkAll.checked = false;
            document.querySelectorAll('.contestant-check').forEach(function(cb) {
                cb.checked = false;
            });
            document.querySelectorAll('.contestant-row').forEach(function(row) {
                row.style.display = '';
            });
            updateCount();
        });
    }
});
