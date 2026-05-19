<!-- Modal Add Sports -->
<div class="modal fade" id="addDetailModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="<?php echo $this->createUrl('addDetail'); ?>" id="add-detail-form">
                <input type="hidden" name="registration_id" value="<?php echo $model->id; ?>">
                <input type="hidden" name="content_type" id="content_type" value="sports">
                <input type="hidden" name="content_id" id="content_id" value="">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fa fa-futbol-o me-2"></i>Đăng ký thể thao</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Môn thể thao <span class="text-danger">*</span></label>
                        <select class="form-select" id="item_id" name="item_id" required>
                            <option value="">-- Chọn môn thể thao --</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Số đội/người <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="quantity" id="quantity" value="1" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Ghi chú</label>
                        <textarea class="form-control" name="note" id="note" rows="2" placeholder="Ghi chú thêm..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-success">Đăng ký</button>
                </div>
            </form>
        </div>
    </div>
</div>
