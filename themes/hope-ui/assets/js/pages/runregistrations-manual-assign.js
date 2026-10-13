/**
 * Màn Đăng ký chạy (admin) — Gán thủ công người vào cự ly với mã BIB tự điền.
 *
 * Luồng: tìm & chọn người (AJAX) -> prefill năm sinh/giới tính -> chọn cự ly -> nhập BIB
 * -> gửi lên controller (backend kiểm tra trùng BIB). Tách riêng theo từng chức năng.
 */
(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        var config = document.getElementById('run-manual-assign-config');
        var modalElement = document.getElementById('modal_manual_assign');
        if (!config || !modalElement) {
            return;
        }

        var searchUrl = config.getAttribute('data-search-url');
        var assignUrl = config.getAttribute('data-assign-url');
        var checkUrl = config.getAttribute('data-check-url');
        var changeUrl = config.getAttribute('data-change-url');

        var openButton = document.getElementById('btn_open_manual_assign');
        if (openButton) {
            openButton.addEventListener('click', function () {
                resetForm();
                setMode('assign');
                new bootstrap.Modal(modalElement).show();
            });
        }

        // Nút "Đổi nội dung" trên từng dòng -> mở popup ở chế độ đổi.
        document.querySelectorAll('.js-change-content').forEach(function (btn) {
            btn.addEventListener('click', function () {
                resetForm();
                fillChangeMode(btn);
                new bootstrap.Modal(modalElement).show();
            });
        });

        bindSearch(searchUrl, checkUrl);
        bindSubmit(modalElement, assignUrl, changeUrl);
        bindGuestToggle();

        // Admin tự chọn năm sinh -> ẩn note cảnh báo thiếu dữ liệu.
        var birthSelect = document.getElementById('manual_birth_year');
        var birthNote = document.getElementById('manual_birth_year_note');
        if (birthSelect && birthNote) {
            birthSelect.addEventListener('change', function () {
                birthNote.style.display = birthSelect.value ? 'none' : '';
            });
        }
    });

    /** Bật/tắt chế độ "Khách ngoài": ẩn ô tìm người, hiện ô nhập họ tên khách. */
    function bindGuestToggle() {
        var checkbox = document.getElementById('manual_is_guest');
        if (!checkbox) {
            return;
        }
        checkbox.addEventListener('change', function () {
            applyGuestMode(checkbox.checked);
        });
    }

    /** Áp dụng trạng thái khách ngoài cho form (ẩn/hiện các khối liên quan). */
    function applyGuestMode(isGuest) {
        var searchGroup = document.getElementById('manual_search_group');
        var guestNameGroup = document.getElementById('manual_guest_name_group');
        var selectedBox = document.getElementById('manual_selected_box');
        var results = document.getElementById('manual_search_results');

        if (searchGroup) { searchGroup.style.display = isGuest ? 'none' : ''; }
        if (guestNameGroup) { guestNameGroup.style.display = isGuest ? '' : 'none'; }
        if (selectedBox) { selectedBox.style.display = 'none'; }
        if (results) { results.style.display = 'none'; results.innerHTML = ''; }

        // Chuyển sang khách ngoài: không gán vào finalist nào, mở khóa ô BIB/nút gán.
        setValue('manual_attendee_id', '');
        if (isGuest) {
            lockForExisting(false);
        } else {
            setValue('manual_guest_name', '');
        }
    }

    /** Tìm người theo từ khoá (debounce) rồi đổ kết quả xuống danh sách chọn. */
    function bindSearch(searchUrl, checkUrl) {
        var input = document.getElementById('manual_search');
        var box = document.getElementById('manual_search_results');
        if (!input || !box) {
            return;
        }

        var timer = null;
        input.addEventListener('input', function () {
            var keyword = input.value.trim();
            window.clearTimeout(timer);
            if (keyword.length < 2) {
                hide(box);
                return;
            }
            timer = window.setTimeout(function () {
                fetchPeople(searchUrl, keyword, box, checkUrl);
            }, 300);
        });

        // Click ra ngoài -> ẩn danh sách.
        document.addEventListener('click', function (e) {
            if (e.target !== input && !box.contains(e.target)) {
                hide(box);
            }
        });
    }

    function fetchPeople(searchUrl, keyword, box, checkUrl) {
        var url = searchUrl + (searchUrl.indexOf('?') >= 0 ? '&' : '?') + 'keyword=' + encodeURIComponent(keyword);
        fetch(url, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                var items = (data && data.data) || [];
                renderResults(box, items, checkUrl);
            })
            .catch(function () { hide(box); });
    }

    function renderResults(box, items, checkUrl) {
        box.innerHTML = '';
        if (!items.length) {
            var empty = document.createElement('div');
            empty.className = 'list-group-item text-muted small';
            empty.textContent = 'Không tìm thấy người phù hợp.';
            box.appendChild(empty);
            show(box);
            return;
        }
        items.forEach(function (p) {
            var item = document.createElement('button');
            item.type = 'button';
            item.className = 'list-group-item list-group-item-action';
            var lucky = p.lucky_number ? ' · Mã ' + p.lucky_number : '';
            item.innerHTML = '<span class="fw-semibold">' + escapeHtml(p.full_name) + '</span>'
                + '<span class="text-muted small">' + escapeHtml(p.unit ? ' — ' + p.unit : '') + lucky + '</span>';
            item.addEventListener('click', function () {
                selectPerson(p);
                hide(box);
                checkExistingRegistration(checkUrl, p.attendee_id);
            });
            box.appendChild(item);
        });
        show(box);
    }

    /** Chọn một người: lưu attendee_id + hiển thị thông tin + prefill năm sinh/giới tính. */
    function selectPerson(p) {
        setValue('manual_attendee_id', p.attendee_id);
        setText('manual_target_name', p.full_name || '-');
        setText('manual_target_unit', p.unit || '-');
        document.getElementById('manual_selected_box').style.display = '';
        document.getElementById('manual_search').value = p.full_name || '';

        // Năm sinh: tự lấy theo attendee; nếu không có dữ liệu thì hiện note đỏ + để admin tự chọn.
        var birthSelect = document.getElementById('manual_birth_year');
        var birthNote = document.getElementById('manual_birth_year_note');
        if (birthSelect) {
            var by = p.birth_year ? String(p.birth_year) : '';
            birthSelect.value = by;
            if (birthSelect.value !== by) {
                birthSelect.value = '';
            }
            if (birthNote) {
                birthNote.style.display = birthSelect.value ? 'none' : '';
            }
        }

        var gender = (p.gender === 0 || p.gender === 1 || p.gender === '0' || p.gender === '1') ? String(p.gender) : '';
        var male = document.getElementById('manual_gender_male');
        var female = document.getElementById('manual_gender_female');
        if (male) { male.checked = (gender === '1'); }
        if (female) { female.checked = (gender === '0'); }
    }

    /**
     * Kiểm tra người này đã có đăng ký chạy chưa (đã có BIB). Nếu rồi -> hiện cảnh báo,
     * khóa ô nhập BIB + nút gán để tránh cấp lần 2.
     */
    function checkExistingRegistration(checkUrl, attendeeId) {
        lockForExisting(false); // mặc định mở khóa trong lúc chờ.
        if (!checkUrl || !attendeeId) {
            return;
        }
        var url = checkUrl + (checkUrl.indexOf('?') >= 0 ? '&' : '?') + 'attendee_id=' + encodeURIComponent(attendeeId);
        fetch(url, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin'
        })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data && data.registered) {
                    var detail = data.bib_number || '';
                    if (data.run_event_name) {
                        detail = data.run_event_name + ' — BIB ' + data.bib_number;
                    }
                    setText('manual_existing_detail', detail);
                    lockForExisting(true);
                } else {
                    lockForExisting(false);
                }
            })
            .catch(function () { lockForExisting(false); });
    }

    /** Bật/tắt trạng thái khóa khi người được chọn đã có đăng ký. */
    function lockForExisting(locked) {
        var warning = document.getElementById('manual_existing_warning');
        var bib = document.getElementById('manual_bib');
        var submit = document.getElementById('btn_submit_manual_assign');
        if (warning) { warning.style.display = locked ? '' : 'none'; }
        if (bib) {
            bib.disabled = locked;
            if (locked) { bib.value = ''; }
        }
        if (submit) { submit.disabled = locked; }
    }

    function bindSubmit(modalElement, assignUrl, changeUrl) {
        var button = document.getElementById('btn_submit_manual_assign');
        if (!button) {
            return;
        }

        button.addEventListener('click', function () {
            var mode = getValue('manual_mode') || 'assign';
            var birthYear = getValue('manual_birth_year');
            var gender = getCheckedValue('manual_gender');
            var runEventId = getCheckedValue('manual_run_event_id');
            var bib = getValue('manual_bib').trim();

            if (!birthYear || gender === null) {
                toastError('Vui lòng chọn năm sinh và giới tính.');
                return;
            }
            if (!runEventId) {
                toastError('Vui lòng chọn cự ly.');
                return;
            }
            // Mã BIB tùy chọn: để trống thì server tự cấp theo quy luật.

            var body = new FormData();
            body.append('birth_year', birthYear);
            body.append('gender', gender);
            body.append('run_event_id', runEventId);
            body.append('bib_number', bib);

            var url;
            if (mode === 'change') {
                var registrationId = getValue('manual_registration_id');
                if (!registrationId) {
                    toastError('Thiếu đăng ký cần đổi.');
                    return;
                }
                body.append('registration_id', registrationId);
                url = changeUrl;
            } else {
                var guestCheckbox = document.getElementById('manual_is_guest');
                var isGuest = guestCheckbox && guestCheckbox.checked;
                if (isGuest) {
                    var guestName = getValue('manual_guest_name').trim();
                    if (!guestName) {
                        toastError('Vui lòng nhập họ tên khách.');
                        return;
                    }
                    body.append('is_guest', '1');
                    body.append('full_name', guestName);
                } else {
                    var attendeeId = getValue('manual_attendee_id');
                    if (!attendeeId) {
                        toastError('Vui lòng chọn người cần gán.');
                        return;
                    }
                    body.append('attendee_id', attendeeId);
                }
                url = assignUrl;
            }

            postWithButton(url, body, button, function (data) {
                var modal = bootstrap.Modal.getInstance(modalElement);
                if (modal) { modal.hide(); }
                if (typeof Toast !== 'undefined') {
                    Toast.success(data.message || 'Đã lưu.');
                }
                window.setTimeout(function () { window.location.reload(); }, 900);
            });
        });
    }

    /** Đặt chế độ popup: 'assign' (gán mới, có tìm người) hoặc 'change' (đổi nội dung). */
    function setMode(mode) {
        setValue('manual_mode', mode);
        var searchGroup = document.getElementById('manual_search_group');
        var title = document.getElementById('manual_modal_title');
        var icon = document.getElementById('manual_modal_icon');
        var isChange = (mode === 'change');
        if (searchGroup) { searchGroup.style.display = isChange ? 'none' : ''; }
        if (title) { title.textContent = isChange ? 'Đổi nội dung chạy' : 'Gán thủ công vào nội dung chạy'; }
        if (icon) { icon.className = isChange ? 'fa fa-exchange' : 'fa fa-user-plus'; }

        // Khách ngoài chỉ áp dụng khi gán mới, không áp dụng cho đổi nội dung.
        var guestToggle = document.getElementById('manual_guest_toggle_group');
        if (guestToggle) { guestToggle.style.display = isChange ? 'none' : ''; }
    }

    /** Đổ dữ liệu dòng vào popup ở chế độ đổi nội dung. */
    function fillChangeMode(btn) {
        setMode('change');
        setValue('manual_registration_id', btn.getAttribute('data-registration-id') || '');
        setValue('manual_attendee_id', ''); // đổi không dùng attendee_id, server lấy theo registration.

        setText('manual_target_name', btn.getAttribute('data-full-name') || '-');
        setText('manual_target_unit', btn.getAttribute('data-unit') || '-');
        document.getElementById('manual_selected_box').style.display = '';

        // Note thu hồi BIB cũ.
        var oldBib = btn.getAttribute('data-bib') || '';
        setText('manual_change_old_bib', oldBib || '(chưa có)');
        var changeNote = document.getElementById('manual_change_note');
        if (changeNote) { changeNote.style.display = ''; }

        // Prefill năm sinh / giới tính.
        var birthYear = btn.getAttribute('data-birth-year') || '';
        var birthSelect = document.getElementById('manual_birth_year');
        var birthNote = document.getElementById('manual_birth_year_note');
        if (birthSelect) {
            birthSelect.value = birthYear;
            if (birthSelect.value !== birthYear) { birthSelect.value = ''; }
            if (birthNote) { birthNote.style.display = birthSelect.value ? 'none' : ''; }
        }
        var gender = btn.getAttribute('data-gender');
        var male = document.getElementById('manual_gender_male');
        var female = document.getElementById('manual_gender_female');
        if (male) { male.checked = (gender === '1'); }
        if (female) { female.checked = (gender === '0'); }

        // Prefill cự ly hiện tại.
        var runEventId = btn.getAttribute('data-run-event-id') || '';
        var runRadio = document.getElementById('manual_run_' + runEventId);
        if (runRadio) { runRadio.checked = true; }
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
                    toastError(result.data.message || 'Không thể gán vào nội dung.');
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

    function resetForm() {
        setValue('manual_attendee_id', '');
        setValue('manual_registration_id', '');
        setValue('manual_search', '');
        setValue('manual_bib', '');
        setValue('manual_guest_name', '');
        var guestCheckbox = document.getElementById('manual_is_guest');
        if (guestCheckbox) { guestCheckbox.checked = false; }
        var guestNameGroup = document.getElementById('manual_guest_name_group');
        if (guestNameGroup) { guestNameGroup.style.display = 'none'; }
        var searchGroupReset = document.getElementById('manual_search_group');
        if (searchGroupReset) { searchGroupReset.style.display = ''; }
        setText('manual_target_name', '-');
        setText('manual_target_unit', '-');
        var changeNote = document.getElementById('manual_change_note');
        if (changeNote) { changeNote.style.display = 'none'; }
        var box = document.getElementById('manual_selected_box');
        if (box) { box.style.display = 'none'; }
        var results = document.getElementById('manual_search_results');
        if (results) { results.style.display = 'none'; results.innerHTML = ''; }
        var birthSelect = document.getElementById('manual_birth_year');
        if (birthSelect) { birthSelect.value = ''; }
        var birthNote = document.getElementById('manual_birth_year_note');
        if (birthNote) { birthNote.style.display = 'none'; }
        ['manual_gender_male', 'manual_gender_female'].forEach(function (id) {
            var el = document.getElementById(id);
            if (el) { el.checked = false; }
        });
        document.querySelectorAll('input[name="manual_run_event_id"]').forEach(function (el) {
            el.checked = false;
        });
        lockForExisting(false);
    }

    function getCheckedValue(name) {
        var checked = document.querySelector('input[name="' + name + '"]:checked');
        if (!checked) {
            return (name === 'manual_gender') ? null : '';
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

    function show(el) { el.style.display = ''; }
    function hide(el) { el.style.display = 'none'; }

    function escapeHtml(str) {
        return String(str == null ? '' : str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    function toastError(message) {
        if (typeof Toast !== 'undefined') {
            Toast.error(message);
        } else {
            window.alert(message);
        }
    }
})();
