<div class="modal fade" id="modalDetail" tabindex="-1" aria-labelledby="modalDetailLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDetailLabel">Chi tiết tiết mục</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="detail_id">
                <input type="hidden" id="detail_show_id">
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
                    <div class="col-auto" id="detail_music_container" style="display:none;">
                        <button type="button" class="btn btn-info" id="btn_play_music">
                            <i class="fa fa-music me-1"></i>Nghe nhạc
                        </button>
                        <input type="hidden" id="detail_music_src">
                    </div>
                    <div class="col-auto" id="detail_video_container" style="display:none;">
                        <button type="button" class="btn btn-primary" id="btn_play_video">
                            <i class="fa fa-play me-1"></i>Xem Video
                        </button>
                        <a id="detail_video_download" href="#" class="btn btn-outline-primary" download>
                            <i class="fa fa-download me-1"></i>Tải bản gốc
                        </a>
                        <input type="hidden" id="detail_video_src">
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

            <!-- Modal xem video fullscreen -->
            <div class="modal fade" id="modalVideoViewer" tabindex="-1" style="z-index:1060;">
                <div class="modal-dialog modal-fullscreen">
                    <div class="modal-content bg-dark">
                        <div class="modal-header border-0 py-2">
                            <span class="text-white fw-bold">Video tiết mục</span>
                            <div class="ms-auto d-flex gap-2">
                                <a id="fullscreen_video_download" href="#" class="btn btn-sm btn-outline-light" download>
                                    <i class="fa fa-download me-1"></i>Tải bản gốc
                                </a>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                            </div>
                        </div>
                        <div class="modal-body d-flex align-items-center justify-content-center p-0">
                            <video id="fullscreen_video" playsinline controls preload="auto" style="max-width:100%;max-height:100%;"></video>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal nghe nhạc fullscreen -->
            <div class="modal fade" id="modalMusicViewer" tabindex="-1" style="z-index:1060;">
                <div class="modal-dialog modal-dialog-centered">
                    <div class="modal-content bg-dark">
                        <div class="modal-header border-0 py-2">
                            <span class="text-white fw-bold"><i class="fa fa-music me-2"></i>File nhạc tiết mục</span>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body text-center p-4">
                            <div class="mb-3">
                                <i class="fa fa-music fa-4x text-info"></i>
                            </div>
                            <audio id="fullscreen_music" controls preload="auto" class="w-100"></audio>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
