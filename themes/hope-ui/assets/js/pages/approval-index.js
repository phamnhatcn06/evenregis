var ApprovalIndex = (function() {
    function init() {
        bindSelectAll();
        bindRowCheckboxes();
    }

    function bindSelectAll() {
        var selectAll = document.getElementById('select-all');
        if (!selectAll) return;

        selectAll.addEventListener('change', function() {
            var checkboxes = document.querySelectorAll('.row-checkbox');
            checkboxes.forEach(function(cb) {
                cb.checked = selectAll.checked;
            });
            updateSelectedCount();
        });
    }

    function bindRowCheckboxes() {
        var checkboxes = document.querySelectorAll('.row-checkbox');
        checkboxes.forEach(function(cb) {
            cb.addEventListener('change', function() {
                updateSelectedCount();
            });
        });
    }

    function updateSelectedCount() {
        var checked = document.querySelectorAll('.row-checkbox:checked');
        var countSpan = document.getElementById('selected-count');
        var btn = document.getElementById('btn-bulk-approve');

        if (countSpan) {
            countSpan.textContent = checked.length;
        }
        if (btn) {
            btn.disabled = (checked.length === 0);
        }
    }

    return {
        init: init
    };
})();

function approveAttendee(id) {
    Swal.fire({
        title: 'Xác nhận phê duyệt',
        text: 'Bạn có chắc chắn muốn phê duyệt người tham dự này?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#28a745',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Phê duyệt',
        cancelButtonText: 'Hủy'
    }).then(function(result) {
        if (result.isConfirmed) {
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = window.BASE_URL + 'admin/approval/approve/id/' + id;
            document.body.appendChild(form);
            form.submit();
        }
    });
}

function rejectAttendee(id) {
    Swal.fire({
        title: 'Từ chối người tham dự',
        input: 'textarea',
        inputLabel: 'Lý do từ chối',
        inputPlaceholder: 'Nhập lý do từ chối...',
        inputAttributes: {
            'aria-label': 'Lý do từ chối'
        },
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Từ chối',
        cancelButtonText: 'Hủy',
        inputValidator: function(value) {
            if (!value || value.trim() === '') {
                return 'Vui lòng nhập lý do từ chối!';
            }
        }
    }).then(function(result) {
        if (result.isConfirmed) {
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = window.BASE_URL + 'admin/approval/reject/id/' + id;

            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'rejection_reason';
            input.value = result.value;
            form.appendChild(input);

            document.body.appendChild(form);
            form.submit();
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    ApprovalIndex.init();
});
