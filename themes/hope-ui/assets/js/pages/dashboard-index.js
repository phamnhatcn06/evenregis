document.addEventListener('DOMContentLoaded', function() {
    var eventSelect = document.getElementById('filter-event');
    var periodSelect = document.getElementById('filter-period');
    var btnFilter = document.getElementById('btn-filter');
    var btnReset = document.getElementById('btn-reset');
    var statsUrl = document.getElementById('stats-url');

    if (!eventSelect || !periodSelect) return;

    var apiUrl = eventSelect.getAttribute('data-api-url');
    var apiKey = eventSelect.getAttribute('data-api-key');

    eventSelect.addEventListener('change', function() {
        var eventId = this.value;
        periodSelect.innerHTML = '<option value="">-- Đang tải... --</option>';
        periodSelect.disabled = true;

        if (!eventId) {
            periodSelect.innerHTML = '<option value="">-- Chọn sự kiện trước --</option>';
            return;
        }

        fetch(apiUrl + '?event_id=' + eventId, {
            headers: {
                'Authorization': 'Bearer ' + apiKey,
                'Accept': 'application/json'
            }
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            periodSelect.innerHTML = '<option value="">-- Tất cả đợt --</option>';
            var items = data.data || data;
            if (Array.isArray(items) && items.length > 0) {
                items.forEach(function(p) {
                    var option = document.createElement('option');
                    option.value = p.id;
                    option.textContent = p.name;
                    periodSelect.appendChild(option);
                });
            }
            periodSelect.disabled = false;
        })
        .catch(function() {
            periodSelect.innerHTML = '<option value="">-- Lỗi tải dữ liệu --</option>';
        });
    });

    if (btnFilter) {
        btnFilter.addEventListener('click', function() {
            loadStats();
        });
    }

    if (btnReset) {
        btnReset.addEventListener('click', function() {
            eventSelect.value = '';
            periodSelect.innerHTML = '<option value="">-- Chọn sự kiện trước --</option>';
            periodSelect.disabled = true;
            loadStats();
        });
    }

    function loadStats() {
        var eventId = eventSelect.value;
        var periodId = periodSelect.value;

        btnFilter.disabled = true;
        btnFilter.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i>Đang tải...';

        var url = statsUrl.value + '?event_id=' + eventId + '&period_id=' + periodId;

        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(response) { return response.json(); })
        .then(function(result) {
            if (result.success && result.data) {
                updateDashboard(result.data);
            }
            btnFilter.disabled = false;
            btnFilter.innerHTML = '<i class="fa fa-filter me-1"></i>Lọc dữ liệu';
        })
        .catch(function() {
            btnFilter.disabled = false;
            btnFilter.innerHTML = '<i class="fa fa-filter me-1"></i>Lọc dữ liệu';
            if (typeof Toast !== 'undefined') {
                Toast.error('Lỗi tải dữ liệu');
            }
        });
    }

    function updateDashboard(data) {
        var totalProps = data.total_properties || 0;
        var registeredProps = data.registered_properties || 0;

        setText('stat-total-properties', totalProps);
        setText('stat-registered-properties', registeredProps);
        setText('stat-unregistered-properties', totalProps - registeredProps);
        setText('stat-total-attendees', data.total_attendees || 0);

        var byStatus = data.registrations_by_status || {};
        setText('stat-status-draft', byStatus.draft || 0);
        setText('stat-status-submitted', byStatus.submitted || 0);
        setText('stat-status-approved', byStatus.approved || 0);
        setText('stat-status-rejected', byStatus.rejected || 0);

        updateTable('table-notstarted', 'stat-count-notstarted', data.properties_not_started || []);
        updateTable('table-draft', 'stat-count-draft', data.properties_draft || []);
        updateTable('table-submitted', 'stat-count-submitted', data.properties_submitted || []);

        if (window.dashboardStatsData) {
            window.dashboardStatsData.notstarted = data.properties_not_started || [];
            window.dashboardStatsData.draft = data.properties_draft || [];
            window.dashboardStatsData.submitted = data.properties_submitted || [];
        }
    }

    function setText(id, value) {
        var el = document.getElementById(id);
        if (el) el.textContent = value;
    }

    function updateTable(containerId, countId, items) {
        var container = document.getElementById(containerId);
        var countEl = document.getElementById(countId);

        if (countEl) countEl.textContent = items.length;

        if (!container) return;

        if (items.length === 0) {
            container.innerHTML = '<div class="p-3 text-center text-muted small">Không có</div>';
            return;
        }

        var html = '<div class="table-responsive" style="max-height: 280px; overflow-y: auto;">' +
            '<table class="table table-sm table-hover mb-0">' +
            '<thead class="table-light sticky-top"><tr><th>Mã</th><th>Tên đơn vị</th></tr></thead><tbody>';

        items.forEach(function(p) {
            html += '<tr><td><code>' + escapeHtml(p.code || '') + '</code></td>' +
                '<td class="small">' + escapeHtml(p.name || '') + '</td></tr>';
        });

        html += '</tbody></table></div>';
        container.innerHTML = html;
    }

    function escapeHtml(text) {
        var div = document.createElement('div');
        div.appendChild(document.createTextNode(text));
        return div.innerHTML;
    }

    // Export to Excel handlers
    document.addEventListener('click', function(e) {
        var btn = e.target.closest('.btn-export-excel');
        if (btn) {
            e.preventDefault();

            if (btn.disabled) return;

            var type = btn.getAttribute('data-type');
            var items = [];
            var title = 'Danh_sach';

            if (window.dashboardStatsData && window.dashboardStatsData[type]) {
                items = window.dashboardStatsData[type];
            }

            if (!items || items.length === 0) {
                alert('Không có dữ liệu để xuất');
                return;
            }

            // Set loading state on button
            var originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i>Đang xuất...';

            if (type === 'notstarted') {
                title = 'Danh_sach_Chua_khoi_tao';
            } else if (type === 'draft') {
                title = 'Danh_sach_Chua_gui';
            } else if (type === 'submitted') {
                title = 'Danh_sach_Da_gui';
            }

            // Short delay to show loading state to user
            setTimeout(function() {
                exportToExcel(title, items);
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }, 600);
        }
    });

    function exportToExcel(title, items) {
        if (!items || items.length === 0) {
            alert('Không có dữ liệu để xuất');
            return;
        }

        var csvContent = "\uFEFF"; // UTF-8 BOM to read correctly in Excel
        csvContent += "Mã,Tên đơn vị\n";

        items.forEach(function(item) {
            var code = (item.code || '').replace(/"/g, '""');
            var name = (item.name || '').replace(/"/g, '""');
            csvContent += '"' + code + '","' + name + '"\n';
        });

        var blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        var url = URL.createObjectURL(blob);
        var link = document.createElement("a");
        link.setAttribute("href", url);
        link.setAttribute("download", title + "_" + getFormattedDate() + ".csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    function getFormattedDate() {
        var d = new Date();
        var month = '' + (d.getMonth() + 1);
        var day = '' + d.getDate();
        var year = d.getFullYear();
        if (month.length < 2) month = '0' + month;
        if (day.length < 2) day = '0' + day;
        return [year, month, day].join('');
    }
});
