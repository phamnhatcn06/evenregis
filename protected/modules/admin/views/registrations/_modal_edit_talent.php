<!-- Modal Sửa Tiết Mục Văn Nghệ -->
<div class="modal fade" id="editTalentModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa fa-edit me-2"></i>Sửa tiết mục văn nghệ</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editTalentForm" method="post">
                <div class="modal-body">
                    <input type="hidden" name="talent_entry_id" id="edit_talent_id">

                    <div class="row">
                        <!-- Cột trái: Form fields -->
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Tên tiết mục <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="title" id="edit_talent_title" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Thể loại <span class="text-danger">*</span></label>
                                <select class="form-select" name="category_id" id="edit_talent_category" required>
                                    <option value="">-- Chọn thể loại --</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Thời lượng (giây)</label>
                                <input type="number" class="form-control" name="duration_seconds" id="edit_talent_duration" min="0" placeholder="VD: 180">
                                <small class="text-muted">VD: 180 giây = 3 phút</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Đạo diễn/Biên đạo</label>
                                <input type="text" class="form-control" name="director" id="edit_talent_director" placeholder="Tên người biên đạo">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">SĐT đạo diễn</label>
                                <input type="text" class="form-control" name="director_phone" id="edit_talent_director_phone" placeholder="Số điện thoại">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Mô tả ngắn</label>
                                <textarea class="form-control" name="description" id="edit_talent_description" rows="2" placeholder="Mô tả nội dung tiết mục..."></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nội dung chi tiết</label>
                                <textarea class="form-control" name="content" id="edit_talent_content" rows="2" placeholder="Nội dung chi tiết, kịch bản, lời bài hát..."></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Nguồn gốc/Xuất xứ</label>
                                <input type="text" class="form-control" name="origin" id="edit_talent_origin" placeholder="VD: Dân ca Bắc Bộ, Nhạc trẻ...">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Số người tham gia</label>
                                <input type="number" class="form-control" name="participant_count" id="edit_talent_participant_count" min="1">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Link nhạc nền</label>
                                <input type="url" class="form-control" name="music_path" id="edit_talent_music_path" placeholder="https://...">
                                <small class="text-muted">Link nhạc nền (Google Drive, Dropbox...)</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Link video</label>
                                <div class="input-group">
                                    <input type="url" class="form-control" name="video_path" id="edit_talent_video_path" placeholder="https://youtube.com/...">
                                    <button type="button" class="btn btn-outline-secondary" onclick="previewEditVideo()" title="Xem trước">
                                        <i class="fa fa-play"></i>
                                    </button>
                                </div>
                                <small class="text-muted">Link YouTube hoặc video trực tiếp</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Ghi chú</label>
                                <textarea class="form-control" name="note" id="edit_talent_note" rows="2" placeholder="Ghi chú thêm..."></textarea>
                            </div>
                        </div>

                        <!-- Cột phải: Chọn người tham gia -->
                        <div class="col-md-8">
                            <label class="form-label">Chọn người biểu diễn <span class="text-danger">*</span></label>
                            <small class="text-muted d-block mb-2">Danh sách người có vai trò "Văn nghệ" của đơn vị</small>
                            <div class="row" id="edit_talent_dual_listbox_wrapper">
                                <div class="col-md-5">
                                    <div class="card h-100">
                                        <div class="card-header py-2">
                                            <small class="fw-bold">Danh sách người tham dự</small>
                                            <input type="text" class="form-control form-control-sm mt-2" id="edit_talent_search" placeholder="Tìm kiếm...">
                                        </div>
                                        <div class="card-body p-0" style="height:350px;overflow-y:auto;">
                                            <div class="list-group list-group-flush" id="edit_talent_available_list">
                                                <div class="text-center text-muted p-3">Đang tải...</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2 d-flex flex-column justify-content-center align-items-center">
                                    <button type="button" class="btn btn-sm btn-outline-primary mb-2" id="edit_talent_btn_add" title="Thêm">
                                        <i class="fa fa-chevron-right"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-primary mb-2" id="edit_talent_btn_add_all" title="Thêm tất cả">
                                        <i class="fa fa-angle-double-right"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" id="edit_talent_btn_remove" title="Xóa">
                                        <i class="fa fa-chevron-left"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger mt-2" id="edit_talent_btn_remove_all" title="Xóa tất cả">
                                        <i class="fa fa-angle-double-left"></i>
                                    </button>
                                </div>
                                <div class="col-md-5">
                                    <div class="card h-100">
                                        <div class="card-header py-2">
                                            <small class="fw-bold">Đã chọn (<span id="edit_talent_selected_count">0</span>)</small>
                                        </div>
                                        <div class="card-body p-0" style="height:390px;overflow-y:auto;">
                                            <div class="list-group list-group-flush" id="edit_talent_selected_list">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-sm btn-primary">
                        <i class="fa fa-save me-1"></i>Lưu thay đổi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
