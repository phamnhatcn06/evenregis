<?php
$this->breadcrumbs = array(
    'Thí sinh Miss' => array('admin'),
    $model->attendee_name,
);

$this->menu = array(
    array(
        'label' => 'Cập nhật',
        'url' => $this->createUrl('update', array('id' => $model->id)),
        'color' => 'primary',
        'icon' => 'fa-edit',
    ),
    array(
        'label' => 'Danh sách',
        'url' => $this->createUrl('admin'),
        'color' => 'secondary',
        'icon' => 'fa-list',
    ),
);
$this->Tabletitle = 'Chi tiết thí sinh: ' . CHtml::encode($model->attendee_name);
?>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Thông tin thí sinh</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-bordered table-striped">
                            <tr>
                                <th style="width:40%;background:#f8f9fa;">Số báo danh</th>
                                <td><strong><?php echo CHtml::encode($model->contestant_number); ?></strong></td>
                            </tr>
                            <tr>
                                <th style="background:#f8f9fa;">Họ và tên</th>
                                <td><?php echo CHtml::encode($model->attendee_name); ?></td>
                            </tr>
                            <tr>
                                <th style="background:#f8f9fa;">Cuộc thi</th>
                                <td><?php echo CHtml::encode($model->contest_name); ?></td>
                            </tr>
                            <tr>
                                <th style="background:#f8f9fa;">Đơn vị</th>
                                <td><?php echo CHtml::encode($model->property_name); ?></td>
                            </tr>
                            <tr>
                                <th style="background:#f8f9fa;">Trạng thái</th>
                                <td><?php echo BeautyContestants::getStatusLabel($model->status); ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-bordered table-striped">
                            <tr>
                                <th style="width:40%;background:#f8f9fa;">Chiều cao</th>
                                <td><?php echo $model->height_cm ? $model->height_cm . ' cm' : '-'; ?></td>
                            </tr>
                            <tr>
                                <th style="background:#f8f9fa;">Cân nặng</th>
                                <td><?php echo $model->weight_kg ? $model->weight_kg . ' kg' : '-'; ?></td>
                            </tr>
                            <tr>
                                <th style="background:#f8f9fa;">Số đo 3 vòng</th>
                                <td><?php echo CHtml::encode($model->measurements) ?: '-'; ?></td>
                            </tr>
                            <tr>
                                <th style="background:#f8f9fa;">Tài năng</th>
                                <td><?php echo CHtml::encode($model->talent) ?: '-'; ?></td>
                            </tr>
                        </table>
                    </div>
                </div>
                <?php if (!empty($model->bio)): ?>
                    <div class="mt-3">
                        <h6>Tiểu sử</h6>
                        <p><?php echo nl2br(CHtml::encode($model->bio)); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Ảnh thí sinh</h5>
            </div>
            <div class="card-body text-center">
                <?php if (!empty($model->photo_portrait)): ?>
                    <img src="<?php echo $model->photo_portrait; ?>" class="img-fluid rounded mb-3" style="max-height:300px;" alt="Ảnh chân dung">
                <?php else: ?>
                    <div class="text-muted py-5">
                        <i class="fa fa-image fa-3x mb-2"></i>
                        <p>Chưa có ảnh</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
