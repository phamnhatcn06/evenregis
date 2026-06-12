<?php
$this->breadcrumbs = array(
    'Duyệt đăng ký',
);

$this->Tabletitle = 'Danh sách chờ duyệt';
$pendingCount = count($pendingList);
?>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card bg-warning text-white">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0 text-white">Đang chờ duyệt</h6>
                        <h2 class="mb-0 text-white"><?php echo $pendingCount; ?></h2>
                    </div>
                    <div class="fs-1">
                        <i class="fa fa-clock-o"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card bg-light">
            <div class="card-body">
                <p class="mb-1"><strong>Xin chào,</strong> <?php echo CHtml::encode($ssoUser['full_name'] ?? $ssoUser['username']); ?></p>
                <p class="mb-0 text-muted small">Bạn có <?php echo $pendingCount; ?> đơn đăng ký đang chờ duyệt.</p>
            </div>
        </div>
    </div>
</div>

<?php if (empty($pendingList)): ?>
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="fa fa-check-circle fa-4x text-success mb-3"></i>
            <h5>Không có đơn nào chờ duyệt</h5>
            <p class="text-muted">Tất cả đơn đăng ký đã được xử lý.</p>
        </div>
    </div>
<?php else: ?>
    <div class="card">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width:60px;">ID</th>
                        <th>Đơn đăng ký</th>
                        <th style="width:150px;">Bước hiện tại</th>
                        <th style="width:100px;">Tiến độ</th>
                        <th style="width:150px;">Thời gian nộp</th>
                        <th style="width:120px;">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($pendingList as $item): ?>
                        <tr>
                            <td><?php echo $item->id; ?></td>
                            <td>
                                <strong>Đăng ký #<?php echo $item->registration_id; ?></strong>
                                <?php if (isset($item->registration)): ?>
                                    <br><small class="text-muted">
                                        <?php echo CHtml::encode($item->registration->organization_name ?? ''); ?>
                                    </small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-info">Bước <?php echo $item->current_index; ?></span>
                                <br><small><?php echo CHtml::encode($item->getCurrentStepName()); ?></small>
                            </td>
                            <td>
                                <div class="progress" style="height: 20px;">
                                    <?php
                                    $percent = ($item->current_index / $item->total_steps) * 100;
                                    ?>
                                    <div class="progress-bar bg-info" style="width: <?php echo $percent; ?>%;">
                                        <?php echo $item->getProgressText(); ?>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <?php echo $item->started_at ? date('d/m/Y H:i', $item->started_at) : '-'; ?>
                            </td>
                            <td>
                                <a href="<?php echo $this->createUrl('view', array('id' => $item->id)); ?>"
                                   class="btn btn-sm btn-primary">
                                    <i class="fa fa-eye"></i> Xem & Duyệt
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>
