var RegistrationView = (function() {
    var eventId = null;
    var registrationId = null;
    var propertyId = null;
    var propertyCode = null;
    var isHotel = false;
    var contentsData = [];
    var registeredSports = [];
    var registeredCompetitions = [];
    var allStaff = [];
    var selectedStaff = [];
    var maxPerOrg = 0;
    var competitionContentId = null;
    var sportsContentId = null;
    var attendeeAllStaff = [];
    var attendeeSelectedStaff = [];
    var existingStaffIds = [];

    // Sport registration variables
    var sportAllAttendees = [];
    var sportSelectedAttendees = [];

    function init(config) {
        eventId = config.eventId;
        registrationId = config.registrationId;
        propertyId = config.propertyId;
        propertyCode = config.propertyCode;
        isHotel = config.isHotel || false;
        registeredSports = config.registeredSports || [];
        registeredCompetitions = config.registeredCompetitions || [];
        existingStaffIds = config.existingStaffIds || [];

        if (eventId) {
            loadContentsData();
        }

        bindCompetitionEvents();
        bindSportEvents();
        bindAttendeeEvents();
        bindEditAttendeeForm();
        bindAddAttendeeModalReset();

        if (isHotel && propertyId) {
            loadAttendeeStaffList();
        }
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
                        if (c.code === 'sports') {
                            sportsContentId = c.id;
                            var contentIdField = document.getElementById('sport_content_id');
                            if (contentIdField) {
                                contentIdField.value = c.id;
                            }
                        }
                    });
                    console.log('Contents loaded:', contentsData);
                    console.log('Competition content ID:', competitionContentId);
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
            var subInfo = [];
            if (staff.department_name) subInfo.push(staff.department_name);
            if (staff.position) subInfo.push(staff.position);
            item.innerHTML = '<small>' + escapeHtml(staff.display) + '</small>' +
                (subInfo.length ? '<br><span class="text-muted" style="font-size:11px;">' + escapeHtml(subInfo.join(' - ')) + '</span>' : '');
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
            hidden.name = 'staff_codes[]';
            hidden.value = staff.code;
            document.getElementById('add-competition-form').appendChild(hidden);
        });
    }

    function removeHiddenInputs() {
        var form = document.getElementById('add-competition-form');
        var inputs = form.querySelectorAll('input[name="staff_codes[]"]');
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

    // ==================== SPORT REGISTRATION ====================

    function bindSportEvents() {
        var searchInput = document.getElementById('sport_attendee_search');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                filterSportAttendeeList(this.value);
            });
        }

        document.getElementById('btn_add_sport_attendee')?.addEventListener('click', addSelectedSportAttendee);
        document.getElementById('btn_add_all_sport_attendee')?.addEventListener('click', addAllSportAttendees);
        document.getElementById('btn_remove_sport_attendee')?.addEventListener('click', removeSelectedSportAttendee);
        document.getElementById('btn_remove_all_sport_attendee')?.addEventListener('click', removeAllSportAttendees);

        var form = document.getElementById('add-sport-form');
        if (form) {
            form.addEventListener('submit', function(e) {
                if (sportSelectedAttendees.length === 0) {
                    e.preventDefault();
                    Toast.error('Vui lòng chọn ít nhất một người tham dự.');
                    return false;
                }
            });
        }
    }

    function loadAllianceProperties() {
        var container = document.getElementById('alliance_checkboxes');
        if (!container) return;

        container.innerHTML = '<div class="text-muted small">Đang tải...</div>';

        fetch(window.BASE_URL + '/admin/registrations/getAllianceProperties?registration_id=' + registrationId)
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success && data.data && data.data.length > 0) {
                    var html = '';
                    data.data.forEach(function(item) {
                        html += '<div class="form-check">' +
                            '<input class="form-check-input" type="checkbox" name="alliance_property_ids[]" value="' + item.id + '" id="alliance_' + item.id + '">' +
                            '<label class="form-check-label small" for="alliance_' + item.id + '">' + escapeHtml(item.code + ' - ' + item.name) + '</label>' +
                            '</div>';
                    });
                    container.innerHTML = html;
                } else {
                    container.innerHTML = '<div class="text-muted small">Không có đơn vị liên quân</div>';
                }
            });
    }

    function loadSportsList() {
        var sportSelect = document.getElementById('sport_item_id');
        if (!sportSelect) return;

        sportSelect.innerHTML = '<option value="">-- Đang tải... --</option>';

        fetch(window.BASE_URL + '/admin/registrations/getContentItems?event_id=' + eventId + '&content_type=sports')
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success && data.data && data.data.length > 0) {
                    sportSelect.innerHTML = renderSportsTree(data.data, registeredSports);
                } else {
                    sportSelect.innerHTML = '<option value="">-- Không có môn nào --</option>';
                }
            });
    }

    function loadSportAttendees() {
        var availableList = document.getElementById('sport_available_attendee_list');
        if (!availableList) return;

        availableList.innerHTML = '<div class="text-center p-3"><i class="fa fa-spinner fa-spin"></i> Đang tải...</div>';

        // Load attendees with sports role from current registration only
        fetch(window.BASE_URL + '/admin/registrations/getSportAttendees?registration_id=' + registrationId)
            .then(function(response) { return response.json(); })
            .then(function(data) {
                sportAllAttendees = data.success && data.data ? data.data : [];
                sportSelectedAttendees = [];
                renderSportAvailableAttendees();
                renderSportSelectedAttendees();
            });
    }

    function renderSportAvailableAttendees() {
        var list = document.getElementById('sport_available_attendee_list');
        if (!list) return;

        var searchTerm = (document.getElementById('sport_attendee_search')?.value || '').toLowerCase();

        var available = sportAllAttendees.filter(function(a) {
            return sportSelectedAttendees.findIndex(function(sel) { return sel.id == a.id; }) === -1;
        });

        if (searchTerm) {
            available = available.filter(function(a) {
                var name = a.full_name || '';
                return name.toLowerCase().indexOf(searchTerm) !== -1;
            });
        }

        if (available.length === 0) {
            list.innerHTML = '<div class="text-center text-muted p-3">Không có người tham dự</div>';
            return;
        }

        list.innerHTML = '';
        available.forEach(function(att) {
            var item = document.createElement('a');
            item.href = '#';
            item.className = 'list-group-item list-group-item-action py-2';
            item.setAttribute('data-id', att.id);
            var subInfo = [];
            if (att.property_name) subInfo.push(att.property_name);
            if (att.position) subInfo.push(att.position);
            item.innerHTML = '<small>' + escapeHtml(att.full_name) + '</small>' +
                (subInfo.length ? '<br><span class="text-muted" style="font-size:11px;">' + escapeHtml(subInfo.join(' - ')) + '</span>' : '');
            item.addEventListener('click', function(e) {
                e.preventDefault();
                this.classList.toggle('active');
            });
            list.appendChild(item);
        });
    }

    function renderSportSelectedAttendees() {
        var list = document.getElementById('sport_selected_attendee_list');
        if (!list) return;

        var countSpan = document.getElementById('sport_selected_count');
        if (countSpan) countSpan.textContent = sportSelectedAttendees.length;

        removeSportHiddenInputs();

        if (sportSelectedAttendees.length === 0) {
            list.innerHTML = '<div class="text-center text-muted p-3">Chưa chọn ai</div>';
            return;
        }

        list.innerHTML = '';
        var form = document.getElementById('add-sport-form');
        sportSelectedAttendees.forEach(function(att) {
            var item = document.createElement('a');
            item.href = '#';
            item.className = 'list-group-item list-group-item-action py-2';
            item.setAttribute('data-id', att.id);
            item.innerHTML = '<small>' + escapeHtml(att.full_name) + '</small>';
            item.addEventListener('click', function(e) {
                e.preventDefault();
                this.classList.toggle('active');
            });
            list.appendChild(item);

            var hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'attendee_ids[]';
            hidden.value = att.id;
            form.appendChild(hidden);
        });
    }

    function removeSportHiddenInputs() {
        var form = document.getElementById('add-sport-form');
        if (!form) return;
        var inputs = form.querySelectorAll('input[name="attendee_ids[]"]');
        inputs.forEach(function(input) { input.remove(); });
    }

    function addSelectedSportAttendee() {
        var list = document.getElementById('sport_available_attendee_list');
        var actives = list.querySelectorAll('.active');

        actives.forEach(function(el) {
            var id = el.getAttribute('data-id');
            var att = sportAllAttendees.find(function(a) { return a.id == id; });
            if (att) {
                sportSelectedAttendees.push(att);
            }
            el.classList.remove('active');
        });

        renderSportAvailableAttendees();
        renderSportSelectedAttendees();
    }

    function addAllSportAttendees() {
        var available = sportAllAttendees.filter(function(a) {
            return sportSelectedAttendees.findIndex(function(sel) { return sel.id == a.id; }) === -1;
        });

        available.forEach(function(att) {
            sportSelectedAttendees.push(att);
        });

        renderSportAvailableAttendees();
        renderSportSelectedAttendees();
    }

    function removeSelectedSportAttendee() {
        var list = document.getElementById('sport_selected_attendee_list');
        var actives = list.querySelectorAll('.active');

        actives.forEach(function(el) {
            var id = el.getAttribute('data-id');
            sportSelectedAttendees = sportSelectedAttendees.filter(function(a) { return a.id != id; });
        });

        renderSportAvailableAttendees();
        renderSportSelectedAttendees();
    }

    function removeAllSportAttendees() {
        sportSelectedAttendees = [];
        renderSportAvailableAttendees();
        renderSportSelectedAttendees();
    }

    function filterSportAttendeeList(term) {
        renderSportAvailableAttendees();
    }

    function resetSportModal() {
        var form = document.getElementById('add-sport-form');
        if (form) form.reset();

        var contentIdField = document.getElementById('sport_content_id');
        if (sportsContentId && contentIdField) {
            contentIdField.value = sportsContentId;
        }

        sportAllAttendees = [];
        sportSelectedAttendees = [];
        hideSportDualListbox();
        removeSportHiddenInputs();

        loadAllianceProperties();
        loadSportsList();
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

    function resetCompetitionModal() {
        var compSelect = document.getElementById('comp_competition_id');
        var propSelect = document.getElementById('comp_property_id');
        var contentIdField = document.getElementById('comp_content_id');

        document.getElementById('add-competition-form').reset();
        compSelect.innerHTML = '<option value="">-- Đang tải... --</option>';
        propSelect.innerHTML = '<option value="">-- Chọn cuộc thi trước --</option>';
        document.getElementById('comp_max_per_org').value = '-';

        if (competitionContentId && contentIdField) {
            contentIdField.value = competitionContentId;
            console.log('Set content_id to:', competitionContentId);
        } else {
            console.log('competitionContentId not found, searching in contentsData...');
            contentsData.forEach(function(c) {
                if (c.code === 'competition' && contentIdField) {
                    contentIdField.value = c.id;
                    competitionContentId = c.id;
                    console.log('Found and set content_id to:', c.id);
                }
            });
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

    function bindAttendeeEvents() {
        // Init datepickers for Add Staff modal
        var staffModal = document.getElementById('addAttendeeFromStaffModal');
        if (staffModal) {
            staffModal.addEventListener('shown.bs.modal', function() {
                console.log('Modal shown, calling initDatePickers');
                if (window.initDatePickers) {
                    window.initDatePickers();
                    console.log('initDatePickers called');
                    var checkInEl = document.getElementById('staff_check_in_date');
                    console.log('After init - checkInEl._flatpickr:', checkInEl ? checkInEl._flatpickr : null);
                }
            });
        }

        // Init datepickers for Add Manual modal
        var manualModal = document.getElementById('addAttendeeManualModal');
        if (manualModal) {
            manualModal.addEventListener('shown.bs.modal', function() {
                if (window.initDatePickers) window.initDatePickers();
            });
        }

        var searchInput = document.getElementById('attendee_staff_search');
        if (searchInput) {
            searchInput.addEventListener('input', function() {
                filterAttendeeStaffList(this.value);
            });
        }

        document.getElementById('btn_add_attendee_staff')?.addEventListener('click', addSelectedAttendeeStaff);
        document.getElementById('btn_add_all_attendee_staff')?.addEventListener('click', addAllAttendeeStaff);
        document.getElementById('btn_remove_attendee_staff')?.addEventListener('click', removeSelectedAttendeeStaff);
        document.getElementById('btn_remove_all_attendee_staff')?.addEventListener('click', removeAllAttendeeStaff);

        var form = document.getElementById('add-attendees-staff-form');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                if (attendeeSelectedStaff.length === 0) {
                    Toast.error('Vui lòng chọn ít nhất một nhân viên.');
                    return false;
                }

                // Get date values - try flatpickr first, then raw value
                var checkInEl = document.getElementById('staff_check_in_date');
                var checkOutEl = document.getElementById('staff_check_out_date');

                console.log('checkInEl:', checkInEl);
                console.log('checkInEl.value:', checkInEl ? checkInEl.value : 'null');
                console.log('checkInEl._flatpickr:', checkInEl ? checkInEl._flatpickr : 'null');
                console.log('checkOutEl:', checkOutEl);
                console.log('checkOutEl.value:', checkOutEl ? checkOutEl.value : 'null');
                console.log('checkOutEl._flatpickr:', checkOutEl ? checkOutEl._flatpickr : 'null');

                var checkInValue = '';
                var checkOutValue = '';

                if (checkInEl) {
                    if (checkInEl._flatpickr && checkInEl._flatpickr.selectedDates.length > 0) {
                        checkInValue = checkInEl._flatpickr.formatDate(checkInEl._flatpickr.selectedDates[0], 'Y-m-d');
                    } else if (checkInEl._flatpickr && checkInEl._flatpickr.altInput && checkInEl._flatpickr.altInput.value) {
                        checkInValue = checkInEl._flatpickr.altInput.value;
                    } else {
                        checkInValue = checkInEl.value;
                    }
                }

                if (checkOutEl) {
                    if (checkOutEl._flatpickr && checkOutEl._flatpickr.selectedDates.length > 0) {
                        checkOutValue = checkOutEl._flatpickr.formatDate(checkOutEl._flatpickr.selectedDates[0], 'Y-m-d');
                    } else if (checkOutEl._flatpickr && checkOutEl._flatpickr.altInput && checkOutEl._flatpickr.altInput.value) {
                        checkOutValue = checkOutEl._flatpickr.altInput.value;
                    } else {
                        checkOutValue = checkOutEl.value;
                    }
                }

                // If dates are in dd/mm/yyyy format, convert them
                if (checkInValue && checkInValue.indexOf('/') !== -1) {
                    var parts = checkInValue.split('/');
                    if (parts.length === 3) checkInValue = parts[2] + '-' + parts[1] + '-' + parts[0];
                }
                if (checkOutValue && checkOutValue.indexOf('/') !== -1) {
                    var parts = checkOutValue.split('/');
                    if (parts.length === 3) checkOutValue = parts[2] + '-' + parts[1] + '-' + parts[0];
                }

                console.log('Final checkInValue:', checkInValue);
                console.log('Final checkOutValue:', checkOutValue);

                if (!checkInValue || !checkOutValue) {
                    Toast.error('Vui lòng chọn ngày đến và ngày đi.');
                    return false;
                }

                var btn = document.getElementById('btn_submit_attendees_staff');
                btn.disabled = true;
                btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i>Đang thêm...';

                var formData = new FormData(form);

                // Ensure dates are included
                formData.set('check_in_date', checkInValue);
                formData.set('check_out_date', checkOutValue);

                // Debug: log all FormData entries
                console.log('FormData entries:');
                for (var pair of formData.entries()) {
                    console.log(pair[0] + ': ' + pair[1]);
                }

                fetch(form.action, {
                    method: 'POST',
                    body: formData
                })
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    btn.disabled = false;
                    btn.innerHTML = 'Thêm người tham dự';

                    if (data.success) {
                        Toast.success(data.message || 'Thêm thành công.');
                        // Thêm các staff đã chọn vào existingStaffIds để không hiện lại
                        attendeeSelectedStaff.forEach(function(staff) {
                            if (existingStaffIds.indexOf(parseInt(staff.id)) === -1) {
                                existingStaffIds.push(parseInt(staff.id));
                            }
                        });
                        bootstrap.Modal.getInstance(document.getElementById('addAttendeeFromStaffModal')).hide();
                        reloadAttendeesTable();
                        resetAttendeeStaffSelection();
                    } else {
                        Toast.error(data.error || 'Không thể thêm.');
                    }
                })
                .catch(function() {
                    btn.disabled = false;
                    btn.innerHTML = 'Thêm người tham dự';
                    Toast.error('Lỗi kết nối.');
                });
            });
        }
    }

    function resetAttendeeStaffSelection() {
        attendeeSelectedStaff = [];
        document.getElementById('staff_role_id').value = '';
        renderAttendeeAvailableStaff();
        renderAttendeeSelectedStaff();
    }

    function reloadAttendeesTable() {
        fetch(window.BASE_URL + '/admin/registrations/getAttendeesList?registration_id=' + registrationId)
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success && data.data) {
                    renderAttendeesTable(data.data);
                }
            });
    }

    function renderAttendeesTable(attendees) {
        var tbody = document.querySelector('#attendees-table tbody');
        if (!tbody) return;

        var hasActionCol = document.querySelector('#attendees-table thead th:last-child:empty') !== null;
        var colCount = hasActionCol ? 11 : 10;

        if (attendees.length === 0) {
            tbody.innerHTML = '<tr><td colspan="' + colCount + '" class="text-center text-muted">Chưa có người tham dự nào.</td></tr>';
            updateAttendeesCount(0);
            return;
        }

        var html = '';
        attendees.forEach(function(att, idx) {
            var photoHtml = att.portrait_path
                ? '<img src="' + escapeHtml(att.portrait_path) + '" class="rounded" style="width:160px;height:160px;object-fit:cover;cursor:pointer;" onclick="viewDocument(\'' + escapeHtml(att.portrait_path) + '\', \'image\')" title="Click để xem">'
                : '<div class="bg-light rounded d-flex align-items-center justify-content-center" style="width:160px;height:160px;"><i class="fa fa-user text-muted fa-3x"></i></div>';

            var statusLabel = getApprovalStatusLabel(att.approval_status);
            var positionDept = [];
            if (att.department_name) positionDept.push(att.department_name);
            if (att.position) positionDept.push(att.position);

            html += '<tr>' +
                '<td class="text-center">' + (idx + 1) + '</td>' +
                '<td class="text-center">' + photoHtml + '</td>' +
                '<td>' + escapeHtml(att.full_name) + '</td>' +
                '<td>' + escapeHtml(positionDept.join(' - ')) + '</td>' +
                '<td>' + escapeHtml(att.role_name || '') + '</td>' +
                '<td>' + formatDate(att.start_date) + '</td>' +
                '<td>' + formatDate(att.check_in_date) + '</td>' +
                '<td>' + formatDate(att.check_out_date) + '</td>' +
                '<td>' + escapeHtml(att.transport_name || '-') + '</td>' +
                '<td>' + statusLabel + '</td>';

            if (hasActionCol) {
                var docsBtn = '';
                var docs = {
                    portrait: att.portrait_path || att.photo_path || '',
                    cccd_front: att.cccd_front_path || '',
                    cccd_back: att.cccd_back_path || '',
                    contract: att.contract_path || ''
                };
                if (docs.portrait || docs.cccd_front || docs.cccd_back || docs.contract) {
                    var docsJson = escapeHtml(JSON.stringify(docs));
                    docsBtn = '<button type="button" class="btn btn-sm btn-outline-info me-1" onclick="viewAllDocuments(this)" data-docs="' + docsJson + '" title="Xem tài liệu đính kèm"><i class="fa fa-folder-open-o"></i></button>';
                }
                
                html += '<td class="text-center">' +
                    docsBtn +
                    '<button type="button" class="btn btn-sm btn-outline-primary me-1" onclick="editAttendee(' + att.id + ')" title="Sửa"><i class="fa fa-pencil"></i></button>' +
                    '<button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDeleteAttendee(' + att.id + ')" title="Xóa"><i class="fa fa-trash"></i></button>' +
                    '<form method="post" action="' + window.BASE_URL + '/admin/registrations/deleteAttendee/id/' + att.id + '/registration_id/' + registrationId + '" id="delete-attendee-form-' + att.id + '" style="display:none;"></form>' +
                '</td>';
            }

            html += '</tr>';
        });

        tbody.innerHTML = html;
        updateAttendeesCount(attendees.length);
    }

    function formatDate(dateStr) {
        if (!dateStr) return '-';
        var d = new Date(dateStr);
        if (isNaN(d.getTime())) return '-';
        var day = ('0' + d.getDate()).slice(-2);
        var month = ('0' + (d.getMonth() + 1)).slice(-2);
        var year = d.getFullYear();
        return day + '/' + month + '/' + year;
    }

    function getApprovalStatusLabel(status) {
        var labels = {
            0: '<span class="badge bg-warning">Chờ duyệt</span>',
            1: '<span class="badge bg-success">Đã duyệt</span>',
            2: '<span class="badge bg-danger">Từ chối</span>'
        };
        return labels[status] || '<span class="badge bg-secondary">Không xác định</span>';
    }

    function updateAttendeesCount(count) {
        var header = document.querySelector('#attendees-card .card-header h5');
        if (header) {
            header.innerHTML = '<i class="fa fa-users me-2"></i>Danh sách người tham dự (' + count + ')';
        }
    }

    function loadAttendeeStaffList() {
        var availableList = document.getElementById('attendee_available_staff_list');
        if (!availableList) return;

        availableList.innerHTML = '<div class="text-center p-3"><i class="fa fa-spinner fa-spin"></i> Đang tải...</div>';

        fetch(window.BASE_URL + '/admin/registrations/getStaffByProperty?property_id=' + propertyId)
            .then(function(response) { return response.json(); })
            .then(function(data) {
                attendeeAllStaff = data.success && data.data ? data.data : [];
                attendeeSelectedStaff = [];
                renderAttendeeAvailableStaff();
                renderAttendeeSelectedStaff();
            });
    }

    function renderAttendeeAvailableStaff() {
        var list = document.getElementById('attendee_available_staff_list');
        if (!list) return;

        var searchTerm = (document.getElementById('attendee_staff_search')?.value || '').toLowerCase();

        var available = attendeeAllStaff.filter(function(s) {
            // Loại bỏ những người đã chọn trong session hiện tại
            if (attendeeSelectedStaff.findIndex(function(sel) { return sel.id == s.id; }) !== -1) {
                return false;
            }
            // Loại bỏ những người đã là attendee trước đó
            if (existingStaffIds.indexOf(parseInt(s.id)) !== -1) {
                return false;
            }
            return true;
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
            var subInfo = [];
            if (staff.department_name) subInfo.push(staff.department_name);
            if (staff.position) subInfo.push(staff.position);
            if (staff.join_hotel_date) subInfo.push('Vào: ' + formatDate(staff.join_hotel_date));
            item.innerHTML = '<small>' + escapeHtml(staff.display) + '</small>' +
                (subInfo.length ? '<br><span class="text-muted" style="font-size:11px;">' + escapeHtml(subInfo.join(' - ')) + '</span>' : '');
            item.addEventListener('click', function(e) {
                e.preventDefault();
                this.classList.toggle('active');
            });
            list.appendChild(item);
        });
    }

    function renderAttendeeSelectedStaff() {
        var list = document.getElementById('attendee_selected_staff_list');
        if (!list) return;

        var countSpan = document.getElementById('attendee_selected_count');
        if (countSpan) countSpan.textContent = attendeeSelectedStaff.length;

        removeAttendeeHiddenInputs();

        if (attendeeSelectedStaff.length === 0) {
            list.innerHTML = '<div class="text-center text-muted p-3">Chưa chọn nhân viên nào</div>';
            return;
        }

        list.innerHTML = '';
        var form = document.getElementById('add-attendees-staff-form');
        attendeeSelectedStaff.forEach(function(staff) {
            var item = document.createElement('a');
            item.href = '#';
            item.className = 'list-group-item list-group-item-action py-2';
            item.setAttribute('data-id', staff.id);
            var joinDateInfo = staff.join_hotel_date ? '<br><span class="text-muted" style="font-size:11px;">Ngày vào: ' + formatDate(staff.join_hotel_date) + '</span>' : '';
            item.innerHTML = '<small>' + escapeHtml(staff.display) + '</small>' + joinDateInfo;
            item.addEventListener('click', function(e) {
                e.preventDefault();
                this.classList.toggle('active');
            });
            list.appendChild(item);

            // Hidden input for staff_id
            var hiddenId = document.createElement('input');
            hiddenId.type = 'hidden';
            hiddenId.name = 'staff_ids[]';
            hiddenId.value = staff.id;
            form.appendChild(hiddenId);
        });
    }

    function removeAttendeeHiddenInputs() {
        var form = document.getElementById('add-attendees-staff-form');
        if (!form) return;
        var inputs = form.querySelectorAll('input[name="staff_ids[]"]');
        inputs.forEach(function(input) { input.remove(); });
    }

    function addSelectedAttendeeStaff() {
        var list = document.getElementById('attendee_available_staff_list');
        var actives = list.querySelectorAll('.active');

        actives.forEach(function(el) {
            var id = el.getAttribute('data-id');
            var staff = attendeeAllStaff.find(function(s) { return s.id == id; });
            if (staff) {
                attendeeSelectedStaff.push(staff);
            }
            el.classList.remove('active');
        });

        renderAttendeeAvailableStaff();
        renderAttendeeSelectedStaff();
    }

    function addAllAttendeeStaff() {
        var available = attendeeAllStaff.filter(function(s) {
            // Loại bỏ đã chọn trong session
            if (attendeeSelectedStaff.findIndex(function(sel) { return sel.id == s.id; }) !== -1) {
                return false;
            }
            // Loại bỏ đã là attendee
            if (existingStaffIds.indexOf(parseInt(s.id)) !== -1) {
                return false;
            }
            return true;
        });

        available.forEach(function(staff) {
            attendeeSelectedStaff.push(staff);
        });

        renderAttendeeAvailableStaff();
        renderAttendeeSelectedStaff();
    }

    function removeSelectedAttendeeStaff() {
        var list = document.getElementById('attendee_selected_staff_list');
        var actives = list.querySelectorAll('.active');

        actives.forEach(function(el) {
            var id = el.getAttribute('data-id');
            attendeeSelectedStaff = attendeeSelectedStaff.filter(function(s) { return s.id != id; });
        });

        renderAttendeeAvailableStaff();
        renderAttendeeSelectedStaff();
    }

    function removeAllAttendeeStaff() {
        attendeeSelectedStaff = [];
        renderAttendeeAvailableStaff();
        renderAttendeeSelectedStaff();
    }

    function filterAttendeeStaffList(term) {
        renderAttendeeAvailableStaff();
    }

    function editAttendee(id) {
        var modal = document.getElementById('editAttendeeModal');
        if (!modal) {
            window.location.href = window.BASE_URL + '/admin/attendees/update/id/' + id;
            return;
        }

        document.getElementById('edit-attendee-form').reset();
        document.getElementById('edit_attendee_id').value = id;
        clearPreviews();

        fetch(window.BASE_URL + '/admin/registrations/getAttendeeDetail?id=' + id)
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success && data.data) {
                    var att = data.data;
                    document.getElementById('edit_full_name').value = att.full_name || '';
                    document.getElementById('edit_position').value = att.position || '';
                    document.getElementById('edit_department').value = att.department_name || '';
                    document.getElementById('edit_role_id').value = att.role_id || '';
                    document.getElementById('edit_note').value = att.note || '';
                    document.getElementById('edit_start_date').value = formatDate(att.start_date);
                    document.getElementById('edit_transport_id').value = att.transport_id || '';

                    if (att.portrait_path) {
                        showPreview('edit_portrait_preview', att.portrait_path);
                    }
                    if (att.cccd_front_path) {
                        showPreview('edit_cccd_front_preview', att.cccd_front_path);
                    }
                    if (att.cccd_back_path) {
                        showPreview('edit_cccd_back_preview', att.cccd_back_path);
                    }
                    if (att.contract_path) {
                        showPreview('edit_contract_preview', att.contract_path, att.contract_path.indexOf('.pdf') > -1);
                    }

                    var bsModal = new bootstrap.Modal(modal);
                    bsModal.show();

                    // Re-init datepickers and set values after modal is shown
                    modal.addEventListener('shown.bs.modal', function initOnce() {
                        if (window.initDatePickers) window.initDatePickers();
                        var checkInEl = document.getElementById('edit_check_in_date');
                        var checkOutEl = document.getElementById('edit_check_out_date');
                        if (checkInEl._flatpickr && att.check_in_date) checkInEl._flatpickr.setDate(att.check_in_date, true);
                        if (checkOutEl._flatpickr && att.check_out_date) checkOutEl._flatpickr.setDate(att.check_out_date, true);
                        modal.removeEventListener('shown.bs.modal', initOnce);
                    });
                } else {
                    Toast.error(data.error || 'Không thể tải thông tin.');
                }
            })
            .catch(function() {
                Toast.error('Lỗi kết nối.');
            });
    }

    function clearPreviews() {
        ['edit_portrait_preview', 'edit_cccd_front_preview', 'edit_cccd_back_preview', 'edit_contract_preview'].forEach(function(id) {
            var el = document.getElementById(id);
            if (el) el.innerHTML = '';
        });
    }

    function showPreview(elementId, url, isPdf) {
        var el = document.getElementById(elementId);
        if (!el) return;
        if (isPdf) {
            el.innerHTML = '<a href="' + url + '" target="_blank" class="btn btn-sm btn-outline-primary"><i class="fa fa-file-pdf-o me-1"></i>Xem PDF</a>';
        } else {
            el.innerHTML = '<img src="' + url + '" class="img-thumbnail" style="max-height:80px;cursor:pointer;" onclick="RegistrationView.viewDocument(\'' + url + '\', \'image\')">';
        }
    }

    function bindAddAttendeeModalReset() {
        var modal = document.getElementById('addAttendeeManualModal');
        if (!modal) return;
        modal.addEventListener('show.bs.modal', function() {
            ['add_portrait_preview', 'add_cccd_front_preview', 'add_cccd_back_preview', 'add_contract_preview'].forEach(function(id) {
                var el = document.getElementById(id);
                if (el) el.innerHTML = '';
            });
        });

        // Handle form submission for addAttendeeManualModal
        var form = document.getElementById('add-attendee-manual-form');
        if (form) {
            form.addEventListener('submit', function(e) {
                var checkInEl = document.getElementById('add_check_in_date');
                var checkOutEl = document.getElementById('add_check_out_date');
                var startDateEl = document.getElementById('add_start_date');

                // Set values from flatpickr to hidden inputs
                if (checkInEl._flatpickr && checkInEl._flatpickr.selectedDates[0]) {
                    checkInEl.value = checkInEl._flatpickr.formatDate(checkInEl._flatpickr.selectedDates[0], 'Y-m-d');
                }
                if (checkOutEl._flatpickr && checkOutEl._flatpickr.selectedDates[0]) {
                    checkOutEl.value = checkOutEl._flatpickr.formatDate(checkOutEl._flatpickr.selectedDates[0], 'Y-m-d');
                }
                if (startDateEl && startDateEl._flatpickr && startDateEl._flatpickr.selectedDates[0]) {
                    startDateEl.value = startDateEl._flatpickr.formatDate(startDateEl._flatpickr.selectedDates[0], 'Y-m-d');
                }
            });
        }
    }

    function bindFilePreview() {
        var selectors = '#editAttendeeModal input[type="file"], #addAttendeeManualModal input[type="file"]';
        var fileInputs = document.querySelectorAll(selectors);
        fileInputs.forEach(function(input) {
            input.addEventListener('change', function(e) {
                var file = e.target.files[0];
                if (!file) return;

                var baseName = input.name.replace('_file', '_preview');
                var previewId = input.closest('#editAttendeeModal') ? 'edit_' + baseName : 'add_' + baseName;
                var previewEl = document.getElementById(previewId);

                if (!previewEl) return;

                if (file.type.startsWith('image/')) {
                    var reader = new FileReader();
                    reader.onload = function(ev) {
                        previewEl.innerHTML = '<img src="' + ev.target.result + '" class="img-thumbnail" style="max-height:100px;">';
                    };
                    reader.readAsDataURL(file);
                } else if (file.type === 'application/pdf') {
                    previewEl.innerHTML = '<span class="badge bg-secondary"><i class="fa fa-file-pdf-o me-1"></i>' + escapeHtml(file.name) + '</span>';
                }
            });
        });
    }

    function bindEditAttendeeForm() {
        var form = document.getElementById('edit-attendee-form');
        if (!form) return;

        bindFilePreview();

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            var formData = new FormData(form);

            // Ensure flatpickr dates are included
            var checkInEl = document.getElementById('edit_check_in_date');
            var checkOutEl = document.getElementById('edit_check_out_date');
            if (checkInEl._flatpickr && checkInEl._flatpickr.selectedDates[0]) {
                formData.set('check_in_date', checkInEl._flatpickr.formatDate(checkInEl._flatpickr.selectedDates[0], 'Y-m-d'));
            }
            if (checkOutEl._flatpickr && checkOutEl._flatpickr.selectedDates[0]) {
                formData.set('check_out_date', checkOutEl._flatpickr.formatDate(checkOutEl._flatpickr.selectedDates[0], 'Y-m-d'));
            }

            var btn = document.getElementById('btn_save_attendee');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i>Đang lưu...';

            fetch(window.BASE_URL + '/admin/registrations/updateAttendeeAjax', {
                method: 'POST',
                body: formData
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa fa-save me-1"></i>Lưu thay đổi';

                if (data.success) {
                    Toast.success(data.message || 'Cập nhật thành công.');
                    bootstrap.Modal.getInstance(document.getElementById('editAttendeeModal')).hide();
                    setTimeout(function() { location.reload(); }, 1000);
                } else {
                    Toast.error(data.error || 'Không thể cập nhật.');
                }
            })
            .catch(function() {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa fa-save me-1"></i>Lưu thay đổi';
                Toast.error('Lỗi kết nối.');
            });
        });
    }

    function confirmDeleteAttendee(attId) {
        Swal.fire({
            title: 'Xác nhận xóa',
            text: 'Bạn có chắc chắn muốn xóa người tham dự này?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Xóa',
            cancelButtonText: 'Hủy'
        }).then(function(result) {
            if (result.isConfirmed) {
                document.getElementById('delete-attendee-form-' + attId).submit();
            }
        });
    }

    window.viewAllDocuments = function(btn) {
        try {
            var docsStr = btn.getAttribute('data-docs');
            var docs = JSON.parse(docsStr);
            var html = '';
            
            if (docs.portrait) {
                html += '<div class="mb-4 text-center"><h6>Ảnh chân dung</h6>';
                html += '<img src="' + escapeHtml(docs.portrait) + '" class="rounded" style="width: 530px; height: 530px; object-fit: cover; max-width: 100%;"></div>';
            }
            if (docs.cccd_front) {
                html += '<div class="mb-4 text-center"><h6>Ảnh CCCD mặt trước</h6>';
                html += '<img src="' + escapeHtml(docs.cccd_front) + '" class="img-fluid rounded" style="max-height: 500px;"></div>';
            }
            if (docs.cccd_back) {
                html += '<div class="mb-4 text-center"><h6>Ảnh CCCD mặt sau</h6>';
                html += '<img src="' + escapeHtml(docs.cccd_back) + '" class="img-fluid rounded" style="max-height: 500px;"></div>';
            }
            if (docs.contract) {
                html += '<div class="mb-4 text-center"><h6>Hợp đồng lao động</h6>';
                var isPdf = docs.contract.toLowerCase().indexOf('.pdf') > -1;
                if (isPdf) {
                    html += '<iframe src="' + escapeHtml(docs.contract) + '" style="width:100%; height:700px;" frameborder="0"></iframe>';
                } else {
                    html += '<img src="' + escapeHtml(docs.contract) + '" class="img-fluid rounded" style="max-height: 700px;">';
                }
                html += '</div>';
            }
            
            if (html === '') {
                html = '<div class="alert alert-info">Không có tài liệu nào.</div>';
            }
            
            document.getElementById('all_documents_viewer').innerHTML = html;
            new bootstrap.Modal(document.getElementById('allDocumentsModal')).show();
        } catch (e) {
            console.error(e);
            Toast.error('Không thể hiển thị tài liệu.');
        }
    };

    return {
        init: init,
        resetAddModal: resetSportModal,
        resetCompetitionModal: resetCompetitionModal,
        resetSportModal: resetSportModal,
        viewDocument: viewDocument,
        confirmDeleteDetail: confirmDeleteDetail,
        editAttendee: editAttendee,
        confirmDeleteAttendee: confirmDeleteAttendee
    };
})();
