<?php
$this->breadcrumbs = array(
    'Duyệt đăng ký' => array('index'),
    'Chi tiết #' . $model->id,
);

$this->menu = array(
    array(
        'label' => 'Quay lại',
        'url' => $this->createUrl('index'),
        'color' => 'secondary',
        'icon' => 'fa-arrow-left',
    ),
);
$this->Tabletitle = 'Duyệt đơn đăng ký #' . $model->registration_id;
?>

<div class="row">
    <!-- Thông tin đơn đăng ký -->
    <div class="col-md-7">
        <div class="card mb-3">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Thông tin đơn đăng ký</h5>
                <?php echo RegistrationApprovals::getStatusLabel($model->status); ?>
            </div>
            <div class="card-body p-0">
                <table class="table table-bordered mb-0">
                    <tbody>
                        <tr>
                            <th style="width:35%;background:#f8f9fa;">Mã đăng ký</th>
                            <td>#<?php echo $model->registration_id; ?></td>
                        </tr>
                        <?php if ($registration): ?>
                        <tr>
                            <th style="background:#f8f9fa;">Đơn vị</th>
                            <td><?php echo CHtml::encode($registration->organization_name ?? $registration->property_name ?? '-'); ?></td>
                        </tr>
                        <tr>
                            <th style="background:#f8f9fa;">Sự kiện</th>
                            <td><?php echo CHtml::encode($registration->event_name ?? '-'); ?></td>
                        </tr>
                        <tr>
                            <th style="background:#f8f9fa;">Đợt đăng ký</th>
                            <td><?php echo CHtml::encode($registration->period_name ?? '-'); ?></td>
                        </tr>
                        <?php endif; ?>
                        <tr>
                            <th style="background:#f8f9fa;">Tiến độ duyệt</th>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height: 25px;">
                                        <?php $percent = ($model->current_index / $model->total_steps) * 100; ?>
                                        <div class="progress-bar bg-info" style="width: <?php echo $percent; ?>%;">
                                            <?php echo $model->getProgressText(); ?>
                                        </div>
                                    </div>
                                    <span class="badge bg-primary">Bước <?php echo $model->current_index; ?>/<?php echo $model->total_steps; ?></span>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th style="background:#f8f9fa;">Bước hiện tại</th>
                            <td><strong><?php echo CHtml::encode($model->getCurrentStepName()); ?></strong></td>
                        </tr>
                        <tr>
                            <th style="background:#f8f9fa;">Thời gian nộp</th>
                            <td><?php echo $model->started_at ? date('d/m/Y H:i', $model->started_at) : '-'; ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Lịch sử duyệt -->
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0"><i class="fa fa-history"></i> Lịch sử xử lý</h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($logs)): ?>
                    <div class="p-3 text-muted">Chưa có lịch sử</div>
                <?php else: ?>
                    <div class="timeline p-3">
                        <?php foreach ($logs as $log): ?>
                            <div class="timeline-item mb-3 pb-3 border-bottom">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <?php echo RegistrationApprovalLogs::getActionLabel($log->action); ?>
                                        <?php if ($log->step_index > 0): ?>
                                            <span class="text-muted">- Bước <?php echo $log->step_index; ?>: <?php echo CHtml::encode($log->step_name); ?></span>
                                        <?php endif; ?>
                                        <?php if ($log->return_to_index !== null && $log->action == 'revision'): ?>
                                            <span class="badge bg-warning">Trả về bước <?php echo $log->return_to_index; ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <small class="text-muted"><?php echo $log->getActedAtFormatted(); ?></small>
                                </div>
                                <?php if ($log->approver_name): ?>
                                    <div class="small text-muted">
                                        <i class="fa fa-user"></i> <?php echo CHtml::encode($log->approver_name); ?>
                                    </div>
                                <?php endif; ?>
                                <?php if ($log->comment): ?>
                                    <div class="mt-2 p-2 bg-light rounded">
                                        <small><i class="fa fa-comment"></i> <?php echo nl2br(CHtml::encode($log->comment)); ?></small>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div class="col-md-5">
        <?php if ($model->status == RegistrationApprovals::STATUS_PENDING): ?>
            <!-- Duyệt -->
            <div class="card mb-3 border-success">
                <div class="card-header bg-success text-white">
                    <h6 class="mb-0"><i class="fa fa-check"></i> Duyệt đơn</h6>
                </div>
                <div class="card-body">
                    <form method="post" action="<?php echo $this->createUrl('approve', array('id' => $model->id)); ?>">
                        <input type="hidden" name="YII_CSRF_TOKEN" value="<?php echo Yii::app()->request->csrfToken; ?>">
                        <div class="mb-3">
                            <label class="form-label">Ghi chú (không bắt buộc)</label>
                            <textarea name="comment" class="form-control" rows="2" placeholder="Nhập ghi chú nếu cần..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-success w-100">
                            <i class="fa fa-check"></i> Duyệt
                            <?php if ($model->current_index < $model->total_steps): ?>
                                (Chuyển bước <?php echo $model->current_index + 1; ?>)
                            <?php else: ?>
                                (Hoàn tất)
                            <?php endif; ?>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Từ chối -->
            <div class="card mb-3 border-danger">
                <div class="card-header bg-danger text-white">
                    <h6 class="mb-0"><i class="fa fa-times"></i> Từ chối</h6>
                </div>
                <div class="card-body">
                    <form method="post" action="<?php echo $this->createUrl('reject', array('id' => $model->id)); ?>">
                        <input type="hidden" name="YII_CSRF_TOKEN" value="<?php echo Yii::app()->request->csrfToken; ?>">
                        <div class="mb-3">
                            <label class="form-label">Lý do từ chối <span class="text-danger">*</span></label>
                            <textarea name="comment" class="form-control" rows="2" required placeholder="Nhập lý do từ chối..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-danger w-100">
                            <i class="fa fa-times"></i> Từ chối đơn
                        </button>
                    </form>
                </div>
            </div>

            <!-- Yêu cầu chỉnh sửa -->
            <div class="card border-warning">
                <div class="card-header bg-warning text-dark">
                    <h6 class="mb-0"><i class="fa fa-undo"></i> Yêu cầu chỉnh sửa</h6>
                </div>
                <div class="card-body">
                    <form method="post" action="<?php echo $this->createUrl('revision', array('id' => $model->id)); ?>">
                        <input type="hidden" name="YII_CSRF_TOKEN" value="<?php echo Yii::app()->request->csrfToken; ?>">
                        <div class="mb-3">
                            <label class="form-label">Trả về <span class="text-danger">*</span></label>
                            <select name="return_to_index" class="form-select" required>
                                <?php
                                $returnableSteps = $model->getReturnableSteps();
                                foreach ($returnableSteps as $index => $stepName):
                                ?>
                                    <option value="<?php echo $index; ?>"><?php echo CHtml::encode($stepName); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Lý do yêu cầu chỉnh sửa <span class="text-danger">*</span></label>
                            <textarea name="comment" class="form-control" rows="2" required placeholder="Nhập lý do..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-warning w-100">
                            <i class="fa fa-undo"></i> Trả về chỉnh sửa
                        </button>
                    </form>
                </div>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-body text-center">
                    <i class="fa fa-info-circle fa-3x text-info mb-3"></i>
                    <h5>Đơn này đã được xử lý</h5>
                    <p class="text-muted">Trạng thái: <?php echo RegistrationApprovals::getStatusLabel($model->status); ?></p>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
