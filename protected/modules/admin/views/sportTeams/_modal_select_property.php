<?php
/**
 * Modal chọn đơn vị để xem đội thể thao
 * @var array $properties Danh sách đơn vị
 * @var array $events Danh sách sự kiện
 */
$booster = Yii::app()->booster;
$assetsUrl = $booster->getAssetsUrl();
Yii::app()->clientScript->registerCssFile($assetsUrl . '/select2/select2.css');
Yii::app()->clientScript->registerScriptFile($assetsUrl . '/select2/select2.min.js', CClientScript::POS_END);
?>
<div class="modal fade" id="modalSelectProperty" tabindex="-1" aria-labelledby="modalSelectPropertyLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalSelectPropertyLabel">
                    <i class="fa fa-building me-2"></i>Chọn đơn vị
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label for="select_event_property" class="form-label">Sự kiện <span class="text-danger">*</span></label>
                    <select id="select_event_property" class="form-select" required>
                        <option value="">-- Chọn sự kiện --</option>
                        <?php foreach ($events as $id => $name): ?>
                            <option value="<?php echo $id; ?>"><?php echo CHtml::encode($name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="select_property" class="form-label">Đơn vị <span class="text-danger">*</span></label>
                    <select id="select_property" class="form-select select2-property" required>
                        <option value="">-- Chọn đơn vị --</option>
                        <?php foreach ($properties as $id => $name): ?>
                            <option value="<?php echo $id; ?>"><?php echo CHtml::encode($name); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Hủy</button>
                <button type="button" class="btn btn-sm btn-primary" id="btn_view_by_property">
                    <i class="fa fa-eye me-1"></i>Xem
                </button>
            </div>
        </div>
    </div>
</div>

<style>
.select2-container { z-index: 9999; }
.select2-drop { z-index: 99999; }
.select2-drop-mask { z-index: 99998; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var modal = document.getElementById('modalSelectProperty');
    modal.addEventListener('shown.bs.modal', function() {
        if (typeof $ !== 'undefined' && $.fn.select2) {
            $('#select_property').select2({
                dropdownParent: $('#modalSelectProperty'),
                placeholder: '-- Chọn đơn vị --',
                allowClear: true,
                width: '100%'
            });
        }
    });
});
</script>
