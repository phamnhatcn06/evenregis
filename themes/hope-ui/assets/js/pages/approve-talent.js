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
                document.getElementById('detail_show_id').value = d.show_id || '';
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

    function approveEntry(id, showId, name) {
        document.getElementById('approve_entry_id').value = id;
        document.getElementById('approve_show_id').value = showId || '';
        document.getElementById('approve_entry_name').textContent = name || 'Tiết mục #' + id;

        var roundsList = document.getElementById('rounds_list');
        var roundsLoading = document.getElementById('rounds_loading');

        roundsList.innerHTML = '';
        roundsLoading.style.display = 'block';

        var modal = new bootstrap.Modal(document.getElementById('modalApprove'));
        modal.show();

        fetch(approveTalentConfig.getRoundsUrl + '?entry_id=' + id)
            .then(function(res) { return res.json(); })
            .then(function(res) {
                roundsLoading.style.display = 'none';
                if (res.success && res.data.length > 0) {
                    var html = '';
                    res.data.forEach(function(r) {
                        html += '<label class="list-group-item list-group-item-action d-flex align-items-center">';
                        html += '<input type="radio" name="approve_round" value="' + r.id + '" class="form-check-input me-3"' + (r.is_current ? ' checked' : '') + '>';
                        html += '<div>';
                        html += '<strong>' + r.name + '</strong>';
                        if (r.round_type) {
                            html += ' <span class="badge bg-secondary ms-2">' + r.round_type + '</span>';
                        }
                        if (r.is_current) {
                            html += ' <span class="badge bg-info ms-1">Vòng hiện tại</span>';
                        }
                        html += '</div>';
                        html += '</label>';
                    });
                    roundsList.innerHTML = html;
                } else {
                    roundsList.innerHTML = '<div class="text-muted p-3"><i class="fa fa-info-circle me-1"></i>Hội diễn này chưa có vòng thi nào</div>';
                }
            })
            .catch(function() {
                roundsLoading.style.display = 'none';
                roundsList.innerHTML = '<div class="text-danger p-3">Lỗi tải danh sách vòng thi</div>';
            });
    }

    var btnConfirmApprove = document.getElementById('btn_confirm_approve');
    if (btnConfirmApprove) {
        btnConfirmApprove.addEventListener('click', function() {
            var id = document.getElementById('approve_entry_id').value;
            var selectedRound = document.querySelector('input[name="approve_round"]:checked');
            var roundId = selectedRound ? selectedRound.value : '';

            var btn = this;
            var originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i>Đang xử lý...';

            fetch(approveTalentConfig.approveUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'id=' + id + '&round_id=' + roundId
            })
            .then(function(res) { return res.json(); })
            .then(function(res) {
                if (res.success) {
                    Toast.success(res.message);
                    location.reload();
                } else {
                    Toast.error(res.message);
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                }
            })
            .catch(function() {
                Toast.error('Lỗi kết nối server');
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            });
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
            var card = this.closest('.talent-card');
            var name = card ? card.querySelector('.card-title').textContent : '';
            approveEntry(this.dataset.id, this.dataset.showId, name);
        });
    });

    document.querySelectorAll('.btn-reject').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            rejectEntry(this.dataset.id);
        });
    });

    var btnApproveModal = document.getElementById('btn_approve_modal');
    if (btnApproveModal) {
        btnApproveModal.addEventListener('click', function() {
            var id = document.getElementById('detail_id').value;
            var showId = document.getElementById('detail_show_id').value;
            var name = document.getElementById('detail_title').textContent;
            bootstrap.Modal.getInstance(document.getElementById('modalDetail')).hide();
            approveEntry(id, showId, name);
        });
    }

    var btnRejectModal = document.getElementById('btn_reject_modal');
    if (btnRejectModal) {
        btnRejectModal.addEventListener('click', function() {
            var id = document.getElementById('detail_id').value;
            bootstrap.Modal.getInstance(document.getElementById('modalDetail')).hide();
            rejectEntry(id);
        });
    }

    // Video popup
    var btnPlayVideo = document.getElementById('btn_play_video');
    if (btnPlayVideo) {
        btnPlayVideo.addEventListener('click', function() {
            var videoSrc = document.getElementById('detail_video_src').value;
            var downloadBtn = document.getElementById('detail_video_download');
            var downloadSrc = downloadBtn ? downloadBtn.href : '';
            if (!videoSrc) return;

            var fullscreenVideo = document.getElementById('fullscreen_video');
            var fullscreenDownload = document.getElementById('fullscreen_video_download');
            if (fullscreenVideo) {
                fullscreenVideo.src = videoSrc;
            }
            if (fullscreenDownload) fullscreenDownload.href = downloadSrc;

            var modal = new bootstrap.Modal(document.getElementById('modalVideoViewer'));
            modal.show();

            if (fullscreenVideo) fullscreenVideo.play();
        });
    }

    // Music popup
    var btnPlayMusic = document.getElementById('btn_play_music');
    if (btnPlayMusic) {
        btnPlayMusic.addEventListener('click', function() {
            var musicSrc = document.getElementById('detail_music_src').value;
            if (!musicSrc) return;

            var fullscreenMusic = document.getElementById('fullscreen_music');
            if (fullscreenMusic) {
                fullscreenMusic.src = musicSrc;
                var modal = new bootstrap.Modal(document.getElementById('modalMusicViewer'));
                modal.show();
                fullscreenMusic.play();
            }
        });
    }
});
