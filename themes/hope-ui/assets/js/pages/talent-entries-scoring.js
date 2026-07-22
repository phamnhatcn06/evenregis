document.addEventListener('DOMContentLoaded', function () {
    var saveUrl = document.getElementById('save_score_url').value;
    var deleteUrl = document.getElementById('delete_score_url').value;
    var modalEl = document.getElementById('modal_score');
    var modal = new bootstrap.Modal(modalEl);
    var form = document.getElementById('form_score');

    function openModal(data) {
        document.getElementById('modal_score_title').textContent = data ? 'Sửa điểm' : 'Thêm điểm';
        document.getElementById('score_id').value = data ? data.id : '';
        document.getElementById('score_judge_id').value = data ? data.judgeId : '';
        document.getElementById('score_criteria').value = data ? data.criteria : '';
        document.getElementById('score_value').value = data ? data.score : '';
        document.getElementById('score_note').value = data ? data.note : '';
        modal.show();
    }

    document.getElementById('btn_add_score').addEventListener('click', function () {
        openModal(null);
    });

    document.querySelectorAll('.btn-edit-score').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var row = btn.closest('tr');
            openModal({
                id: row.getAttribute('data-id'),
                judgeId: row.getAttribute('data-judge-id'),
                criteria: row.getAttribute('data-criteria'),
                score: row.getAttribute('data-score'),
                note: row.getAttribute('data-note')
            });
        });
    });

    document.querySelectorAll('.btn-delete-score').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var row = btn.closest('tr');
            var id = row.getAttribute('data-id');
            Swal.fire({
                title: 'Xác nhận xóa',
                text: 'Bạn có chắc chắn muốn xóa phiếu điểm này?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Xóa',
                cancelButtonText: 'Hủy'
            }).then(function (result) {
                if (result.isConfirmed) {
                    var fd = new FormData();
                    fd.append('id', id);
                    fetch(deleteUrl, { method: 'POST', body: fd })
                        .then(function (res) { return res.json(); })
                        .then(function (d) {
                            if (d.success) {
                                Toast.success(d.message || 'Đã xóa');
                                setTimeout(function () { location.reload(); }, 600);
                            } else {
                                Toast.error(d.message || 'Có lỗi xảy ra');
                            }
                        })
                        .catch(function () { Toast.error('Lỗi kết nối server'); });
                }
            });
        });
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var btn = document.getElementById('btn_submit_score');
        var originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i>Đang xử lý...';

        var formData = new FormData(form);
        fetch(saveUrl, { method: 'POST', body: formData })
            .then(function (res) { return res.json(); })
            .then(function (d) {
                if (d.success) {
                    modal.hide();
                    Toast.success(d.message || 'Thành công');
                    setTimeout(function () { location.reload(); }, 600);
                } else {
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                    Toast.error(d.message || 'Có lỗi xảy ra');
                }
            })
            .catch(function () {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
                Toast.error('Lỗi kết nối server');
            });
    });
});
