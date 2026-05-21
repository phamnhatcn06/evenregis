<?php
/**
 * Partial view: Competition Departments - BR-REG-06
 * Quản lý danh sách phòng ban được phép tham gia thi nghiệp vụ
 *
 * @var Competitions $model
 * @var array $allDepartments Danh sách tất cả phòng ban
 * @var array $selectedDepartments Danh sách phòng ban đã chọn
 */

$selectedDepartments = isset($selectedDepartments) ? $selectedDepartments : array();
?>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">
            <i class="fa fa-building"></i> Phòng ban được phép thi
        </h5>
        <small class="text-muted">BR-REG-06: Chỉ nhân viên thuộc phòng ban này mới được đăng ký</small>
    </div>
    <div class="card-body">
        <?php
Yii::app()->clientScript->registerScriptFile(
    Yii::app()->theme->baseUrl . '/assets/js/pages/competitions-form.js',
    CClientScript::POS_END
);
?>
        <?php if (empty($allDepartments)): ?>
            <div class="alert alert-warning">
                <i class="fa fa-exclamation-triangle"></i>
                Chưa có danh sách phòng ban. Vui lòng cập nhật mã phòng ban cho nhân viên.
            </div>
        <?php else: ?>
            <p class="text-muted mb-3">
                <i class="fa fa-info-circle"></i>
                Nếu không chọn phòng ban nào, tất cả người tham dự đều có thể đăng ký thi.
            </p>

            <div class="row">
                <?php foreach ($allDepartments as $code => $name): ?>
                    <div class="col-md-4 col-lg-3 mb-2">
                        <div class="form-check">
                            <input class="form-check-input department-checkbox"
                                   type="checkbox"
                                   name="CompetitionDepartments[]"
                                   value="<?php echo CHtml::encode($code); ?>"
                                   id="dept_<?php echo CHtml::encode($code); ?>"
                                   <?php echo in_array($code, $selectedDepartments) ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="dept_<?php echo CHtml::encode($code); ?>">
                                <?php echo CHtml::encode($name); ?>
                            </label>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <hr>

            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-outline-primary" id="select-all-depts">
                    <i class="fa fa-check-square-o"></i> Chọn tất cả
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary" id="deselect-all-depts">
                    <i class="fa fa-square-o"></i> Bỏ chọn tất cả
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>
