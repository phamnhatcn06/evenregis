<?php
/**
 * Modal chọn bộ môn để xem đội thể thao
 * @var array $sports Danh sách môn thể thao
 * @var array $events Danh sách sự kiện
 */
?>
<div class="modal fade" id="modalSelectSport" tabindex="-1" aria-labelledby="modalSelectSportLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalSelectSportLabel">
                    <i class="fa fa-futbol-o me-2"></i>Chọn bộ môn
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="select_event_sport" class="form-label">Sự kiện <span class="text-danger">*</span></label>
                    <select id="select_event_sport" class="form-select" required>
                        <option value="">-- Chọn sự kiện --</option>
                        <?php foreach ($events as $id => $name): ?>
                            <option value="<?php echo $id; ?>"><?php echo CHtml::encode($name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="select_sport" class="form-label">Bộ môn <span class="text-danger">*</span></label>
                    <select id="select_sport" class="form-select" required>
                        <option value="">-- Chọn bộ môn --</option>
                        <?php foreach ($sports as $sport): ?>
                            <option value="<?php echo $sport->id; ?>"><?php echo CHtml::encode($sport->name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-success" id="btn_view_by_sport">
                    <i class="fa fa-eye me-1"></i>Xem
                </button>
            </div>
        </div>
    </div>
</div>
