document.addEventListener('DOMContentLoaded', function() {
    var availableOrgs = document.getElementById('availableOrgs');
    var assignedOrgs = document.getElementById('assignedOrgs');
    var hiddenInputs = document.getElementById('hiddenInputs');

    // Load all properties when modal opens
    var modal = document.getElementById('assignOrganizationsModal');
    modal.addEventListener('show.bs.modal', function () {
        loadAvailableProperties();
    });

    function loadAvailableProperties() {
        fetch(allPropertiesUrl)
            .then(function(response) { return response.json(); })
            .then(function(data) {
                availableOrgs.innerHTML = '';
                if (data.success && data.data) {
                    var properties = data.data;
                    properties.forEach(function(prop) {
                        if (assignedOrgIds.indexOf(parseInt(prop.id)) === -1) {
                            var option = document.createElement('option');
                            option.value = prop.id;
                            option.textContent = prop.code + ' - ' + prop.name;
                            availableOrgs.appendChild(option);
                        }
                    });
                }
            })
            .catch(function(err) {
                console.error('Error loading properties:', err);
            });
    }

    // Move selected items to assigned
    document.getElementById('btnAddSelected').addEventListener('click', function() {
        moveSelected(availableOrgs, assignedOrgs);
    });

    // Move all items to assigned
    document.getElementById('btnAddAll').addEventListener('click', function() {
        moveAll(availableOrgs, assignedOrgs);
    });

    // Remove selected items from assigned
    document.getElementById('btnRemoveSelected').addEventListener('click', function() {
        moveSelected(assignedOrgs, availableOrgs);
    });

    // Remove all items from assigned
    document.getElementById('btnRemoveAll').addEventListener('click', function() {
        moveAll(assignedOrgs, availableOrgs);
    });

    function moveSelected(source, target) {
        var selected = Array.from(source.selectedOptions);
        selected.forEach(function(option) {
            target.appendChild(option);
        });
        sortOptions(target);
    }

    function moveAll(source, target) {
        var options = Array.from(source.options);
        options.forEach(function(option) {
            target.appendChild(option);
        });
        sortOptions(target);
    }

    function sortOptions(select) {
        var options = Array.from(select.options);
        options.sort(function(a, b) {
            var codeA = a.textContent.split(' - ')[0];
            var codeB = b.textContent.split(' - ')[0];
            return codeA.localeCompare(codeB);
        });
        select.innerHTML = '';
        options.forEach(function(option) {
            select.appendChild(option);
        });
    }

    // Before form submit, create hidden inputs for assigned orgs
    document.getElementById('btnSave').addEventListener('click', function(e) {
        hiddenInputs.innerHTML = '';
        var options = Array.from(assignedOrgs.options);
        options.forEach(function(option) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'organization_ids[]';
            input.value = option.value;
            hiddenInputs.appendChild(input);
        });
    });
});
