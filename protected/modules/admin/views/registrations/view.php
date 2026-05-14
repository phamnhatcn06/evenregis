<?php
$this->menu = array(
    array(
        'label' => Yii::t('app', 'Manage'),
        'labelIcon' => Yii::t('app', 'Manage'),
        'url' => $this->createUrl('admin'),
        'color' => 'primary',
        'icon' => 'fa-th',
        'id' => 'btn_manage',
    ),
    array(
        'label' => Yii::t('app', 'Create'),
        'labelIcon' => Yii::t('app', 'Create'),
        'url' => $this->createUrl('create'),
        'color' => 'success',
        'icon' => 'fa-plus',
        'id' => 'btn_create',
    ),
    array(
        'label' => Yii::t('app', 'Update'),
        'labelIcon' => Yii::t('app', 'Update'),
        'url' => $this->createUrl('update', array('id' => $model->id)),
        'color' => 'warning',
        'icon' => 'fa-pencil',
        'id' => 'btn_update',
    ),
    array(
        'label' => Yii::t('app', 'Delete'),
        'labelIcon' => Yii::t('app', 'Delete'),
        'url' => $this->createUrl('delete', array('id' => $model->id)),
        'color' => 'danger',
        'icon' => 'fa-trash',
        'id' => 'btn_delete'
    ),
);

$this->breadcrumbs = array(
    Registrations::label(2) => array('admin'),
    Yii::t('app', 'View'),
);

$this->Tabletitle = 'Chi tiết phiếu đăng ký của ' . $model->property_name;
?>

<?php
$attributes = array(
    array('label' => 'ID', 'value' => $model->id),
    array('label' => 'Sự kiện', 'value' => isset($model->event_name) ? $model->event_name : ''),
    array('label' => 'Đơn vị', 'value' => isset($model->property_name) ? $model->property_name : ''),
    array('label' => 'Đợt đăng ký', 'value' => isset($model->period_name) ? $model->period_name : ''),
    array('label' => 'Trạng thái', 'value' => Registrations::getStatusLabel($model->status), 'raw' => true),
    array('label' => 'Ngày nộp', 'value' => $model->submitted_at ? MyHelper::formatDateTime($model->submitted_at) : '-'),
    array('label' => 'Ngày duyệt', 'value' => $model->reviewed_at ? MyHelper::formatDateTime($model->reviewed_at) : '-'),
    array('label' => 'Lý do từ chối', 'value' => $model->rejection_reason ?: '-'),
    array('label' => 'Ghi chú', 'value' => $model->note ?: '-'),
    array('label' => 'Ngày tạo', 'value' => MyHelper::formatDateTime($model->created_at)),
);

// Parse documents
$documents = array();
if (!empty($model->document)) {
    $parsed = json_decode($model->document, true);
    if (is_array($parsed)) {
        $documents = $parsed;
    } elseif (is_string($model->document)) {
        $documents = array($model->document);
    }
}

$totalAttrs = count($attributes);
if ($totalAttrs <= 4) {
    $colClass = 'col-12';
    $columns = 1;
} elseif ($totalAttrs <= 8) {
    $colClass = 'col-md-6';
    $columns = 2;
} else {
    $colClass = 'col-md-4';
    $columns = 3;
}
$perColumn = ceil($totalAttrs / $columns);
?>

<div class="card mb-3">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fa fa-info-circle me-2"></i>Thông tin phiếu đăng ký</h5>
        <div class="btn-group">
            <?php if ($model->status === 'draft'): ?>
                <form method="post" action="<?php echo $this->createUrl('submit', array('id' => $model->id)); ?>" style="display:inline;">
                    <button type="submit" class="btn btn-sm btn-info" onclick="return confirm('Bạn có chắc muốn nộp phiếu đăng ký này?')">
                        <i class="fa fa-paper-plane me-1"></i>Nộp đăng ký
                    </button>
                </form>
            <?php endif; ?>
            <?php if ($model->status === 'submitted'): ?>
                <form method="post" action="<?php echo $this->createUrl('approve', array('id' => $model->id)); ?>" style="display:inline;">
                    <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Bạn có chắc muốn phê duyệt phiếu đăng ký này?')">
                        <i class="fa fa-check me-1"></i>Phê duyệt
                    </button>
                </form>
                <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal">
                    <i class="fa fa-times me-1"></i>Từ chối
                </button>
            <?php endif; ?>
        </div>
    </div>
    <div class="card-body">
        <div class="row">
            <?php for ($col = 0; $col < $columns; $col++): ?>
                <div class="<?php echo $colClass; ?>">
                    <table class="table table-bordered table-striped mb-0">
                        <tbody>
                            <?php
                            $start = $col * $perColumn;
                            $end = min($start + $perColumn, $totalAttrs);
                            for ($i = $start; $i < $end; $i++):
                                $attr = $attributes[$i];
                            ?>
                                <tr>
                                    <th style="width:40%;background:#f8f9fa;"><?php echo CHtml::encode($attr['label']); ?></th>
                                    <td><?php echo isset($attr['raw']) && $attr['raw'] ? $attr['value'] : CHtml::encode($attr['value']); ?></td>
                                </tr>
                            <?php endfor; ?>
                        </tbody>
                    </table>
                </div>
            <?php endfor; ?>
        </div>
    </div>
</div>

<?php if ($model->relation_property_id): ?>
    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="fa fa-handshake-o me-2"></i>Thông tin liên quân</h5>
            <?php if (isset($allianceRequest) && $allianceRequest && $allianceRequest->status == AllianceRequests::STATUS_PENDING): ?>
                <span class="badge bg-warning">Đang chờ xác nhận</span>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <table class="table table-bordered table-striped mb-0">
                        <tbody>
                            <tr>
                                <th style="width:40%;background:#f8f9fa;">Đơn vị liên quân</th>
                                <td><?php echo CHtml::encode($model->relation_property_name ?: '-'); ?></td>
                            </tr>
                            <tr>
                                <th style="background:#f8f9fa;">Trạng thái</th>
                                <td>
                                    <?php if (isset($allianceRequest) && $allianceRequest): ?>
                                        <?php echo AllianceRequests::getStatusLabel($allianceRequest->status); ?>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Chưa có yêu cầu</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th style="background:#f8f9fa;">Ngày yêu cầu</th>
                                <td>
                                    <?php echo (isset($allianceRequest) && $allianceRequest && $allianceRequest->requested_at)
                                        ? MyHelper::formatDateTime($allianceRequest->requested_at) : '-'; ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="col-md-6">
                    <table class="table table-bordered table-striped mb-0">
                        <tbody>
                            <tr>
                                <th style="width:40%;background:#f8f9fa;">Người duyệt</th>
                                <td>
                                    <?php echo (isset($allianceRequest) && $allianceRequest && !empty($allianceRequest->reviewed_by_name))
                                        ? CHtml::encode($allianceRequest->reviewed_by_name) : '-'; ?>
                                </td>
                            </tr>
                            <tr>
                                <th style="background:#f8f9fa;">Ngày duyệt</th>
                                <td>
                                    <?php echo (isset($allianceRequest) && $allianceRequest && $allianceRequest->reviewed_at)
                                        ? MyHelper::formatDateTime($allianceRequest->reviewed_at) : '-'; ?>
                                </td>
                            </tr>
                            <?php if (isset($allianceRequest) && $allianceRequest && $allianceRequest->status == AllianceRequests::STATUS_REJECTED): ?>
                                <tr>
                                    <th style="background:#f8f9fa;">Lý do từ chối</th>
                                    <td class="text-danger">
                                        <?php echo CHtml::encode($allianceRequest->rejection_reason ?: '-'); ?>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <tr>
                                    <th style="background:#f8f9fa;">Ghi chú</th>
                                    <td>
                                        <?php echo (isset($allianceRequest) && $allianceRequest && !empty($allianceRequest->note))
                                            ? CHtml::encode($allianceRequest->note) : '-'; ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="card mb-3">
    <div class="card-header">
        <h5 class="mb-0"><i class="fa fa-file-text me-2"></i>Tài liệu đính kèm</h5>
    </div>
    <div class="card-body">
        <?php if (!empty($documents)): ?>
            <div class="row g-3">
                <?php foreach ($documents as $index => $docUrl):
                    $filename = basename($docUrl);
                    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                    $isImage = in_array($ext, array('jpg', 'jpeg', 'png', 'gif', 'webp'));
                    $isPdf = ($ext === 'pdf');
                ?>
                    <div class="col-6 col-md-3">
                        <div class="card h-100">
                            <?php if ($isImage): ?>
                                <img src="<?php echo CHtml::encode($docUrl); ?>" class="card-img-top" style="height:120px;object-fit:cover;cursor:pointer;"
                                    onclick="viewDocument('<?php echo CHtml::encode($docUrl); ?>', 'image')" title="Click để xem">
                            <?php else: ?>
                                <div class="card-img-top d-flex align-items-center justify-content-center bg-light" style="height:120px;">
                                    <?php if ($isPdf): ?>
                                        <i class="fa fa-file-pdf-o fa-3x text-danger"></i>
                                    <?php elseif (in_array($ext, array('doc', 'docx'))): ?>
                                        <i class="fa fa-file-word-o fa-3x text-primary"></i>
                                    <?php else: ?>
                                        <i class="fa fa-file-o fa-3x text-muted"></i>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            <div class="card-body p-2 text-center">
                                <small class="text-truncate d-block mb-2" title="<?php echo CHtml::encode($filename); ?>">
                                    <?php echo CHtml::encode($filename); ?>
                                </small>
                                <?php if ($isImage || $isPdf): ?>
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                        onclick="viewDocument('<?php echo CHtml::encode($docUrl); ?>', '<?php echo $isImage ? 'image' : 'pdf'; ?>')">
                                        <i class="fa fa-eye me-1"></i>Xem
                                    </button>
                                <?php endif; ?>
                                <a href="<?php echo CHtml::encode($docUrl); ?>" class="btn btn-sm btn-outline-secondary" download>
                                    <i class="fa fa-download me-1"></i>Tải
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-muted mb-0">Không có tài liệu đính kèm.</p>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0"><i class="fa fa-list me-2"></i>Chi tiết đăng ký</h5>
    </div>
    <div class="card-body">
        <?php if (!empty($registrationDetails)): ?>
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Nội dung</th>
                        <th>Môn thể thao</th>
                        <th>Số lượng</th>
                        <th>Ghi chú</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($registrationDetails as $detail): ?>
                        <tr>
                            <td><?php echo CHtml::encode(isset($detail['content_name']) ? $detail['content_name'] : ''); ?></td>
                            <td><?php echo CHtml::encode(isset($detail['sport_name']) ? $detail['sport_name'] : '-'); ?></td>
                            <td><?php echo CHtml::encode(isset($detail['quantity']) ? $detail['quantity'] : 1); ?></td>
                            <td><?php echo CHtml::encode(isset($detail['note']) ? $detail['note'] : ''); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="text-muted">Chưa có chi tiết đăng ký.</p>
        <?php endif; ?>
    </div>
</div>

<!-- Modal View Document -->
<div class="modal fade" id="documentModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Xem tài liệu</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0" id="documentModalBody" style="min-height:500px;">
            </div>
            <div class="modal-footer">
                <a href="#" id="documentDownloadLink" class="btn btn-primary" download>
                    <i class="fa fa-download me-1"></i>Tải xuống
                </a>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Reject -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="<?php echo $this->createUrl('reject', array('id' => $model->id)); ?>">
                <div class="modal-header">
                    <h5 class="modal-title">Từ chối phiếu đăng ký</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label">Lý do từ chối <span class="text-danger">*</span></label>
                        <textarea name="rejection_reason" class="form-control" rows="3" required placeholder="Nhập lý do từ chối..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    <button type="submit" class="btn btn-danger">Từ chối</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function viewDocument(url, type) {
        var modalBody = document.getElementById('documentModalBody');
        var downloadLink = document.getElementById('documentDownloadLink');

        downloadLink.href = url;

        if (type === 'image') {
            modalBody.innerHTML = '<div class="text-center p-3"><img src="' + url + '" class="img-fluid" style="max-height:80vh;"></div>';
        } else if (type === 'pdf') {
            modalBody.innerHTML = '<iframe src="' + url + '" style="width:100%;height:80vh;border:none;"></iframe>';
        }

        var modal = new bootstrap.Modal(document.getElementById('documentModal'));
        modal.show();
    }
</script>