var RegistrationView = (function() {
    var eventId = null;
    var contentsData = [];
    var registeredSports = [];
    var registeredCompetitions = [];
    var allStaff = [];
    var selectedStaff = [];
    var maxPerOrg = 0;
    var competitionContentId = null;

    function init(config) {
        eventId = config.eventId;
        registeredSports = config.registeredSports || [];
        registeredCompetitions = config.registeredCompetitions || [];

        if (eventId) {
            loadContentsData();
        }

        bindCompetitionEvents();
    }

    function loadContentsData() {
        fetch(window.BASE_URL + '/admin/registrations/getEventContents?event_id=' + eventId)
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success && data.data) {
                    contentsData = data.data;
                    contentsData.forEach(function(c) {
                        if (c.code === 'competition') {
                            competitionContentId = c.id;
                        }
                    });
                }
            });
    }

    function bindCompetitionEvents() {
        var compSelect = document.getElementById('comp_competition_id');
        var propSelect = document.getElementById('comp_property_id');

        if (compSelect) {
            compSelect.addEventListener('change', function() {
                var competitionId = this.value;
                if (competitionId) {
                    loadCompetitionInfo(competitionId);
                    loadOrganizations();
                } else {
                    propSelect.innerHTML = '<option value="">-- Chọn cuộc thi trước --</option>';
                    document.getElementById('comp_max_per_org').value = '-';
                    hideDualListbox();
                }
            });
        }

        if (propSelect) {
            propSelect.addEventListener('change', function() {
                var propertyId = this.value;
                if (propertyId) {
                    loadStaffByProperty(propertyId);
                } else {
                    hideDualListbox();
                }
            });
        }

        var searchInput = document.getElementById('staff_search');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                filterStaffList(this.value);
            });
        }

        document.getElementById('btn_add_staff')?.addEventListener('click', addSelectedStaff);
        document.getElementById('btn_add_all_staff')?.addEventListener('click', addAllStaff);
        document.getElementById('btn_remove_staff')?.addEventListener('click', removeSelectedStaff);
        document.getElementById('btn_remove_all_staff')?.addEventListener('click', removeAllStaff);

        var form = document.getElementById('add-competition-form');
        if (form) {
            form.addEventListener('submit', function(e) {
                if (selectedStaff.length === 0) {
                    e.preventDefault();
                    alert('Vui lòng chọn ít nhất một nhân viên.');
                    return false;
                }
            });
        }
    }

    function loadCompetitionInfo(competitionId) {
        fetch(window.BASE_URL + '/admin/registrations/getCompetitionInfo?competition_id=' + competitionId)
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success && data.data) {
                    maxPerOrg = data.data.max_per_org || 0;
                    document.getElementById('comp_max_per_org').value = maxPerOrg > 0 ? maxPerOrg : 'Không giới hạn';
                    document.getElementById('max_count').textContent = maxPerOrg > 0 ? maxPerOrg : '∞';
                }
            });
    }

    function loadOrganizations() {
        var propSelect = document.getElementById('comp_property_id');
        propSelect.innerHTML = '<option value="">-- Đang tải... --</option>';

        fetch(window.BASE_URL + '/admin/registrations/getOrganizations')
            .then(function(response) { return response.json(); })
            .then(function(data) {
                propSelect.innerHTML = '<option value="">-- Chọn đơn vị --</option>';
                if (data.success && data.data && data.data.length > 0) {
                    data.data.forEach(function(org) {
                        var opt = document.createElement('option');
                        opt.value = org.id;
                        opt.textContent = org.code + ' - ' + org.name;
                        propSelect.appendChild(opt);
                    });
                }
            });
    }

    function loadStaffByProperty(propertyId) {
        document.getElementById('staff_placeholder').style.display = 'none';
        document.getElementById('dual_listbox_wrapper').style.display = 'flex';

        var availableList = document.getElementById('available_staff_list');
        availableList.innerHTML = '<div class="text-center p-3"><i class="fa fa-spinner fa-spin"></i> Đang tải...</div>';

        fetch(window.BASE_URL + '/admin/registrations/getStaffByProperty?property_id=' + propertyId)
            .then(function(response) { return response.json(); })
            .then(function(data) {
                allStaff = data.success && data.data ? data.data : [];
                selectedStaff = [];
                renderAvailableStaff();
                renderSelectedStaff();
            });
    }

    function hideDualListbox() {
        document.getElementById('staff_placeholder').style.display = 'block';
        document.getElementById('dual_listbox_wrapper').style.display = 'none';
        allStaff = [];
        selectedStaff = [];
    }

    function renderAvailableStaff() {
        var list = document.getElementById('available_staff_list');
        var searchTerm = (document.getElementById('staff_search')?.value || '').toLowerCase();

        var available = allStaff.filter(function(s) {
            return selectedStaff.findIndex(function(sel) { return sel.id == s.id; }) === -1;
        });

        if (searchTerm) {
            available = available.filter(function(s) {
                return s.display.toLowerCase().indexOf(searchTerm) !== -1;
            });
        }

        if (available.length === 0) {
            list.innerHTML = '<div class="text-center text-muted p-3">Không có nhân viên</div>';
            return;
        }

        list.innerHTML = '';
        available.forEach(function(staff) {
            var item = document.createElement('a');
            item.href = '#';
            item.className = 'list-group-item list-group-item-action py-2';
            item.setAttribute('data-id', staff.id);
            item.innerHTML = '<small>' + escapeHtml(staff.display) + '</small>' +
                (staff.position ? '<br><span class="text-muted" style="font-size:11px;">' + escapeHtml(staff.position) + '</span>' : '');
            item.addEventListener('click', function(e) {
                e.preventDefault();
                this.classList.toggle('active');
            });
            list.appendChild(item);
        });
    }

    function renderSelectedStaff() {
        var list = document.getElementById('selected_staff_list');
        document.getElementById('selected_count').textContent = selectedStaff.length;

        removeHiddenInputs();

        if (selectedStaff.length === 0) {
            list.innerHTML = '<div class="text-center text-muted p-3">Chưa chọn nhân viên nào</div>';
            return;
        }

        list.innerHTML = '';
        selectedStaff.forEach(function(staff) {
            var item = document.createElement('a');
            item.href = '#';
            item.className = 'list-group-item list-group-item-action py-2';
            item.setAttribute('data-id', staff.id);
            item.innerHTML = '<small>' + escapeHtml(staff.display) + '</small>';
            item.addEventListener('click', function(e) {
                e.preventDefault();
                this.classList.toggle('active');
            });
            list.appendChild(item);

            var hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'staff_ids[]';
            hidden.value = staff.id;
            document.getElementById('add-competition-form').appendChild(hidden);
        });
    }

    function removeHiddenInputs() {
        var form = document.getElementById('add-competition-form');
        var inputs = form.querySelectorAll('input[name="staff_ids[]"]');
        inputs.forEach(function(input) { input.remove(); });
    }

    function addSelectedStaff() {
        var list = document.getElementById('available_staff_list');
        var actives = list.querySelectorAll('.active');

        actives.forEach(function(el) {
            var id = el.getAttribute('data-id');
            var staff = allStaff.find(function(s) { return s.id == id; });
            if (staff && canAddMore()) {
                selectedStaff.push(staff);
            }
            el.classList.remove('active');
        });

        renderAvailableStaff();
        renderSelectedStaff();
    }

    function addAllStaff() {
        var available = allStaff.filter(function(s) {
            return selectedStaff.findIndex(function(sel) { return sel.id == s.id; }) === -1;
        });

        available.forEach(function(staff) {
            if (canAddMore()) {
                selectedStaff.push(staff);
            }
        });

        renderAvailableStaff();
        renderSelectedStaff();
    }

    function removeSelectedStaff() {
        var list = document.getElementById('selected_staff_list');
        var actives = list.querySelectorAll('.active');

        actives.forEach(function(el) {
            var id = el.getAttribute('data-id');
            selectedStaff = selectedStaff.filter(function(s) { return s.id != id; });
        });

        renderAvailableStaff();
        renderSelectedStaff();
    }

    function removeAllStaff() {
        selectedStaff = [];
        renderAvailableStaff();
        renderSelectedStaff();
    }

    function canAddMore() {
        return maxPerOrg === 0 || selectedStaff.length < maxPerOrg;
    }

    function filterStaffList(term) {
        renderAvailableStaff();
    }

    function escapeHtml(text) {
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
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
        var itemSelect = document.getElementById('item_id');

        document.getElementById('add-detail-form').reset();
        document.getElementById('quantity').value = '1';

        itemSelect.innerHTML = '<option value="">-- Đang tải... --</option>';

        var sportsContent = contentsData.find(function(c) { return c.code === 'sports'; });
        if (sportsContent) {
            document.getElementById('content_id').value = sportsContent.id;
        }

        fetch(window.BASE_URL + '/admin/registrations/getContentItems?event_id=' + eventId + '&content_type=sports')
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success && data.data && data.data.length > 0) {
                    itemSelect.innerHTML = renderSportsTree(data.data, registeredSports);
                } else {
                    itemSelect.innerHTML = '<option value="">-- Không có môn nào --</option>';
                }
            });
    }

    function resetCompetitionModal() {
        var compSelect = document.getElementById('comp_competition_id');
        var propSelect = document.getElementById('comp_property_id');

        document.getElementById('add-competition-form').reset();
        compSelect.innerHTML = '<option value="">-- Đang tải... --</option>';
        propSelect.innerHTML = '<option value="">-- Chọn cuộc thi trước --</option>';
        document.getElementById('comp_max_per_org').value = '-';

        if (competitionContentId) {
            document.getElementById('comp_content_id').value = competitionContentId;
        }

        allStaff = [];
        selectedStaff = [];
        maxPerOrg = 0;
        hideDualListbox();
        removeHiddenInputs();

        fetch(window.BASE_URL + '/admin/registrations/getContentItems?event_id=' + eventId + '&content_type=competition')
            .then(function(response) { return response.json(); })
            .then(function(data) {
                compSelect.innerHTML = '<option value="">-- Chọn cuộc thi --</option>';
                if (data.success && data.data && data.data.length > 0) {
                    data.data.forEach(function(item) {
                        var opt = document.createElement('option');
                        opt.value = item.id;
                        opt.textContent = item.name;
                        compSelect.appendChild(opt);
                    });
                }
            });
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
        resetCompetitionModal: resetCompetitionModal,
        viewDocument: viewDocument,
        confirmDeleteDetail: confirmDeleteDetail
    };
})();
