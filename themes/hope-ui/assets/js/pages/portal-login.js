/**
 * Xử lý ô nhập mã PIN (6 chữ số) và tương tác form trên cổng đăng nhập cá nhân:
 * - Tự nhảy ô kế tiếp khi nhập, xử lý backspace / phím mũi tên / dán.
 * - Gom giá trị vào input ẩn, kiểm tra đủ 6 số trước khi submit.
 * - Nút hiện/ẩn mã PIN.
 * - Tự động viết hoa & nút dán nhanh cho mã định danh.
 * - So khớp thời gian thực cho mã PIN xác nhận (bước setpin).
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        // -------------------------------------------------------------
        // 1. Tự động viết hoa và hỗ trợ dán nhanh cho ô mã định danh
        // -------------------------------------------------------------
        var identifierInput = document.getElementById('portal-identifier-input');
        if (identifierInput) {
            identifierInput.addEventListener('input', function () {
                var cursor = this.selectionStart;
                this.value = this.value.toUpperCase().replace(/\s+/g, '');
                this.setSelectionRange(cursor, cursor);
            });

            var pasteBtn = document.getElementById('btn-paste-identifier');
            if (pasteBtn && navigator.clipboard && navigator.clipboard.readText) {
                pasteBtn.addEventListener('click', function () {
                    navigator.clipboard.readText().then(function (clipText) {
                        if (clipText) {
                            identifierInput.value = clipText.trim().toUpperCase().replace(/\s+/g, '');
                            identifierInput.focus();
                            if (typeof Toast !== 'undefined') {
                                Toast.info('Đã dán mã định danh từ bộ nhớ tạm.');
                            }
                        }
                    }).catch(function () {
                        identifierInput.focus();
                    });
                });
            }
        }

        // -------------------------------------------------------------
        // 2. Xử lý nhóm ô nhập mã PIN 6 số
        // -------------------------------------------------------------
        var pinGroups = document.querySelectorAll('.pin-code-group');
        var pinNewInput = document.getElementById('pin-new');
        var pinConfirmInput = document.getElementById('pin-confirm');
        var matchIndicator = document.getElementById('pin-match-indicator');

        function checkPinMatch() {
            if (!pinNewInput || !pinConfirmInput || !matchIndicator) return;
            var val1 = pinNewInput.value;
            var val2 = pinConfirmInput.value;

            if (val1.length === 6 && val2.length === 6) {
                if (val1 === val2) {
                    matchIndicator.className = 'pin-match-indicator match';
                    matchIndicator.innerHTML = '<i class="bi bi-check-circle-fill"></i> Mã PIN trùng khớp';
                } else {
                    matchIndicator.className = 'pin-match-indicator mismatch';
                    matchIndicator.innerHTML = '<i class="bi bi-x-circle-fill"></i> Mã PIN chưa khớp';
                }
                matchIndicator.style.display = 'inline-flex';
            } else if (val2.length > 0) {
                matchIndicator.className = 'pin-match-indicator mismatch';
                matchIndicator.innerHTML = '<i class="bi bi-dash-circle"></i> Chưa đủ 6 số';
                matchIndicator.style.display = 'inline-flex';
            } else {
                matchIndicator.style.display = 'none';
            }
        }

        if (pinGroups.length) {
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
                    checkPinMatch();
                    return val;
                }

                inputs.forEach(function (input, idx) {
                    input.addEventListener('input', function () {
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

                        // Kiểm tra xác nhận mã PIN trùng nhau ở bước setpin
                        if (pinNewInput && pinConfirmInput) {
                            if (pinNewInput.value !== pinConfirmInput.value) {
                                e.preventDefault();
                                if (typeof Toast !== 'undefined') {
                                    Toast.error('Xác nhận mã PIN không trùng khớp.');
                                } else {
                                    alert('Xác nhận mã PIN không trùng khớp.');
                                }
                                if (pinConfirmInput) {
                                    var grp2 = form.querySelector('[data-hidden-id="pin-confirm"]');
                                    if (grp2) {
                                        var b = grp2.querySelector('.pin-digit-box');
                                        if (b) b.focus();
                                    }
                                }
                                return false;
                            }
                        }

                        // Hiển thị trạng thái đang xử lý trên nút submit
                        var submitBtn = form.querySelector('button[type="submit"]');
                        if (submitBtn) {
                            submitBtn.disabled = true;
                            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Đang xác thực...';
                        }
                    });
                }
            });

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
        }
    });
})();
