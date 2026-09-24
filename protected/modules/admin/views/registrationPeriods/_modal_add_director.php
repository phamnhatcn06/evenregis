<div class="modal fade" id="modalAddDirector" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa fa-user-tie me-2"></i>Thêm giám đốc (phòng ban 610)</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <form id="form-add-director">
                <div class="modal-body">
                    <input type="hidden" name="type" value="director">
                    <input type="hidden" name="property_id" id="director_property_id">
                    <p class="mb-2">Đơn vị: <strong id="director_property_name"></strong></p>

                    <div class="form-group mb-3">
                        <label class="form-label">Chọn nhân sự (phòng ban 610)</label>
                        <select class="form-select" name="staff_id" id="director_staff_id">
                            <option value="">-- Đang tải danh sách... --</option>
                        </select>
                        <small class="text-muted">Chọn từ danh sách nhân sự thuộc phòng ban 610 của đơn vị.</small>
                    </div>

                    <div class="form-group mb-3">
                        <label class="form-label">Hoặc nhập họ tên thủ công</label>
                        <input type="text" class="form-control" name="full_name" id="director_full_name" maxlength="255" placeholder="Để trống nếu đã chọn nhân sự">
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">Chức danh</label>
                        <input type="text" class="form-control" name="position" id="director_position" maxlength="255" placeholder="VD: Giám đốc">
                    </div>
                    <div class="form-group mb-0">
                        <label class="form-label">Đường dẫn ảnh (tùy chọn)</label>
                        <input type="text" class="form-control" name="photo_path" id="director_photo_path" maxlength="255">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary" id="btn_submit_director">
                        <i class="fa fa-save me-1"></i>Thêm giám đốc
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
