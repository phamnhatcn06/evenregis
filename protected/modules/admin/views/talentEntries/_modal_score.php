<div class="modal fade" id="modal_score" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form_score">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal_score_title">Thêm điểm</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" id="score_id">
                    <input type="hidden" name="entry_id" value="<?php echo $entry->id; ?>">

                    <div class="mb-3">
                        <label class="form-label">Giám khảo <span class="text-danger">*</span></label>
                        <input type="number" name="judge_id" id="score_judge_id" class="form-control" min="1" placeholder="Nhập mã số giám khảo" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tiêu chí</label>
                        <input type="text" name="criteria" id="score_criteria" class="form-control" maxlength="100" placeholder="VD: Giọng hát, Vũ đạo...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Điểm <span class="text-danger">*</span></label>
                        <input type="number" name="score" id="score_value" class="form-control" min="0" step="0.01" placeholder="Nhập điểm" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nhận xét</label>
                        <textarea name="note" id="score_note" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary" id="btn_submit_score">
                        <i class="fa fa-save me-1"></i>Lưu
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
