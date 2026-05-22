document.addEventListener('DOMContentLoaded', function() {
    var allowAllianceToggle = document.getElementById('allow-alliance-toggle');
    var maxAllianceWrapper = document.getElementById('max-alliance-wrapper');

    if (allowAllianceToggle && maxAllianceWrapper) {
        allowAllianceToggle.addEventListener('change', function() {
            maxAllianceWrapper.style.display = this.checked ? 'block' : 'none';
        });
    }
});
