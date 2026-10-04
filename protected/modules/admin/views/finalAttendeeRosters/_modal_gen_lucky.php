<?php
/**
 * Modal xác nhận cấp mã lucky — Giao diện hiện đại.
 *
 * @var FinalAttendeeRostersController $this
 * @var int $eventId
 * @var array|null $stats
 * @var array $filterOptions
 */

$propertyOptions = array();
foreach ($filterOptions['properties'] as $property) {
    $propertyOptions[$property['id']] = $property['name'];
}

$withoutLucky = $stats ? (int) $stats['without_lucky'] : 0;
?>
<div class="modal fade" id="modal_gen_lucky" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <span class="far-kpi-icon-bubble far-kpi-bubble-warning mb-0 me-2" style="width: 34px; height: 34px; font-size: 15px;">
                        <i class="fa fa-ticket"></i>
                    </span>
                    <span>Cấp mã lucky Vòng Chung Kết</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>

            <div class="modal-body">
                <div class="alert alert-warning py-2 small mb-3 border-0 d-flex align-items-center" style="background: rgba(241, 106, 27, 0.1); color: #92400e;">
                    <i class="fa fa-exclamation-triangle fa-lg me-2 flex-shrink-0"></i>
                    <div>
                        Chỉ cấp cho người <strong>chưa có mã</strong>. <strong>Mã đã cấp không bao giờ thay đổi</strong> và không có tính năng cấp lại.
                    </div>
                </div>

                <?php if ($withoutLucky > 0): ?>
                    <div class="p-3 rounded-3 mb-3 border d-flex align-items-center justify-content-between" style="background: #fff1f2; border-color: #fecdd3 !important;">
                        <div>
                            <div class="small fw-semibold text-danger">Số người chưa có mã:</div>
                            <div class="fs-4 fw-bold text-danger"><?php echo number_format($withoutLucky); ?> người</div>
                        </div>
                        <i class="fa fa-ticket fa-2x text-danger opacity-50"></i>
                    </div>
                <?php else: ?>
                    <div class="p-3 rounded-3 mb-3 border d-flex align-items-center" style="background: #f0fdf4; border-color: #bbf7d0 !important;">
                        <i class="fa fa-check-circle fa-2x text-success me-3"></i>
                        <div>
                            <div class="fw-bold text-success">Tất cả mọi người đã có mã lucky!</div>
                            <div class="small text-muted">Bấm cấp mã vẫn an toàn — hệ thống sẽ không sửa đổi mã nào đã cấp.</div>
                        </div>
                    </div>
                <?php endif; ?>

                <form id="form_gen_lucky" method="post" action="<?php echo $this->createUrl('genLucky'); ?>">
                    <input type="hidden" name="event_id" value="<?php echo (int) $eventId; ?>">

                    <div class="mb-2">
                        <label class="form-label fw-semibold">Phạm vi cấp mã</label>
                        <?php echo CHtml::dropDownList('property_id', '', $propertyOptions, array(
                            'class' => 'form-select',
                            'empty' => '-- Toàn bộ sự kiện --',
                            'id'    => 'gen_lucky_property_id',
                        )); ?>
                        <div class="form-text">
                            <i class="fa fa-info-circle me-1"></i>Mã lucky cũng là định danh đăng nhập cổng chạy (<code>DHMT</code> + mã) và dùng để ghép số BIB.
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="far-btn far-btn-warning text-white" id="btn_gen_lucky_submit">
                    <i class="fa fa-ticket"></i>
                    <span>Cấp mã lucky</span>
                </button>
            </div>
        </div>
    </div>
</div>
