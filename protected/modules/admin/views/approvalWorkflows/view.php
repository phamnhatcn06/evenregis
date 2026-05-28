<?php
$this->breadcrumbs = array(
    'Quy trình duyệt' => array('admin'),
    $model->name,
);

$this->menu = array(
    array(
        'label' => 'Danh sách',
        'url' => $this->createUrl('admin'),
        'color' => 'secondary',
        'icon' => 'fa-list',
    ),
    array(
        'label' => 'Cập nhật',
        'url' => $this->createUrl('update', array('id' => $model->id)),
        'color' => 'warning',
        'icon' => 'fa-edit',
    ),
    array(
        'label' => 'Thêm người duyệt',
        'url' => $this->createUrl('addApprover', array('id' => $model->id)),
        'color' => 'primary',
        'icon' => 'fa-user-plus',
    ),
);
$this->Tabletitle = 'Chi tiết quy trình: ' . CHtml::encode($model->name);
?>

<div class="row">
    <div class="col-md-5">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Thông tin quy trình</h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-bordered table-striped mb-0">
                    <tbody>
                        <tr>
                            <th style="width:40%;background:#f8f9fa;">ID</th>
                            <td><?php echo $model->id; ?></td>
                        </tr>
                        <tr>
                            <th style="background:#f8f9fa;">Mã</th>
                            <td><code><?php echo CHtml::encode($model->code); ?></code></td>
                        </tr>
                        <tr>
                            <th style="background:#f8f9fa;">Tên quy trình</th>
                            <td><?php echo CHtml::encode($model->name); ?></td>
                        </tr>
                        <tr>
                            <th style="background:#f8f9fa;">Mô tả</th>
                            <td><?php echo $model->description ? nl2br(CHtml::encode($model->description)) : '<span class="text-muted">-</span>'; ?></td>
                        </tr>
                        <tr>
                            <th style="background:#f8f9fa;">Số bước duyệt</th>
                            <td><span class="badge bg-info fs-6"><?php echo $model->total_steps; ?></span></td>
                        </tr>
                        <tr>
                            <th style="background:#f8f9fa;">Mặc định</th>
                            <td>
                                <?php echo $model->is_default
                                    ? '<span class="badge bg-success">Có</span>'
                                    : '<span class="badge bg-secondary">Không</span>'; ?>
                            </td>
                        </tr>
                        <tr>
                            <th style="background:#f8f9fa;">Trạng thái</th>
                            <td>
                                <?php echo $model->is_active
                                    ? '<span class="badge bg-success">Hoạt động</span>'
                                    : '<span class="badge bg-secondary">Không hoạt động</span>'; ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Danh sách người duyệt theo bước</h5>
                <a href="<?php echo $this->createUrl('addApprover', array('id' => $model->id)); ?>" class="btn btn-sm btn-primary">
                    <i class="fa fa-user-plus"></i> Thêm
                </a>
            </div>
            <div class="card-body p-0">
                <?php
                $approverData = $approvers->getData();
                if (empty($approverData)): ?>
                    <div class="alert alert-warning m-3">
                        <i class="fa fa-exclamation-triangle"></i> Chưa có người duyệt nào được gán.
                        <a href="<?php echo $this->createUrl('addApprover', array('id' => $model->id)); ?>">Thêm ngay</a>
                    </div>
                <?php else:
                    // Group by step_index
                    $groupedApprovers = array();
                    foreach ($approverData as $approver) {
                        $step = $approver->step_index;
                        if (!isset($groupedApprovers[$step])) {
                            $groupedApprovers[$step] = array(
                                'step_name' => $approver->step_name,
                                'approvers' => array(),
                            );
                        }
                        $groupedApprovers[$step]['approvers'][] = $approver;
                    }
                    ksort($groupedApprovers);
                ?>
                    <div class="accordion" id="approverAccordion">
                        <?php foreach ($groupedApprovers as $stepIndex => $stepData): ?>
                            <div class="accordion-item">
                                <h2 class="accordion-header">
                                    <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                            data-bs-target="#step<?php echo $stepIndex; ?>">
                                        <span class="badge bg-primary me-2"><?php echo $stepIndex; ?></span>
                                        <?php echo CHtml::encode($stepData['step_name']); ?>
                                        <span class="badge bg-secondary ms-2"><?php echo count($stepData['approvers']); ?> người</span>
                                    </button>
                                </h2>
                                <div id="step<?php echo $stepIndex; ?>" class="accordion-collapse collapse show"
                                     data-bs-parent="#approverAccordion">
                                    <div class="accordion-body p-0">
                                        <table class="table table-hover mb-0">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Tên người duyệt</th>
                                                    <th>Email</th>
                                                    <th>Đơn vị</th>
                                                    <th style="width:80px;">Thao tác</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($stepData['approvers'] as $approver): ?>
                                                    <tr>
                                                        <td>
                                                            <?php echo CHtml::encode($approver->portal_user_name); ?>
                                                            <small class="text-muted d-block">ID: <?php echo $approver->portal_user_id; ?></small>
                                                        </td>
                                                        <td><?php echo CHtml::encode($approver->portal_user_email); ?></td>
                                                        <td>
                                                            <?php if ($approver->organization_id): ?>
                                                                <span class="badge bg-info"><?php echo $approver->organization_id; ?></span>
                                                            <?php else: ?>
                                                                <span class="text-muted">Tất cả</span>
                                                            <?php endif; ?>
                                                        </td>
                                                        <td>
                                                            <?php echo CHtml::link(
                                                                '<i class="fa fa-trash text-danger"></i>',
                                                                '#',
                                                                array(
                                                                    'onclick' => "confirmDeleteApprover('" . $approver->id . "'); return false;",
                                                                    'title' => 'Xóa',
                                                                )
                                                            ); ?>
                                                            <form id="delete-approver-<?php echo $approver->id; ?>"
                                                                  action="<?php echo $this->createUrl('deleteApprover', array('id' => $model->id, 'approverId' => $approver->id)); ?>"
                                                                  method="post" style="display:none;">
                                                                <input type="hidden" name="YII_CSRF_TOKEN" value="<?php echo Yii::app()->request->csrfToken; ?>">
                                                            </form>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDeleteApprover(approverId) {
    Swal.fire({
        title: 'Xác nhận xóa',
        text: 'Bạn có chắc chắn muốn xóa người duyệt này?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Xóa',
        cancelButtonText: 'Hủy'
    }).then(function(result) {
        if (result.isConfirmed) {
            document.getElementById('delete-approver-' + approverId).submit();
        }
    });
}
</script>
