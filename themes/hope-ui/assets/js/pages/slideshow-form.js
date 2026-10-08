/**
 * Slideshow form — dropzone tải ảnh nền (desktop & mobile).
 * Kéo & thả hoặc bấm để chọn ảnh → upload AJAX → lưu URL vào hidden field.
 */
document.addEventListener('DOMContentLoaded', function () {
    var zones = document.querySelectorAll('.slide-dropzone');

    zones.forEach(function (zone) {
        var input = zone.querySelector('.slide-dropzone-input');
        var preview = zone.querySelector('.slide-dropzone-preview');
        var status = zone.querySelector('.slide-dropzone-status');
        var hidden = document.getElementById('Slideshow_' + zone.getAttribute('data-target'));
        var uploadUrl = zone.getAttribute('data-upload-url');

        if (!input || !hidden) {
            return;
        }

        zone.addEventListener('click', function () {
            input.click();
        });

        zone.addEventListener('dragover', function (e) {
            e.preventDefault();
            zone.classList.add('bg-light');
        });

        zone.addEventListener('dragleave', function () {
            zone.classList.remove('bg-light');
        });

        zone.addEventListener('drop', function (e) {
            e.preventDefault();
            zone.classList.remove('bg-light');
            if (e.dataTransfer.files.length) {
                uploadFile(e.dataTransfer.files[0]);
            }
        });

        input.addEventListener('change', function () {
            if (this.files.length) {
                uploadFile(this.files[0]);
            }
        });

        function uploadFile(file) {
            if (!/^image\//.test(file.type)) {
                if (typeof Toast !== 'undefined') {
                    Toast.error('Vui lòng chọn tệp ảnh hợp lệ.');
                }
                return;
            }

            status.textContent = 'Đang tải ảnh lên...';

            var formData = new FormData();
            formData.append('file', file);

            fetch(uploadUrl, { method: 'POST', body: formData })
                .then(function (res) { return res.json(); })
                .then(function (data) {
                    if (data.success && data.url) {
                        hidden.value = data.url;
                        preview.innerHTML = '<img src="' + data.url + '" alt="preview" style="max-height:140px;max-width:100%;">';
                        status.textContent = '';
                        if (typeof Toast !== 'undefined') {
                            Toast.success('Tải ảnh thành công.');
                        }
                    } else {
                        status.textContent = '';
                        if (typeof Toast !== 'undefined') {
                            Toast.error(data.message || 'Không thể tải ảnh.');
                        }
                    }
                })
                .catch(function () {
                    status.textContent = '';
                    if (typeof Toast !== 'undefined') {
                        Toast.error('Lỗi kết nối khi tải ảnh.');
                    }
                });
        }
    });
});
