<div class="modal fade" id="modalAddDriver" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fa fa-car me-2"></i>Thêm lái xe</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <form id="form-add-driver">
                <div class="modal-body">
                    <input type="hidden" name="type" value="driver">
                    <input type="hidden" name="property_id" id="driver_property_id">
                    <p class="mb-2">Đơn vị: <strong id="driver_property_name"></strong></p>

                    <div class="form-group mb-3">
                        <label class="form-label">Họ tên lái xe <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="full_name" id="driver_full_name" maxlength="255" required>
                    </div>
                    <div class="form-group mb-3">
                        <label class="form-label">Chức danh</label>
                        <input type="text" class="form-control" name="position" id="driver_position" maxlength="255" placeholder="VD: Lái xe">
                    </div>
                    <div class="form-group mb-0">
                        <label class="form-label">Ảnh (tùy chọn)</label>
                        <input type="file" class="form-control photo-file-input" accept="image/jpeg,image/png"
                            data-target="driver_photo_path" data-preview="driver_photo_preview">
                        <input type="hidden" name="photo_path" id="driver_photo_path">
                        <small class="text-muted d-block photo-upload-status"></small>
                        <img id="driver_photo_preview" src="" alt="" class="img-thumbnail mt-2 d-none" style="max-height:120px">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-info text-white" id="btn_submit_driver">
                        <i class="fa fa-save me-1"></i>Thêm lái xe
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
