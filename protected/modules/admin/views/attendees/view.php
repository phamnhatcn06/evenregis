<?php
/**
 * View Attendee Detail
 * @var Attendees $model
 */

$this->pageTitle = 'Chi tiết người tham dự';
$this->breadcrumbs = array(
    'Quản lý' => array('/admin/default/index'),
    'Người tham dự' => array('admin'),
    $model->full_name,
);
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">
                        <i class="fa fa-user"></i> <?php echo CHtml::encode($model->full_name); ?>
                    </h4>
                    <div>
                        <?php if (PermissionHelper::can('attendee', 'update')): ?>
                            <a href="<?php echo $this->createUrl('update', array('id' => $model->id)); ?>" class="btn btn-primary btn-sm">
                                <i class="fa fa-edit"></i> Sửa
                            </a>
                        <?php endif; ?>
                        <a href="<?php echo $this->createUrl('admin'); ?>" class="btn btn-secondary btn-sm">
                            <i class="fa fa-arrow-left"></i> Quay lại
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Cột trái: Thông tin -->
                        <div class="col-md-8">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-bordered">
                                        <tbody>
                                            <tr>
                                                <th style="width:40%;background:#f8f9fa;">Họ tên</th>
                                                <td><?php echo CHtml::encode($model->full_name); ?></td>
                                            </tr>
                                            <tr>
                                                <th style="background:#f8f9fa;">Chức vụ</th>
                                                <td><?php echo CHtml::encode($model->position); ?></td>
                                            </tr>
                                            <tr>
                                                <th style="background:#f8f9fa;">Đơn vị hiển thị</th>
                                                <td><?php echo CHtml::encode($model->unit_label); ?></td>
                                            </tr>
                                            <tr>
                                                <th style="background:#f8f9fa;">Mã số thẻ</th>
                                                <td><?php echo CHtml::encode($model->badge_number); ?></td>
                                            </tr>
                                            <tr>
                                                <th style="background:#f8f9fa;">Trưởng đoàn</th>
                                                <td>
                                                    <?php echo $model->is_team_lead
                                                        ? '<span class="badge bg-success">Có</span>'
                                                        : '<span class="badge bg-secondary">Không</span>'; ?>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-bordered">
                                        <tbody>
                                            <tr>
                                                <th style="width:40%;background:#f8f9fa;">Sự kiện</th>
                                                <td><?php echo CHtml::encode($model->event_name); ?></td>
                                            </tr>
                                            <tr>
                                                <th style="background:#f8f9fa;">Đơn vị</th>
                                                <td><?php echo CHtml::encode($model->property_name); ?></td>
                                            </tr>
                                            <tr>
                                                <th style="background:#f8f9fa;">Ngày check-in</th>
                                                <td><?php echo $model->check_in_date ? date('d/m/Y', strtotime($model->check_in_date)) : '-'; ?></td>
                                            </tr>
                                            <tr>
                                                <th style="background:#f8f9fa;">Ngày check-out</th>
                                                <td><?php echo $model->check_out_date ? date('d/m/Y', strtotime($model->check_out_date)) : '-'; ?></td>
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

                            <!-- Ghi chú -->
                            <?php if ($model->note): ?>
                            <div class="mt-3">
                                <h6><i class="fa fa-sticky-note-o"></i> Ghi chú</h6>
                                <div class="p-3 bg-light rounded">
                                    <?php echo nl2br(CHtml::encode($model->note)); ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>

                        <!-- Cột phải: Ảnh -->
                        <div class="col-md-4">
                            <div class="text-center">
                                <?php if ($model->portrait_path): ?>
                                    <img src="<?php echo $model->portrait_path; ?>" class="img-thumbnail mb-3" style="max-width:200px;">
                                    <p class="text-muted small">Ảnh chân dung 530×530px</p>
                                <?php elseif ($model->photo_path): ?>
                                    <img src="<?php echo $model->photo_path; ?>" class="img-thumbnail mb-3" style="max-width:200px;">
                                <?php else: ?>
                                    <div class="bg-secondary text-white d-flex align-items-center justify-content-center mb-3" style="width:200px;height:200px;margin:0 auto;">
                                        <i class="fa fa-user fa-5x"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- Giấy tờ đính kèm -->
                    <h5><i class="fa fa-file-image-o"></i> Giấy tờ đính kèm</h5>
                    <div class="row">
                        <div class="col-md-3">
                            <div class="card">
                                <div class="card-header py-2 bg-light">
                                    <strong>CCCD mặt trước</strong>
                                </div>
                                <div class="card-body text-center p-2">
                                    <?php if ($model->cccd_front_path): ?>
                                        <a href="<?php echo $model->cccd_front_path; ?>" target="_blank">
                                            <img src="<?php echo $model->cccd_front_path; ?>" class="img-fluid" style="max-height:120px;">
                                        </a>
                                    <?php else: ?>
                                        <div class="text-muted py-4">
                                            <i class="fa fa-times-circle fa-2x"></i>
                                            <p class="mb-0 small">Chưa upload</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card">
                                <div class="card-header py-2 bg-light">
                                    <strong>CCCD mặt sau</strong>
                                </div>
                                <div class="card-body text-center p-2">
                                    <?php if ($model->cccd_back_path): ?>
                                        <a href="<?php echo $model->cccd_back_path; ?>" target="_blank">
                                            <img src="<?php echo $model->cccd_back_path; ?>" class="img-fluid" style="max-height:120px;">
                                        </a>
                                    <?php else: ?>
                                        <div class="text-muted py-4">
                                            <i class="fa fa-times-circle fa-2x"></i>
                                            <p class="mb-0 small">Chưa upload</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card">
                                <div class="card-header py-2 bg-light">
                                    <strong>Ảnh chân dung</strong>
                                </div>
                                <div class="card-body text-center p-2">
                                    <?php if ($model->portrait_path): ?>
                                        <a href="<?php echo $model->portrait_path; ?>" target="_blank">
                                            <img src="<?php echo $model->portrait_path; ?>" class="img-fluid" style="max-height:120px;">
                                        </a>
                                    <?php else: ?>
                                        <div class="text-muted py-4">
                                            <i class="fa fa-times-circle fa-2x"></i>
                                            <p class="mb-0 small">Chưa upload</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="card">
                                <div class="card-header py-2 bg-light">
                                    <strong>Hợp đồng lao động</strong>
                                </div>
                                <div class="card-body text-center p-2">
                                    <?php if ($model->contract_path): ?>
                                        <?php if (pathinfo($model->contract_path, PATHINFO_EXTENSION) === 'pdf'): ?>
                                            <a href="<?php echo $model->contract_path; ?>" target="_blank" class="btn btn-outline-danger">
                                                <i class="fa fa-file-pdf-o fa-2x"></i>
                                                <p class="mb-0 small">Xem PDF</p>
                                            </a>
                                        <?php else: ?>
                                            <a href="<?php echo $model->contract_path; ?>" target="_blank">
                                                <img src="<?php echo $model->contract_path; ?>" class="img-fluid" style="max-height:120px;">
                                            </a>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <div class="text-muted py-4">
                                            <i class="fa fa-times-circle fa-2x"></i>
                                            <p class="mb-0 small">Chưa upload</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
