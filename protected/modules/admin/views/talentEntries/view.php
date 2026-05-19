<?php
$this->breadcrumbs = array(
    'Tiết mục văn nghệ' => array('admin'),
    $model->title,
);

$this->menu = array(
    array(
        'label' => 'Thêm thành viên',
        'url' => $this->createUrl('addMember', array('entryId' => $model->id)),
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
$this->Tabletitle = 'Chi tiết tiết mục: ' . CHtml::encode($model->title);
?>

<div class="row">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Thông tin tiết mục</h5>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-striped">
                    <tr>
                        <th style="width:35%;background:#f8f9fa;">ID</th>
                        <td><?php echo CHtml::encode($model->id); ?></td>
                    </tr>
                    <tr>
                        <th style="background:#f8f9fa;">Tên tiết mục</th>
                        <td><strong><?php echo CHtml::encode($model->title); ?></strong></td>
                    </tr>
                    <tr>
                        <th style="background:#f8f9fa;">Hội diễn</th>
                        <td><?php echo CHtml::encode($model->show_name); ?></td>
                    </tr>
                    <tr>
                        <th style="background:#f8f9fa;">Thể loại</th>
                        <td><?php echo CHtml::encode($model->category_name); ?></td>
                    </tr>
                    <tr>
                        <th style="background:#f8f9fa;">Đơn vị</th>
                        <td><?php echo CHtml::encode($model->property_name); ?></td>
                    </tr>
                    <tr>
                        <th style="background:#f8f9fa;">Số người tham gia</th>
                        <td><?php echo CHtml::encode($model->participant_count); ?></td>
                    </tr>
                    <tr>
                        <th style="background:#f8f9fa;">Thời lượng</th>
                        <td><?php echo $model->duration_seconds ? floor($model->duration_seconds / 60) . ' phút ' . ($model->duration_seconds % 60) . ' giây' : '-'; ?></td>
                    </tr>
                    <tr>
                        <th style="background:#f8f9fa;">Thứ tự biểu diễn</th>
                        <td><?php echo $model->performance_order ?: '-'; ?></td>
                    </tr>
                    <tr>
                        <th style="background:#f8f9fa;">Trạng thái</th>
                        <td><?php echo TalentEntries::getStatusLabel($model->status); ?></td>
                    </tr>
                </table>
                <?php if (!empty($model->description)): ?>
                    <div class="mt-3">
                        <h6>Mô tả</h6>
                        <p><?php echo nl2br(CHtml::encode($model->description)); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Thành viên tham gia (<?php echo count($members); ?>)</h5>
            </div>
            <div class="card-body">
                <?php if (empty($members)): ?>
                    <p class="text-muted text-center">Chưa có thành viên nào</p>
                <?php else: ?>
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Họ tên</th>
                                <th width="120">Vai trò</th>
                                <th width="80">Thao tác</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($members as $member): ?>
                                <tr>
                                    <td><?php echo CHtml::encode($member->attendee_name); ?></td>
                                    <td><?php echo CHtml::encode($member->role); ?></td>
                                    <td>
                                        <form id="remove-member-<?php echo $member->id; ?>" method="post" action="<?php echo $this->createUrl('removeMember', array('id' => $member->id)); ?>" style="display:none;"></form>
                                        <button type="button" class="btn btn-sm btn-danger" onclick="confirmDelete('remove-member-<?php echo $member->id; ?>')">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
