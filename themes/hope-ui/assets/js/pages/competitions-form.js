document.addEventListener('DOMContentLoaded', function() {
    var selectAllBtn = document.getElementById('select-all-depts');
    var deselectAllBtn = document.getElementById('deselect-all-depts');
    var checkboxes = document.querySelectorAll('.department-checkbox');

    if (selectAllBtn) {
        selectAllBtn.addEventListener('click', function() {
            checkboxes.forEach(function(cb) { cb.checked = true; });
        });
    }

    if (deselectAllBtn) {
        deselectAllBtn.addEventListener('click', function() {
            checkboxes.forEach(function(cb) { cb.checked = false; });
        });
    }
});
