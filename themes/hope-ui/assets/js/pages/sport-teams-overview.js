document.addEventListener('DOMContentLoaded', function() {
    // Dynamic filtering of properties based on selected event
    var selectEventProperty = document.getElementById('select_event_property');
    var selectProperty = document.getElementById('select_property');

    if (selectEventProperty && selectProperty) {
        selectEventProperty.addEventListener('change', function() {
            var eventId = this.value;

            // Clear select_property
            selectProperty.innerHTML = '<option value="">-- Đang tải... --</option>';

            if (!eventId) {
                selectProperty.innerHTML = '<option value="">-- Chọn sự kiện trước --</option>';
                return;
            }

            var basePath = window.location.pathname.replace(/\/(admin|overview)$/, '');
            var url = basePath + '/getPropertiesByEvent?event_id=' + eventId;

            fetch(url)
                .then(function(res) { return res.json(); })
                .then(function(resData) {
                    if (resData.success) {
                        var html = '<option value="">-- Chọn đơn vị --</option>';
                        if (Array.isArray(resData.data) && resData.data.length > 0) {
                            resData.data.forEach(function(item) {
                                html += '<option value="' + item.id + '">' + item.name + '</option>';
                            });
                        } else {
                            html = '<option value="">-- Không có đơn vị nào tham gia --</option>';
                        }
                        selectProperty.innerHTML = html;
                    } else {
                        selectProperty.innerHTML = '<option value="">-- Lỗi tải đơn vị --</option>';
                    }
                })
                .catch(function() {
                    selectProperty.innerHTML = '<option value="">-- Lỗi kết nối --</option>';
                });
        });
    }

    // Dynamic filtering of sports based on selected event
    var selectEventSport = document.getElementById('select_event_sport');
    var selectSport = document.getElementById('select_sport');

    if (selectEventSport && selectSport) {
        selectEventSport.addEventListener('change', function() {
            var eventId = this.value;

            // Clear select_sport
            selectSport.innerHTML = '<option value="">-- Đang tải... --</option>';

            if (!eventId) {
                selectSport.innerHTML = '<option value="">-- Chọn sự kiện trước --</option>';
                return;
            }

            var url = (window.BASE_URL || '') + '/admin/registrations/getContentItems?event_id=' + eventId + '&content_type=sports';

            fetch(url)
                .then(function(res) { return res.json(); })
                .then(function(resData) {
                    if (resData.success) {
                        if (Array.isArray(resData.data) && resData.data.length > 0) {
                            selectSport.innerHTML = renderSportsTree(resData.data);
                        } else {
                            selectSport.innerHTML = '<option value="">-- Không có môn nào --</option>';
                        }
                    } else {
                        selectSport.innerHTML = '<option value="">-- Lỗi tải bộ môn --</option>';
                    }
                })
                .catch(function() {
                    selectSport.innerHTML = '<option value="">-- Lỗi kết nối --</option>';
                });
        });
    }

    function renderSportsTree(data) {
        var html = '<option value="">-- Chọn bộ môn --</option>';
        var groups = {};
        var prefixes = ['Bóng bàn', 'Bóng đá', 'Cầu lông', 'Pickerball', 'Bơi ếch', 'Bơi tự do', 'Kéo co', 'Tennis', 'Cờ vua', 'Cờ tướng'];

        data.forEach(function(item) {
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

    var btnViewByProperty = document.getElementById('btn_view_by_property');
    var btnViewBySport = document.getElementById('btn_view_by_sport');

    if (btnViewByProperty) {
        btnViewByProperty.addEventListener('click', function() {
            var eventId = document.getElementById('select_event_property').value;
            var propertyId = document.getElementById('select_property').value;

            if (!eventId) {
                Toast.warning('Vui lòng chọn sự kiện');
                return;
            }
            if (!propertyId) {
                Toast.warning('Vui lòng chọn đơn vị');
                return;
            }

            var basePath = window.location.pathname.replace(/\/(admin|overview)$/, '');
            var url = basePath + '/viewByProperty?event_id=' + eventId + '&property_id=' + propertyId;
            window.location.href = url;
        });
    }

    if (btnViewBySport) {
        btnViewBySport.addEventListener('click', function() {
            var eventId = document.getElementById('select_event_sport').value;
            var sportId = document.getElementById('select_sport').value;

            if (!eventId) {
                Toast.warning('Vui lòng chọn sự kiện');
                return;
            }
            if (!sportId) {
                Toast.warning('Vui lòng chọn bộ môn');
                return;
            }

            var basePath = window.location.pathname.replace(/\/(admin|overview)$/, '');
            var url = basePath + '/viewBySport?event_id=' + eventId + '&sport_id=' + sportId;
            window.location.href = url;
        });
    }

    // Statistics logic
    var selectEventStats = document.getElementById('select_event_stats');
    if (selectEventStats) {
        // Fetch stats on change
        selectEventStats.addEventListener('change', function() {
            loadStats(this.value);
        });

        // Auto load first event on page load
        if (selectEventStats.value) {
            loadStats(selectEventStats.value);
        }
    }

    function loadStats(eventId) {
        var loader = document.getElementById('stats-loader');
        var content = document.getElementById('stats-content');
        var tableBody = document.getElementById('stats-table-body');
        var totalAthletesEl = document.getElementById('stat-total-athletes');
        var totalTeamsEl = document.getElementById('stat-total-teams');
        var singleTeamsEl = document.getElementById('stat-single-teams');
        var allianceTeamsEl = document.getElementById('stat-alliance-teams');

        if (!eventId) {
            if (loader) loader.classList.add('d-none');
            if (content) content.classList.add('d-none');
            return;
        }

        if (loader) loader.classList.remove('d-none');
        if (content) content.classList.add('d-none');

        var basePath = window.location.pathname.replace(/\/(admin|overview)$/, '');
        var url = basePath + '/getOverviewStats?event_id=' + eventId;

        fetch(url)
            .then(function(res) { return res.json(); })
            .then(function(resData) {
                if (loader) loader.classList.add('d-none');
                if (content) content.classList.remove('d-none');

                var baseUrl = window.BASE_URL || '';
                if (baseUrl.endsWith('/')) {
                    baseUrl = baseUrl.slice(0, -1);
                }

                var btnExportExcel = document.getElementById('btn-export-excel');
                if (btnExportExcel) {
                    btnExportExcel.href = baseUrl + '/admin/reports/exportSports?event_id=' + eventId;
                }

                var btnExportTeams = document.getElementById('btn-export-teams');
                if (btnExportTeams) {
                    btnExportTeams.href = baseUrl + '/admin/reports/exportSports?event_id=' + eventId + '&teams_only=1';
                }

                var btnExportDetail = document.getElementById('btn-export-detail');
                if (btnExportDetail) {
                    btnExportDetail.href = baseUrl + '/admin/reports/exportSportsDetail?event_id=' + eventId;
                }

                if (resData.success) {
                    if (totalAthletesEl) totalAthletesEl.textContent = resData.total_athletes || 0;
                    if (totalTeamsEl) totalTeamsEl.textContent = resData.total_teams || 0;
                    if (singleTeamsEl) singleTeamsEl.textContent = resData.single_team_count || 0;
                    if (allianceTeamsEl) allianceTeamsEl.textContent = resData.alliance_team_count || 0;

                    var html = '';
                    if (Array.isArray(resData.sports) && resData.sports.length > 0) {
                        resData.sports.forEach(function(sport, index) {
                            html += '<tr>' +
                                '<td class="ps-3 fw-bold text-muted">' + (index + 1) + '</td>' +
                                '<td class="fw-bold text-dark">' + sport.name + '</td>' +
                                '<td class="text-center fw-bold text-primary">' + (sport.team_count || 0) + '</td>' +
                                '<td class="text-center fw-bold text-success pe-3">' + (sport.athlete_count || 0) + '</td>' +
                                '</tr>';
                        });
                    } else {
                        html = '<tr><td colspan="4" class="text-center text-muted py-4">Chưa có nội dung thi đấu hoặc đội nào đăng ký.</td></tr>';
                    }
                    if (tableBody) tableBody.innerHTML = html;

                    // Render sports summary table
                    var summaryTable = document.getElementById('sports-summary-table');
                    if (summaryTable) {
                        var sports = resData.sports || [];
                        var sportsReportData = resData.sports_report_data || {};
                        var regionalMap = resData.regional_map || {};
                        var propertyRegionalMap = resData.property_regional_map || {};

                        if (sports.length === 0) {
                            summaryTable.innerHTML = '<tbody><tr><td class="text-center text-muted py-5"><i class="fa fa-trophy fa-3x mb-3 text-white-50 d-block"></i>Chưa có môn thể thao nào được đăng ký cho sự kiện này.</td></tr></tbody>';
                        } else {
                            var tHtml = '';
                            
                            // 1. Render Header
                            tHtml += '<thead>';
                            tHtml += '<tr>';
                            tHtml += '<th rowspan="2" class="text-center align-middle col-sticky-stt">STT</th>';
                            tHtml += '<th rowspan="2" class="text-center align-middle col-sticky-region">Cụm</th>';
                            tHtml += '<th rowspan="2" class="align-middle col-sticky-property">Tên ĐV</th>';
                            sports.forEach(function(sport) {
                                tHtml += '<th colspan="3" class="text-center sport-header">' + sport.name + '</th>';
                            });
                            tHtml += '<th colspan="2" class="text-center" style="background:#198754;color:#fff;font-weight:bold;">TỔNG CỘNG</th>';
                            tHtml += '</tr>';

                            tHtml += '<tr>';
                            sports.forEach(function(sport) {
                                tHtml += '<th class="text-center col-num">Đội</th>';
                                tHtml += '<th class="text-center col-num">VĐV</th>';
                                tHtml += '<th class="text-center col-note">Ghi chú</th>';
                            });
                            tHtml += '<th class="text-center col-num" style="background:#d1fae5;">Đội</th>';
                            tHtml += '<th class="text-center col-num" style="background:#d1fae5;">VĐV</th>';
                            tHtml += '</tr>';
                            tHtml += '</thead>';
                            
                            // 2. Render Body
                            tHtml += '<tbody>';
                            
                            var grandTotals = {};
                            sports.forEach(function(sport) {
                                grandTotals[sport.id] = { team_count: 0, member_count: 0 };
                            });
                            
                            // Sort regions
                            var regionIds = Object.keys(sportsReportData).sort(function(a, b) {
                                return parseInt(a) - parseInt(b);
                            });
                            
                            var rowStt = 1;

                            regionIds.forEach(function(regionId) {
                                var propData = sportsReportData[regionId] || {};
                                var regionName = regionalMap[regionId] || 'Chưa phân cụm';

                                var regionTotals = {};
                                sports.forEach(function(sport) {
                                    regionTotals[sport.id] = { team_count: 0, member_count: 0 };
                                });

                                // Sort properties by their code
                                var propIds = Object.keys(propData).sort(function(a, b) {
                                    var codeA = (propertyRegionalMap[a] && propertyRegionalMap[a].code) || '';
                                    var codeB = (propertyRegionalMap[b] && propertyRegionalMap[b].code) || '';
                                    return codeA.localeCompare(codeB, undefined, {numeric: true, sensitivity: 'base'});
                                });

                                var regionRowCount = propIds.length;

                                propIds.forEach(function(propId, propIndex) {
                                    var sportsData = propData[propId] || {};
                                    var propInfo = propertyRegionalMap[propId];
                                    var propName = propInfo ? propInfo.name : 'Không xác định';

                                    tHtml += '<tr>';
                                    tHtml += '<td class="text-center fw-bold text-muted col-sticky-stt">' + rowStt++ + '</td>';

                                    // Merge region cell for first row of each region
                                    if (propIndex === 0) {
                                        tHtml += '<td class="small align-middle col-sticky-region" rowspan="' + regionRowCount + '">' + regionName + '</td>';
                                    }

                                    tHtml += '<td class="fw-bold text-dark col-sticky-property">' + propName + '</td>';

                                    var rowTotalTeams = 0;
                                    var rowTotalMembers = 0;
                                    sports.forEach(function(sport) {
                                        var spData = sportsData[sport.id] || { team_count: 0, member_count: 0, note: '' };
                                        var teamCount = spData.team_count || 0;
                                        var memberCount = spData.member_count || 0;
                                        var note = spData.note || '';

                                        regionTotals[sport.id].team_count += teamCount;
                                        regionTotals[sport.id].member_count += memberCount;
                                        grandTotals[sport.id].team_count += teamCount;
                                        grandTotals[sport.id].member_count += memberCount;

                                        rowTotalTeams += teamCount;
                                        rowTotalMembers += memberCount;

                                        tHtml += '<td class="col-num ' + (teamCount ? 'text-primary fw-bold' : 'text-muted') + '">' + (teamCount || '-') + '</td>';
                                        tHtml += '<td class="col-num ' + (memberCount ? 'text-success fw-bold' : 'text-muted') + '">' + (memberCount || '-') + '</td>';
                                        tHtml += '<td class="col-note text-muted">' + (note || '-') + '</td>';
                                    });
                                    tHtml += '<td class="col-num fw-bold text-primary" style="background:#d1fae5;">' + (rowTotalTeams || '-') + '</td>';
                                    tHtml += '<td class="col-num fw-bold text-success" style="background:#d1fae5;">' + (rowTotalMembers || '-') + '</td>';
                                    tHtml += '</tr>';
                                });

                                // Region subtotal
                                tHtml += '<tr class="table-warning">';
                                tHtml += '<td colspan="3" class="text-end fw-bold col-sticky-total">Tổng ' + regionName + ':</td>';
                                var regionRowTotalTeams = 0;
                                var regionRowTotalMembers = 0;
                                sports.forEach(function(sp) {
                                    tHtml += '<td class="col-num fw-bold">' + regionTotals[sp.id].team_count + '</td>';
                                    tHtml += '<td class="col-num fw-bold">' + regionTotals[sp.id].member_count + '</td>';
                                    tHtml += '<td class="col-note"></td>';
                                    regionRowTotalTeams += regionTotals[sp.id].team_count;
                                    regionRowTotalMembers += regionTotals[sp.id].member_count;
                                });
                                tHtml += '<td class="col-num fw-bold">' + regionRowTotalTeams + '</td>';
                                tHtml += '<td class="col-num fw-bold">' + regionRowTotalMembers + '</td>';
                                tHtml += '</tr>';
                            });
                            
                            tHtml += '</tbody>';
                            
                            // 3. Render Footer (Grand Total)
                            tHtml += '<tr class="table-success">';
                            tHtml += '<td colspan="3" class="text-end fw-bold fs-6 col-sticky-total">TỔNG CỘNG:</td>';
                            var grandRowTotalTeams = 0;
                            var grandRowTotalMembers = 0;
                            sports.forEach(function(sp) {
                                tHtml += '<td class="col-num fw-bold fs-6">' + grandTotals[sp.id].team_count + '</td>';
                                tHtml += '<td class="col-num fw-bold fs-6">' + grandTotals[sp.id].member_count + '</td>';
                                tHtml += '<td class="col-note"></td>';
                                grandRowTotalTeams += grandTotals[sp.id].team_count;
                                grandRowTotalMembers += grandTotals[sp.id].member_count;
                            });
                            tHtml += '<td class="col-num fw-bold fs-6">' + grandRowTotalTeams + '</td>';
                            tHtml += '<td class="col-num fw-bold fs-6">' + grandRowTotalMembers + '</td>';
                            tHtml += '</tr>';
                            tHtml += '</tbody>';
                            
                            summaryTable.innerHTML = tHtml;
                        }
                    }
                } else {
                    if (tableBody) tableBody.innerHTML = '<tr><td colspan="4" class="text-center text-danger py-4">Không thể tải dữ liệu thống kê.</td></tr>';
                    var summaryTable = document.getElementById('sports-summary-table');
                    if (summaryTable) {
                        summaryTable.innerHTML = '<tbody><tr><td class="text-center text-danger py-4">Không thể tải dữ liệu thống kê.</td></tr></tbody>';
                    }
                }
            })
            .catch(function() {
                if (loader) loader.classList.add('d-none');
                if (content) content.classList.remove('d-none');
                if (tableBody) tableBody.innerHTML = '<tr><td colspan="4" class="text-center text-danger py-4">Lỗi kết nối máy chủ.</td></tr>';
                var summaryTable = document.getElementById('sports-summary-table');
                if (summaryTable) {
                    summaryTable.innerHTML = '<tbody><tr><td class="text-center text-danger py-4">Lỗi kết nối máy chủ.</td></tr></tbody>';
                }
            });
    }
});
