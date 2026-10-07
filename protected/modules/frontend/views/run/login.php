<?php
$this->pageTitle = 'Đăng ký hoạt động - Đăng nhập';
$loginUrl = $this->createUrl('/frontend/run/login');

Yii::app()->clientScript->registerCssFile(
    Yii::app()->theme->baseUrl . '/assets/vendor/bootstrap-icons/bootstrap-icons.css'
);
Yii::app()->clientScript->registerCssFile(
    Yii::app()->theme->baseUrl . '/assets/css/pages/run-portal.css'
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
?>
<div class="row justify-content-center mt-3 mt-md-4">
    <div class="col-lg-5 col-md-7 col-sm-10">
        <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <div class="user-avatar-circle mx-auto mb-3" style="width: 56px; height: 56px; font-size: 1.6rem;">
                        <?php if ($step === 'setpin'): ?>
                            <i class="bi bi-shield-lock-fill"></i>
                        <?php elseif ($step === 'login'): ?>
                            <i class="bi bi-key-fill"></i>
                        <?php else: ?>
                            <i class="bi bi-person-badge-fill"></i>
                        <?php endif; ?>
                    </div>
                    <h4 class="fw-bold mb-1 text-dark">Cổng Đăng Ký Đại Hội</h4>
                    <p class="text-muted small mb-0">Dành cho đại biểu tham dự Vòng Chung Kết</p>
                </div>

                <?php if ($step === 'identify'): ?>
                    <form method="post" action="<?php echo $loginUrl; ?>">
                        <input type="hidden" name="step" value="identify">
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark small mb-1">
                                <i class="bi bi-person-vcard text-primary me-1"></i> Định danh đăng nhập
                            </label>
                            <input type="text" name="identifier" class="form-control form-control-lg text-center fw-bold rounded-3"
                                   placeholder="VD: DHMT123456" value="<?php echo CHtml::encode($identifier); ?>"
                                   style="letter-spacing: 0.05em;" autofocus required>
                            <div class="form-text text-muted small mt-1">
                                Nhập mã định danh được Ban tổ chức cung cấp (bắt đầu bằng DHMT).
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold rounded-3 shadow-sm">
                            Tiếp tục <i class="bi bi-arrow-right ms-1"></i>
                        </button>
                    </form>

                    <div class="text-center my-3 text-muted small">— hoặc —</div>

                    <button type="button" id="btn-scan-qr" class="btn btn-outline-primary w-100 py-2 rounded-3">
                        <i class="bi bi-qr-code-scan me-1"></i> Quét QR thẻ tham dự
                    </button>

                    <!-- Khung camera quét QR (ẩn đến khi bấm) -->
                    <div id="qr-scan-wrap" class="mt-3" style="display:none;">
                        <div id="qr-reader" style="width:100%;"></div>
                        <button type="button" id="btn-stop-qr" class="btn btn-outline-secondary btn-sm w-100 mt-2 rounded-3">Đóng camera</button>
                        <div class="form-text text-muted small mt-1 text-center">Đưa mã QR trên thẻ vào khung hình. Cần cho phép quyền camera.</div>
                    </div>

                    <!-- Form ẩn gửi giá trị QR về server -->
                    <form method="post" action="<?php echo $loginUrl; ?>" id="qr-login-form">
                        <input type="hidden" name="step" value="qr">
                        <input type="hidden" name="qr_value" id="qr_value">
                    </form>

                <?php elseif ($step === 'setpin'): ?>
                    <div class="alert alert-info py-2 px-3 small d-flex align-items-center mb-4 rounded-3 border-0 bg-info-subtle text-dark">
                        <i class="bi bi-info-circle-fill text-info fs-5 me-2 flex-shrink-0"></i>
                        <div>
                            Xin chào <strong><?php echo CHtml::encode($fullName); ?></strong>. Vui lòng tạo mã PIN 6 số để bảo mật tài khoản.
                        </div>
                    </div>

                    <form method="post" action="<?php echo $loginUrl; ?>" id="form-setpin">
                        <input type="hidden" name="step" value="setpin">
                        <input type="hidden" name="identifier" value="<?php echo CHtml::encode($identifier); ?>">
                        <input type="hidden" name="pin" id="pin-new" required>
                        <input type="hidden" name="pin_confirm" id="pin-confirm" required>

                        <div class="mb-3 text-center">
                            <label class="form-label fw-bold text-dark small mb-1">
                                <i class="bi bi-lock-fill text-primary me-1"></i> Mã PIN mới (6 chữ số)
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
                            <label class="form-label fw-bold text-dark small mb-1">
                                <i class="bi bi-shield-check text-success me-1"></i> Nhập lại mã PIN
                            </label>
                            <div class="pin-code-group" id="group-setpin-confirm" data-hidden-id="pin-confirm">
                                <input type="password" inputmode="numeric" maxlength="1" pattern="[0-9]" class="pin-digit-box" autocomplete="one-time-code">
                                <input type="password" inputmode="numeric" maxlength="1" pattern="[0-9]" class="pin-digit-box" autocomplete="one-time-code">
                                <input type="password" inputmode="numeric" maxlength="1" pattern="[0-9]" class="pin-digit-box" autocomplete="one-time-code">
                                <input type="password" inputmode="numeric" maxlength="1" pattern="[0-9]" class="pin-digit-box" autocomplete="one-time-code">
                                <input type="password" inputmode="numeric" maxlength="1" pattern="[0-9]" class="pin-digit-box" autocomplete="one-time-code">
                                <input type="password" inputmode="numeric" maxlength="1" pattern="[0-9]" class="pin-digit-box" autocomplete="one-time-code">
                            </div>
                            <button type="button" class="pin-toggle-btn" data-target-group="group-setpin-confirm">
                                <i class="bi bi-eye"></i> Hiện mã PIN
                            </button>
                        </div>

                        <button type="submit" class="btn btn-success w-100 py-2 fw-semibold rounded-3 shadow-sm">
                            <i class="bi bi-check-circle me-1"></i> Đặt mã PIN & Đăng nhập
                        </button>
                    </form>

                <?php else: ?>
                    <?php if ($fullName): ?>
                        <div class="alert alert-info py-2 px-3 small d-flex align-items-center mb-4 rounded-3 border-0 bg-info-subtle text-dark">
                            <i class="bi bi-person-check-fill text-primary fs-5 me-2 flex-shrink-0"></i>
                            <div>
                                Xin chào <strong><?php echo CHtml::encode($fullName); ?></strong>. Vui lòng nhập mã PIN để vào cổng.
                            </div>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="<?php echo $loginUrl; ?>" id="form-login-pin">
                        <input type="hidden" name="step" value="login">
                        <input type="hidden" name="identifier" value="<?php echo CHtml::encode($identifier); ?>">
                        <input type="hidden" name="pin" id="pin-login" required>

                        <div class="mb-4 text-center">
                            <label class="form-label fw-bold text-dark small mb-1">
                                <i class="bi bi-lock-fill text-primary me-1"></i> Mã PIN (6 chữ số)
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

                        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold rounded-3 shadow-sm">
                            <i class="bi bi-box-arrow-in-right me-1"></i> Đăng nhập
                        </button>

                        <div class="text-center mt-3">
                            <a href="<?php echo $loginUrl; ?>" class="small text-muted text-decoration-none">
                                <i class="bi bi-arrow-left me-1"></i> Nhập lại định danh khác
                            </a>
                        </div>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var pinGroups = document.querySelectorAll('.pin-code-group');
    if (!pinGroups.length) return;

    pinGroups.forEach(function (group) {
        var hiddenInput = document.getElementById(group.getAttribute('data-hidden-id'));
        var inputs = Array.prototype.slice.call(group.querySelectorAll('.pin-digit-box'));

        function syncValue() {
            var val = inputs.map(function (inp) { return inp.value; }).join('');
            if (hiddenInput) {
                hiddenInput.value = val;
            }
            inputs.forEach(function (inp) {
                if (inp.value) {
                    inp.classList.add('is-filled');
                } else {
                    inp.classList.remove('is-filled');
                }
            });
            return val;
        }

        inputs.forEach(function (input, idx) {
            input.addEventListener('input', function (e) {
                var val = input.value.replace(/\D/g, '');
                input.value = val ? val[val.length - 1] : '';
                syncValue();

                if (input.value && idx < inputs.length - 1) {
                    inputs[idx + 1].focus();
                    inputs[idx + 1].select();
                }
            });

            input.addEventListener('keydown', function (e) {
                if (e.key === 'Backspace') {
                    if (!input.value && idx > 0) {
                        inputs[idx - 1].value = '';
                        inputs[idx - 1].focus();
                        syncValue();
                        e.preventDefault();
                    } else if (input.value) {
                        input.value = '';
                        syncValue();
                        e.preventDefault();
                    }
                } else if (e.key === 'ArrowLeft' && idx > 0) {
                    inputs[idx - 1].focus();
                    inputs[idx - 1].select();
                } else if (e.key === 'ArrowRight' && idx < inputs.length - 1) {
                    inputs[idx + 1].focus();
                    inputs[idx + 1].select();
                }
            });

            input.addEventListener('paste', function (e) {
                e.preventDefault();
                var paste = (e.clipboardData || window.clipboardData).getData('text');
                var digits = paste.replace(/\D/g, '').split('').slice(0, inputs.length);
                if (digits.length) {
                    digits.forEach(function (d, i) {
                        if (inputs[i]) inputs[i].value = d;
                    });
                    syncValue();
                    var nextIdx = Math.min(digits.length, inputs.length - 1);
                    inputs[nextIdx].focus();
                }
            });

            input.addEventListener('focus', function () {
                input.select();
            });
        });

        // Form validation
        var form = group.closest('form');
        if (form && !form.dataset.pinBound) {
            form.dataset.pinBound = 'true';
            form.addEventListener('submit', function (e) {
                var hiddenInputs = form.querySelectorAll('input[type="hidden"][id^="pin-"]');
                for (var h = 0; h < hiddenInputs.length; h++) {
                    var hInp = hiddenInputs[h];
                    if (hInp.value.length < 6) {
                        e.preventDefault();
                        var grp = form.querySelector('[data-hidden-id="' + hInp.id + '"]');
                        if (grp) {
                            var emptyBox = grp.querySelector('.pin-digit-box:not(.is-filled)') || grp.querySelector('.pin-digit-box');
                            if (emptyBox) emptyBox.focus();
                        }
                        if (typeof Toast !== 'undefined') {
                            Toast.warning('Vui lòng nhập đủ 6 chữ số mã PIN.');
                        } else {
                            alert('Vui lòng nhập đủ 6 chữ số mã PIN.');
                        }
                        return false;
                    }
                }
            });
        }
    });

    // Toggle Hiện / Ẩn mã PIN
    var toggleBtns = document.querySelectorAll('.pin-toggle-btn');
    toggleBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            var targetGroupId = btn.getAttribute('data-target-group');
            var group = document.getElementById(targetGroupId);
            if (!group) return;
            var inputs = group.querySelectorAll('.pin-digit-box');
            var isPass = inputs[0] && inputs[0].type === 'password';
            inputs.forEach(function (inp) {
                inp.type = isPass ? 'text' : 'password';
            });
            btn.innerHTML = isPass
                ? '<i class="bi bi-eye-slash"></i> Ẩn mã PIN'
                : '<i class="bi bi-eye"></i> Hiện mã PIN';
        });
    });
});
</script>
