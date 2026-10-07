/**
 * Xử lý ô nhập mã PIN (6 chữ số) trên cổng đăng nhập cá nhân:
 * - Tự nhảy ô kế tiếp khi nhập, xử lý backspace / phím mũi tên / dán.
 * - Gom giá trị vào input ẩn, kiểm tra đủ 6 số trước khi submit.
 * - Nút hiện/ẩn mã PIN.
 */
(function () {
    'use strict';

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
    });
})();
