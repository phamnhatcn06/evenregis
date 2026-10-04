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
                    <span class="far-kpi-icon-bubble far-kpi-bubble-primary mb-0 me-2" style="width: 34px; height: 34px; font-size: 15px;">
                        <i class="fa fa-refresh"></i>
                    </span>
                    <span>Đồng bộ từ danh sách Vòng Chung Kết</span>
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
                        <label class="form-label fw-semibold">Phạm vi đồng bộ</label>
                        <?php echo CHtml::dropDownList('property_id', '', $propertyOptions, array(
                            'class' => 'form-select',
                            'empty' => '-- Toàn bộ sự kiện --',
                            'id'    => 'sync_property_id',
                        )); ?>
                        <div class="form-text">
                            <i class="fa fa-info-circle me-1"></i>Chọn một đơn vị cụ thể nếu bạn chỉ muốn nạp / đồng bộ riêng đơn vị đó.
                        </div>
                    </div>
                </form>

                <div class="alert alert-info py-2 small mb-3 border-0 d-flex align-items-center" style="background: rgba(8, 177, 186, 0.1); color: #067c83;">
                    <i class="fa fa-shield fa-lg me-2 flex-shrink-0"></i>
                    <div>
                        Đồng bộ <strong>an toàn tuyệt đối</strong>: không bao giờ ghi đè những trường bạn đã sửa thủ công và <strong>không thay đổi mã lucky</strong> đã cấp.
                    </div>
                </div>

                <div id="sync_preview_empty" class="far-empty-state py-4">
                    <div class="far-empty-icon" style="width: 56px; height: 56px; font-size: 24px;">
                        <i class="fa fa-search"></i>
                    </div>
                    <div class="text-muted small">
                        Bấm <strong>Xem trước</strong> bên dưới để kiểm tra các thay đổi trước khi ghi thật vào cơ sở dữ liệu.
                    </div>
                </div>

                <div id="sync_preview_result" class="d-none">
                    <h6 class="mb-3 fw-bold text-dark d-flex align-items-center">
                        <i class="fa fa-pie-chart text-primary me-2"></i>Kết quả phân tích xem trước
                    </h6>

                    <!-- Grid thống kê kết quả -->
                    <div class="row g-2 mb-3">
                        <div class="col-6 col-md-3">
                            <div class="p-2 border rounded-3 text-center bg-light">
                                <div class="small text-muted mb-1">Thêm mới</div>
                                <span class="badge bg-success fs-6" id="sync_sum_inserted">0</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-2 border rounded-3 text-center bg-light">
                                <div class="small text-muted mb-1">Cập nhật</div>
                                <span class="badge bg-primary fs-6" id="sync_sum_updated">0</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-2 border rounded-3 text-center bg-light">
                                <div class="small text-muted mb-1">Khôi phục (giữ mã)</div>
                                <span class="badge bg-info fs-6" id="sync_sum_restored">0</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-2 border rounded-3 text-center bg-light">
                                <div class="small text-muted mb-1">Không đổi</div>
                                <span class="badge bg-secondary fs-6" id="sync_sum_unchanged">0</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-4 mt-2">
                            <div class="p-2 border rounded-3 text-center bg-light">
                                <div class="small text-muted mb-1">Bỏ qua do đã sửa tay</div>
                                <span class="badge bg-warning text-dark fs-6" id="sync_sum_skipped">0</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-4 mt-2">
                            <div class="p-2 border rounded-3 text-center bg-light">
                                <div class="small text-muted mb-1">Huỷ tư cách</div>
                                <span class="badge bg-danger fs-6" id="sync_sum_deleted">0</span>
                            </div>
                        </div>
                        <div class="col-12 col-md-4 mt-2">
                            <div class="p-2 border rounded-3 text-center bg-light">
                                <div class="small text-muted mb-1">Xung đột cần soát</div>
                                <span class="badge bg-danger fs-6" id="sync_sum_conflicts">0</span>
                            </div>
                        </div>
                    </div>

                    <!-- Danh sách chi tiết nếu có -->
                    <div id="sync_detail_skipped" class="d-none mb-3 p-3 bg-light rounded-3 border">
                        <div class="fw-semibold text-warning mb-1">
                            <i class="fa fa-pencil me-1"></i>Trường bị bỏ qua vì bạn đã sửa thủ công:
                        </div>
                        <ul class="small mb-0 text-muted" id="sync_list_skipped"></ul>
                    </div>

                    <div id="sync_detail_restored" class="d-none mb-3 p-3 bg-light rounded-3 border">
                        <div class="fw-semibold text-info mb-1">
                            <i class="fa fa-undo me-1"></i>Người được khôi phục (giữ nguyên mã lucky):
                        </div>
                        <ul class="small mb-0 text-muted" id="sync_list_restored"></ul>
                    </div>

                    <div id="sync_detail_deleted" class="d-none mb-3 p-3 bg-light rounded-3 border">
                        <div class="fw-semibold text-danger mb-1">
                            <i class="fa fa-user-times me-1"></i>Người sẽ bị huỷ tư cách (mã lucky vẫn được giữ):
                        </div>
                        <ul class="small mb-0 text-muted" id="sync_list_deleted"></ul>
                    </div>

                    <div id="sync_detail_conflicts" class="d-none mb-3 p-3 bg-light rounded-3 border">
                        <div class="fw-semibold text-danger mb-1">
                            <i class="fa fa-exclamation-triangle me-1"></i>Xung đột cần soát lại:
                        </div>
                        <ul class="small mb-0 text-muted" id="sync_list_conflicts"></ul>
                    </div>

                    <div id="sync_detail_inserted" class="d-none p-3 bg-light rounded-3 border">
                        <div class="fw-semibold text-success mb-1">
                            <i class="fa fa-user-plus me-1"></i>Người sẽ được thêm mới:
                        </div>
                        <ul class="small mb-0 text-muted" id="sync_list_inserted"></ul>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Đóng</button>
                <button type="button" class="btn btn-info text-white" id="btn_sync_preview">
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
