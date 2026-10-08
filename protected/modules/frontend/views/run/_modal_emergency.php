<?php
/**
 * Modal khai báo Thông tin khẩn cấp (tùy chọn) cho người đăng ký chạy.
 * Dùng chung cho cả 2 luồng: bật tự động sau khi đăng ký thành công và khi bấm
 * nút "Chỉnh sửa". Tất cả các trường đều KHÔNG bắt buộc.
 * Tham số: $runMine (mảng đăng ký hiện tại), $saveUrl.
 */
$emName  = isset($runMine['emergency_contact_name']) ? $runMine['emergency_contact_name'] : '';
$emPhone = isset($runMine['emergency_contact_phone']) ? $runMine['emergency_contact_phone'] : '';
$emCond  = isset($runMine['medical_conditions']) ? $runMine['medical_conditions'] : '';
$emMed   = isset($runMine['medications']) ? $runMine['medications'] : '';
?>
<div class="modal fade" id="modalEmergency" tabindex="-1" aria-labelledby="modalEmergencyLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="form-emergency" action="<?php echo $saveUrl; ?>" method="post">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalEmergencyLabel">
                        <i class="bi bi-heart-pulse-fill text-danger me-1"></i> Thông tin khẩn cấp
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-3">
                        <i class="bi bi-info-circle me-1"></i>
                        Các thông tin dưới đây giúp Ban tổ chức hỗ trợ bạn khi cần thiết.
                        Tất cả đều <strong>không bắt buộc</strong> — bạn có thể bỏ trống và bổ sung sau.
                    </p>

                    <div class="mb-3">
                        <label class="form-label" for="emergency_contact_name">Tên người liên hệ</label>
                        <input type="text" class="form-control" id="emergency_contact_name"
                               name="emergency_contact_name" maxlength="150"
                               placeholder="VD: Nguyễn Văn B (người thân)"
                               value="<?php echo CHtml::encode($emName); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="emergency_contact_phone">Số điện thoại</label>
                        <input type="tel" class="form-control" id="emergency_contact_phone"
                               name="emergency_contact_phone" maxlength="30"
                               placeholder="VD: 0912 345 678"
                               value="<?php echo CHtml::encode($emPhone); ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="medical_conditions">Có bệnh nền gì không?</label>
                        <textarea class="form-control" id="medical_conditions" name="medical_conditions"
                                  rows="2" maxlength="500"
                                  placeholder="VD: Tim mạch, huyết áp, hen suyễn... (nếu có)"><?php echo CHtml::encode($emCond); ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="medications">Có đang dùng thuốc gì không?</label>
                        <textarea class="form-control" id="medications" name="medications"
                                  rows="2" maxlength="500"
                                  placeholder="VD: Thuốc huyết áp, tiểu đường... (nếu có)"><?php echo CHtml::encode($emMed); ?></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Để sau</button>
                    <button type="submit" class="btn btn-primary" id="btn-submit-emergency">
                        <i class="bi bi-check-circle me-1"></i> Xác nhận
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
