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
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fa fa-info-circle me-2"></i>Thông tin tiết mục</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-bordered table-striped">
                            <tr>
                                <th style="width:40%;background:#f8f9fa;">Tên tiết mục</th>
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
                                <th style="background:#f8f9fa;">Đội liên quân</th>
                                <td>
                                    <?php if ($model->is_alliance_team): ?>
                                        <span class="badge bg-info">Có</span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">Không</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <tr>
                                <th style="background:#f8f9fa;">Trạng thái</th>
                                <td><?php echo TalentEntries::getStatusLabel($model->status); ?></td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-bordered table-striped">
                            <tr>
                                <th style="width:40%;background:#f8f9fa;">Số người tham gia</th>
                                <td><?php echo $model->participant_count ?: '-'; ?></td>
                            </tr>
                            <tr>
                                <th style="background:#f8f9fa;">Thời lượng</th>
                                <td>
                                    <?php
                                    if ($model->duration_seconds) {
                                        $mins = floor($model->duration_seconds / 60);
                                        $secs = $model->duration_seconds % 60;
                                        echo $mins . ' phút ' . ($secs > 0 ? $secs . ' giây' : '');
                                    } else {
                                        echo '-';
                                    }
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <th style="background:#f8f9fa;">Thứ tự biểu diễn</th>
                                <td><?php echo $model->performance_order ?: '-'; ?></td>
                            </tr>
                            <tr>
                                <th style="background:#f8f9fa;">Đạo diễn/Biên đạo</th>
                                <td><?php echo CHtml::encode($model->director) ?: '-'; ?></td>
                            </tr>
                            <tr>
                                <th style="background:#f8f9fa;">SĐT đạo diễn</th>
                                <td><?php echo CHtml::encode($model->director_phone) ?: '-'; ?></td>
                            </tr>
                            <tr>
                                <th style="background:#f8f9fa;">Nguồn gốc/Xuất xứ</th>
                                <td><?php echo CHtml::encode($model->origin) ?: '-'; ?></td>
                            </tr>
                        </table>
                    </div>
                </div>

                <?php if (!empty($model->description)): ?>
                <div class="mt-3">
                    <h6><i class="fa fa-align-left me-1"></i>Mô tả ngắn</h6>
                    <p class="mb-0"><?php echo nl2br(CHtml::encode($model->description)); ?></p>
                </div>
                <?php endif; ?>

                <?php if (!empty($model->content)): ?>
                <div class="mt-3">
                    <h6><i class="fa fa-file-text-o me-1"></i>Nội dung chi tiết</h6>
                    <div class="p-3 bg-light rounded"><?php echo nl2br(CHtml::encode($model->content)); ?></div>
                </div>
                <?php endif; ?>

                <?php if (!empty($model->note)): ?>
                <div class="mt-3">
                    <h6><i class="fa fa-sticky-note-o me-1"></i>Ghi chú</h6>
                    <p class="mb-0 text-muted"><?php echo nl2br(CHtml::encode($model->note)); ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fa fa-file me-2"></i>Tài liệu & Media</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="border rounded p-3 mb-3">
                            <h6 class="text-primary mb-3"><i class="fa fa-music me-1"></i>File nhạc nền</h6>
                            <?php if (!empty($model->music_path)): ?>
                                <audio controls class="w-100 mb-2">
                                    <source src="<?php echo CHtml::encode($model->music_path); ?>" type="audio/mpeg">
                                    Trình duyệt không hỗ trợ phát audio.
                                </audio>
                                <div class="mt-2">
                                    <a href="<?php echo CHtml::encode($model->music_path); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="fa fa-download me-1"></i>Tải xuống
                                    </a>
                                </div>
                            <?php else: ?>
                                <p class="text-muted mb-0"><i class="fa fa-times-circle me-1"></i>Chưa có file nhạc</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="border rounded p-3 mb-3">
                            <h6 class="text-primary mb-3"><i class="fa fa-video-camera me-1"></i>Video</h6>
                            <?php if (!empty($model->video_path)): ?>
                                <div class="ratio ratio-16x9 mb-2">
                                    <video class="plyr-video" playsinline controls>
                                        <source src="<?php echo CHtml::encode($model->video_path); ?>" type="video/mp4">
                                        Trình duyệt không hỗ trợ phát video.
                                    </video>
                                </div>
                                <div class="mt-2">
                                    <a href="<?php echo CHtml::encode($model->video_path); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="fa fa-external-link me-1"></i>Xem video
                                    </a>
                                </div>
                            <?php else: ?>
                                <p class="text-muted mb-0"><i class="fa fa-times-circle me-1"></i>Chưa có video</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="border rounded p-3">
                            <h6 class="text-primary mb-3"><i class="fa fa-file-pdf-o me-1"></i>Tài liệu đính kèm</h6>
                            <?php if (!empty($model->document)): ?>
                                <a href="<?php echo CHtml::encode($model->document); ?>" target="_blank" class="btn btn-outline-secondary">
                                    <i class="fa fa-download me-1"></i>Tải tài liệu
                                </a>
                                <span class="text-muted ms-2"><?php echo basename($model->document); ?></span>
                            <?php else: ?>
                                <p class="text-muted mb-0"><i class="fa fa-times-circle me-1"></i>Chưa có tài liệu</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fa fa-users me-2"></i>Thành viên (<?php echo count($members); ?>)</h5>
                <a href="<?php echo $this->createUrl('addMember', array('entryId' => $model->id)); ?>" class="btn btn-sm btn-success">
                    <i class="fa fa-plus"></i>
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($members)): ?>
                    <div class="text-center py-4">
                        <i class="fa fa-users fa-3x text-muted mb-3"></i>
                        <p class="text-muted mb-0">Chưa có thành viên nào</p>
                        <a href="<?php echo $this->createUrl('addMember', array('entryId' => $model->id)); ?>" class="btn btn-sm btn-outline-primary mt-2">
                            <i class="fa fa-plus me-1"></i>Thêm thành viên
                        </a>
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach ($members as $index => $member): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center px-0">
                                <div>
                                    <span class="badge bg-secondary me-2"><?php echo $index + 1; ?></span>
                                    <strong><?php echo CHtml::encode($member->attendee_name); ?></strong>
                                    <?php if (!empty($member->role)): ?>
                                        <br><small class="text-muted ms-4"><?php echo CHtml::encode($member->role); ?></small>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <form id="remove-member-<?php echo $member->id; ?>" method="post" action="<?php echo $this->createUrl('removeMember', array('id' => $member->id)); ?>" style="display:none;"></form>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete('remove-member-<?php echo $member->id; ?>')">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fa fa-clock-o me-2"></i>Thông tin hệ thống</h5>
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr>
                        <td class="text-muted">ID:</td>
                        <td><?php echo CHtml::encode($model->id); ?></td>
                    </tr>
                    <?php if (!empty($model->created_at)): ?>
                    <tr>
                        <td class="text-muted">Ngày đăng ký:</td>
                        <td><?php echo date('d/m/Y H:i', strtotime($model->created_at)); ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if (!empty($model->updated_at)): ?>
                    <tr>
                        <td class="text-muted">Cập nhật:</td>
                        <td><?php echo date('d/m/Y H:i', strtotime($model->updated_at)); ?></td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>
</div>
