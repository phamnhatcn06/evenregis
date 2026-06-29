document.addEventListener('DOMContentLoaded', function() {
    var availableList = document.getElementById('available_list');
    var selectedList = document.getElementById('selected_list');
    var btnMoveRight = document.getElementById('btn_move_right');
    var btnMoveAllRight = document.getElementById('btn_move_all_right');
    var btnMoveLeft = document.getElementById('btn_move_left');
    var btnMoveAllLeft = document.getElementById('btn_move_all_left');
    var btnSave = document.getElementById('btn_save');
    var searchAvailable = document.getElementById('search_available');
    var searchSelected = document.getElementById('search_selected');
    var assignUrl = document.getElementById('assign_url').value;

    function updateCounts() {
        var availableCount = availableList.querySelectorAll('.contestant-item').length;
        var selectedCount = selectedList.querySelectorAll('.contestant-item').length;
        document.getElementById('available_count').textContent = availableCount;
        document.getElementById('selected_count').textContent = selectedCount;

        var emptyAvailable = document.getElementById('empty_available');
        var emptySelected = document.getElementById('empty_selected');

        if (emptyAvailable) {
            emptyAvailable.style.display = availableCount === 0 ? '' : 'none';
        }
        if (emptySelected) {
            emptySelected.style.display = selectedCount === 0 ? '' : 'none';
        }
    }

    function createItemHtml(data) {
        var photoHtml = data.photo
            ? '<img src="' + data.photo + '" class="rounded me-2" style="width:40px;height:40px;object-fit:cover;">'
            : '<span class="me-2 text-muted" style="width:40px;text-align:center;"><i class="fa fa-user"></i></span>';

        return '<label class="list-group-item list-group-item-action d-flex align-items-center contestant-item" ' +
            'data-id="' + data.id + '" ' +
            'data-number="' + data.number + '" ' +
            'data-name="' + data.name + '" ' +
            'data-property="' + data.property + '" ' +
            'data-photo="' + data.photo + '" ' +
            'data-search="' + (data.name + ' ' + data.number + ' ' + data.property).toLowerCase() + '">' +
            '<input type="checkbox" class="form-check-input me-2 item-checkbox">' +
            photoHtml +
            '<div class="flex-grow-1">' +
            '<div class="fw-bold">' + data.number + ' - ' + data.name + '</div>' +
            '<small class="text-muted">' + data.property + '</small>' +
            '</div></label>';
    }

    function moveItems(fromList, toList, onlyChecked) {
        var items = fromList.querySelectorAll('.contestant-item');
        items.forEach(function(item) {
            var checkbox = item.querySelector('input[type="checkbox"]');
            if (!onlyChecked || (checkbox && checkbox.checked)) {
                var data = {
                    id: item.getAttribute('data-id'),
                    number: item.getAttribute('data-number'),
                    name: item.getAttribute('data-name'),
                    property: item.getAttribute('data-property'),
                    photo: item.getAttribute('data-photo')
                };
                item.remove();
                toList.insertAdjacentHTML('beforeend', createItemHtml(data));
            }
        });
        updateCounts();
    }

    function filterList(input, listId) {
        var filter = input.value.toLowerCase();
        var items = document.querySelectorAll('#' + listId + ' .contestant-item');
        items.forEach(function(item) {
            var search = item.getAttribute('data-search') || '';
            item.style.display = search.indexOf(filter) > -1 ? '' : 'none';
        });
    }

    if (btnMoveRight) {
        btnMoveRight.addEventListener('click', function() {
            moveItems(availableList, selectedList, true);
        });
    }

    if (btnMoveAllRight) {
        btnMoveAllRight.addEventListener('click', function() {
            moveItems(availableList, selectedList, false);
        });
    }

    if (btnMoveLeft) {
        btnMoveLeft.addEventListener('click', function() {
            moveItems(selectedList, availableList, true);
        });
    }

    if (btnMoveAllLeft) {
        btnMoveAllLeft.addEventListener('click', function() {
            moveItems(selectedList, availableList, false);
        });
    }

    if (searchAvailable) {
        searchAvailable.addEventListener('input', function() {
            filterList(this, 'available_list');
        });
    }

    if (searchSelected) {
        searchSelected.addEventListener('input', function() {
            filterList(this, 'selected_list');
        });
    }

    if (btnSave) {
        btnSave.addEventListener('click', function() {
            var items = selectedList.querySelectorAll('.contestant-item');
            var ids = [];
            items.forEach(function(item) {
                ids.push(item.getAttribute('data-id'));
            });

            if (ids.length === 0) {
                Toast.warning('Vui lòng chọn ít nhất 1 thí sinh.');
                return;
            }

            var originalHtml = this.innerHTML;
            this.disabled = true;
            this.innerHTML = '<i class="fa fa-spinner fa-spin me-2"></i>Đang lưu...';

            var formData = new FormData();
            ids.forEach(function(id) {
                formData.append('registration_ids[]', id);
            });

            console.log('Sending IDs:', ids);
            console.log('Assign URL:', assignUrl);

            fetch(assignUrl, {
                method: 'POST',
                body: formData
            })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                console.log('API Response:', data);
                btnSave.disabled = false;
                btnSave.innerHTML = originalHtml;

                if (data.success) {
                    Toast.success(data.message);
                    console.log('SUCCESS - data.debug.data:', data.debug.data);
                    location.reload();
                } else {
                    Toast.error(data.message);
                    console.log('ERROR - Check data.debug:', data.debug);
                }
            })
            .catch(function() {
                btnSave.disabled = false;
                btnSave.innerHTML = originalHtml;
                Toast.error('Lỗi kết nối server');
            });
        });
    }

    updateCounts();
});
