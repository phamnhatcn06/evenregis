document.addEventListener('DOMContentLoaded', function() {
    var propertySelect = document.getElementById('property-select');
    var attendeeSelect = document.getElementById('attendee-select');

    if (propertySelect && attendeeSelect) {
        propertySelect.addEventListener('change', function() {
            loadFemaleAttendees(this.value);
        });
    }

    function loadFemaleAttendees(propertyId) {
        if (!propertyId) {
            attendeeSelect.innerHTML = '<option value="">-- Chọn đơn vị trước --</option>';
            return;
        }

        attendeeSelect.innerHTML = '<option value="">-- Đang tải... --</option>';

        var url = window.location.pathname.replace(/\/create$|\/update\/\d+$/, '/getFemaleAttendees') + '?propertyId=' + propertyId;

        fetch(url)
            .then(function(response) { return response.json(); })
            .then(function(data) {
                attendeeSelect.innerHTML = '<option value="">-- Chọn thí sinh --</option>';
                if (data.success && data.data && data.data.length > 0) {
                    data.data.forEach(function(att) {
                        var option = document.createElement('option');
                        option.value = att.id;
                        option.textContent = att.name + (att.staff_code ? ' (' + att.staff_code + ')' : '');
                        attendeeSelect.appendChild(option);
                    });
                } else {
                    attendeeSelect.innerHTML = '<option value="">-- Không có nhân viên nữ --</option>';
                }
            })
            .catch(function(error) {
                console.error('Error:', error);
                attendeeSelect.innerHTML = '<option value="">-- Lỗi tải dữ liệu --</option>';
            });
    }
});
