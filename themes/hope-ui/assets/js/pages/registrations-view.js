var RegistrationView = (function() {
    var eventId = null;
    var registrationId = null;
    var propertyId = null;
    var propertyCode = null;
    var isHotel = false;
    var hasGolf = false;
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
    var canEdit = false;

    // Sport registration variables
    var sportAllAttendees = [];
    var sportSelectedAttendees = [];
    var originalSportTeamAttendeeIds = [];
    var sportsDataCache = []; // Cache danh sách môn thể thao với min_members

    // Pending sport registrations (preview before save)
    var pendingSportRegistrations = [];
    var editingSportIndex = -1; // -1 = adding new, >= 0 = editing existing

    function initBootstrapFileInput(selector, options) {
        if (typeof $.fn.fileinput === 'undefined') return;
        var defaultOptions = {
            language: 'vi',
            showUpload: false,
            showRemove: true,
            showCaption: true,
            dropZoneEnabled: true,
            browseClass: "btn btn-primary btn-sm",
            removeClass: "btn btn-danger btn-sm",
            browseIcon: "<i class=\"fa fa-folder-open-o\"></i> ",
            removeIcon: "<i class=\"fa fa-trash-o\"></i> ",
            fileActionSettings: {
                showUpload: false,
                showRemove: true,
                showZoom: true
            }
        };
        var mergedOptions = $.extend(true, {}, defaultOptions, options);
        $(selector).fileinput('destroy').fileinput(mergedOptions);
    }

    function init(config) {
        eventId = config.eventId;
        registrationId = config.registrationId;
        propertyId = config.propertyId;
        propertyCode = config.propertyCode;
        isHotel = config.isHotel || false;
        registeredSports = config.registeredSports || [];
        registeredCompetitions = config.registeredCompetitions || [];
        existingStaffIds = config.existingStaffIds || [];
        canEdit = config.canEdit || false;

        if (eventId) {
            loadContentsData();
            loadMainSportsList();
            loadAlliancePropertiesDropdown();
        }

        bindCompetitionEvents();
        bindEditCompetitionEvents();
        bindSportEvents();
        bindSportCardEvents();
        bindAttendeeEvents();
        bindEditAttendeeForm();
        bindAddAttendeeModalReset();
        bindMissEvents();
        bindEditMissForm();
        bindTalentEvents();

        if (isHotel && propertyId) {
            loadAttendeeStaffList();
        }

        // Initialize bootstrap-fileinput for static forms
        initBootstrapFileInput("#import_excel_file", {
            allowedFileExtensions: ["xls", "xlsx"],
            maxFileSize: 10240,
            dropZoneTitle: 'Kéo & thả file Excel vào đây ...'
        });
        initBootstrapFileInput("#upload_documents", {
            allowedFileExtensions: ["jpg", "jpeg", "png", "pdf", "doc", "docx", "xls", "xlsx"],
            maxFileSize: 10240,
            dropZoneTitle: 'Kéo & thả nhiều tệp đính kèm vào đây ...'
        });
        initBootstrapFileInput("#add_portrait_file", {
            allowedFileExtensions: ["jpg", "jpeg", "png"],
            maxFileSize: 5120,
            dropZoneTitle: 'Kéo thả ảnh chân dung vào đây ...'
        });
        initBootstrapFileInput("#add_cccd_front_file", {
            allowedFileExtensions: ["jpg", "jpeg", "png"],
            maxFileSize: 5120,
            dropZoneTitle: 'Kéo thả ảnh CCCD mặt trước vào đây ...'
        });
        initBootstrapFileInput("#add_cccd_back_file", {
            allowedFileExtensions: ["jpg", "jpeg", "png"],
            maxFileSize: 5120,
            dropZoneTitle: 'Kéo thả ảnh CCCD mặt sau vào đây ...'
        });
        initBootstrapFileInput("#add_contract_file", {
            allowedFileExtensions: ["jpg", "jpeg", "png", "pdf"],
            maxFileSize: 5120,
            dropZoneTitle: 'Kéo thả ảnh hoặc tệp PDF hợp đồng ...'
        });
    }

    // Load danh sách môn thể thao vào dropdown chính (ngoài card)
    function loadMainSportsList() {
        var sportSelect = document.getElementById('sport_select_main');
        if (!sportSelect) return;

        sportSelect.innerHTML = '<option value="">-- Đang tải... --</option>';

        fetch(window.BASE_URL + '/admin/registrations/getContentItems?event_id=' + eventId + '&content_type=sports')
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success && data.data && data.data.length > 0) {
                    sportsDataCache = data.data; // Lưu cache để lấy min_members
                    var html = renderSportsTree(data.data, registeredSports);
                    sportSelect.innerHTML = html;
                    var modalSportSelect = document.getElementById('sport_item_id');
                    if (modalSportSelect) {
                        modalSportSelect.innerHTML = html;
                    }
                } else {
                    sportsDataCache = [];
                    var emptyHtml = '<option value="">-- Không có môn nào --</option>';
                    sportSelect.innerHTML = emptyHtml;
                    var modalSportSelect = document.getElementById('sport_item_id');
                    if (modalSportSelect) {
                        modalSportSelect.innerHTML = emptyHtml;
                    }
                }
            });
    }

    // Lấy min_members từ cache theo sport_id
    function getSportMinMembersById(sportId) {
        var sport = sportsDataCache.find(function(s) { return s.id == sportId; });
        if (sport) {
            if (sport.min_members !== undefined && sport.min_members !== null && sport.min_members !== '') {
                return parseInt(sport.min_members);
            }
            if (sport.min_per_team_member !== undefined && sport.min_per_team_member !== null && sport.min_per_team_member !== '') {
                return parseInt(sport.min_per_team_member);
            }
        }
        return 1;
    }

    // Lấy max_members từ cache theo sport_id
    function getSportMaxMembersById(sportId) {
        var sport = sportsDataCache.find(function(s) { return s.id == sportId; });
        if (sport) {
            if (sport.max_members !== undefined && sport.max_members !== null && sport.max_members !== '') {
                var val = parseInt(sport.max_members);
                if (!isNaN(val) && val > 0) return val;
            }
            if (sport.max_per_team_member !== undefined && sport.max_per_team_member !== null && sport.max_per_team_member !== '') {
                var val = parseInt(sport.max_per_team_member);
                if (!isNaN(val) && val > 0) return val;
            }
        }
        return null;
    }

    // Load danh sách đơn vị có thể liên quân vào dropdown
    function loadAlliancePropertiesDropdown() {
        var allianceSelect = document.getElementById('sport_alliance_property');
        if (!allianceSelect) return;

        var sportsCard = document.getElementById('sports-registration-card');
        var sportsEventContentId = sportsCard ? sportsCard.getAttribute('data-event-content-id') : '';

        fetch(window.BASE_URL + '/admin/registrations/getAllianceProperties?registration_id=' + registrationId + '&event_content_id=' + sportsEventContentId)
            .then(function(response) { return response.json(); })
            .then(function(data) {
                allianceSelect.innerHTML = '';
                var modalList = document.getElementById('alliance_modal_list');
                if (modalList) modalList.innerHTML = '';

                if (data.success && data.data && data.data.length > 0) {
                    data.data.forEach(function(item) {
                        var opt = document.createElement('option');
                        opt.value = item.id;
                        opt.setAttribute('data-code', item.code);
                        opt.textContent = item.code + ' - ' + item.name;
                        allianceSelect.appendChild(opt);

                        if (modalList) {
                            var div = document.createElement('div');
                            div.className = 'form-check mb-2';
                            var escapedName = escapeHtml(item.code + ' - ' + item.name);
                            var checked = item.is_selected == 1 ? 'checked' : '';
                            div.innerHTML = '<input class="form-check-input alliance-modal-cb" type="checkbox" value="'+item.id+'" data-name="'+escapedName+'" data-code="'+escapeHtml(item.code)+'" id="modal_alliance_'+item.id+'" '+checked+'>' +
                                            '<label class="form-check-label" for="modal_alliance_'+item.id+'">' + escapedName + '</label>';
                            modalList.appendChild(div);
                        }
                    });
                    
                    // Trigger a UI update to display previously selected
                    setTimeout(function() {
                        if (document.getElementById('btn_confirm_alliance')) {
                            // Cập nhật lại UI hiển thị ở dưới nút
                            var checkboxes = document.querySelectorAll('.alliance-modal-cb');
                            var selectedTexts = [];
                            var selectedIds = [];
                            checkboxes.forEach(function(cb) {
                                if (cb.checked) {
                                    selectedIds.push(cb.value);
                                    selectedTexts.push(cb.getAttribute('data-name'));
                                }
                            });
                            
                            var allianceSelect = document.getElementById('sport_alliance_property');
                            if (allianceSelect) {
                                Array.from(allianceSelect.options).forEach(function(opt) {
                                    opt.selected = selectedIds.includes(opt.value);
                                });
                            }
                            
                            var displayText = document.getElementById('alliance_selected_texts');
                            if (displayText) {
                                displayText.innerHTML = '';
                                if (selectedIds.length > 0) {
                                    for (var i = 0; i < selectedIds.length; i++) {
                                        var selId = selectedIds[i];
                                        var selText = selectedTexts[i];
                                        var badge = document.createElement('span');
                                        badge.className = 'badge bg-primary me-1 mb-1 p-2 border';
                                        badge.style.fontSize = '12px';
                                        badge.innerHTML = selText + ' <i class="fa fa-times ms-1 text-white" style="cursor:pointer;" onclick="removeAllianceProperty(\'' + selId + '\')" title="Huỷ"></i>';
                                        displayText.appendChild(badge);
                                    }
                                }
                            }
                        }
                    }, 100);
                } else {
                    if (modalList) {
                        modalList.innerHTML = '<p class="text-muted mb-0">Không có đơn vị nào để liên quân.</p>';
                    }
                }
            });
    }

    function getSportMinPlayers(sportName) {
        if (!sportName) return 1;
        var nameLower = sportName.toLowerCase();
        
        // Strip Vietnamese accents for safe matching (handles both pre-composed and decomposed Unicode)
        var nameNoSign = nameLower
            .replace(/[àáạảãâầấậẩẫăằắặẳẵ]/g, "a")
            .replace(/[èéẹẻẽêềếệểễ]/g, "e")
            .replace(/[ìíịỉĩ]/g, "i")
            .replace(/[òóọỏõôồốộổỗơờớợởỡ]/g, "o")
            .replace(/[ùúụủũưừứựửữ]/g, "u")
            .replace(/[ỳýỵỷỹ]/g, "y")
            .replace(/[đĐ]/g, "d");

        // Racket sports (Table tennis, Badminton, Tennis, Pickleball) - Min is always 1 (singles) or 2 (doubles)
        if (nameNoSign.indexOf('bong ban') !== -1 || 
            nameNoSign.indexOf('cau long') !== -1 || 
            nameNoSign.indexOf('tennis') !== -1 || 
            nameNoSign.indexOf('quan vot') !== -1 || 
            nameNoSign.indexOf('pickleball') !== -1 || 
            nameNoSign.indexOf('pickerball') !== -1) {
            
            if (nameNoSign.indexOf('doi') !== -1 || nameNoSign.indexOf('doubles') !== -1) {
                return 2;
            }
            return 1; // Default for racket sports is singles (min 1)
        }

        if (nameNoSign.indexOf('bong da') !== -1 || nameNoSign.indexOf('football') !== -1 || nameNoSign.indexOf('soccer') !== -1) {
            return 11;
        }
        if (nameNoSign.indexOf('keo co') !== -1) {
            return 8;
        }
        if (nameNoSign.indexOf('bong chuyen') !== -1 || nameNoSign.indexOf('volleyball') !== -1) {
            return 6;
        }
        if (nameNoSign.indexOf('boi tiep suc') !== -1 || nameNoSign.indexOf('boi dong doi') !== -1) {
            return 4;
        }
        if (nameNoSign.indexOf('doi') !== -1 || nameNoSign.indexOf('doubles') !== -1) {
            return 2;
        }
        if (nameNoSign.indexOf('don') !== -1 || nameNoSign.indexOf('singles') !== -1 || nameNoSign.indexOf('co vua') !== -1 || nameNoSign.indexOf('co tuong') !== -1) {
            return 1;
        }
        
        return 1;
    }

    function updateSportTeamName(sportNameOverride) {
        var teamNameInput = document.getElementById('sport_team_name');
        if (!teamNameInput) return;

        // Get selected sport ID from modal or main select
        var sportSelect = document.getElementById('sport_select_main');
        var modalSportSelect = document.getElementById('sport_item_id');
        var sportId = modalSportSelect && modalSportSelect.value 
            ? modalSportSelect.value 
            : (sportSelect ? sportSelect.value : '');

        // Tên đội readonly - tự động sinh
        teamNameInput.readOnly = true;

        // Get selected sport name to check min players
        var sportText = sportNameOverride || '';
        if (!sportText) {
            if (modalSportSelect && modalSportSelect.value && modalSportSelect.selectedIndex >= 0) {
                sportText = modalSportSelect.options[modalSportSelect.selectedIndex].text;
            } else if (sportSelect && sportSelect.value && sportSelect.selectedIndex >= 0) {
                sportText = sportSelect.options[sportSelect.selectedIndex].text;
            }
        }

        // Clean spaces and HTML entities
        sportText = sportText.replace(/[\u00a0\s]+/g, ' ').trim();

        // Kiểm tra checkbox liên quân mới
        var isAllianceCheckbox = document.getElementById('sport_is_alliance');
        var isAlliance = isAllianceCheckbox && isAllianceCheckbox.checked;

        if (isAlliance) {
            // Lấy danh sách alliance đã chọn từ checkbox mới
            var checkboxes = document.querySelectorAll('.sport-alliance-cb:checked');
            var allianceCodes = [];
            checkboxes.forEach(function(cb) {
                var code = cb.getAttribute('data-code');
                if (code) allianceCodes.push(code);
            });

            if (allianceCodes.length > 0) {
                var allCodes = [propertyCode].concat(allianceCodes);
                teamNameInput.value = 'Liên quân ' + allCodes.join(' - ');
            } else {
                teamNameInput.value = propertyCode || 'Team';
            }
        } else {
            // Team độc lập - tên theo property code
            teamNameInput.value = propertyCode || 'Team';
        }
    }

    // Kiểm tra pending alliance ngay khi chọn môn có max_per_team_member > 3
    function checkSportPendingAlliance(sportId) {
        var sport = sportsDataCache.find(function(s) { return s.id == sportId; });
        var maxPerTeam = sport ? (parseInt(sport.max_per_team_member) || parseInt(sport.max_members) || 999) : 999;

        // Chỉ check nếu max_per_team_member > 3 (môn đồng đội lớn)
        if (maxPerTeam <= 3) {
            return;
        }

        fetch(window.BASE_URL + '/admin/registrations/checkSportPendingAlliance?registration_id=' + registrationId + '&sport_id=' + sportId, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (!data.success && data.error) {
                Toast.error(data.error);
                // Reset dropdown và disable nút
                var sportSelect = document.getElementById('sport_select_main');
                var btnOpen = document.getElementById('btn_open_sport_modal');
                if (sportSelect) sportSelect.value = '';
                if (btnOpen) btnOpen.disabled = true;
            }
        });
    }

    // Bind events cho sport card (dropdown chọn môn, liên quân, button mở modal)
    function bindSportCardEvents() {
        var sportSelect = document.getElementById('sport_select_main');
        var btnOpen = document.getElementById('btn_open_sport_modal');
        var allianceSelect = document.getElementById('sport_alliance_property');

        if (sportSelect) {
            sportSelect.addEventListener('change', function() {
                var sportId = this.value;
                if (btnOpen) {
                    btnOpen.disabled = !sportId;
                }
                // Kiểm tra pending alliance ngay khi chọn môn có max_per_team_member > 3
                if (sportId) {
                    checkSportPendingAlliance(sportId);
                }
            });
        }

        var modalSportSelect = document.getElementById('sport_item_id');
        if (modalSportSelect) {
            modalSportSelect.addEventListener('change', function() {
                renderSportSelectedAttendees();
                var selectedText = this.options[this.selectedIndex].text;
                updateSportTeamName(selectedText);
            });
        }

        var btnConfirmAlliance = document.getElementById('btn_confirm_alliance');
        if (btnConfirmAlliance) {
            btnConfirmAlliance.addEventListener('click', function() {
                var checkboxes = document.querySelectorAll('.alliance-modal-cb');
                var selectedTexts = [];
                var selectedIds = [];
                checkboxes.forEach(function(cb) {
                    if (cb.checked) {
                        selectedIds.push(cb.value);
                        selectedTexts.push(cb.getAttribute('data-name'));
                    }
                });

                if (allianceSelect) {
                    Array.from(allianceSelect.options).forEach(function(opt) {
                        opt.selected = selectedIds.includes(opt.value);
                    });
                }

                var displayText = document.getElementById('alliance_selected_texts');
                if (displayText) {
                    displayText.innerHTML = '';
                    if (selectedIds.length > 0) {
                        for (var i = 0; i < selectedIds.length; i++) {
                            var selId = selectedIds[i];
                            var selText = selectedTexts[i];
                            var badge = document.createElement('span');
                            badge.className = 'badge bg-primary me-1 mb-1 p-2 border';
                            badge.style.fontSize = '12px';
                            badge.innerHTML = selText + ' <i class="fa fa-times ms-1 text-white" style="cursor:pointer;" onclick="removeAllianceProperty(\'' + selId + '\')" title="Huỷ"></i>';
                            displayText.appendChild(badge);
                        }
                    }
                }

                // AJAX Save Alliance Properties
                btnConfirmAlliance.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Đang lưu...';
                btnConfirmAlliance.disabled = true;

                var sportsCard = document.getElementById('sports-registration-card');
                var sportsEventContentId = sportsCard ? sportsCard.getAttribute('data-event-content-id') : '';

                var formData = new FormData();
                formData.append('registration_id', registrationId);
                if (sportsEventContentId) {
                    formData.append('event_content_id', sportsEventContentId);
                }
                selectedIds.forEach(function(id) {
                    formData.append('target_org_ids[]', id);
                });

                fetch(window.BASE_URL + '/admin/registrations/saveAllianceProperties', {
                    method: 'POST',
                    body: formData
                })
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    btnConfirmAlliance.innerHTML = 'Xác nhận';
                    btnConfirmAlliance.disabled = false;

                    if (data.success) {
                        Toast.success('Lưu đơn vị liên quân thành công');
                        
                        var modalEl = document.getElementById('alliancePropertyModal');
                        if (modalEl) {
                            var modal = bootstrap.Modal.getInstance(modalEl);
                            if (modal) {
                                modal.hide();
                            } else {
                                modal = new bootstrap.Modal(modalEl);
                                modal.hide();
                            }
                        }
                        
                        updateSportTeamName();
                    } else {
                        Toast.error(data.error || 'Có lỗi xảy ra');
                    }
                })
                .catch(function(err) {
                    btnConfirmAlliance.innerHTML = 'Xác nhận';
                    btnConfirmAlliance.disabled = false;
                    Toast.error('Có lỗi xảy ra khi lưu đơn vị liên quân');
                });
            });
        }

        if (btnOpen) {
            btnOpen.addEventListener('click', function() {
                var sportId = sportSelect ? sportSelect.value : '';
                if (!sportId) {
                    Toast.error('Vui lòng chọn môn thể thao.');
                    return;
                }

                // Reset modal state trước khi mở (tránh bị disable từ lần edit trước)
                resetSportModalUI();

                // Lấy tên môn đã chọn
                var sportName = sportSelect.options[sportSelect.selectedIndex].text;

                // Lấy danh sách liên quân đã chọn
                var allianceIds = [];
                if (allianceSelect) {
                    Array.from(allianceSelect.selectedOptions).forEach(function(opt) {
                        allianceIds.push(opt.value);
                    });
                }

                // Set giá trị vào modal - ẩn dropdown, hiện tên môn
                var modalSportSelect = document.getElementById('sport_item_id');
                var sportNameDiv = document.getElementById('sport_selected_name');
                if (modalSportSelect) {
                    modalSportSelect.value = sportId;
                    modalSportSelect.classList.add('d-none');
                }
                if (sportNameDiv) {
                    sportNameDiv.textContent = sportName;
                    sportNameDiv.classList.remove('d-none');
                }

                // Set alliance vào modal (checkboxes)
                var allianceContainer = document.getElementById('alliance_checkboxes');
                if (allianceContainer) {
                    var checkboxes = allianceContainer.querySelectorAll('input[type="checkbox"]');
                    checkboxes.forEach(function(cb) {
                        cb.checked = allianceIds.includes(cb.value);
                    });
                }

                // Ẩn phần chọn liên quân trong modal nếu đã chọn từ ngoài
                var allianceWrapper = document.getElementById('alliance_checkboxes_wrapper');
                if (allianceWrapper && allianceIds.length > 0) {
                    allianceWrapper.style.display = 'none';
                }

                // Mở modal
                var modal = new bootstrap.Modal(document.getElementById('addDetailModal'));
                modal.show();

                // Load attendees
                loadSportAttendees();

                updateSportTeamName(sportName);
            });
        }
    }

    // Reset sport modal khi đóng
    function resetSportModalUI() {
        var modalSportSelect = document.getElementById('sport_item_id');
        var sportNameDiv = document.getElementById('sport_selected_name');
        var allianceWrapper = document.getElementById('alliance_checkboxes_wrapper');
        var teamNameInput = document.getElementById('sport_team_name');

        if (modalSportSelect) {
            modalSportSelect.classList.remove('d-none');
            modalSportSelect.value = '';
        }
        if (sportNameDiv) {
            sportNameDiv.classList.add('d-none');
            sportNameDiv.textContent = '';
        }
        if (allianceWrapper) {
            allianceWrapper.style.display = '';
        }
        if (teamNameInput) {
            teamNameInput.value = '';
            teamNameInput.readOnly = true;
            teamNameInput.classList.remove('bg-light');
        }

        // Reset alliance checkbox
        var isAllianceCheckbox = document.getElementById('sport_is_alliance');
        var sportAllianceWrapper = document.getElementById('sport_alliance_wrapper');
        if (isAllianceCheckbox) {
            isAllianceCheckbox.checked = false;
            isAllianceCheckbox.disabled = false;
        }
        if (sportAllianceWrapper) {
            sportAllianceWrapper.classList.add('d-none');
        }

        // Reset button text and editing state
        var btnAdd = document.getElementById('btn_add_to_preview');
        if (btnAdd) {
            btnAdd.innerHTML = '<i class="fa fa-plus me-1"></i>Thêm vào danh sách';
        }
        editingSportIndex = -1;
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

        if (compSelect) {
            compSelect.addEventListener('change', function() {
                var competitionId = this.value;
                if (competitionId) {
                    loadCompetitionInfo(competitionId);
                    loadAttendeesForCompetition();
                } else {
                    document.getElementById('comp_max_per_org').value = '-';
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
                e.preventDefault();

                if (selectedStaff.length === 0) {
                    Toast.error('Vui lòng chọn ít nhất một nhân viên.');
                    return false;
                }

                var submitBtn = document.getElementById('btn_submit_competition');
                var originalHtml = submitBtn.innerHTML;

                // Hiển thị loading, vô hiệu hoá nút để tránh bấm nhiều lần
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i>Đang đăng ký...';

                var formData = new FormData(form);

                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalHtml;

                    if (data.success) {
                        // Đóng modal
                        var modalEl = document.getElementById('addCompetitionModal');
                        if (modalEl) {
                            var modal = bootstrap.Modal.getInstance(modalEl);
                            if (modal) { modal.hide(); }
                        }

                        Toast.success(data.message || 'Đăng ký thi nghiệp vụ thành công!');

                        // Append row mới vào bảng
                        appendCompetitionRow(data);

                        // Reset form
                        resetCompetitionModal();
                    } else {
                        Toast.error(data.error || 'Có lỗi xảy ra khi đăng ký.');
                    }
                })
                .catch(function(err) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalHtml;
                    Toast.error('Lỗi kết nối. Vui lòng thử lại.');
                });
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
        // Không cần propertyId nữa, load attendees từ registration
        loadAttendeesForCompetition();
    }

    function loadAttendeesForCompetition() {
        document.getElementById('staff_placeholder').style.display = 'none';
        document.getElementById('dual_listbox_wrapper').style.display = 'flex';

        var availableList = document.getElementById('available_staff_list');
        availableList.innerHTML = '<div class="text-center p-3"><i class="fa fa-spinner fa-spin"></i> Đang tải...</div>';

        var competitionId = document.getElementById('comp_competition_id')?.value || '';
        var url = window.BASE_URL + '/admin/registrations/getAttendeesForCompetition?registration_id=' + registrationId;
        if (competitionId) {
            url += '&competition_id=' + competitionId;
        }
        fetch(url)
            .then(function(response) { return response.json(); })
            .then(function(data) {
                allStaff = data.success && data.data ? data.data : [];
                selectedStaff = [];
                renderAvailableStaff();
                renderSelectedStaff();
            })
            .catch(function(err) {
                console.error('Load attendees error:', err);
                availableList.innerHTML = '<div class="text-center text-danger p-3">Lỗi tải dữ liệu</div>';
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
                if (!this.classList.contains('active')) {
                    var activeCount = list.querySelectorAll('.active').length;
                    if (maxPerOrg > 0 && (selectedStaff.length + activeCount >= maxPerOrg)) {
                        Toast.error('Chỉ được chọn tối đa ' + maxPerOrg + ' nhân viên');
                        return;
                    }
                }
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

    function appendCompetitionRow(data) {
        var card = document.getElementById('competition-registration-card');
        if (!card) return;

        var cardBody = card.querySelector('.card-body');
        // Tìm table trong col chính (tránh lấy nhầm table trong alliance sidebar)
        var mainCol = cardBody.querySelector('.col-12, .col-md-9');
        var table = mainCol ? mainCol.querySelector('table#competition-list-table') : cardBody.querySelector('table#competition-list-table');
        var emptyMsg = mainCol ? mainCol.querySelector('p.text-muted') : cardBody.querySelector('p.text-muted');

        // Tạo danh sách thí sinh theo dòng
        var listHtml = '';
        if (data.attendees && data.attendees.length > 0) {
            data.attendees.forEach(function(att, idx) {
                var name = att.attendee_name || att.name || '';
                var position = att.position_name || '';
                var division = att.division_name || '';
                var info = escapeHtml(name);
                if (position || division) {
                    var extra = (position && division) ? (position + ' - ' + division) : (position || division);
                    info += ' <small class="text-muted">(' + escapeHtml(extra) + ')</small>';
                }
                listHtml += '<div>' + (idx + 1) + '. ' + info + '</div>';
            });
        }

        // Tính STT mới
        var newStt = 1;
        if (table) {
            var existingRows = table.querySelectorAll('tbody tr');
            newStt = existingRows.length + 1;
        }

        // Tạo row HTML với nút sửa/xóa
        var actionsHtml = '<td class="text-center">' +
            '<button type="button" class="btn btn-sm btn-outline-primary me-1" onclick="RegistrationView.editCompetitionRegistration(' + data.competitionId + ', \'' + escapeHtml(data.competitionName).replace(/'/g, "\\'") + '\')" title="Sửa">' +
                '<i class="fa fa-pencil"></i>' +
            '</button>' +
            '<button type="button" class="btn btn-sm btn-outline-danger" onclick="RegistrationView.deleteCompetitionRegistration(' + data.competitionId + ')" title="Xóa">' +
                '<i class="fa fa-trash"></i>' +
            '</button>' +
        '</td>';

        var rowHtml = '<tr data-competition-id="' + data.competitionId + '">' +
            '<td class="text-center">' + newStt + '</td>' +
            '<td>' + escapeHtml(data.competitionName) + '</td>' +
            '<td class="text-center">' + (data.attendees ? data.attendees.length : 0) + '</td>' +
            '<td>' + listHtml + '</td>' +
            actionsHtml +
        '</tr>';

        if (!table) {
            // Tạo table mới nếu chưa có
            var tableWrapper = document.createElement('div');
            tableWrapper.className = 'table-responsive';
            tableWrapper.innerHTML = '<table class="table table-bordered table-striped table-sm mb-0 content-table" id="competition-list-table">' +
                '<thead class="table-light"><tr>' +
                    '<th class="col-stt text-center">STT</th>' +
                    '<th class="col-name">Cuộc thi</th>' +
                    '<th class="col-count text-center">Số người</th>' +
                    '<th class="col-list">Danh sách thí sinh</th>' +
                    '<th class="col-action text-center">Thao tác</th>' +
                '</tr></thead>' +
                '<tbody>' + rowHtml + '</tbody>' +
            '</table>';

            if (emptyMsg) emptyMsg.remove();
            if (mainCol) {
                mainCol.appendChild(tableWrapper);
            } else {
                cardBody.appendChild(tableWrapper);
            }
        } else {
            // Append vào tbody
            var tbody = table.querySelector('tbody');
            if (tbody) {
                tbody.insertAdjacentHTML('beforeend', rowHtml);
            }
        }
    }

    function addSelectedStaff() {
        var list = document.getElementById('available_staff_list');
        var actives = list.querySelectorAll('.active');
        var limitExceeded = false;

        actives.forEach(function(el) {
            var id = el.getAttribute('data-id');
            var staff = allStaff.find(function(s) { return s.id == id; });
            if (staff) {
                if (canAddMore()) {
                    selectedStaff.push(staff);
                } else {
                    limitExceeded = true;
                }
            }
            el.classList.remove('active');
        });

        if (limitExceeded) {
            Toast.error('Chỉ được chọn tối đa ' + maxPerOrg + ' nhân viên');
        }

        renderAvailableStaff();
        renderSelectedStaff();
    }

    function addAllStaff() {
        var available = allStaff.filter(function(s) {
            return selectedStaff.findIndex(function(sel) { return sel.id == s.id; }) === -1;
        });
        var limitExceeded = false;

        available.forEach(function(staff) {
            if (canAddMore()) {
                selectedStaff.push(staff);
            } else {
                limitExceeded = true;
            }
        });

        if (limitExceeded) {
            Toast.error('Chỉ được chọn tối đa ' + maxPerOrg + ' nhân viên');
        }

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

    // Cache danh sách approved alliances
    var approvedAlliances = [];

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

        // Thêm vào danh sách preview hoặc cập nhật đội
        document.getElementById('btn_add_to_preview')?.addEventListener('click', function() {
            if (editingTeamId) {
                updateSportTeam();
            } else {
                addSportToPreview();
            }
        });

        // Lưu tất cả
        document.getElementById('btn_save_all_sports')?.addEventListener('click', saveAllSportRegistrations);

        // Reset khi đóng modal
        var modalEl = document.getElementById('addDetailModal');
        if (modalEl) {
            modalEl.addEventListener('hidden.bs.modal', function() {
                resetSportModalUI();
                editingTeamId = null;
                var btnAdd = document.getElementById('btn_add_to_preview');
                if (btnAdd) {
                    btnAdd.innerHTML = '<i class="fa fa-plus me-1"></i>Thêm vào danh sách';
                }
            });
        }

        // Bind checkbox "Có liên quân"
        bindAllianceCheckbox();
    }

    // Bind sự kiện cho checkbox "Có liên quân"
    function bindAllianceCheckbox() {
        var isAllianceCheckbox = document.getElementById('sport_is_alliance');
        var sportAllianceWrapper = document.getElementById('sport_alliance_wrapper');

        if (isAllianceCheckbox) {
            isAllianceCheckbox.addEventListener('change', function() {
                if (this.checked) {
                    sportAllianceWrapper.classList.remove('d-none');
                    loadApprovedAlliances();
                } else {
                    sportAllianceWrapper.classList.add('d-none');
                    // Reset team name về mặc định
                    updateSportTeamNameFromAlliance();
                }
            });
        }
    }

    // Load danh sách đơn vị liên quân đã được approved
    function loadApprovedAlliances() {
        var allianceList = document.getElementById('sport_alliance_list');
        if (!allianceList) return;

        allianceList.innerHTML = '<div class="text-center py-2"><i class="fa fa-spinner fa-spin"></i> Đang tải...</div>';

        fetch(window.BASE_URL + '/admin/registrations/getApprovedAlliances?registration_id=' + registrationId)
            .then(function(response) { return response.json(); })
            .then(function(data) {
                approvedAlliances = data.success && data.data ? data.data : [];

                if (approvedAlliances.length === 0) {
                    allianceList.innerHTML = '<div class="text-muted small py-2">Chưa có đơn vị liên quân nào được xác nhận. Vui lòng gửi yêu cầu liên quân trước.</div>';
                    return;
                }

                var html = '';
                approvedAlliances.forEach(function(item) {
                    html += '<div class="form-check">' +
                        '<input class="form-check-input sport-alliance-cb" type="checkbox" value="' + item.id + '" ' +
                        'data-code="' + escapeHtml(item.code) + '" data-name="' + escapeHtml(item.name) + '" ' +
                        'id="sport_alliance_' + item.id + '">' +
                        '<label class="form-check-label small" for="sport_alliance_' + item.id + '">' +
                        escapeHtml(item.code + ' - ' + item.name) + '</label></div>';
                });
                allianceList.innerHTML = html;

                // Bind change event để update team name và check existing team
                var checkboxes = allianceList.querySelectorAll('.sport-alliance-cb');
                checkboxes.forEach(function(cb) {
                    cb.addEventListener('change', function() {
                        updateSportTeamNameFromAlliance();
                        checkExistingAllianceTeam();
                    });
                });
            })
            .catch(function() {
                allianceList.innerHTML = '<div class="text-danger small py-2">Lỗi tải dữ liệu liên quân.</div>';
            });
    }

    // Cập nhật tên đội dựa trên các đơn vị liên quân đã chọn
    function updateSportTeamNameFromAlliance() {
        var teamNameInput = document.getElementById('sport_team_name');
        if (!teamNameInput) return;

        var isAllianceCheckbox = document.getElementById('sport_is_alliance');
        var isAlliance = isAllianceCheckbox && isAllianceCheckbox.checked;

        if (!isAlliance) {
            // Team độc lập - tên theo property code
            teamNameInput.value = propertyCode || 'Team';
            return;
        }

        // Lấy danh sách alliance đã chọn
        var checkboxes = document.querySelectorAll('.sport-alliance-cb:checked');
        var allianceCodes = [];
        checkboxes.forEach(function(cb) {
            var code = cb.getAttribute('data-code');
            if (code) allianceCodes.push(code);
        });

        if (allianceCodes.length > 0) {
            // Tên đội: Liên quân [Code đơn vị hiện tại] - [Code đơn vị liên quân 1] - ...
            var allCodes = [propertyCode].concat(allianceCodes);
            teamNameInput.value = 'Liên quân ' + allCodes.join(' - ');
        } else {
            teamNameInput.value = propertyCode || 'Team';
        }
    }

    // Lấy danh sách alliance_property_ids đã chọn từ form
    function getSelectedAlliancePropertyIds() {
        var isAllianceCheckbox = document.getElementById('sport_is_alliance');
        if (!isAllianceCheckbox || !isAllianceCheckbox.checked) {
            return [];
        }

        var checkboxes = document.querySelectorAll('.sport-alliance-cb:checked');
        var ids = [];
        checkboxes.forEach(function(cb) {
            ids.push(cb.value);
        });
        return ids;
    }

    // Kiểm tra xem đơn vị liên quân đã có team cho môn này chưa
    function checkExistingAllianceTeam() {
        var sportSelect = document.getElementById('sport_select_main');
        var modalSportSelect = document.getElementById('sport_item_id');
        var sportId = modalSportSelect && modalSportSelect.value
            ? modalSportSelect.value
            : (sportSelect ? sportSelect.value : '');

        var allianceIds = getSelectedAlliancePropertyIds();
        var hintEl = document.getElementById('sport_team_name_hint');

        if (!sportId || allianceIds.length === 0) {
            if (hintEl) {
                hintEl.innerHTML = '<span class="text-muted">Tên đội sẽ tự động sinh theo đơn vị liên quân.</span>';
                hintEl.classList.remove('text-info', 'text-warning');
            }
            return;
        }

        var url = window.BASE_URL + '/admin/registrations/checkAllianceTeam?registration_id=' + registrationId +
            '&sport_id=' + sportId + '&alliance_property_ids=' + allianceIds.join(',');

        fetch(url)
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (hintEl && data.success) {
                    if (data.has_existing_team) {
                        hintEl.innerHTML = '<i class="fa fa-info-circle"></i> ' + escapeHtml(data.message);
                        hintEl.classList.remove('text-muted');
                        hintEl.classList.add('text-info');
                    } else {
                        hintEl.innerHTML = '<span class="text-muted">' + escapeHtml(data.message) + '</span>';
                        hintEl.classList.remove('text-info', 'text-warning');
                    }
                }
            })
            .catch(function() {
                if (hintEl) {
                    hintEl.innerHTML = '<span class="text-muted">Tên đội sẽ tự động sinh theo đơn vị liên quân.</span>';
                }
            });
    }

    function addSportToPreview() {
        var sportSelect = document.getElementById('sport_select_main');
        var modalSportSelect = document.getElementById('sport_item_id');
        var sportId = modalSportSelect ? modalSportSelect.value : (sportSelect ? sportSelect.value : '');
        var sportName = '';

        if (modalSportSelect && modalSportSelect.value) {
            var opt = modalSportSelect.querySelector('option[value="' + modalSportSelect.value + '"]');
            sportName = opt ? opt.textContent.trim() : '';
        } else if (sportSelect && sportSelect.selectedIndex > 0) {
            sportName = sportSelect.options[sportSelect.selectedIndex].text.trim();
        }

        if (!sportId) {
            Toast.error('Vui lòng chọn môn thể thao.');
            return;
        }
        if (sportSelectedAttendees.length === 0) {
            Toast.error('Vui lòng chọn ít nhất một người tham dự.');
            return;
        }

        // Validate số người >= min_members
        var minPlayers = getSportMinMembersById(sportId);
        if (sportSelectedAttendees.length < minPlayers) {
            Toast.error('Môn "' + sportName + '" yêu cầu chọn ít nhất ' + minPlayers + ' người.');
            return;
        }

        var maxPlayers = getSportMaxPlayers(sportName, sportId);
        if (sportSelectedAttendees.length > maxPlayers) {
            Toast.error('Môn "' + sportName + '" tối đa chỉ cho phép chọn ' + maxPlayers + ' người.');
            return;
        }

        // AJAX check sport limits on the backend first!
        var btnAdd = document.getElementById('btn_add_to_preview');
        var originalHtml = btnAdd.innerHTML;
        btnAdd.disabled = true;
        btnAdd.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i>Đang kiểm tra...';

        var formData = new FormData();
        formData.append('sport_id', sportId);
        sportSelectedAttendees.forEach(function(att) {
            formData.append('attendee_ids[]', att.id);
        });

        fetch(window.BASE_URL + '/admin/registrations/checkSportAttendeesLimit', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            btnAdd.disabled = false;
            btnAdd.innerHTML = originalHtml;

            if (!data.success) {
                Toast.error(data.error);
                return;
            }

            // Proceed with adding to preview
            // Get alliance info từ checkbox mới trong modal
            var isAllianceCheckbox = document.getElementById('sport_is_alliance');
            var isAlliance = isAllianceCheckbox && isAllianceCheckbox.checked;
            var allianceIds = [];
            var allianceCodes = [];

            if (isAlliance) {
                var checkboxes = document.querySelectorAll('.sport-alliance-cb:checked');
                checkboxes.forEach(function(cb) {
                    allianceIds.push(cb.value);
                    allianceCodes.push(cb.getAttribute('data-code'));
                });
            }

            // Lấy tên đội từ input (đã được tự động sinh)
            var teamName = document.getElementById('sport_team_name')?.value || '';
            if (!teamName) {
                if (allianceCodes.length > 0) {
                    var allCodes = [propertyCode].concat(allianceCodes);
                    teamName = 'Liên quân ' + allCodes.join(' - ');
                } else {
                    teamName = propertyCode || 'Team';
                }
            }

            var regData = {
                sportId: sportId,
                sportName: sportName,
                teamName: teamName,
                isAlliance: isAlliance ? 1 : 0,
                allianceIds: allianceIds,
                allianceCodes: allianceCodes,
                attendees: sportSelectedAttendees.slice() // clone array
            };

            if (editingSportIndex >= 0) {
                // Update existing
                pendingSportRegistrations[editingSportIndex] = regData;
                Toast.success('Đã cập nhật "' + sportName + '".');
            } else {
                // Add new
                pendingSportRegistrations.push(regData);
                Toast.success('Đã thêm "' + sportName + '" vào danh sách.');
            }

            // Reset editing state
            editingSportIndex = -1;

            // Close modal and render preview
            var modalEl = document.getElementById('addDetailModal');
            if (modalEl) {
                var modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
            }

            renderSportPreview();

            // Reset dropdown
            if (sportSelect) sportSelect.value = '';
            document.getElementById('btn_open_sport_modal').disabled = true;
        })
        .catch(function(err) {
            btnAdd.disabled = false;
            btnAdd.innerHTML = originalHtml;
            Toast.error('Có lỗi xảy ra khi kiểm tra giới hạn.');
        });
    }

    function editPendingSport(idx) {
        if (idx < 0 || idx >= pendingSportRegistrations.length) return;

        var reg = pendingSportRegistrations[idx];
        editingSportIndex = idx;

        // Set sport in main dropdown
        var sportSelect = document.getElementById('sport_select_main');
        if (sportSelect) {
            sportSelect.value = reg.sportId;
        }

        // Set modal sport select
        var modalSportSelect = document.getElementById('sport_item_id');
        var sportNameDiv = document.getElementById('sport_selected_name');
        if (modalSportSelect) {
            modalSportSelect.value = reg.sportId;
            modalSportSelect.classList.add('d-none');
        }
        if (sportNameDiv) {
            sportNameDiv.textContent = reg.sportName;
            sportNameDiv.classList.remove('d-none');
        }

        // Set alliance
        var allianceSelect = document.getElementById('sport_alliance_property');
        if (allianceSelect) {
            Array.from(allianceSelect.options).forEach(function(opt) {
                opt.selected = reg.allianceIds.includes(opt.value);
            });
        }

        // Set team name
        var teamNameInput = document.getElementById('sport_team_name');
        if (teamNameInput) {
            teamNameInput.value = reg.teamName;
        }

        // Load attendees then pre-select
        loadSportAttendees(function() {
            // Pre-select attendees
            sportSelectedAttendees = reg.attendees.slice();
            renderSportAvailableAttendees();
            renderSportSelectedAttendees();
        });

        // Change button text
        var btnAdd = document.getElementById('btn_add_to_preview');
        if (btnAdd) {
            btnAdd.innerHTML = '<i class="fa fa-save me-1"></i>Cập nhật';
        }

        // Open modal
        var modal = new bootstrap.Modal(document.getElementById('addDetailModal'));
        modal.show();
    }

    function renderSportPreview() {
        var container = document.getElementById('sport_preview_container');
        var listEl = document.getElementById('sport_preview_list');
        var noMsg = document.getElementById('no_sport_msg');

        if (!container || !listEl) return;

        if (pendingSportRegistrations.length === 0) {
            container.style.display = 'none';
            return;
        }

        container.style.display = 'block';
        if (noMsg) noMsg.style.display = 'none';

        var html = '<table class="table table-bordered table-striped table-sm mb-0">' +
            '<thead class="table-warning"><tr>' +
            '<th>Môn thi đấu</th>' +
            '<th>Tên đội</th>' +
            '<th style="width:150px;">Liên quân</th>' +
            '<th style="width:100px;">Số VĐV</th>' +
            '<th>Danh sách VĐV</th>' +
            '<th style="width:60px;"></th>' +
            '</tr></thead><tbody>';

        pendingSportRegistrations.forEach(function(reg, idx) {
            html += '<tr>' +
                '<td>' + escapeHtml(reg.sportName) + '</td>' +
                '<td><span class="badge bg-warning text-dark">' + escapeHtml(reg.teamName) + '</span></td>' +
                '<td>' + (reg.allianceCodes.length > 0 ? escapeHtml(reg.allianceCodes.join(', ')) : '-') + '</td>' +
                '<td class="text-center">' + reg.attendees.length + '</td>' +
                '<td>';

            reg.attendees.forEach(function(att, i) {
                html += '<span class="badge bg-light text-dark border me-1 mb-1 d-inline-flex align-items-center">' +
                    (i + 1) + '. ' + escapeHtml(att.full_name) +
                    ' <i class="fa fa-times ms-1 text-danger" style="cursor:pointer;" onclick="RegistrationView.removeAthleteFromSport(' + idx + ',' + att.id + ')" title="Xóa VĐV"></i>' +
                    '</span>';
            });

            html += '</td>' +
                '<td class="text-center">' +
                '<button type="button" class="btn btn-sm btn-outline-primary me-1" onclick="RegistrationView.editPendingSport(' + idx + ')" title="Sửa">' +
                '<i class="fa fa-pencil"></i></button>' +
                '<button type="button" class="btn btn-sm btn-outline-danger" onclick="RegistrationView.removePendingSport(' + idx + ')" title="Xóa môn">' +
                '<i class="fa fa-trash"></i></button></td></tr>';
        });

        html += '</tbody></table>';
        listEl.innerHTML = html;
    }

    function removePendingSport(idx) {
        if (idx >= 0 && idx < pendingSportRegistrations.length) {
            var removed = pendingSportRegistrations.splice(idx, 1)[0];
            Toast.error('Đã xóa "' + removed.sportName + '" khỏi danh sách.');
            renderSportPreview();
        }
    }

    function removeAthleteFromSport(sportIdx, attendeeId) {
        if (sportIdx >= 0 && sportIdx < pendingSportRegistrations.length) {
            var reg = pendingSportRegistrations[sportIdx];
            var originalLength = reg.attendees.length;
            reg.attendees = reg.attendees.filter(function(att) { return att.id != attendeeId; });

            if (reg.attendees.length < originalLength) {
                Toast.info('Đã xóa VĐV khỏi danh sách.');

                // Nếu không còn VĐV nào, xóa luôn môn
                if (reg.attendees.length === 0) {
                    pendingSportRegistrations.splice(sportIdx, 1);
                    Toast.info('Môn "' + reg.sportName + '" đã bị xóa do không còn VĐV.');
                }

                renderSportPreview();
            }
        }
    }

    function saveAllSportRegistrations() {
        if (pendingSportRegistrations.length === 0) {
            Toast.error('Không có môn nào để lưu.');
            return;
        }

        var btn = document.getElementById('btn_save_all_sports');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i>Đang lưu...';

        var promises = pendingSportRegistrations.map(function(reg) {
            var formData = new FormData();
            formData.append('registration_id', registrationId);
            formData.append('content_type', 'sports');
            formData.append('content_id', sportsContentId || '');
            formData.append('sport_id', reg.sportId);
            formData.append('team_name', reg.teamName);
            formData.append('is_alliance', reg.isAlliance || 0);
            reg.allianceIds.forEach(function(id) {
                formData.append('alliance_property_ids[]', id);
            });
            reg.attendees.forEach(function(att) {
                formData.append('attendee_ids[]', att.id);
                formData.append('attendee_names[]', att.full_name || '');
            });

            return fetch(window.BASE_URL + '/admin/registrations/addSportRegistration', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }).then(function(response) { return response.json(); });
        });

        Promise.all(promises)
            .then(function(results) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa fa-save me-1"></i>Lưu tất cả đăng ký';

                var successCount = results.filter(function(r) { return r.success; }).length;
                var errors = results.filter(function(r) { return !r.success && r.error; }).map(function(r) { return r.error; });

                if (successCount === pendingSportRegistrations.length) {
                    Toast.success('Đã lưu thành công ' + successCount + ' môn thể thao.');
                    pendingSportRegistrations = [];
                    setTimeout(function() { location.reload(); }, 1000);
                } else if (errors.length > 0) {
                    Toast.error(errors.join('; '));
                } else {
                    Toast.warning('Lưu được ' + successCount + '/' + pendingSportRegistrations.length + ' môn.');
                    setTimeout(function() { location.reload(); }, 2000);
                }
            })
            .catch(function() {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa fa-save me-1"></i>Lưu tất cả đăng ký';
                Toast.error('Có lỗi xảy ra khi lưu.');
            });
    }

    function loadAllianceProperties() {
        var container = document.getElementById('alliance_checkboxes');
        if (!container) return;

        container.innerHTML = '<div class="text-muted small">Đang tải...</div>';

        var sportsCard = document.getElementById('sports-registration-card');
        var sportsEventContentId = sportsCard ? sportsCard.getAttribute('data-event-content-id') : '';

        fetch(window.BASE_URL + '/admin/registrations/getAllianceProperties?registration_id=' + registrationId + '&event_content_id=' + sportsEventContentId)
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

    function getSportMaxPlayers(sportName, sportId) {
        if (sportId) {
            var cachedMax = getSportMaxMembersById(sportId);
            if (cachedMax !== null && cachedMax !== undefined) {
                return cachedMax;
            }
        }
        if (!sportName) return Infinity;
        sportName = sportName.toLowerCase();
        
        if (sportName.includes('bóng đá') || sportName.includes('football') || sportName.includes('soccer')) {
            return 11;
        }
        if (sportName.includes('kéo co')) {
            return 10;
        }
        if (sportName.includes('bơi tiếp sức') || sportName.includes('bơi đồng đội')) {
            return 4;
        }
        if (sportName.includes('đôi') || sportName.includes('doubles')) {
            return 2;
        }
        if (sportName.includes('đơn') || sportName.includes('singles') || sportName.includes('cờ vua') || sportName.includes('cờ tướng') || sportName.includes('bản đồ')) {
            return 1;
        }
        
        if (sportName.includes('bóng bàn') || sportName.includes('cầu lông') || sportName.includes('tennis') || sportName.includes('quần vợt') || sportName.includes('pickleball') || sportName.includes('pickerball')) {
            return 2;
        }
        
        return Infinity;
    }

    function getSelectedSportName() {
        var sportSelect = document.getElementById('sport_select_main');
        var modalSportSelect = document.getElementById('sport_item_id');
        var sportName = '';
        if (modalSportSelect && modalSportSelect.value) {
            var opt = modalSportSelect.querySelector('option[value="' + modalSportSelect.value + '"]');
            sportName = opt ? opt.textContent.trim() : '';
        } else if (sportSelect && sportSelect.selectedIndex > 0) {
            sportName = sportSelect.options[sportSelect.selectedIndex].text.trim();
        }
        return sportName;
    }

    function getSelectedSportId() {
        var sportSelect = document.getElementById('sport_select_main');
        var modalSportSelect = document.getElementById('sport_item_id');
        if (modalSportSelect && modalSportSelect.value) {
            return modalSportSelect.value;
        }
        if (sportSelect && sportSelect.value) {
            return sportSelect.value;
        }
        return '';
    }

    function loadSportAttendees(callback) {
        var availableList = document.getElementById('sport_available_attendee_list');
        if (!availableList) return;

        availableList.innerHTML = '<div class="text-center p-3"><i class="fa fa-spinner fa-spin"></i> Đang tải...</div>';

        // Lấy sport_id đang chọn để loại trừ người đã đăng ký team của môn này
        var sportSelect = document.getElementById('sport_select_main');
        var modalSportSelect = document.getElementById('sport_item_id');
        var sportId = modalSportSelect && modalSportSelect.value
            ? modalSportSelect.value
            : (sportSelect ? sportSelect.value : '');

        var url = window.BASE_URL + '/admin/registrations/getSportAttendees?registration_id=' + registrationId;
        if (sportId) {
            url += '&sport_id=' + sportId;
        }

        // Load attendees with sports role from current registration only
        fetch(url)
            .then(function(response) { return response.json(); })
            .then(function(data) {
                sportAllAttendees = data.success && data.data ? data.data : [];
                sportSelectedAttendees = [];
                renderSportAvailableAttendees();
                renderSportSelectedAttendees();
                if (typeof callback === 'function') callback();
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
            var canRegister = att.can_register !== false;
            item.className = 'list-group-item list-group-item-action py-2' + (canRegister ? '' : ' disabled text-muted');
            item.setAttribute('data-id', att.id);
            item.setAttribute('data-can-register', canRegister ? '1' : '0');

            var subInfo = [];
            if (att.department_name) subInfo.push(att.department_name);
            if (att.property_name) subInfo.push(att.property_name);
            if (att.position) subInfo.push(att.position);

            var reasonHtml = '';
            if (!canRegister && att.reason) {
                reasonHtml = '<br><span class="text-danger" style="font-size:10px;"><i class="fa fa-exclamation-circle"></i> ' + escapeHtml(att.reason) + '</span>';
            }

            item.innerHTML = '<small>' + escapeHtml(att.full_name) + '</small>' +
                (subInfo.length ? '<br><span class="text-muted" style="font-size:11px;">' + escapeHtml(subInfo.join(' - ')) + '</span>' : '') +
                reasonHtml;

            if (canRegister) {
                item.addEventListener('click', function(e) {
                    e.preventDefault();
                    this.classList.toggle('active');
                });
            } else {
                item.style.pointerEvents = 'none';
                item.style.opacity = '0.6';
            }
            list.appendChild(item);
        });
    }

    function renderSportSelectedAttendees() {
        var list = document.getElementById('sport_selected_attendee_list');
        if (!list) return;

        var countSpan = document.getElementById('sport_selected_count');
        if (countSpan) countSpan.textContent = sportSelectedAttendees.length;

        var maxSpan = document.getElementById('sport_max_count');
        if (maxSpan) {
            var sportId = getSelectedSportId();
            var minPlayers = getSportMinMembersById(sportId);
            maxSpan.textContent = minPlayers;
        }

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
            var subInfo = [];
            if (att.department_name) subInfo.push(att.department_name);
            if (att.property_name) subInfo.push(att.property_name);
            if (att.position) subInfo.push(att.position);
            item.innerHTML = '<small>' + escapeHtml(att.full_name) + '</small>' +
                (subInfo.length ? '<br><span class="text-muted" style="font-size:11px;">' + escapeHtml(subInfo.join(' - ')) + '</span>' : '');
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
        if (actives.length === 0) return;

        var sportName = getSelectedSportName();
        var sportId = getSelectedSportId();
        var currentlySelected = sportSelectedAttendees.length;

        var invalidAttendees = [];
        var validAttendees = [];

        actives.forEach(function(el) {
            var id = el.getAttribute('data-id');
            var canRegister = el.getAttribute('data-can-register') === '1';
            var att = sportAllAttendees.find(function(a) { return a.id == id; });
            if (att) {
                if (!canRegister) {
                    invalidAttendees.push(att.full_name + ': ' + (att.reason || 'Không thể đăng ký'));
                    el.classList.remove('active');
                } else {
                    validAttendees.push(att);
                }
            }
        });

        if (invalidAttendees.length > 0) {
            Toast.error('Không thể chọn: ' + invalidAttendees.join('; '));
        }

        if (validAttendees.length === 0) return;

        // Cảnh báo nếu chọn vượt quá số lượng tối đa
        var maxPlayers = getSportMaxPlayers(sportName, sportId);
        if (currentlySelected + validAttendees.length > maxPlayers) {
            Toast.error('Môn "' + sportName + '" tối đa chỉ cho phép chọn ' + maxPlayers + ' người.');
            return;
        }

        validAttendees.forEach(function(att) {
            sportSelectedAttendees.push(att);
            var el = list.querySelector('[data-id="' + att.id + '"]');
            if (el) el.classList.remove('active');
        });

        renderSportAvailableAttendees();
        renderSportSelectedAttendees();
    }

    function addAllSportAttendees() {
        var available = sportAllAttendees.filter(function(a) {
            return sportSelectedAttendees.findIndex(function(sel) { return sel.id == a.id; }) === -1;
        });
        if (available.length === 0) return;

        var sportName = getSelectedSportName();
        var sportId = getSelectedSportId();
        var currentlySelected = sportSelectedAttendees.length;

        // Chỉ thêm những người có thể đăng ký
        var validAttendees = available.filter(function(att) {
            return att.can_register !== false;
        });

        if (validAttendees.length === 0) {
            Toast.error('Không có người nào có thể đăng ký môn này.');
            return;
        }

        // Cảnh báo nếu chọn vượt quá số lượng tối đa
        var maxPlayers = getSportMaxPlayers(sportName, sportId);
        if (currentlySelected + validAttendees.length > maxPlayers) {
            Toast.error('Môn "' + sportName + '" tối đa chỉ cho phép chọn ' + maxPlayers + ' người.');
            return;
        }

        validAttendees.forEach(function(att) {
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
        originalSportTeamAttendeeIds = [];
        removeSportHiddenInputs();

        // Reset UI elements
        resetSportModalUI();

        loadAlliancePropertiesDropdown();
        loadSportsList();
        loadSportAttendees();
    }

    function removeAllianceProperty(id) {
        Swal.fire({
            title: 'Xác nhận',
            text: 'Bạn có chắc muốn xóa đơn vị liên quân này?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Xóa',
            cancelButtonText: 'Hủy'
        }).then(function(result) {
            if (result.isConfirmed) {
                Swal.fire({
                    title: "Đang xử lý...",
                    text: "Vui lòng chờ trong giây lát.",
                    allowOutsideClick: false,
                    didOpen: function() {
                        Swal.showLoading();
                    }
                });
                doRemoveAllianceProperty(id);
            }
        });
    }

    function doRemoveAllianceProperty(id) {
        // Bỏ chọn checkbox trong modal
        var cb = document.getElementById('modal_alliance_' + id);
        if (cb) cb.checked = false;

        // Bỏ chọn option trong select
        var allianceSelect = document.getElementById('sport_alliance_property');
        if (allianceSelect) {
            Array.from(allianceSelect.options).forEach(function(opt) {
                if (opt.value == id) {
                    opt.selected = false;
                }
            });
        }

        // Xóa badge trực tiếp từ DOM
        var displayText = document.getElementById('alliance_selected_texts');
        if (displayText) {
            var badges = displayText.querySelectorAll('span.badge');
            badges.forEach(function(badge) {
                var closeIcon = badge.querySelector('i.fa-times');
                if (closeIcon) {
                    var onclickAttr = closeIcon.getAttribute('onclick') || '';
                    if (onclickAttr.indexOf("'" + id + "'") !== -1 || onclickAttr.indexOf('"' + id + '"') !== -1) {
                        badge.remove();
                    }
                }
            });
        }

        updateSportTeamName();

        // Lưu lại danh sách liên quân còn lại vào server
        var checkboxes = document.querySelectorAll('.alliance-modal-cb');
        var remainingIds = [];
        checkboxes.forEach(function(cbEl) {
            if (cbEl.checked) {
                remainingIds.push(cbEl.value);
            }
        });

        var sportsCard = document.getElementById('sports-registration-card');
        var sportsEventContentId = sportsCard ? sportsCard.getAttribute('data-event-content-id') : '';

        var formData = new FormData();
        formData.append('registration_id', registrationId);
        formData.append('event_content_id', sportsEventContentId);
        remainingIds.forEach(function(rid) {
            formData.append('target_org_ids[]', rid);
        });

        fetch(window.BASE_URL + '/admin/registrations/saveAllianceProperties', {
            method: 'POST',
            body: formData
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            Swal.close();
            if (data.success) {
                Toast.success('Đã xóa đơn vị liên quân.');
            } else {
                Toast.error(data.error || 'Có lỗi xảy ra.');
            }
        })
        .catch(function() {
            Swal.close();
            Toast.error('Lỗi kết nối.');
        });
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
            // Không check excludeIds - cho phép đăng ký nhiều đội cho cùng 1 môn
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
        var contentIdField = document.getElementById('comp_content_id');

        document.getElementById('add-competition-form').reset();
        compSelect.innerHTML = '<option value="">-- Đang tải... --</option>';
        var propSelect = document.getElementById('comp_property_id');
        if (propSelect) propSelect.innerHTML = '<option value="">-- Chọn cuộc thi trước --</option>';
        document.getElementById('comp_max_per_org').value = '-';

        if (competitionContentId && contentIdField) {
            contentIdField.value = competitionContentId;
        } else {
            contentsData.forEach(function(c) {
                if ((c.code === 'competition' || c.code === 'competitions') && contentIdField) {
                    contentIdField.value = c.id;
                    competitionContentId = c.id;
                }
            });
        }

        allStaff = [];
        selectedStaff = [];
        maxPerOrg = 0;
        hideDualListbox();
        removeHiddenInputs();

        fetch(window.BASE_URL + '/admin/registrations/getContentItems?event_id=' + eventId + '&content_type=competition&registration_id=' + registrationId)
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
                } else {
                    compSelect.innerHTML = '<option value="">-- Đã đăng ký đủ tất cả cuộc thi --</option>';
                }
            });
    }

    // Edit Competition Registration
    var editCompAllStaff = [];
    var editCompSelectedStaff = [];
    var editCompMaxPerOrg = 0;

    function editCompetitionRegistration(competitionId, competitionName) {
        document.getElementById('edit_comp_competition_id').value = competitionId;
        document.getElementById('edit_comp_name').textContent = competitionName;

        // Load competition info
        fetch(window.BASE_URL + '/admin/registrations/getCompetitionInfo?competition_id=' + competitionId)
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success && data.data) {
                    editCompMaxPerOrg = data.data.max_per_org || 0;
                    document.getElementById('edit_comp_max_per_org').value = editCompMaxPerOrg > 0 ? editCompMaxPerOrg : 'Không giới hạn';
                    document.getElementById('edit_max_count').textContent = editCompMaxPerOrg > 0 ? editCompMaxPerOrg : '∞';
                }
            });

        // Load attendees và đánh dấu đã chọn
        var url = window.BASE_URL + '/admin/registrations/getAttendeesForCompetition?registration_id=' + registrationId + '&competition_id=' + competitionId;
        fetch(url)
            .then(function(response) { return response.json(); })
            .then(function(data) {
                editCompAllStaff = data.success && data.data ? data.data : [];

                // Load danh sách đã đăng ký
                return fetch(window.BASE_URL + '/admin/registrations/getCompetitionRegisteredAttendees?registration_id=' + registrationId + '&competition_id=' + competitionId);
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                var registeredIds = [];
                if (data.success && data.data) {
                    data.data.forEach(function(item) {
                        registeredIds.push(parseInt(item.attendee_id));
                    });
                }

                // Phân loại available và selected
                editCompSelectedStaff = [];
                var availableStaff = [];
                editCompAllStaff.forEach(function(s) {
                    if (registeredIds.indexOf(parseInt(s.id)) !== -1) {
                        editCompSelectedStaff.push(s);
                    } else {
                        availableStaff.push(s);
                    }
                });
                editCompAllStaff = availableStaff;

                renderEditCompAvailableStaff();
                renderEditCompSelectedStaff();

                var modal = new bootstrap.Modal(document.getElementById('editCompetitionModal'));
                modal.show();
            });
    }

    function renderEditCompAvailableStaff() {
        var list = document.getElementById('edit_available_staff_list');
        var searchTerm = (document.getElementById('edit_staff_search')?.value || '').toLowerCase();

        var available = editCompAllStaff.filter(function(s) {
            return editCompSelectedStaff.findIndex(function(sel) { return sel.id == s.id; }) === -1;
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
                if (!this.classList.contains('active')) {
                    var activeCount = list.querySelectorAll('.active').length;
                    if (editCompMaxPerOrg > 0 && (editCompSelectedStaff.length + activeCount >= editCompMaxPerOrg)) {
                        Toast.error('Chỉ được chọn tối đa ' + editCompMaxPerOrg + ' nhân viên');
                        return;
                    }
                }
                this.classList.toggle('active');
            });
            list.appendChild(item);
        });
    }

    function renderEditCompSelectedStaff() {
        var list = document.getElementById('edit_selected_staff_list');
        document.getElementById('edit_selected_count').textContent = editCompSelectedStaff.length;

        // Update hidden inputs
        var form = document.getElementById('edit-competition-form');
        form.querySelectorAll('input[name="staff_ids[]"]').forEach(function(el) { el.remove(); });
        editCompSelectedStaff.forEach(function(staff) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'staff_ids[]';
            input.value = staff.id;
            form.appendChild(input);
        });

        if (editCompSelectedStaff.length === 0) {
            list.innerHTML = '<div class="text-center text-muted p-3">Chưa chọn nhân viên</div>';
            return;
        }

        list.innerHTML = '';
        editCompSelectedStaff.forEach(function(staff) {
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

    function bindEditCompetitionEvents() {
        document.getElementById('edit_staff_search')?.addEventListener('input', function() {
            renderEditCompAvailableStaff();
        });

        document.getElementById('edit_btn_add_staff')?.addEventListener('click', function() {
            var selected = document.querySelectorAll('#edit_available_staff_list .active');
            var limitExceeded = false;
            selected.forEach(function(el) {
                var id = el.getAttribute('data-id');
                var staff = editCompAllStaff.find(function(s) { return s.id == id; });
                if (staff && editCompSelectedStaff.findIndex(function(s) { return s.id == id; }) === -1) {
                    if (editCompMaxPerOrg > 0 && editCompSelectedStaff.length >= editCompMaxPerOrg) {
                        limitExceeded = true;
                        return;
                    }
                    editCompSelectedStaff.push(staff);
                }
            });

            if (limitExceeded) {
                Toast.error('Chỉ được chọn tối đa ' + editCompMaxPerOrg + ' nhân viên');
            }

            renderEditCompAvailableStaff();
            renderEditCompSelectedStaff();
        });

        document.getElementById('edit_btn_add_all_staff')?.addEventListener('click', function() {
            var limitExceeded = false;
            editCompAllStaff.forEach(function(staff) {
                if (editCompSelectedStaff.findIndex(function(s) { return s.id == staff.id; }) === -1) {
                    if (editCompMaxPerOrg > 0 && editCompSelectedStaff.length >= editCompMaxPerOrg) {
                        limitExceeded = true;
                        return;
                    }
                    editCompSelectedStaff.push(staff);
                }
            });

            if (limitExceeded) {
                Toast.error('Chỉ được chọn tối đa ' + editCompMaxPerOrg + ' nhân viên');
            }

            renderEditCompAvailableStaff();
            renderEditCompSelectedStaff();
        });

        document.getElementById('edit_btn_remove_staff')?.addEventListener('click', function() {
            var selected = document.querySelectorAll('#edit_selected_staff_list .active');
            selected.forEach(function(el) {
                var id = el.getAttribute('data-id');
                editCompSelectedStaff = editCompSelectedStaff.filter(function(s) { return s.id != id; });
                var staff = editCompAllStaff.find(function(s) { return s.id == id; });
                if (!staff) {
                    // Add back to available
                    var originalStaff = editCompSelectedStaff.concat(editCompAllStaff).find(function(s) { return s.id == id; });
                    if (originalStaff) editCompAllStaff.push(originalStaff);
                }
            });
            renderEditCompAvailableStaff();
            renderEditCompSelectedStaff();
        });

        document.getElementById('edit_btn_remove_all_staff')?.addEventListener('click', function() {
            editCompAllStaff = editCompAllStaff.concat(editCompSelectedStaff);
            editCompSelectedStaff = [];
            renderEditCompAvailableStaff();
            renderEditCompSelectedStaff();
        });

        // Form submit
        var form = document.getElementById('edit-competition-form');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                if (editCompSelectedStaff.length === 0) {
                    Toast.error('Vui lòng chọn ít nhất một nhân viên.');
                    return false;
                }

                var submitBtn = document.getElementById('btn_submit_edit_competition');
                var originalHtml = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i>Đang cập nhật...';

                var formData = new FormData(form);

                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalHtml;

                    if (data.success) {
                        var modalEl = document.getElementById('editCompetitionModal');
                        if (modalEl) {
                            var modal = bootstrap.Modal.getInstance(modalEl);
                            if (modal) modal.hide();
                        }
                        Toast.success(data.message || 'Cập nhật thành công!');
                        location.reload();
                    } else {
                        Toast.error(data.error || 'Có lỗi xảy ra.');
                    }
                })
                .catch(function(err) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalHtml;
                    Toast.error('Lỗi kết nối. Vui lòng thử lại.');
                });
            });
        }
    }

    function confirmDeleteTeam(id) {
        Swal.fire({
            title: 'Xóa đội thể thao?',
            text: "Toàn bộ thành viên và thông tin đội sẽ bị xóa. Thao tác này không thể hoàn tác!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Có, xóa ngay!',
            cancelButtonText: 'Hủy'
        }).then(function(result) {
            if (result.isConfirmed) {
                Swal.fire({
                    title: "Đang xử lý...",
                    text: "Vui lòng chờ trong giây lát.",
                    allowOutsideClick: false,
                    didOpen: function() {
                        Swal.showLoading();
                    }
                });
                var form = document.getElementById('delete-team-form-' + id);
                if (form) form.submit();
            }
        });
    }

    function confirmDeleteTeamMember(memberId, teamId) {
        Swal.fire({
            title: 'Xóa VĐV khỏi đội?',
            text: "VĐV này sẽ bị xóa khỏi đội thi đấu. Bạn có chắc chắn?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Có, xóa ngay!',
            cancelButtonText: 'Hủy'
        }).then(function(result) {
            if (result.isConfirmed) {
                Swal.fire({
                    title: "Đang xử lý...",
                    text: "Vui lòng chờ trong giây lát.",
                    allowOutsideClick: false,
                    didOpen: function() {
                        Swal.showLoading();
                    }
                });
                fetch(window.BASE_URL + '/admin/registrations/deleteTeamMember', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: 'member_id=' + memberId + '&team_id=' + teamId + '&registration_id=' + registrationId
                })
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    Swal.close();
                    if (data.success) {
                        Toast.success(data.message || 'Đã xóa VĐV khỏi đội.');
                        location.reload();
                    } else {
                        Toast.error(data.error || 'Không thể xóa VĐV.');
                    }
                })
                .catch(function(err) {
                    Swal.close();
                    Toast.error('Lỗi kết nối. Vui lòng thử lại.');
                });
            }
        });
    }

    var editingTeamId = null;
    var editingTeamMaxMembers = null;
    var editingTeamAllianceMemberCount = 0;

    function editSportTeam(teamId) {
        editingTeamId = teamId;
        editingTeamMaxMembers = null;
        editingTeamAllianceMemberCount = 0;

        fetch(window.BASE_URL + '/admin/registrations/getSportTeamDetail?id=' + teamId + '&registration_id=' + registrationId, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (!data.success) {
                Toast.error(data.error || 'Không thể tải thông tin đội.');
                return;
            }

            var team = data.data.team;
            var members = data.data.members || [];

            // Lưu thông tin giới hạn số người
            editingTeamMaxMembers = team.max_per_team_member || null;
            editingTeamAllianceMemberCount = team.alliance_member_count || 0;

            // Set sport (readonly)
            var modalSportSelect = document.getElementById('sport_item_id');
            var sportNameDiv = document.getElementById('sport_selected_name');
            if (modalSportSelect && sportNameDiv) {
                modalSportSelect.value = team.sport_id;
                modalSportSelect.classList.add('d-none');
                sportNameDiv.textContent = team.sport_name || '';
                sportNameDiv.classList.remove('d-none');
            }

            // Set team name
            var teamNameInput = document.getElementById('sport_team_name');
            if (teamNameInput) {
                teamNameInput.value = team.team_name || team.name || '';
                teamNameInput.readOnly = true;
                teamNameInput.classList.add('bg-light');
            }

            // Set alliance checkbox và hiển thị đơn vị liên quân
            var allianceCheckbox = document.getElementById('sport_is_alliance');
            var allianceWrapper = document.getElementById('sport_alliance_wrapper');
            var allianceList = document.getElementById('sport_alliance_list');
            var isAlliance = team.is_alliance || (team.alliance_properties && team.alliance_properties.length > 0);

            if (allianceCheckbox) {
                allianceCheckbox.checked = isAlliance;
                allianceCheckbox.disabled = true; // Không cho sửa khi edit
            }

            if (allianceWrapper && allianceList) {
                if (isAlliance && team.alliance_properties && team.alliance_properties.length > 0) {
                    allianceWrapper.classList.remove('d-none');
                    var html = '';
                    team.alliance_properties.forEach(function(prop) {
                        html += '<div class="form-check">';
                        html += '<input type="checkbox" class="form-check-input" checked disabled>';
                        html += '<label class="form-check-label">' + prop + '</label>';
                        html += '</div>';
                    });
                    allianceList.innerHTML = html;
                } else {
                    allianceWrapper.classList.add('d-none');
                }
            }

            // Load attendees then pre-select members (chỉ lấy những thành viên thuộc đơn vị mình)
            loadSportAttendees(function() {
                sportSelectedAttendees = [];
                originalSportTeamAttendeeIds = [];
                members.forEach(function(m) {
                    if (m.is_own_member) {
                        var att = sportAllAttendees.find(function(a) { return a.id == m.attendee_id; });
                        if (att) {
                            originalSportTeamAttendeeIds.push(m.attendee_id);
                            sportSelectedAttendees.push(att);
                        }
                    }
                });
                renderSportAvailableAttendees();
                renderSportSelectedAttendees();
            });

            // Change button text
            var btnAdd = document.getElementById('btn_add_to_preview');
            if (btnAdd) {
                btnAdd.innerHTML = '<i class="fa fa-save me-1"></i>Cập nhật đội';
            }

            // Open modal
            var modal = new bootstrap.Modal(document.getElementById('addDetailModal'));
            modal.show();
        })
        .catch(function(err) {
            Toast.error('Lỗi kết nối server.');
        });
    }

    function updateSportTeam() {
        if (!editingTeamId) return;

        if (sportSelectedAttendees.length === 0) {
            Toast.error('Vui lòng chọn ít nhất một người tham dự.');
            return;
        }

        var sportId = getSelectedSportId();
        var sportName = getSelectedSportName();
        var minPlayers = getSportMinMembersById(sportId);
        if (sportSelectedAttendees.length < minPlayers) {
            Toast.error('Môn "' + sportName + '" yêu cầu chọn ít nhất ' + minPlayers + ' người.');
            return;
        }

        // Sử dụng max từ API nếu có, tính cả thành viên liên quân từ đơn vị khác
        var maxPlayers = editingTeamMaxMembers || getSportMaxPlayers(sportName, sportId);
        var availableSlots = maxPlayers - editingTeamAllianceMemberCount;
        if (sportSelectedAttendees.length > availableSlots) {
            var msg = 'Môn "' + sportName + '" tối đa chỉ cho phép ' + maxPlayers + ' người/đội.';
            if (editingTeamAllianceMemberCount > 0) {
                msg += ' Đội liên quân hiện có ' + editingTeamAllianceMemberCount + ' thành viên từ đơn vị khác, bạn chỉ có thể chọn tối đa ' + availableSlots + ' người.';
            }
            Toast.error(msg);
            return;
        }

        var btnAdd = document.getElementById('btn_add_to_preview');
        var originalHtml = btnAdd ? btnAdd.innerHTML : '';
        if (btnAdd) {
            btnAdd.disabled = true;
            btnAdd.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i>Đang cập nhật...';
        }

        var teamName = document.getElementById('sport_team_name')?.value || '';
        var formData = new FormData();
        formData.append('team_id', editingTeamId);
        formData.append('team_name', teamName);
        formData.append('registration_id', registrationId);
        sportSelectedAttendees.forEach(function(att) {
            formData.append('attendee_ids[]', att.id);
            formData.append('attendee_names[]', att.full_name || '');
        });

        fetch(window.BASE_URL + '/admin/registrations/updateSportTeam', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            if (btnAdd) {
                btnAdd.disabled = false;
                btnAdd.innerHTML = originalHtml;
            }
            if (data.success) {
                Toast.success('Cập nhật đội thành công.');
                editingTeamId = null;
                bootstrap.Modal.getInstance(document.getElementById('addDetailModal'))?.hide();
                location.reload();
            } else {
                Toast.error(data.error || 'Không thể cập nhật đội.');
            }
        })
        .catch(function() {
            if (btnAdd) {
                btnAdd.disabled = false;
                btnAdd.innerHTML = originalHtml;
            }
            Toast.error('Lỗi kết nối server.');
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
                Swal.fire({
                    title: "Đang xử lý...",
                    text: "Vui lòng chờ trong giây lát.",
                    allowOutsideClick: false,
                    didOpen: function() {
                        Swal.showLoading();
                    }
                });
                document.getElementById('delete-detail-form-' + detailId).submit();
            }
        });
    }

    function confirmDeleteTalent(entryId) {
        Swal.fire({
            title: 'Xác nhận xóa',
            text: 'Bạn có chắc chắn muốn xóa tiết mục văn nghệ này?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Xóa',
            cancelButtonText: 'Hủy'
        }).then(function(result) {
            if (result.isConfirmed) {
                Swal.fire({
                    title: "Đang xử lý...",
                    text: "Vui lòng chờ trong giây lát.",
                    allowOutsideClick: false,
                    didOpen: function() {
                        Swal.showLoading();
                    }
                });
                document.getElementById('delete-talent-form-' + entryId).submit();
            }
        });
    }

    function deleteCompetitionRegistration(competitionId) {
        Swal.fire({
            title: 'Xác nhận xóa',
            text: 'Bạn có chắc chắn muốn xóa đăng ký thi nghiệp vụ này?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Xóa',
            cancelButtonText: 'Hủy'
        }).then(function(result) {
            if (result.isConfirmed) {
                Swal.fire({
                    title: "Đang xử lý...",
                    text: "Vui lòng chờ trong giây lát.",
                    allowOutsideClick: false,
                    didOpen: function() {
                        Swal.showLoading();
                    }
                });
                fetch(window.BASE_URL + '/admin/registrations/deleteCompetitionRegistration', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: 'registration_id=' + registrationId + '&competition_id=' + competitionId
                })
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    Swal.close();
                    if (data.success) {
                        Toast.success('Đã xóa đăng ký thi nghiệp vụ.');
                        var row = document.querySelector('tr[data-competition-id="' + competitionId + '"]');
                        if (row) row.remove();

                        // Check if table is empty
                        var tbody = document.querySelector('#competition-list-table tbody');
                        if (tbody && tbody.children.length === 0) {
                            location.reload();
                        }
                    } else {
                        Toast.error(data.error || 'Có lỗi xảy ra.');
                    }
                })
                .catch(function() {
                    Swal.close();
                    Toast.error('Lỗi kết nối server.');
                });
            }
        });
    }

    function bindAttendeeEvents() {
        // Init datepickers for Add Staff modal
        var staffModal = document.getElementById('addAttendeeFromStaffModal');
        if (staffModal) {
            staffModal.addEventListener('shown.bs.modal', function() {
                var checkInEl = document.getElementById('staff_check_in_date');
                var checkOutEl = document.getElementById('staff_check_out_date');

                // Destroy existing flatpickr instances
                if (checkInEl._flatpickr) checkInEl._flatpickr.destroy();
                if (checkOutEl._flatpickr) checkOutEl._flatpickr.destroy();

                // Init check_out_date (disabled initially)
                var checkOutPicker = flatpickr(checkOutEl, {
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'd/m/Y',
                    allowInput: true,
                    clickOpens: false
                });
                // Disable altInput
                if (checkOutPicker.altInput) {
                    checkOutPicker.altInput.disabled = true;
                    checkOutPicker.altInput.placeholder = '-- Chọn ngày đến trước --';
                }

                // Init check_in_date with onChange handler
                flatpickr(checkInEl, {
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'd/m/Y',
                    allowInput: true,
                    onChange: function(selectedDates) {
                        if (selectedDates.length > 0) {
                            // Enable check_out
                            if (checkOutPicker.altInput) {
                                checkOutPicker.altInput.disabled = false;
                                checkOutPicker.altInput.placeholder = 'dd/mm/yyyy';
                            }
                            checkOutPicker.set('minDate', selectedDates[0]);
                            checkOutPicker.set('clickOpens', true);
                            // Clear check_out if it's before new check_in
                            if (checkOutPicker.selectedDates.length > 0 && checkOutPicker.selectedDates[0] < selectedDates[0]) {
                                checkOutPicker.clear();
                            }
                        } else {
                            // Disable check_out
                            if (checkOutPicker.altInput) {
                                checkOutPicker.altInput.disabled = true;
                                checkOutPicker.altInput.placeholder = '-- Chọn ngày đến trước --';
                            }
                            checkOutPicker.clear();
                            checkOutPicker.set('clickOpens', false);
                        }
                    }
                });
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
                        bootstrap.Modal.getInstance(document.getElementById('addAttendeeFromStaffModal')).hide();
                        setTimeout(function() {
                            location.reload();
                        }, 1000);
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
        var roleSelect = document.getElementById('staff_role_id');
        if (roleSelect) {
            for (var i = 0; i < roleSelect.options.length; i++) {
                roleSelect.options[i].selected = false;
            }
        }
        renderAttendeeAvailableStaff();
        renderAttendeeSelectedStaff();
    }

    function reloadAttendeesTable() {
        fetch(window.BASE_URL + '/admin/registrations/getAttendeesList?registration_id=' + registrationId)
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success && data.data) {
                    // Destroy existing DataTable if any
                    var table = $('#attendees-table');
                    if ($.fn.DataTable && $.fn.DataTable.isDataTable(table)) {
                        table.DataTable().destroy();
                    }
                    renderAttendeesTable(data.data);
                    // Reinitialize DataTable
                    if (typeof initAttendeesDataTable === 'function') {
                        initAttendeesDataTable();
                    }
                    // Scroll to table
                    var tableEl = document.getElementById('attendees-table');
                    if (tableEl) {
                        tableEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                }
            });
    }

    function getRoleBadgeClassJs(roleName) {
        var rLower = roleName.toLowerCase().trim();
        if (rLower.indexOf('trưởng đoàn') !== -1) {
            return 'bg-danger text-white';
        } else if (rLower.indexOf('phó đoàn') !== -1) {
            return 'bg-warning text-dark';
        } else if (rLower.indexOf('huấn luyện viên') !== -1 || rLower.indexOf('hlv') !== -1) {
            return 'bg-info text-dark';
        } else if (rLower.indexOf('vận động viên') !== -1 || rLower.indexOf('vđv') !== -1 || rLower.indexOf('thi đấu') !== -1) {
            return 'bg-primary text-white';
        } else if (rLower.indexOf('cổ động viên') !== -1 || rLower.indexOf('cdv') !== -1) {
            return 'bg-success text-white';
        } else if (rLower.indexOf('khách') !== -1) {
            return 'bg-dark text-white';
        } else {
            var hash = 0;
            for (var i = 0; i < rLower.length; i++) {
                hash = rLower.charCodeAt(i) + ((hash << 5) - hash);
            }
            var classes = [
                'bg-primary text-white',
                'bg-secondary text-white',
                'bg-success text-white',
                'bg-danger text-white',
                'bg-warning text-dark',
                'bg-info text-dark',
                'bg-dark text-white'
            ];
            return classes[Math.abs(hash) % classes.length];
        }
    }

    function renderAttendeesTable(attendees) {
        var tbody = document.querySelector('#attendees-table tbody');
        if (!tbody) return;

        var colCount = canEdit ? 11 : 10;

        if (attendees.length === 0) {
            tbody.innerHTML = '<tr><td colspan="' + colCount + '" class="text-center text-muted">Chưa có người tham dự nào.</td></tr>';
            updateAttendeesCount(0);
            return;
        }

        var html = '';
        attendees.forEach(function(att, idx) {
            var photoHtml = att.portrait_path
                ? '<img src="' + escapeHtml(att.portrait_path) + '" class="rounded mx-auto d-block" style="width:160px;height:160px;object-fit:cover;cursor:pointer;" onclick="viewDocument(\'' + escapeHtml(att.portrait_path) + '\', \'image\')" title="Click để xem">'
                : '<div class="bg-light rounded d-flex align-items-center justify-content-center mx-auto" style="width:160px;height:160px;"><i class="fa fa-user text-muted fa-3x"></i></div>';

            var statusLabel = getApprovalStatusLabel(att.approval_status);
            var positionDept = [];
            if (att.department_name) positionDept.push(att.department_name);
            if (att.position) positionDept.push(att.position);

            var roleBadges = '';
            if (att.role_name) {
                var roles = att.role_name.split(',').map(function(r) { return r.trim(); });
                roles.forEach(function(r) {
                    if (!r) return;
                    var cls = getRoleBadgeClassJs(r);
                    roleBadges += '<span class="badge ' + cls + ' me-1 mb-1">' + escapeHtml(r) + '</span>';
                });
            } else {
                roleBadges = '-';
            }

            html += '<tr>' +
                '<td class="text-center">' + (idx + 1) + '</td>' +
                '<td class="text-center">' + photoHtml + '</td>' +
                '<td>' + escapeHtml(att.full_name) + '</td>' +
                '<td>' + escapeHtml(positionDept.join(' - ')) + '</td>' +
                '<td>' + roleBadges + '</td>' +
                '<td>' + formatDate(att.start_date) + '</td>' +
                '<td>' + formatDate(att.check_in_date) + '</td>' +
                '<td>' + formatDate(att.check_out_date) + '</td>' +
                '<td>' + escapeHtml(att.transport_name || '-') + '</td>' +
                '<td>' + statusLabel + '</td>';

            if (canEdit) {
                var docsBtn = '';
                var docs = {
                    portrait: att.portrait_path || att.photo_path || '',
                    cccd_front: att.cccd_front_path || '',
                    cccd_back: att.cccd_back_path || '',
                    contract: att.contract_path || ''
                };
                if (docs.portrait || docs.cccd_front || docs.cccd_back || docs.contract) {
                    var docsJson = JSON.stringify(docs).replace(/'/g, "&#39;");
                    docsBtn = "<button type=\"button\" class=\"btn btn-sm btn-outline-info me-1\" onclick=\"viewAllDocuments(this)\" data-docs='" + docsJson + "' title=\"Xem tài liệu đính kèm\"><i class=\"fa fa-folder-open-o\"></i></button>";
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

    function reloadAttendeesList() {
        fetch(window.BASE_URL + '/admin/registrations/getAttendeesAjax?registration_id=' + registrationId)
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success && data.data) {
                    // Destroy existing DataTable if exists
                    if ($.fn.DataTable && $.fn.DataTable.isDataTable('#attendees-table')) {
                        $('#attendees-table').DataTable().destroy();
                    }
                    renderAttendeesTable(data.data);
                    // Reinitialize DataTable
                    if (typeof initAttendeesDataTable === 'function') {
                        initAttendeesDataTable();
                    }
                }
            })
            .catch(function() {
                Toast.error('Không thể tải lại danh sách người tham dự.');
            });
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
            if (staff.gender) subInfo.push(staff.gender === 'male' || staff.gender === 'Nam' ? 'Nam' : (staff.gender === 'female' || staff.gender === 'Nữ' ? 'Nữ' : staff.gender));
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
            var subInfo = [];
            if (staff.department_name) subInfo.push(staff.department_name);
            if (staff.position) subInfo.push(staff.position);
            if (staff.gender) subInfo.push(staff.gender === 'male' || staff.gender === 'Nam' ? 'Nam' : (staff.gender === 'female' || staff.gender === 'Nữ' ? 'Nữ' : staff.gender));
            if (staff.join_hotel_date) subInfo.push('Vào: ' + formatDate(staff.join_hotel_date));
            item.innerHTML = '<small>' + escapeHtml(staff.display) + '</small>' +
                (subInfo.length ? '<br><span class="text-muted" style="font-size:11px;">' + escapeHtml(subInfo.join(' - ')) + '</span>' : '');
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
                    var hasStaffId = att.staff_id && att.staff_id !== '' && att.staff_id !== '0' && att.staff_id !== 0;

                    // Lưu staff_id
                    document.getElementById('edit_staff_id').value = att.staff_id || '';

                    // Toggle readonly dựa vào staff_id
                    var fullNameEl = document.getElementById('edit_full_name');
                    var positionEl = document.getElementById('edit_position');
                    var departmentEl = document.getElementById('edit_department');
                    var noticeEl = document.getElementById('edit_staff_notice');

                    if (hasStaffId) {
                        fullNameEl.readOnly = true;
                        fullNameEl.classList.add('bg-light');
                        positionEl.readOnly = true;
                        positionEl.classList.add('bg-light');
                        departmentEl.readOnly = true;
                        departmentEl.classList.add('bg-light');
                        noticeEl.classList.remove('d-none');
                    } else {
                        fullNameEl.readOnly = false;
                        fullNameEl.classList.remove('bg-light');
                        positionEl.readOnly = false;
                        positionEl.classList.remove('bg-light');
                        departmentEl.readOnly = false;
                        departmentEl.classList.remove('bg-light');
                        noticeEl.classList.add('d-none');
                    }

                    document.getElementById('edit_full_name').value = att.full_name || '';
                    document.getElementById('edit_position').value = att.position || '';
                    document.getElementById('edit_department').value = att.department_name || '';
                    var editRoleSelect = document.getElementById('edit_role_id');
                    if (editRoleSelect) {
                        for (var i = 0; i < editRoleSelect.options.length; i++) {
                            editRoleSelect.options[i].selected = false;
                        }
                        if (att.role_id) {
                            var selectedRoles = String(att.role_id).split(',').map(function(s) { return s.trim(); });
                            for (var i = 0; i < editRoleSelect.options.length; i++) {
                                if (selectedRoles.indexOf(editRoleSelect.options[i].value) !== -1) {
                                    editRoleSelect.options[i].selected = true;
                                }
                            }
                        }
                    }
                    document.getElementById('edit_note').value = att.note || '';
                    document.getElementById('edit_start_date').value = formatDate(att.start_date);
                    document.getElementById('edit_transport_id').value = att.transport_id || '';

                    // Show simple previews for existing files
                    showSimplePreview('edit_portrait_preview', att.portrait_path);
                    showSimplePreview('edit_cccd_front_preview', att.cccd_front_path);
                    showSimplePreview('edit_cccd_back_preview', att.cccd_back_path);
                    showSimplePreview('edit_contract_preview', att.contract_path);

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
        ['edit_portrait_file', 'edit_cccd_front_file', 'edit_cccd_back_file', 'edit_contract_file'].forEach(function(id) {
            var el = document.getElementById(id);
            if (el) el.value = '';
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

    function showSimplePreview(elementId, url) {
        var el = document.getElementById(elementId);
        if (!el) return;
        if (!url) {
            el.innerHTML = '<i class="fa fa-image fa-2x text-muted"></i><div class="small text-muted mt-1">Chưa có ảnh</div>';
            return;
        }
        var isPdf = url.toLowerCase().indexOf('.pdf') > -1;
        if (isPdf) {
            el.innerHTML = '<a href="' + url + '" target="_blank"><i class="fa fa-file-pdf-o fa-2x text-danger"></i></a><div class="small text-muted mt-1">Click để xem PDF</div>';
        } else {
            el.innerHTML = '<img src="' + url + '" onclick="RegistrationView.viewDocument(\'' + url + '\', \'image\')" title="Click để xem lớn">';
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
                e.preventDefault();

                var checkInEl = document.getElementById('add_check_in_date');
                var checkOutEl = document.getElementById('add_check_out_date');
                var startDateEl = document.getElementById('add_start_date');

                // Set values from flatpickr to hidden inputs
                if (checkInEl && checkInEl._flatpickr && checkInEl._flatpickr.selectedDates[0]) {
                    checkInEl.value = checkInEl._flatpickr.formatDate(checkInEl._flatpickr.selectedDates[0], 'Y-m-d');
                }
                if (checkOutEl && checkOutEl._flatpickr && checkOutEl._flatpickr.selectedDates[0]) {
                    checkOutEl.value = checkOutEl._flatpickr.formatDate(checkOutEl._flatpickr.selectedDates[0], 'Y-m-d');
                }
                if (startDateEl && startDateEl._flatpickr && startDateEl._flatpickr.selectedDates[0]) {
                    startDateEl.value = startDateEl._flatpickr.formatDate(startDateEl._flatpickr.selectedDates[0], 'Y-m-d');
                }

                var btn = document.getElementById('btn_submit_attendee_manual');
                var originalHtml = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i>Đang thêm...';

                var formData = new FormData(form);

                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;

                    if (data.success) {
                        var modal = bootstrap.Modal.getInstance(document.getElementById('addAttendeeManualModal'));
                        if (modal) modal.hide();
                        Toast.success(data.message || 'Thêm thành công.');
                        location.reload();
                    } else {
                        Toast.error(data.error || 'Không thể thêm.');
                    }
                })
                .catch(function() {
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                    Toast.error('Lỗi kết nối.');
                });
            });
        }
    }

    function bindEditAttendeeForm() {
        var form = document.getElementById('edit-attendee-form');
        if (!form) return;

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
                    reloadAttendeesList();
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
                Swal.fire({
                    title: "Đang xử lý...",
                    text: "Vui lòng chờ trong giây lát.",
                    allowOutsideClick: false,
                    didOpen: function() {
                        Swal.showLoading();
                    }
                });
                document.getElementById('delete-attendee-form-' + attId).submit();
            }
        });
    }

    window.viewAllDocuments = function(btn) {
        try {
            var docsStr = btn.getAttribute('data-docs');
            var docs = JSON.parse(docsStr);
            var html = '';
            
            // Hàng 1: Ảnh chân dung + CCCD
            if (docs.portrait || docs.cccd_front || docs.cccd_back) {
                html += '<div class="row mb-4">';
                // Cột 1: Ảnh chân dung
                html += '<div class="col-md-1 text-center"></div>';
                html += '<div class="col-md-6 text-center">';
                html += '<h6>Ảnh chân dung</h6>';
                html += '<div class="text-center mb-3"><small class="text-muted d-block mb-2"></small>';
                if (docs.portrait) {
                    html += '<img src="' + escapeHtml(docs.portrait) + '" class="rounded" style="width: 530px; height: 530px; object-fit: cover; max-width: 100%;">';
                } else {
                    html += '<div class="border rounded d-flex align-items-center justify-content-center text-muted" style="width: 530px; height: 530px; max-width: 100%;"><i class="fa fa-user fa-3x"></i></div>';
                }
                html += '</div>';
                html += '</div>';
                // Cột 2: CCCD xếp dọc
                html += '<div class="col-md-4">';
                html += '<h6 class="text-center">Ảnh CCCD</h6>';
                if (docs.cccd_front) {
                    html += '<div class="text-center mb-3"><small class="text-muted d-block mb-2">Mặt trước</small>';
                    html += '<img src="' + escapeHtml(docs.cccd_front) + '" class="img-fluid rounded" style="max-width: 100%; aspect-ratio: 856/540; object-fit: cover;"></div>';
                }
                if (docs.cccd_back) {
                    html += '<div class="text-center"><small class="text-muted d-block mb-2">Mặt sau</small>';
                    html += '<img src="' + escapeHtml(docs.cccd_back) + '" class="img-fluid rounded" style="max-width: 100%; aspect-ratio: 856/540; object-fit: cover;"></div>';
                }
                if (!docs.cccd_front && !docs.cccd_back) {
                    html += '<div class="text-center text-muted"><i class="fa fa-id-card-o fa-3x"></i><p class="mt-2">Chưa có ảnh CCCD</p></div>';
                }
                html += '</div>';
                html += '<div class="col-md-1 text-center"></div>';
                html += '</div>';
            }
            // Hàng 2: Hợp đồng
            if (docs.contract) {
                html += '<div class="mb-4"><h6 class="text-center">Hợp đồng lao động</h6>';
                var isPdf = docs.contract.toLowerCase().indexOf('.pdf') > -1;
                if (isPdf) {
                    html += '<iframe src="' + escapeHtml(docs.contract) + '" style="width:100%; height:600px;" frameborder="0"></iframe>';
                } else {
                    html += '<div class="text-center"><img src="' + escapeHtml(docs.contract) + '" class="img-fluid rounded" style="max-height: 600px;"></div>';
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

    // ==================== MISS REGISTRATION ====================
    var missAllAttendees = [];
    var missSelectedAttendees = [];
    var missMaxPerOrg = 0;
    var missRegisteredCount = 0;

    function bindMissEvents() {
        var contestSelect = document.getElementById('miss_contest_id');
        if (contestSelect) {
            contestSelect.addEventListener('change', function() {
                if (this.value) {
                    loadMissContestInfo(this.value);
                    loadAttendeesForMiss();
                } else {
                    document.getElementById('miss_max_per_org').value = '-';
                    hideMissDualListbox();
                }
            });
        }

        document.getElementById('miss_search')?.addEventListener('input', function() {
            filterMissList(this.value);
        });

        document.getElementById('miss_btn_add')?.addEventListener('click', addMissSelected);
        document.getElementById('miss_btn_add_all')?.addEventListener('click', addMissAll);
        document.getElementById('miss_btn_remove')?.addEventListener('click', removeMissSelected);
        document.getElementById('miss_btn_remove_all')?.addEventListener('click', removeMissAll);

        var form = document.getElementById('add-miss-form');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                if (missSelectedAttendees.length === 0) {
                    Toast.error('Vui lòng chọn ít nhất một thí sinh.');
                    return false;
                }
                submitMissForm(form);
            });
        }

        // Load contests when modal opens
        var modal = document.getElementById('addMissModal');
        if (modal) {
            modal.addEventListener('show.bs.modal', function() {
                resetMissModal();
            });
        }
    }

    function resetMissModal() {
        var contestSelect = document.getElementById('miss_contest_id');
        document.getElementById('add-miss-form').reset();
        contestSelect.innerHTML = '<option value="">-- Đang tải... --</option>';
        document.getElementById('miss_max_per_org').value = '-';
        missAllAttendees = [];
        missSelectedAttendees = [];
        missMaxPerOrg = 0;
        hideMissDualListbox();
        removeMissHiddenInputs();

        fetch(window.BASE_URL + '/admin/registrations/getContentItems?event_id=' + eventId + '&content_type=miss&registration_id=' + registrationId)
            .then(function(response) { return response.json(); })
            .then(function(data) {
                contestSelect.innerHTML = '<option value="">-- Chọn cuộc thi --</option>';
                if (data.success && data.data && data.data.length > 0) {
                    data.data.forEach(function(item) {
                        var opt = document.createElement('option');
                        opt.value = item.id;
                        opt.textContent = item.name;
                        contestSelect.appendChild(opt);
                    });
                } else {
                    contestSelect.innerHTML = '<option value="">-- Không có cuộc thi nào --</option>';
                }
            });
    }

    function loadMissContestInfo(contestId) {
        fetch(window.BASE_URL + '/admin/registrations/getMissContestInfo?contest_id=' + contestId)
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success && data.data) {
                    missMaxPerOrg = data.data.max_per_org || 0;
                    document.getElementById('miss_max_per_org').value = missMaxPerOrg > 0 ? missMaxPerOrg : 'Không giới hạn';
                    document.getElementById('miss_max_count').textContent = missMaxPerOrg > 0 ? missMaxPerOrg : '∞';
                }
            });
    }

    function loadAttendeesForMiss() {
        var contestId = document.getElementById('miss_contest_id').value;
        if (!contestId) return;

        fetch(window.BASE_URL + '/admin/registrations/getAttendeesForMiss?registration_id=' + registrationId + '&contest_id=' + contestId)
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success) {
                    missAllAttendees = data.data || [];
                    missSelectedAttendees = [];
                    missRegisteredCount = (data.registered || []).length;
                    renderMissAvailableList();
                    renderMissSelectedList();
                    showMissDualListbox();
                    renderMissRegisteredList(data.registered || []);
                    updateMissMaxDisplay();
                }
            });
    }

    function updateMissMaxDisplay() {
        var remaining = missMaxPerOrg > 0 ? (missMaxPerOrg - missRegisteredCount - missSelectedAttendees.length) : '∞';
        document.getElementById('miss_max_count').textContent = remaining;
    }

    function renderMissRegisteredList(registered) {
        var wrapper = document.getElementById('miss_registered_wrapper');
        var list = document.getElementById('miss_registered_list');
        var count = document.getElementById('miss_registered_count');

        if (!wrapper || !list) return;

        list.innerHTML = '';
        if (count) count.textContent = registered.length;

        if (registered.length > 0) {
            wrapper.style.display = 'block';
            registered.forEach(function(item) {
                var tr = document.createElement('tr');
                tr.innerHTML = '<td><span class="badge bg-primary">' + (item.candidate_number || '-') + '</span></td>' +
                    '<td>' + (item.name || '') + '</td>';
                list.appendChild(tr);
            });
        } else {
            wrapper.style.display = 'none';
        }
    }

    function showMissDualListbox() {
        document.getElementById('miss_dual_listbox_wrapper').style.display = 'flex';
        document.getElementById('miss_placeholder').style.display = 'none';
    }

    function hideMissDualListbox() {
        document.getElementById('miss_dual_listbox_wrapper').style.display = 'none';
        document.getElementById('miss_placeholder').style.display = 'block';
        var wrapper = document.getElementById('miss_registered_wrapper');
        if (wrapper) wrapper.style.display = 'none';
    }

    function renderMissAvailableList() {
        var list = document.getElementById('miss_available_list');
        if (!list) return;
        list.innerHTML = '';
        missAllAttendees.forEach(function(att) {
            var div = document.createElement('div');
            div.className = 'list-group-item list-group-item-action py-2';
            div.setAttribute('data-id', att.id);
            var displayName = att.name || att.full_name || '';
            var subInfo = [];
            if (att.department_name) subInfo.push(att.department_name);
            if (att.position) subInfo.push(att.position);
            div.innerHTML = '<small>' + escapeHtml(displayName) + '</small>' +
                (subInfo.length ? '<br><span class="text-muted" style="font-size:11px;">' + escapeHtml(subInfo.join(' - ')) + '</span>' : '');
            div.addEventListener('click', function() { this.classList.toggle('active'); });
            list.appendChild(div);
        });
    }

    function renderMissSelectedList() {
        var list = document.getElementById('miss_selected_list');
        if (!list) return;
        list.innerHTML = '';
        removeMissHiddenInputs();
        missSelectedAttendees.forEach(function(att) {
            var div = document.createElement('div');
            div.className = 'list-group-item list-group-item-action py-2';
            div.setAttribute('data-id', att.id);
            var displayName = att.name || att.full_name || '';
            var subInfo = [];
            if (att.department_name) subInfo.push(att.department_name);
            if (att.position) subInfo.push(att.position);
            div.innerHTML = '<small>' + escapeHtml(displayName) + '</small>' +
                (subInfo.length ? '<br><span class="text-muted" style="font-size:11px;">' + escapeHtml(subInfo.join(' - ')) + '</span>' : '');
            div.addEventListener('click', function() { this.classList.toggle('active'); });
            list.appendChild(div);

            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'attendee_ids[]';
            input.value = att.id;
            input.className = 'miss-hidden-input';
            document.getElementById('add-miss-form').appendChild(input);
        });
        document.getElementById('miss_selected_count').textContent = missSelectedAttendees.length;
    }

    function removeMissHiddenInputs() {
        document.querySelectorAll('.miss-hidden-input').forEach(function(el) { el.remove(); });
    }

    function filterMissList(keyword) {
        var items = document.querySelectorAll('#miss_available_list .list-group-item');
        keyword = keyword.toLowerCase();
        items.forEach(function(item) {
            var text = item.textContent.toLowerCase();
            item.style.display = text.indexOf(keyword) > -1 ? '' : 'none';
        });
    }

    function addMissSelected() {
        var selected = document.querySelectorAll('#miss_available_list .list-group-item.active');
        var totalAllowed = missMaxPerOrg > 0 ? (missMaxPerOrg - missRegisteredCount) : Infinity;
        selected.forEach(function(item) {
            var id = item.getAttribute('data-id');
            var att = missAllAttendees.find(function(a) { return String(a.id) === String(id); });
            if (att && !missSelectedAttendees.find(function(s) { return String(s.id) === String(id); })) {
                if (missSelectedAttendees.length >= totalAllowed) {
                    Toast.warning('Đã đạt số lượng tối đa cho phép: ' + totalAllowed);
                    return;
                }
                missSelectedAttendees.push(att);
                missAllAttendees = missAllAttendees.filter(function(a) { return String(a.id) !== String(id); });
            }
        });
        renderMissAvailableList();
        renderMissSelectedList();
        updateMissMaxDisplay();
    }

    function addMissAll() {
        var totalAllowed = missMaxPerOrg > 0 ? (missMaxPerOrg - missRegisteredCount) : Infinity;
        missAllAttendees.forEach(function(att) {
            if (missSelectedAttendees.length >= totalAllowed) return;
            missSelectedAttendees.push(att);
        });
        missAllAttendees = missAllAttendees.filter(function(att) {
            return !missSelectedAttendees.find(function(s) { return String(s.id) === String(att.id); });
        });
        renderMissAvailableList();
        renderMissSelectedList();
        updateMissMaxDisplay();
    }

    function removeMissSelected() {
        var selected = document.querySelectorAll('#miss_selected_list .list-group-item.active');
        selected.forEach(function(item) {
            var id = item.getAttribute('data-id');
            var att = missSelectedAttendees.find(function(a) { return String(a.id) === String(id); });
            if (att) {
                missAllAttendees.push(att);
                missSelectedAttendees = missSelectedAttendees.filter(function(a) { return String(a.id) !== String(id); });
            }
        });
        renderMissAvailableList();
        renderMissSelectedList();
        updateMissMaxDisplay();
    }

    function removeMissAll() {
        missSelectedAttendees.forEach(function(att) { missAllAttendees.push(att); });
        missSelectedAttendees = [];
        renderMissAvailableList();
        renderMissSelectedList();
        updateMissMaxDisplay();
    }

    function submitMissForm(form) {
        var submitBtn = document.getElementById('btn_submit_miss');
        var originalHtml = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i>Đang đăng ký...';

        var formData = new FormData(form);
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalHtml;
            if (data.success) {
                var modal = bootstrap.Modal.getInstance(document.getElementById('addMissModal'));
                if (modal) modal.hide();
                Toast.success(data.message || 'Đăng ký thành công!');
                location.reload();
            } else {
                Toast.error(data.error || 'Có lỗi xảy ra.');
            }
        })
        .catch(function() {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalHtml;
            Toast.error('Lỗi kết nối.');
        });
    }

    function editMissContestant(id) {
        fetch(window.BASE_URL + '/admin/registrations/getMissContestant?id=' + id)
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success && data.data) {
                    var d = data.data;
                    document.getElementById('edit_miss_id').value = d.id;
                    document.getElementById('edit_miss_name').value = d.attendee_name || '';
                    document.getElementById('edit_miss_candidate_number').value = d.candidate_number || '';
                    document.getElementById('edit_miss_height').value = d.height_cm || '';
                    document.getElementById('edit_miss_weight').value = d.weight_kg || '';
                    document.getElementById('edit_miss_measurements').value = d.measurements || '';
                    document.getElementById('edit_miss_talent').value = d.talent || '';
                    document.getElementById('edit_miss_bio').value = d.bio || '';

                    var modal = new bootstrap.Modal(document.getElementById('editMissModal'));
                    modal.show();
                } else {
                    Toast.error(data.error || 'Không thể tải thông tin.');
                }
            })
            .catch(function() { Toast.error('Lỗi kết nối.'); });
    }

    function deleteMissContestant(id) {
        Swal.fire({
            title: 'Xác nhận xóa',
            text: 'Bạn có chắc muốn xóa thí sinh này khỏi cuộc thi?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Xóa',
            cancelButtonText: 'Hủy'
        }).then(function(result) {
            if (result.isConfirmed) {
                Swal.fire({
                    title: "Đang xử lý...",
                    text: "Vui lòng chờ trong giây lát.",
                    allowOutsideClick: false,
                    didOpen: function() {
                        Swal.showLoading();
                    }
                });
                var formData = new FormData();
                formData.append('id', id);
                fetch(window.BASE_URL + '/admin/registrations/deleteMissContestant', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    Swal.close();
                    if (data.success) {
                        Toast.success(data.message || 'Xóa thành công!');
                        location.reload();
                    } else {
                        Toast.error(data.error || 'Có lỗi xảy ra.');
                    }
                })
                .catch(function() {
                    Swal.close();
                    Toast.error('Lỗi kết nối.');
                });
            }
        });
    }

    function bindEditMissForm() {
        var form = document.getElementById('edit-miss-form');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                var submitBtn = document.getElementById('btn_submit_edit_miss');
                var originalHtml = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i>Đang cập nhật...';

                var formData = new FormData(form);
                fetch(window.BASE_URL + '/admin/registrations/updateMissContestant', {
                    method: 'POST',
                    body: formData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalHtml;
                    if (data.success) {
                        var modal = bootstrap.Modal.getInstance(document.getElementById('editMissModal'));
                        if (modal) modal.hide();
                        Toast.success(data.message || 'Cập nhật thành công!');
                        location.reload();
                    } else {
                        Toast.error(data.error || 'Có lỗi xảy ra.');
                    }
                })
                .catch(function() {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalHtml;
                    Toast.error('Lỗi kết nối.');
                });
            });
        }
    }

    // ==================== TALENT REGISTRATION ====================
    var talentAllAttendees = [];
    var talentSelectedAttendees = [];

    function bindTalentEvents() {
        var btnOpenModal = document.getElementById('btn_open_talent_modal');

        // Button open modal
        if (btnOpenModal) {
            btnOpenModal.addEventListener('click', function() {
                // Copy alliance từ ngoài vào form
                var allianceSelect = document.getElementById('talent_alliance_property');
                var form = document.getElementById('add-talent-form');
                if (form) {
                    // Remove old hidden inputs
                    form.querySelectorAll('input[name="alliance_property_ids[]"]').forEach(function(el) { el.remove(); });
                    if (allianceSelect) {
                        Array.from(allianceSelect.selectedOptions).forEach(function(opt) {
                            var hidden = document.createElement('input');
                            hidden.type = 'hidden';
                            hidden.name = 'alliance_property_ids[]';
                            hidden.value = opt.value;
                            form.appendChild(hidden);
                        });
                    }
                }

                // Load attendees
                loadAttendeesForTalent();

                // Open modal
                var modal = new bootstrap.Modal(document.getElementById('addTalentModal'));
                modal.show();
            });
        }

        document.getElementById('talent_search')?.addEventListener('input', function() {
            filterTalentList(this.value);
        });

        document.getElementById('talent_btn_add')?.addEventListener('click', addTalentSelected);
        document.getElementById('talent_btn_add_all')?.addEventListener('click', addTalentAll);
        document.getElementById('talent_btn_remove')?.addEventListener('click', removeTalentSelected);
        document.getElementById('talent_btn_remove_all')?.addEventListener('click', removeTalentAll);

        // Talent alliance confirm button
        var btnConfirmTalentAlliance = document.getElementById('btn_confirm_talent_alliance');
        if (btnConfirmTalentAlliance) {
            btnConfirmTalentAlliance.addEventListener('click', function() {
                var checkboxes = document.querySelectorAll('.talent-alliance-modal-cb');
                var selectedTexts = [];
                var selectedIds = [];
                checkboxes.forEach(function(cb) {
                    if (cb.checked) {
                        selectedIds.push(cb.value);
                        selectedTexts.push(cb.getAttribute('data-name'));
                    }
                });

                // Lưu vào server
                btnConfirmTalentAlliance.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Đang lưu...';
                btnConfirmTalentAlliance.disabled = true;

                var talentCard = document.getElementById('talent-registration-card');
                var talentEventContentId = talentCard ? talentCard.getAttribute('data-event-content-id') : '';


                var formData = new FormData();
                formData.append('registration_id', registrationId);
                if (talentEventContentId) {
                    formData.append('event_content_id', talentEventContentId);
                }
                selectedIds.forEach(function(id) {
                    formData.append('target_org_ids[]', id);
                });

                fetch(window.BASE_URL + '/admin/registrations/saveAllianceProperties', {
                    method: 'POST',
                    body: formData
                })
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    btnConfirmTalentAlliance.innerHTML = 'Xác nhận';
                    btnConfirmTalentAlliance.disabled = false;

                    console.log('Save alliance response:', data);

                    if (data.success) {
                        // Cập nhật UI
                        var allianceSelect = document.getElementById('talent_alliance_property');
                        if (allianceSelect) {
                            Array.from(allianceSelect.options).forEach(function(opt) {
                                opt.selected = selectedIds.includes(opt.value);
                            });
                        }

                        var displayText = document.getElementById('talent_alliance_selected_texts');
                        if (displayText) {
                            displayText.innerHTML = '';
                            for (var i = 0; i < selectedIds.length; i++) {
                                var badge = document.createElement('span');
                                badge.className = 'badge bg-primary me-1 mb-1 p-2 border';
                                badge.style.fontSize = '12px';
                                badge.innerHTML = selectedTexts[i] + ' <i class="fa fa-times ms-1 text-white" style="cursor:pointer;" onclick="RegistrationView.removeTalentAllianceProperty(\'' + selectedIds[i] + '\')" title="Huỷ"></i>';
                                displayText.appendChild(badge);
                            }
                        }

                        Toast.success('Lưu đơn vị liên quân thành công');

                        var modalEl = document.getElementById('talentAlliancePropertyModal');
                        if (modalEl) {
                            var modal = bootstrap.Modal.getInstance(modalEl);
                            if (modal) modal.hide();
                        }
                    } else {
                        Toast.error(data.error || 'Có lỗi xảy ra');
                    }
                })
                .catch(function() {
                    btnConfirmTalentAlliance.innerHTML = 'Xác nhận';
                    btnConfirmTalentAlliance.disabled = false;
                    Toast.error('Lỗi kết nối');
                });
            });
        }

        var form = document.getElementById('add-talent-form');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                submitTalentForm(form);
            });
        }

        // Load categories và alliance khi page load
        loadTalentCategoriesMain();
        loadTalentAllianceProperties();

        // Reset modal khi đóng
        var modalEl = document.getElementById('addTalentModal');
        if (modalEl) {
            modalEl.addEventListener('hidden.bs.modal', function() {
                document.getElementById('add-talent-form').reset();
                talentAllAttendees = [];
                talentSelectedAttendees = [];
                removeTalentHiddenInputs();
            });
        }

        // Bindings for edit talent modal dual listbox
        document.getElementById('edit_talent_search')?.addEventListener('input', function() {
            filterEditTalentList(this.value);
        });

        document.getElementById('edit_talent_btn_add')?.addEventListener('click', addEditTalentSelected);
        document.getElementById('edit_talent_btn_add_all')?.addEventListener('click', addEditTalentAll);
        document.getElementById('edit_talent_btn_remove')?.addEventListener('click', removeEditTalentSelected);
        document.getElementById('edit_talent_btn_remove_all')?.addEventListener('click', removeEditTalentAll);

        var editModalEl = document.getElementById('editTalentModal');
        if (editModalEl) {
            editModalEl.addEventListener('hidden.bs.modal', function() {
                document.getElementById('editTalentForm').reset();
                editTalentAllAttendees = [];
                editTalentSelectedAttendees = [];
                removeEditTalentHiddenInputs();
            });
        }
    }

    function loadTalentCategoriesMain() {
        var categorySelect = document.getElementById('talent_category_select');
        var editCategorySelect = document.getElementById('edit_talent_category');
        if (!categorySelect && !editCategorySelect) return;

        if (categorySelect) {
            categorySelect.innerHTML = '<option value="">-- Đang tải... --</option>';
        }
        if (editCategorySelect) {
            editCategorySelect.innerHTML = '<option value="">-- Đang tải... --</option>';
        }

        fetch(window.BASE_URL + '/admin/registrations/getTalentCategories?event_id=' + eventId)
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (categorySelect) {
                    categorySelect.innerHTML = '<option value="">-- Chọn thể loại --</option>';
                }
                if (editCategorySelect) {
                    editCategorySelect.innerHTML = '<option value="">-- Chọn thể loại --</option>';
                }
                if (data.success && data.data && data.data.length > 0) {
                    var categories = data.data.slice().sort(function(a, b) {
                        var codeA = (a.code || '').toLowerCase();
                        var codeB = (b.code || '').toLowerCase();
                        return codeA.localeCompare(codeB);
                    });
                    categories.forEach(function(item) {
                        if (categorySelect) {
                            var opt = document.createElement('option');
                            opt.value = item.id;
                            opt.textContent = item.name;
                            categorySelect.appendChild(opt);
                        }

                        if (editCategorySelect) {
                            var editOpt = document.createElement('option');
                            editOpt.value = item.id;
                            editOpt.textContent = item.name;
                            editCategorySelect.appendChild(editOpt);
                        }
                    });
                } else {
                    if (categorySelect) {
                        categorySelect.innerHTML = '<option value="">-- Không có thể loại nào --</option>';
                    }
                    if (editCategorySelect) {
                        editCategorySelect.innerHTML = '<option value="">-- Không có thể loại nào --</option>';
                    }
                }
            });
    }

    function resetTalentModal() {
        document.getElementById('add-talent-form').reset();
        talentAllAttendees = [];
        talentSelectedAttendees = [];
        removeTalentHiddenInputs();

        // Reset category select
        var categorySelect = document.getElementById('talent_category_select');
        if (categorySelect) categorySelect.value = '';

        // Reset alliance display
        var allianceDisplay = document.getElementById('talent_alliance_selected_texts');
        if (allianceDisplay) allianceDisplay.innerHTML = '';
        var allianceSelect = document.getElementById('talent_alliance_property');
        if (allianceSelect) {
            Array.from(allianceSelect.options).forEach(function(opt) { opt.selected = false; });
        }

        // Reload categories và alliance
        loadTalentCategoriesMain();
        loadTalentAllianceProperties();
    }

    function loadTalentAllianceProperties() {
        var allianceSelect = document.getElementById('talent_alliance_property');
        var modalList = document.getElementById('talent_alliance_modal_list');
        if (!allianceSelect || !modalList) return;

        var talentCard = document.getElementById('talent-registration-card');
        var talentEventContentId = talentCard ? talentCard.getAttribute('data-event-content-id') : '';

        fetch(window.BASE_URL + '/admin/registrations/getAllianceProperties?registration_id=' + registrationId + '&event_content_id=' + talentEventContentId)
            .then(function(response) { return response.json(); })
            .then(function(data) {
                allianceSelect.innerHTML = '';
                modalList.innerHTML = '';

                var selectedIds = [];
                var selectedTexts = [];

                if (data.success && data.data && data.data.length > 0) {
                    data.data.forEach(function(item) {
                        var isSelected = item.is_selected == 1;
                        var escapedName = escapeHtml(item.code + ' - ' + item.name);

                        var opt = document.createElement('option');
                        opt.value = item.id;
                        opt.setAttribute('data-code', item.code);
                        opt.textContent = item.code + ' - ' + item.name;
                        if (isSelected) {
                            opt.selected = true;
                            selectedIds.push(item.id);
                            selectedTexts.push(escapedName);
                        }
                        allianceSelect.appendChild(opt);

                        var div = document.createElement('div');
                        div.className = 'form-check mb-2';
                        var checked = isSelected ? 'checked' : '';
                        div.innerHTML = '<input class="form-check-input talent-alliance-modal-cb" type="checkbox" value="'+item.id+'" data-name="'+escapedName+'" data-code="'+escapeHtml(item.code)+'" id="talent_alliance_'+item.id+'" '+checked+'>' +
                                        '<label class="form-check-label" for="talent_alliance_'+item.id+'">' + escapedName + '</label>';
                        modalList.appendChild(div);
                    });

                    // Hiển thị badges cho các đơn vị đã chọn
                    var displayText = document.getElementById('talent_alliance_selected_texts');
                    if (displayText && selectedIds.length > 0) {
                        displayText.innerHTML = '';
                        for (var i = 0; i < selectedIds.length; i++) {
                            var badge = document.createElement('span');
                            badge.className = 'badge bg-primary me-1 mb-1 p-2 border';
                            badge.style.fontSize = '12px';
                            badge.innerHTML = selectedTexts[i] + ' <i class="fa fa-times ms-1 text-white" style="cursor:pointer;" onclick="RegistrationView.removeTalentAllianceProperty(\'' + selectedIds[i] + '\')" title="Huỷ"></i>';
                            displayText.appendChild(badge);
                        }
                    }
                } else {
                    modalList.innerHTML = '<p class="text-muted mb-0">Không có đơn vị nào để liên quân.</p>';
                }
            });
    }

    function removeTalentAllianceProperty(id) {
        Swal.fire({
            title: 'Xác nhận',
            text: 'Bạn có chắc muốn xóa đơn vị liên quân này?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Xóa',
            cancelButtonText: 'Hủy'
        }).then(function(result) {
            if (result.isConfirmed) {
                Swal.fire({
                    title: "Đang xử lý...",
                    text: "Vui lòng chờ trong giây lát.",
                    allowOutsideClick: false,
                    didOpen: function() {
                        Swal.showLoading();
                    }
                });
                doRemoveTalentAllianceProperty(id);
            }
        });
    }

    function doRemoveTalentAllianceProperty(id) {
        var cb = document.getElementById('talent_alliance_' + id);
        if (cb) cb.checked = false;

        var allianceSelect = document.getElementById('talent_alliance_property');
        if (allianceSelect) {
            Array.from(allianceSelect.options).forEach(function(opt) {
                if (opt.value == id) opt.selected = false;
            });
        }

        // Xóa badge
        var displayText = document.getElementById('talent_alliance_selected_texts');
        if (displayText) {
            var badges = displayText.querySelectorAll('span.badge');
            badges.forEach(function(badge) {
                var closeIcon = badge.querySelector('i.fa-times');
                if (closeIcon) {
                    var onclickAttr = closeIcon.getAttribute('onclick') || '';
                    if (onclickAttr.indexOf("'" + id + "'") !== -1) {
                        badge.remove();
                    }
                }
            });
        }

        // Lưu danh sách còn lại vào server
        var checkboxes = document.querySelectorAll('.talent-alliance-modal-cb');
        var remainingIds = [];
        checkboxes.forEach(function(cbEl) {
            if (cbEl.checked) remainingIds.push(cbEl.value);
        });

        var talentCard = document.getElementById('talent-registration-card');
        var talentEventContentId = talentCard ? talentCard.getAttribute('data-event-content-id') : '';

        var formData = new FormData();
        formData.append('registration_id', registrationId);
        formData.append('event_content_id', talentEventContentId);
        remainingIds.forEach(function(rid) {
            formData.append('target_org_ids[]', rid);
        });

        fetch(window.BASE_URL + '/admin/registrations/saveAllianceProperties', {
            method: 'POST',
            body: formData
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            Swal.close();
            if (data.success) {
                Toast.success('Đã xóa đơn vị liên quân.');
            } else {
                Toast.error(data.error || 'Có lỗi xảy ra.');
            }
        })
        .catch(function() {
            Swal.close();
            Toast.error('Lỗi kết nối.');
        });
    }

    function loadAttendeesForTalent() {
        fetch(window.BASE_URL + '/admin/registrations/getAttendeesForTalent?registration_id=' + registrationId)
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success) {
                    talentAllAttendees = data.data || [];
                    talentSelectedAttendees = [];
                    renderTalentAvailableList();
                    renderTalentSelectedList();
                    showTalentDualListbox();
                }
            });
    }

    function showTalentDualListbox() {
        var wrapper = document.getElementById('talent_dual_listbox_wrapper');
        var placeholder = document.getElementById('talent_placeholder');
        if (wrapper) wrapper.style.display = 'flex';
        if (placeholder) placeholder.style.display = 'none';
    }

    function hideTalentDualListbox() {
        var wrapper = document.getElementById('talent_dual_listbox_wrapper');
        var placeholder = document.getElementById('talent_placeholder');
        if (wrapper) wrapper.style.display = 'none';
        if (placeholder) placeholder.style.display = 'block';
    }

    function renderTalentAvailableList() {
        var list = document.getElementById('talent_available_list');
        if (!list) return;
        list.innerHTML = '';
        talentAllAttendees.forEach(function(att) {
            var div = document.createElement('div');
            div.className = 'list-group-item list-group-item-action py-2';
            div.setAttribute('data-id', att.id);
            var displayName = att.name || att.full_name || '';
            var subInfo = [];
            if (att.department_name) subInfo.push(att.department_name);
            if (att.position) subInfo.push(att.position);
            div.innerHTML = '<small>' + escapeHtml(displayName) + '</small>' +
                (subInfo.length ? '<br><span class="text-muted" style="font-size:11px;">' + escapeHtml(subInfo.join(' - ')) + '</span>' : '');
            div.addEventListener('click', function() { this.classList.toggle('active'); });
            list.appendChild(div);
        });
    }

    function renderTalentSelectedList() {
        var list = document.getElementById('talent_selected_list');
        if (!list) return;
        list.innerHTML = '';
        removeTalentHiddenInputs();
        talentSelectedAttendees.forEach(function(att) {
            var div = document.createElement('div');
            div.className = 'list-group-item list-group-item-action py-2';
            div.setAttribute('data-id', att.id);
            var displayName = att.name || att.full_name || '';
            var subInfo = [];
            if (att.department_name) subInfo.push(att.department_name);
            if (att.position) subInfo.push(att.position);
            div.innerHTML = '<small>' + escapeHtml(displayName) + '</small>' +
                (subInfo.length ? '<br><span class="text-muted" style="font-size:11px;">' + escapeHtml(subInfo.join(' - ')) + '</span>' : '');
            div.addEventListener('click', function() { this.classList.toggle('active'); });
            list.appendChild(div);

            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'attendee_ids[]';
            input.value = att.id;
            input.className = 'talent-hidden-input';
            document.getElementById('add-talent-form').appendChild(input);
        });
        document.getElementById('talent_selected_count').textContent = talentSelectedAttendees.length;
    }

    function removeTalentHiddenInputs() {
        document.querySelectorAll('.talent-hidden-input').forEach(function(el) { el.remove(); });
    }

    function filterTalentList(keyword) {
        var items = document.querySelectorAll('#talent_available_list .list-group-item');
        keyword = keyword.toLowerCase();
        items.forEach(function(item) {
            var text = item.textContent.toLowerCase();
            item.style.display = text.indexOf(keyword) > -1 ? '' : 'none';
        });
    }

    function addTalentSelected() {
        var selected = document.querySelectorAll('#talent_available_list .list-group-item.active');
        selected.forEach(function(item) {
            var id = item.getAttribute('data-id');
            var att = talentAllAttendees.find(function(a) { return String(a.id) === String(id); });
            if (att && !talentSelectedAttendees.find(function(s) { return String(s.id) === String(id); })) {
                talentSelectedAttendees.push(att);
                talentAllAttendees = talentAllAttendees.filter(function(a) { return String(a.id) !== String(id); });
            }
        });
        renderTalentAvailableList();
        renderTalentSelectedList();
    }

    function addTalentAll() {
        talentAllAttendees.forEach(function(att) { talentSelectedAttendees.push(att); });
        talentAllAttendees = [];
        renderTalentAvailableList();
        renderTalentSelectedList();
    }

    function removeTalentSelected() {
        var selected = document.querySelectorAll('#talent_selected_list .list-group-item.active');
        selected.forEach(function(item) {
            var id = item.getAttribute('data-id');
            var att = talentSelectedAttendees.find(function(a) { return String(a.id) === String(id); });
            if (att) {
                talentAllAttendees.push(att);
                talentSelectedAttendees = talentSelectedAttendees.filter(function(a) { return String(a.id) !== String(id); });
            }
        });
        renderTalentAvailableList();
        renderTalentSelectedList();
    }

    function removeTalentAll() {
        talentSelectedAttendees.forEach(function(att) { talentAllAttendees.push(att); });
        talentSelectedAttendees = [];
        renderTalentAvailableList();
        renderTalentSelectedList();
    }

    var editTalentAllAttendees = [];
    var editTalentSelectedAttendees = [];

    function loadAttendeesForEditTalent(selectedMemberIds) {
        fetch(window.BASE_URL + '/admin/registrations/getAttendeesForTalent?registration_id=' + registrationId)
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success) {
                    var all = data.data || [];
                    editTalentSelectedAttendees = [];
                    editTalentAllAttendees = [];

                    all.forEach(function(att) {
                        if (selectedMemberIds.includes(Number(att.id)) || selectedMemberIds.includes(String(att.id))) {
                            editTalentSelectedAttendees.push(att);
                        } else {
                            editTalentAllAttendees.push(att);
                        }
                    });

                    renderEditTalentAvailableList();
                    renderEditTalentSelectedList();
                }
            });
    }

    function renderEditTalentAvailableList() {
        var list = document.getElementById('edit_talent_available_list');
        if (!list) return;
        list.innerHTML = '';
        editTalentAllAttendees.forEach(function(att) {
            var div = document.createElement('div');
            div.className = 'list-group-item list-group-item-action py-2';
            div.setAttribute('data-id', att.id);
            var displayName = att.name || att.full_name || '';
            var subInfo = [];
            if (att.department_name) subInfo.push(att.department_name);
            if (att.position) subInfo.push(att.position);
            div.innerHTML = '<small>' + escapeHtml(displayName) + '</small>' +
                (subInfo.length ? '<br><span class="text-muted" style="font-size:11px;">' + escapeHtml(subInfo.join(' - ')) + '</span>' : '');
            div.addEventListener('click', function() { this.classList.toggle('active'); });
            list.appendChild(div);
        });
    }

    function renderEditTalentSelectedList() {
        var list = document.getElementById('edit_talent_selected_list');
        if (!list) return;
        list.innerHTML = '';
        removeEditTalentHiddenInputs();
        editTalentSelectedAttendees.forEach(function(att) {
            var div = document.createElement('div');
            div.className = 'list-group-item list-group-item-action py-2';
            div.setAttribute('data-id', att.id);
            var displayName = att.name || att.full_name || '';
            var subInfo = [];
            if (att.department_name) subInfo.push(att.department_name);
            if (att.position) subInfo.push(att.position);
            div.innerHTML = '<small>' + escapeHtml(displayName) + '</small>' +
                (subInfo.length ? '<br><span class="text-muted" style="font-size:11px;">' + escapeHtml(subInfo.join(' - ')) + '</span>' : '');
            div.addEventListener('click', function() { this.classList.toggle('active'); });
            list.appendChild(div);

            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'attendee_ids[]';
            input.value = att.id;
            input.className = 'edit-talent-hidden-input';
            document.getElementById('editTalentForm').appendChild(input);
        });
        document.getElementById('edit_talent_selected_count').textContent = editTalentSelectedAttendees.length;
    }

    function removeEditTalentHiddenInputs() {
        document.querySelectorAll('.edit-talent-hidden-input').forEach(function(el) { el.remove(); });
    }

    function filterEditTalentList(keyword) {
        var items = document.querySelectorAll('#edit_talent_available_list .list-group-item');
        keyword = keyword.toLowerCase();
        items.forEach(function(item) {
            var text = item.textContent.toLowerCase();
            item.style.display = text.indexOf(keyword) > -1 ? '' : 'none';
        });
    }

    function addEditTalentSelected() {
        var selected = document.querySelectorAll('#edit_talent_available_list .list-group-item.active');
        selected.forEach(function(item) {
            var id = item.getAttribute('data-id');
            var att = editTalentAllAttendees.find(function(a) { return String(a.id) === String(id); });
            if (att && !editTalentSelectedAttendees.find(function(s) { return String(s.id) === String(id); })) {
                editTalentSelectedAttendees.push(att);
                editTalentAllAttendees = editTalentAllAttendees.filter(function(a) { return String(a.id) !== String(id); });
            }
        });
        renderEditTalentAvailableList();
        renderEditTalentSelectedList();
    }

    function addEditTalentAll() {
        editTalentAllAttendees.forEach(function(att) { editTalentSelectedAttendees.push(att); });
        editTalentAllAttendees = [];
        renderEditTalentAvailableList();
        renderEditTalentSelectedList();
    }

    function removeEditTalentSelected() {
        var selected = document.querySelectorAll('#edit_talent_selected_list .list-group-item.active');
        selected.forEach(function(item) {
            var id = item.getAttribute('data-id');
            var att = editTalentSelectedAttendees.find(function(a) { return String(a.id) === String(id); });
            if (att) {
                editTalentAllAttendees.push(att);
                editTalentSelectedAttendees = editTalentSelectedAttendees.filter(function(a) { return String(a.id) !== String(id); });
            }
        });
        renderEditTalentAvailableList();
        renderEditTalentSelectedList();
    }

    function removeEditTalentAll() {
        editTalentSelectedAttendees.forEach(function(att) { editTalentAllAttendees.push(att); });
        editTalentSelectedAttendees = [];
        renderEditTalentAvailableList();
        renderEditTalentSelectedList();
    }

    function submitTalentForm(form) {
        var submitBtn = document.getElementById('btn_submit_talent');
        var originalHtml = submitBtn.innerHTML;
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i>Đang đăng ký...';

        var formData = new FormData(form);
        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalHtml;
            if (data.success) {
                var modal = bootstrap.Modal.getInstance(document.getElementById('addTalentModal'));
                if (modal) modal.hide();
                Toast.success(data.message || 'Đăng ký thành công!');

                // Thêm dòng mới vào bảng văn nghệ mà không cần reload
                var title = document.getElementById('talent_title').value;
                var catSelect = document.getElementById('talent_category_select');
                var categoryName = catSelect.options[catSelect.selectedIndex].text;
                var origin = document.getElementById('talent_origin').value;
                var newId = data.id;

                var container = document.getElementById('talent-entries-container');
                if (container) {
                    var noMsg = container.querySelector('.no-talent-message');
                    if (noMsg) noMsg.style.display = 'none';

                    var tableWrapper = container.querySelector('.table-responsive');
                    if (tableWrapper) tableWrapper.style.display = 'block';

                    var table = document.getElementById('talent-entries-table');
                    if (table) {
                        var tbody = table.querySelector('tbody');
                        if (tbody) {
                            var tr = document.createElement('tr');
                            tr.id = 'talent-row-' + newId;

                            var tdTitle = document.createElement('td');
                            tdTitle.className = 'talent-title';
                            tdTitle.textContent = title;

                            var tdCategory = document.createElement('td');
                            var badgeSpan = document.createElement('span');
                            badgeSpan.className = 'badge bg-info talent-category';
                            badgeSpan.textContent = categoryName;
                            tdCategory.appendChild(badgeSpan);

                            var tdOrigin = document.createElement('td');
                            tdOrigin.className = 'talent-origin';
                            tdOrigin.textContent = origin;

                            var tdActions = document.createElement('td');
                            tdActions.className = 'text-center text-nowrap';

                            var editBtn = document.createElement('button');
                            editBtn.type = 'button';
                            editBtn.className = 'btn btn-sm btn-outline-primary me-1';
                            editBtn.title = 'Sửa';
                            editBtn.innerHTML = '<i class="fa fa-pencil"></i>';
                            editBtn.onclick = (function(id) {
                                return function() { editTalentEntry(id); };
                            })(newId);

                            var deleteForm = document.createElement('form');
                            deleteForm.method = 'post';
                            deleteForm.action = window.BASE_URL + '/admin/registrations/deleteTalentEntry?id=' + newId + '&registration_id=' + registrationId;
                            deleteForm.id = 'delete-talent-form-' + newId;
                            deleteForm.style.display = 'none';

                            var deleteBtn = document.createElement('button');
                            deleteBtn.type = 'button';
                            deleteBtn.className = 'btn btn-sm btn-outline-danger';
                            deleteBtn.title = 'Xóa';
                            deleteBtn.innerHTML = '<i class="fa fa-trash"></i>';
                            deleteBtn.onclick = (function(id) {
                                return function() { confirmDeleteTalent(id); };
                            })(newId);

                            tdActions.appendChild(editBtn);
                            tdActions.appendChild(deleteForm);
                            tdActions.appendChild(deleteBtn);

                            tr.appendChild(tdTitle);
                            tr.appendChild(tdCategory);
                            tr.appendChild(tdOrigin);
                            tr.appendChild(tdActions);

                            tbody.appendChild(tr);
                        }
                    }
                }
            } else {
                Toast.error(data.error || 'Có lỗi xảy ra.');
            }
        })
        .catch(function() {
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalHtml;
            Toast.error('Lỗi kết nối.');
        });
    }

    return {
        init: init,
        resetAddModal: resetSportModal,
        resetCompetitionModal: resetCompetitionModal,
        resetSportModal: resetSportModal,
        resetMissModal: resetMissModal,
        resetTalentModal: resetTalentModal,
        viewDocument: viewDocument,
        confirmDeleteDetail: confirmDeleteDetail,
        confirmDeleteTalent: confirmDeleteTalent,
        editAttendee: editAttendee,
        confirmDeleteAttendee: confirmDeleteAttendee,
        removeAllianceProperty: removeAllianceProperty,
        confirmDeleteTeam: confirmDeleteTeam,
        confirmDeleteTeamMember: confirmDeleteTeamMember,
        removePendingSport: removePendingSport,
        removeAthleteFromSport: removeAthleteFromSport,
        editPendingSport: editPendingSport,
        editSportTeam: editSportTeam,
        editCompetitionRegistration: editCompetitionRegistration,
        deleteCompetitionRegistration: deleteCompetitionRegistration,
        editMissContestant: editMissContestant,
        deleteMissContestant: deleteMissContestant,
        removeTalentAllianceProperty: removeTalentAllianceProperty,
        loadAttendeesForEditTalent: loadAttendeesForEditTalent
    };
})();
