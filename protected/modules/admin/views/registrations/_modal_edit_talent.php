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
                        <div class="col-md-12">
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
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-sm btn-primary" id="btn_submit_edit_talent">
                        <i class="fa fa-save me-1"></i>Lưu thay đổi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>