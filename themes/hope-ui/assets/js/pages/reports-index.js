document.addEventListener('DOMContentLoaded', function() {

    var searchRegsInput = document.getElementById('searchRegs');
    if (searchRegsInput) {
        searchRegsInput.addEventListener('keyup', function() {
            filterTable('tableRegs', this.value);
        });
    }

    var searchAttendeesInput = document.getElementById('searchAttendees');
    if (searchAttendeesInput) {
        searchAttendeesInput.addEventListener('keyup', function() {
            filterTable('tableAttendees', this.value);
        });
    }

    var searchTalentListInput = document.getElementById('searchTalentList');
    if (searchTalentListInput) {
        searchTalentListInput.addEventListener('keyup', function() {
            filterTable('tableTalentList', this.value);
        });
    }

    var searchBeautyInput = document.getElementById('searchBeauty');
    if (searchBeautyInput) {
        searchBeautyInput.addEventListener('keyup', function() {
            filterTable('tableBeauty', this.value);
        });
    }

    var searchSportsTeamsInput = document.getElementById('searchSportsTeams');
    if (searchSportsTeamsInput) {
        searchSportsTeamsInput.addEventListener('keyup', function() {
            filterTable('tableSportsTeams', this.value);
        });
    }

    function filterTable(tableId, query) {
        var table = document.getElementById(tableId);
        if (!table) return;

        var rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
        var filter = query.toLowerCase().trim();

        for (var i = 0; i < rows.length; i++) {
            var row = rows[i];
            if (row.cells.length === 1 && row.cells[0].classList.contains('text-center')) {
                continue;
            }

            var match = false;
            for (var j = 0; j < row.cells.length; j++) {
                var cellText = row.cells[j].textContent || row.cells[j].innerText;
                if (cellText.toLowerCase().indexOf(filter) > -1) {
                    match = true;
                    break;
                }
            }

            row.style.display = match ? '' : 'none';
        }
    }
});
