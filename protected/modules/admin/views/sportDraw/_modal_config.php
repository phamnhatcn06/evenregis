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

                    <div class="mb-3">
                        <label class="form-label">Giờ bắt đầu thi đấu <span class="text-muted">(không bắt buộc)</span></label>
                        <input type="datetime-local" class="form-control" name="SportStages[start_time]" id="cfg-start-time">
                        <small class="text-muted">Hiển thị trên trang Lịch thi đấu công khai (dùng cho nội dung bơi).</small>
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

                    <div class="cfg-group-fields d-none mt-3">
                        <label class="form-label fw-semibold">Cách tính điểm vòng bảng</label>
                        <div class="row g-2">
                            <div class="col-4">
                                <label class="form-label small text-muted mb-1">Thắng</label>
                                <input type="number" min="0" max="10" class="form-control" name="SportStages[win_points]" id="cfg-win-points">
                            </div>
                            <div class="col-4">
                                <label class="form-label small text-muted mb-1">Hoà</label>
                                <input type="number" min="0" max="10" class="form-control" name="SportStages[draw_points]" id="cfg-draw-points">
                            </div>
                            <div class="col-4">
                                <label class="form-label small text-muted mb-1">Thua</label>
                                <input type="number" min="0" max="10" class="form-control" name="SportStages[loss_points]" id="cfg-loss-points">
                            </div>
                        </div>
                        <div class="mt-2">
                            <label class="form-label small text-muted mb-1">Tiêu chí phụ khi bằng điểm</label>
                            <?php echo CHtml::dropDownList('SportStages[tiebreak]', '', SportStages::getTiebreakOptions(), array(
                                'class' => 'form-select',
                                'id' => 'cfg-tiebreak',
                            )); ?>
                        </div>
                    </div>

                    <div class="mt-3 cfg-pairing-fields d-none">
                        <label class="form-label">Cách ghép đội từ bảng vào loại trực tiếp</label>
                        <?php echo CHtml::dropDownList('SportStages[ko_pairing]', '', SportStages::getPairingOptions(), array(
                            'class' => 'form-select',
                            'id' => 'cfg-ko-pairing',
                        )); ?>
                    </div>

                    <div class="mt-3 cfg-playoff-fields d-none">
                        <label class="form-label">Giai đoạn play-off nguồn <span class="text-muted">(không bắt buộc)</span></label>
                        <select class="form-select" name="SportStages[playoff_source_stage_id]" id="cfg-playoff-source">
                            <option value="">-- Không đặt chỗ trước play-off --</option>
                        </select>
                        <small class="text-muted">Khi bốc bảng sẽ đặt sẵn 1 slot cho mỗi trận play-off; đội thắng tự điền vào slot khi có kết quả.</small>
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
