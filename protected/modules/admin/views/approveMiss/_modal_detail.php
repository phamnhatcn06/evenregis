<div class="modal fade" id="modalDetail" tabindex="-1" aria-labelledby="modalDetailLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDetailLabel">Chi tiết thí sinh</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-5">
                        <div id="detail_photos" class="contestant-photos">
                            <div class="photo-grid">
                                <div class="photo-item main-photo">
                                    <img id="detail_photo_portrait" src="" alt="Ảnh chân dung">
                                    <span class="photo-label">Chân dung 1</span>
                                </div>
                                <div class="photo-item">
                                    <img id="detail_photo_portrait_2" src="" alt="Ảnh chân dung 2">
                                    <span class="photo-label">Chân dung 2</span>
                                </div>
                                <div class="photo-item">
                                    <img id="detail_photo_full_body" src="" alt="Ảnh toàn thân">
                                    <span class="photo-label">Toàn thân 1</span>
                                </div>
                                <div class="photo-item">
                                    <img id="detail_photo_full_body_2" src="" alt="Ảnh toàn thân 2">
                                    <span class="photo-label">Toàn thân 2</span>
                                </div>
                            </div>
                            <div id="detail_video_container" class="mt-3" style="display:none;">
                                <label class="form-label fw-bold">Video dự thi:</label>
                                <video id="detail_video" controls class="w-100" style="max-height:300px;"></video>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-7">
                        <input type="hidden" id="detail_id">
                        <table class="table table-bordered">
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
                        <div class="mb-3">
                            <label class="form-label fw-bold">Tiểu sử:</label>
                            <div id="detail_bio" class="border rounded p-2 bg-light" style="min-height:80px;max-height:200px;overflow-y:auto;"></div>
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
