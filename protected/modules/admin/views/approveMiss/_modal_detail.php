<div class="modal fade" id="modalDetail" tabindex="-1" aria-labelledby="modalDetailLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDetailLabel">Chi tiết thí sinh</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3" style="height:calc(100vh - 120px);overflow:hidden;">
                <input type="hidden" id="detail_id">
                <input type="hidden" id="detail_contest_id">

                <div class="row h-100">
                    <!-- Cột trái: 4 ảnh (2x2) -->
                    <div class="col-md-6 h-100">
                        <div class="row g-2 h-100">
                            <div class="col-6" style="height:50%;">
                                <div class="photo-wrapper text-center border rounded p-1 h-100 d-flex flex-column position-relative">
                                    <img id="detail_photo_portrait" src="" alt="Ảnh chân dung" class="img-fluid flex-grow-1 photo-zoomable" style="object-fit:contain;max-height:calc(100% - 25px);cursor:pointer;">
                                    <div class="photo-zoom-icon"><i class="fa fa-search-plus"></i></div>
                                    <div class="photo-label fw-bold small">Chân dung 1</div>
                                </div>
                            </div>
                            <div class="col-6" style="height:50%;">
                                <div class="photo-wrapper text-center border rounded p-1 h-100 d-flex flex-column position-relative">
                                    <img id="detail_photo_portrait_2" src="" alt="Ảnh chân dung 2" class="img-fluid flex-grow-1 photo-zoomable" style="object-fit:contain;max-height:calc(100% - 25px);cursor:pointer;">
                                    <div class="photo-zoom-icon"><i class="fa fa-search-plus"></i></div>
                                    <div class="photo-label fw-bold small">Chân dung 2</div>
                                </div>
                            </div>
                            <div class="col-6" style="height:50%;">
                                <div class="photo-wrapper text-center border rounded p-1 h-100 d-flex flex-column position-relative">
                                    <img id="detail_photo_full_body" src="" alt="Ảnh toàn thân" class="img-fluid flex-grow-1 photo-zoomable" style="object-fit:contain;max-height:calc(100% - 25px);cursor:pointer;">
                                    <div class="photo-zoom-icon"><i class="fa fa-search-plus"></i></div>
                                    <div class="photo-label fw-bold small">Toàn thân 1</div>
                                </div>
                            </div>
                            <div class="col-6" style="height:50%;">
                                <div class="photo-wrapper text-center border rounded p-1 h-100 d-flex flex-column position-relative">
                                    <img id="detail_photo_full_body_2" src="" alt="Ảnh toàn thân 2" class="img-fluid flex-grow-1 photo-zoomable" style="object-fit:contain;max-height:calc(100% - 25px);cursor:pointer;">
                                    <div class="photo-zoom-icon"><i class="fa fa-search-plus"></i></div>
                                    <div class="photo-label fw-bold small">Toàn thân 2</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cột phải: Thông tin + Video -->
                    <div class="col-md-6 h-100 d-flex flex-column">
                        <table class="table table-bordered table-sm mb-2">
                            <tbody>
                                <tr>
                                    <th style="width:35%;">Họ tên</th>
                                    <td id="detail_name"></td>
                                </tr>
                                <tr>
                                    <th>Đơn vị</th>
                                    <td id="detail_property"></td>
                                </tr>
                                <tr>
                                    <th>Cuộc thi</th>
                                    <td id="detail_contest"></td>
                                </tr>
                                <tr>
                                    <th>Trạng thái</th>
                                    <td id="detail_status"></td>
                                </tr>
                                <tr>
                                    <th>Chiều cao</th>
                                    <td id="detail_height"></td>
                                </tr>
                                <tr>
                                    <th>Cân nặng</th>
                                    <td id="detail_weight"></td>
                                </tr>
                                <tr>
                                    <th>Số đo 3 vòng</th>
                                    <td id="detail_measurements"></td>
                                </tr>
                                <tr>
                                    <th>Năng khiếu</th>
                                    <td id="detail_talent"></td>
                                </tr>
                                <tr>
                                    <th>Email cá nhân</th>
                                    <td id="detail_email"></td>
                                </tr>
                                <tr>
                                    <th>Ngày gửi hồ sơ</th>
                                    <td id="detail_submitted_at"></td>
                                </tr>
                            </tbody>
                        </table>

                        <div id="detail_video_container" class="mt-2 flex-grow-1 d-flex flex-column" style="display:none;min-height:0;">
                            <label class="form-label fw-bold small mb-1">Video dự thi:</label>
                            <div id="detail_video_wrapper" class="flex-grow-1" style="min-height:0;">
                                <video id="detail_video" class="plyr-video" playsinline controls preload="metadata" style="width:100%;height:100%;object-fit:contain;"></video>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal xem ảnh fullscreen với carousel -->
            <div class="modal fade" id="modalImageViewer" tabindex="-1" style="z-index:1060;">
                <div class="modal-dialog modal-fullscreen">
                    <div class="modal-content bg-dark">
                        <div class="modal-header border-0 py-2">
                            <span id="fullscreen_label" class="text-white"></span>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body d-flex align-items-center justify-content-center p-0">
                            <div id="fullscreenCarousel" class="carousel slide w-100 h-100" data-bs-ride="false">
                                <div class="carousel-inner h-100" id="fullscreen_carousel_inner">
                                </div>
                                <button class="carousel-control-prev" type="button" data-bs-target="#fullscreenCarousel" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#fullscreenCarousel" data-bs-slide="next">
                                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                                </button>
                                <div class="carousel-indicators" id="fullscreen_carousel_indicators">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-danger btn-reject-modal" id="btn_reject_modal">
                    <i class="fa fa-times me-1"></i>Từ chối
                </button>
                <button type="button" class="btn btn-success btn-approve-modal" id="btn_approve_modal">
                    <i class="fa fa-check me-1"></i>Duyệt
                </button>
            </div>
        </div>
    </div>
</div>
