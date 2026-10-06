<?php
/**
 * Modal cấu hình thể thức & bốc thăm cho một giai đoạn.
 *
 * @var SportDrawController $this
 */
?>
<div class="modal fade" id="modal-config" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-config">
                <div class="modal-header">
                    <h5 class="modal-title">Cấu hình thể thức thi đấu</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="SportStages[id]" id="cfg-id">

                    <div class="mb-3">
                        <label class="form-label">Tên giai đoạn</label>
                        <input type="text" class="form-control" name="SportStages[name]" id="cfg-name" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Thể thức</label>
                        <?php echo CHtml::dropDownList('SportStages[format]', '', SportStages::getFormatOptions(), array(
                            'class' => 'form-select',
                            'id' => 'cfg-format',
                            'prompt' => '-- Chọn thể thức --',
                        )); ?>
                    </div>

                    <div class="row g-3 cfg-group-fields d-none">
                        <div class="col-6">
                            <label class="form-label">Số bảng</label>
                            <input type="number" min="1" max="32" class="form-control" name="SportStages[num_groups]" id="cfg-num-groups">
                        </div>
                        <div class="col-6">
                            <label class="form-label">Số suất đi tiếp mỗi bảng</label>
                            <input type="number" min="1" max="32" class="form-control" name="SportStages[advance_per_group]" id="cfg-advance">
                        </div>
                    </div>

                    <div class="mb-3 cfg-bracket-fields d-none mt-3">
                        <label class="form-label">Kích thước nhánh loại trực tiếp</label>
                        <?php echo CHtml::dropDownList('SportStages[bracket_size]', '', array(
                            2 => '2', 4 => '4', 8 => '8', 16 => '16', 32 => '32', 64 => '64',
                        ), array(
                            'class' => 'form-select',
                            'id' => 'cfg-bracket-size',
                            'prompt' => '-- Tự động theo số đội --',
                        )); ?>
                        <small class="text-muted">Để trống sẽ tự tính theo số đội (làm tròn lên 2^n). Đội thiếu sẽ được xử lý bye.</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-primary" id="btn-config-submit">
                        <i class="fa fa-save me-1"></i>Lưu cấu hình
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
