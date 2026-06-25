document.addEventListener('DOMContentLoaded', function() {
    var searchAvailable = document.getElementById('search_available');
    var searchAssigned = document.getElementById('search_assigned');
    var btnAssign = document.getElementById('btn_assign');
    var btnAssignAll = document.getElementById('btn_assign_all');
    var btnRemove = document.getElementById('btn_remove');
    var btnRemoveAll = document.getElementById('btn_remove_all');
    var assignUrl = document.getElementById('assign_url').value;

    function filterList(input, listId) {
        var filter = input.value.toLowerCase();
        var items = document.querySelectorAll('#' + listId + ' label');
        items.forEach(function(item) {
            var search = item.getAttribute('data-search') || '';
            item.style.display = search.indexOf(filter) > -1 ? '' : 'none';
        });
    }

    if (searchAvailable) {
        searchAvailable.addEventListener('input', function() {
            filterList(this, 'available_list');
        });
    }

    if (searchAssigned) {
        searchAssigned.addEventListener('input', function() {
            filterList(this, 'assigned_list');
        });
    }

    function getCheckedValues(selector) {
        var checked = document.querySelectorAll(selector + ':checked');
        var values = [];
        checked.forEach(function(cb) {
            values.push(cb.value);
        });
        return values;
    }

    function submitAssign(ids, callback) {
        if (ids.length === 0) {
            Toast.warning('Vui lòng chọn thí sinh.');
            return;
        }

        var formData = new FormData();
        ids.forEach(function(id) {
            formData.append('contestant_ids[]', id);
        });

        fetch(assignUrl, {
            method: 'POST',
            body: formData
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.success) {
                Toast.success(data.message);
                location.reload();
            } else {
                Toast.error(data.message);
            }
        })
        .catch(function() {
            Toast.error('Lỗi kết nối server');
        });
    }

    if (btnAssign) {
        btnAssign.addEventListener('click', function() {
            var ids = getCheckedValues('.contestant-checkbox');
            submitAssign(ids);
        });
    }

    if (btnAssignAll) {
        btnAssignAll.addEventListener('click', function() {
            var allCheckboxes = document.querySelectorAll('.contestant-checkbox');
            var ids = [];
            allCheckboxes.forEach(function(cb) {
                ids.push(cb.value);
            });
            submitAssign(ids);
        });
    }

    if (btnRemove) {
        btnRemove.addEventListener('click', function() {
            Toast.info('Chức năng bỏ thí sinh đang phát triển.');
        });
    }

    if (btnRemoveAll) {
        btnRemoveAll.addEventListener('click', function() {
            Toast.info('Chức năng bỏ tất cả đang phát triển.');
        });
    }
});
