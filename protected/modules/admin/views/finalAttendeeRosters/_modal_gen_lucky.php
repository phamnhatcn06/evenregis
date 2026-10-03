<?php
/**
 * Modal xác nhận cấp mã lucky.
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
                <h5 class="modal-title"><i class="fa fa-ticket me-1"></i>Cấp mã lucky</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>

            <div class="modal-body">
                <div class="alert alert-warning py-2 small">
                    <i class="fa fa-exclamation-triangle me-1"></i>
                    Chỉ cấp cho người <strong>chưa có mã</strong>.
                    <strong>Mã đã cấp không bao giờ thay đổi</strong> và không có chức năng cấp lại.
                </div>

                <?php if ($withoutLucky > 0): ?>
                    <p class="mb-3">
                        Hiện có <strong class="text-danger"><?php echo number_format($withoutLucky); ?></strong>
                        người chưa có mã lucky.
                    </p>
                <?php else: ?>
                    <p class="mb-3 text-success">
                        <i class="fa fa-check-circle me-1"></i>
                        Tất cả mọi người đã có mã lucky. Bấm cấp mã vẫn an toàn — hệ thống sẽ không đổi mã nào.
                    </p>
                <?php endif; ?>

                <form id="form_gen_lucky" method="post" action="<?php echo $this->createUrl('genLucky'); ?>">
                    <input type="hidden" name="event_id" value="<?php echo (int) $eventId; ?>">

                    <label class="form-label">Phạm vi cấp mã</label>
                    <?php echo CHtml::dropDownList('property_id', '', $propertyOptions, array(
                        'class' => 'form-select',
                        'empty' => '-- Toàn bộ sự kiện --',
                        'id'    => 'gen_lucky_property_id',
                    )); ?>
                    <div class="form-text">
                        Mã lucky cũng là định danh đăng nhập cổng chạy
                        (<code>DHMT</code> + mã) và dùng để ghép số BIB.
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-warning" id="btn_gen_lucky_submit">
                    <i class="fa fa-ticket me-1"></i>Cấp mã lucky
                </button>
            </div>
        </div>
    </div>
</div>
