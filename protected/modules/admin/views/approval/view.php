<?php
$this->breadcrumbs = array(
    'Phê duyệt người tham dự' => array('index'),
    'Chi tiết',
);

$this->Tabletitle = 'Chi tiết người tham dự: ' . $model->full_name;

$approvalStatus = isset($model->approval_status) ? (int)$model->approval_status : Attendees::APPROVAL_PENDING;
?>

<div class="row">
    <div class="col-md-4">
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0"><i class="fa fa-image me-2"></i>Ảnh chân dung</h5>
            </div>
            <div class="card-body text-center">
                <?php if (!empty($model->portrait_path)): ?>
                    <img src="<?php echo CHtml::encode($model->portrait_path); ?>" class="img-fluid rounded" style="max-height:300px;">
                <?php else: ?>
                    <div class="bg-light rounded d-flex align-items-center justify-content-center mx-auto" style="width:200px;height:200px;">
                        <i class="fa fa-user fa-5x text-muted"></i>
                    </div>
                    <p class="text-muted mt-2">Chưa có ảnh</p>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($approvalStatus == Attendees::APPROVAL_PENDING): ?>
            <div class="card mb-3">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fa fa-gavel me-2"></i>Phê duyệt</h5>
                </div>
                <div class="card-body">
                    <form method="post" action="<?php echo $this->createUrl('approve', array('id' => $model->id)); ?>" class="mb-2">
                        <button type="submit" class="btn btn-success w-100" onclick="return confirm('Bạn có chắc muốn phê duyệt người này?')">
                            <i class="fa fa-check me-1"></i>Phê duyệt
                        </button>
                    </form>
                    <button type="button" class="btn btn-danger w-100" onclick="showRejectModal()">
                        <i class="fa fa-times me-1"></i>Từ chối
                    </button>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="col-md-8">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fa fa-info-circle me-2"></i>Thông tin cá nhân</h5>
                <?php echo Attendees::getApprovalStatusLabel($approvalStatus); ?>
            </div>
            <div class="card-body">
                <table class="table table-bordered mb-0">
                    <tbody>
                        <tr>
                            <th style="width:30%;background:#f8f9fa;">Họ và tên</th>
                            <td><?php echo CHtml::encode($model->full_name); ?></td>
                        </tr>
                        <tr>
                            <th style="background:#f8f9fa;">Mã nhân viên</th>
                            <td><?php echo CHtml::encode($model->staff_code ?: '-'); ?></td>
                        </tr>
                        <tr>
                            <th style="background:#f8f9fa;">Chức danh</th>
                            <td><?php echo CHtml::encode($model->position ?: '-'); ?></td>
                        </tr>
                        <tr>
                            <th style="background:#f8f9fa;">Đơn vị</th>
                            <td><?php echo CHtml::encode($model->property_name ?: '-'); ?></td>
                        </tr>
                        <tr>
                            <th style="background:#f8f9fa;">Vai trò tham dự</th>
                            <td><?php echo CHtml::encode($model->role_name ?: '-'); ?></td>
                        </tr>
                        <tr>
                            <th style="background:#f8f9fa;">Trưởng đoàn</th>
                            <td><?php echo $model->is_team_lead ? '<span class="badge bg-primary">Có</span>' : 'Không'; ?></td>
                        </tr>
                        <tr>
                            <th style="background:#f8f9fa;">Ghi chú</th>
                            <td><?php echo CHtml::encode($model->note ?: '-'); ?></td>
                        </tr>
                        <?php if ($approvalStatus == Attendees::APPROVAL_REJECTED && !empty($model->rejection_reason)): ?>
                            <tr>
                                <th style="background:#f8f9fa;">Lý do từ chối</th>
                                <td class="text-danger"><?php echo CHtml::encode($model->rejection_reason); ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0"><i class="fa fa-id-card me-2"></i>Giấy tờ tùy thân</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">CCCD mặt trước</label>
                        <?php if (!empty($model->cccd_front_path)): ?>
                            <div>
                                <img src="<?php echo CHtml::encode($model->cccd_front_path); ?>" class="img-fluid rounded border" style="max-height:200px;cursor:pointer;" onclick="viewImage('<?php echo CHtml::encode($model->cccd_front_path); ?>')">
                            </div>
                        <?php else: ?>
                            <p class="text-muted">Chưa có</p>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">CCCD mặt sau</label>
                        <?php if (!empty($model->cccd_back_path)): ?>
                            <div>
                                <img src="<?php echo CHtml::encode($model->cccd_back_path); ?>" class="img-fluid rounded border" style="max-height:200px;cursor:pointer;" onclick="viewImage('<?php echo CHtml::encode($model->cccd_back_path); ?>')">
                            </div>
                        <?php else: ?>
                            <p class="text-muted">Chưa có</p>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Hợp đồng lao động</label>
                        <?php if (!empty($model->contract_path)):
                            $ext = strtolower(pathinfo($model->contract_path, PATHINFO_EXTENSION));
                            $isPdf = ($ext === 'pdf');
                        ?>
                            <?php if ($isPdf): ?>
                                <div>
                                    <a href="<?php echo CHtml::encode($model->contract_path); ?>" target="_blank" class="btn btn-outline-primary">
                                        <i class="fa fa-file-pdf-o me-1"></i>Xem PDF
                                    </a>
                                </div>
                            <?php else: ?>
                                <div>
                                    <img src="<?php echo CHtml::encode($model->contract_path); ?>" class="img-fluid rounded border" style="max-height:200px;cursor:pointer;" onclick="viewImage('<?php echo CHtml::encode($model->contract_path); ?>')">
                                </div>
                            <?php endif; ?>
                        <?php else: ?>
                            <p class="text-muted">Chưa có</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="mt-3">
    <a href="<?php echo $this->createUrl('index'); ?>" class="btn btn-secondary">
        <i class="fa fa-arrow-left me-1"></i>Quay lại danh sách
    </a>
</div>

<!-- Modal Reject -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="<?php echo $this->createUrl('reject', array('id' => $model->id)); ?>" id="reject-form">
                <div class="modal-header">
                    <h5 class="modal-title">Từ chối người tham dự</h5>
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

<!-- Modal View Image -->
<div class="modal fade" id="imageModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Xem ảnh</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center p-0">
                <img id="imageModalImg" src="" class="img-fluid" style="max-height:80vh;">
            </div>
        </div>
    </div>
</div>

<script>
function showRejectModal() {
    var modal = new bootstrap.Modal(document.getElementById('rejectModal'));
    modal.show();
}

function viewImage(url) {
    document.getElementById('imageModalImg').src = url;
    var modal = new bootstrap.Modal(document.getElementById('imageModal'));
    modal.show();
}
</script>
