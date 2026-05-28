<?php
$this->breadcrumbs = array(
    'Phê duyệt người tham dự',
);

$this->Tabletitle = 'Phê duyệt người tham dự';

$eventOptions = array('' => '-- Tất cả sự kiện --');
foreach ($events as $e) {
    $eId = isset($e['id']) ? $e['id'] : (isset($e->id) ? $e->id : null);
    $eName = isset($e['name']) ? $e['name'] : (isset($e->name) ? $e->name : '');
    if ($eId) $eventOptions[$eId] = $eName;
}

$propertyOptions = array('' => '-- Tất cả đơn vị --');
foreach ($properties as $p) {
    $pId = isset($p['id']) ? $p['id'] : (isset($p->id) ? $p->id : null);
    $pName = isset($p['name']) ? $p['name'] : (isset($p->name) ? $p->name : '');
    $pCode = isset($p['code']) ? $p['code'] : (isset($p->code) ? $p->code : '');
    if ($pId) $propertyOptions[$pId] = $pCode . ' - ' . $pName;
}
?>

<div class="card mb-3">
    <div class="card-header">
        <h5 class="mb-0"><i class="fa fa-filter me-2"></i>Bộ lọc</h5>
    </div>
    <div class="card-body">
        <form method="get" action="<?php echo $this->createUrl('index'); ?>">
            <div class="row">
                <div class="col-md-3">
                    <label class="form-label">Sự kiện</label>
                    <select class="form-select" name="event_id">
                        <?php foreach ($eventOptions as $val => $label): ?>
                            <option value="<?php echo $val; ?>" <?php echo (isset($_GET['event_id']) && $_GET['event_id'] == $val) ? 'selected' : ''; ?>><?php echo CHtml::encode($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Đơn vị</label>
                    <select class="form-select" name="property_id">
                        <?php foreach ($propertyOptions as $val => $label): ?>
                            <option value="<?php echo $val; ?>" <?php echo (isset($_GET['property_id']) && $_GET['property_id'] == $val) ? 'selected' : ''; ?>><?php echo CHtml::encode($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Trạng thái</label>
                    <select class="form-select" name="approval_status">
                        <option value="">-- Tất cả --</option>
                        <?php foreach (Attendees::getApprovalStatusOptions() as $val => $label): ?>
                            <option value="<?php echo $val; ?>" <?php echo (isset($_GET['approval_status']) && $_GET['approval_status'] !== '' && $_GET['approval_status'] == $val) ? 'selected' : ''; ?>><?php echo CHtml::encode($label); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2"><i class="fa fa-search me-1"></i>Lọc</button>
                    <a href="<?php echo $this->createUrl('index'); ?>" class="btn btn-secondary"><i class="fa fa-refresh me-1"></i>Reset</a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fa fa-users me-2"></i>Danh sách người tham dự</h5>
        <form method="post" action="<?php echo $this->createUrl('bulkApprove'); ?>" id="bulk-approve-form">
            <button type="submit" class="btn btn-sm btn-success" id="btn-bulk-approve" disabled onclick="return confirm('Bạn có chắc muốn phê duyệt tất cả người đã chọn?')">
                <i class="fa fa-check-circle me-1"></i>Phê duyệt đã chọn (<span id="selected-count">0</span>)
            </button>
        </form>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover" id="approval-table">
                <thead class="table-light">
                    <tr>
                        <th style="width:40px;">
                            <input type="checkbox" id="select-all" class="form-check-input">
                        </th>
                        <th style="width:60px;">Ảnh</th>
                        <th>Họ tên</th>
                        <th>Đơn vị</th>
                        <th>Chức danh</th>
                        <th>Vai trò</th>
                        <th style="width:120px;">Trạng thái</th>
                        <th style="width:150px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $data = $dataProvider->getData();
                    if (empty($data)):
                    ?>
                        <tr>
                            <td colspan="8" class="text-center text-muted">Không có dữ liệu</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($data as $att):
                            $attId = isset($att['id']) ? $att['id'] : (isset($att->id) ? $att->id : '');
                            $fullName = isset($att['full_name']) ? $att['full_name'] : (isset($att->full_name) ? $att->full_name : '');
                            $propertyName = isset($att['property_name']) ? $att['property_name'] : '';
                            $position = isset($att['position']) ? $att['position'] : (isset($att->position) ? $att->position : '');
                            $roleName = Attendees::resolveRoleNames(isset($att['role_id']) ? $att['role_id'] : (isset($att->role_id) ? $att->role_id : ''));
                            $photoPath = isset($att['portrait_path']) ? $att['portrait_path'] : (isset($att['photo_path']) ? $att['photo_path'] : '');
                            $approvalStatus = isset($att['approval_status']) ? (int)$att['approval_status'] : Attendees::APPROVAL_PENDING;
                        ?>
                            <tr>
                                <td class="text-center">
                                    <?php if ($approvalStatus == Attendees::APPROVAL_PENDING): ?>
                                        <input type="checkbox" name="ids[]" value="<?php echo $attId; ?>" class="form-check-input row-checkbox" form="bulk-approve-form">
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($photoPath): ?>
                                        <img src="<?php echo CHtml::encode($photoPath); ?>" class="rounded" style="width:40px;height:40px;object-fit:cover;">
                                    <?php else: ?>
                                        <div class="bg-light rounded d-flex align-items-center justify-content-center" style="width:40px;height:40px;">
                                            <i class="fa fa-user text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo CHtml::encode($fullName); ?></td>
                                <td><?php echo CHtml::encode($propertyName); ?></td>
                                <td><?php echo CHtml::encode($position); ?></td>
                                <td>
                                    <?php if (!empty($roleName)): ?>
                                        <?php foreach (array_map('trim', explode(',', $roleName)) as $role): ?>
                                            <span class="badge <?php echo Attendees::getRoleBadgeClass($role); ?> me-1 mb-1"><?php echo CHtml::encode($role); ?></span>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td><?php echo Attendees::getApprovalStatusLabel($approvalStatus); ?></td>
                                <td class="text-center">
                                    <a href="<?php echo $this->createUrl('view', array('id' => $attId)); ?>" class="btn btn-sm btn-outline-info me-1" title="Xem chi tiết">
                                        <i class="fa fa-eye"></i>
                                    </a>
                                    <?php if ($approvalStatus == Attendees::APPROVAL_PENDING): ?>
                                        <button type="button" class="btn btn-sm btn-success me-1" onclick="approveAttendee(<?php echo $attId; ?>)" title="Phê duyệt">
                                            <i class="fa fa-check"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="rejectAttendee(<?php echo $attId; ?>)" title="Từ chối">
                                            <i class="fa fa-times"></i>
                                        </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php
        $pagination = $dataProvider->getPagination();
        if ($pagination && $pagination->getPageCount() > 1):
        ?>
            <nav class="mt-3">
                <?php $this->widget('CLinkPager', array(
                    'pages' => $pagination,
                    'htmlOptions' => array('class' => 'pagination justify-content-center'),
                    'header' => '',
                    'firstPageLabel' => '&laquo;',
                    'lastPageLabel' => '&raquo;',
                    'prevPageLabel' => '&lsaquo;',
                    'nextPageLabel' => '&rsaquo;',
                    'selectedPageCssClass' => 'active',
                )); ?>
            </nav>
        <?php endif; ?>
    </div>
</div>

<?php
Yii::app()->clientScript->registerScriptFile(
    Yii::app()->theme->baseUrl . '/assets/js/pages/approval-index.js',
    CClientScript::POS_END
);

Yii::app()->clientScript->registerScript('approval-init', '
    window.BASE_URL = "' . Yii::app()->createUrl('/') . '";
', CClientScript::POS_END);
?>
