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

                var photos = [];
                if (d.photo_portrait) photos.push({ url: d.photo_portrait, label: 'Chân dung 1' });
                if (d.photo_portrait_2) photos.push({ url: d.photo_portrait_2, label: 'Chân dung 2' });
                if (d.photo_full_body) photos.push({ url: d.photo_full_body, label: 'Toàn thân 1' });
                if (d.photo_full_body_2) photos.push({ url: d.photo_full_body_2, label: 'Toàn thân 2' });

                html += '<div class="' + colClass + ' compare-column">';
                html += '<div class="card h-100">';

                if (photos.length > 0) {
                    html += '<div id="' + carouselId + '" class="carousel slide compare-carousel" data-bs-ride="false">';

                    if (photos.length > 1) {
                        html += '<div class="carousel-indicators">';
                        photos.forEach(function(p, i) {
                            html += '<button type="button" data-bs-target="#' + carouselId + '" data-bs-slide-to="' + i + '"' + (i === 0 ? ' class="active"' : '') + '></button>';
                        });
                        html += '</div>';
                    }

                    html += '<div class="carousel-inner">';
                    photos.forEach(function(p, i) {
                        html += '<div class="carousel-item' + (i === 0 ? ' active' : '') + '">';
                        html += '<img src="' + p.url + '" alt="' + p.label + '">';
                        html += '<div class="photo-label-overlay">' + p.label + '</div>';
                        html += '</div>';
                    });
                    html += '</div>';

                    if (photos.length > 1) {
                        html += '<button class="carousel-control-prev" type="button" data-bs-target="#' + carouselId + '" data-bs-slide="prev">';
                        html += '<span class="carousel-control-prev-icon"></span></button>';
                        html += '<button class="carousel-control-next" type="button" data-bs-target="#' + carouselId + '" data-bs-slide="next">';
                        html += '<span class="carousel-control-next-icon"></span></button>';
                    }
                    html += '</div>';
                } else {
                    html += '<div class="compare-carousel d-flex align-items-center justify-content-center" style="height:280px;">';
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
                photos.forEach(function(p) {
                    var img = document.getElementById('detail_' + p);
                    var wrapper = img.closest('.col-6');
                    if (d[p]) {
                        img.src = d[p];
                        wrapper.style.display = 'block';
                    } else {
                        img.src = '';
                        wrapper.style.display = 'none';
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

    // Click ảnh để xem fullscreen với carousel
    var photoIds = ['detail_photo_portrait', 'detail_photo_portrait_2', 'detail_photo_full_body', 'detail_photo_full_body_2'];
    var photoLabels = ['Chân dung 1', 'Chân dung 2', 'Toàn thân 1', 'Toàn thân 2'];

    function openImageViewer(startIndex) {
        var carouselInner = document.getElementById('fullscreen_carousel_inner');
        var indicators = document.getElementById('fullscreen_carousel_indicators');
        carouselInner.innerHTML = '';
        indicators.innerHTML = '';

        var validPhotos = [];
        photoIds.forEach(function(id, idx) {
            var img = document.getElementById(id);
            if (img && img.src && !img.src.endsWith('/')) {
                validPhotos.push({ src: img.src, label: photoLabels[idx], originalIndex: idx });
            }
        });

        if (validPhotos.length === 0) return;

        var activeIdx = 0;
        validPhotos.forEach(function(photo, idx) {
            if (photo.originalIndex === startIndex) activeIdx = idx;
        });

        validPhotos.forEach(function(photo, idx) {
            var item = document.createElement('div');
            item.className = 'carousel-item h-100' + (idx === activeIdx ? ' active' : '');
            item.innerHTML = '<div class="d-flex flex-column align-items-center justify-content-center h-100">' +
                '<img src="' + photo.src + '" class="img-fluid" style="max-height:calc(90vh - 50px);object-fit:contain;">' +
                '<div class="text-white mt-2 fs-5">' + photo.label + '</div>' +
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

    photoIds.forEach(function(id, idx) {
        var img = document.getElementById(id);
        if (img) {
            img.addEventListener('click', function() {
                if (this.src && !this.src.endsWith('/')) {
                    openImageViewer(idx);
                }
            });
        }
    });
});
