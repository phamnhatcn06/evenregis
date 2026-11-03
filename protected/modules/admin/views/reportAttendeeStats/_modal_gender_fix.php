<?php
/**
 * Modal: sửa nhanh giới tính cho người chưa xác định giới tính.
 * @var ReportAttendeeStatsController $this
 */
?>
<div class="modal fade" id="genderFixModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="fa fa-venus-mars me-2 text-primary"></i>
                    Người chưa xác định giới tính
                    <span class="badge bg-danger ms-1" id="genderFixCount">0</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
            </div>
            <div class="modal-body p-0">
                <table class="table table-bordered table-hover mb-0 align-middle">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th class="text-center" style="width:50px;">STT</th>
                            <th>Họ và tên</th>
                            <th>Đơn vị</th>
                            <th class="text-center" colspan="2">Chọn giới tính</th>
                        </tr>
                    </thead>
                    <tbody id="genderFixBody">
                        <tr><td colspan="5" class="text-center text-muted py-3">Đang tải...</td></tr>
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Đóng</button>
            </div>
        </div>
    </div>
</div>
