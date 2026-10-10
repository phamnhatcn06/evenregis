/**
 * Màn Tổng hợp VCK — Gán người vào nội dung Fun Run / Tham quan (admin).
 *
 * Mở popup xác nhận năm sinh + giới tính, chọn cự ly chạy và/hoặc đợt tham quan rồi gửi
 * lên controller. Tách riêng khỏi file admin chính để giữ mỗi file gọn theo từng chức năng.
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var config = document.getElementById('final-attendee-roster-config');
        var modalElement = document.getElementById('modal_assign_activity');
        if (!config || !modalElement) {
            return;
        }

        var assignUrl = config.getAttribute('data-assign-activity-url');
        bindOpenButtons(modalElement);
        bindSubmit(modalElement, assignUrl);
    });

    /** Gắn sự kiện mở popup cho từng nút gán trên mỗi dòng. */
    function bindOpenButtons(modalElement) {
        document.querySelectorAll('.js-assign-activity').forEach(function (button) {
            button.addEventListener('click', function () {
                fillModal(button);
                new bootstrap.Modal(modalElement).show();
            });
        });
    }

    /** Đổ dữ liệu người được gán vào popup + prefill năm sinh / giới tính nếu đã biết. */
    function fillModal(button) {
        setText('assign_target_name', button.getAttribute('data-full-name') || '-');
        setText('assign_target_unit', button.getAttribute('data-unit') || '-');
        setValue('assign_id', button.getAttribute('data-roster-id') || '');

        // Prefill năm sinh (nếu dòng đã có).
        var birthYear = button.getAttribute('data-birth-year') || '';
        var birthSelect = document.getElementById('assign_birth_year');
        if (birthSelect) {
            birthSelect.value = birthYear;
            if (birthSelect.value !== birthYear) {
                birthSelect.value = '';
            }
        }

        // Prefill giới tính (1 = Nam, 0 = Nữ).
        var gender = button.getAttribute('data-gender');
        var male = document.getElementById('assign_gender_male');
        var female = document.getElementById('assign_gender_female');
        if (male) { male.checked = (gender === '1'); }
        if (female) { female.checked = (gender === '0'); }

        // Reset lựa chọn nội dung về "không gán".
        resetRadioGroup('assign_run_event_id');
        resetRadioGroup('assign_tour_session_id');
    }

    /** Gắn submit cho nút gán. */
    function bindSubmit(modalElement, assignUrl) {
        var button = document.getElementById('btn_submit_assign');
        if (!button) {
            return;
        }

        button.addEventListener('click', function () {
            var id = getValue('assign_id');
            var birthYear = getValue('assign_birth_year');
            var gender = getCheckedValue('assign_gender');
            var runEventId = getCheckedValue('assign_run_event_id');
            var tourSessionId = getCheckedValue('assign_tour_session_id');

            if (!birthYear || gender === null) {
                toastError('Vui lòng xác nhận năm sinh và giới tính.');
                return;
            }
            if (!runEventId && !tourSessionId) {
                toastError('Vui lòng chọn ít nhất một nội dung (Fun Run hoặc Tham quan).');
                return;
            }

            var body = new FormData();
            body.append('id', id);
            body.append('birth_year', birthYear);
            body.append('gender', gender);
            if (runEventId) { body.append('run_event_id', runEventId); }
            if (tourSessionId) { body.append('tour_session_id', tourSessionId); }

            postWithButton(assignUrl, body, button, function (data) {
                var modal = bootstrap.Modal.getInstance(modalElement);
                if (modal) { modal.hide(); }
                if (typeof Toast !== 'undefined') {
                    Toast.success(data.message || 'Đã gán nội dung.');
                }
                window.setTimeout(function () { window.location.reload(); }, 900);
            });
        });
    }

    function postWithButton(url, body, button, onSuccess) {
        var originalHtml = button ? button.innerHTML : null;
        if (button) {
            button.disabled = true;
            button.innerHTML = '<i class="fa fa-spinner fa-spin me-1"></i>Đang xử lý...';
        }

        fetch(url, {
            method: 'POST',
            body: body,
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        })
            .then(function (response) {
                return response.json().then(function (data) {
                    return { ok: response.ok, data: data };
                });
            })
            .then(function (result) {
                if (button) {
                    button.disabled = false;
                    button.innerHTML = originalHtml;
                }
                if (!result.ok || !result.data.success) {
                    toastError(result.data.message || 'Không thể gán nội dung.');
                    return;
                }
                onSuccess(result.data);
            })
            .catch(function () {
                if (button) {
                    button.disabled = false;
                    button.innerHTML = originalHtml;
                }
                toastError('Lỗi kết nối máy chủ.');
            });
    }

    function resetRadioGroup(name) {
        var inputs = document.querySelectorAll('input[name="' + name + '"]');
        inputs.forEach(function (input) {
            input.checked = (input.value === '');
        });
    }

    function getCheckedValue(name) {
        var checked = document.querySelector('input[name="' + name + '"]:checked');
        if (!checked) {
            return (name === 'assign_gender') ? null : '';
        }
        return checked.value;
    }

    function getValue(id) {
        var el = document.getElementById(id);
        return el ? el.value : '';
    }

    function setValue(id, value) {
        var el = document.getElementById(id);
        if (el) { el.value = value; }
    }

    function setText(id, value) {
        var el = document.getElementById(id);
        if (el) { el.textContent = value; }
    }

    function toastError(message) {
        if (typeof Toast !== 'undefined') {
            Toast.error(message);
        } else {
            window.alert(message);
        }
    }
})();
