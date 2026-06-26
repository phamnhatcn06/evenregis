document.addEventListener('DOMContentLoaded', function() {
    var selectedIds = [];

    function updateCompareButton() {
        var count = selectedIds.length;
        document.getElementById('compare_count').textContent = count;
        document.getElementById('btn_compare').disabled = count < 2;
    }

    document.querySelectorAll('.compare-check').forEach(function(checkbox) {
        checkbox.addEventListener('change', function(e) {
            e.stopPropagation();
            var id = this.dataset.id;
            var card = this.closest('.contestant-card');

            if (this.checked) {
                if (selectedIds.length >= 4) {
                    Toast.warning('Chỉ có thể so sánh tối đa 4 thí sinh');
                    this.checked = false;
                    return;
                }
                selectedIds.push(id);
                card.classList.add('selected');
            } else {
                selectedIds = selectedIds.filter(function(i) { return i !== id; });
                card.classList.remove('selected');
            }
            updateCompareButton();
        });
    });

    document.getElementById('btn_clear_compare').addEventListener('click', function() {
        selectedIds = [];
        document.querySelectorAll('.compare-check').forEach(function(cb) {
            cb.checked = false;
        });
        document.querySelectorAll('.contestant-card.selected').forEach(function(card) {
            card.classList.remove('selected');
        });
        updateCompareButton();
    });

    document.getElementById('btn_compare').addEventListener('click', function() {
        if (selectedIds.length < 2) return;

        var container = document.getElementById('compare_container');
        container.innerHTML = '<div class="text-center p-5"><i class="fa fa-spinner fa-spin fa-2x"></i></div>';

        var modal = new bootstrap.Modal(document.getElementById('modalCompare'));
        modal.show();

        var promises = selectedIds.map(function(id) {
            return fetch(approveMissConfig.getDetailUrl + '?id=' + id)
                .then(function(res) { return res.json(); });
        });

        Promise.all(promises).then(function(results) {
            var colClass = selectedIds.length === 2 ? 'col-md-6' : (selectedIds.length === 3 ? 'col-md-4' : 'col-md-3');
            var html = '';

            results.forEach(function(res) {
                if (!res.success) return;
                var d = res.data;
                var photo = d.photo_portrait || d.photo_full_body || '';

                html += '<div class="' + colClass + ' compare-column">';
                html += '<div class="card">';
                if (photo) {
                    html += '<img src="' + photo + '" class="card-img-top contestant-photo" alt="">';
                }
                html += '<div class="card-body">';
                html += '<h5 class="card-title">' + (d.attendee_name || '') + '</h5>';
                html += '<table class="table table-sm table-bordered compare-table">';
                html += '<tr><th>Đơn vị</th><td>' + (d.property_name || '') + '</td></tr>';
                html += '<tr><th>Chiều cao</th><td>' + (d.height_cm ? d.height_cm + ' cm' : '-') + '</td></tr>';
                html += '<tr><th>Cân nặng</th><td>' + (d.weight_kg ? d.weight_kg + ' kg' : '-') + '</td></tr>';
                html += '<tr><th>Số đo</th><td>' + (d.measurements || '-') + '</td></tr>';
                html += '<tr><th>Năng khiếu</th><td>' + (d.talent || '-') + '</td></tr>';
                html += '<tr><th>Trạng thái</th><td>' + (d.status_label || '') + '</td></tr>';
                html += '</table>';
                html += '</div></div></div>';
            });

            container.innerHTML = html;
        });
    });

    document.querySelectorAll('.btn-view-detail').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            var id = this.dataset.id;
            loadDetail(id);
        });
    });

    function loadDetail(id) {
        var modal = new bootstrap.Modal(document.getElementById('modalDetail'));
        modal.show();

        fetch(approveMissConfig.getDetailUrl + '?id=' + id)
            .then(function(res) { return res.json(); })
            .then(function(res) {
                if (!res.success) {
                    Toast.error(res.message || 'Không thể tải thông tin');
                    return;
                }
                var d = res.data;
                document.getElementById('detail_id').value = d.id;
                document.getElementById('detail_name').textContent = d.attendee_name || '';
                document.getElementById('detail_property').textContent = d.property_name || '';
                document.getElementById('detail_contest').textContent = d.contest_name || '';
                document.getElementById('detail_status').innerHTML = d.status_label || '';
                document.getElementById('detail_height').textContent = d.height_cm ? d.height_cm + ' cm' : '-';
                document.getElementById('detail_weight').textContent = d.weight_kg ? d.weight_kg + ' kg' : '-';
                document.getElementById('detail_measurements').textContent = d.measurements || '-';
                document.getElementById('detail_talent').textContent = d.talent || '-';
                document.getElementById('detail_email').textContent = d.personal_email || '-';
                document.getElementById('detail_submitted_at').textContent = d.submitted_at || '-';
                document.getElementById('detail_bio').textContent = d.bio || '';

                var photos = ['photo_portrait', 'photo_portrait_2', 'photo_full_body', 'photo_full_body_2'];
                photos.forEach(function(p) {
                    var img = document.getElementById('detail_' + p);
                    if (d[p]) {
                        img.src = d[p];
                        img.parentElement.style.display = 'block';
                    } else {
                        img.src = '';
                        img.parentElement.style.display = 'none';
                    }
                });

                var videoContainer = document.getElementById('detail_video_container');
                var video = document.getElementById('detail_video');
                if (d.video_path) {
                    video.src = d.video_path;
                    videoContainer.style.display = 'block';
                } else {
                    video.src = '';
                    videoContainer.style.display = 'none';
                }

                var approveBtn = document.getElementById('btn_approve_modal');
                var rejectBtn = document.getElementById('btn_reject_modal');
                approveBtn.style.display = (d.status == 1) ? 'none' : 'inline-block';
                rejectBtn.style.display = (d.status == 3) ? 'none' : 'inline-block';
            });
    }

    function approveContestant(id) {
        Swal.fire({
            title: 'Xác nhận duyệt',
            text: 'Bạn có chắc chắn muốn duyệt thí sinh này?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Duyệt',
            cancelButtonText: 'Hủy'
        }).then(function(result) {
            if (result.isConfirmed) {
                fetch(approveMissConfig.approveUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'id=' + id
                })
                .then(function(res) { return res.json(); })
                .then(function(res) {
                    if (res.success) {
                        Toast.success(res.message);
                        location.reload();
                    } else {
                        Toast.error(res.message);
                    }
                });
            }
        });
    }

    function rejectContestant(id) {
        Swal.fire({
            title: 'Từ chối thí sinh',
            input: 'textarea',
            inputLabel: 'Lý do từ chối (không bắt buộc)',
            inputPlaceholder: 'Nhập lý do...',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Từ chối',
            cancelButtonText: 'Hủy'
        }).then(function(result) {
            if (result.isConfirmed) {
                fetch(approveMissConfig.rejectUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'id=' + id + '&reason=' + encodeURIComponent(result.value || '')
                })
                .then(function(res) { return res.json(); })
                .then(function(res) {
                    if (res.success) {
                        Toast.success(res.message);
                        location.reload();
                    } else {
                        Toast.error(res.message);
                    }
                });
            }
        });
    }

    document.querySelectorAll('.btn-approve').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            approveContestant(this.dataset.id);
        });
    });

    document.querySelectorAll('.btn-reject').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            rejectContestant(this.dataset.id);
        });
    });

    document.getElementById('btn_approve_modal').addEventListener('click', function() {
        var id = document.getElementById('detail_id').value;
        bootstrap.Modal.getInstance(document.getElementById('modalDetail')).hide();
        approveContestant(id);
    });

    document.getElementById('btn_reject_modal').addEventListener('click', function() {
        var id = document.getElementById('detail_id').value;
        bootstrap.Modal.getInstance(document.getElementById('modalDetail')).hide();
        rejectContestant(id);
    });
});
