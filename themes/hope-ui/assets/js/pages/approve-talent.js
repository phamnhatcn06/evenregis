document.addEventListener('DOMContentLoaded', function() {

    document.querySelectorAll('.btn-view-detail').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            loadDetail(this.dataset.id);
        });
    });

    function loadDetail(id) {
        var modal = new bootstrap.Modal(document.getElementById('modalDetail'));
        modal.show();

        fetch(approveTalentConfig.getDetailUrl + '?id=' + id)
            .then(function(res) { return res.json(); })
            .then(function(res) {
                if (!res.success) {
                    Toast.error(res.message || 'Không thể tải thông tin');
                    return;
                }
                var d = res.data;
                document.getElementById('detail_id').value = d.id;
                document.getElementById('detail_title').textContent = d.title || '';
                document.getElementById('detail_property').textContent = d.property_name || '';
                document.getElementById('detail_show').textContent = d.show_name || '';
                document.getElementById('detail_category').textContent = d.category_name || '';
                document.getElementById('detail_status').innerHTML = d.status_label || '';

                var duration = d.duration_seconds ? Math.floor(d.duration_seconds / 60) + ':' + ('0' + (d.duration_seconds % 60)).slice(-2) : '-';
                document.getElementById('detail_duration').textContent = duration;
                document.getElementById('detail_participant_count').textContent = d.participant_count || '-';
                document.getElementById('detail_alliance').textContent = d.is_alliance_team ? 'Có' : 'Không';
                document.getElementById('detail_director').textContent = d.director || '-';
                document.getElementById('detail_director_phone').textContent = d.director_phone || '-';
                document.getElementById('detail_origin').textContent = d.origin || '-';
                document.getElementById('detail_created_at').textContent = d.created_at || '-';
                document.getElementById('detail_description').textContent = d.description || '';
                document.getElementById('detail_content').textContent = d.content || '';

                var noteContainer = document.getElementById('detail_note_container');
                if (d.note) {
                    document.getElementById('detail_note').textContent = d.note;
                    noteContainer.style.display = 'block';
                } else {
                    noteContainer.style.display = 'none';
                }

                var musicContainer = document.getElementById('detail_music_container');
                var musicSrcInput = document.getElementById('detail_music_src');
                if (d.music_path) {
                    musicSrcInput.value = d.music_path;
                    musicContainer.style.display = 'block';
                } else {
                    musicSrcInput.value = '';
                    musicContainer.style.display = 'none';
                }

                var videoContainer = document.getElementById('detail_video_container');
                var videoSrcInput = document.getElementById('detail_video_src');
                var downloadBtn = document.getElementById('detail_video_download');
                if (d.video_path) {
                    videoSrcInput.value = d.video_path;
                    videoContainer.style.display = 'block';
                    if (downloadBtn) {
                        downloadBtn.href = d.video_path_original || d.video_path;
                    }
                } else {
                    videoSrcInput.value = '';
                    videoContainer.style.display = 'none';
                    if (downloadBtn) downloadBtn.href = '#';
                }

                var docContainer = document.getElementById('detail_document_container');
                var docLink = document.getElementById('detail_document');
                if (d.document) {
                    docLink.href = d.document;
                    docContainer.style.display = 'block';
                } else {
                    docContainer.style.display = 'none';
                }

                var approveBtn = document.getElementById('btn_approve_modal');
                var rejectBtn = document.getElementById('btn_reject_modal');
                approveBtn.style.display = (d.status == 3) ? 'none' : 'inline-block';
                rejectBtn.style.display = (d.status == 4) ? 'none' : 'inline-block';
            });
    }

    function approveEntry(id) {
        Swal.fire({
            title: 'Xác nhận duyệt',
            text: 'Bạn có chắc chắn muốn duyệt tiết mục này?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Duyệt',
            cancelButtonText: 'Hủy'
        }).then(function(result) {
            if (result.isConfirmed) {
                fetch(approveTalentConfig.approveUrl, {
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

    function rejectEntry(id) {
        Swal.fire({
            title: 'Từ chối tiết mục',
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
                fetch(approveTalentConfig.rejectUrl, {
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
            approveEntry(this.dataset.id);
        });
    });

    document.querySelectorAll('.btn-reject').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            rejectEntry(this.dataset.id);
        });
    });

    document.getElementById('btn_approve_modal').addEventListener('click', function() {
        var id = document.getElementById('detail_id').value;
        bootstrap.Modal.getInstance(document.getElementById('modalDetail')).hide();
        approveEntry(id);
    });

    document.getElementById('btn_reject_modal').addEventListener('click', function() {
        var id = document.getElementById('detail_id').value;
        bootstrap.Modal.getInstance(document.getElementById('modalDetail')).hide();
        rejectEntry(id);
    });
});
