document.addEventListener('DOMContentLoaded', function () {
    var cfg = window.FINAL_LIST || {};

    function getModal(id) {
        return bootstrap.Modal.getOrCreateInstance(document.getElementById(id));
    }

    function postForm(form, url, btn, modalId) {
        var originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i>Đang xử lý...';

        fetch(url, {
            method: 'POST',
            body: new FormData(form),
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.success) {
                    getModal(modalId).hide();
                    Toast.success(data.message || 'Thành công');
                    setTimeout(function () { location.reload(); }, 800);
                } else {
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                    Toast.error(data.message || 'Có lỗi xảy ra');
                }
            })
            .catch(function () {
                btn.disabled = false;
                btn.innerHTML = originalHtml;
                Toast.error('Lỗi kết nối server');
            });
    }

    // ----- Sửa ảnh / chức danh -----
    document.querySelectorAll('.btn-edit-attendee').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('edit_attendee_id').value = btn.getAttribute('data-id');
            document.getElementById('edit_position').value = btn.getAttribute('data-position') || '';
            document.getElementById('edit_photo_path').value = btn.getAttribute('data-photo') || '';
            getModal('modalEditFinalAttendee').show();
        });
    });

    var formEdit = document.getElementById('form-edit-final-attendee');
    if (formEdit) {
        formEdit.addEventListener('submit', function (e) {
            e.preventDefault();
            var id = document.getElementById('edit_attendee_id').value;
            var url = (cfg.updateUrl || '').replace('__ID__', id);
            postForm(formEdit, url, document.getElementById('btn_submit_edit_final'), 'modalEditFinalAttendee');
        });
    }

    // ----- Thêm giám đốc -----
    document.querySelectorAll('.btn-add-director').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var pid = btn.getAttribute('data-property-id');
            document.getElementById('director_property_id').value = pid;
            document.getElementById('director_property_name').textContent = btn.getAttribute('data-property-name') || '';
            var sel = document.getElementById('director_staff_id');
            sel.innerHTML = '<option value="">-- Đang tải danh sách... --</option>';

            fetch(cfg.candidatesUrl + '?property_id=' + encodeURIComponent(pid), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    sel.innerHTML = '<option value="">-- Chọn nhân sự hoặc nhập thủ công --</option>';
                    var items = (data && data.data) ? data.data : [];
                    if (items.length === 0) {
                        sel.innerHTML = '<option value="">-- Không có nhân sự 610, nhập thủ công --</option>';
                    }
                    items.forEach(function (s) {
                        var opt = document.createElement('option');
                        opt.value = s.staff_id;
                        opt.textContent = s.full_name + (s.position_name ? ' - ' + s.position_name : '');
                        sel.appendChild(opt);
                    });
                })
                .catch(function () {
                    sel.innerHTML = '<option value="">-- Lỗi tải danh sách --</option>';
                });

            getModal('modalAddDirector').show();
        });
    });

    var formDirector = document.getElementById('form-add-director');
    if (formDirector) {
        formDirector.addEventListener('submit', function (e) {
            e.preventDefault();
            postForm(formDirector, cfg.addSupportUrl, document.getElementById('btn_submit_director'), 'modalAddDirector');
        });
    }

    // ----- Thêm lái xe -----
    document.querySelectorAll('.btn-add-driver').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('driver_property_id').value = btn.getAttribute('data-property-id');
            document.getElementById('driver_property_name').textContent = btn.getAttribute('data-property-name') || '';
            getModal('modalAddDriver').show();
        });
    });

    var formDriver = document.getElementById('form-add-driver');
    if (formDriver) {
        formDriver.addEventListener('submit', function (e) {
            e.preventDefault();
            postForm(formDriver, cfg.addSupportUrl, document.getElementById('btn_submit_driver'), 'modalAddDriver');
        });
    }
});
