<div class="modal fade" id="modalDetail" tabindex="-1" aria-labelledby="modalDetailLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDetailLabel">Chi tiết tiết mục</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="detail_id">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <th style="width:40%;">Tên tiết mục</th>
                                    <td id="detail_title"></td>
                                </tr>
                                <tr>
                                    <th>Đơn vị</th>
                                    <td id="detail_property"></td>
                                </tr>
                                <tr>
                                    <th>Hội diễn</th>
                                    <td id="detail_show"></td>
                                </tr>
                                <tr>
                                    <th>Thể loại</th>
                                    <td id="detail_category"></td>
                                </tr>
                                <tr>
                                    <th>Trạng thái</th>
                                    <td id="detail_status"></td>
                                </tr>
                                <tr>
                                    <th>Thời lượng</th>
                                    <td id="detail_duration"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <th style="width:40%;">Số người tham gia</th>
                                    <td id="detail_participant_count"></td>
                                </tr>
                                <tr>
                                    <th>Đội liên quân</th>
                                    <td id="detail_alliance"></td>
                                </tr>
                                <tr>
                                    <th>Đạo diễn/Biên đạo</th>
                                    <td id="detail_director"></td>
                                </tr>
                                <tr>
                                    <th>SĐT đạo diễn</th>
                                    <td id="detail_director_phone"></td>
                                </tr>
                                <tr>
                                    <th>Nguồn gốc</th>
                                    <td id="detail_origin"></td>
                                </tr>
                                <tr>
                                    <th>Ngày đăng ký</th>
                                    <td id="detail_created_at"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="mb-3">
                            <label class="form-label fw-bold">Mô tả:</label>
                            <div id="detail_description" class="border rounded p-2 bg-light" style="min-height:60px;max-height:150px;overflow-y:auto;"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Nội dung chi tiết:</label>
                            <div id="detail_content" class="border rounded p-2 bg-light" style="min-height:60px;max-height:150px;overflow-y:auto;"></div>
                        </div>
                        <div class="mb-3" id="detail_note_container" style="display:none;">
                            <label class="form-label fw-bold">Ghi chú:</label>
                            <div id="detail_note" class="border rounded p-2 bg-light"></div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4" id="detail_music_container" style="display:none;">
                        <label class="form-label fw-bold">File nhạc:</label>
                        <audio id="detail_music" controls class="w-100"></audio>
                    </div>
                    <div class="col-md-8" id="detail_video_container" style="display:none;">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label fw-bold mb-0">Video:</label>
                            <a id="detail_video_download" href="#" class="btn btn-sm btn-outline-primary" download>
                                <i class="fa fa-download me-1"></i>Tải bản gốc
                            </a>
                        </div>
                        <div id="detail_video_wrapper">
                            <video id="detail_video" class="plyr-video" playsinline controls preload="auto" style="max-height:250px;"></video>
                        </div>
                    </div>
                </div>
                <div class="row mt-3" id="detail_document_container" style="display:none;">
                    <div class="col-12">
                        <label class="form-label fw-bold">Tài liệu:</label>
                        <a id="detail_document" href="#" target="_blank" class="btn btn-outline-primary btn-sm">
                            <i class="fa fa-file me-1"></i>Tải tài liệu
                        </a>
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
