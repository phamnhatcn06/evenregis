document.addEventListener('DOMContentLoaded', function() {
    var isAllianceSelect = document.getElementById('is-alliance-select');
    var propertySelect = document.getElementById('property-select');
    var allianceSection = document.getElementById('alliance-section');
    var allianceOrgList = document.getElementById('alliance-org-list');

    if (isAllianceSelect && allianceSection) {
        isAllianceSelect.addEventListener('change', function() {
            if (this.value === '1') {
                allianceSection.style.display = 'block';
                loadSameRegionalProperties();
            } else {
                allianceSection.style.display = 'none';
            }
        });

        if (isAllianceSelect.value === '1') {
            allianceSection.style.display = 'block';
        }
    }

    if (propertySelect) {
        propertySelect.addEventListener('change', function() {
            if (isAllianceSelect && isAllianceSelect.value === '1') {
                loadSameRegionalProperties();
            }
        });
    }

    function loadSameRegionalProperties() {
        var propertyId = propertySelect ? propertySelect.value : '';
        if (!propertyId) {
            allianceOrgList.innerHTML = '<p class="text-muted">Vui lòng chọn đơn vị chính trước</p>';
            return;
        }

        allianceOrgList.innerHTML = '<p class="text-muted"><i class="fa fa-spinner fa-spin me-2"></i>Đang tải...</p>';

        var url = window.location.pathname.replace(/\/create$|\/update\/\d+$/, '/getSameRegionalProperties') + '?propertyId=' + propertyId;

        fetch(url)
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success && data.data && data.data.length > 0) {
                    var html = '<div class="row">';
                    data.data.forEach(function(prop) {
                        html += '<div class="col-md-6 mb-2">';
                        html += '<div class="form-check">';
                        html += '<input class="form-check-input" type="checkbox" name="alliance_org_ids[]" value="' + prop.id + '" id="alliance-' + prop.id + '">';
                        html += '<label class="form-check-label" for="alliance-' + prop.id + '">' + prop.name + ' (' + prop.code + ')</label>';
                        html += '</div></div>';
                    });
                    html += '</div>';
                    allianceOrgList.innerHTML = html;
                } else {
                    allianceOrgList.innerHTML = '<p class="text-warning"><i class="fa fa-exclamation-triangle me-2"></i>Không có đơn vị cùng khu vực</p>';
                }
            })
            .catch(function(error) {
                console.error('Error:', error);
                allianceOrgList.innerHTML = '<p class="text-danger"><i class="fa fa-times me-2"></i>Lỗi tải dữ liệu</p>';
            });
    }
});
