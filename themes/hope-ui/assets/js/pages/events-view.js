function moveSelected(fromId, toId) {
    var from = document.getElementById(fromId);
    var to = document.getElementById(toId);
    var selected = from.querySelectorAll('option:checked');
    selected.forEach(function(opt) {
        to.appendChild(opt);
    });
    sortSelect(to);
    updateCount();
}

function moveAll(fromId, toId) {
    var from = document.getElementById(fromId);
    var to = document.getElementById(toId);
    var options = from.querySelectorAll('option');
    options.forEach(function(opt) {
        to.appendChild(opt);
    });
    sortSelect(to);
    updateCount();
}

function sortSelect(select) {
    var options = Array.from(select.options);
    options.sort(function(a, b) {
        return a.text.localeCompare(b.text, 'vi');
    });
    options.forEach(function(opt) {
        select.appendChild(opt);
    });
}

function updateCount() {
    var count = document.getElementById('selected-units').options.length;
    document.getElementById('selected-count').textContent = count;
}

function selectAllSelected() {
    var select = document.getElementById('selected-units');
    for (var i = 0; i < select.options.length; i++) {
        select.options[i].selected = true;
    }
}

document.addEventListener('DOMContentLoaded', function() {
    var formAddSport = document.getElementById('formAddSport');
    if (formAddSport) {
        formAddSport.addEventListener('submit', function() {
            var btnSubmit = document.getElementById('btnSubmitSport');
            var btnCancel = document.getElementById('btnCancelSport');
            var spinner = btnSubmit.querySelector('.spinner-border');
            var btnText = btnSubmit.querySelector('.btn-text');

            spinner.classList.remove('d-none');
            btnText.textContent = 'Đang xử lý...';
            btnSubmit.disabled = true;
            btnCancel.disabled = true;
        });
    }
});
