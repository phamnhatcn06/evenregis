<?php
$this->pageTitle = 'Đăng ký chạy - Đăng nhập';
$loginUrl = $this->createUrl('/frontend/run/login');

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
?>
<div class="row justify-content-center mt-4">
    <div class="col-md-5 col-sm-8">
        <div class="card shadow-sm">
            <div class="card-body p-4">
                <h4 class="text-center mb-1">Cổng đăng ký Bộ môn Chạy</h4>
                <p class="text-center text-muted mb-4">Dành cho người tham dự Vòng chung kết</p>

                <?php if ($step === 'identify'): ?>
                    <form method="post" action="<?php echo $loginUrl; ?>">
                        <input type="hidden" name="step" value="identify">
                        <div class="mb-3">
                            <label class="form-label">Định danh đăng nhập</label>
                            <input type="text" name="identifier" class="form-control form-control-lg text-center"
                                   placeholder="VD: DHMT123456" value="<?php echo CHtml::encode($identifier); ?>"
                                   autofocus required>
                            <div class="form-text">Nhập mã định danh được Ban tổ chức cung cấp (bắt đầu bằng DHMT).</div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Tiếp tục</button>
                    </form>

                    <div class="text-center my-3 text-muted">— hoặc —</div>

                    <button type="button" id="btn-scan-qr" class="btn btn-outline-primary w-100">
                        <i class="fa fa-qrcode me-1"></i> Quét QR thẻ tham dự
                    </button>

                    <!-- Khung camera quét QR (ẩn đến khi bấm) -->
                    <div id="qr-scan-wrap" class="mt-3" style="display:none;">
                        <div id="qr-reader" style="width:100%;"></div>
                        <button type="button" id="btn-stop-qr" class="btn btn-outline-secondary btn-sm w-100 mt-2">Đóng camera</button>
                        <div class="form-text">Đưa mã QR trên thẻ vào khung hình. Cần cho phép quyền camera.</div>
                    </div>

                    <!-- Form ẩn gửi giá trị QR về server -->
                    <form method="post" action="<?php echo $loginUrl; ?>" id="qr-login-form">
                        <input type="hidden" name="step" value="qr">
                        <input type="hidden" name="qr_value" id="qr_value">
                    </form>

                <?php elseif ($step === 'setpin'): ?>
                    <div class="alert alert-info py-2">Xin chào <strong><?php echo CHtml::encode($fullName); ?></strong>. Vui lòng tạo mã PIN 6 số để đăng nhập.</div>
                    <form method="post" action="<?php echo $loginUrl; ?>">
                        <input type="hidden" name="step" value="setpin">
                        <input type="hidden" name="identifier" value="<?php echo CHtml::encode($identifier); ?>">
                        <div class="mb-3">
                            <label class="form-label">Mã PIN mới (6 số)</label>
                            <input type="password" name="pin" class="form-control text-center" inputmode="numeric"
                                   pattern="\d{6}" maxlength="6" placeholder="••••••" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nhập lại mã PIN</label>
                            <input type="password" name="pin_confirm" class="form-control text-center" inputmode="numeric"
                                   pattern="\d{6}" maxlength="6" placeholder="••••••" required>
                        </div>
                        <button type="submit" class="btn btn-success w-100">Đặt mã PIN & Đăng nhập</button>
                    </form>

                <?php else: ?>
                    <?php if ($fullName): ?>
                        <div class="alert alert-info py-2">Xin chào <strong><?php echo CHtml::encode($fullName); ?></strong>. Nhập mã PIN để đăng nhập.</div>
                    <?php endif; ?>
                    <form method="post" action="<?php echo $loginUrl; ?>">
                        <input type="hidden" name="step" value="login">
                        <input type="hidden" name="identifier" value="<?php echo CHtml::encode($identifier); ?>">
                        <div class="mb-3">
                            <label class="form-label">Mã PIN</label>
                            <input type="password" name="pin" class="form-control form-control-lg text-center" inputmode="numeric"
                                   pattern="\d{6}" maxlength="6" placeholder="••••••" autofocus required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Đăng nhập</button>
                        <div class="text-center mt-2">
                            <a href="<?php echo $loginUrl; ?>" class="small text-muted">Nhập lại định danh khác</a>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
