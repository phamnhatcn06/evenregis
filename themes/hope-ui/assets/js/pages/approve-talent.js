document.addEventListener('DOMContentLoaded', function() {

    function resetModalDetail() {
        document.getElementById('detail_id').value = '';
        document.getElementById('detail_title').textContent = '';
        document.getElementById('detail_property').textContent = '';
        document.getElementById('detail_show').textContent = '';
        document.getElementById('detail_category').textContent = '';
        document.getElementById('detail_status').innerHTML = '';
        document.getElementById('detail_duration').textContent = '';
        document.getElementById('detail_participant_count').textContent = '';
        document.getElementById('detail_alliance').textContent = '';
        document.getElementById('detail_director').textContent = '';
        document.getElementById('detail_director_phone').textContent = '';
        document.getElementById('detail_origin').textContent = '';
        document.getElementById('detail_created_at').textContent = '';
        document.getElementById('detail_description').textContent = '';
        document.getElementById('detail_content').textContent = '';

        document.getElementById('detail_note_container').style.display = 'none';
        document.getElementById('detail_note').textContent = '';

        var musicSrcInput = document.getElementById('detail_music_src');
        if (musicSrcInput) musicSrcInput.value = '';
        document.getElementById('detail_music_container').style.display = 'none';

        var videoSrcInput = document.getElementById('detail_video_src');
        if (videoSrcInput) videoSrcInput.value = '';
        document.getElementById('detail_video_container').style.display = 'none';

        document.getElementById('detail_document_container').style.display = 'none';
    }

    function stopAllMedia() {
        document.querySelectorAll('#modalDetail audio, #modalDetail video').forEach(function(el) {
            el.pause();
        });

        var fullscreenVideo = document.getElementById('fullscreen_video');
        if (fullscreenVideo) {
            fullscreenVideo.pause();
            fullscreenVideo.src = '';
        }

        var fullscreenMusic = document.getElementById('fullscreen_music');
        if (fullscreenMusic) {
            fullscreenMusic.pause();
            fullscreenMusic.src = '';
        }
    }

    var modalDetail = document.getElementById('modalDetail');
    if (modalDetail) {
        modalDetail.addEventListener('hidden.bs.modal', function() {
            stopAllMedia();
        });
    }

    var modalVideoViewer = document.getElementById('modalVideoViewer');
    if (modalVideoViewer) {
        modalVideoViewer.addEventListener('hidden.bs.modal', function() {
            var video = document.getElementById('fullscreen_video');
            if (video) {
                video.pause();
                video.src = '';
            }
        });
    }

    var modalMusicViewer = document.getElementById('modalMusicViewer');
    if (modalMusicViewer) {
        modalMusicViewer.addEventListener('hidden.bs.modal', function() {
            var audio = document.getElementById('fullscreen_music');
            if (audio) {
                audio.pause();
                audio.src = '';
            }
        });
    }

    document.querySelectorAll('.btn-view-detail').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            loadDetail(this.dataset.id);
        });
    });

    function loadDetail(id) {
        resetModalDetail();

        var modalEl = document.getElementById('modalDetail');
        var modalBody = modalEl.querySelector('.modal-body');
        var loadingHtml = '<div id="detail_loading" class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-white" style="z-index:10;"><i class="fa fa-spinner fa-spin fa-3x text-primary"></i></div>';

        var existingLoading = document.getElementById('detail_loading');
        if (existingLoading) existingLoading.remove();
        modalBody.style.position = 'relative';
        modalBody.insertAdjacentHTML('afterbegin', loadingHtml);

        var modal = new bootstrap.Modal(modalEl);
        modal.show();

        fetch(approveTalentConfig.getDetailUrl + '?id=' + id)
            .then(function(res) { return res.json(); })
            .then(function(res) {
                if (!res.success) {
                    Toast.error(res.message || 'Không thể tải thông tin');
                    var loading = document.getElementById('detail_loading');
                    if (loading) loading.remove();
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

                var loading = document.getElementById('detail_loading');
                if (loading) loading.remove();
            })
            .catch(function() {
                Toast.error('Lỗi kết nối server');
                var loading = document.getElementById('detail_loading');
                if (loading) loading.remove();
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

    // Video popup
    document.getElementById('btn_play_video').addEventListener('click', function() {
        var videoSrc = document.getElementById('detail_video_src').value;
        var downloadSrc = document.getElementById('detail_video_download').href;
        if (!videoSrc) return;

        var fullscreenVideo = document.getElementById('fullscreen_video');
        var fullscreenDownload = document.getElementById('fullscreen_video_download');
        fullscreenVideo.src = videoSrc;
        if (fullscreenDownload) fullscreenDownload.href = downloadSrc;

        var modal = new bootstrap.Modal(document.getElementById('modalVideoViewer'));
        modal.show();

        fullscreenVideo.play();
    });

    // Music popup
    document.getElementById('btn_play_music').addEventListener('click', function() {
        var musicSrc = document.getElementById('detail_music_src').value;
        if (!musicSrc) return;

        var fullscreenMusic = document.getElementById('fullscreen_music');
        fullscreenMusic.src = musicSrc;

        var modal = new bootstrap.Modal(document.getElementById('modalMusicViewer'));
        modal.show();

        fullscreenMusic.play();
    });
});
