<?php
$this->breadcrumbs = array(
    'Đội thể thao' => array('admin'),
    $model->team_name,
);

$this->menu = array(
    array(
        'label' => 'Thêm thành viên',
        'url' => $this->createUrl('addMember', array('teamId' => $model->id)),
        'color' => 'success',
        'icon' => 'fa-user-plus',
    ),
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
$this->Tabletitle = 'Chi tiết đội: ' . CHtml::encode($model->team_name);
?>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Thông tin đội</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <tr>
                        <th style="width:35%;background:#f8f9fa;">ID</th>
                        <td><?php echo CHtml::encode($model->id); ?></td>
                    </tr>
                    <tr>
                        <th style="background:#f8f9fa;">Tên đội</th>
                        <td><?php echo CHtml::encode($model->team_name); ?></td>
                    </tr>
                    <tr>
                        <th style="background:#f8f9fa;">Môn thể thao</th>
                        <td><?php echo CHtml::encode($model->sport_name); ?></td>
                    </tr>
                    <tr>
                        <th style="background:#f8f9fa;">Đơn vị</th>
                        <td><?php echo CHtml::encode($model->property_name); ?></td>
                    </tr>
                    <tr>
                        <th style="background:#f8f9fa;">Liên quân</th>
                        <td><?php echo $model->is_alliance ? '<span class="badge bg-info">Có</span>' : '<span class="badge bg-secondary">Không</span>'; ?></td>
                    </tr>
                    <tr>
                        <th style="background:#f8f9fa;">Trạng thái</th>
                        <td><?php echo SportTeams::getStatusLabel($model->status); ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Danh sách thành viên (<?php echo count($members); ?>)</h5>
            </div>
            <div class="card-body">
                <?php if (empty($members)): ?>
                    <p class="text-muted text-center">Chưa có thành viên nào</p>
                <?php else: ?>
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Họ tên</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($members as $index => $member): ?>
                                <tr>
                                    <td><?php echo $index + 1; ?></td>
                                    <td><?php echo CHtml::encode($member->attendee_name); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
