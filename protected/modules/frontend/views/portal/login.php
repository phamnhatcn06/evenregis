<?php

/**
 * Cổng đăng nhập cá nhân người tham dự - Đại hội Mường Thanh 2026 Ninh Bình.
 * Giao diện hiện đại, sang trọng, đẳng cấp sự kiện với trải nghiệm tương tác mượt mà.
 */
$this->pageTitle = 'Cổng Cá Nhân Đại Biểu';
$loginUrl = $this->createUrl('/frontend/portal/login');
$returnUrl = isset($returnUrl) ? $returnUrl : '';
$units = isset($units) && is_array($units) ? $units : array();
$propertyId = isset($propertyId) ? (string) $propertyId : '';

// Đăng ký Select2 cho dropdown chọn đơn vị (tìm kiếm nhanh)
Yii::app()->clientScript->registerCssFile(
    Yii::app()->theme->baseUrl . '/assets/vendor/select2/css/select2.min.css'
);
Yii::app()->clientScript->registerScriptFile(
    Yii::app()->theme->baseUrl . '/assets/vendor/jquery/jquery.min.js',
    CClientScript::POS_END
);
Yii::app()->clientScript->registerScriptFile(
    Yii::app()->theme->baseUrl . '/assets/vendor/select2/js/select2.min.js',
    CClientScript::POS_END
);

// Đăng ký JS cho xử lý mã PIN và QR scanner
Yii::app()->clientScript->registerScriptFile(
    Yii::app()->theme->baseUrl . '/assets/js/pages/portal-login.js',
    CClientScript::POS_END
);

if ($step === 'identify') {
    Yii::app()->clientScript->registerScriptFile(
        Yii::app()->theme->baseUrl . '/assets/vendor/html5-qrcode/html5-qrcode.min.js',
        CClientScript::POS_END
    );
    Yii::app()->clientScript->registerScriptFile(
        Yii::app()->theme->baseUrl . '/assets/js/pages/run-qr-login.js',
        CClientScript::POS_END
    );
}

// Xác định điểm đến người dùng muốn truy cập
$returnStr = is_array($returnUrl) ? (isset($returnUrl[0]) ? $returnUrl[0] : '') : (string)$returnUrl;
$isRunPortal = (strpos($returnStr, 'run') !== false);
?>

<div class="row align-items-center justify-content-center g-4 g-xl-5">
    <!-- Cột trái: Giới thiệu Đại hội & Banner chuyển tiếp tính năng (Desktop/Tablet) -->
    <div class="col-lg-6 col-xl-7">
        <div class="portal-brand-showcase">
            <!-- Logo biểu trưng chính thức Đại hội Mường Thanh Ninh Bình 2026 -->
            <div class="portal-logo-wrapper">
                <img src="<?php echo Yii::app()->theme->baseUrl; ?>/logo_daihoi.png"
                    alt="Đại hội Mường Thanh Ninh Bình 2026"
                    class="portal-logo-img">
            </div>

            <!-- Huy hiệu cổng chính thức -->
            <div class="d-block mb-2">
                <span class="portal-event-badge mb-0">
                    <span class="portal-pulse-dot"></span>
                    Cổng Thông tin đại hội Chính Thức • Ninh Bình 2026
                </span>
            </div>

            <!-- Tiêu đề & Thông điệp Đại hội (Ẩn bớt mô tả dài trên mobile để ưu tiên khung đăng nhập) -->
            <h1 class="portal-hero-title mb-2 mb-lg-3">
                Hội Tụ Bản Sắc<br>
                <span class="text-gradient">Dẫn Dắt Tương Lai</span>
            </h1>

            <p class="portal-hero-subtitle d-none d-lg-block">
                Chào mừng Quý Đại biểu tham dự ngày hội văn hóa, thể thao và tay nghề nghiệp vụ quy mô lớn nhất toàn Tập đoàn Mường Thanh.
            </p>

            <!-- Banner thông tin chuyển tiếp (Hiển thị khi đăng nhập để vào /run hoặc tính năng cụ thể) -->
            <?php if ($isRunPortal): ?>
                <div class="portal-destination-banner mb-3 mb-lg-4">
                    <div class="destination-tag">
                        <i class="bi bi-geo-alt-fill text-warning"></i> Bạn đang truy cập tính năng
                    </div>
                    <div class="destination-name">
                        <i class="bi bi-person-walking text-info me-1"></i> Đăng Ký Giải Chạy Fun Run & Tour Tham Quan
                    </div>
                    <div class="destination-chips d-none d-sm-flex">
                        <span class="destination-chip">
                            <i class="bi bi-trophy text-warning"></i> Fun Run 5km & 10km & 15km
                        </span>
                        <span class="destination-chip">
                            <i class="bi bi-compass text-info"></i> Quần thể Di sản Tràng An
                        </span>
                        <span class="destination-chip">
                            <i class="bi bi-lightning-charge text-success"></i> Nhận số BIB tức thì
                        </span>
                    </div>
                </div>
            <?php else: ?>
                <div class="portal-destination-banner mb-3 mb-lg-4 d-none d-lg-block">
                    <div class="destination-tag">
                        <i class="bi bi-shield-check text-success"></i> Xác thực tài khoản cá nhân
                    </div>
                    <div class="destination-name">
                        Cổng Tiện Ích Dành Cho Đại Biểu VCK
                    </div>
                    <div class="destination-chips">
                        <span class="destination-chip"><i class="bi bi-check2-circle text-success"></i> Đăng ký hoạt động</span>
                        <span class="destination-chip"><i class="bi bi-check2-circle text-success"></i> Lịch thi đấu thể thao</span>
                        <span class="destination-chip"><i class="bi bi-check2-circle text-success"></i> Kho hình ảnh kỷ niệm</span>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Danh mục điểm nổi bật -->
            <div class="portal-feature-list">
                <div class="portal-feature-item">
                    <div class="portal-feature-icon icon-blue">
                        <i class="bi bi-shield-lock-fill"></i>
                    </div>
                    <div class="portal-feature-text">
                        <h6>Bảo Mật Tối Đa Với Mã PIN</h6>
                        <p>Chỉ cần thiết lập mã PIN 6 chữ số một lần duy nhất cho toàn bộ kỳ Đại hội.</p>
                    </div>
                </div>

                <div class="portal-feature-item">
                    <div class="portal-feature-icon icon-green">
                        <i class="bi bi-qr-code-scan"></i>
                    </div>
                    <div class="portal-feature-text">
                        <h6>Đăng Nhập 1 Chạm Bằng QR Thẻ</h6>
                        <p>Quét mã QR trực tiếp trên thẻ đeo Đại biểu để vào cổng ngay lập tức không cần gõ phím.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Cột phải: Khung tương tác đăng nhập (Card tương tác cao cấp) -->
    <div class="col-lg-6 col-xl-5">
        <div class="portal-auth-card">
            <!-- Header của thẻ đăng nhập -->
            <div class="auth-card-header">
                <!-- Thanh tiến trình các bước -->
                <div class="auth-step-dots">
                    <div class="step-dot <?php echo ($step === 'identify') ? 'active' : 'completed'; ?>"></div>
                    <div class="step-dot <?php echo ($step !== 'identify') ? 'active' : ''; ?>"></div>
                </div>

                <!-- Avatar icon biểu trưng cho từng bước -->
                <div class="auth-avatar-pill step-<?php echo $step; ?>">
                    <?php if ($step === 'setpin'): ?>
                        <i class="bi bi-shield-lock-fill"></i>
                    <?php elseif ($step === 'login'): ?>
                        <i class="bi bi-key-fill"></i>
                    <?php else: ?>
                        <i class="bi bi-person-vcard-fill"></i>
                    <?php endif; ?>
                </div>

                <!-- Tiêu đề & mô tả -->
                <?php if ($step === 'setpin'): ?>
                    <h3 class="auth-card-title">Thiết Lập Mã PIN</h3>
                    <p class="auth-card-subtitle">Tạo mã PIN 6 chữ số bí mật để bảo vệ tài khoản Đại biểu của bạn</p>
                <?php elseif ($step === 'login'): ?>
                    <h3 class="auth-card-title">Xác Thực Mã PIN</h3>
                    <p class="auth-card-subtitle">Nhập mã PIN 6 số đã đăng ký để vào cổng cá nhân</p>
                <?php else: ?>
                    <h3 class="auth-card-title">Đăng Nhập Cổng Thông tin đại hội</h3>
                    <p class="auth-card-subtitle">Nhập mã định danh trên thẻ hoặc quét QR để tiếp tục</p>
                <?php endif; ?>
            </div>

            <!-- BƯỚC 1: NHẬP MÃ ĐỊNH DANH HOẶC QUÉT QR -->
            <?php if ($step === 'identify'): ?>
                <form method="post" action="<?php echo $loginUrl; ?>" id="form-identify">
                    <input type="hidden" name="step" value="identify">
                    <input type="hidden" name="return" value="<?php echo CHtml::encode($returnUrl); ?>">

                    <div class="mb-3">
                        <label class="auth-field-label" for="portal-identifier-input">
                            <span><i class="bi bi-person-badge text-primary me-1"></i> Mã định danh Đại biểu</span>
                            <span class="text-muted fw-normal small">Ví dụ: <strong>MT0088</strong></span>
                        </label>

                        <div class="auth-input-group">
                            <div class="auth-input-icon">
                                <i class="bi bi-upc-scan"></i>
                            </div>
                            <input type="text"
                                name="identifier"
                                id="portal-identifier-input"
                                class="auth-text-input"
                                placeholder="NHẬP MÃ ĐẠI BIỂU..."
                                value="<?php echo CHtml::encode($identifier); ?>"
                                maxlength="20"
                                autocomplete="off"
                                spellcheck="false"
                                autofocus
                                required>
                            <button type="button"
                                class="auth-input-action-btn"
                                id="btn-paste-identifier"
                                title="Dán mã từ bộ nhớ tạm">
                                <i class="bi bi-clipboard"></i> Dán
                            </button>
                        </div>

                        <div class="auth-field-hint">
                            <i class="bi bi-info-circle text-primary"></i> Mã định danh in trên thẻ đeo Đại biểu do BTC cấp.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="auth-field-label" for="portal-unit-select">
                            <span><i class="bi bi-buildings text-primary me-1"></i> Đơn vị của bạn</span>
                            <span class="text-danger fw-normal small">Bắt buộc</span>
                        </label>

                        <div class="auth-input-group">
                            <div class="auth-input-icon">
                                <i class="bi bi-diagram-3"></i>
                            </div>
                            <select name="property_id"
                                id="portal-unit-select"
                                class="auth-text-input"
                                required>
                                <option value="">-- Chọn đơn vị của bạn --</option>
                                <?php foreach ($units as $unit): ?>
                                    <option value="<?php echo CHtml::encode($unit['id']); ?>"
                                        <?php echo ((string) $unit['id'] === $propertyId) ? 'selected' : ''; ?>>
                                        <?php echo CHtml::encode($unit['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="auth-field-hint">
                            <i class="bi bi-shield-check text-success"></i> Chọn đúng đơn vị để xác thực — tránh nhập nhầm mã của người khác.
                        </div>
                    </div>

                    <button type="submit" class="btn-auth-primary">
                        <span>Tiếp tục xác thực</span>
                        <i class="bi bi-arrow-right btn-arrow-icon"></i>
                    </button>
                </form>

                <div class="auth-divider">
                    <span>hoặc đăng nhập nhanh bằng</span>
                </div>

                <!-- Nút bật máy ảnh quét QR -->
                <button type="button" id="btn-scan-qr" class="btn-auth-qr">
                    <i class="bi bi-qr-code-scan text-primary"></i> Quét mã QR trên thẻ đeo
                </button>

                <!-- Khung camera quét QR -->
                <div id="qr-scan-wrap" class="qr-scanner-card" style="display:none;">
                    <div class="qr-scanner-header">
                        <span><i class="bi bi-camera-video me-1"></i> Hướng camera vào mã QR thẻ</span>
                        <span class="badge bg-danger-subtle text-danger">LIVE</span>
                    </div>
                    <div class="qr-scanner-viewport">
                        <div id="qr-reader" style="width:100%;"></div>
                    </div>
                    <button type="button" id="btn-stop-qr" class="btn btn-outline-light btn-sm w-100 mt-2 rounded-3">
                        <i class="bi bi-x-circle me-1"></i> Đóng camera quét QR
                    </button>
                    <div class="text-white-50 small mt-1 text-center" style="font-size: 0.75rem;">
                        Vui lòng cho phép quyền truy cập camera trên thiết bị của bạn.
                    </div>
                </div>

                <!-- Form ẩn gửi mã QR đọc được về server -->
                <form method="post" action="<?php echo $loginUrl; ?>" id="qr-login-form">
                    <input type="hidden" name="step" value="qr">
                    <input type="hidden" name="return" value="<?php echo CHtml::encode($returnUrl); ?>">
                    <input type="hidden" name="qr_value" id="qr_value">
                </form>

                <!-- Thẻ hướng dẫn bổ sung -->
                <div class="auth-help-card">
                    <i class="bi bi-lightbulb-fill"></i>
                    <div>
                        <strong>Bạn chưa biết mã định danh?</strong><br>
                        Vui lòng kiểm tra trên thẻ đeo Đại biểu hoặc liên hệ Trưởng đoàn của đơn vị để được hướng dẫn.
                    </div>
                </div>

                <!-- BƯỚC 2: THIẾT LẬP MÃ PIN (CHO ĐẠI BIỂU MỚI) -->
            <?php elseif ($step === 'setpin'): ?>
                <div class="auth-user-banner banner-success">
                    <div class="user-banner-avatar">
                        <i class="bi bi-person-fill"></i>
                    </div>
                    <div class="user-banner-text">
                        <div class="user-banner-name"><?php echo CHtml::encode($fullName ?: 'Quý Đại biểu'); ?></div>
                        <div class="user-banner-sub">
                            Mã số: <strong><?php echo CHtml::encode($identifier); ?></strong> • <span class="text-success fw-semibold">Thiết lập PIN lần đầu</span>
                        </div>
                    </div>
                </div>

                <form method="post" action="<?php echo $loginUrl; ?>" id="form-setpin">
                    <input type="hidden" name="step" value="setpin">
                    <input type="hidden" name="return" value="<?php echo CHtml::encode($returnUrl); ?>">
                    <input type="hidden" name="identifier" value="<?php echo CHtml::encode($identifier); ?>">
                    <input type="hidden" name="property_id" value="<?php echo CHtml::encode($propertyId); ?>">
                    <input type="hidden" name="pin" id="pin-new" required>
                    <input type="hidden" name="pin_confirm" id="pin-confirm" required>

                    <div class="mb-3 text-center">
                        <label class="auth-field-label justify-content-center">
                            <span><i class="bi bi-lock-fill text-primary me-1"></i> Tạo mã PIN mới (6 chữ số)</span>
                        </label>
                        <div class="pin-code-group" id="group-setpin-new" data-hidden-id="pin-new">
                            <input type="password" inputmode="numeric" maxlength="1" pattern="[0-9]" class="pin-digit-box" autocomplete="one-time-code" autofocus>
                            <input type="password" inputmode="numeric" maxlength="1" pattern="[0-9]" class="pin-digit-box" autocomplete="one-time-code">
                            <input type="password" inputmode="numeric" maxlength="1" pattern="[0-9]" class="pin-digit-box" autocomplete="one-time-code">
                            <input type="password" inputmode="numeric" maxlength="1" pattern="[0-9]" class="pin-digit-box" autocomplete="one-time-code">
                            <input type="password" inputmode="numeric" maxlength="1" pattern="[0-9]" class="pin-digit-box" autocomplete="one-time-code">
                            <input type="password" inputmode="numeric" maxlength="1" pattern="[0-9]" class="pin-digit-box" autocomplete="one-time-code">
                        </div>
                        <button type="button" class="pin-toggle-btn" data-target-group="group-setpin-new">
                            <i class="bi bi-eye"></i> Hiện mã PIN
                        </button>
                    </div>

                    <div class="mb-4 text-center">
                        <label class="auth-field-label justify-content-center">
                            <span><i class="bi bi-shield-check text-success me-1"></i> Nhập lại mã PIN để xác nhận</span>
                        </label>
                        <div class="pin-code-group" id="group-setpin-confirm" data-hidden-id="pin-confirm">
                            <input type="password" inputmode="numeric" maxlength="1" pattern="[0-9]" class="pin-digit-box" autocomplete="one-time-code">
                            <input type="password" inputmode="numeric" maxlength="1" pattern="[0-9]" class="pin-digit-box" autocomplete="one-time-code">
                            <input type="password" inputmode="numeric" maxlength="1" pattern="[0-9]" class="pin-digit-box" autocomplete="one-time-code">
                            <input type="password" inputmode="numeric" maxlength="1" pattern="[0-9]" class="pin-digit-box" autocomplete="one-time-code">
                            <input type="password" inputmode="numeric" maxlength="1" pattern="[0-9]" class="pin-digit-box" autocomplete="one-time-code">
                            <input type="password" inputmode="numeric" maxlength="1" pattern="[0-9]" class="pin-digit-box" autocomplete="one-time-code">
                        </div>

                        <div class="d-flex align-items-center justify-content-center gap-2">
                            <button type="button" class="pin-toggle-btn" data-target-group="group-setpin-confirm">
                                <i class="bi bi-eye"></i> Hiện mã PIN
                            </button>
                            <span id="pin-match-indicator" class="pin-match-indicator" style="display:none;"></span>
                        </div>
                    </div>

                    <button type="submit" class="btn-auth-primary btn-auth-success mb-3">
                        <i class="bi bi-check2-circle fs-5"></i>
                        <span>Lưu Mã PIN & Đăng Nhập</span>
                    </button>

                    <div class="text-center">
                        <a href="<?php echo $loginUrl; ?>" class="small text-muted text-decoration-none">
                            <i class="bi bi-arrow-left me-1"></i> Quay lại nhập mã định danh khác
                        </a>
                    </div>
                </form>

                <!-- BƯỚC 3: NHẬP PIN ĐĂNG NHẬP (CHO ĐẠI BIỂU ĐÃ CÓ PIN) -->
            <?php else: ?>
                <div class="auth-user-banner">
                    <div class="user-banner-avatar">
                        <i class="bi bi-person-badge-fill"></i>
                    </div>
                    <div class="user-banner-text">
                        <div class="user-banner-name"><?php echo CHtml::encode($fullName ?: 'Quý Đại biểu'); ?></div>
                        <div class="user-banner-sub">
                            Mã định danh: <strong><?php echo CHtml::encode($identifier); ?></strong> • <span class="badge bg-primary-subtle text-primary border border-primary-subtle py-0 px-2">Đại biểu VCK</span>
                        </div>
                    </div>
                </div>

                <form method="post" action="<?php echo $loginUrl; ?>" id="form-login-pin">
                    <input type="hidden" name="step" value="login">
                    <input type="hidden" name="return" value="<?php echo CHtml::encode($returnUrl); ?>">
                    <input type="hidden" name="identifier" value="<?php echo CHtml::encode($identifier); ?>">
                    <input type="hidden" name="property_id" value="<?php echo CHtml::encode($propertyId); ?>">
                    <input type="hidden" name="pin" id="pin-login" required>

                    <div class="mb-4 text-center">
                        <label class="auth-field-label justify-content-center">
                            <span><i class="bi bi-lock-fill text-primary me-1"></i> Nhập mã PIN (6 chữ số)</span>
                        </label>

                        <div class="pin-code-group" id="group-login" data-hidden-id="pin-login">
                            <input type="password" inputmode="numeric" maxlength="1" pattern="[0-9]" class="pin-digit-box" autocomplete="one-time-code" autofocus>
                            <input type="password" inputmode="numeric" maxlength="1" pattern="[0-9]" class="pin-digit-box" autocomplete="one-time-code">
                            <input type="password" inputmode="numeric" maxlength="1" pattern="[0-9]" class="pin-digit-box" autocomplete="one-time-code">
                            <input type="password" inputmode="numeric" maxlength="1" pattern="[0-9]" class="pin-digit-box" autocomplete="one-time-code">
                            <input type="password" inputmode="numeric" maxlength="1" pattern="[0-9]" class="pin-digit-box" autocomplete="one-time-code">
                            <input type="password" inputmode="numeric" maxlength="1" pattern="[0-9]" class="pin-digit-box" autocomplete="one-time-code">
                        </div>

                        <button type="button" class="pin-toggle-btn" data-target-group="group-login">
                            <i class="bi bi-eye"></i> Hiện mã PIN
                        </button>
                    </div>

                    <button type="submit" class="btn-auth-primary mb-3">
                        <i class="bi bi-box-arrow-in-right fs-5"></i>
                        <span>Đăng Nhập Cổng Thông tin đại hội</span>
                    </button>

                    <div class="text-center">
                        <a href="<?php echo $loginUrl; ?>" class="small text-muted text-decoration-none">
                            <i class="bi bi-arrow-left me-1"></i> Đổi tài khoản / Nhập định danh khác
                        </a>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>