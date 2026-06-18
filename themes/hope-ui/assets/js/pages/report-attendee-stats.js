/**
 * Report Attendee Stats Page JS
 */
document.addEventListener('DOMContentLoaded', function() {
    var table = document.getElementById('attendeeStatsTable');
    if (!table) return;

    // Highlight rows on hover
    var rows = table.querySelectorAll('tbody tr:not(.table-warning)');
    rows.forEach(function(row) {
        row.addEventListener('mouseenter', function() {
            this.style.backgroundColor = '#e8f4fd';
        });
        row.addEventListener('mouseleave', function() {
            this.style.backgroundColor = '';
        });
    });
});
