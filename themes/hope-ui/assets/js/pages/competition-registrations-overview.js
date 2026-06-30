(function() {
    var filterEvent = document.getElementById('filter-event');
    var filterOrganization = document.getElementById('filter-organization');
    var statsContainer = document.getElementById('competitions-stats-container');
    var orgStatsContainer = document.getElementById('organization-stats-container');
    var statTotal = document.getElementById('stat-total');
    var statConfirmed = document.getElementById('stat-confirmed');
    var statPending = document.getElementById('stat-pending');
    var statCompetitions = document.getElementById('stat-competitions');
    var statRegSubmitted = document.getElementById('stat-reg-submitted');
    var statRegNotSubmitted = document.getElementById('stat-reg-not-submitted');
    var statRegApproved = document.getElementById('stat-reg-approved');
    var statRegNotApproved = document.getElementById('stat-reg-not-approved');

    function loadStats() {
        var eventId = filterEvent ? filterEvent.value : '';
        statsContainer.innerHTML = '<div class="text-center py-4"><i class="fa fa-spinner fa-spin fa-2x"></i><p class="mt-2">Đang tải dữ liệu...</p></div>';
        loadOrganizationStats();

        var basePath = window.location.pathname.replace(/\/(admin|overview)$/, '');
        var url = basePath + '/getOverviewStats';
        if (eventId) {
            url += '?event_id=' + eventId;
        }

        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.success) {
                statTotal.textContent = data.total_contestants || 0;
                statConfirmed.textContent = data.confirmed_count || 0;
                statPending.textContent = data.pending_count || 0;
                if (statCompetitions) statCompetitions.textContent = data.competitions ? data.competitions.length : 0;

                var regStats = data.registration_stats || {};
                if (statRegSubmitted) statRegSubmitted.textContent = regStats.submitted || 0;
                if (statRegNotSubmitted) statRegNotSubmitted.textContent = regStats.not_submitted || 0;
                if (statRegApproved) statRegApproved.textContent = regStats.approved || 0;
                if (statRegNotApproved) statRegNotApproved.textContent = regStats.not_approved || 0;

                if (data.competitions && data.competitions.length > 0) {
                    var html = '<div class="table-responsive">';
                    html += '<table class="table table-bordered table-hover">';
                    html += '<thead class="table-light"><tr>';
                    html += '<th style="width:5%">#</th>';
                    html += '<th>Tên nghiệp vụ</th>';
                    html += '<th style="width:15%">Số thí sinh</th>';
                    html += '<th style="width:15%">Thao tác</th>';
                    html += '</tr></thead><tbody>';

                    data.competitions.forEach(function(comp, idx) {
                        html += '<tr>';
                        html += '<td>' + (idx + 1) + '</td>';
                        html += '<td><strong>' + comp.name + '</strong></td>';
                        html += '<td><span class="badge bg-primary">' + comp.contestant_count + '</span></td>';
                        html += '<td>';
                        var viewUrl = basePath + '/viewByCompetition?event_id=' + eventId + '&competition_id=' + comp.id;
                        html += '<a href="' + viewUrl + '" class="btn btn-sm btn-info" title="Xem chi tiết"><i class="fa fa-eye"></i> Xem</a>';
                        html += '</td>';
                        html += '</tr>';
                    });

                    html += '</tbody></table></div>';
                    statsContainer.innerHTML = html;
                } else {
                    statsContainer.innerHTML = '<div class="alert alert-info"><i class="fa fa-info-circle me-2"></i>Chưa có thí sinh đăng ký thi nghiệp vụ.</div>';
                }
            } else {
                statsContainer.innerHTML = '<div class="alert alert-danger">Có lỗi khi tải dữ liệu</div>';
            }
        })
        .catch(function() {
            statsContainer.innerHTML = '<div class="alert alert-danger">Lỗi kết nối server</div>';
        });
    }

    function loadOrganizationStats() {
        var eventId = filterEvent ? filterEvent.value : '';
        var orgId = filterOrganization ? filterOrganization.value : '';

        if (!orgStatsContainer) return;

        orgStatsContainer.innerHTML = '<div class="text-center py-4"><i class="fa fa-spinner fa-spin fa-2x"></i><p class="mt-2">Đang tải dữ liệu...</p></div>';

        var basePath = window.location.pathname.replace(/\/(admin|overview)$/, '');
        var url = basePath + '/getOrganizationStats?event_id=' + eventId;
        if (orgId) {
            url += '&organization_id=' + orgId;
        }

        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.success && data.organizations && data.organizations.length > 0) {
                var html = '<div class="table-responsive">';
                html += '<table class="table table-bordered table-hover table-sm">';
                html += '<thead class="table-light"><tr>';
                html += '<th style="width:5%">#</th>';
                html += '<th>Đơn vị</th>';
                html += '<th style="width:15%">Cụm</th>';
                html += '<th style="width:15%">Số thí sinh</th>';
                html += '<th style="width:15%">Đã xác nhận</th>';
                html += '<th style="width:15%" class="text-center">Thao tác</th>';
                html += '</tr></thead><tbody>';

                data.organizations.forEach(function(org, idx) {
                    html += '<tr>';
                    html += '<td>' + (idx + 1) + '</td>';
                    html += '<td>' + org.name + '</td>';
                    html += '<td>' + (org.region_name || '-') + '</td>';
                    html += '<td><span class="badge bg-primary">' + org.contestant_count + '</span></td>';
                    html += '<td><span class="badge bg-success">' + org.confirmed_count + '</span></td>';
                    html += '<td class="text-center"><button type="button" class="btn btn-sm btn-info btn-view-org-contestants" data-id="' + org.id + '" data-name="' + org.name + '"><i class="fa fa-eye"></i> Chi tiết</button></td>';
                    html += '</tr>';
                });

                html += '</tbody></table></div>';
                orgStatsContainer.innerHTML = html;

                // Bind click events
                orgStatsContainer.querySelectorAll('.btn-view-org-contestants').forEach(function(btn) {
                    btn.addEventListener('click', function() {
                        var propertyId = this.getAttribute('data-id');
                        var propertyName = this.getAttribute('data-name');
                        showOrgContestantsModal(propertyId, propertyName);
                    });
                });
            } else {
                orgStatsContainer.innerHTML = '<div class="alert alert-info"><i class="fa fa-info-circle me-2"></i>Chưa có dữ liệu.</div>';
            }
        })
        .catch(function() {
            orgStatsContainer.innerHTML = '<div class="alert alert-danger">Lỗi kết nối server</div>';
        });
    }

    function showOrgContestantsModal(propertyId, propertyName) {
        var eventId = filterEvent ? filterEvent.value : '';
        var modalTitle = document.getElementById('modalViewOrgContestantsTitleName');
        var modalBody = document.getElementById('modalViewOrgContestantsBody');
        var modalElement = document.getElementById('modalViewOrgContestants');
        if (!modalElement) return;
        
        var modal = new bootstrap.Modal(modalElement);

        if (modalTitle) modalTitle.textContent = propertyName;
        if (modalBody) {
            modalBody.innerHTML = '<tr><td colspan="6" class="text-center py-4"><i class="fa fa-spinner fa-spin fa-2x"></i><p class="mt-2 mb-0">Đang tải...</p></td></tr>';
        }
        modal.show();

        var basePath = window.location.pathname.replace(/\/(admin|overview)$/, '');
        var url = basePath + '/ajaxGetPropertyContestants?event_id=' + eventId + '&property_id=' + propertyId;

        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(res) { return res.json(); })
        .then(function(data) {
            if (data.success && data.contestants) {
                var html = '';
                if (data.contestants.length > 0) {
                    data.contestants.forEach(function(c, idx) {
                        var compsHtml = '';
                        if (Array.isArray(c.registered_competitions) && c.registered_competitions.length > 0) {
                            c.registered_competitions.forEach(function(compName) {
                                compsHtml += '<span class="badge bg-info me-1 mb-1">' + compName + '</span>';
                            });
                        } else {
                            compsHtml = '-';
                        }
                        
                        html += '<tr>';
                        html += '<td class="text-center">' + (idx + 1) + '</td>';
                        html += '<td class="text-center"><strong>' + (c.candidate_number || '-') + '</strong></td>';
                        html += '<td><strong>' + c.name + '</strong></td>';
                        html += '<td>' + (c.position || '-') + '</td>';
                        html += '<td>' + compsHtml + '</td>';
                        html += '<td class="text-center">' + c.status_label + '</td>';
                        html += '</tr>';
                    });
                } else {
                    html = '<tr><td colspan="6" class="text-center py-4 text-muted">Không có thí sinh nào đăng ký.</td></tr>';
                }
                if (modalBody) modalBody.innerHTML = html;
            } else {
                if (modalBody) modalBody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-danger">' + (data.message || 'Lỗi tải danh sách thí sinh.') + '</td></tr>';
            }
        })
        .catch(function() {
            if (modalBody) modalBody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-danger">Lỗi kết nối máy chủ.</td></tr>';
        });
    }

    if (filterEvent) {
        filterEvent.addEventListener('change', loadStats);
    }

    if (filterOrganization) {
        filterOrganization.addEventListener('change', loadOrganizationStats);
    }

    // Xuất Excel tất cả nghiệp vụ
    var btnExportAll = document.getElementById('btn-export-all-excel');
    if (btnExportAll) {
        btnExportAll.addEventListener('click', function(e) {
            e.preventDefault();
            var eventId = filterEvent ? filterEvent.value : '';
            var basePath = window.location.pathname.replace(/\/(admin|overview)$/, '');
            var url = basePath + '/exportAllExcel?event_id=' + eventId;
            window.location.href = url;
        });
    }

    loadStats();
})();
