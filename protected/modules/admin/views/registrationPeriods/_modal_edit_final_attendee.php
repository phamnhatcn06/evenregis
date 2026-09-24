<div class="modal fade" id="modalEditFinalAttendee" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa fa-pencil me-2"></i>Cập nhật ảnh / chức danh</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <form id="form-edit-final-attendee">
                <div class="modal-body">
                    <input type="hidden" name="attendee_id" id="edit_attendee_id">
                    <div class="alert alert-info py-2">
                        <i class="fa fa-info-circle me-1"></i>Chỉ được sửa 2 trường dưới đây. Các thông tin thi đấu đã khoá.
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">Chức danh</label>
                        <input type="text" class="form-control" name="position" id="edit_position" maxlength="255" placeholder="VD: Trưởng phòng Kinh doanh">
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">Đường dẫn ảnh (photo_path)</label>
                        <input type="text" class="form-control" name="photo_path" id="edit_photo_path" maxlength="255" placeholder="VD: uploads/photos/xxx.jpg">
                        <small class="text-muted">Nhập đường dẫn ảnh mới nếu cần thay ảnh.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary" id="btn_submit_edit_final">
                        <i class="fa fa-save me-1"></i>Lưu
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
