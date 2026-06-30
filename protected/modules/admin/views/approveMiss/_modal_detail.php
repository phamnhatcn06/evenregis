<div class="modal fade" id="modalDetail" tabindex="-1" aria-labelledby="modalDetailLabel" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDetailLabel">Chi tiết thí sinh</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="detail_id">
                <input type="hidden" id="detail_contest_id">

                <div class="row mb-4">
                    <div class="col-12">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <th style="width:15%;">Họ tên</th>
                                    <td id="detail_name"></td>
                                    <th style="width:15%;">Đơn vị</th>
                                    <td id="detail_property"></td>
                                </tr>
                                <tr>
                                    <th>Cuộc thi</th>
                                    <td id="detail_contest"></td>
                                    <th>Trạng thái</th>
                                    <td id="detail_status"></td>
                                </tr>
                                <tr>
                                    <th>Chiều cao</th>
                                    <td id="detail_height"></td>
                                    <th>Cân nặng</th>
                                    <td id="detail_weight"></td>
                                </tr>
                                <tr>
                                    <th>Số đo 3 vòng</th>
                                    <td id="detail_measurements"></td>
                                    <th>Năng khiếu</th>
                                    <td id="detail_talent"></td>
                                </tr>
                                <tr>
                                    <th>Email cá nhân</th>
                                    <td id="detail_email"></td>
                                    <th>Ngày gửi hồ sơ</th>
                                    <td id="detail_submitted_at"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="row mb-4">
                    <div class="col-md-4">
                        <div class="photo-item-fullwidth text-center">
                            <img id="detail_photo_portrait" src="" alt="Ảnh chân dung" class="img-fluid" style="max-height:500px;object-fit:contain;">
                            <div class="photo-label mt-2">Chân dung</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="photo-item-fullwidth text-center">
                            <img id="detail_photo_full_body" src="" alt="Ảnh toàn thân" class="img-fluid" style="max-height:500px;object-fit:contain;">
                            <div class="photo-label mt-2">Toàn thân</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="photo-item-fullwidth text-center">
                            <img id="detail_photo_full_body_2" src="" alt="Ảnh toàn thân 2" class="img-fluid" style="max-height:500px;object-fit:contain;">
                            <div class="photo-label mt-2">Toàn thân 2</div>
                        </div>
                    </div>
                </div>

                <div id="detail_video_container" class="row" style="display:none;">
                    <div class="col-12">
                        <label class="form-label fw-bold">Video dự thi:</label>
                        <video id="detail_video" controls class="w-100" style="max-height:400px;"></video>
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
