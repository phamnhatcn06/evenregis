<?php
/**
 * Popup xác nhận / bổ sung hồ sơ đại biểu — hiện ngay sau khi đăng nhập, ở mọi trang của
 * cổng cá nhân, cho tới khi lưu thành công (reload vẫn hiện lại). Không có nút đóng.
 * Tham số: $saveProfileUrl, $logoutUrl, $fullName, $profile (mảng từ RunAuth::getProfile hoặc null)
 */
$hasProfile = is_array($profile);
$displayName = $hasProfile && !empty($profile['full_name']) ? $profile['full_name'] : $fullName;
$position = $hasProfile && !empty($profile['position']) ? $profile['position'] : '';
$unitName = $hasProfile && !empty($profile['unit_name']) ? $profile['unit_name'] : '';
$birthYear = $hasProfile && !empty($profile['birth_year']) ? (int) $profile['birth_year'] : 0;
$gender = $hasProfile && isset($profile['gender']) && $profile['gender'] !== null ? (int) $profile['gender'] : null;
$isComplete = $hasProfile && !empty($profile['is_complete']);

$minYear = 1950;
$maxYear = (int) date('Y');
?>
<div class="modal fade" id="modalProfile" tabindex="-1" aria-labelledby="modalProfileLabel" aria-hidden="true"
     data-bs-backdrop="static" data-bs-keyboard="false">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form id="form-profile" action="<?php echo $saveProfileUrl; ?>" method="post">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalProfileLabel">
                        <i class="bi bi-person-vcard me-2 text-primary"></i>
                        <?php echo $isComplete ? 'Xác nhận thông tin cá nhân' : 'Bổ sung thông tin cá nhân'; ?>
                    </h5>
                </div>

                <div class="modal-body">
                    <?php if (!$hasProfile): ?>
                        <div class="alert alert-warning mb-0">
                            <i class="bi bi-exclamation-triangle me-1"></i>
                            Không tải được hồ sơ đại biểu của bạn. Vui lòng tải lại trang hoặc liên hệ Ban tổ chức.
                        </div>
                    <?php else: ?>
                        <p class="text-muted small mb-3">
                            <i class="bi bi-info-circle me-1"></i>
                            Vui lòng kiểm tra thông tin và chọn đầy đủ năm sinh, giới tính để sử dụng các chức năng đăng ký.
                        </p>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Họ và tên</label>
                            <input type="text" class="form-control" value="<?php echo CHtml::encode($displayName); ?>" readonly disabled>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Chức danh</label>
                            <input type="text" class="form-control" value="<?php echo CHtml::encode($position ?: '—'); ?>" readonly disabled>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Đơn vị</label>
                            <input type="text" class="form-control" value="<?php echo CHtml::encode($unitName ?: '—'); ?>" readonly disabled>
                        </div>

                        <div class="mb-3">
                            <label for="profile-birth-year" class="form-label fw-semibold">
                                Năm sinh <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" id="profile-birth-year" name="birth_year" required>
                                <option value="">-- Chọn năm sinh --</option>
                                <?php for ($year = $maxYear; $year >= $minYear; $year--): ?>
                                    <option value="<?php echo $year; ?>" <?php echo $year === $birthYear ? 'selected' : ''; ?>>
                                        <?php echo $year; ?>
                                    </option>
                                <?php endfor; ?>
                            </select>
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
                    <?php endif; ?>
                </div>

                <div class="modal-footer">
                    <a href="<?php echo $logoutUrl; ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-box-arrow-right me-1"></i>Đăng xuất
                    </a>
                    <?php if ($hasProfile): ?>
                        <button type="submit" class="btn btn-primary" id="btn-submit-profile">
                            <i class="bi bi-check-circle me-1"></i>
                            <?php echo $isComplete ? 'Xác nhận' : 'Lưu thông tin'; ?>
                        </button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>
