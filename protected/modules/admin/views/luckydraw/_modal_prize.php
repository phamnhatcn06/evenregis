<?php
/**
 * Modal thêm/sửa giải thưởng.
 * @var array $types Danh sách attendee_type để chọn eligible_types
 */
?>
<div class="modal fade" id="modal-prize" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="form-prize">
                <input type="hidden" name="id" id="prize-id">
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-prize-title">Thêm giải</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label">Tên giải <span class="text-danger">*</span></label>
                            <input type="text" name="prize_name" id="prize-name" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Thứ tự quay <span class="text-danger">*</span></label>
                            <input type="number" name="prize_order" id="prize-order" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Mã hiệu ứng (code)</label>
                            <input type="text" name="code" id="prize-code" class="form-control" placeholder="special / first / ...">
                            <small class="text-muted">"special" bật màn che + confetti lớn.</small>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Số lượng giải</label>
                            <input type="number" name="quantity" id="prize-quantity" class="form-control" value="1" min="1">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Số người quay mỗi lần</label>
                            <input type="number" name="spin_count" id="prize-spin-count" class="form-control" value="1" min="1">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Thời gian quay (ms)</label>
                            <input type="number" name="duration" id="prize-duration" class="form-control" value="3200" min="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Độ lệch dừng reel (ms)</label>
                            <input type="number" name="stagger_duration" id="prize-stagger" class="form-control" value="0" min="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Loại trừ level (CSV)</label>
                            <input type="text" name="exclude_levels" id="prize-exclude-levels" class="form-control" placeholder="vd: 1,2">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Áp dụng cho loại người tham dự (type)</label>
                            <select name="eligible_types[]" id="prize-eligible-types" class="form-select" multiple size="5">
                                <?php foreach ($types as $t): ?>
                                    <option value="<?php echo CHtml::encode($t); ?>"><?php echo CHtml::encode($t); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Không chọn = áp dụng cho tất cả. Giữ Ctrl/Cmd để chọn nhiều.</small>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
                    <button type="submit" class="btn btn-primary" id="btn-submit-prize">
                        <i class="fa fa-save me-1"></i>Lưu
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
