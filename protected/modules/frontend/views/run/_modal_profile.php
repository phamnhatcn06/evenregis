<?php
/**
 * Modal bổ sung / xác nhận ngày sinh + giới tính của đại biểu.
 * - Thiếu thông tin: bắt buộc điền (không cho đóng ngoài).
 * - Đã có: hiển thị sẵn để người dùng xác nhận (có thể sửa).
 * Tham số: $saveProfileUrl, $fullName, $birthday (Y-m-d|''), $gender (0|1|''), $isComplete (0|1)
 */
$isComplete = !empty($isComplete);
?>
<div class="modal fade" id="modalProfile" tabindex="-1" aria-labelledby="modalProfileLabel" aria-hidden="true"
     data-bs-backdrop="<?php echo $isComplete ? 'true' : 'static'; ?>"
     data-bs-keyboard="<?php echo $isComplete ? 'true' : 'false'; ?>">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="form-profile" action="<?php echo $saveProfileUrl; ?>" method="post">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalProfileLabel">
                        <i class="bi bi-person-vcard me-2 text-primary"></i>
                        <?php echo $isComplete ? 'Xác nhận thông tin cá nhân' : 'Bổ sung thông tin cá nhân'; ?>
                    </h5>
                    <?php if ($isComplete): ?>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Đóng"></button>
                    <?php endif; ?>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-3">
                        <i class="bi bi-info-circle me-1"></i>
                        <?php echo $isComplete
                            ? 'Vui lòng kiểm tra và xác nhận ngày sinh, giới tính của bạn trước khi đăng ký hoạt động.'
                            : 'Vui lòng cung cấp ngày sinh và giới tính để hoàn tất hồ sơ tham dự.'; ?>
                    </p>

                    <?php if (!empty($fullName)): ?>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Họ và tên</label>
                            <input type="text" class="form-control" value="<?php echo CHtml::encode($fullName); ?>" disabled>
                        </div>
                    <?php endif; ?>

                    <div class="mb-3">
                        <label for="profile-birthday" class="form-label fw-semibold">
                            Ngày sinh <span class="text-danger">*</span>
                        </label>
                        <input type="date" class="form-control" id="profile-birthday" name="birthday"
                               value="<?php echo CHtml::encode($birthday); ?>"
                               max="<?php echo date('Y-m-d'); ?>" required>
                    </div>

                    <div class="mb-1">
                        <label class="form-label fw-semibold d-block">
                            Giới tính <span class="text-danger">*</span>
                        </label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="gender" id="profile-gender-male" value="1"
                                <?php echo ($gender === 1) ? 'checked' : ''; ?> required>
                            <label class="form-check-label" for="profile-gender-male">Nam</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="gender" id="profile-gender-female" value="0"
                                <?php echo ($gender === 0) ? 'checked' : ''; ?> required>
                            <label class="form-check-label" for="profile-gender-female">Nữ</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <?php if ($isComplete): ?>
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Để sau</button>
                    <?php endif; ?>
                    <button type="submit" class="btn btn-primary" id="btn-submit-profile">
                        <i class="bi bi-check-circle me-1"></i>
                        <?php echo $isComplete ? 'Xác nhận' : 'Lưu thông tin'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
