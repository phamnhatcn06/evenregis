<?php
/**
 * Modal đồng bộ danh sách VCK: chọn phạm vi -> Xem trước (dry-run) -> Ghi thật.
 *
 * @var FinalAttendeeRostersController $this
 * @var int $eventId
 * @var int $periodId
 * @var array $filterOptions
 */

$propertyOptions = array();
foreach ($filterOptions['properties'] as $property) {
    $propertyOptions[$property['id']] = $property['name'];
}
?>
<div class="modal fade" id="modal_sync" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa fa-refresh me-1"></i>Đồng bộ từ danh sách Vòng Chung Kết
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>

            <div class="modal-body">
                <form id="form_sync"
                      method="post"
                      action="<?php echo $this->createUrl('sync'); ?>"
                      data-preview-url="<?php echo $this->createUrl('syncPreview'); ?>">
                    <input type="hidden" name="event_id" value="<?php echo (int) $eventId; ?>">
                    <input type="hidden" name="period_id" value="<?php echo (int) $periodId; ?>">

                    <div class="mb-3">
                        <label class="form-label">Phạm vi đồng bộ</label>
                        <?php echo CHtml::dropDownList('property_id', '', $propertyOptions, array(
                            'class' => 'form-select',
                            'empty' => '-- Toàn bộ sự kiện --',
                            'id'    => 'sync_property_id',
                        )); ?>
                        <div class="form-text">
                            Chọn một đơn vị nếu chỉ muốn đồng bộ riêng đơn vị đó.
                        </div>
                    </div>
                </form>

                <div class="alert alert-info py-2 small mb-3">
                    <i class="fa fa-info-circle me-1"></i>
                    Đồng bộ <strong>không bao giờ ghi đè</strong> những trường bạn đã sửa thủ công,
                    và <strong>không chạm vào mã lucky</strong> đã cấp.
                </div>

                <div id="sync_preview_empty" class="text-center text-muted py-4">
                    <i class="fa fa-search fa-2x mb-2 d-block"></i>
                    Bấm <strong>Xem trước</strong> để biết đồng bộ sẽ thay đổi những gì.
                </div>

                <div id="sync_preview_result" class="d-none">
                    <h6 class="mb-2">Kết quả xem trước</h6>
                    <div class="table-responsive mb-3">
                        <table class="table table-sm table-bordered mb-0">
                            <tbody>
                                <tr>
                                    <th class="bg-light">Thêm mới</th>
                                    <td><span class="badge bg-success" id="sync_sum_inserted">0</span></td>
                                    <th class="bg-light">Khôi phục (giữ mã)</th>
                                    <td><span class="badge bg-info" id="sync_sum_restored">0</span></td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Cập nhật</th>
                                    <td><span class="badge bg-primary" id="sync_sum_updated">0</span></td>
                                    <th class="bg-light">Không thay đổi</th>
                                    <td><span class="badge bg-secondary" id="sync_sum_unchanged">0</span></td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Bỏ qua do đã sửa tay</th>
                                    <td><span class="badge bg-warning text-dark" id="sync_sum_skipped">0</span></td>
                                    <th class="bg-light">Huỷ tư cách</th>
                                    <td><span class="badge bg-danger" id="sync_sum_deleted">0</span></td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Xung đột cần soát</th>
                                    <td colspan="3"><span class="badge bg-danger" id="sync_sum_conflicts">0</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div id="sync_detail_skipped" class="d-none mb-3">
                        <div class="fw-semibold text-warning mb-1">
                            <i class="fa fa-pencil me-1"></i>Trường bị bỏ qua vì bạn đã sửa thủ công
                        </div>
                        <ul class="small mb-0" id="sync_list_skipped"></ul>
                    </div>

                    <div id="sync_detail_restored" class="d-none mb-3">
                        <div class="fw-semibold text-info mb-1">
                            <i class="fa fa-undo me-1"></i>Người được khôi phục (giữ nguyên mã lucky)
                        </div>
                        <ul class="small mb-0" id="sync_list_restored"></ul>
                    </div>

                    <div id="sync_detail_deleted" class="d-none mb-3">
                        <div class="fw-semibold text-danger mb-1">
                            <i class="fa fa-user-times me-1"></i>Người sẽ bị huỷ tư cách (mã lucky vẫn được giữ)
                        </div>
                        <ul class="small mb-0" id="sync_list_deleted"></ul>
                    </div>

                    <div id="sync_detail_conflicts" class="d-none mb-3">
                        <div class="fw-semibold text-danger mb-1">
                            <i class="fa fa-exclamation-triangle me-1"></i>Xung đột cần soát lại
                        </div>
                        <ul class="small mb-0" id="sync_list_conflicts"></ul>
                    </div>

                    <div id="sync_detail_inserted" class="d-none">
                        <div class="fw-semibold text-success mb-1">
                            <i class="fa fa-user-plus me-1"></i>Người sẽ được thêm mới
                        </div>
                        <ul class="small mb-0" id="sync_list_inserted"></ul>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-info" id="btn_sync_preview">
                    <i class="fa fa-search me-1"></i>Xem trước
                </button>
                <button type="button" class="btn btn-primary" id="btn_sync_apply" disabled
                        title="Hãy xem trước kết quả trước khi ghi thật">
                    <i class="fa fa-save me-1"></i>Ghi thật
                </button>
            </div>
        </div>
    </div>
</div>
