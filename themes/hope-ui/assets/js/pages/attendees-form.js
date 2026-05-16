/**
 * Attendees Form - Document Upload & Validation
 * Section 15: Yêu cầu đăng ký mở rộng
 */
(function() {
    'use strict';

    var PORTRAIT_WIDTH = 530;
    var PORTRAIT_HEIGHT = 530;
    var MAX_IMAGE_SIZE = 5 * 1024 * 1024; // 5MB
    var MAX_CONTRACT_SIZE = 10 * 1024 * 1024; // 10MB

    function init() {
        initStaffSelector();
        initDocumentUploads();
        initFormValidation();
    }

    /**
     * BR-REG-01: Toggle giữa chọn từ staff và tự điền
     */
    function initStaffSelector() {
        var modeRadios = document.querySelectorAll('input[name="attendee_mode"]');
        var staffSelect = document.getElementById('staff-select-container');
        var manualFields = document.getElementById('manual-fields-container');
        var staffDropdown = document.getElementById('Attendees_staff_id');

        if (!modeRadios.length) return;

        modeRadios.forEach(function(radio) {
            radio.addEventListener('change', function() {
                var mode = this.value;

                if (mode === 'staff') {
                    staffSelect.style.display = 'block';
                    manualFields.style.display = 'none';
                    enableStaffAutoFill(true);
                } else {
                    staffSelect.style.display = 'none';
                    manualFields.style.display = 'block';
                    enableStaffAutoFill(false);
                    if (staffDropdown) staffDropdown.value = '';
                }
            });
        });

        if (staffDropdown) {
            staffDropdown.addEventListener('change', function() {
                if (!this.value) return;
                autoFillFromStaff(this.value);
            });
        }
    }

    function enableStaffAutoFill(enabled) {
        var fields = ['full_name', 'position', 'department'];
        fields.forEach(function(field) {
            var input = document.getElementById('Attendees_' + field);
            if (input) {
                input.readOnly = enabled;
                input.classList.toggle('bg-light', enabled);
            }
        });
    }

    function autoFillFromStaff(staffId) {
        var apiUrl = document.getElementById('staff-select-container').dataset.apiUrl;
        var apiKey = document.getElementById('staff-select-container').dataset.apiKey;

        fetch(apiUrl + '?id=' + staffId, {
            headers: {
                'Authorization': 'Bearer ' + apiKey,
                'Accept': 'application/json'
            }
        })
        .then(function(response) { return response.json(); })
        .then(function(data) {
            var staff = data.data || data;
            if (staff) {
                setValue('Attendees_full_name', staff.full_name);
                setValue('Attendees_position', staff.position_name || staff.position);
                setValue('Attendees_unit_label', staff.property_name || staff.unit_label);
            }
        })
        .catch(function(err) {
            console.error('Lỗi tải thông tin nhân viên:', err);
        });
    }

    function setValue(id, value) {
        var el = document.getElementById(id);
        if (el) el.value = value || '';
    }

    /**
     * BR-REG-02, BR-REG-03: Document uploads với validation
     */
    function initDocumentUploads() {
        var uploadFields = [
            { id: 'cccd_front', type: 'image', label: 'CCCD mặt trước' },
            { id: 'cccd_back', type: 'image', label: 'CCCD mặt sau' },
            { id: 'portrait', type: 'portrait', label: 'Ảnh chân dung' },
            { id: 'contract', type: 'contract', label: 'Hợp đồng lao động' }
        ];

        uploadFields.forEach(function(field) {
            var input = document.getElementById(field.id + '_upload');
            var preview = document.getElementById(field.id + '_preview');
            var errorEl = document.getElementById(field.id + '_error');

            if (!input) return;

            input.addEventListener('change', function(e) {
                var file = e.target.files[0];
                if (!file) return;

                clearError(errorEl);

                var errors = validateFile(file, field.type);
                if (errors.length > 0) {
                    showError(errorEl, errors.join(', '));
                    input.value = '';
                    return;
                }

                if (field.type === 'portrait') {
                    validatePortraitDimension(file, function(err) {
                        if (err) {
                            showError(errorEl, err);
                            input.value = '';
                        } else {
                            showPreview(file, preview);
                        }
                    });
                } else {
                    showPreview(file, preview);
                }
            });
        });
    }

    function validateFile(file, type) {
        var errors = [];
        var maxSize = type === 'contract' ? MAX_CONTRACT_SIZE : MAX_IMAGE_SIZE;
        var allowedTypes = type === 'contract'
            ? ['image/jpeg', 'image/png', 'application/pdf']
            : ['image/jpeg', 'image/png'];

        if (file.size > maxSize) {
            errors.push('Dung lượng file vượt quá ' + (maxSize / 1024 / 1024) + 'MB');
        }

        if (allowedTypes.indexOf(file.type) === -1) {
            errors.push('Định dạng file không hợp lệ');
        }

        return errors;
    }

    function validatePortraitDimension(file, callback) {
        var img = new Image();
        var url = URL.createObjectURL(file);

        img.onload = function() {
            URL.revokeObjectURL(url);
            if (img.width !== PORTRAIT_WIDTH || img.height !== PORTRAIT_HEIGHT) {
                callback('Ảnh phải có kích thước ' + PORTRAIT_WIDTH + 'x' + PORTRAIT_HEIGHT + 'px (hiện tại: ' + img.width + 'x' + img.height + 'px)');
            } else {
                callback(null);
            }
        };

        img.onerror = function() {
            URL.revokeObjectURL(url);
            callback('Không thể đọc ảnh');
        };

        img.src = url;
    }

    function showPreview(file, previewEl) {
        if (!previewEl) return;

        if (file.type === 'application/pdf') {
            previewEl.innerHTML = '<div class="p-3 bg-light rounded text-center"><i class="fa fa-file-pdf-o fa-3x text-danger"></i><p class="mb-0 mt-2">' + file.name + '</p></div>';
        } else {
            var reader = new FileReader();
            reader.onload = function(e) {
                previewEl.innerHTML = '<img src="' + e.target.result + '" class="img-thumbnail" style="max-height:150px;">';
            };
            reader.readAsDataURL(file);
        }
    }

    function showError(el, message) {
        if (el) {
            el.textContent = message;
            el.style.display = 'block';
        }
    }

    function clearError(el) {
        if (el) {
            el.textContent = '';
            el.style.display = 'none';
        }
    }

    /**
     * Form validation before submit
     */
    function initFormValidation() {
        var form = document.getElementById('attendee-form');
        if (!form) return;

        form.addEventListener('submit', function(e) {
            var requiredDocs = ['cccd_front', 'cccd_back', 'portrait', 'contract'];
            var missingDocs = [];

            requiredDocs.forEach(function(doc) {
                var input = document.getElementById(doc + '_upload');
                var existingPath = document.getElementById(doc + '_path');

                var hasFile = input && input.files && input.files.length > 0;
                var hasExisting = existingPath && existingPath.value;

                if (!hasFile && !hasExisting) {
                    missingDocs.push(doc.replace('_', ' ').toUpperCase());
                }
            });

            if (missingDocs.length > 0) {
                e.preventDefault();
                if (typeof Toast !== 'undefined') {
                    Toast.error('Vui lòng upload: ' + missingDocs.join(', '));
                } else {
                    alert('Vui lòng upload: ' + missingDocs.join(', '));
                }
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
