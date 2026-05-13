document.addEventListener('DOMContentLoaded', function() {
    var Vietnamese = {
        weekdays: {
            shorthand: ['CN', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7'],
            longhand: ['Chủ nhật', 'Thứ hai', 'Thứ ba', 'Thứ tư', 'Thứ năm', 'Thứ sáu', 'Thứ bảy']
        },
        months: {
            shorthand: ['Th1', 'Th2', 'Th3', 'Th4', 'Th5', 'Th6', 'Th7', 'Th8', 'Th9', 'Th10', 'Th11', 'Th12'],
            longhand: ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6', 'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12']
        },
        firstDayOfWeek: 1
    };

    var config = {
        enableTime: true,
        dateFormat: 'd-m-Y H:i',
        time_24hr: true,
        allowInput: true,
        locale: Vietnamese
    };

    flatpickr('#start_time_picker', Object.assign({}, config, {
        onChange: function(selectedDates) {
            if (selectedDates.length > 0) {
                document.getElementById('start_time_hidden').value = Math.floor(selectedDates[0].getTime() / 1000);
            }
        }
    }));

    flatpickr('#end_time_picker', Object.assign({}, config, {
        onChange: function(selectedDates) {
            if (selectedDates.length > 0) {
                document.getElementById('end_time_hidden').value = Math.floor(selectedDates[0].getTime() / 1000);
            }
        }
    }));
});
