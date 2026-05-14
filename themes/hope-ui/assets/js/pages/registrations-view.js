var RegistrationView = (function() {
    var eventId = null;
    var contentsData = [];
    var registeredSports = [];
    var registeredCompetitions = [];

    function init(config) {
        eventId = config.eventId;
        registeredSports = config.registeredSports || [];
        registeredCompetitions = config.registeredCompetitions || [];

        if (eventId) {
            loadContentsData();
        }

        bindEvents();
    }

    function loadContentsData() {
        var contentSelect = document.getElementById('content_select');
        if (!contentSelect) return;

        fetch(window.BASE_URL + '/admin/registrations/getEventContents?event_id=' + eventId)
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success && data.data) {
                    contentsData = data.data;
                }
            });
    }

    function bindEvents() {
        var contentSelect = document.getElementById('content_select');
        if (contentSelect) {
            contentSelect.addEventListener('change', function() {
                var selectedOpt = this.options[this.selectedIndex];
                var contentCode = selectedOpt.getAttribute('data-code') || '';
                var contentId = this.value;

                document.getElementById('content_type').value = contentCode;
                document.getElementById('content_id').value = contentId;

                loadContentItems(contentCode);
            });
        }
    }

    function renderSportsTree(data, excludeIds) {
        var html = '<option value="">-- Chọn môn thể thao --</option>';
        var groups = {};
        var prefixes = ['Bóng bàn', 'Bóng đá', 'Cầu lông', 'Pickerball', 'Bơi ếch', 'Bơi tự do', 'Kéo co', 'Tennis', 'Cờ vua', 'Cờ tướng'];

        data.forEach(function(item) {
            if (excludeIds.indexOf(parseInt(item.id)) !== -1) return;
            var groupName = 'Khác';
            for (var i = 0; i < prefixes.length; i++) {
                if (item.name.indexOf(prefixes[i]) === 0) {
                    groupName = prefixes[i];
                    break;
                }
            }
            if (!groups[groupName]) groups[groupName] = [];
            groups[groupName].push(item);
        });

        var sortedGroups = Object.keys(groups).sort();
        sortedGroups.forEach(function(groupName) {
            var items = groups[groupName];
            if (items.length > 1) {
                html += '<option value="" disabled style="font-weight:bold;background:#e9ecef;">▸ ' + groupName + '</option>';
                items.forEach(function(item) {
                    html += '<option value="' + item.id + '">&nbsp;&nbsp;&nbsp;' + item.name + '</option>';
                });
            } else {
                html += '<option value="' + items[0].id + '">' + items[0].name + '</option>';
            }
        });
        return html;
    }

    function resetAddModal() {
        var contentSelect = document.getElementById('content_select');
        var itemSelect = document.getElementById('item_id');
        var itemWrapper = document.getElementById('item_wrapper');

        document.getElementById('add-detail-form').reset();
        document.getElementById('content_type').value = '';
        document.getElementById('content_id').value = '';
        document.getElementById('quantity').value = '1';

        contentSelect.innerHTML = '<option value="">-- Chọn loại nội dung --</option>';
        contentsData.forEach(function(c) {
            var opt = document.createElement('option');
            opt.value = c.id;
            opt.textContent = c.name;
            opt.setAttribute('data-code', c.code || '');
            contentSelect.appendChild(opt);
        });

        itemWrapper.style.display = 'none';
        itemSelect.innerHTML = '<option value="">-- Chọn bộ môn --</option>';
        itemSelect.removeAttribute('required');
    }

    function loadContentItems(contentCode) {
        var itemLabel = document.getElementById('item_label');
        var quantityLabel = document.getElementById('quantity_label');
        var itemSelect = document.getElementById('item_id');
        var itemWrapper = document.getElementById('item_wrapper');

        itemSelect.innerHTML = '<option value="">-- Đang tải... --</option>';

        if (contentCode === 'sports') {
            itemLabel.innerHTML = 'Môn thể thao <span class="text-danger">*</span>';
            quantityLabel.innerHTML = 'Số đội/người <span class="text-danger">*</span>';
            itemWrapper.style.display = 'block';
            itemSelect.setAttribute('required', 'required');

            fetch(window.BASE_URL + '/admin/registrations/getContentItems?event_id=' + eventId + '&content_type=sports')
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    if (data.success && data.data && data.data.length > 0) {
                        itemSelect.innerHTML = renderSportsTree(data.data, registeredSports);
                    } else {
                        itemSelect.innerHTML = '<option value="">-- Đã đăng ký hết --</option>';
                    }
                });
        } else if (contentCode === 'competition') {
            itemLabel.innerHTML = 'Cuộc thi <span class="text-danger">*</span>';
            quantityLabel.innerHTML = 'Số người <span class="text-danger">*</span>';
            itemWrapper.style.display = 'block';
            itemSelect.setAttribute('required', 'required');

            fetch(window.BASE_URL + '/admin/registrations/getContentItems?event_id=' + eventId + '&content_type=competition')
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    var html = '<option value="">-- Chọn cuộc thi --</option>';
                    if (data.success && data.data && data.data.length > 0) {
                        data.data.forEach(function(item) {
                            if (registeredCompetitions.indexOf(parseInt(item.id)) === -1) {
                                html += '<option value="' + item.id + '">' + item.name + '</option>';
                            }
                        });
                    }
                    itemSelect.innerHTML = html;
                });
        } else if (contentCode === 'miss') {
            quantityLabel.innerHTML = 'Số người dự thi <span class="text-danger">*</span>';
            itemWrapper.style.display = 'none';
            itemSelect.removeAttribute('required');
        } else if (contentCode === 'talent') {
            quantityLabel.innerHTML = 'Số tiết mục <span class="text-danger">*</span>';
            itemWrapper.style.display = 'none';
            itemSelect.removeAttribute('required');
        } else {
            itemWrapper.style.display = 'none';
            itemSelect.removeAttribute('required');
        }
    }

    function viewDocument(url, type) {
        var modalBody = document.getElementById('documentModalBody');
        var downloadLink = document.getElementById('documentDownloadLink');

        downloadLink.href = url;

        if (type === 'image') {
            modalBody.innerHTML = '<div class="text-center p-3"><img src="' + url + '" class="img-fluid" style="max-height:80vh;"></div>';
        } else if (type === 'pdf') {
            modalBody.innerHTML = '<iframe src="' + url + '" style="width:100%;height:80vh;border:none;"></iframe>';
        }

        var modal = new bootstrap.Modal(document.getElementById('documentModal'));
        modal.show();
    }

    function confirmDeleteDetail(detailId) {
        Swal.fire({
            title: 'Xác nhận xóa',
            text: 'Bạn có chắc chắn muốn xóa nội dung này?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Xóa',
            cancelButtonText: 'Hủy'
        }).then(function(result) {
            if (result.isConfirmed) {
                document.getElementById('delete-detail-form-' + detailId).submit();
            }
        });
    }

    return {
        init: init,
        resetAddModal: resetAddModal,
        viewDocument: viewDocument,
        confirmDeleteDetail: confirmDeleteDetail
    };
})();
