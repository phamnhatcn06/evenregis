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

            results.forEach(function(res, idx) {
                if (!res.success) return;
                var d = res.data;
                var carouselId = 'compare-carousel-' + idx;

                var media = [];
                if (d.photo_portrait) media.push({ url: d.photo_portrait, label: 'Chân dung 1', type: 'image' });
                if (d.photo_portrait_2) media.push({ url: d.photo_portrait_2, label: 'Chân dung 2', type: 'image' });
                if (d.photo_full_body) media.push({ url: d.photo_full_body, label: 'Toàn thân 1', type: 'image' });
                if (d.photo_full_body_2) media.push({ url: d.photo_full_body_2, label: 'Toàn thân 2', type: 'image' });
                if (d.video_path) media.push({ url: d.video_path, label: 'Video dự thi', type: 'video' });

                html += '<div class="' + colClass + ' compare-column">';
                html += '<div class="card h-100">';

                if (media.length > 0) {
                    html += '<div id="' + carouselId + '" class="carousel slide compare-carousel" data-bs-ride="false" style="height:70vh;">';

                    if (media.length > 1) {
                        html += '<div class="carousel-indicators">';
                        media.forEach(function(m, i) {
                            html += '<button type="button" data-bs-target="#' + carouselId + '" data-bs-slide-to="' + i + '"' + (i === 0 ? ' class="active"' : '') + '></button>';
                        });
                        html += '</div>';
                    }

                    html += '<div class="carousel-inner h-100">';
                    media.forEach(function(m, i) {
                        html += '<div class="carousel-item h-100' + (i === 0 ? ' active' : '') + '">';
                        if (m.type === 'video') {
                            html += '<video controls class="w-100 h-100" style="object-fit:contain;background:#000;"><source src="' + m.url + '" type="video/mp4"></video>';
                        } else {
                            html += '<img src="' + m.url + '" alt="' + m.label + '" style="width:100%;height:100%;object-fit:contain;">';
                        }
                        html += '<div class="photo-label-overlay">' + m.label + '</div>';
                        html += '</div>';
                    });
                    html += '</div>';

                    if (media.length > 1) {
                        html += '<button class="carousel-control-prev" type="button" data-bs-target="#' + carouselId + '" data-bs-slide="prev">';
                        html += '<span class="carousel-control-prev-icon"></span></button>';
                        html += '<button class="carousel-control-next" type="button" data-bs-target="#' + carouselId + '" data-bs-slide="next">';
                        html += '<span class="carousel-control-next-icon"></span></button>';
                    }
                    html += '</div>';
                } else {
                    html += '<div class="compare-carousel d-flex align-items-center justify-content-center" style="height:70vh;">';
                    html += '<i class="fa fa-user fa-4x text-muted"></i></div>';
                }

                html += '<div class="card-body">';
                html += '<h5 class="card-title text-center mb-3">' + (d.attendee_name || '') + '</h5>';
                html += '<table class="table table-sm table-bordered compare-table">';
                html += '<tr><th>Đơn vị</th><td>' + (d.property_name || '-') + '</td></tr>';
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

    document.querySelectorAll('.contestant-photo, .contestant-photo-placeholder').forEach(function(el) {
        el.addEventListener('click', function(e) {
            var card = this.closest('.contestant-card');
            if (card) {
                var id = card.dataset.id;
                loadDetail(id);
            }
        });
    });

    function resetModalDetail() {
        document.getElementById('detail_id').value = '';
        document.getElementById('detail_contest_id').value = '';
        document.getElementById('detail_name').textContent = '';
        document.getElementById('detail_property').textContent = '';
        document.getElementById('detail_contest').textContent = '';
        document.getElementById('detail_status').innerHTML = '';
        document.getElementById('detail_height').textContent = '';
        document.getElementById('detail_weight').textContent = '';
        document.getElementById('detail_measurements').textContent = '';
        document.getElementById('detail_talent').textContent = '';
        document.getElementById('detail_email').textContent = '';
        document.getElementById('detail_submitted_at').textContent = '';

        var photos = ['photo_portrait', 'photo_portrait_2', 'photo_full_body', 'photo_full_body_2'];
        photos.forEach(function(p) {
            var img = document.getElementById('detail_' + p);
            if (img) {
                img.src = '';
                var wrapper = img.closest('.col-6');
                if (wrapper) wrapper.style.display = 'none';
            }
        });

        var videoSrcInput = document.getElementById('detail_video_src');
        if (videoSrcInput) videoSrcInput.value = '';
        document.getElementById('detail_video_container').style.display = 'none';

        var noMediaAlert = document.getElementById('detail_no_media');
        var contentRow = document.getElementById('detail_content_row');
        if (noMediaAlert) noMediaAlert.style.display = 'none';
        if (contentRow) contentRow.style.display = 'flex';
    }

    function stopAllMedia() {
        document.querySelectorAll('#modalDetail video, #modalDetail audio').forEach(function(el) {
            el.pause();
        });

        document.querySelectorAll('#modalCompare video, #modalCompare audio').forEach(function(el) {
            el.pause();
        });

        document.querySelectorAll('#modalImageViewer video').forEach(function(el) {
            el.pause();
        });

        var fullscreenVideo = document.getElementById('fullscreen_video');
        if (fullscreenVideo) {
            fullscreenVideo.pause();
            fullscreenVideo.src = '';
        }
    }

    document.getElementById('modalDetail').addEventListener('hidden.bs.modal', function() {
        stopAllMedia();
    });

    document.getElementById('modalCompare').addEventListener('hidden.bs.modal', function() {
        stopAllMedia();
    });

    document.getElementById('modalImageViewer').addEventListener('hidden.bs.modal', function() {
        stopAllMedia();
        resetZoom();
    });

    document.getElementById('modalVideoViewer').addEventListener('hidden.bs.modal', function() {
        var video = document.getElementById('fullscreen_video');
        if (video) {
            video.pause();
            video.src = '';
        }
    });

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

        fetch(approveMissConfig.getDetailUrl + '?id=' + id)
            .then(function(res) { return res.json(); })
            .then(function(res) {
                if (!res.success) {
                    Toast.error(res.message || 'Không thể tải thông tin');
                    return;
                }
                var d = res.data;
                document.getElementById('detail_id').value = d.id;
                document.getElementById('detail_contest_id').value = d.contest_id || '';
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

                var photos = ['photo_portrait', 'photo_portrait_2', 'photo_full_body', 'photo_full_body_2'];
                var hasAnyMedia = false;

                photos.forEach(function(p) {
                    var img = document.getElementById('detail_' + p);
                    var wrapper = img.closest('.col-6');
                    if (d[p]) {
                        img.src = d[p];
                        wrapper.style.display = 'block';
                        hasAnyMedia = true;
                    } else {
                        img.src = '';
                        wrapper.style.display = 'none';
                    }
                });

                var videoContainer = document.getElementById('detail_video_container');
                var videoSrcInput = document.getElementById('detail_video_src');
                var downloadBtn = document.getElementById('detail_video_download');
                if (d.video_path) {
                    videoSrcInput.value = d.video_path;
                    videoContainer.style.display = 'block';
                    hasAnyMedia = true;
                    if (downloadBtn) {
                        downloadBtn.href = d.video_path_original || d.video_path;
                    }
                } else {
                    videoSrcInput.value = '';
                    videoContainer.style.display = 'none';
                    if (downloadBtn) downloadBtn.href = '#';
                }

                var noMediaAlert = document.getElementById('detail_no_media');
                var contentRow = document.getElementById('detail_content_row');
                if (hasAnyMedia) {
                    noMediaAlert.style.display = 'none';
                    contentRow.style.display = 'flex';
                } else {
                    noMediaAlert.style.display = 'block';
                    contentRow.style.display = 'none';
                }

                var approveBtn = document.getElementById('btn_approve_modal');
                var rejectBtn = document.getElementById('btn_reject_modal');
                approveBtn.style.display = (d.status == 1) ? 'none' : 'inline-block';
                rejectBtn.style.display = (d.status == 3) ? 'none' : 'inline-block';

                var loading = document.getElementById('detail_loading');
                if (loading) loading.remove();
            })
            .catch(function() {
                Toast.error('Lỗi kết nối server');
                var loading = document.getElementById('detail_loading');
                if (loading) loading.remove();
            });
    }

    function approveContestant(id, contestId, name) {
        document.getElementById('approve_contestant_id').value = id;
        document.getElementById('approve_contest_id').value = contestId || '';
        document.getElementById('approve_contestant_name').textContent = name || 'Thí sinh #' + id;

        var roundsList = document.getElementById('rounds_list');
        var roundsLoading = document.getElementById('rounds_loading');

        roundsList.innerHTML = '';
        roundsLoading.style.display = 'block';

        var modal = new bootstrap.Modal(document.getElementById('modalApprove'));
        modal.show();

        if (contestId) {
            fetch(approveMissConfig.getRoundsUrl + '?contest_id=' + contestId + '&contestant_id=' + id)
                .then(function(res) { return res.json(); })
                .then(function(res) {
                    roundsLoading.style.display = 'none';
                    if (res.success && res.data.length > 0) {
                        var html = '';
                        res.data.forEach(function(r) {
                            html += '<label class="list-group-item list-group-item-action d-flex align-items-center">';
                            html += '<input type="radio" name="approve_round" value="' + r.id + '" class="form-check-input me-3">';
                            html += '<div>';
                            html += '<strong>' + r.name + '</strong>';
                            if (r.round_type) {
                                html += ' <span class="badge bg-secondary ms-2">' + r.round_type + '</span>';
                            }
                            html += '</div>';
                            html += '</label>';
                        });
                        roundsList.innerHTML = html;
                    } else {
                        roundsList.innerHTML = '<div class="text-muted p-3"><i class="fa fa-info-circle me-1"></i>Thí sinh đã được gán vào tất cả các vòng hoặc chưa có vòng thi nào</div>';
                    }
                })
                .catch(function() {
                    roundsLoading.style.display = 'none';
                    roundsList.innerHTML = '<div class="text-danger p-3">Lỗi tải danh sách vòng thi</div>';
                });
        } else {
            roundsLoading.style.display = 'none';
            roundsList.innerHTML = '<div class="text-muted p-3">Không xác định được cuộc thi</div>';
        }
    }

    document.getElementById('btn_confirm_approve').addEventListener('click', function() {
        var id = document.getElementById('approve_contestant_id').value;
        var selectedRound = document.querySelector('input[name="approve_round"]:checked');
        var roundId = selectedRound ? selectedRound.value : '';

        var btn = this;
        var originalHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i>Đang xử lý...';

        fetch(approveMissConfig.approveUrl, {
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
            var card = this.closest('.contestant-card');
            var id = this.dataset.id;
            var contestId = card ? card.dataset.contestId : '';
            var name = card ? card.querySelector('.card-title').textContent : '';
            approveContestant(id, contestId, name);
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
        var contestId = document.getElementById('detail_contest_id') ? document.getElementById('detail_contest_id').value : '';
        var name = document.getElementById('detail_name').textContent;
        bootstrap.Modal.getInstance(document.getElementById('modalDetail')).hide();
        approveContestant(id, contestId, name);
    });

    document.getElementById('btn_reject_modal').addEventListener('click', function() {
        var id = document.getElementById('detail_id').value;
        bootstrap.Modal.getInstance(document.getElementById('modalDetail')).hide();
        rejectContestant(id);
    });

    // Click ảnh/video để xem fullscreen với carousel
    var photoIds = ['detail_photo_portrait', 'detail_photo_portrait_2', 'detail_photo_full_body', 'detail_photo_full_body_2'];
    var photoLabels = ['Chân dung 1', 'Chân dung 2', 'Toàn thân 1', 'Toàn thân 2'];

    function getValidMedia() {
        var media = [];
        photoIds.forEach(function(id, idx) {
            var img = document.getElementById(id);
            if (img && img.src && img.src !== '' && img.src !== window.location.href && !img.src.endsWith('/') && img.naturalWidth > 0) {
                media.push({ type: 'image', src: img.src, label: photoLabels[idx], originalIndex: idx });
            }
        });

        var video = document.getElementById('detail_video');
        if (video && video.src && video.src !== '' && video.src !== window.location.href) {
            media.push({ type: 'video', src: video.src, label: 'Video dự thi', originalIndex: photoIds.length });
        }

        return media;
    }

    function openMediaViewer(startIndex) {
        var carouselInner = document.getElementById('fullscreen_carousel_inner');
        var indicators = document.getElementById('fullscreen_carousel_indicators');
        carouselInner.innerHTML = '';
        indicators.innerHTML = '';

        var validMedia = getValidMedia();
        if (validMedia.length === 0) return;

        var activeIdx = 0;
        validMedia.forEach(function(item, idx) {
            if (item.originalIndex === startIndex) activeIdx = idx;
        });

        validMedia.forEach(function(media, idx) {
            var item = document.createElement('div');
            item.className = 'carousel-item h-100' + (idx === activeIdx ? ' active' : '');

            var content = '';
            if (media.type === 'image') {
                content = '<img src="' + media.src + '" class="img-fluid zoom-target" style="max-height:calc(90vh - 50px);object-fit:contain;">';
            } else {
                content = '<video src="' + media.src + '" controls class="img-fluid" style="max-height:calc(90vh - 50px);"></video>';
            }

            item.innerHTML = '<div class="d-flex flex-column align-items-center justify-content-center h-100">' +
                content +
                '<div class="text-white mt-2 fs-5">' + media.label + '</div>' +
                '</div>';
            carouselInner.appendChild(item);

            var indicator = document.createElement('button');
            indicator.type = 'button';
            indicator.setAttribute('data-bs-target', '#fullscreenCarousel');
            indicator.setAttribute('data-bs-slide-to', idx);
            if (idx === activeIdx) indicator.classList.add('active');
            indicators.appendChild(indicator);
        });

        var viewer = new bootstrap.Modal(document.getElementById('modalImageViewer'));
        viewer.show();
    }

    // Zoom/Pan State
    var zoomState = {
        scale: 1,
        translateX: 0,
        translateY: 0,
        isDragging: false,
        startX: 0,
        startY: 0
    };

    function getActiveZoomTarget() {
        var activeItem = document.querySelector('#fullscreen_carousel_inner .carousel-item.active');
        if (!activeItem) return null;
        return activeItem.querySelector('.zoom-target');
    }

    function updateZoomControlsVisibility() {
        var activeItem = document.querySelector('#fullscreen_carousel_inner .carousel-item.active');
        var zoomControls = document.querySelector('.zoom-controls');
        if (!zoomControls) return;
        
        if (activeItem && activeItem.querySelector('.zoom-target')) {
            zoomControls.style.setProperty('display', 'inline-flex', 'important');
        } else {
            zoomControls.style.setProperty('display', 'none', 'important');
        }
    }

    function resetZoom() {
        var allImgs = document.querySelectorAll('#fullscreen_carousel_inner .zoom-target');
        allImgs.forEach(function(img) {
            img.style.transform = 'none';
        });
        zoomState.scale = 1;
        zoomState.translateX = 0;
        zoomState.translateY = 0;
        zoomState.isDragging = false;
        
        updateZoomControlsVisibility();
    }

    function applyTransform() {
        var img = getActiveZoomTarget();
        if (!img) return;
        
        if (zoomState.scale <= 1) {
            zoomState.translateX = 0;
            zoomState.translateY = 0;
        } else {
            var maxPanX = (zoomState.scale - 1) * (img.clientWidth / 2);
            var maxPanY = (zoomState.scale - 1) * (img.clientHeight / 2);
            if (maxPanX === 0) maxPanX = (zoomState.scale - 1) * 200;
            if (maxPanY === 0) maxPanY = (zoomState.scale - 1) * 300;
            
            if (Math.abs(zoomState.translateX) > maxPanX) {
                zoomState.translateX = Math.sign(zoomState.translateX) * maxPanX;
            }
            if (Math.abs(zoomState.translateY) > maxPanY) {
                zoomState.translateY = Math.sign(zoomState.translateY) * maxPanY;
            }
        }
        
        img.style.transform = 'translate(' + zoomState.translateX + 'px, ' + zoomState.translateY + 'px) scale(' + zoomState.scale + ')';
    }

    // Zoom buttons event listeners
    var btnZoomIn = document.getElementById('btn_zoom_in');
    if (btnZoomIn) {
        btnZoomIn.addEventListener('click', function(e) {
            e.stopPropagation();
            zoomState.scale = Math.min(zoomState.scale + 0.5, 5);
            applyTransform();
        });
    }

    var btnZoomOut = document.getElementById('btn_zoom_out');
    if (btnZoomOut) {
        btnZoomOut.addEventListener('click', function(e) {
            e.stopPropagation();
            zoomState.scale = Math.max(zoomState.scale - 0.5, 1);
            applyTransform();
        });
    }

    var btnZoomReset = document.getElementById('btn_zoom_reset');
    if (btnZoomReset) {
        btnZoomReset.addEventListener('click', function(e) {
            e.stopPropagation();
            resetZoom();
            applyTransform();
        });
    }

    // Wheel zooming on the modal
    var modalImageViewer = document.getElementById('modalImageViewer');
    if (modalImageViewer) {
        modalImageViewer.addEventListener('wheel', function(e) {
            var img = getActiveZoomTarget();
            if (!img) return;
            
            e.preventDefault();
            var delta = e.deltaY < 0 ? 0.25 : -0.25;
            zoomState.scale = Math.max(1, Math.min(zoomState.scale + delta, 5));
            applyTransform();
        }, { passive: false });

        modalImageViewer.addEventListener('shown.bs.modal', function() {
            resetZoom();
        });

        // Mouse Drag Panning
        modalImageViewer.addEventListener('mousedown', function(e) {
            var img = getActiveZoomTarget();
            if (!img || zoomState.scale <= 1) return;
            
            if (e.target.classList.contains('zoom-target')) {
                e.preventDefault();
                zoomState.isDragging = true;
                zoomState.startX = e.clientX - zoomState.translateX;
                zoomState.startY = e.clientY - zoomState.translateY;
            }
        });
    }

    document.addEventListener('mousemove', function(e) {
        if (!zoomState.isDragging) return;
        zoomState.translateX = e.clientX - zoomState.startX;
        zoomState.translateY = e.clientY - zoomState.startY;
        applyTransform();
    });

    document.addEventListener('mouseup', function() {
        zoomState.isDragging = false;
    });

    // Touch support for mobile devices
    if (modalImageViewer) {
        modalImageViewer.addEventListener('touchstart', function(e) {
            var img = getActiveZoomTarget();
            if (!img || zoomState.scale <= 1 || e.touches.length !== 1) return;
            
            if (e.target.classList.contains('zoom-target')) {
                zoomState.isDragging = true;
                zoomState.startX = e.touches[0].clientX - zoomState.translateX;
                zoomState.startY = e.touches[0].clientY - zoomState.translateY;
            }
        }, { passive: true });
    }

    document.addEventListener('touchmove', function(e) {
        if (!zoomState.isDragging || e.touches.length !== 1) return;
        
        if (zoomState.scale > 1) {
            if (e.cancelable) e.preventDefault();
        }
        
        zoomState.translateX = e.touches[0].clientX - zoomState.startX;
        zoomState.translateY = e.touches[0].clientY - zoomState.startY;
        applyTransform();
    }, { passive: false });

    document.addEventListener('touchend', function() {
        zoomState.isDragging = false;
    });

    // Pause video khi chuyển slide
    var fullscreenCarousel = document.getElementById('fullscreenCarousel');
    if (fullscreenCarousel) {
        fullscreenCarousel.addEventListener('slide.bs.carousel', function() {
            var videos = this.querySelectorAll('video');
            videos.forEach(function(v) { v.pause(); });
            resetZoom();
        });
        fullscreenCarousel.addEventListener('slid.bs.carousel', function() {
            updateZoomControlsVisibility();
        });
    }

    photoIds.forEach(function(id, idx) {
        var img = document.getElementById(id);
        if (img) {
            img.addEventListener('click', function() {
                if (this.src && this.naturalWidth > 0) {
                    openMediaViewer(idx);
                }
            });
        }
    });

    // Click video cũng mở slider
    var detailVideo = document.getElementById('detail_video');
    if (detailVideo) {
        detailVideo.addEventListener('click', function(e) {
            if (e.target.paused) {
                openMediaViewer(photoIds.length);
                e.preventDefault();
            }
        });
    }
});

