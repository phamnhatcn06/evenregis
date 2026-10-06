<?php
/** Khối đăng ký Chạy bộ (Fun Run). Tham số: $runMine, $runEvents. */
?>
<div class="card shadow-sm h-100">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0"><i class="fa fa-running me-1"></i> Chạy bộ (Fun Run)</h5>
    </div>
    <div class="card-body">
        <?php if (!empty($runMine)): ?>
            <?php $isPending = (isset($runMine['status']) && $runMine['status'] === 'cancel_requested'); ?>
            <div class="text-center">
                <i class="fa fa-check-circle text-success" style="font-size:40px;"></i>
                <h6 class="mt-2 mb-1">Bạn đã đăng ký</h6>
            </div>
            <table class="table table-bordered mt-2 mb-2">
                <tr>
                    <th style="width:40%;background:#f8f9fa;">Cự ly</th>
                    <td><?php echo CHtml::encode(isset($runMine['run_event_name']) ? $runMine['run_event_name'] : ''); ?></td>
                </tr>
                <tr>
                    <th style="background:#f8f9fa;">Số BIB</th>
                    <td><span class="badge bg-primary fs-6"><?php echo CHtml::encode(isset($runMine['bib_number']) ? $runMine['bib_number'] : ''); ?></span></td>
                </tr>
            </table>
            <?php if ($isPending): ?>
                <div class="alert alert-warning mb-0"><i class="fa fa-clock-o"></i> Yêu cầu hủy đang chờ ban tổ chức duyệt.</div>
            <?php elseif (!empty($runMine['can_request_cancel'])): ?>
                <button type="button" class="btn btn-outline-danger w-100" data-bs-toggle="modal" data-bs-target="#modalCancelRun">
                    <i class="fa fa-times-circle"></i> Xin hủy đăng ký
                </button>
            <?php else: ?>
                <div class="alert alert-info mb-0 small">Đã khóa — không thể thay đổi.</div>
            <?php endif; ?>
        <?php elseif (empty($runEvents)): ?>
            <div class="alert alert-warning mb-0">Hiện chưa có cự ly nào được mở đăng ký.</div>
        <?php else: ?>
            <p class="text-muted small">Chọn <strong>1 cự ly</strong>:</p>
            <?php foreach ($runEvents as $e):
                $remaining = (int) $e['remaining'];
                $badgeCls = $remaining <= 0 ? 'bg-danger' : ($remaining <= 5 ? 'bg-warning text-dark' : 'bg-success');
            ?>
                <div class="border rounded p-2 mb-2 d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-semibold"><?php echo CHtml::encode($e['name']); ?></div>
                        <span class="badge <?php echo $badgeCls; ?>">Còn <?php echo $remaining; ?></span>
                        <span class="text-muted small">(<?php echo (int) $e['registered_count']; ?>/<?php echo (int) $e['quota']; ?>)</span>
                    </div>
                    <?php if (!empty($e['not_started'])): ?>
                        <button class="btn btn-sm btn-secondary" disabled>Chưa mở</button>
                    <?php elseif (!empty($e['ended'])): ?>
                        <button class="btn btn-sm btn-secondary" disabled>Hết hạn</button>
                    <?php elseif ($remaining <= 0): ?>
                        <button class="btn btn-sm btn-secondary" disabled>Hết chỗ</button>
                    <?php else: ?>
                        <button type="button" class="btn btn-sm btn-primary btn-portal-register"
                                data-type="run" data-id="<?php echo (int) $e['id']; ?>"
                                data-name="<?php echo CHtml::encode($e['name']); ?>">Đăng ký</button>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
