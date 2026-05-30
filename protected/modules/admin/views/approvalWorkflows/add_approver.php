<?php
$this->breadcrumbs = array(
    'Quy trình duyệt' => array('admin'),
    $workflow->name => array('view', 'id' => $workflow->id),
    'Thêm người duyệt',
);

$this->menu = array(
    array(
        'label' => 'Quay lại',
        'url' => $this->createUrl('view', array('id' => $workflow->id)),
        'color' => 'secondary',
        'icon' => 'fa-arrow-left',
        'id' => 'btn_create'
    ),
);
$this->Tabletitle = 'Thêm người duyệt cho: ' . CHtml::encode($workflow->name);

$booster = Yii::app()->booster;
$assetsUrl = $booster->getAssetsUrl();
Yii::app()->clientScript->registerCssFile($assetsUrl . '/select2/select2.css');
Yii::app()->clientScript->registerScriptFile($assetsUrl . '/select2/select2.min.js', CClientScript::POS_END);
?>

<form id="add-approver-form" method="post">
    <input type="hidden" name="<?php echo Yii::app()->request->csrfTokenName; ?>" value="<?php echo Yii::app()->request->csrfToken; ?>">

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Chọn người duyệt</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <label class="col-md-3 col-form-label">Bước duyệt <span class="text-danger">*</span></label>
                        <div class="col-md-3">
                            <select name="step_index" class="form-select" required>
                                <option value="">-- Chọn bước --</option>
                                <?php for ($i = 1; $i <= $workflow->total_steps; $i++): ?>
                                    <option value="<?php echo $i; ?>">Bước <?php echo $i; ?></option>
                                <?php endfor; ?>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <label class="col-md-3 col-form-label">Tên bước <span class="text-danger">*</span></label>
                        <div class="col-md-9">
                            <input type="text" name="step_name" class="form-control"
                                   placeholder="VD: Giám đốc đơn vị, Nhân sự TĐ..." maxlength="255" required>
                        </div>
                    </div>

                    <hr>

                    <div class="row mb-3">
                        <label class="col-md-3 col-form-label">
                            Chọn nhân viên <span class="text-danger">*</span>
                        </label>
                        <div class="col-md-9">
                            <select id="staff-select" name="staff_ids[]" multiple="multiple" class="form-control" style="width:100%;">
                                <?php foreach ($staffList as $id => $name): ?>
                                    <option value="<?php echo $id; ?>"><?php echo CHtml::encode($name); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <small class="text-muted">Gõ để tìm kiếm, có thể chọn nhiều người</small>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    <div class="d-flex justify-content-end gap-2">
                        <a href="<?php echo $this->createUrl('view', array('id' => $workflow->id)); ?>" class="btn btn-secondary">
                            <i class="fa fa-arrow-left"></i> Quay lại
                        </a>
                        <button type="submit" class="btn btn-primary" id="btn-submit">
                            <i class="fa fa-save"></i> Thêm người duyệt
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card bg-light">
                <div class="card-body">
                    <h6 class="card-title"><i class="fa fa-info-circle"></i> Hướng dẫn</h6>
                    <ul class="small text-muted mb-0">
                        <li class="mb-2"><strong>Bước duyệt:</strong> Chọn bước trong quy trình (1, 2, 3...)</li>
                        <li class="mb-2"><strong>Tên bước:</strong> Mô tả vai trò (VD: Giám đốc đơn vị)</li>
                        <li class="mb-2"><strong>Chọn nhân viên:</strong> Gõ tên để tìm, click để chọn</li>
                        <li class="mb-2">Mỗi nhân viên sẽ được gán <strong>đơn vị của họ</strong> tự động</li>
                        <li>Có thể chọn nhiều người cho cùng 1 bước</li>
                    </ul>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h6 class="card-title mb-0">Workflow: <?php echo CHtml::encode($workflow->name); ?></h6>
                </div>
                <div class="card-body">
                    <p class="mb-1"><strong>Mã:</strong> <?php echo CHtml::encode($workflow->code); ?></p>
                    <p class="mb-0"><strong>Số bước:</strong> <?php echo $workflow->total_steps; ?></p>
                </div>
            </div>

            <div class="card mt-3 border-info">
                <div class="card-body">
                    <h6 class="card-title text-info"><i class="fa fa-lightbulb-o"></i> Ví dụ</h6>
                    <p class="small mb-2"><strong>Bước 1 - GĐ đơn vị:</strong></p>
                    <ul class="small text-muted mb-0">
                        <li>Nguyễn Văn A - KS Hạ Long</li>
                        <li>Trần Văn B - KS Đà Nẵng</li>
                        <li>Lê Văn C - KS Nha Trang</li>
                    </ul>
                    <p class="small text-muted mt-2 mb-0">→ Mỗi người chỉ duyệt đơn của đơn vị mình</p>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    $('#staff-select').select2({
        placeholder: 'Gõ tên để tìm kiếm...',
        allowClear: true,
        width: '100%'
    });

    document.getElementById('add-approver-form').addEventListener('submit', function(e) {
        var selected = $('#staff-select').val();
        if (!selected || selected.length === 0) {
            e.preventDefault();
            alert('Vui lòng chọn ít nhất 1 nhân viên');
            return false;
        }

        var btn = document.getElementById('btn-submit');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Đang xử lý...';
    });
});
</script>
