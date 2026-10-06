<?php
/** Khối đăng ký Đi tham quan. Tham số: $tourMine, $tourSessions. */
?>
<div class="card shadow-sm h-100">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0"><i class="fa fa-bus me-1"></i> Đi tham quan</h5>
    </div>
    <div class="card-body">
        <?php if (!empty($tourMine)): ?>
            <?php $isPending = (isset($tourMine['status']) && $tourMine['status'] === 'cancel_requested'); ?>
            <div class="text-center">
                <i class="fa fa-check-circle text-success" style="font-size:40px;"></i>
                <h6 class="mt-2 mb-1">Bạn đã đăng ký</h6>
            </div>
            <table class="table table-bordered mt-2 mb-2">
                <tr>
                    <th style="width:40%;background:#f8f9fa;">Đợt</th>
                    <td><?php echo CHtml::encode(isset($tourMine['tour_session_name']) ? $tourMine['tour_session_name'] : ''); ?></td>
                </tr>
                <tr>
                    <th style="background:#f8f9fa;">Khung giờ</th>
                    <td><?php echo CHtml::encode(isset($tourMine['start_time']) ? $tourMine['start_time'] : ''); ?></td>
                </tr>
            </table>
            <?php if ($isPending): ?>
                <div class="alert alert-warning mb-0"><i class="fa fa-clock-o"></i> Yêu cầu hủy đang chờ ban tổ chức duyệt.</div>
            <?php elseif (!empty($tourMine['can_request_cancel'])): ?>
                <button type="button" class="btn btn-outline-danger w-100" data-bs-toggle="modal" data-bs-target="#modalCancelTour">
                    <i class="fa fa-times-circle"></i> Xin hủy đăng ký
                </button>
            <?php else: ?>
                <div class="alert alert-info mb-0 small">Đã khóa — không thể thay đổi.</div>
            <?php endif; ?>
        <?php elseif (empty($tourSessions)): ?>
            <div class="alert alert-warning mb-0">Hiện chưa có đợt tham quan nào được mở đăng ký.</div>
        <?php else: ?>
            <p class="text-muted small">Chọn <strong>1 đợt</strong>:</p>
            <?php foreach ($tourSessions as $s):
                $remaining = (int) $s['remaining'];
                $badgeCls = $remaining <= 0 ? 'bg-danger' : ($remaining <= 5 ? 'bg-warning text-dark' : 'bg-success');
            ?>
                <div class="border rounded p-2 mb-2 d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-semibold"><?php echo CHtml::encode($s['name']); ?></div>
                        <div class="text-muted small"><?php echo CHtml::encode(isset($s['start_time']) ? $s['start_time'] : ''); ?></div>
                        <span class="badge <?php echo $badgeCls; ?>">Còn <?php echo $remaining; ?></span>
                        <span class="text-muted small">(<?php echo (int) $s['registered_count']; ?>/<?php echo (int) $s['quota']; ?>)</span>
                    </div>
                    <?php if (!empty($s['not_started'])): ?>
                        <button class="btn btn-sm btn-secondary" disabled>Chưa mở</button>
                    <?php elseif (!empty($s['ended'])): ?>
                        <button class="btn btn-sm btn-secondary" disabled>Hết hạn</button>
                    <?php elseif ($remaining <= 0): ?>
                        <button class="btn btn-sm btn-secondary" disabled>Hết chỗ</button>
                    <?php else: ?>
                        <button type="button" class="btn btn-sm btn-success btn-portal-register"
                                data-type="tour" data-id="<?php echo (int) $s['id']; ?>"
                                data-name="<?php echo CHtml::encode($s['name']); ?>">Đăng ký</button>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
